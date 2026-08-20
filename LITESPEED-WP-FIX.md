# رفع خطاهای کنسول + فرم نوبت — فقط wp-admin

صفحه جدید درست شد. این دو مرحله را در Elementor/LiteSpeed انجام بده.

---

## مرحله ۱ — Elementor Custom Code (۲ snippet)

### A) Head — رفع `wp is not defined`

**Elementor → Custom Code → Add New**

| فیلد | مقدار |
|------|--------|
| Name | Delsa wp shim |
| Location | **Head** |
| Display | Entire Site (یا فقط صفحه اصلی) |

محتوا: کل فایل **`elementor-custom-code-head.html`**

### B) Footer — منو + مودال + فرم نوبت

**Elementor → Custom Code → Add New**

| فیلد | مقدار |
|------|--------|
| Name | Delsa homepage JS |
| Location | **Footer** |
| Display | **Entire Site** |

محتوا: کل فایل **`elementor-custom-code-footer.html`**

این قطعه باید روی کل سایت فعال باشد، چون منوی موبایل هدر در صفحات داخلی هم
به همین اسکریپت نیاز دارد. عناصر مخصوص هوم فقط وقتی در صفحه وجود داشته باشند
فعال می‌شوند.

> Elementor `<script>` داخل HTML widget را حذف می‌کند — JS فقط از Custom Code Footer.

---

## مرحله ۲ — LiteSpeed JS Excludes

**LiteSpeed Cache → Page Optimization → JS Settings**

در **JS Deferred Excludes** (هر خط یک مورد):

```
jquery
jquery-core
jquery.min.js
jquery-migrate
wp-i18n
wp-hooks
contact-form-7
gravityforms
gform
persian-gravity-forms
persian-datepicker
gf-persian-datepicker
```

اگر **JS Delay** روشن است → همان لیست را در **JS Delay Excludes** هم بگذار.

**Save → Toolbox → Purge All → Hard refresh**

---

## مرحله ۳ — فرم نوبت در مودال

در ویجت HTML (part3 یا مودال)، shortcode باید این باشد:

```
[gravityform id="1" title="false" description="false" ajax="false"]
```

`ajax="false"` → datepicker فارسی در مودال درست لود می‌شود.

اگر `[gravityform ...]` به‌صورت متن ساده دیده می‌شود → **Elementor Custom Code → PHP**:

```php
add_filter('elementor/widget/render_content', function ($content, $widget) {
    if ($widget->get_name() === 'html') {
        return do_shortcode($content);
    }
    return $content;
}, 10, 2);
```

(Location: Functions PHP یا Snippet PHP — یک بار کافی است)

---

## چیزهایی که **حذف** کن (اضافه‌های قبلی)

| چی بود | چرا حذف |
|--------|---------|
| **WPCode snippet «Delsa wp.i18n shim»** (اگر داری) | با Elementor Custom Code Head جایگزین شد — **دو تا با هم نگذار** |
| **WPCode «footer fix CSS»** (`wpcode-footer-fix.php`) | فوتر الان در HTML درست است — snippet قدیمی تداخل می‌کند |
| **Tailwind CDN** در Header (اگر گذاشتی) | دیگر لازم نیست — CSS همه inline است |
| **`<script>` داخل HTML widget** | Elementor حذفش می‌کند؛ بی‌فایده است |
| **صفحه/ویجت قدیمی ID 2824** | ناقص است — فقط صفحه جدید را نگه دار |
| **JS Delay** (اگر بعد از excludes هنوز خطا داری) | موقت Off کن برای تست |

**نگه دار:**
- ۴ ویجت HTML صفحه جدید (part1–4)
- Custom Code Head + Footer
- LiteSpeed excludes بالا

---

## خطاهایی که بی‌ضررند (نادیده بگیر)

| خطا | توضیح |
|-----|--------|
| `JQMIGRATE: Migrate is installed` | عادی — jQuery |
| `Google Maps NoApiKeys` | از تم Doctor — API Key در تنظیمات تم یا نادیده |
| `Google Maps loading=async` | فقط performance warning |

---

## تست فرم نوبت

1. «درخواست وقت» → مودال باز شود
2. فیلد تاریخ → پیکر فارسی باز شود
3. استان قبل شهر باشد
4. بعد از ارسال → پیام تأیید → مودال بسته شود

اگر part3 را دوباره paste می‌کنی: `python3 scripts/regenerate-elementor-parts.py`
