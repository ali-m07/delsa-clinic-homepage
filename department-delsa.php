<?php
/**
 * WPCode Snippet — صفحات دپارتمان (یک استایل برای همه)
 * نوع: PHP Snippet | محل: Run Everywhere
 * نسخه: 3.0.0
 *
 * مهم: فقط یک snippet دپارتمان فعال باشد.
 * CSS قدیمی page-id-761 را خاموش/حذف کنید.
 */

if (!defined('ABSPATH')) {
  exit;
}

final class Delsa_Department_Profiles {
  const VERSION = '3.0.2';
  const TRANSIENT = 'delsa_department_profile_ids_v8';
  const SLUG_PREFIX = 'دپارتمان-';
  /** IDs واقعی delsaclinic.com */
  const HARD_IDS = [676, 752, 755, 3225, 759, 761];

  public static function init() {
    add_action('save_post_page', [__CLASS__, 'bust_cache']);
    add_filter('body_class', [__CLASS__, 'body_class']);
    add_action('wp_enqueue_scripts', [__CLASS__, 'assets'], 100);
    add_action('wp_head', [__CLASS__, 'font_head'], 1);
    add_filter('the_content', [__CLASS__, 'wrap_content'], 9999);
    add_action('template_redirect', [__CLASS__, 'buffer_start'], 0);
  }

  public static function bust_cache() {
    delete_transient(self::TRANSIENT);
    delete_option('delsa_department_profile_ids');
  }

  public static function page_id() {
    $id = (int) get_queried_object_id();
    return $id ?: (int) get_the_ID();
  }

  public static function is_profile() {
    if (!is_singular('page')) {
      return false;
    }
    $id = self::page_id();
    if (!$id) {
      return false;
    }
    if (in_array($id, self::HARD_IDS, true)) {
      return true;
    }
    $slug = (string) get_post_field('post_name', $id);
    return $slug !== '' && strpos($slug, self::SLUG_PREFIX) === 0;
  }

  public static function body_class($classes) {
    if (self::is_profile()) {
      $classes[] = 'delsa-department-profile';
    }
    return $classes;
  }

  public static function department_nav_label($title) {
    $full = trim((string) $title);
    $short = preg_replace('/^دپارتمان\s+/u', '', $full);
    $short = preg_replace('/\s+دپارتمان$/u', '', $short);
    return $short !== '' ? $short : $full;
  }

  public static function profile_ids() {
    static $memo = null;
    if (is_array($memo)) {
      return $memo;
    }
    $cached = get_transient(self::TRANSIENT);
    if (is_array($cached) && $cached) {
      $memo = $cached;
      return $memo;
    }

    $ids = self::HARD_IDS;
    $slugs = [
      'دپارتمان-روانپزشکی',
      'دپارتمان-روان-درمانی',
      'دپارتمان-زوج-و-خانواده',
      'دپارتمان-کودک-و-نوجوان',
      'دپارتمان-ترک-اعتیاد',
      'دپارتمان-مشاوره-شغلی',
    ];
    foreach ($slugs as $slug) {
      $p = get_page_by_path($slug);
      if ($p) {
        $ids[] = (int) $p->ID;
      }
    }
    foreach (get_pages(['post_status' => 'publish', 'number' => 80]) as $page) {
      if (strpos((string) $page->post_name, self::SLUG_PREFIX) === 0) {
        $ids[] = (int) $page->ID;
      }
    }

    $out = [];
    foreach (array_unique(array_map('intval', $ids)) as $id) {
      if ($id && get_post_status($id) === 'publish') {
        $out[] = $id;
      }
    }
    set_transient(self::TRANSIENT, $out, WEEK_IN_SECONDS);
    $memo = $out;
    return $memo;
  }

  public static function book_url() {
    return home_url('/%d9%81%d8%b1%d9%85-%d9%86%d9%88%d8%a8%d8%aa-%d8%af%d9%87%db%8c/');
  }

  public static function hero_html() {
    $id = self::page_id();
    $name = $id ? get_the_title($id) : wp_get_document_title();
    $title = self::department_nav_label($name);
    $book = self::book_url();

    return '<div class="delsa-dp" dir="rtl" data-dp-version="' . esc_attr(self::VERSION) . '">'
      . '<div class="delsa-dp__hero">'
      . '<div class="delsa-dp__hero-main">'
      . '<nav class="delsa-dp__crumb" aria-label="مسیر">'
      . '<a href="' . esc_url(home_url('/')) . '">خانه</a><span>/</span>'
      . '<span>دپارتمان‌ها</span><span>/</span>'
      . '<span>' . esc_html($title) . '</span>'
      . '</nav>'
      . '<p class="delsa-dp__label">دپارتمان تخصصی</p>'
      . '<h1 class="delsa-dp__title">' . esc_html($title) . '</h1>'
      . '<p class="delsa-dp__lead">مسیر تخصصی این دپارتمان در کلینیک دلسا، با رویکرد علمی و فضای امن.</p>'
      . '</div>'
      . '<div class="delsa-dp__hero-actions">'
      . '<a class="delsa-dp__hero-btn" href="' . esc_url($book) . '">درخواست وقت ملاقات</a>'
      . '<a class="delsa-dp__hero-btn delsa-dp__hero-btn--ghost" href="' . esc_url(home_url('/مشاوران/')) . '">مشاوران</a>'
      . '</div>'
      . '</div>'
      . '</div>';
  }

  public static function departments_nav_html() {
    $current = self::page_id();
    $items = [];
    foreach (self::profile_ids() as $id) {
      $label = self::department_nav_label(get_the_title($id));
      if ($label === '') {
        continue;
      }
      $items[] = [
        'id' => (int) $id,
        'label' => $label,
        'url' => get_permalink($id),
      ];
    }
    if (count($items) < 2) {
      return '';
    }
    $html = '<nav class="delsa-dp__nav" aria-label="دپارتمان‌های کلینیک">'
      . '<p class="delsa-dp__nav-label">دپارتمان‌های کلینیک</p>'
      . '<div class="delsa-dp__nav-list">';
    foreach ($items as $item) {
      $active = $item['id'] === $current ? ' is-active' : '';
      $html .= '<a class="delsa-dp__nav-item' . $active . '" href="' . esc_url($item['url']) . '"'
        . ($item['id'] === $current ? ' aria-current="page"' : '')
        . '>' . esc_html($item['label']) . '</a>';
    }
    return $html . '</div></nav>';
  }

  public static function font_head() {
    if (!self::is_profile()) {
      return;
    }
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
    echo '<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">' . "\n";
  }

  public static function assets() {
    if (!self::is_profile()) {
      return;
    }
    wp_enqueue_style(
      'delsa-vazirmatn',
      'https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap',
      [],
      null
    );
    wp_register_style('delsa-department-profile', false, ['delsa-vazirmatn'], self::VERSION);
    wp_enqueue_style('delsa-department-profile');
    wp_add_inline_style('delsa-department-profile', self::css());

    wp_register_script('delsa-department-profile', false, [], self::VERSION, true);
    wp_enqueue_script('delsa-department-profile');
    wp_add_inline_script('delsa-department-profile', self::js());
  }

  public static function buffer_start() {
    if (!self::is_profile()) {
      return;
    }
    ob_start([__CLASS__, 'buffer_end']);
  }

  public static function buffer_end($html) {
    if (!is_string($html) || $html === '' || strpos($html, 'delsa-dp__hero') !== false) {
      return $html;
    }
    $hero = self::hero_html();
    foreach ([
      '/(<div[^>]*\bclass="[^"]*\bentry-content\b[^"]*"[^>]*>)/i',
      "/(<div[^>]*\\bclass='[^']*\\bentry-content\\b[^']*'[^>]*>)/i",
      '/(<div[^>]*\bclass="[^"]*\bwpb-content-wrapper\b[^"]*"[^>]*>)/i',
    ] as $pat) {
      $out = preg_replace($pat, '$1' . $hero, $html, 1, $count);
      if (!empty($count)) {
        return $out;
      }
    }
    return $html;
  }

  public static function wrap_content($content) {
    if (!self::is_profile()) {
      return $content;
    }
    $qid = (int) get_queried_object_id();
    $tid = (int) get_the_ID();
    if ($qid && $tid && $qid !== $tid) {
      return $content;
    }
    if (strpos($content, 'delsa-dp__hero') !== false || strpos($content, 'data-dp-version=') !== false) {
      return $content;
    }

    static $done = [];
    $key = $qid ?: $tid;
    if ($key && isset($done[$key])) {
      return $content;
    }
    if ($key) {
      $done[$key] = true;
    }

    $book = self::book_url();
    $top = self::hero_html() . '<div class="delsa-dp__shell"><div class="delsa-dp__card">';
    $bottom = '</div>'
      . self::departments_nav_html()
      . '<div class="delsa-dp__cta"><a class="delsa-dp__btn" href="' . esc_url($book) . '">درخواست وقت در این دپارتمان</a></div>'
      . '</div>';

    return $top . $content . $bottom;
  }

  private static function js() {
    $ver = self::VERSION;
    return <<<JS
(function () {
  function ensureHero() {
    if (!document.body.classList.contains("delsa-department-profile")) return;
    if (document.querySelector(".delsa-dp__hero")) return;
    var entry = document.querySelector(".entry-content") || document.querySelector(".wpb-content-wrapper");
    if (!entry) return;
    var h5 = entry.querySelector(".about-content h5");
    var title = (h5 && (h5.textContent || "").trim()) || (document.title || "").split(/[-|–]/)[0].trim();
    title = title.replace(/^دپارتمان\\s+/u, "").trim() || title;
    var box = document.createElement("div");
    box.innerHTML = '<div class="delsa-dp" dir="rtl" data-dp-version="{$ver}-js"><div class="delsa-dp__hero"><div class="delsa-dp__hero-main"><nav class="delsa-dp__crumb" aria-label="مسیر"><a href="/">خانه</a><span>/</span><span>دپارتمان‌ها</span><span>/</span><span></span></nav><p class="delsa-dp__label">دپارتمان تخصصی</p><h1 class="delsa-dp__title"></h1><p class="delsa-dp__lead">مسیر تخصصی این دپارتمان در کلینیک دلسا، با رویکرد علمی و فضای امن.</p></div><div class="delsa-dp__hero-actions"><a class="delsa-dp__hero-btn" href="/فرم-نوبت-دهی/">درخواست وقت ملاقات</a><a class="delsa-dp__hero-btn delsa-dp__hero-btn--ghost" href="/مشاوران/">مشاوران</a></div></div></div>';
    var node = box.firstElementChild;
    node.querySelector(".delsa-dp__title").textContent = title;
    var spans = node.querySelectorAll(".delsa-dp__crumb span");
    if (spans.length) spans[spans.length - 1].textContent = title;
    entry.insertBefore(node, entry.firstChild);
  }
  document.addEventListener("DOMContentLoaded", ensureHero);
  window.addEventListener("load", ensureHero);
})();
JS;
  }

  private static function css() {
    return <<<'CSS'
@import url("https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap");

/* ========== پایه یکسان برای همه دپارتمان‌ها ========== */
body.delsa-department-profile{
  --dp-ink:#0F2740;
  --dp-teal:#1FA8A0;
  --dp-teal-deep:#178F88;
  --dp-sand:#F3F6F8;
  --dp-wrap:980px;
  --dp-font:"Vazirmatn", Tahoma, sans-serif;
  font-family:var(--dp-font) !important;
}
body.delsa-department-profile,
body.delsa-department-profile .entry-content,
body.delsa-department-profile .wpb_wrapper,
body.delsa-department-profile .about-section,
body.delsa-department-profile .about-content,
body.delsa-department-profile .delsa-dp,
body.delsa-department-profile .delsa-dp *{
  font-family:var(--dp-font) !important;
}

/* بنر تم */
body.delsa-department-profile .page-banner,
body.delsa-department-profile .page-title-block,
body.delsa-department-profile .breadcrumb_s,
body.delsa-department-profile .page-breadcrumb,
body.delsa-department-profile .main-title-section-wrapper,
body.delsa-department-profile h2.hide,
body.delsa-department-profile .entry-content > h2.no-padding.no-margin.hide{
  display:none !important;
  height:0 !important;
  margin:0 !important;
  padding:0 !important;
  overflow:hidden !important;
}

/* عرض یکسان + وسط‌چین — بدون float راستِ Bootstrap */
body.delsa-department-profile #main,
body.delsa-department-profile .site-main{
  padding:.35rem 0 1.4rem !important;
  margin:0 !important;
  background:
    radial-gradient(ellipse 70% 40% at 100% 0%, rgba(31,168,160,.12), transparent 55%),
    var(--dp-sand) !important;
}
body.delsa-department-profile #main > .container,
body.delsa-department-profile #main > .container-fluid,
body.delsa-department-profile .site-main > .container,
body.delsa-department-profile .site-main > .container-fluid,
body.delsa-department-profile .content-area,
body.delsa-department-profile .content-area.col-md-12,
body.delsa-department-profile .content-area[class*="col-"],
body.delsa-department-profile .entry-content,
body.delsa-department-profile .delsa-dp,
body.delsa-department-profile .delsa-dp__shell{
  float:none !important;
  clear:both !important;
  position:relative !important;
  left:auto !important;
  right:auto !important;
  max-width:var(--dp-wrap) !important;
  width:100% !important;
  margin-left:auto !important;
  margin-right:auto !important;
  padding-left:1.15rem !important;
  padding-right:1.15rem !important;
  box-sizing:border-box !important;
}
body.delsa-department-profile #main > .container,
body.delsa-department-profile #main > .container-fluid,
body.delsa-department-profile .site-main > .container,
body.delsa-department-profile .site-main > .container-fluid{
  padding:0 1.15rem !important;
}
body.delsa-department-profile .entry-content,
body.delsa-department-profile .delsa-dp,
body.delsa-department-profile .delsa-dp__shell{
  padding-left:0 !important;
  padding-right:0 !important;
}
/* اگر تم دور محتوا .row گذاشته، float ستون‌ها را خنثی کن */
body.delsa-department-profile .site-main > .container > .row,
body.delsa-department-profile .site-main > .container-fluid > .row,
body.delsa-department-profile #main > .container > .row,
body.delsa-department-profile #main > .container-fluid > .row{
  display:block !important;
  width:100% !important;
  margin:0 auto !important;
  float:none !important;
}
body.delsa-department-profile .site-main > .container > .row::before,
body.delsa-department-profile .site-main > .container > .row::after,
body.delsa-department-profile .site-main > .container-fluid > .row::before,
body.delsa-department-profile .site-main > .container-fluid > .row::after,
body.delsa-department-profile #main > .container > .row::before,
body.delsa-department-profile #main > .container > .row::after,
body.delsa-department-profile #main > .container-fluid > .row::before,
body.delsa-department-profile #main > .container-fluid > .row::after{
  display:none !important;
  content:none !important;
}

/* ----- هیرو ----- */
.delsa-dp__hero{
  display:flex !important;
  flex-wrap:wrap !important;
  align-items:center !important;
  justify-content:space-between !important;
  gap:.85rem 1.15rem !important;
  margin:0 0 1rem !important;
  padding:1.05rem 1.15rem !important;
  border-radius:18px !important;
  background:linear-gradient(120deg,#0A1C2E 0%,#0F2740 100%) !important;
  box-shadow:0 12px 32px rgba(15,39,64,.18) !important;
  color:#fff !important;
}
.delsa-dp__hero-main{
  flex:1 1 14rem !important;
  min-width:0 !important;
  max-width:36rem !important;
}
.delsa-dp__crumb{
  display:flex !important;
  flex-wrap:wrap !important;
  gap:.35rem !important;
  margin:0 0 .45rem !important;
  font-size:12px !important;
  font-weight:500 !important;
}
.delsa-dp__label{
  display:inline-flex !important;
  margin:0 0 .5rem !important;
  padding:.4rem 1rem !important;
  font-size:12px !important;
  font-weight:700 !important;
  border-radius:999px !important;
  background:var(--dp-teal) !important;
}
.delsa-dp__title{
  margin:0 !important;
  font-size:clamp(1.35rem,1.15rem + .7vw,1.8rem) !important;
  font-weight:800 !important;
  line-height:1.35 !important;
}
.delsa-dp__lead{
  margin:.45rem 0 0 !important;
  max-width:34rem !important;
  font-size:14px !important;
  line-height:1.75 !important;
  text-align:right !important;
}
.delsa-dp__hero-actions{
  display:flex !important;
  flex-wrap:wrap !important;
  gap:.5rem !important;
}
.delsa-dp__hero-btn{
  display:inline-flex !important;
  align-items:center !important;
  justify-content:center !important;
  padding:.6rem 1.1rem !important;
  font-size:13px !important;
  font-weight:700 !important;
  text-decoration:none !important;
  border-radius:999px !important;
  background:var(--dp-teal) !important;
  border:0 !important;
}
.delsa-dp__hero-btn--ghost{
  background:transparent !important;
  border:1.5px solid rgba(255,255,255,.45) !important;
}

/* رنگ هیرو — آخر و با !important تا قانون p تیره نشکند */
body.delsa-department-profile .delsa-dp__hero,
body.delsa-department-profile .delsa-dp__hero *:not(.delsa-dp__label):not(.delsa-dp__hero-btn){
  color:#fff !important;
  -webkit-text-fill-color:#fff !important;
  opacity:1 !important;
}
body.delsa-department-profile .delsa-dp__hero .delsa-dp__lead,
body.delsa-department-profile .delsa-dp__hero .delsa-dp__crumb,
body.delsa-department-profile .delsa-dp__hero .delsa-dp__crumb span,
body.delsa-department-profile .delsa-dp__hero .delsa-dp__crumb a{
  color:rgba(255,255,255,.92) !important;
  -webkit-text-fill-color:rgba(255,255,255,.92) !important;
}
body.delsa-department-profile .delsa-dp__hero .delsa-dp__title{
  color:#fff !important;
  -webkit-text-fill-color:#fff !important;
}
body.delsa-department-profile .delsa-dp__hero .delsa-dp__label{
  color:#0F2740 !important;
  -webkit-text-fill-color:#0F2740 !important;
}
body.delsa-department-profile .delsa-dp__hero .delsa-dp__hero-btn{
  color:#0F2740 !important;
  -webkit-text-fill-color:#0F2740 !important;
}
body.delsa-department-profile .delsa-dp__hero .delsa-dp__hero-btn--ghost{
  color:#fff !important;
  -webkit-text-fill-color:#fff !important;
}

/* ----- کارت / about ----- */
.delsa-dp__card{
  background:#fff !important;
  border:1px solid #e4ebf1 !important;
  border-radius:18px !important;
  padding:1rem 1.1rem 1.15rem !important;
  box-shadow:0 10px 28px rgba(15,39,64,.06) !important;
  overflow:hidden !important;
}
body.delsa-department-profile .about-section{
  margin:0 0 .85rem !important;
  padding:1rem 1.05rem !important;
  background:linear-gradient(145deg,#f8fbfc 0%,#fff 55%) !important;
  border:1px solid #e4ebf1 !important;
  border-radius:16px !important;
  box-shadow:0 8px 22px rgba(15,39,64,.05) !important;
  width:100% !important;
  max-width:100% !important;
}
body.delsa-department-profile .about-content{
  margin:0 !important;
  padding:0 !important;
  background:transparent !important;
  border:0 !important;
  box-shadow:none !important;
}
body.delsa-department-profile .about-section .row::before,
body.delsa-department-profile .about-section .row::after{
  display:none !important;
  content:none !important;
}
body.delsa-department-profile .about-section .container{
  width:100% !important;
  max-width:100% !important;
  margin:0 !important;
  padding:0 !important;
}
body.delsa-department-profile .about-section .row{
  display:flex !important;
  flex-wrap:wrap !important;
  align-items:center !important;
  gap:1.15rem 1.35rem !important;
  margin:0 !important;
  width:100% !important;
}
body.delsa-department-profile .about-section .row > [class*="col-"]{
  float:none !important;
  width:auto !important;
  max-width:none !important;
  padding:0 !important;
  flex:1 1 240px !important;
}
body.delsa-department-profile .about-section .row > .about-img,
body.delsa-department-profile .about-section .row > .col-md-4{
  flex:0.9 1 200px !important;
  max-width:360px !important;
}
body.delsa-department-profile .about-content h5{
  display:none !important;
}
body.delsa-department-profile:not(:has(.delsa-dp__hero)) .about-content h5{
  display:block !important;
  margin:0 0 .75rem !important;
  padding:0 0 .4rem !important;
  font-size:clamp(1.2rem,1.05rem + .6vw,1.6rem) !important;
  font-weight:800 !important;
  color:var(--dp-ink) !important;
  border-bottom:3px solid var(--dp-teal) !important;
}
body.delsa-department-profile .about-img img{
  display:block !important;
  width:100% !important;
  height:auto !important;
  border-radius:16px !important;
  box-shadow:0 12px 28px rgba(15,39,64,.12) !important;
}

/* متن محتوا — تیره و خوانا */
body.delsa-department-profile .entry-content p,
body.delsa-department-profile .entry-content li,
body.delsa-department-profile .entry-content h2,
body.delsa-department-profile .entry-content h3,
body.delsa-department-profile .wpb_wrapper p,
body.delsa-department-profile .wpb_wrapper li,
body.delsa-department-profile .wpb_wrapper h2,
body.delsa-department-profile .wpb_wrapper h3,
body.delsa-department-profile .vc_column_text,
body.delsa-department-profile .vc_column_text p,
body.delsa-department-profile .vc_column_text li,
body.delsa-department-profile .about-content p{
  color:var(--dp-ink) !important;
  -webkit-text-fill-color:var(--dp-ink) !important;
  opacity:1 !important;
}
body.delsa-department-profile .entry-content h2,
body.delsa-department-profile .wpb_wrapper h2,
body.delsa-department-profile .vc_column_text h2{
  margin:1.25rem 0 .65rem !important;
  padding:0 0 .4rem !important;
  font-size:1.15rem !important;
  font-weight:800 !important;
  border-bottom:3px solid #d8f0ed !important;
  text-align:right !important;
}
body.delsa-department-profile .entry-content p,
body.delsa-department-profile .wpb_wrapper p,
body.delsa-department-profile .about-content p{
  font-size:15px !important;
  line-height:1.95 !important;
  text-align:justify !important;
  margin:0 0 .85rem !important;
}
body.delsa-department-profile .entry-content ul,
body.delsa-department-profile .wpb_wrapper ul{
  margin:0 0 1rem !important;
  padding-right:1.15rem !important;
  list-style:disc !important;
}
body.delsa-department-profile .entry-content li,
body.delsa-department-profile .wpb_wrapper li{
  margin:0 0 .4rem !important;
  font-size:15px !important;
  line-height:1.8 !important;
}
body.delsa-department-profile .entry-content blockquote,
body.delsa-department-profile .wpb_wrapper blockquote{
  border:0 !important;
  border-right:4px solid var(--dp-teal) !important;
  padding:1rem 1.15rem !important;
  margin:1.1rem 0 !important;
  background:linear-gradient(90deg,#eefaf9 0%,#f7fbfc 100%) !important;
  border-radius:0 14px 14px 0 !important;
}

/* ناوبری دپارتمان‌ها + CTA */
.delsa-dp__nav{
  margin:1.15rem 0 0 !important;
  padding:1rem !important;
  background:#fff !important;
  border:1px solid #e4ebf1 !important;
  border-radius:16px !important;
}
.delsa-dp__nav-label{
  margin:0 0 .65rem !important;
  font-size:13px !important;
  font-weight:800 !important;
  color:var(--dp-ink) !important;
}
.delsa-dp__nav-list{
  display:flex !important;
  flex-wrap:wrap !important;
  gap:.45rem !important;
}
.delsa-dp__nav-item{
  display:inline-flex !important;
  padding:.45rem .8rem !important;
  font-size:12px !important;
  font-weight:600 !important;
  text-decoration:none !important;
  color:var(--dp-ink) !important;
  background:#f3f7f9 !important;
  border:1px solid #e4ebf1 !important;
  border-radius:999px !important;
}
.delsa-dp__nav-item.is-active,
.delsa-dp__nav-item:hover{
  background:var(--dp-teal) !important;
  color:#0F2740 !important;
  border-color:var(--dp-teal) !important;
}
.delsa-dp__cta{margin:1rem 0 0 !important}
.delsa-dp__btn{
  display:inline-flex !important;
  align-items:center !important;
  justify-content:center !important;
  padding:.72rem 1.25rem !important;
  font-size:13px !important;
  font-weight:700 !important;
  text-decoration:none !important;
  border-radius:999px !important;
  color:#0F2740 !important;
  background:var(--dp-teal) !important;
}

/* مشاوران / اسلایدر */
body.delsa-department-profile .elementor-1649,
body.delsa-department-profile .swiper-wrapper,
body.delsa-department-profile .team-carousel{
  display:flex !important;
  flex-wrap:wrap !important;
  justify-content:center !important;
  gap:1rem !important;
}

@media (max-width:781px){
  body.delsa-department-profile .about-section .row{flex-direction:column !important}
  body.delsa-department-profile .about-section .row > [class*="col-"],
  body.delsa-department-profile .about-section .row > .about-img{
    flex:1 1 auto !important;
    max-width:100% !important;
    width:100% !important;
  }
  .delsa-dp__hero{padding:1rem !important}
}
CSS;
  }
}

Delsa_Department_Profiles::init();
