/**
 * WPCode Snippet — رفع خطای «wp is not defined»
 *
 * نوع: HTML Snippet
 * محل اجرا: Site Wide Header
 * اولویت: ۱ (یا هر عددی — مهم «Header» است)
 *
 * چرا لازم است؟
 * LiteSpeed اسکریpt‌های ترجمه (Contact Form 7 / WordPress) را با defer
 * قبل از wp-i18n اجرا می‌کند. ویجت HTML المنتور <script> را حذف می‌کند.
 *
 * بعد از فعال‌سازی: LiteSpeed → Purge All → Hard refresh
 */
?>
<script id="delsa-wp-i18n-shim">
window.wp=window.wp||{};window.wp.i18n=window.wp.i18n||{setLocaleData:function(){},__:function(t){return t},_x:function(t){return t},_n:function(s,p,n){return Number(n)===1?s:p},sprintf:function(f){var a=[].slice.call(arguments,1),i=0;return String(f).replace(/%s/g,function(){return a[i++]!=null?a[i-1]:""})}};
</script>
