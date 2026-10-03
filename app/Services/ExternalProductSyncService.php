<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ExternalProductSyncService
{
    /**
     * Fetch products from the configured API and synchronize them into the local database.
     */
    public function sync(?string $customUrl = null, ?string $customToken = null): array
    {
        $apiUrl = trim($customUrl ?: Setting::get('products_api_url', ''));
        $apiToken = trim($customToken ?: Setting::get('products_api_token', ''));

        if (empty($apiUrl)) {
            return [
                'success' => false,
                'message' => 'يرجى إدخال رابط الـ API أولاً في الإعدادات.',
                'count'   => 0,
            ];
        }

        try {
            $fetchResult = $this->fetchData($apiUrl, $apiToken);

            if (!$fetchResult['success']) {
                return [
                    'success' => false,
                    'message' => $fetchResult['error'] ?? 'فشل الاتصال بالـ API أو استلام بيانات غير صالحة. يرجى التأكد من الرابط وعمل الخادم.',
                    'count'   => 0,
                ];
            }

            $json = $fetchResult['data'];
            $items = $this->extractProductList($json);

            if (empty($items)) {
                return [
                    'success' => false,
                    'message' => 'تم الاتصال بالـ API بنجاح ولكن لم يتم العثور على أي منتجات في الرد المستلم من الخادم.',
                    'count'   => 0,
                ];
            }

            $createdCount = 0;
            $updatedCount = 0;

            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $productData = $this->mapItemToProductData($item);
                if (empty($productData['name'])) {
                    continue;
                }

                // Ensure category exists
                $this->ensureCategoryExists($productData['category']);

                // Find existing product by name
                $existing = Product::where('name', $productData['name'])->first();

                if ($existing) {
                    unset($productData['vendor_id']);
                    $existing->update($productData);
                    $updatedCount++;
                } else {
                    $productData['vendor_id'] = app(\App\Services\VendorService::class)->defaultVendor()->id;
                    $productData['approval_status'] = 'approved';
                    Product::create($productData);
                    $createdCount++;
                }
            }

            // Save last sync timestamp
            Setting::set('products_api_last_synced_at', now()->toDateTimeString());
            \Illuminate\Support\Facades\Cache::put('products_api_last_synced_time', time(), 86400 * 30);

            // Invalidate caches
            CacheService::clearAllPublicCaches();

            $totalSynced = $createdCount + $updatedCount;

            return [
                'success'       => true,
                'message'       => "تمت المزامنة بنجاح! تم استيراد {$createdCount} منتج جديد وتحديث {$updatedCount} منتج.",
                'count'         => $totalSynced,
                'created_count' => $createdCount,
                'updated_count' => $updatedCount,
                'last_synced_at'=> now()->toDateTimeString(),
            ];

        } catch (\Throwable $e) {
            Log::error('External Product API exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'حدث خطأ أثناء معالجة المنتجات: ' . $e->getMessage(),
                'count'   => 0,
            ];
        }
    }

    /**
     * Automatically synchronize products if the configured API URL exists
     * and the elapsed time since the last sync exceeds $intervalMinutes.
     * Uses Cache lock to prevent duplicate parallel runs.
     */
    public function syncIfStale(int $intervalMinutes = 15): array
    {
        $apiUrl = trim(Setting::get('products_api_url', ''));
        if (empty($apiUrl)) {
            return ['success' => false, 'message' => 'رابط الـ API غير محدد.', 'count' => 0];
        }

        $lastSync = \Illuminate\Support\Facades\Cache::get('products_api_last_synced_time');
        if (!$lastSync) {
            $lastSyncStr = Setting::get('products_api_last_synced_at');
            $lastSync = $lastSyncStr ? strtotime($lastSyncStr) : 0;
        }

        if ($lastSync > 0 && (time() - (int)$lastSync) < ($intervalMinutes * 60)) {
            return ['success' => true, 'message' => 'المنتجات محدثة بالفعل.', 'count' => 0];
        }

        try {
            $lock = \Illuminate\Support\Facades\Cache::lock('external_products_sync_lock', 60);
            if ($lock->get()) {
                try {
                    return $this->sync($apiUrl);
                } finally {
                    optional($lock)->release();
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Automatic syncIfStale lock error: ' . $e->getMessage());
        }

        return ['success' => true, 'message' => 'مزامنة جارية في الخلفية.', 'count' => 0];
    }

    /**
     * Fetch API data using cURL / Http client with robust fallbacks and detailed error reporting.
     */
    private function fetchData(string $url, ?string $token): array
    {
        $lastError = '';

        // 1. Direct native cURL (fastest and handles SSL / OpenSSL 3 / IPv4 best)
        if (function_exists('curl_init')) {
            try {
                $ch = curl_init($url);
                $headers = [
                    'Accept: application/json',
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                ];
                if (!empty($token)) {
                    $headers[] = 'Authorization: Bearer ' . $token;
                }

                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER     => $headers,
                    CURLOPT_TIMEOUT        => 20,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS      => 5,
                    CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4,
                ]);

                if (defined('CURLOPT_SSL_CIPHER_LIST')) {
                    curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'DEFAULT@SECLEVEL=0');
                }

                $raw = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlErr = curl_error($ch);
                curl_close($ch);

                if ($raw !== false && $httpCode >= 200 && $httpCode < 300) {
                    $decoded = json_decode($raw, true);
                    if (is_array($decoded)) {
                        return ['success' => true, 'data' => $decoded];
                    }
                    $lastError = 'الخادم أرجع استجابة لكنها ليست بصيغة JSON صالحة.';
                } elseif ($httpCode === 404) {
                    $lastError = 'الرابط غير موجود (404 Not Found) - يرجى التأكد من صحة مسار الـ Endpoint.';
                } elseif ($httpCode === 401 || $httpCode === 403) {
                    $lastError = "فشل التوثيق ({$httpCode} Unauthorized) - يرجى التأكد من إدخال الـ Bearer Token الصحيح.";
                } elseif ($httpCode >= 500) {
                    $lastError = "خادم الـ API أرجع خطأ داخلي ({$httpCode} Internal Server Error).";
                } elseif (!empty($curlErr)) {
                    if (str_contains(strtolower($curlErr), 'could not resolve host')) {
                        $lastError = 'تعذر الوصول لاسم النطاق (Host not found). إذا كنت تستخدم رابط محلي استخدم 127.0.0.1 بدلاً من localhost.';
                    } elseif (str_contains(strtolower($curlErr), 'connection reset') || str_contains(strtolower($curlErr), 'recv failure')) {
                        $lastError = 'مزود الإنترنت أو السيرفر قام بقطع الاتصال (Connection Reset). جرب رابط API آخر مثل dummyjson.com/products.';
                    } elseif (str_contains(strtolower($curlErr), 'connection refused') || str_contains(strtolower($curlErr), 'failed to connect')) {
                        $lastError = 'تم رفض الاتصال (Connection Refused). تأكد أن الخادم/المشروع يعمل ومفتوح على نفس المنفذ (Port).';
                    } elseif (str_contains(strtolower($curlErr), 'timed out') || str_contains(strtolower($curlErr), 'timeout')) {
                        $lastError = 'انتهت مهلة الاتصال (Connection Timeout). الخادم لم يستجب في الوقت المحدد.';
                    } else {
                        $lastError = 'خطأ اتصال cURL: ' . $curlErr;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Direct cURL attempt exception: ' . $e->getMessage());
            }
        }

        // 2. Fallback to Laravel Http Client
        try {
            $options = ['verify' => false];
            if (defined('CURLOPT_SSL_CIPHER_LIST')) {
                $options['curl'] = [
                    CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=0',
                ];
            }

            $request = Http::timeout(15)
                ->withOptions($options)
                ->withoutVerifying()
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                ])
                ->acceptJson();

            if (!empty($token)) {
                $request = $request->withToken($token);
            }

            $response = $request->get($url);

            if ($response->successful()) {
                $decoded = $response->json();
                if (is_array($decoded)) {
                    return ['success' => true, 'data' => $decoded];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Http client sync fallback failed: ' . $e->getMessage());
        }

        // 3. Fallback to stream context
        try {
            $headers = [
                "Accept: application/json",
                "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)",
            ];
            if (!empty($token)) {
                $headers[] = "Authorization: Bearer {$token}";
            }

            $context = stream_context_create([
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ],
                'http' => [
                    'timeout' => 15,
                    'header'  => implode("\r\n", $headers) . "\r\n",
                    'ignore_errors' => true,
                ],
            ]);

            $raw = @file_get_contents($url, false, $context);
            if ($raw !== false) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    return ['success' => true, 'data' => $decoded];
                }
            }
        } catch (\Throwable $e) {
            Log::error('Stream context sync fallback also failed: ' . $e->getMessage());
        }

        return [
            'success' => false,
            'error'   => !empty($lastError) ? $lastError : 'فشل الاتصال بالـ API أو استلام بيانات غير صالحة. يرجى التأكد من الرابط وعمل الخادم.',
        ];
    }

    /**
     * Extract array of product objects from varied API JSON structures.
     */
    private function extractProductList(mixed $json): array
    {
        if (!is_array($json)) {
            return [];
        }

        // Direct array of products: [ {...}, {...} ]
        if (array_is_list($json) && !empty($json) && is_array($json[0])) {
            return $json;
        }

        // Standard Laravel resource or API envelope: { "data": [...] }
        if (isset($json['data']) && is_array($json['data'])) {
            if (array_is_list($json['data'])) {
                return $json['data'];
            }
            // Paginated envelope: { "data": { "data": [...] } } or { "data": { "products": [...] } }
            if (isset($json['data']['data']) && is_array($json['data']['data'])) {
                return $json['data']['data'];
            }
            if (isset($json['data']['products']) && is_array($json['data']['products'])) {
                return $json['data']['products'];
            }
        }

        // Common keys: { "products": [...] }, { "items": [...] }, { "result": [...] }
        foreach (['products', 'items', 'result', 'records'] as $key) {
            if (isset($json[$key]) && is_array($json[$key]) && array_is_list($json[$key])) {
                return $json[$key];
            }
        }

        return [];
    }

    /**
     * Map a generic product item from API to our Product model fields.
     */
    private function mapItemToProductData(array $item): array
    {
        // 1. Name
        $name = $item['name'] 
            ?? $item['title'] 
            ?? $item['name_ar'] 
            ?? $item['name_en'] 
            ?? $item['product_name'] 
            ?? 'منتج غير مسمى';

        // 2. Brand
        $brand = $item['brand'] 
            ?? $item['brand_name'] 
            ?? $item['manufacturer'] 
            ?? 'عام';
        if (is_array($brand)) {
            $brand = $brand['name'] ?? $brand['title'] ?? 'عام';
        }

        // 3. Category
        $rawCategory = $item['category'] 
            ?? $item['category_slug'] 
            ?? $item['category_name'] 
            ?? 'general';
        if (is_array($rawCategory)) {
            $rawCategory = $rawCategory['slug'] ?? $rawCategory['name'] ?? $rawCategory['title'] ?? 'general';
        }
        $categorySlug = Str::slug((string)$rawCategory) ?: 'general';

        // 4. Description
        $description = $item['description'] 
            ?? $item['details'] 
            ?? $item['desc'] 
            ?? $item['summary'] 
            ?? $name;

        // 5. Price & Old Price
        $price = floatval($item['price'] ?? $item['sell_price'] ?? $item['unit_price'] ?? 0);
        if ($price <= 0 && !empty($item['units']) && is_array($item['units'])) {
            foreach ($item['units'] as $u) {
                if (!empty($u['price']) && floatval($u['price']) > 0) {
                    $price = floatval($u['price']);
                    break;
                }
            }
        }
        $oldPrice = null;
        if (!empty($item['old_price'])) {
            $oldPrice = floatval($item['old_price']);
        } elseif (!empty($item['compare_at_price'])) {
            $oldPrice = floatval($item['compare_at_price']);
        } elseif (!empty($item['original_price'])) {
            $oldPrice = floatval($item['original_price']);
        }

        // 6. Stock
        $stock = intval($item['stock'] ?? $item['quantity'] ?? $item['qty'] ?? $item['balance'] ?? 10);

        // 7. Image
        $image = null;
        if (!empty($item['image']) && is_string($item['image'])) {
            $image = $item['image'];
        } elseif (!empty($item['image_url']) && is_string($item['image_url'])) {
            $image = $item['image_url'];
        } elseif (!empty($item['thumbnail']) && is_string($item['thumbnail'])) {
            $image = $item['thumbnail'];
        } elseif (!empty($item['photo']) && is_string($item['photo'])) {
            $image = $item['photo'];
        } elseif (!empty($item['images']) && is_array($item['images']) && !empty($item['images'][0])) {
            $firstImg = $item['images'][0];
            $image = is_string($firstImg) ? $firstImg : ($firstImg['url'] ?? $firstImg['path'] ?? null);
        }

        // 8. Rating & Reviews
        $rating = isset($item['rating']) 
            ? (is_array($item['rating']) ? intval(round($item['rating']['rate'] ?? 5)) : intval(round($item['rating'])))
            : 5;
        $reviews = isset($item['reviews']) 
            ? (is_array($item['reviews']) ? intval($item['reviews']['count'] ?? 0) : intval($item['reviews']))
            : (isset($item['rating']['count']) ? intval($item['rating']['count']) : 0);

        // 9. Specs
        $specs = null;
        if (isset($item['specs']) && is_array($item['specs'])) {
            $specs = $item['specs'];
        }

        // 10. Status
        $isActive = true;
        if (isset($item['is_active'])) {
            $isActive = (bool) $item['is_active'];
        } elseif (isset($item['status'])) {
            $isActive = in_array(strtolower((string)$item['status']), ['1', 'active', 'true', 'available']);
        }

        return [
            'name'        => trim($name),
            'brand'       => trim($brand),
            'category'    => $categorySlug,
            'description' => trim($description),
            'price'       => max(0, $price),
            'old_price'   => $oldPrice && $oldPrice > $price ? $oldPrice : null,
            'stock'       => max(0, $stock),
            'image'       => $image,
            'badge'       => $item['badge'] ?? null,
            'badge_color' => $item['badge_color'] ?? null,
            'rating'      => max(1, min(5, $rating)),
            'reviews'     => max(0, $reviews),
            'specs'       => $specs,
            'is_active'   => $isActive,
            'is_featured' => !empty($item['is_featured']),
        ];
    }

    /**
     * Helper to auto-create category if missing so the category filter & tree don't break.
     */
    private function ensureCategoryExists(string $slug): void
    {
        if (empty($slug)) {
            return;
        }

        $exists = Category::where('slug', $slug)->exists();
        if (!$exists) {
            $nameEn = ucwords(str_replace(['-', '_'], ' ', $slug));
            $arabicNames = [
                'general'    => 'منتجات عامة',
                'printers'   => 'طابعات وماسحات',
                'mice'       => 'ماوسات',
                'headphones' => 'سماعات',
                'flash'      => 'فلاشات',
            ];
            $nameAr = $arabicNames[strtolower($slug)] ?? $nameEn;

            Category::create([
                'slug'       => $slug,
                'name_ar'    => $nameAr,
                'name_en'    => $nameEn,
                'is_active'  => true,
                'sort_order' => 99,
            ]);
            CacheService::clearCategoryCaches();
        }
    }
}
