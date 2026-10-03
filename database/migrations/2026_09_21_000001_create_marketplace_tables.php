<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role VARCHAR(32) NOT NULL DEFAULT 'user'");
        }

        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('store_name');
            $table->string('store_slug')->unique();
            $table->string('phone')->nullable();
            $table->string('logo')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'suspended'])->default('pending');
            $table->decimal('commission_rate', 5, 2)->default(10);
            $table->decimal('rating', 3, 2)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('store_slug');
        });

        Schema::create('vendor_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('balance', 12, 2)->default(0);
            $table->decimal('pending_balance', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // credit, debit, withdrawal, commission, refund
            $table->decimal('amount', 12, 2);
            $table->string('reference')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['vendor_id', 'created_at']);
        });

        Schema::create('withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['vendor_id', 'status']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('vendor_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->enum('approval_status', ['pending', 'approved', 'rejected', 'inactive'])->default('approved')->after('is_active');
            $table->text('rejection_reason')->nullable()->after('approval_status');

            $table->index('vendor_id');
            $table->index('approval_status');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('vendor_id')->nullable()->after('product_id')->constrained()->nullOnDelete();
            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending')->after('total');
            $table->decimal('commission_rate', 5, 2)->default(0)->after('status');
            $table->decimal('commission_amount', 12, 2)->default(0)->after('commission_rate');
            $table->decimal('vendor_earning', 12, 2)->default(0)->after('commission_amount');

            $table->index('vendor_id');
            $table->index('status');
        });

        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->decimal('order_amount', 12, 2);
            $table->decimal('commission_rate', 5, 2);
            $table->decimal('commission_amount', 12, 2);
            $table->decimal('vendor_amount', 12, 2);
            $table->enum('status', ['pending', 'available', 'paid', 'cancelled'])->default('pending');
            $table->timestamps();

            $table->unique('order_item_id');
            $table->index(['vendor_id', 'status']);
            $table->index('order_id');
        });

        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('author_name');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->text('comment')->nullable();
            $table->boolean('is_approved')->default(true);
            $table->timestamps();

            $table->index(['product_id', 'is_approved']);
            $table->index('vendor_id');
        });

        $this->seedDefaultVendorAndBackfill();
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
        Schema::dropIfExists('commissions');

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vendor_id');
            $table->dropColumn(['status', 'commission_rate', 'commission_amount', 'vendor_earning']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vendor_id');
            $table->dropColumn(['approval_status', 'rejection_reason']);
        });

        Schema::dropIfExists('withdrawals');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('vendor_wallets');
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('notifications');
    }

    private function seedDefaultVendorAndBackfill(): void
    {
        $admin = DB::table('users')->where('role', 'admin')->orderBy('id')->first();

        if (! $admin) {
            $adminId = DB::table('users')->insertGetId([
                'name' => 'مدير النظام',
                'email' => 'admin@admin.com',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $admin = DB::table('users')->where('id', $adminId)->first();
        }

        $existingVendorId = DB::table('vendors')->where('store_slug', 'default-store')->value('id');

        if (! $existingVendorId) {
            $existingVendorId = DB::table('vendors')->insertGetId([
                'user_id' => $admin->id,
                'store_name' => 'Default Store',
                'store_slug' => 'default-store',
                'phone' => $admin->phone ?? null,
                'description' => 'Official platform store',
                'status' => 'approved',
                'commission_rate' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('vendor_wallets')->insert([
                'vendor_id' => $existingVendorId,
                'balance' => 0,
                'pending_balance' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('products')->whereNull('vendor_id')->update([
            'vendor_id' => $existingVendorId,
            'approval_status' => 'approved',
        ]);

        $products = DB::table('products')->select('id', 'vendor_id')->get()->keyBy('id');

        foreach (DB::table('order_items')->whereNull('vendor_id')->get() as $item) {
            $vendorId = $products[$item->product_id]->vendor_id ?? $existingVendorId;
            DB::table('order_items')->where('id', $item->id)->update(['vendor_id' => $vendorId]);
        }
    }
};
