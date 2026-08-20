# راهنمای نصب روی WordPress

## ⚠️ مهم: بکاپ بگیرید قبل از هر تغییر!

فایل بکاپ: `backup-homepage-2026-08-13.html` (۱۲۴KB — HTML کامل صفحه فعلی)

---

## روش پیشنهادی (سریع‌ترین)

### مرحله ۱ — بکاپ WordPress
1. wp-admin → **UpdraftPlus** یا **All-in-One WP Migration** → Export
2. یا: **صفحات → خانه → ویرایش → Revisions** → آخرین نسخه را Restore کنید

### مرحله ۲ — نصب با پلاگین

1. پلاگین **Insert Headers and Footers** یا **WPCode** را نصب کنید
2. در بخش Header این کدها را اضافه کنید:

```html
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
```

3. **صفحات → Add New** → نام: «خانه جدید»
4. یک بلوک **Custom HTML** اضافه کنید
5. محتوای بین `<body>` و `</body>` از فایل `index.html` را paste کنید
6. **Settings → Reading → Homepage** → «خانه جدید» را انتخاب کنید

### مرحله ۳ — فرم نوبت‌دهی

فرم فعلی سایت از **Gravity Forms** استفاده می‌کند.
برای اتصال فرم جدید:
- shortcode فرم Gravity Forms را جایگزین `<form>` در بخش `#appointment` کنید
- یا از Contact Form 7 shortcode استفاده کنید

---

## روش Template (حرفه‌ای)

1. فایل `wordpress/page-delsa-home.php` را در پوشه تم child آپلود کنید:
   ```
   wp-content/themes/your-child-theme/page-delsa-home.php
   ```
2. **صفحات → Add New** → Template: «Delsa Homepage (New)»
3. **Settings → Reading** → این صفحه را Homepage کنید

---

## نکات

- **Tailwind CDN** در production بهتر است compile شود (برای سرعت)
- **لوگو:** تصویر لوگوی فعلی را از `wp-content/uploads` بردارید و جای SVG placeholder بگذارید
- **لینک‌ها:** `#` را با URL واقعی صفحات WordPress جایگزین کنید
- **نقشه:** iframe OpenStreetMap را با Google Maps embed واقعی جایگزین کنید

---

## دسترسی wp-admin

از IP شما (ایران) wp-admin در دسترس است.
از IP سرور ابری Cursor، WAF سایت دسترسی را block کرد — باید خودتان از مرورگر محلی وارد شوید.

## امنیت

🔒 **حتماً پسورد wp-admin را تغییر دهید** — در چت به اشتراک گذاشته شده است.
