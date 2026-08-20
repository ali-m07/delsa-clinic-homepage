<?php
/**
 * WPCode Snippet — پروفایل و فهرست مشاوران
 * نوع: PHP Snippet
 * محل: Run Everywhere
 *
 * - صفحات پروفایل مشاور: هیرو + کارت محتوا
 * - صفحه /مشاوران/: گرید خودکار از همه پروفایل‌ها
 * - صفحات جدید: با لینک در صفحه مشاوران یا زیرصفحه بودن، خودکار اضافه می‌شوند
 */

if (!defined('ABSPATH')) {
  exit;
}

final class Delsa_Consultant_Profiles {
  const TRANSIENT = 'delsa_consultant_profile_ids_v3';
  const VERSION = '1.8.0';

  public static function init() {
    add_action('save_post_page', [__CLASS__, 'bust_cache']);
    add_filter('body_class', [__CLASS__, 'body_class']);
    add_action('wp_enqueue_scripts', [__CLASS__, 'assets'], 40);
    add_filter('the_content', [__CLASS__, 'wrap_content'], 20);
    add_filter('the_content', [__CLASS__, 'inject_listing_grid'], 99);
    add_shortcode('delsa_team', [__CLASS__, 'shortcode_team']);
  }

  public static function listing_page_id() {
    static $id = null;
    if ($id !== null) {
      return $id;
    }
    $page = get_page_by_path('مشاوران');
    if ($page) {
      $id = (int) $page->ID;
      return $id;
    }
    if (get_post_status(770) === 'publish') {
      $id = 770;
      return $id;
    }
    $id = 0;
    return $id;
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

    $option = get_option('delsa_consultant_profile_ids', []);
    if (is_array($option) && $option) {
      $memo = array_values(array_filter(array_map('intval', $option), [__CLASS__, 'is_consultant_page']));
      set_transient(self::TRANSIENT, $memo, WEEK_IN_SECONDS);
      return $memo;
    }

    $listing_id = self::listing_page_id();
    $ids = [];

    foreach (self::consultant_slug_list() as $slug) {
      $p = get_page_by_path($slug);
      if ($p) {
        $ids[] = (int) $p->ID;
      }
    }

    $known = [1286, 1314, 1303, 1939, 2292];
    foreach ($known as $kid) {
      if (get_post_status($kid) === 'publish') {
        $ids[] = (int) $kid;
      }
    }

    if ($listing_id) {
      $children = get_pages([
        'child_of' => $listing_id,
        'post_status' => 'publish',
        'number' => 100,
      ]);
      foreach ($children as $child) {
        $ids[] = (int) $child->ID;
      }

      $blob = '';
      $meta = get_post_meta($listing_id, '_elementor_data', true);
      if ($meta) {
        $blob .= is_string($meta) ? $meta : wp_json_encode($meta);
      }
      $post = get_post($listing_id);
      if ($post) {
        $blob .= "\n" . (string) $post->post_content;
      }

      if ($blob !== '' && preg_match_all('#https?://[^"\'\\\\\s<>]+#u', $blob, $m)) {
        foreach ($m[0] as $url) {
          $pid = self::page_id_from_url($url, $listing_id);
          if ($pid && self::is_consultant_page($pid)) {
            $ids[] = $pid;
          }
        }
      }
    }

    if ($listing_id) {
      $ids = array_values(array_diff(array_unique(array_filter($ids)), [$listing_id]));
    } else {
      $ids = array_values(array_unique(array_filter($ids)));
    }

    $ids = array_values(array_filter($ids, [__CLASS__, 'is_consultant_page']));

    usort($ids, function ($a, $b) {
      $oa = (int) get_post_field('menu_order', $a);
      $ob = (int) get_post_field('menu_order', $b);
      if ($oa === $ob) {
        return strcasecmp((string) get_the_title($a), (string) get_the_title($b));
      }
      return $oa <=> $ob;
    });

    update_option('delsa_consultant_profile_ids', $ids, false);
    set_transient(self::TRANSIENT, $ids, WEEK_IN_SECONDS);
    $memo = $ids;
    return $memo;
  }

  private static function page_id_from_url($url, $exclude_id = 0) {
    $url = html_entity_decode($url, ENT_QUOTES, 'UTF-8');
    $path = (string) wp_parse_url($url, PHP_URL_PATH);
    if ($path === '' || $path === '/') {
      return 0;
    }
    $slug = trim(urldecode($path), '/');
    if ($slug === '' || strpos($slug, '/') !== false) {
      $pid = (int) url_to_postid($url);
    } else {
      $p = get_page_by_path($slug);
      $pid = $p ? (int) $p->ID : (int) url_to_postid($url);
    }
    if (!$pid || $pid === (int) $exclude_id || get_post_type($pid) !== 'page') {
      return 0;
    }
    if (get_post_status($pid) !== 'publish') {
      return 0;
    }
    return $pid;
  }

  private static function consultant_slug_list() {
    return [
      'الهام-مصباحی',
      'مریم-صالحی',
      'حسن-اکبرزاده',
      'دکتر-رباب-حامدی',
      'دکتر-نسرین-مصباح',
      'دکتر-نسرین-دانایی',
      'فاطمه-حسین-پور',
    ];
  }

  private static function excluded_page_ids() {
    static $memo = null;
    if (is_array($memo)) {
      return $memo;
    }

    $ids = [
      self::listing_page_id(),
      (int) get_option('page_on_front'),
    ];
    if (get_post_status(562) === 'publish') {
      $ids[] = 562;
    }
    if (get_post_status(21) === 'publish') {
      $ids[] = 21;
    }

    foreach ([
      'فرم-نوبت-دهی',
      'درباره-ما',
      'تماس-با-ما',
      'blog',
      'مشاوران',
      'خانه',
    ] as $slug) {
      $p = get_page_by_path($slug);
      if ($p) {
        $ids[] = (int) $p->ID;
      }
    }

    $memo = array_values(array_unique(array_filter(array_map('intval', $ids))));
    return $memo;
  }

  public static function is_consultant_page($post_id) {
    $post_id = (int) $post_id;
    if (!$post_id || get_post_type($post_id) !== 'page' || get_post_status($post_id) !== 'publish') {
      return false;
    }
    if (in_array($post_id, self::excluded_page_ids(), true)) {
      return false;
    }

    $slug = (string) get_post_field('post_name', $post_id);
    if ($slug === '' || strpos($slug, 'دپارتمان-') === 0) {
      return false;
    }

    $title = trim((string) get_the_title($post_id));
    if ($title === '' || preg_match('/فرم|نوبت|درباره|تماس|وبلاگ|دپارتمان|خانه/ui', $title)) {
      return false;
    }

    $listing = self::listing_page_id();
    if ($listing && (int) get_post_field('post_parent', $post_id) === $listing) {
      return true;
    }
    if (in_array($slug, self::consultant_slug_list(), true)) {
      return true;
    }
    if (in_array($post_id, [1286, 1314, 1303, 1939, 2292], true)) {
      return true;
    }

    return false;
  }

  public static function bust_cache($post_id) {
    delete_transient(self::TRANSIENT);
    delete_option('delsa_consultant_profile_ids');
  }

  public static function is_profile() {
    if (!is_singular('page')) {
      return false;
    }
    $id = (int) get_the_ID();
    $listing = self::listing_page_id();
    if ($listing && $id === $listing) {
      return false;
    }
    return in_array($id, self::profile_ids(), true);
  }

  public static function is_listing() {
    if (!is_singular('page')) {
      return false;
    }
    $listing = self::listing_page_id();
    return $listing && (int) get_the_ID() === $listing;
  }

  public static function body_class($classes) {
    if (self::is_profile()) {
      $classes[] = 'delsa-consultant-profile';
    }
    if (self::is_listing()) {
      $classes[] = 'delsa-consultants-listing';
    }
    return $classes;
  }

  public static function assets() {
    if (self::is_profile()) {
      wp_register_style('delsa-consultant-profile', false, [], self::VERSION);
      wp_enqueue_style('delsa-consultant-profile');
      wp_add_inline_style('delsa-consultant-profile', self::profile_css());

      wp_register_script('delsa-consultant-profile', false, [], self::VERSION, true);
      wp_enqueue_script('delsa-consultant-profile');
      wp_add_inline_script('delsa-consultant-profile', self::js());
    }

    if (self::is_listing() || (class_exists('Delsa_Department_Profiles') && Delsa_Department_Profiles::is_profile())) {
      wp_register_style('delsa-consultant-team', false, [], self::VERSION);
      wp_enqueue_style('delsa-consultant-team');
      wp_add_inline_style('delsa-consultant-team', self::team_css());
    }

    if (is_singular('page') && has_shortcode((string) get_post_field('post_content', get_queried_object_id()), 'delsa_team')) {
      wp_register_style('delsa-consultant-team', false, [], self::VERSION);
      wp_enqueue_style('delsa-consultant-team');
      wp_add_inline_style('delsa-consultant-team', self::team_css());
    }
  }

  public static function profile_card_data($post_id) {
    $post_id = (int) $post_id;
    $post = get_post($post_id);
    if (!$post || $post->post_status !== 'publish') {
      return null;
    }

    $thumb = get_the_post_thumbnail_url($post_id, 'large');
    if (!$thumb) {
      $blob = (string) $post->post_content;
      $meta = get_post_meta($post_id, '_elementor_data', true);
      if ($meta) {
        $blob .= is_string($meta) ? $meta : wp_json_encode($meta);
      }
      if (preg_match('#https?://[^"\'\\\\\s<>]+\.(?:jpe?g|png|webp)(?:\?[^"\'\\\\\s<>]*)?#iu', $blob, $m)) {
        $thumb = $m[0];
      }
    }

    $role = trim(wp_strip_all_tags(strip_shortcodes((string) $post->post_excerpt)));
    if ($role === '') {
      $plain = wp_strip_all_tags(strip_shortcodes((string) $post->post_content));
      $plain = preg_replace('/\s+/u', ' ', $plain);
      if (is_string($plain) && $plain !== '') {
        $role = mb_substr($plain, 0, 120);
        if (mb_strlen($plain) > 120) {
          $role .= '…';
        }
      }
    }

    return [
      'id' => $post_id,
      'name' => get_the_title($post_id),
      'url' => get_permalink($post_id),
      'image' => $thumb ? esc_url($thumb) : '',
      'role' => $role,
    ];
  }

  public static function normalize_name($name) {
    $name = trim((string) $name);
    $name = preg_replace('/^(دکتر|دكتر|مشاور)\s+/u', '', $name);
    $name = preg_replace('/\s+/u', ' ', $name);
    return $name;
  }

  public static function resolve_ids_from_names($names) {
    if (!is_array($names)) {
      $names = preg_split('/[,\-|]+/u', (string) $names);
    }

    $targets = [];
    foreach ($names as $name) {
      $name = self::normalize_name($name);
      if ($name !== '') {
        $targets[] = $name;
      }
    }

    if (!$targets) {
      return [];
    }

    $ids = [];
    $pool = array_merge(self::profile_ids(), self::discover_page_ids());

    foreach ($targets as $target) {
      $found = 0;
      foreach ($pool as $pid) {
        $pid = (int) $pid;
        if (!$pid || get_post_status($pid) !== 'publish') {
          continue;
        }
        $title = self::normalize_name(get_the_title($pid));
        if ($title === $target || mb_strpos($title, $target) !== false || mb_strpos($target, $title) !== false) {
          $found = $pid;
          break;
        }
      }

      if (!$found) {
        $pages = get_pages([
          'post_status' => 'publish',
          'number' => 20,
          's' => $target,
        ]);
        foreach ($pages as $page) {
          $title = self::normalize_name($page->post_title);
          if ($title === $target || mb_strpos($title, $target) !== false) {
            $found = (int) $page->ID;
            break;
          }
        }
      }

      if ($found) {
        $ids[] = $found;
      }
    }

    return array_values(array_unique(array_filter($ids)));
  }

  private static function discover_page_ids() {
    $ids = [];
    $listing = self::listing_page_id();
    if ($listing) {
      $ids[] = $listing;
      $children = get_pages([
        'child_of' => $listing,
        'post_status' => 'publish',
        'number' => 100,
      ]);
      foreach ($children as $child) {
        $ids[] = (int) $child->ID;
      }
    }
    return array_values(array_unique($ids));
  }

  public static function shortcode_team($atts) {
    $atts = shortcode_atts([
      'title' => 'مشاوران این دپارتمان',
      'names' => '',
      'ids' => '',
      'layout' => 'inline',
    ], $atts, 'delsa_team');

    $ids = [];
    if ($atts['ids'] !== '') {
      $ids = array_map('intval', preg_split('/[,\s|]+/', (string) $atts['ids']));
      $ids = array_values(array_filter($ids));
    } elseif ($atts['names'] !== '') {
      $ids = self::resolve_ids_from_names($atts['names']);
    }

    if (!$ids) {
      return '';
    }

    wp_enqueue_style('delsa-consultant-team');

    return self::team_grid_html([
      'context' => $atts['layout'] === 'grid' ? 'department' : 'inline',
      'show_heading' => true,
      'title' => (string) $atts['title'],
      'ids' => $ids,
    ]);
  }

  public static function team_grid_html($args = []) {
    $args = wp_parse_args($args, [
      'context' => 'listing',
      'title' => '',
      'show_heading' => false,
      'ids' => [],
    ]);

    $cards = [];
    $source_ids = $args['ids'] ? array_map('intval', (array) $args['ids']) : self::profile_ids();

    foreach ($source_ids as $pid) {
      $pid = (int) $pid;
      if (!$pid) {
        continue;
      }
      if ($args['ids'] && get_post_status($pid) !== 'publish') {
        continue;
      }
      if (!$args['ids'] && !self::is_consultant_page($pid)) {
        continue;
      }
      $data = self::profile_card_data($pid);
      if ($data) {
        $cards[] = $data;
      }
    }

    if (!$cards) {
      return '';
    }

    $heading = '';
    if ($args['show_heading'] && $args['title'] !== '') {
      $heading = '<div class="delsa-team__head">'
        . '<h2 class="delsa-team__title">' . esc_html($args['title']) . '</h2>'
        . '</div>';
    }

    $html = '<div class="delsa-team delsa-team--' . esc_attr($args['context']) . '">'
      . $heading
      . '<div class="delsa-team__grid">';

    foreach ($cards as $card) {
      $html .= '<article class="delsa-team__card">';
      $html .= '<a class="delsa-team__link" href="' . esc_url($card['url']) . '">';
      $html .= '<div class="delsa-team__media">';
      if ($card['image'] !== '') {
        $html .= '<img src="' . esc_url($card['image']) . '" alt="' . esc_attr($card['name']) . '" loading="lazy" decoding="async">';
      }
      $html .= '</div>';
      $html .= '<h3 class="delsa-team__name">' . esc_html($card['name']) . '</h3>';
      if ($card['role'] !== '') {
        $html .= '<p class="delsa-team__role">' . esc_html($card['role']) . '</p>';
      }
      $html .= '</a></article>';
    }

    $html .= '</div></div>';
    return $html;
  }

  public static function inject_listing_grid($content) {
    if (!self::is_listing() || !in_the_loop() || !is_main_query()) {
      return $content;
    }
    if (strpos($content, 'delsa-team--listing') !== false) {
      return $content;
    }

    $grid = self::team_grid_html([
      'context' => 'listing',
      'show_heading' => true,
      'title' => 'تیم مشاوران',
    ]);

    if ($grid === '') {
      return $content;
    }

    return $content . $grid;
  }

  public static function wrap_content($content) {
    if (!self::is_profile() || !in_the_loop() || !is_main_query()) {
      return $content;
    }
    if (strpos($content, 'delsa-cp') !== false) {
      return $content;
    }

    $content = self::strip_empty_list_items($content);

    $book = home_url('/%d9%81%d8%b1%d9%85-%d9%86%d9%88%d8%a8%d8%aa-%d8%af%d9%87%db%8c/');
    $list = home_url('/%d9%85%d8%b4%d8%a7%d9%88%d8%b1%d8%a7%d9%86/');
    $name = get_the_title();

    $top = '<div class="delsa-cp">'
      . '<div class="delsa-cp__hero">'
      . '<div class="delsa-cp__hero-main">'
      . '<nav class="delsa-cp__crumb" aria-label="مسیر">'
      . '<a href="' . esc_url(home_url('/')) . '">خانه</a><span>/</span>'
      . '<a href="' . esc_url($list) . '">مشاوران</a><span>/</span>'
      . '<span>' . esc_html($name) . '</span>'
      . '</nav>'
      . '<p class="delsa-cp__label">پروفایل مشاور</p>'
      . '<h1 class="delsa-cp__title">' . esc_html($name) . '</h1>'
      . '</div>'
      . '<div class="delsa-cp__hero-actions">'
      . '<a class="delsa-cp__hero-btn" href="' . esc_url($book) . '">رزرو وقت</a>'
      . '<a class="delsa-cp__hero-btn delsa-cp__hero-btn--ghost" href="' . esc_url($list) . '">همه مشاوران</a>'
      . '</div>'
      . '</div>'
      . '<div class="delsa-cp__card">';

    $bottom = '</div>'
      . '<div class="delsa-cp__cta">'
      . '<a class="delsa-cp__btn" href="' . esc_url($book) . '">درخواست وقت ملاقات با این مشاور</a>'
      . '<a class="delsa-cp__btn delsa-cp__btn--ghost" href="' . esc_url($list) . '">بازگشت به فهرست مشاوران</a>'
      . '</div>'
      . '</div>';

    return $top . $content . $bottom;
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
      if (!text) {
        li.parentNode && li.parentNode.removeChild(li);
      }
    });
  }
  function run() {
    var main = document.querySelector(".delsa-consultant-profile #main")
      || document.querySelector(".delsa-consultant-profile .elementor");
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

  private static function team_css() {
    return <<<'CSS'
.delsa-team{
  --team-ink:#1B4283;
  --team-muted:rgba(27,66,131,.68);
  --team-teal:#4CC9C0;
  font-family:"Vazirmatn",Tahoma,sans-serif;
}
.delsa-team__head{margin:0 0 1rem;text-align:right}
.delsa-team__title{
  margin:0;
  padding:0 0 .35rem;
  font-size:15px;
  font-weight:700;
  color:var(--team-ink);
  border-bottom:2px solid #d8f0ed;
}
.delsa-team__grid{
  display:grid;
  grid-template-columns:repeat(2,minmax(0,1fr));
  gap:.85rem;
  width:100%;
}
@media(min-width:640px){.delsa-team__grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(min-width:980px){.delsa-team__grid{grid-template-columns:repeat(4,minmax(0,1fr))}}
.delsa-team__card{
  background:#fff;
  border:1px solid #e4ebf1;
  border-radius:16px;
  overflow:hidden;
  box-shadow:0 8px 22px rgba(18,47,92,.06);
  transition:transform .2s ease, box-shadow .2s ease;
}
.delsa-team__card:hover{
  transform:translateY(-2px);
  box-shadow:0 12px 28px rgba(18,47,92,.1);
}
.delsa-team__link{
  display:flex;
  flex-direction:column;
  height:100%;
  color:inherit;
  text-decoration:none !important;
}
.delsa-team__media{
  aspect-ratio:3/4;
  background:#dce8f2;
  overflow:hidden;
}
.delsa-team__media img{
  width:100%;
  height:100%;
  object-fit:cover;
  object-position:center top;
}
.delsa-team__name{
  margin:0;
  padding:.65rem .75rem .25rem;
  font-size:13px;
  font-weight:700;
  line-height:1.45;
  color:var(--team-ink);
  text-align:center;
}
.delsa-team__role{
  margin:0;
  padding:0 .75rem .75rem;
  font-size:11px;
  line-height:1.65;
  color:var(--team-muted);
  text-align:center;
}

body.delsa-consultants-listing .elementor-770 > .elementor-section:not(:first-child),
body.delsa-consultants-listing .elementor-element-55eecd9,
body.delsa-consultants-listing .elementor-element-b340df1{
  display:none !important;
}
body.delsa-consultants-listing .delsa-team--listing{
  max-width:1120px;
  margin:0 auto;
  padding:1.25rem 1.15rem 1.75rem;
}
body.delsa-consultants-listing .delsa-team--listing .delsa-team__head{
  text-align:center;
  margin-bottom:1.25rem;
}
body.delsa-consultants-listing .delsa-team--listing .delsa-team__title{
  display:inline-block;
  font-size:clamp(1.1rem,1rem + .4vw,1.35rem);
  border:0;
  padding:0;
}

body.delsa-department-profile .delsa-team--department{
  margin:0;
  padding-top:0;
  border-top:0;
}
body.delsa-department-profile .delsa-dp__legacy-counselors{
  display:none !important;
}

.delsa-team--inline .delsa-team__grid{
  display:flex !important;
  flex-wrap:wrap !important;
  justify-content:center !important;
  align-items:stretch !important;
  gap:1rem 1.15rem !important;
}
.delsa-team--inline .delsa-team__card{
  flex:0 1 200px !important;
  max-width:220px !important;
  width:100% !important;
}
@media(max-width:480px){
  .delsa-team--inline .delsa-team__card{
    flex:1 1 calc(50% - .6rem) !important;
    max-width:calc(50% - .6rem) !important;
  }
}
CSS;
  }

  private static function profile_css() {
    return <<<'CSS'
@font-face{
  font-family:"Vazirmatn";
  font-style:normal;
  font-weight:100 900;
  font-display:swap;
  src:url("https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/fonts/webfonts/Vazirmatn[wght].woff2") format("woff2");
}
body.delsa-consultant-profile{
  --cp-ink:#1B4283;
  --cp-ink-dark:#122f5c;
  --cp-teal:#4CC9C0;
  --cp-sand:#F3F6F8;
  --cp-muted:rgba(27,66,131,.68);
  --cp-font:"Vazirmatn", Tahoma, sans-serif;
}
.delsa-cp,
.delsa-cp *{
  font-family:var(--cp-font) !important;
}
body.delsa-consultant-profile #main,
body.delsa-consultant-profile .site-main,
body.delsa-consultant-profile .page_spacing{
  padding-top:.75rem !important;
  padding-bottom:1.75rem !important;
  margin:0 !important;
  background:
    radial-gradient(ellipse 70% 40% at 100% 0%, rgba(76,201,192,.16), transparent 55%),
    radial-gradient(ellipse 50% 35% at 0% 30%, rgba(27,66,131,.07), transparent 50%),
    var(--cp-sand) !important;
}
body.delsa-consultant-profile #main > .container,
body.delsa-consultant-profile .site-main > .container{
  max-width:920px !important;
  width:100% !important;
  margin:0 auto !important;
  padding:0 1.15rem !important;
}
body.delsa-consultant-profile .page-title-block,
body.delsa-consultant-profile .page-banner,
body.delsa-consultant-profile .breadcrumb_s,
body.delsa-consultant-profile .page-breadcrumb,
body.delsa-consultant-profile .main-title-section-wrapper,
body.delsa-consultant-profile h2.hide,
body.delsa-consultant-profile .entry-content > h2.no-padding.no-margin.hide{
  display:none !important;
  height:0 !important;
  margin:0 !important;
  padding:0 !important;
}
.delsa-cp__hero{
  position:relative;
  overflow:hidden;
  display:flex;
  flex-wrap:wrap;
  align-items:center;
  justify-content:space-between;
  gap:.85rem 1.15rem;
  margin:0 0 1rem;
  padding:1.1rem 1.25rem;
  border-radius:18px;
  color:#fff;
  background:linear-gradient(120deg, #122f5c 0%, #1B4283 100%);
  box-shadow:0 12px 32px rgba(18,47,92,.18);
}
.delsa-cp__hero-main{position:relative;z-index:1;min-width:0;flex:1 1 14rem}
.delsa-cp__crumb{display:flex;flex-wrap:wrap;align-items:center;gap:.35rem;margin:0 0 .45rem;font-size:12px;font-weight:500;color:rgba(255,255,255,.55)}
.delsa-cp__crumb a{color:rgba(255,255,255,.82);text-decoration:none}
.delsa-cp__label{
  display:inline-flex !important;
  align-items:center !important;
  margin:0 0 .5rem !important;
  padding:.4rem 1rem !important;
  font-size:12px !important;
  font-weight:700 !important;
  color:#122f5c !important;
  background:#4CC9C0 !important;
  border-radius:999px !important;
}
.delsa-cp__title{margin:0;font-size:clamp(1.4rem,1.2rem + .8vw,1.85rem);font-weight:800;line-height:1.35;color:#fff}
.delsa-cp__hero-actions{position:relative;z-index:1;display:flex;flex-wrap:wrap;gap:.5rem}
.delsa-cp__hero-btn{
  display:inline-flex;align-items:center;justify-content:center;
  padding:.6rem 1.1rem;font-size:13px;font-weight:700;text-decoration:none !important;
  border-radius:999px;color:#122f5c !important;background:#4CC9C0 !important;
}
.delsa-cp__hero-btn--ghost{color:#fff !important;background:transparent !important;border:1.5px solid rgba(255,255,255,.45) !important}
.delsa-cp__card{
  background:#fff;border:1px solid #e4ebf1;border-radius:18px;
  padding:1.2rem 1.15rem 1.35rem;box-shadow:0 10px 28px rgba(18,47,92,.06);overflow:hidden;
}
body.delsa-consultant-profile .entry-content h2,
body.delsa-consultant-profile .entry-content h3,
body.delsa-consultant-profile .wpb_wrapper h2,
body.delsa-consultant-profile .wpb_wrapper h3{
  margin:1.15rem 0 .55rem !important;padding:0 0 .35rem !important;
  font-size:15px !important;font-weight:700 !important;color:var(--cp-ink) !important;
  border-bottom:2px solid #d8f0ed !important;text-align:right !important;
}
body.delsa-consultant-profile .entry-content p,
body.delsa-consultant-profile .wpb_wrapper p{
  font-size:14px !important;line-height:1.9 !important;
  color:rgba(27,66,131,.78) !important;text-align:justify !important;text-justify:inter-word !important;
}
.delsa-cp__cta{display:flex;flex-wrap:wrap;gap:.65rem;margin:1.1rem 0 0}
.delsa-cp__btn{
  display:inline-flex;align-items:center;justify-content:center;
  padding:.72rem 1.25rem;font-size:13px;font-weight:700;text-decoration:none !important;
  border-radius:999px;color:#122f5c !important;background:var(--cp-teal);
}
.delsa-cp__btn--ghost{color:var(--cp-ink) !important;background:#fff;border:1px solid #d7e1ea}
CSS;
  }
}

Delsa_Consultant_Profiles::init();
