<?php
/**
 * WPCode Snippet — پروفایل و فهرست مشاوران
 * نوع: PHP Snippet
 * محل: Run Everywhere
 *
 * - فهرست /consultant/: خودکار از همهٔ مشاوران CPT
 * - مشاور جدید با Publish در ادمین، بدون ویرایش PHP، به لیست اضافه می‌شود
 * - پروفایل: هیرو + کارت محتوا
 * - ریدایرکت ۳۰۱ برگه‌های قدیمی هم‌نام به CPT
 */

if (!defined('ABSPATH')) {
  exit;
}

final class Delsa_Consultant_Profiles {
  const TRANSIENT = 'delsa_consultant_profile_ids_v18';
  const VERSION = '2.4.0';

  /** آدرس فهرست مشاوران — آرشیو CPT */
  private static function listing_url() {
    $archive = get_post_type_archive_link('delsa_consultant');
    if (is_string($archive) && $archive !== '') {
      return $archive;
    }
    return home_url('/%d9%85%d8%b4%d8%a7%d9%88%d8%b1%d8%a7%d9%86/');
  }

  /** پیدا کردن CPT مشاور با نام (برای ریدایرکت برگهٔ قدیمی) */
  private static function find_cpt_by_name($name) {
    $name = self::normalize_name($name);
    if ($name === '' || self::is_blocked_name($name)) {
      return 0;
    }
    $posts = get_posts([
      'post_type' => 'delsa_consultant',
      'post_status' => 'publish',
      'numberposts' => 100,
      'suppress_filters' => true,
    ]);
    foreach ($posts as $post) {
      $title = self::normalize_name($post->post_title);
      if ($title === $name || mb_strpos($title, $name) !== false || mb_strpos($name, $title) !== false) {
        return (int) $post->ID;
      }
    }
    return 0;
  }

  /** ریدایرکت ۳۰۱ برگهٔ قدیمی ← CPT هم‌نام */
  public static function redirect_legacy_profiles() {
    if (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
      return;
    }
    if (!is_singular('page')) {
      return;
    }

    $current_id = (int) get_the_ID();
    if (!$current_id || $current_id === self::listing_page_id()) {
      return;
    }
    if (in_array($current_id, self::excluded_page_ids(), true)) {
      return;
    }
    if (!self::is_consultant_page($current_id)) {
      return;
    }

    $cpt_id = self::find_cpt_by_name(get_the_title($current_id));
    if (!$cpt_id || $cpt_id === $current_id) {
      return;
    }

    $target = get_permalink($cpt_id);
    if ($target) {
      wp_safe_redirect($target, 301);
      exit;
    }
  }

  /** همهٔ مشاوران منتشرشدهٔ CPT — منبع اصلی فهرست */
  public static function cpt_listing_cards() {
    $posts = get_posts([
      'post_type' => 'delsa_consultant',
      'post_status' => 'publish',
      'numberposts' => 100,
      'orderby' => [
        'menu_order' => 'ASC',
        'title' => 'ASC',
      ],
      'suppress_filters' => true,
    ]);

    $cards = [];
    foreach ($posts as $post) {
      $data = self::profile_card_data((int) $post->ID);
      if (!$data || self::is_blocked_name($data['name'])) {
        continue;
      }
      if ($data['role'] !== '' && mb_strlen($data['role']) > 90) {
        $data['role'] = mb_substr($data['role'], 0, 90) . '…';
      }
      $cards[] = $data;
    }
    return $cards;
  }

  /** فهرست = فقط CPT؛ مشاور جدید با Publish خودکار اضافه می‌شود */
  public static function listing_all_cards() {
    return self::cpt_listing_cards();
  }

  public static function init() {
    add_action('save_post_page', [__CLASS__, 'bust_cache']);
    add_action('save_post_delsa_consultant', [__CLASS__, 'bust_cache']);
    add_action('trashed_post', [__CLASS__, 'bust_cache']);
    add_action('deleted_post', [__CLASS__, 'bust_cache']);
    add_action('template_redirect', [__CLASS__, 'redirect_legacy_profiles'], 1);
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

    $ids = [];
    foreach (get_posts([
      'post_type' => 'delsa_consultant',
      'post_status' => 'publish',
      'numberposts' => 100,
      'suppress_filters' => true,
    ]) as $post) {
      if (!self::is_blocked_name($post->post_title)) {
        $ids[] = (int) $post->ID;
      }
    }

    $ids = self::sort_profile_ids(array_values(array_unique(array_filter($ids))));

    update_option('delsa_consultant_profile_ids', $ids, false);
    set_transient(self::TRANSIENT, $ids, WEEK_IN_SECONDS);
    $memo = $ids;
    return $memo;
  }

  /** ترتیب با menu_order (Order در ادیتور وردپرس) */
  private static function sort_profile_ids(array $ids) {
    usort($ids, static function ($a, $b) {
      $oa = (int) get_post_field('menu_order', $a);
      $ob = (int) get_post_field('menu_order', $b);
      if ($oa !== $ob) {
        return $oa <=> $ob;
      }
      return strcasecmp((string) get_the_title($a), (string) get_the_title($b));
    });
    return array_values($ids);
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

  private static function excluded_page_ids() {
    static $memo = null;
    if (is_array($memo)) {
      return $memo;
    }

    $ids = [
      self::listing_page_id(),
      (int) get_option('page_on_front'),
      2300, // دکتر نسرین مصباح — دیگر در تیم نیست
    ];
    foreach ([562, 21] as $kid) {
      if (get_post_status($kid) === 'publish') {
        $ids[] = $kid;
      }
    }

    foreach ([
      'فرم-نوبت-دهی',
      'درباره-ما',
      'تماس-با-ما',
      'blog',
      'مشاوران',
      'خانه',
      'دکتر-نسرین-مصباح',
      'دکتر-نسرین-دانایی',
      'نسرین-مصباح',
      'نسرین-دانایی',
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
    $ptype = get_post_type($post_id);
    if (!$post_id || get_post_status($post_id) !== 'publish') {
      return false;
    }
    if (in_array($post_id, self::excluded_page_ids(), true)) {
      return false;
    }

    $title = trim((string) get_the_title($post_id));
    if (self::is_blocked_name($title)) {
      return false;
    }

    if ($ptype === 'delsa_consultant') {
      return true;
    }
    if ($ptype !== 'page') {
      return false;
    }

    $slug = (string) get_post_field('post_name', $post_id);
    if ($slug === '' || strpos($slug, 'دپارتمان-') === 0) {
      return false;
    }

    if ($title === '' || preg_match('/فرم|نوبت|درباره|تماس|وبلاگ|دپارتمان|خانه|مشاوران/ui', $title)) {
      return false;
    }

    // زیرصفحهٔ قدیمی /مشاوران/ یا صفحهٔ شخصی شبیه پروفایل
    $listing = self::listing_page_id();
    if ($listing && (int) get_post_field('post_parent', $post_id) === $listing) {
      return true;
    }

    if (preg_match('/\s/u', $title) && mb_strlen($title) >= 5) {
      return true;
    }

    return false;
  }

  public static function bust_cache($post_id = 0) {
    unset($post_id);
    delete_transient(self::TRANSIENT);
    delete_option('delsa_consultant_profile_ids');
  }

  public static function is_profile() {
    if (is_singular('delsa_consultant')) {
      return true;
    }
    if (!is_singular('page')) {
      return false;
    }
    $id = (int) get_the_ID();
    $listing = self::listing_page_id();
    if ($listing && $id === $listing) {
      return false;
    }
    return self::is_consultant_page($id);
  }

  public static function is_listing() {
    if (is_post_type_archive('delsa_consultant')) {
      return true;
    }
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
    if (!in_array($post->post_type, ['page', 'delsa_consultant'], true)) {
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
    if ($role !== '' && mb_strlen($role) > 90) {
      $role = mb_substr($role, 0, 90) . '…';
    }
    if ($role === '') {
      $role = self::consultant_specialty($post_id);
    }
    // محتوای کامل صفحه را به‌عنوان نقش روی کارت نگذار

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

  /** مشاورانی که دیگر نباید در فهرست بیایند */
  private static function blocked_names() {
    return [
      'نسرین مصباح',
      'نسرین دانایی',
      'دکتر نسرین مصباح',
      'دکتر نسرین دانایی',
    ];
  }

  private static function is_blocked_name($name) {
    $n = self::normalize_name($name);
    if ($n === '') {
      return true;
    }
    foreach (self::blocked_names() as $blocked) {
      $b = self::normalize_name($blocked);
      if ($n === $b || mb_strpos($n, $b) !== false || mb_strpos($b, $n) !== false) {
        return true;
      }
    }
    return false;
  }

  /** فقط اسم واقعی آدم — نه CSS / کد Elementor */
  private static function is_valid_person_name($name) {
    $name = trim((string) $name);
    if ($name === '' || self::is_blocked_name($name)) {
      return false;
    }
    if (preg_match('/[.{#};@<>$]|body\.|elementor|page-id|widget|text-editor|heading/iu', $name)) {
      return false;
    }
    if (!preg_match('/[\x{0600}-\x{06FF}]{2,}/u', $name)) {
      return false;
    }
    if (!preg_match('/\s/u', $name) || mb_strlen($name) < 5 || mb_strlen($name) > 50) {
      return false;
    }
    if (substr_count($name, ' ') > 5) {
      return false;
    }
    return true;
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
    $pool = self::profile_ids();

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
          if (!self::is_consultant_page((int) $page->ID)) {
            continue;
          }
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

    if ($args['context'] === 'listing' && !$args['ids']) {
      // خودکار از همهٔ CPTهای مشاور (+ fallback)
      $cards = self::listing_all_cards();
    } else {
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
          if ($data['role'] !== '' && mb_strlen($data['role']) > 90) {
            $data['role'] = mb_substr($data['role'], 0, 90) . '…';
          }
          $cards[] = $data;
        }
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
    $list = self::listing_url();
    $post_id = get_the_ID();
    $name = get_the_title($post_id);
    $role = self::consultant_specialty($post_id);
    $thumb = get_the_post_thumbnail_url($post_id, 'large');
    if (!$thumb) {
      $data = self::profile_card_data($post_id);
      if ($data && $data['image'] !== '') {
        $thumb = $data['image'];
      }
    }

    $photo = '';
    if ($thumb) {
      $photo = '<div class="delsa-cp__photo">'
        . '<img src="' . esc_url($thumb) . '" alt="' . esc_attr($name) . '" width="420" height="480" loading="eager" decoding="async">'
        . '</div>';
    }

    $role_html = $role !== ''
      ? '<p class="delsa-cp__role" style="color:#ffffff !important;opacity:1 !important;font-size:1.2rem !important;font-weight:700 !important;line-height:1.7 !important;margin:0 0 .85rem !important;text-shadow:0 1px 3px rgba(0,0,0,.35);">'
        . esc_html($role)
        . '</p>'
      : '';

    $top = '<div class="delsa-cp">'
      . '<div class="delsa-cp__hero">'
      . $photo
      . '<div class="delsa-cp__hero-main">'
      . '<nav class="delsa-cp__crumb" aria-label="مسیر">'
      . '<a href="' . esc_url(home_url('/')) . '">خانه</a><span>/</span>'
      . '<a href="' . esc_url($list) . '">مشاوران</a><span>/</span>'
      . '<span>' . esc_html($name) . '</span>'
      . '</nav>'
      . '<p class="delsa-cp__label">پروفایل مشاور</p>'
      . '<h1 class="delsa-cp__title">' . esc_html($name) . '</h1>'
      . $role_html
      . '<div class="delsa-cp__hero-actions">'
      . '<a class="delsa-cp__hero-btn" href="' . esc_url($book) . '">رزرو وقت</a>'
      . '<a class="delsa-cp__hero-btn delsa-cp__hero-btn--ghost" href="' . esc_url($list) . '">همه مشاوران</a>'
      . '</div>'
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

  /** تخصص کوتاه از متای CPT یا excerpt */
  private static function consultant_specialty($post_id) {
    $post_id = (int) $post_id;
    $keys = [
      'delsa_specialty',
      '_delsa_specialty',
      'consultant_specialty',
      'specialty',
      'تخصص',
    ];
    foreach ($keys as $key) {
      $val = get_post_meta($post_id, $key, true);
      if (is_string($val) && trim($val) !== '') {
        return trim($val);
      }
    }
    $excerpt = trim(wp_strip_all_tags(strip_shortcodes((string) get_post_field('post_excerpt', $post_id))));
    if ($excerpt !== '' && mb_strlen($excerpt) <= 90) {
      return $excerpt;
    }
    return '';
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
  function killThemeChrome() {
    if (!document.body.classList.contains("delsa-consultant-profile")) return;
    document.querySelectorAll(
      ".widget-area, .sidebar-right, .sidebar-1, .entry-cover, .post-thumbnail, .entry-meta, .blog-entry-meta"
    ).forEach(function (el) {
      el.parentNode && el.parentNode.removeChild(el);
    });
    document.querySelectorAll(".content-area, .content-left, .col-md-8").forEach(function (el) {
      el.style.width = "100%";
      el.style.maxWidth = "100%";
      el.style.flex = "0 0 100%";
      el.style.float = "none";
    });
  }
  function run() {
    var main = document.querySelector(".delsa-consultant-profile #main")
      || document.querySelector(".delsa-consultant-profile .elementor");
    cleanEmptyLis(main);
    killThemeChrome();
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

/* فقط intro المنتور بماند؛ کارت‌های قدیمی پنهان — گرید WPCode منبع حقیقت است */
body.delsa-consultants-listing .elementor-770 > .elementor-section:not(:first-child),
body.delsa-consultants-listing .elementor-770 > section:not(:first-of-type),
body.delsa-consultants-listing .elementor-770 .elementor-top-section:nth-of-type(n+2),
body.delsa-consultants-listing .elementor-element-55eecd9,
body.delsa-consultants-listing .elementor-element-b340df1{
  display:none !important;
  height:0 !important;
  max-height:0 !important;
  overflow:hidden !important;
  margin:0 !important;
  padding:0 !important;
  border:0 !important;
}
body.delsa-consultants-listing .delsa-team--listing{
  max-width:1120px;
  margin:0 auto;
  padding:1.25rem 1.15rem 1.75rem;
}
body.delsa-consultants-listing .delsa-team--listing .delsa-team__grid{
  grid-template-columns:repeat(2,minmax(0,1fr));
  gap:1rem;
}
@media(min-width:700px){
  body.delsa-consultants-listing .delsa-team--listing .delsa-team__grid{
    grid-template-columns:repeat(3,minmax(0,1fr));
  }
}
@media(min-width:1024px){
  body.delsa-consultants-listing .delsa-team--listing .delsa-team__grid{
    grid-template-columns:repeat(auto-fill,minmax(180px,1fr));
  }
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
/* پروفایل CSS هرگز روی فهرست اعمال نشود */
body.delsa-consultants-listing .delsa-cp{display:contents}

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
  --cp-ink:#0F2740;
  --cp-ink-dark:#0A1B2E;
  --cp-teal:#1FA8A0;
  --cp-sand:#F7F8FA;
  --cp-muted:rgba(15,39,64,.68);
  --cp-font:"Vazirmatn", Tahoma, sans-serif;
}

/* تمام‌عرض؛ سایدبار وبلاگ حذف (تم Doctor: col-md-8 + col-md-4) */
body.delsa-consultant-profile #secondary,
body.delsa-consultant-profile .secondary-sidebar,
body.delsa-consultant-profile .widget-area,
body.delsa-consultant-profile aside.sidebar,
body.delsa-consultant-profile #sidebar,
body.delsa-consultant-profile .sidebar,
body.delsa-consultant-profile .sidebar-right,
body.delsa-consultant-profile .sidebar-1,
body.delsa-consultant-profile .dt-sc-dark-bg,
body.single-delsa_consultant #secondary,
body.single-delsa_consultant .widget-area,
body.single-delsa_consultant .sidebar-right{
  display:none !important;
  width:0 !important;
  max-width:0 !important;
  flex:0 0 0 !important;
  height:0 !important;
  overflow:hidden !important;
  margin:0 !important;
  padding:0 !important;
  border:0 !important;
}
body.delsa-consultant-profile #primary,
body.delsa-consultant-profile .content-area,
body.delsa-consultant-profile .content-left,
body.delsa-consultant-profile .page-with-sidebar,
body.delsa-consultant-profile .with-sidebar,
body.delsa-consultant-profile .container,
body.delsa-consultant-profile .col-md-8,
body.delsa-consultant-profile .col-sm-8,
body.single-delsa_consultant #primary,
body.single-delsa_consultant .content-area,
body.single-delsa_consultant .content-left,
body.single-delsa_consultant .col-md-8{
  width:100% !important;
  max-width:100% !important;
  flex:0 0 100% !important;
  float:none !important;
  margin:0 auto !important;
  left:auto !important;
  right:auto !important;
}
body.delsa-consultant-profile .page-with-sidebar > .container,
body.delsa-consultant-profile .with-sidebar > .container,
body.delsa-consultant-profile .content-main,
body.delsa-consultant-profile .row{
  display:block !important;
  width:100% !important;
  max-width:100% !important;
}

/* متای بلاگ + عکس تکراری/غلط تم (entry-cover) */
body.delsa-consultant-profile .entry-meta,
body.delsa-consultant-profile .blog-entry-meta,
body.delsa-consultant-profile .entry-date,
body.delsa-consultant-profile .posted-on,
body.delsa-consultant-profile .byline,
body.delsa-consultant-profile .author,
body.delsa-consultant-profile .comments-link,
body.delsa-consultant-profile .entry-format,
body.delsa-consultant-profile .entry-title,
body.delsa-consultant-profile .blog-title,
body.delsa-consultant-profile .post-thumb,
body.delsa-consultant-profile .entry-thumb,
body.delsa-consultant-profile .blog-image,
body.delsa-consultant-profile .entry-thumb-wrapper,
body.delsa-consultant-profile .blog-thumb,
body.delsa-consultant-profile .entry-cover,
body.delsa-consultant-profile .post-thumbnail,
body.delsa-consultant-profile article > .entry-cover,
body.delsa-consultant-profile article > .post-thumbnail,
body.delsa-consultant-profile .dt-sc-post-date,
body.delsa-consultant-profile .breadcrumb,
body.delsa-consultant-profile .page-title-block,
body.delsa-consultant-profile .page-banner,
body.delsa-consultant-profile .main-title-section-wrapper,
body.delsa-consultant-profile .post-nav-container,
body.delsa-consultant-profile .share,
body.delsa-consultant-profile .social-share,
body.delsa-consultant-profile .tags,
body.delsa-consultant-profile .entry-tags{
  display:none !important;
  height:0 !important;
  max-height:0 !important;
  margin:0 !important;
  padding:0 !important;
  border:0 !important;
  overflow:hidden !important;
}
/* عکس تم Doctor را بکش؛ عکس هیرو خودمان بماند */
body.delsa-consultant-profile .entry-cover img,
body.delsa-consultant-profile .post-thumbnail img,
body.delsa-consultant-profile article > .entry-cover .wp-post-image{
  display:none !important;
}

.delsa-cp,
.delsa-cp *,
body.delsa-consultant-profile,
body.delsa-consultant-profile #main,
body.delsa-consultant-profile .site-main,
body.delsa-consultant-profile article,
body.delsa-consultant-profile .entry-content,
body.delsa-consultant-profile .entry-content *{
  font-family:var(--cp-font) !important;
  -webkit-font-smoothing:antialiased;
}
body.delsa-consultant-profile #main,
body.delsa-consultant-profile .site-main,
body.delsa-consultant-profile .page_spacing,
body.single-delsa_consultant #main{
  padding-top:0 !important;
  padding-bottom:2.5rem !important;
  margin:0 !important;
  background:
    radial-gradient(ellipse 70% 40% at 100% 0%, rgba(31,168,160,.1), transparent 55%),
    var(--cp-sand) !important;
}
/* فول‌عرض واقعی — محدودیت ۹۲۰px برداشته شد */
body.delsa-consultant-profile #main > .container,
body.delsa-consultant-profile .site-main > .container,
body.delsa-consultant-profile #primary > .container,
body.delsa-consultant-profile .content-area > .container,
body.delsa-consultant-profile article,
body.delsa-consultant-profile .hentry{
  max-width:none !important;
  width:100% !important;
  margin:0 !important;
  padding-inline:0 !important;
  background:transparent !important;
  box-shadow:none !important;
  border:0 !important;
}
.delsa-cp{
  width:100% !important;
  max-width:none !important;
  margin:0 !important;
  padding:0 !important;
}

.delsa-cp__hero{
  position:relative;
  overflow:hidden;
  display:grid;
  grid-template-columns:1fr;
  gap:1rem;
  align-items:center;
  margin:0;
  padding:1.1rem clamp(1.15rem, 4vw, 2.5rem);
  border-radius:0;
  color:#fff;
  background:linear-gradient(125deg, #0A1B2E 0%, #163A4A 55%, #1A5F5A 100%);
  box-shadow:none;
  min-height:0;
}
@media(min-width:720px){
  .delsa-cp__hero{
    grid-template-columns:140px minmax(0,1fr);
    gap:1.15rem;
    padding:1.15rem clamp(1.25rem, 4vw, 2.75rem);
    min-height:0;
  }
}
.delsa-cp__photo{
  border-radius:14px;
  overflow:hidden;
  aspect-ratio:1/1;
  background:rgba(255,255,255,.08);
  max-width:160px;
  width:100%;
  margin-inline:auto;
  box-shadow:0 8px 22px rgba(0,0,0,.2);
}
@media(min-width:720px){
  .delsa-cp__photo{max-width:none;margin:0;aspect-ratio:4/5;max-height:190px}
}
.delsa-cp__photo img{
  width:100%;height:100%;object-fit:cover;object-position:center top;display:block;
}
.delsa-cp__hero-main{position:relative;z-index:1;min-width:0}
.delsa-cp__crumb{display:flex;flex-wrap:wrap;align-items:center;gap:.35rem;margin:0 0 .35rem;font-size:12px;font-weight:500;color:rgba(255,255,255,.7)}
.delsa-cp__crumb a{color:rgba(255,255,255,.92);text-decoration:none}
.delsa-cp__label{
  display:inline-flex !important;
  align-items:center !important;
  margin:0 0 .4rem !important;
  padding:.3rem .85rem !important;
  font-size:12px !important;
  font-weight:700 !important;
  color:#0A1B2E !important;
  background:#5EE0D6 !important;
  border-radius:999px !important;
}
.delsa-cp__title{
  margin:0 0 .3rem;
  font-size:clamp(1.45rem,1.2rem + 1vw,2rem);
  font-weight:800;
  line-height:1.25;
  color:#fff;
  letter-spacing:-.02em;
}
.delsa-cp__role,
body.delsa-consultant-profile .delsa-cp__role,
body.delsa-consultant-profile .delsa-cp__hero .delsa-cp__role,
body.delsa-consultant-profile .delsa-cp__hero p.delsa-cp__role,
body.delsa-consultant-profile .entry-content p.delsa-cp__role,
body.single-delsa_consultant .delsa-cp__role,
body.single-delsa_consultant .entry-content p.delsa-cp__role{
  margin:0 0 .85rem !important;
  font-size:1.2rem !important;
  font-weight:700 !important;
  line-height:1.7 !important;
  color:#ffffff !important;
  opacity:1 !important;
  max-width:40rem;
  text-shadow:0 1px 3px rgba(0,0,0,.35) !important;
  background:transparent !important;
  -webkit-text-fill-color:#ffffff !important;
}
.delsa-cp__hero-actions{display:flex;flex-wrap:wrap;gap:.55rem}
.delsa-cp__hero-btn{
  display:inline-flex;align-items:center;justify-content:center;
  padding:.65rem 1.2rem;font-size:14px;font-weight:700;text-decoration:none !important;
  border-radius:12px;color:#0A1B2E !important;background:#5EE0D6 !important;
}
.delsa-cp__hero-btn--ghost{color:#fff !important;background:transparent !important;border:1.5px solid rgba(255,255,255,.65) !important}
.delsa-cp__card{
  background:#fff;
  border:0;
  border-radius:0;
  padding:clamp(1.35rem, 2.5vw, 1.85rem) clamp(1.15rem, 4vw, 2.75rem) 1.75rem;
  box-shadow:none;
  overflow:hidden;
  max-width:none;
  width:100%;
}
body.delsa-consultant-profile .entry-content h2,
body.delsa-consultant-profile .entry-content h3,
body.delsa-consultant-profile .delsa-cp__card h2,
body.delsa-consultant-profile .delsa-cp__card h3{
  margin:1.25rem 0 .55rem !important;padding:0 0 .4rem !important;
  font-family:var(--cp-font) !important;
  font-size:1.35rem !important;
  font-weight:800 !important;color:var(--cp-ink) !important;
  border-bottom:2px solid #d8f0ed !important;text-align:right !important;
  letter-spacing:-.02em !important;
}
body.delsa-consultant-profile .entry-content h2:first-child,
body.delsa-consultant-profile .delsa-cp__card h2:first-child{margin-top:0 !important}
body.delsa-consultant-profile .entry-content p,
body.delsa-consultant-profile .delsa-cp__card p,
body.delsa-consultant-profile .entry-content p:not(.delsa-cp__role){
  font-family:var(--cp-font) !important;
  font-size:1.22rem !important;
  font-weight:400 !important;
  line-height:2 !important;
  color:#243444 !important;
  text-align:justify !important;
}
body.delsa-consultant-profile .entry-content ul,
body.delsa-consultant-profile .delsa-cp__card ul{
  margin:.35rem 0 1rem !important;padding:0 1.25rem 0 0 !important;
  list-style:disc !important;
}
body.delsa-consultant-profile .entry-content li,
body.delsa-consultant-profile .delsa-cp__card li{
  margin:0 0 .55rem !important;
  font-family:var(--cp-font) !important;
  font-size:1.2rem !important;
  font-weight:400 !important;
  line-height:1.95 !important;
  color:#243444 !important;
}
.delsa-cp__cta{
  display:flex;flex-wrap:wrap;gap:.75rem;
  margin:0;
  padding:0 clamp(1.15rem, 4vw, 2.75rem) 2rem;
  background:#fff;
}
.delsa-cp__btn{
  display:inline-flex;align-items:center;justify-content:center;
  padding:.8rem 1.4rem;font-size:15px;font-weight:700;text-decoration:none !important;
  border-radius:12px;color:#fff !important;background:var(--cp-teal);
  font-family:var(--cp-font) !important;
}
.delsa-cp__btn--ghost{color:var(--cp-ink) !important;background:#fff;border:1px solid rgba(15,39,64,.14)}
CSS;
  }
}

Delsa_Consultant_Profiles::init();
