فقط wp-admin — بدون FTP
══════════════════════════

علت صفحه سفید (نه کار نکردن شما):
Elementor هر ویجت HTML را داخل div جدا می‌گذارد.
اگر part قدیمی وسط یک <style> قطع شده باشد، مرورگر بقیه HTML را CSS
می‌خواند → صفحه سفید. محتوا روی سرور هست؛ CSS خراب است.

راه‌حل: ۴ part جدید — هر کدام style کامل و بسته

گزینه A — Import (سریع‌تر)
──────────────────────────
1. Elementor → Templates → Import Templates
2. فایل elementor-import-delsa-home.json
3. صفحه اصلی → Edit with Elementor
4. محتوای قبلی section را پاک کنید؛ template import شده را Insert کنید
   (یا ۴ ویجت HTML پشت سر هم بگذارید)
5. Update
6. LiteSpeed → Purge All
7. Hard refresh (Ctrl+Shift+R)

گزینه B — Paste دستی
────────────────────
۴ ویجت HTML پشت سر هم:
  part1-of-4.html → ویجت #1
  part2-of-4.html → ویجت #2
  part3-of-4.html → ویجت #3
  part4-of-4.html → ویجت #4

بعد از Publish — تست سریع
─────────────────────────
View Page Source → جستجو: "<style"
باید تعداد <style و </style> برابر باشد (۴ و ۴).

JS (منو، مودال، AOS)
────────────────────
Elementor script داخل HTML widget را روی frontend حذف می‌کند.
از wp-admin:
  Elementor → Custom Code → Add New
  Location: Footer
  محتوا: delsa-homepage.js (داخل <script>...</script>)
  Display Conditions: فقط صفحه اصلی

LiteSpeed JS excludes: jquery, gravityforms, persian-datepicker
(جزئیات در LITESPEED-WP-FIX.md)

بازسازی partها از elementor-paste.html:
  python3 scripts/regenerate-elementor-parts.py
