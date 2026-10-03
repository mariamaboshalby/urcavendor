# روابط وسائل التواصل الاجتماعي
# Social Media Links Integration

## 📱 معلومات الاتصال / Contact Information

### الهاتف / Phone Numbers
| النوع / Type | الرقم / Number | الرابط / Link |
|--------------|----------------|---------------|
| الهاتف الأساسي / Primary | +20 10 93803270 | `tel:+201093803270` |
| WhatsApp | +20 10 93803270 | `https://wa.me/201093803270` |

### البريد الإلكتروني / Email
- **Email:** urca .tv@gmail.com
- **Link:** `mailto:urca .tv@gmail.com`

---

## 🌐 روابط وسائل التواصل الاجتماعي / Social Media Links

### Facebook
- **Profile Link:** https://www.facebook.com/share/181WTrqgHu/
- **Messenger:** https://m.me/181WTrqgHu
- **Page ID:** 181WTrqgHu

### Instagram
- **Profile:** https://www.instagram.com/urca .tv/
- **Username:** @urca .tv

### YouTube
- **Channel:** https://www.youtube.com/@urca -tv
- **Handle:** @urca -tv

### WhatsApp Business
- **Direct Chat:** https://wa.me/201093803270
- **Message with Text:** `https://wa.me/201093803270?text=مرحباً`

---

## ✅ التحديثات المطبقة / Applied Updates

### 1. Footer (جميع الصفحات)
**الملف:** `resources/views/layouts/app.blade.php`

**التحديثات:**
- ✅ رابط Facebook الصحيح
- ✅ رابط Instagram
- ✅ رابط WhatsApp برقم صحيح
- ✅ رابط YouTube
- ✅ زر اتصال مباشر
- ✅ تحديث رقم الهاتف إلى +20 10 93803270
- ✅ إضافة `target="_blank"` و `rel="noopener noreferrer"`
- ✅ إضافة tooltips بالعربية

### 2. أزرار التواصل العائمة / Floating Social Buttons
**الملف:** `resources/views/components/social-float-buttons.blade.php`

**الميزات:**
- ✅ زر WhatsApp أخضر مع تأثيرات
- ✅ زر الاتصال بألوان الموقع
- ✅ زر Facebook Messenger
- ✅ تأثيرات Pulse متحركة
- ✅ نصوص تظهر عند المرور بالماوس
- ✅ متجاوب بالكامل
- ✅ دعم RTL (العربية)
- ✅ موضع ثابت على الجانب

### 3. جميع الصفحات (تحديث شامل)
**تم تحديث:**
- ✅ home.blade.php
- ✅ about/index.blade.php
- ✅ contact/index.blade.php
- ✅ orders/track.blade.php

---

## 🎨 تصميم الأزرار العائمة / Floating Buttons Design

### الألوان المستخدمة:
```css
WhatsApp:  linear-gradient(135deg, #25D366 0%, #128C7E 100%)
Phone:     linear-gradient(135deg, #051836 0%, #0a2e5c 100%)
Messenger: linear-gradient(135deg, #0084FF 0%, #0066CC 100%)
```

### الموضع / Position:
- **Desktop:** `bottom: 100px; left: 20px`
- **RTL:** `bottom: 100px; right: 20px`
- **Mobile:** `bottom: 80px; left: 15px`

### التأثيرات / Effects:
- تحجيم عند المرور (Scale 1.1)
- حركة نبض (Pulse animation)
- إظهار النص عند المرور
- ظلال ديناميكية

---

## 📋 كود HTML للروابط / HTML Link Code

### Facebook
```html
<a href="https://www.facebook.com/share/181WTrqgHu/" 
   target="_blank" 
   rel="noopener noreferrer">
    <i class="fab fa-facebook-f"></i>
</a>
```

### WhatsApp
```html
<a href="https://wa.me/201093803270" 
   target="_blank" 
   rel="noopener noreferrer">
    <i class="fab fa-whatsapp"></i>
</a>
```

### Phone
```html
<a href="tel:+201093803270">
    <i class="fas fa-phone-alt"></i>
</a>
```

### Instagram
```html
<a href="https://www.instagram.com/urca .tv/" 
   target="_blank" 
   rel="noopener noreferrer">
    <i class="fab fa-instagram"></i>
</a>
```

### YouTube
```html
<a href="https://www.youtube.com/@urca -tv" 
   target="_blank" 
   rel="noopener noreferrer">
    <i class="fab fa-youtube"></i>
</a>
```

---

## 🔧 التخصيص / Customization

### تغيير موضع الأزرار العائمة:
```css
/* تغيير من اليسار إلى اليمين / Change from left to right */
.social-float-buttons {
    left: auto;
    right: 20px;
}
```

### تغيير حجم الأزرار:
```css
.social-float-btn {
    width: 60px;  /* Default: 56px */
    height: 60px;
    font-size: 1.75rem;  /* Default: 1.5rem */
}
```

### إضافة زر جديد:
```html
<a href="YOUR_LINK" 
   target="_blank" 
   rel="noopener noreferrer"
   class="social-float-btn YOUR_CLASS"
   aria-label="YOUR_LABEL">
    <i class="fab fa-YOUR_ICON"></i>
    <span class="social-float-text">YOUR_TEXT</span>
</a>
```

```css
.YOUR_CLASS {
    background: linear-gradient(135deg, #COLOR1 0%, #COLOR2 100%);
}
```

---

## 📱 اختبار الروابط / Test Links

يمكنك اختبار الروابط:

### WhatsApp Test:
1. افتح: https://wa.me/201093803270
2. يجب أن يفتح WhatsApp مباشرة
3. الرسالة جاهزة للإرسال

### Phone Test:
1. انقر على: +20 10 93803270
2. يجب أن يفتح تطبيق الهاتف
3. الرقم جاهز للاتصال

### Facebook Test:
1. افتح: https://www.facebook.com/share/181WTrqgHu/
2. يجب أن تفتح صفحة Facebook
3. تحقق من البيانات الصحيحة

---

## 🌟 الميزات / Features

### ✅ إمكانية الوصول / Accessibility
- `aria-label` على جميع الروابط
- `title` tooltips بالعربية والإنجليزية
- Focus states واضحة
- Alternative text للأيقونات

### ✅ الأمان / Security
- `target="_blank"` للروابط الخارجية
- `rel="noopener noreferrer"` لمنع الثغرات
- روابط آمنة (https)

### ✅ الأداء / Performance
- أيقونات Font Awesome المحملة مسبقاً
- CSS مضمّن للأزرار العائمة
- تحميل مؤجل (lazy loading) للخريطة

### ✅ التجاوب / Responsiveness
- تصميم متجاوب 100%
- أحجام مختلفة للشاشات
- إخفاء النصوص في الشاشات الصغيرة
- دعم RTL كامل

---

## 📞 معلومات إضافية / Additional Info

### أوقات العمل / Working Hours
- السبت - الخميس: 9 ص - 6 م
- Saturday - Thursday: 9 AM - 6 PM

### العنوان / Address
- شبين الكوم، المنوفية، مصر
- Shibin El Kom, Menoufia, Egypt

### الموقع على الخريطة / Map Location
- Coordinates: 30.943686, 31.289423
- Google Maps: https://www.google.com/maps/place/30%C2%B056'37.3%22N+31%C2%B017'22.0%22E/@30.9436857,31.2894227,21z

---

## ✨ التحسينات المستقبلية / Future Enhancements

### مقترحات:
- [ ] إضافة زر Telegram
- [ ] إضافة TikTok إذا متوفر
- [ ] زر LinkedIn للأعمال
- [ ] دمج Facebook Pixel
- [ ] تتبع النقرات (Analytics)
- [ ] إضافة QR code للتواصل
- [ ] Live Chat Widget
- [ ] زر المشاركة Share

---

**تاريخ التحديث / Last Updated:** 2025-01-01  
**الحالة / Status:** ✅ نشط ويعمل / Active & Working
