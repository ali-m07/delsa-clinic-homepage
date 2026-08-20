<?php
/**
 * کلینیک دلسا — یک‌بار در functions.php تم فعال (Doctor) اضافه کنید.
 *
 * مسیر: ظاهر → ویرایشگر پرونده پوسته → functions.php
 * یا WPCode → PHP Snippet → Run Everywhere
 *
 * ۱) shortcode [delsa_homepage] — کل HTML از فایل (بدون سقف ۴۵KB المنتور)
 * ۲) shortcode گرویتی‌فرم داخل ویجت HTML
 * ۳) shim برای wp.i18n
 * ۴) JS صفحه از delsa-homepage.js (المنتور <script> داخل HTML را حذف می‌کند)
 */

if (!defined('DELSA_HOMEPAGE_VERSION')) {
    define('DELSA_HOMEPAGE_VERSION', '5.4.1');
}

function delsa_homepage_asset_dir() {
    if (defined('DELSA_HOMEPAGE_DIR')) {
        return rtrim(DELSA_HOMEPAGE_DIR, '/');
    }
    $candidates = array(
        WP_CONTENT_DIR . '/delsa',
        get_stylesheet_directory() . '/delsa-homepage',
        get_stylesheet_directory(),
    );
    foreach ($candidates as $dir) {
        if (is_readable($dir . '/elementor-paste.html')) {
            return $dir;
        }
    }
    return WP_CONTENT_DIR . '/delsa';
}

function delsa_homepage_html_path() {
    $path = delsa_homepage_asset_dir() . '/elementor-paste.html';
    return is_readable($path) ? $path : null;
}

function delsa_homepage_js_url() {
    $dir = delsa_homepage_asset_dir();
    $js_path = $dir . '/delsa-homepage.js';
    if (!is_readable($js_path)) {
        return null;
    }
    if (strpos($dir, WP_CONTENT_DIR) === 0) {
        return content_url(substr($dir, strlen(WP_CONTENT_DIR)) . '/delsa-homepage.js');
    }
    if (strpos($dir, get_stylesheet_directory()) === 0) {
        return get_stylesheet_directory_uri() . substr($dir, strlen(get_stylesheet_directory())) . '/delsa-homepage.js';
    }
    return content_url('delsa/delsa-homepage.js');
}

function delsa_homepage_register_assets() {
    $js_url = delsa_homepage_js_url();
    if (!$js_url) {
        return;
    }
    wp_register_script(
        'delsa-homepage',
        $js_url,
        array(),
        DELSA_HOMEPAGE_VERSION,
        true
    );
}

add_action('wp_enqueue_scripts', 'delsa_homepage_register_assets', 5);

function delsa_homepage_shortcode() {
    $html_path = delsa_homepage_html_path();
    if (!$html_path) {
        if (current_user_can('edit_pages')) {
            return '<p style="padding:1rem;background:#fee;color:#900;">فایل elementor-paste.html پیدا نشد. آن را در wp-content/delsa/ آپلود کنید.</p>';
        }
        return '';
    }

    wp_enqueue_script('delsa-homepage');

    $html = file_get_contents($html_path);
    if ($html === false) {
        return '';
    }

    return do_shortcode($html);
}

add_shortcode('delsa_homepage', 'delsa_homepage_shortcode');

add_filter('elementor/widget/render_content', function ($content, $widget) {
    if ($widget->get_name() === 'html') {
        return do_shortcode($content);
    }
    return $content;
}, 10, 2);

add_action('wp_head', function () {
    echo '<script id="delsa-wp-i18n-shim">(function(w){w.wp=w.wp||{};if(!w.wp.i18n||typeof w.wp.i18n.setLocaleData!=="function"){w.wp.i18n={setLocaleData:function(){},__:function(t){return t},_x:function(t){return t},_n:function(s,p,n){return Number(n)===1?s:p},sprintf:function(f){var a=Array.prototype.slice.call(arguments,1),i=0;return String(f).replace(/%s/g,function(){return a[i++]!=null?a[i-1]:"";})}};}})(window);</script>' . "\n";
}, 0);

add_filter('litespeed_optimize_js_excludes', function ($excludes) {
    if (!is_array($excludes)) {
        $excludes = array();
    }
    $items = array(
        'jquery',
        'jquery-core',
        'jquery.min.js',
        'wp-i18n',
        'wp-hooks',
        'contact-form-7',
        'gravityforms',
        'gform',
        'persian-gravity-forms',
        'persian-datepicker',
        'gf-persian-datepicker',
        'delsa-homepage',
    );
    return array_merge($excludes, $items);
});
