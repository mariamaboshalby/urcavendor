# دليل دمج خرائط جوجل / Google Maps Integration Guide

## الموقع / Location
**الإحداثيات / Coordinates:**
- Latitude: 30.943686
- Longitude: 31.289423
- العنوان / Address: شبين الكوم، المنوفية، مصر

## الخريطة المضافة / Maps Added

### 1. صفحة "من نحن" / About Page
**الملف:** `resources/views/about/index.blade.php`

**الميزات:**
- قسم كامل للموقع مع الخريطة
- معلومات الاتصال التفصيلية
- أزرار للحصول على الاتجاهات وWhatsApp
- تصميم متجاوب

**الرابط:** `/about`

### 2. الـ Footer / Footer Section
**الملف:** `resources/views/layouts/app.blade.php`

**التعديلات:**
- إضافة زر "عرض الموقع على الخريطة"
- تحويل رقم الهاتف والبريد الإلكتروني إلى روابط قابلة للنقر
- تأثيرات تفاعلية عند المرور بالماوس

**متوفر في:** جميع صفحات الموقع

### 3. صفحة اتصل بنا / Contact Page (اختياري)
**الملف:** `resources/views/contact/index.blade.php`

**الميزات:**
- بطاقات معلومات الاتصال (هاتف، WhatsApp، بريد، أوقات العمل)
- نموذج اتصال تفاعلي
- خريطة Google Maps مدمجة
- زر للحصول على الاتجاهات

**ملاحظة:** يحتاج إلى إضافة route في `routes/web.php`

## كود الخريطة / Map Embed Code

```html
<iframe 
    src="https://www.google.com/maps/embed?pb=!1m17!1m12!1m3!1d714.1338338154081!2d31.28942265912146!3d30.94368566303161!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m2!1m1!2s!5e0!3m2!1sar!2seg!4v1783340972314!5m2!1sar!2seg" 
    width="100%" 
    height="450" 
    style="border:0;border-radius:16px;" 
    allowfullscreen="" 
    loading="lazy" 
    referrerpolicy="no-referrer-when-downgrade">
</iframe>
```

## روابط مفيدة / Useful Links

### رابط الموقع على الخريطة / Direct Map Link
```
https://www.google.com/maps/place/30%C2%B056'37.3%22N+31%C2%B017'22.0%22E/@30.9436857,31.2894227,21z
```

### رابط الاتجاهات / Directions Link
```
https://www.google.com/maps/dir//30.943686,31.289423
```

### رابط مشاركة الموقع / Share Location Link
```
https://maps.app.goo.gl/[your-short-link]
```

## إضافة صفحة اتصل بنا / Add Contact Page

إذا أردت تفعيل صفحة اتصل بنا، أضف هذا في `routes/web.php`:

```php
Route::get('/contact', function () {
    return view('contact.index');
})->name('contact');
```

ثم أضف الرابط في القائمة الرئيسية في `resources/views/layouts/app.blade.php`:

```blade
<a href="{{ route('contact') }}" 
   class="urca-nav-link {{ request()->routeIs('contact') ? 'active' : '' }}">
    {{ app()->getLocale() === 'ar' ? 'اتصل بنا' : 'Contact Us' }}
</a>
```

## التخصيص / Customization

### تغيير ارتفاع الخريطة / Change Map Height
في ملف CSS أو inline:
```css
height: 450px; /* غير القيمة كما تريد / Change value as needed */
```

### تغيير نمط الخريطة / Change Map Style
استخدم Google Maps Platform لإنشاء نمط مخصص:
1. اذهب إلى: https://console.cloud.google.com/google/maps-apis
2. أنشئ نمط مخصص
3. احصل على الكود الجديد

### إضافة Marker مخصص / Add Custom Marker
استخدم Google Maps JavaScript API للحصول على تحكم أكبر:
```javascript
const map = new google.maps.Map(document.getElementById("map"), {
    center: { lat: 30.943686, lng: 31.289423 },
    zoom: 15,
});

const marker = new google.maps.Marker({
    position: { lat: 30.943686, lng: 31.289423 },
    map: map,
    title: "urca - قطع غيار شاشات التلفزيون"
});
```

## معلومات الاتصال / Contact Information

| النوع / Type | القيمة / Value |
|--------------|----------------|
| الهاتف / Phone | +20 10 01324539 |
| البريد / Email | urca .tv@gmail.com |
| العنوان / Address | {{ __('app.footer_address') }} |
| أوقات العمل / Hours | {{ __('app.footer_hours') }} |

## خصائص الأمان / Security Features

- `referrerpolicy="no-referrer-when-downgrade"` - حماية الخصوصية
- `loading="lazy"` - تحميل مؤجل لتحسين الأداء
- `allowfullscreen=""` - السماح بوضع الشاشة الكاملة

## التحديثات المستقبلية / Future Updates

### ميزات مقترحة / Suggested Features:
1. ✅ إضافة الخريطة في صفحة "من نحن"
2. ✅ زر الخريطة في Footer
3. ⏳ صفحة اتصل بنا كاملة (متوفرة - تحتاج route)
4. ⏳ نموذج اتصال يعمل (يحتاج backend)
5. ⏳ دمج Google Maps JavaScript API
6. ⏳ إضافة directions API

## الاختبار / Testing

تحقق من الخريطة على:
- ✅ Desktop browsers
- ✅ Mobile devices
- ✅ Tablet sizes
- ✅ RTL (Arabic) layout
- ✅ LTR (English) layout

---
**تاريخ الإضافة / Date Added:** 2025-01-01  
**آخر تحديث / Last Updated:** 2025-01-01
