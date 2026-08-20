فقط wp-admin — بدون FTP
══════════════════════════

۵ ویجت HTML پشت سر هم در Elementor (بدون هدر/فوتر):
  part1-of-5.html → ویجت #1  CSS پایه + فونت
  part2-of-5.html → ویجت #2  CSS ادامه
  part3-of-5.html → ویجت #3  کل محتوای صفحه (هیرو تا FAQ)
  part4-of-5.html → ویجت #4  CSS نهایی / فول‌بلید
  part5-of-5.html → ویجت #5  اسکریپت

JS (مهم)
────────
Elementor اغلب <script> داخل HTML widget را حذف می‌کند.
اگر انیمیشن کار نکرد:
  Elementor → Custom Code → Footer
  کل فایل delsa-homepage.js را داخل <script>...</script> بگذارید
  Display Conditions: فقط صفحه اصلی

بعد از Publish: LiteSpeed Purge + Hard refresh
View Source: تعداد <style و </style> باید برابر باشد.

منبع: ../elementor-paste.html
