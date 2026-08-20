<?php
/**
 * WPCode Snippet — استایل وبلاگ (آرشیو /blog/ و تک‌نوشته‌ها)
 * نوع: PHP Snippet
 * محل: Run Everywhere
 */

if (!defined('ABSPATH')) {
  exit;
}

final class Delsa_Blog_Styling {
  const VERSION = '1.6.3';
  const BLOG_PAGE_ID = 21;

  private static $grid_open = false;
  private static $single_wrapped = [];

  public static function init() {
    add_action('template_redirect', [__CLASS__, 'maybe_buffer_single'], 0);
    add_filter('body_class', [__CLASS__, 'body_class']);
    add_action('wp_enqueue_scripts', [__CLASS__, 'assets'], 40);
    add_action('wp', [__CLASS__, 'register_loop_hooks']);
    add_filter('the_content', [__CLASS__, 'wrap_blog_page_content'], 15);
    add_filter('the_content', [__CLASS__, 'enhance_blog_listing_cards'], 25);
    add_filter('the_content', [__CLASS__, 'wrap_single_content'], 20);
    add_filter('post_class', [__CLASS__, 'post_class'], 10, 3);
    add_filter('get_the_excerpt', [__CLASS__, 'filter_card_excerpt'], 20, 2);
    add_filter('wp_trim_excerpt', [__CLASS__, 'filter_trim_excerpt'], 20, 2);
  }

  public static function maybe_buffer_single() {
    if (!self::is_blog_single() || is_admin()) {
      return;
    }
    ob_start([__CLASS__, 'clean_single_buffer']);
  }

  public static function clean_single_buffer($html) {
    if (!is_string($html) || $html === '') {
      return $html;
    }
    $end = stripos($html, '</html>');
    if ($end === false) {
      return $html;
    }
    return substr($html, 0, $end + 7);
  }

  public static function blog_url() {
    static $url = null;
    if ($url !== null) {
      return $url;
    }
    $posts_page = (int) get_option('page_for_posts');
    if ($posts_page) {
      $url = get_permalink($posts_page);
      return $url;
    }
    $page = get_page_by_path('blog');
    if ($page) {
      $url = get_permalink($page);
      return $url;
    }
    if (get_post_status(895) === 'publish') {
      $url = get_permalink(895);
      return $url;
    }
    $url = home_url('/blog/');
    return $url;
  }

  public static function is_blog_index() {
    if (is_home() && !is_front_page()) {
      return true;
    }
    $posts_page = (int) get_option('page_for_posts');
    return $posts_page && is_page($posts_page);
  }

  /** صفحه /blog/ در دلسا: یک Page با WPBakery و blog-listing داخلش (نه posts page استاندارد) */
  public static function is_blog_listing_page() {
    if (is_page('blog') || (self::BLOG_PAGE_ID > 0 && is_page(self::BLOG_PAGE_ID))) {
      return true;
    }
    $posts_page = (int) get_option('page_for_posts');
    if ($posts_page && (is_page($posts_page) || (is_home() && !is_front_page()))) {
      return true;
    }
    if (!is_singular('page')) {
      return false;
    }
    $slug = (string) get_post_field('post_name', get_queried_object_id());
    return $slug === 'blog';
  }

  private static function has_blog_card_markup($content) {
    if (!is_string($content) || $content === '') {
      return false;
    }
    return strpos($content, 'blog-listing') !== false
      || strpos($content, 'delsa-bl__grid') !== false
      || preg_match('#<article\b[^>]*\bid="post-\d+"#', $content);
  }

  private static function ensure_blog_listing_markup($content) {
    if (!is_string($content) || $content === '') {
      return $content;
    }
    if (strpos($content, 'delsa-bl__grid') !== false && strpos($content, 'blog-listing') === false) {
      $content = preg_replace(
        '#<div class="delsa-bl__grid">#',
        '<div class="delsa-bl__grid blog-listing">',
        $content,
        1
      );
    }
    if (strpos($content, 'blog-listing') !== false && strpos($content, 'delsa-bl__grid') === false) {
      $content = preg_replace(
        '#<div class="blog-listing"([^>]*)>#',
        '<div class="delsa-bl__grid blog-listing"$1>',
        $content,
        1
      );
    }
    return $content;
  }

  private static function is_blog_card_render_context() {
    if (is_admin() || self::is_blog_single()) {
      return false;
    }
    if (!self::is_blog_index() && !self::is_blog_archive() && !self::is_blog_listing_page()) {
      return false;
    }
    return in_the_loop();
  }

  public static function filter_card_excerpt($excerpt, $post) {
    if (!self::is_blog_card_render_context()) {
      return $excerpt;
    }
    $post_id = $post instanceof WP_Post ? (int) $post->ID : (int) $post;
    if (!$post_id) {
      return $excerpt;
    }
    $manual = self::manual_excerpt($post_id);
    return $manual !== '' ? $manual : '';
  }

  public static function filter_trim_excerpt($text, $raw_excerpt) {
    if (!self::is_blog_card_render_context()) {
      return $text;
    }
    $post = get_post();
    if (!$post) {
      return $text;
    }
    $manual = self::manual_excerpt((int) $post->ID);
    return $manual !== '' ? $manual : '';
  }

  public static function is_blog_archive() {
    return is_category() || is_tag() || is_author() || is_date();
  }

  public static function is_blog_single() {
    return is_singular('post');
  }

  public static function is_blog_context() {
    return self::is_blog_index()
      || self::is_blog_listing_page()
      || self::is_blog_archive()
      || self::is_blog_single();
  }

  public static function register_loop_hooks() {
    // صفحه WPBakery با shortcode — نه حلقه پست‌ها
    if (self::is_blog_listing_page() && !is_home()) {
      return;
    }
    if (!self::is_blog_index() && !self::is_blog_archive()) {
      return;
    }
    add_action('loop_start', [__CLASS__, 'loop_start_markup'], 5);
    add_action('loop_end', [__CLASS__, 'loop_end_markup'], 95);
  }

  public static function body_class($classes) {
    if (!self::is_blog_context()) {
      return $classes;
    }
    $classes[] = 'delsa-blog';
    if (self::is_blog_single()) {
      $classes[] = 'delsa-blog-single';
    } else {
      $classes[] = 'delsa-blog-index';
    }
    if (self::is_blog_listing_page()) {
      $classes[] = 'delsa-blog-wpbakery';
    }
    return $classes;
  }

  public static function post_class($classes, $class, $post_id) {
    if (
      (self::is_blog_index() || self::is_blog_listing_page() || self::is_blog_archive())
      && get_post_type($post_id) === 'post'
    ) {
      $classes[] = 'delsa-blog-card';
    }
    return $classes;
  }

  public static function wrap_blog_page_content($content) {
    if (!self::is_blog_listing_page() || !in_the_loop() || !is_main_query()) {
      return $content;
    }

    $content = self::ensure_blog_listing_markup($content);

    if (strpos($content, 'delsa-bl') !== false) {
      return $content;
    }

    if (strpos($content, 'blog-listing') !== false || strpos($content, 'delsa-bl__grid') !== false) {
      $content = self::ensure_blog_listing_markup($content);
    } elseif (self::has_blog_card_markup($content)) {
      $content = '<div class="delsa-bl__grid blog-listing">' . $content . '</div>';
    }

    return '<div class="delsa-bl">' . self::index_hero_markup() . $content . '</div>';
  }

  public static function enhance_blog_listing_cards($content) {
    if ((!self::is_blog_listing_page() && !self::is_blog_index() && !self::is_blog_archive()) || !in_the_loop() || !is_main_query()) {
      return $content;
    }
    if (!self::has_blog_card_markup($content)) {
      return $content;
    }

    $content = self::ensure_blog_listing_markup($content);

    return self::enhance_listing_articles_html($content);
  }

  public static function enhance_listing_articles_html($html) {
    if (!is_string($html) || $html === '') {
      return $html;
    }
    if (!preg_match('#<article\b[^>]*\bid="post-\d+"#', $html)) {
      return $html;
    }

    return preg_replace_callback(
      '#<article\b[^>]*\bid="post-(\d+)"[^>]*>.*?</article>#su',
      [__CLASS__, 'enhance_blog_card_html'],
      $html
    );
  }

  public static function enhance_blog_card_html($matches) {
    $post_id = (int) $matches[1];
    $article = $matches[0];

    $meta_html = self::card_meta_html($post_id);
    $article = self::replace_entry_meta($article, $meta_html);

    $excerpt = self::manual_excerpt($post_id);
    if ($excerpt !== '') {
      // چکیده دستی همیشه جایگزین متن خودکار تم/WPBakery می‌شود
      $article = preg_replace(
        '#<div class="entry-content"[^>]*>.*?</div>#su',
        '<div class="entry-content"><p>' . esc_html($excerpt) . '</p></div>',
        $article,
        1
      );
    } elseif (preg_match('#<div class="entry-content"[^>]*>.*?</div>#su', $article)) {
      // بدون چکیده دستی — متن خودکار تم نشان داده نمی‌شود
      $article = preg_replace(
        '#<div class="entry-content"[^>]*>.*?</div>#su',
        '',
        $article,
        1
      );
    }

    return $article;
  }

  private static function replace_entry_meta($article, $meta_html) {
    $start = strpos($article, '<div class="entry-meta">');
    if ($start === false) {
      return $article;
    }
    $end = strpos($article, '<div class="entry-content">', $start);
    if ($end === false) {
      return $article;
    }
    return substr($article, 0, $start) . $meta_html . substr($article, $end);
  }

  /** فقط خلاصه دستی — بدون تولید خودکار از متن مطلب */
  private static function manual_excerpt($post_id) {
    $post = get_post($post_id);
    if (!$post) {
      return '';
    }
    return trim(wp_strip_all_tags((string) $post->post_excerpt));
  }

  private static function card_meta_html($post_id) {
    $author = get_the_author_meta('display_name', (int) get_post_field('post_author', $post_id));
    $date = get_the_date('', $post_id);
    $user_icon = '<svg class="delsa-bl-card-meta__icon" width="14" height="14" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z"/></svg>';
    $time_icon = '<svg class="delsa-bl-card-meta__icon" width="14" height="14" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2Zm1 11h4v-2h-3V7h-2v6Z"/></svg>';

    return '<div class="entry-meta delsa-bl-card-meta">'
      . '<span class="delsa-bl-card-meta__item delsa-bl-card-meta__item--author">' . $user_icon . '<span>' . esc_html($author) . '</span></span>'
      . '<span class="delsa-bl-card-meta__item delsa-bl-card-meta__item--date">' . $time_icon . '<span>' . esc_html($date) . '</span></span>'
      . '</div>';
  }

  public static function assets() {
    if (!self::is_blog_context()) {
      return;
    }
    wp_register_style('delsa-blog', false, [], self::VERSION);
    wp_enqueue_style('delsa-blog');
    wp_add_inline_style('delsa-blog', self::css());

    wp_register_script('delsa-blog', false, [], self::VERSION, true);
    wp_enqueue_script('delsa-blog');
    wp_add_inline_script('delsa-blog', self::js());
  }

  public static function loop_start_markup($query) {
    if (is_admin() || !$query->is_main_query()) {
      return;
    }
    static $hero_done = false;
    if ($hero_done) {
      return;
    }
    $hero_done = true;
    echo self::index_shell_open();
    self::$grid_open = true;
    ob_start();
  }

  public static function loop_end_markup($query) {
    if (is_admin() || !$query->is_main_query() || !self::$grid_open) {
      return;
    }
    $articles = ob_get_clean();
    if (is_string($articles) && $articles !== '') {
      echo self::enhance_listing_articles_html($articles);
    }
    echo '</div></div>';
    self::$grid_open = false;
  }

  public static function wrap_single_content($content) {
    if (!self::is_blog_single()) {
      return $content;
    }

    $post_id = (int) get_the_ID();
    $queried_id = (int) get_queried_object_id();
    if (!$post_id || $post_id !== $queried_id) {
      return $content;
    }
    if (!in_the_loop() || !is_main_query()) {
      return $content;
    }
    if (!empty(self::$single_wrapped[$post_id])) {
      return $content;
    }
    if (strpos($content, 'delsa-bl') !== false) {
      return $content;
    }
    if (strlen(wp_strip_all_tags($content)) < 120) {
      return $content;
    }

    self::$single_wrapped[$post_id] = true;

    $content = self::strip_empty_list_items($content);

    $blog = self::blog_url();
    $book = home_url('/%d9%81%d8%b1%d9%85-%d9%86%d9%88%d8%a8%d8%aa-%d8%af%d9%87%db%8c/');
    $name = get_the_title();

    $top = '<div class="delsa-bl">'
      . '<div class="delsa-bl__hero">'
      . '<div class="delsa-bl__hero-main">'
      . '<nav class="delsa-bl__crumb" aria-label="مسیر">'
      . '<a href="' . esc_url(home_url('/')) . '">خانه</a><span>/</span>'
      . '<a href="' . esc_url($blog) . '">وبلاگ</a><span>/</span>'
      . '<span>' . esc_html($name) . '</span>'
      . '</nav>'
      . '<p class="delsa-bl__label">مطلب آموزشی</p>'
      . '<h1 class="delsa-bl__title">' . esc_html($name) . '</h1>'
      . self::post_meta_html()
      . '</div>'
      . '<div class="delsa-bl__hero-actions">'
      . '<a class="delsa-bl__hero-btn" href="' . esc_url($book) . '">رزرو وقت</a>'
      . '<a class="delsa-bl__hero-btn delsa-bl__hero-btn--ghost" href="' . esc_url($blog) . '">همه مطالب</a>'
      . '</div>'
      . '</div>'
      . '<div class="delsa-bl__card">';

    $bottom = '</div>'
      . '<div class="delsa-bl__cta">'
      . '<a class="delsa-bl__btn" href="' . esc_url($book) . '">درخواست وقت مشاوره</a>'
      . '<a class="delsa-bl__btn delsa-bl__btn--ghost" href="' . esc_url($blog) . '">بازگشت به وبلاگ</a>'
      . '</div>'
      . '</div>';

    return $top . $content . $bottom;
  }

  private static function index_hero_markup() {
    $title = self::index_title();
    $desc = self::index_desc();
    $book = home_url('/%d9%81%d8%b1%d9%85-%d9%86%d9%88%d8%a8%d8%aa-%d8%af%d9%87%db%8c/');

    $html = '<div class="delsa-bl__hero">'
      . '<div class="delsa-bl__hero-main">'
      . '<nav class="delsa-bl__crumb" aria-label="مسیر">'
      . '<a href="' . esc_url(home_url('/')) . '">خانه</a><span>/</span>'
      . '<span>' . esc_html($title) . '</span>'
      . '</nav>'
      . '<p class="delsa-bl__label">وبلاگ کلینیک دلسا</p>'
      . '<h1 class="delsa-bl__title">' . esc_html($title) . '</h1>';

    if ($desc !== '') {
      $html .= '<p class="delsa-bl__desc">' . esc_html($desc) . '</p>';
    }

    $html .= '</div>'
      . '<div class="delsa-bl__hero-actions">'
      . '<a class="delsa-bl__hero-btn" href="' . esc_url($book) . '">رزرو وقت</a>'
      . '<a class="delsa-bl__hero-btn delsa-bl__hero-btn--ghost" href="' . esc_url(home_url('/')) . '">صفحه اصلی</a>'
      . '</div>'
      . '</div>';

    return $html;
  }

  private static function index_shell_open() {
    return '<div class="delsa-bl">' . self::index_hero_markup() . '<div class="delsa-bl__grid blog-listing">';
  }

  private static function index_title() {
    if (is_category()) {
      return single_cat_title('', false);
    }
    if (is_tag()) {
      return single_tag_title('', false);
    }
    if (is_author()) {
      return get_the_author();
    }
    if (is_date()) {
      return 'آرشیو مطالب';
    }
    return 'وبلاگ';
  }

  private static function index_desc() {
    if (is_category() || is_tag() || is_author() || is_date()) {
      return '';
    }
    return 'مطالب آموزشی روان‌شناسی و سلامت روان از تیم کلینیک دلسا';
  }

  private static function post_meta_html() {
    $parts = [];
    $cats = get_the_category();
    if ($cats) {
      $parts[] = '<span class="delsa-bl__meta-item">' . esc_html($cats[0]->name) . '</span>';
    }
    $parts[] = '<span class="delsa-bl__meta-item">' . esc_html(get_the_date()) . '</span>';
    $author = get_the_author();
    if ($author) {
      $parts[] = '<span class="delsa-bl__meta-item">' . esc_html($author) . '</span>';
    }
    if (!$parts) {
      return '';
    }
    return '<div class="delsa-bl__meta">' . implode('', $parts) . '</div>';
  }

  private static function strip_empty_list_items($html) {
    $prev = null;
    while ($prev !== $html) {
      $prev = $html;
      $html = preg_replace('#<li[^>]*>\s*(?:&nbsp;|\x{00A0}|<br\s*/?>|\s)*</li>#u', '', $html);
    }
    return $html;
  }

  private static function js() {
    return <<<'JS'
(function () {
  function cleanEmptyLis(root) {
    if (!root) return;
    root.querySelectorAll("li").forEach(function (li) {
      var text = (li.textContent || "").replace(/\u00a0/g, " ").trim();
      if (!text) li.parentNode && li.parentNode.removeChild(li);
    });
  }
  function run() {
    var main = document.querySelector(".delsa-blog #main")
      || document.querySelector(".delsa-blog .site-main")
      || document.querySelector(".delsa-blog .elementor");
    cleanEmptyLis(main);
  }
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", run);
  } else {
    run();
  }
})();
JS;
  }

  private static function css() {
    return <<<'CSS'
@font-face{
  font-family:"Vazirmatn";
  font-style:normal;
  font-weight:100 900;
  font-display:swap;
  src:url("https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/fonts/webfonts/Vazirmatn[wght].woff2") format("woff2");
}
body.delsa-blog{
  --bl-ink:#1B4283;
  --bl-ink-dark:#122f5c;
  --bl-teal:#4CC9C0;
  --bl-sand:#F3F6F8;
  --bl-font:"Vazirmatn", Tahoma, sans-serif;
}
.delsa-bl,
.delsa-bl *{
  font-family:var(--bl-font) !important;
}

body.delsa-blog,
body.delsa-blog .boxed-container{
  max-width:100% !important;
  width:100% !important;
  overflow-x:clip;
}
body.delsa-blog #main,
body.delsa-blog .site-main,
body.delsa-blog .page_spacing{
  width:100% !important;
  max-width:100% !important;
  padding-top:.75rem !important;
  padding-bottom:1.75rem !important;
  margin:0 !important;
  background:
    radial-gradient(ellipse 70% 40% at 100% 0%, rgba(76,201,192,.14), transparent 55%),
    radial-gradient(ellipse 50% 35% at 0% 30%, rgba(27,66,131,.07), transparent 50%),
    var(--bl-sand) !important;
}
body.delsa-blog #main > .container,
body.delsa-blog .site-main > .container,
body.delsa-blog .container.no-padding{
  max-width:100% !important;
  width:100% !important;
  margin:0 !important;
  padding:0 clamp(1rem, 2.5vw, 2.25rem) !important;
  box-sizing:border-box !important;
}
body.delsa-blog .row{
  display:block !important;
  width:100% !important;
  max-width:100% !important;
  margin-left:0 !important;
  margin-right:0 !important;
}
body.delsa-blog .content-area,
body.delsa-blog .content-left,
body.delsa-blog .content-area.col-md-8,
body.delsa-blog .content-area.col-sm-8,
body.delsa-blog .content-area.col-md-12,
body.delsa-blog .content-area.col-sm-12{
  width:100% !important;
  max-width:100% !important;
  flex:0 0 100% !important;
  float:none !important;
  padding-left:0 !important;
  padding-right:0 !important;
}
body.delsa-blog .widget-area,
body.delsa-blog #secondary,
body.delsa-blog aside.sidebar,
body.delsa-blog .col-md-4.widget-area,
body.delsa-blog .content-area + .col-md-4,
body.delsa-blog .content-area + .col-sm-4{
  display:none !important;
  width:0 !important;
  max-width:0 !important;
  padding:0 !important;
  margin:0 !important;
}
.delsa-bl{
  display:flex;
  flex-direction:column;
  width:100% !important;
  max-width:100% !important;
  min-width:0;
}

/* عنوان تکراری تم — فقط هدر صفحه، نه کارت‌های blog-listing */
body.delsa-blog .page-title-block,
body.delsa-blog .page-banner,
body.delsa-blog .breadcrumb_s,
body.delsa-blog .page-breadcrumb,
body.delsa-blog .main-title-section-wrapper,
body.delsa-blog .section-header-box,
body.delsa-blog .section-header,
body.delsa-blog h2.hide,
body.delsa-blog .entry-content > h2.no-padding.no-margin.hide,
body.delsa-blog-index [id^="post-"] > h2.hide,
body.delsa-blog-index .page-title{
  display:none !important;
  height:0 !important;
  margin:0 !important;
  padding:0 !important;
  overflow:hidden !important;
}

/* هیرو */
.delsa-bl__hero{
  position:relative;
  overflow:hidden;
  display:flex;
  flex-wrap:wrap;
  align-items:center;
  justify-content:space-between;
  gap:.85rem 1.15rem;
  box-sizing:border-box !important;
  width:100vw !important;
  max-width:100vw !important;
  margin:0 0 1.15rem !important;
  margin-inline:calc(50% - 50vw) !important;
  padding:1.35rem clamp(1.15rem, 4vw, 3rem);
  border-radius:0;
  color:#fff;
  background:linear-gradient(120deg, #122f5c 0%, #1B4283 100%);
  box-shadow:0 12px 32px rgba(18,47,92,.18);
}
.delsa-bl__hero-main{min-width:0; flex:1 1 16rem}
.delsa-bl__crumb{
  display:flex; flex-wrap:wrap; align-items:center; gap:.35rem;
  margin:0 0 .45rem; font-size:12px; font-weight:500;
  color:rgba(255,255,255,.55);
}
.delsa-bl__crumb a{color:rgba(255,255,255,.82); text-decoration:none}
.delsa-bl__crumb a:hover{color:#fff}
.delsa-bl__label{
  display:inline-flex !important;
  align-items:center !important;
  margin:0 0 .5rem !important;
  padding:.4rem 1rem !important;
  font-size:12px !important;
  font-weight:700 !important;
  color:#122f5c !important;
  background:var(--bl-teal) !important;
  border-radius:999px !important;
  box-shadow:0 6px 16px rgba(76,201,192,.35) !important;
}
.delsa-bl__title{
  margin:0;
  font-size:clamp(1.35rem,1.15rem + .8vw,1.8rem);
  font-weight:800;
  line-height:1.35;
  color:#fff;
}
.delsa-bl__desc{
  margin:.55rem 0 0 !important;
  font-size:14px !important;
  line-height:1.75 !important;
  color:rgba(255,255,255,.78) !important;
  max-width:36rem;
}
.delsa-bl__meta{
  display:flex; flex-wrap:wrap; gap:.35rem .65rem;
  margin-top:.65rem;
}
.delsa-bl__meta-item{
  display:inline-flex;
  padding:.25rem .65rem;
  font-size:11px;
  font-weight:600;
  color:rgba(255,255,255,.88);
  background:rgba(255,255,255,.12);
  border:1px solid rgba(255,255,255,.18);
  border-radius:999px;
}
.delsa-bl__hero-actions{
  display:flex; flex-wrap:wrap; gap:.5rem; flex:0 1 auto;
}
.delsa-bl__hero-btn{
  display:inline-flex; align-items:center; justify-content:center;
  padding:.6rem 1.1rem; font-size:13px; font-weight:700;
  text-decoration:none !important; border-radius:999px;
  color:#122f5c !important; background:var(--bl-teal) !important;
  transition:background .2s ease, transform .2s ease;
}
.delsa-bl__hero-btn:hover{background:#6dd4cd !important; transform:translateY(-1px)}
.delsa-bl__hero-btn--ghost{
  color:#fff !important; background:transparent !important;
  border:1.5px solid rgba(255,255,255,.45) !important;
}
.delsa-bl__hero-btn--ghost:hover{
  background:rgba(255,255,255,.12) !important; border-color:#fff !important;
}

/* گرید آرشیو */
.delsa-bl__grid{
  display:grid !important;
  grid-template-columns:repeat(auto-fill, minmax(min(100%, 280px), 1fr)) !important;
  gap:clamp(.85rem, 1.6vw, 1.35rem) !important;
  width:100% !important;
  max-width:100% !important;
  margin:0 !important;
  padding:0 !important;
}
body.delsa-blog-index article.delsa-blog-card:not(:is(.blog-listing > *)),
body.delsa-blog-index article.post:not(:is(.blog-listing > *)),
body.delsa-blog-index article.type-post:not(:is(.blog-listing > *)),
body.delsa-blog-index .blog-item,
body.delsa-blog-index .post-box{
  display:flex !important;
  flex-direction:column !important;
  margin:0 !important;
  padding:1rem 1rem 1.1rem !important;
  background:#fff !important;
  border:1px solid #e4ebf1 !important;
  border-radius:18px !important;
  box-shadow:0 8px 22px rgba(18,47,92,.06) !important;
  overflow:hidden !important;
  transition:box-shadow .2s ease, transform .2s ease;
}
body.delsa-blog-index article.delsa-blog-card:not(:is(.blog-listing > *)):hover,
body.delsa-blog-index article.post:not(:is(.blog-listing > *)):hover{
  box-shadow:0 12px 28px rgba(18,47,92,.1) !important;
  transform:translateY(-2px);
}
body.delsa-blog-index article:not(:is(.blog-listing > *)) img,
body.delsa-blog-index article:not(:is(.blog-listing > *)) .post-thumbnail img{
  width:100% !important;
  height:auto !important;
  aspect-ratio:16/10;
  object-fit:cover !important;
  border-radius:12px !important;
  margin:0 0 .75rem !important;
}
body.delsa-blog-index article h2,
body.delsa-blog-index article h3,
body.delsa-blog-index article .entry-title,
body.delsa-blog-index article .post-title{
  margin:0 0 .45rem !important;
  padding:0 !important;
  font-size:15px !important;
  font-weight:700 !important;
  line-height:1.55 !important;
  color:var(--bl-ink) !important;
  border:0 !important;
  background:transparent !important;
}
body.delsa-blog-index article h2 a,
body.delsa-blog-index article h3 a,
body.delsa-blog-index article .entry-title a{
  color:inherit !important;
  text-decoration:none !important;
}
body.delsa-blog-index article h2 a:hover,
body.delsa-blog-index article h3 a:hover{color:#168f88 !important}
body.delsa-blog-index article p,
body.delsa-blog-index article .entry-summary,
body.delsa-blog-index article .post-excerpt{
  margin:0 !important;
  font-size:13px !important;
  line-height:1.8 !important;
  color:rgba(27,66,131,.72) !important;
  text-align:justify !important;
  text-justify:inter-word !important;
}
body.delsa-blog-index .read-more,
body.delsa-blog-index a.more-link{
  display:inline-flex !important;
  margin-top:.75rem !important;
  font-size:12px !important;
  font-weight:700 !important;
  color:#168f88 !important;
  text-decoration:none !important;
}

/* Doctor theme — صفحه /blog/ با WPBakery */
body.delsa-blog-index .blog-listing{
  display:grid !important;
  grid-template-columns:repeat(auto-fill, minmax(min(100%, 280px), 1fr)) !important;
  gap:clamp(.85rem, 1.6vw, 1.35rem) !important;
  align-items:stretch !important;
  width:100% !important;
  max-width:100% !important;
  margin:0 !important;
  padding:0 !important;
}
body.delsa-blog-index .blog-listing > article{
  position:relative !important;
  display:flex !important;
  flex-direction:column !important;
  height:100% !important;
  margin:0 !important;
  padding:0 !important;
  background:linear-gradient(165deg, #ffffff 0%, #f7fbfc 58%, #f2f8f7 100%) !important;
  border:1px solid rgba(76,201,192,.18) !important;
  border-radius:20px !important;
  box-shadow:0 10px 28px rgba(18,47,92,.07) !important;
  overflow:hidden !important;
  transition:box-shadow .28s ease, transform .28s ease, border-color .28s ease;
}
body.delsa-blog-index .blog-listing > article::before{
  content:"" !important;
  position:absolute !important;
  inset:0 auto auto 0 !important;
  width:100% !important;
  height:3px !important;
  background:linear-gradient(90deg, #4CC9C0, #1B4283) !important;
  opacity:.85 !important;
  z-index:2 !important;
}
body.delsa-blog-index .blog-listing > article:hover{
  border-color:rgba(76,201,192,.38) !important;
  box-shadow:0 16px 36px rgba(18,47,92,.11) !important;
  transform:translateY(-3px);
}
body.delsa-blog-index .blog-listing .entry-cover,
body.delsa-blog-index .blog-listing .entry-cover .post-thumbnail{
  position:relative !important;
  margin:0 !important;
  padding:0 !important;
  border:0 !important;
  border-radius:0 !important;
  overflow:hidden !important;
  float:none !important;
  width:100% !important;
  flex:0 0 auto !important;
  background:transparent !important;
  background-color:transparent !important;
  box-shadow:none !important;
}
body.delsa-blog-index .blog-listing .entry-cover::after{
  content:"" !important;
  position:absolute !important;
  left:0 !important;
  right:0 !important;
  bottom:0 !important;
  height:3.5rem !important;
  background:linear-gradient(to top, rgba(247,251,252,.98), transparent) !important;
  pointer-events:none !important;
  z-index:1 !important;
}
body.delsa-blog-index .blog-listing .post-date-bg,
body.delsa-blog-index .blog-listing .post-date{
  display:none !important;
}
body.delsa-blog-index .blog-listing .post-thumbnail{
  display:block !important;
  margin:0 !important;
  padding:0 !important;
  line-height:0 !important;
  background:transparent !important;
}
body.delsa-blog-index .blog-listing .post-thumbnail img{
  display:block !important;
  width:100% !important;
  max-width:none !important;
  height:12.75rem !important;
  object-fit:cover !important;
  border-radius:0 !important;
  margin:0 !important;
  padding:0 !important;
  border:0 !important;
  box-shadow:none !important;
  transform:scale(1.01);
  transition:transform .5s ease;
}
body.delsa-blog-index .blog-listing > article:hover .post-thumbnail img{
  transform:scale(1.06);
}
body.delsa-blog-index .blog-listing .latest-news-content{
  display:flex !important;
  flex-direction:column !important;
  flex:1 1 auto !important;
  padding:.95rem 1.15rem 1.2rem !important;
  margin:0 !important;
  float:none !important;
  width:100% !important;
  min-height:0 !important;
  background:transparent !important;
  border:0 !important;
  box-shadow:none !important;
}
body.delsa-blog-index .blog-listing .entry-header{
  display:block !important;
  height:auto !important;
  margin:0 0 .55rem !important;
  padding:0 !important;
  overflow:visible !important;
  order:1 !important;
  background:transparent !important;
  border:0 !important;
}
body.delsa-blog-index .blog-listing .entry-title{
  display:-webkit-box !important;
  visibility:visible !important;
  margin:0 0 .5rem !important;
  padding:0 0 .45rem !important;
  font-size:1.02rem !important;
  font-weight:800 !important;
  line-height:1.55 !important;
  color:var(--bl-ink) !important;
  -webkit-line-clamp:2 !important;
  -webkit-box-orient:vertical !important;
  overflow:hidden !important;
  border-bottom:1px solid rgba(76,201,192,.22) !important;
}
body.delsa-blog-index .blog-listing .entry-title a{
  color:inherit !important;
  text-decoration:none !important;
}
body.delsa-blog-index .blog-listing .entry-title a:hover{
  color:#168f88 !important;
}
body.delsa-blog-index .blog-listing .entry-meta{
  display:flex !important;
  flex-wrap:wrap !important;
  align-items:center !important;
  gap:.4rem .65rem !important;
  margin:.15rem 0 .35rem !important;
  padding:0 !important;
  border:0 !important;
  background:transparent !important;
}
body.delsa-blog-index .blog-listing .delsa-bl-card-meta__item{
  display:inline-flex !important;
  align-items:center !important;
  gap:.35rem !important;
  margin:0 !important;
  padding:0 !important;
  border:0 !important;
  background:transparent !important;
  box-shadow:none !important;
  font-size:12px !important;
  font-weight:500 !important;
  line-height:1.3 !important;
  color:rgba(27,66,131,.62) !important;
}
body.delsa-blog-index .blog-listing .delsa-bl-card-meta__item--author::after{
  content:none !important;
  margin:0 !important;
}
body.delsa-blog-index .blog-listing .delsa-bl-card-meta__icon{
  flex:0 0 auto !important;
  color:#4CC9C0 !important;
  opacity:.95 !important;
}
body.delsa-blog-index .blog-listing .entry-meta > div,
body.delsa-blog-index .blog-listing .entry-meta .byline,
body.delsa-blog-index .blog-listing .entry-meta .post-time,
body.delsa-blog-index .blog-listing .entry-meta .post-comment{
  display:none !important;
}
body.delsa-blog-index .blog-listing .entry-meta a{
  color:inherit !important;
  text-decoration:none !important;
  pointer-events:none !important;
}
body.delsa-blog-index .blog-listing .entry-meta i{
  display:none !important;
}
body.delsa-blog-index .blog-listing .entry-content{
  order:2 !important;
  margin:0 !important;
  padding:0 !important;
  flex:0 1 auto !important;
  min-height:0 !important;
}
body.delsa-blog-index .blog-listing .entry-content p{
  margin:0 !important;
  font-size:9.5px !important;
  line-height:1.5 !important;
  color:rgba(27,66,131,.58) !important;
  text-align:justify !important;
  text-justify:inter-word !important;
  display:-webkit-box !important;
  -webkit-line-clamp:4 !important;
  -webkit-box-orient:vertical !important;
  overflow:hidden !important;
  max-height:calc(1.5em * 4) !important;
}
body.delsa-blog-index .blog-listing a.read-more{
  order:3 !important;
  display:inline-flex !important;
  align-items:center !important;
  justify-content:center !important;
  align-self:flex-start !important;
  margin-top:auto !important;
  padding-top:.9rem !important;
  padding-inline:0 !important;
  font-size:12.5px !important;
  font-weight:700 !important;
  line-height:1.2 !important;
  color:#168f88 !important;
  background:transparent !important;
  border:0 !important;
  border-radius:0 !important;
  text-decoration:none !important;
  transition:color .2s ease, transform .2s ease;
}
body.delsa-blog-index .blog-listing a.read-more::after{
  content:" ‹" !important;
  margin-right:.2rem !important;
  opacity:.8 !important;
}
body.delsa-blog-index .blog-listing a.read-more:hover{
  color:#1B4283 !important;
  background:transparent !important;
  transform:translateX(-2px);
}
/* تم Doctor — حذف قاب تیره دور تصویر */
body.delsa-blog-index .blog-listing article .entry-cover,
body.delsa-blog-wpbakery .blog-listing .entry-cover,
body.delsa-blog-index .blog-listing .entry-cover a.post-thumbnail{
  background:none !important;
  background-color:transparent !important;
  background-image:none !important;
}
body.delsa-blog-index .blog-listing .latest-news-content,
body.delsa-blog-index .blog-listing .entry-header,
body.delsa-blog-index .blog-listing .entry-content{
  background:transparent !important;
  box-shadow:none !important;
  border:0 !important;
}

/* تک‌نوشته — تصویر/متا تکراری تم Doctor را پنهان کن */
body.delsa-blog-single .entry-cover,
body.delsa-blog-single .post-date-bg,
body.delsa-blog-single .latest-news-content > .entry-header,
body.delsa-blog-single .entry-categories{
  display:none !important;
}
body.delsa-blog-single .latest-news-content{
  padding:0 !important;
  margin:0 !important;
  float:none !important;
  width:100% !important;
}
body.delsa-blog-single .content-area.blog-listing{
  width:100% !important;
  max-width:100% !important;
  flex:0 0 100% !important;
  float:none !important;
}
body.delsa-blog-single #secondary,
body.delsa-blog-single .widget-area{
  display:none !important;
}

/* لینک ویرایش WPBakery برای ادمین */
body.delsa-blog .vc_controls,
body.delsa-blog .wpb_vc_edit_form_elements,
body.delsa-blog .vc_welcome-header,
body.delsa-blog a.vc_control-btn,
body.delsa-blog .wpb-content-wrapper > .vc_controls,
body.delsa-blog .wpb_wrapper > .wpb_vc_edit_link,
body.delsa-blog .vc_row .vc_control-container{
  display:none !important;
}

/* کارت تک‌نوشته */
.delsa-bl__card{
  width:100% !important;
  max-width:100% !important;
  box-sizing:border-box;
  background:#fff;
  border:1px solid #e4ebf1;
  border-radius:18px;
  padding:1.2rem clamp(1.15rem, 3vw, 2rem) 1.35rem;
  box-shadow:0 10px 28px rgba(18,47,92,.06);
  overflow:hidden;
}
body.delsa-blog-single .entry-content > h1:first-child,
body.delsa-blog-single .entry-content > h2:first-child,
body.delsa-blog-single .entry-content > h3:first-child,
body.delsa-blog-single .wpb_wrapper > h2:first-child,
body.delsa-blog-single .wpb_wrapper > h3:first-child{
  display:none !important;
}

body.delsa-blog .entry-content img,
body.delsa-blog .wpb_wrapper img,
body.delsa-blog .elementor-widget-image img{
  border-radius:16px !important;
  max-width:100% !important;
  height:auto !important;
  box-shadow:0 12px 28px rgba(18,47,92,.12) !important;
}
body.delsa-blog .entry-content h2,
body.delsa-blog .entry-content h3,
body.delsa-blog .wpb_wrapper h2,
body.delsa-blog .wpb_wrapper h3,
body.delsa-blog .vc_column-inner h2,
body.delsa-blog .vc_column-inner h3,
body.delsa-blog .elementor-widget-text-editor h2,
body.delsa-blog .elementor-widget-text-editor h3{
  margin:1.15rem 0 .55rem !important;
  padding:0 0 .35rem !important;
  font-size:15px !important;
  font-weight:700 !important;
  color:var(--bl-ink) !important;
  border:0 !important;
  border-bottom:2px solid #d8f0ed !important;
  line-height:1.5 !important;
  text-align:right !important;
}
body.delsa-blog .entry-content p,
body.delsa-blog .wpb_wrapper p,
body.delsa-blog .elementor-widget-text-editor p,
body.delsa-blog .elementor-text-editor p{
  font-size:14px !important;
  line-height:1.9 !important;
  color:rgba(27,66,131,.78) !important;
  text-align:justify !important;
  text-justify:inter-word !important;
  margin:0 0 .75rem !important;
}
body.delsa-blog .entry-content ul,
body.delsa-blog .wpb_wrapper ul,
body.delsa-blog .elementor-widget-text-editor ul{
  list-style:none !important;
  margin:0 0 .85rem !important;
  padding:0 !important;
}
body.delsa-blog .entry-content ol,
body.delsa-blog .wpb_wrapper ol,
body.delsa-blog .elementor-widget-text-editor ol{
  margin:0 1.15rem .85rem 0 !important;
  padding:0 1.15rem 0 0 !important;
}
body.delsa-blog .entry-content ul li,
body.delsa-blog .wpb_wrapper ul li,
body.delsa-blog .elementor-widget-text-editor ul li{
  position:relative !important;
  padding:.4rem 1.15rem .4rem 0 !important;
  font-size:14px !important;
  line-height:1.85 !important;
  color:rgba(27,66,131,.78) !important;
  text-align:justify !important;
  text-justify:inter-word !important;
  border-bottom:1px solid #eef2f5 !important;
}
body.delsa-blog .entry-content ol li,
body.delsa-blog .wpb_wrapper ol li,
body.delsa-blog .elementor-widget-text-editor ol li{
  padding:.4rem 0 !important;
  font-size:14px !important;
  line-height:1.85 !important;
  color:rgba(27,66,131,.78) !important;
  text-align:justify !important;
  text-justify:inter-word !important;
  border-bottom:1px solid #eef2f5 !important;
}
body.delsa-blog .entry-content ul li::before,
body.delsa-blog .wpb_wrapper ul li::before,
body.delsa-blog .elementor-widget-text-editor ul li::before{
  content:"" !important;
  position:absolute !important;
  right:0 !important;
  top:.95rem !important;
  width:6px !important;
  height:6px !important;
  border-radius:50% !important;
  background:var(--bl-teal) !important;
}
body.delsa-blog .wp-block-separator,
body.delsa-blog hr{
  border:0 !important;
  height:1px !important;
  background:#e8eef3 !important;
  margin:1rem 0 1.15rem !important;
}

/* صفحه‌بندی */
body.delsa-blog .pagination,
body.delsa-blog .nav-links,
body.delsa-blog .page-nav{
  grid-column:1 / -1 !important;
  display:flex !important;
  flex-wrap:wrap !important;
  justify-content:center !important;
  gap:.4rem !important;
  margin:1rem 0 0 !important;
  padding:0 !important;
}
body.delsa-blog .pagination a,
body.delsa-blog .pagination span,
body.delsa-blog .nav-links a,
body.delsa-blog .nav-links span{
  display:inline-flex !important;
  align-items:center !important;
  justify-content:center !important;
  min-width:2.2rem !important;
  padding:.45rem .7rem !important;
  font-size:13px !important;
  font-weight:600 !important;
  color:var(--bl-ink) !important;
  background:#fff !important;
  border:1px solid #d7e1ea !important;
  border-radius:999px !important;
  text-decoration:none !important;
}
body.delsa-blog .pagination .current,
body.delsa-blog .nav-links .current{
  color:#122f5c !important;
  background:var(--bl-teal) !important;
  border-color:var(--bl-teal) !important;
}

.delsa-bl__cta{
  display:flex; flex-wrap:wrap; gap:.65rem; margin:1.1rem 0 0;
}
.delsa-bl__btn{
  display:inline-flex; align-items:center; justify-content:center;
  padding:.72rem 1.25rem; font-size:13px; font-weight:700;
  text-decoration:none !important; border-radius:999px;
  color:#122f5c !important; background:var(--bl-teal);
  transition:background .2s ease, transform .2s ease;
}
.delsa-bl__btn:hover{background:#6dd4cd; transform:translateY(-1px)}
.delsa-bl__btn--ghost{
  color:var(--bl-ink) !important; background:#fff;
  border:1px solid #d7e1ea;
}
.delsa-bl__btn--ghost:hover{background:#f3f7f9}

@media (max-width:781px){
  .delsa-bl__hero{
    flex-direction:column;
    align-items:stretch;
    padding:1.15rem 1rem 1.25rem;
  }
  .delsa-bl__hero-actions{
    width:100%;
  }
  .delsa-bl__hero-btn{
    flex:1 1 auto;
  }
  .delsa-bl__card{padding:1.1rem 1rem 1.2rem}
  .delsa-bl__grid,
  body.delsa-blog-index .blog-listing{
    grid-template-columns:1fr !important;
  }
}
CSS;
  }
}

Delsa_Blog_Styling::init();
