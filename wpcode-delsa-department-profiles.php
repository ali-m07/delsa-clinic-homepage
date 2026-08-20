<?php
/**
 * WPCode Snippet — استایل صفحات دپارتمان (مثل پروفایل مشاوران)
 * نوع: PHP Snippet
 * محل: Run Everywhere
 *
 * صفحات با اسلاگ «دپارتمان-*» را تشخیص می‌دهد، هیرو + کارت محتوا می‌گذارد.
 * هدر سایت دست‌نخورده می‌ماند.
 */

if (!defined('ABSPATH')) {
  exit;
}

final class Delsa_Department_Profiles {
  const TRANSIENT = 'delsa_department_profile_ids_v6';
  const SLUG_PREFIX = 'دپارتمان-';
  const VERSION = '1.4.1';

  public static function init() {
    add_action('save_post_page', [__CLASS__, 'bust_cache']);
    add_filter('body_class', [__CLASS__, 'body_class']);
    add_action('wp_enqueue_scripts', [__CLASS__, 'assets'], 40);
    add_filter('the_content', [__CLASS__, 'wrap_content'], 20);
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

    $option = get_option('delsa_department_profile_ids', []);
    if (is_array($option) && $option) {
      $memo = self::valid_department_ids(array_map('intval', $option));
      if (count($memo) >= 2) {
        set_transient(self::TRANSIENT, $memo, WEEK_IN_SECONDS);
        return $memo;
      }
      delete_option('delsa_department_profile_ids');
    }

    $ids = [];

    $home_id = (int) get_option('page_on_front');
    if (!$home_id && get_post_status(562) === 'publish') {
      $home_id = 562;
    }

    if ($home_id) {
      $blob = '';
      $meta = get_post_meta($home_id, '_elementor_data', true);
      if ($meta) {
        $blob .= is_string($meta) ? $meta : wp_json_encode($meta);
      }
      $post = get_post($home_id);
      if ($post) {
        $blob .= "\n" . (string) $post->post_content;
      }

      if ($blob !== '' && preg_match_all('#https?://[^"\'\\\\\s<>]+#u', $blob, $m)) {
        foreach ($m[0] as $url) {
          $url = html_entity_decode($url, ENT_QUOTES, 'UTF-8');
          $path = (string) wp_parse_url($url, PHP_URL_PATH);
          if ($path === '' || $path === '/') {
            continue;
          }
          $slug = trim(urldecode($path), '/');
          if ($slug === '' || strpos($slug, self::SLUG_PREFIX) !== 0) {
            continue;
          }
          $p = get_page_by_path($slug);
          $pid = $p ? (int) $p->ID : url_to_postid($url);
          if ($pid && get_post_type($pid) === 'page') {
            $ids[] = (int) $pid;
          }
        }
      }
    }

    $known = [];
    foreach ($known as $kid) {
      if (get_post_status($kid) === 'publish') {
        $ids[] = (int) $kid;
      }
    }

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

    $pages = get_pages([
      'post_status' => 'publish',
      'number' => 50,
    ]);
    foreach ($pages as $page) {
      if (strpos((string) $page->post_name, self::SLUG_PREFIX) === 0) {
        $ids[] = (int) $page->ID;
      }
    }

    $ids = array_values(array_unique(array_filter($ids)));
    $ids = self::valid_department_ids($ids);
    update_option('delsa_department_profile_ids', $ids, false);
    set_transient(self::TRANSIENT, $ids, WEEK_IN_SECONDS);
    $memo = $ids;
    return $memo;
  }

  public static function bust_cache($post_id) {
    delete_transient(self::TRANSIENT);
    delete_option('delsa_department_profile_ids');
  }

  private static function valid_department_ids(array $ids) {
    $out = [];
    foreach ($ids as $id) {
      $id = (int) $id;
      if (!$id || get_post_status($id) !== 'publish') {
        continue;
      }
      $slug = (string) get_post_field('post_name', $id);
      if ($slug === '' || strpos($slug, self::SLUG_PREFIX) !== 0) {
        continue;
      }
      if (self::department_nav_label(get_the_title($id)) === '') {
        continue;
      }
      $out[] = $id;
    }
    return array_values(array_unique($out));
  }

  public static function department_nav_label($title) {
    $full = trim((string) $title);
    $short = preg_replace('/^دپارتمان\s+/u', '', $full);
    $short = preg_replace('/\s+دپارتمان$/u', '', $short);
    return $short !== '' ? $short : $full;
  }

  public static function departments_nav_html() {
    $current = (int) get_the_ID();
    $items = [];

    foreach (self::profile_ids() as $id) {
      $id = (int) $id;
      if (!$id || get_post_status($id) !== 'publish') {
        continue;
      }
      $label = self::department_nav_label(get_the_title($id));
      if ($label === '') {
        continue;
      }
      $items[] = [
        'id' => $id,
        'title' => get_the_title($id),
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

    $html .= '</div></nav>';
    return $html;
  }

  public static function nav_items() {
    $items = [];

    foreach (self::profile_ids() as $id) {
      $id = (int) $id;
      if (!$id || get_post_status($id) !== 'publish') {
        continue;
      }
      $label = self::department_nav_label(get_the_title($id));
      if ($label === '') {
        continue;
      }
      $items[] = [
        'id' => $id,
        'url' => get_permalink($id),
        'label' => $label,
      ];
    }

    return $items;
  }

  private static function inject_counselors($content) {
    if (!class_exists('Delsa_Consultant_Profiles')) {
      return $content;
    }
    if (
      strpos($content, 'delsa-team--department') !== false
      || strpos($content, 'team-section') !== false
      || strpos($content, 'مشاوران این دپارتمان') !== false
      || strpos($content, '[delsa_team') !== false
      || strpos($content, 'delsa-team--inline') !== false
    ) {
      return $content;
    }

    $grid = Delsa_Consultant_Profiles::team_grid_html([
      'context' => 'department',
      'show_heading' => true,
      'title' => 'مشاوران مجموعه',
    ]);

    if ($grid === '') {
      return $content;
    }

    $block = '<div class="delsa-dp__counselors">' . $grid . '</div>';

    if (preg_match(
      '#<div class="section-header-box">[\s\S]*?<h3>\s*مقالات\s*مرتبط\s*</h3>#u',
      $content,
      $matches,
      PREG_OFFSET_CAPTURE
    )) {
      $pos = $matches[0][1];
      return substr($content, 0, $pos) . $block . substr($content, $pos);
    }

    return $content . $block;
  }

  public static function is_profile() {
    if (!is_singular('page')) {
      return false;
    }
    $id = (int) get_the_ID();
    if (in_array($id, self::profile_ids(), true)) {
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

  public static function assets() {
    if (!self::is_profile()) {
      return;
    }
    wp_register_style('delsa-department-profile', false, [], self::VERSION);
    wp_enqueue_style('delsa-department-profile');
    wp_add_inline_style('delsa-department-profile', self::css());

    wp_register_script('delsa-department-profile', false, [], self::VERSION, true);
    wp_enqueue_script('delsa-department-profile');
    wp_add_inline_script('delsa-department-profile', self::js());
  }

  public static function wrap_content($content) {
    if (!self::is_profile()) {
      return $content;
    }
    if ((int) get_queried_object_id() !== (int) get_the_ID()) {
      return $content;
    }
    static $wrapped = false;
    if ($wrapped || strpos($content, 'delsa-dp') !== false) {
      return $content;
    }
    $wrapped = true;

    $content = self::strip_empty_list_items($content);
    $content = self::inject_counselors($content);

    $book = home_url('/%d9%81%d8%b1%d9%85-%d9%86%d9%88%d8%a8%d8%aa-%d8%af%d9%87%db%8c/');
    $name = get_the_title();

    $top = '<div class="delsa-dp">'
      . '<div class="delsa-dp__hero">'
      . '<div class="delsa-dp__hero-main">'
      . '<nav class="delsa-dp__crumb" aria-label="مسیر">'
      . '<a href="' . esc_url(home_url('/')) . '">خانه</a><span>/</span>'
      . '<span>' . esc_html($name) . '</span>'
      . '</nav>'
      . '<p class="delsa-dp__label">دپارتمان تخصصی</p>'
      . '<h1 class="delsa-dp__title">' . esc_html($name) . '</h1>'
      . '</div>'
      . '<div class="delsa-dp__hero-actions">'
      . '<a class="delsa-dp__hero-btn" href="' . esc_url($book) . '">رزرو وقت</a>'
      . '</div>'
      . '</div>'
      . '<div class="delsa-dp__card">';

    $bottom = '</div>'
      . self::departments_nav_html()
      . '<div class="delsa-dp__cta">'
      . '<a class="delsa-dp__btn" href="' . esc_url($book) . '">درخواست وقت در این دپارتمان</a>'
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

  function enableArticlesLoop(swiper, slideCount) {
    if (!swiper || slideCount < 2) return;

    var spv = swiper.params.slidesPerView;
    if (spv === "auto") {
      spv = 1;
    } else {
      spv = Math.max(1, Math.floor(Number(spv) || 1));
    }

    swiper.params.rewind = true;

    if (slideCount > spv) {
      swiper.params.loop = true;
      swiper.params.loopedSlides = slideCount;
      try {
        if (typeof swiper.loopDestroy === "function") {
          swiper.loopDestroy();
        }
        if (typeof swiper.loopCreate === "function") {
          swiper.loopCreate();
        }
      } catch (e) {}
    } else {
      swiper.params.loop = false;
    }

    if (swiper.autoplay && swiper.autoplay.running === false && swiper.params.autoplay) {
      swiper.autoplay.start();
    }

    swiper.update();
  }

  function repairArticlesCarousel() {
    var headers = document.querySelectorAll(".delsa-department-profile .section-header h3");
    var section = null;
    headers.forEach(function (h3) {
      if ((h3.textContent || "").indexOf("مقالات") !== -1) {
        section = h3.closest(".vc_row") || h3.closest(".wpb_row") || h3.closest(".entry-content");
      }
    });
    if (!section) return;

    var wrapper = section.querySelector(".swiper-wrapper");
    if (!wrapper) return;

    section.querySelectorAll(".e-loop-item.swiper-slide").forEach(function (slide) {
      if (!wrapper.contains(slide)) {
        wrapper.appendChild(slide);
      }
    });

    var swiperEl = section.querySelector(".elementor-loop-container.swiper");
    if (!swiperEl || !swiperEl.swiper) return;

    var slideCount = wrapper.querySelectorAll(
      ".swiper-slide:not(.swiper-slide-duplicate)"
    ).length;

    enableArticlesLoop(swiperEl.swiper, slideCount);
  }

  function fixTeamCarousel() {
    document.querySelectorAll("body.delsa-department-profile .team-carousel").forEach(function (carousel) {
      carousel.classList.remove("owl-carousel");
      if (window.jQuery) {
        var $c = window.jQuery(carousel);
        if ($c.data("owlCarousel")) {
          $c.trigger("destroy.owl.carousel");
        }
        $c.removeClass("owl-carousel owl-loaded");
        $c.find(".owl-stage-outer").children().unwrap();
      }
      carousel.style.display = "flex";
      carousel.style.flexWrap = "wrap";
      carousel.style.justifyContent = "center";
      carousel.style.gap = "1rem";
    });
  }

  function run() {
    var main = document.querySelector(".delsa-department-profile #main")
      || document.querySelector(".delsa-department-profile .elementor");
    cleanEmptyLis(main);
    fixTeamCarousel();
    repairArticlesCarousel();
    window.setTimeout(function () {
      fixTeamCarousel();
      repairArticlesCarousel();
    }, 500);
    window.setTimeout(function () {
      fixTeamCarousel();
      repairArticlesCarousel();
    }, 1500);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", run);
  } else {
    run();
  }
  window.addEventListener("load", function () {
    fixTeamCarousel();
    repairArticlesCarousel();
    window.setTimeout(function () {
      fixTeamCarousel();
      repairArticlesCarousel();
    }, 500);
  });
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
body.delsa-department-profile{
  --dp-ink:#1B4283;
  --dp-ink-dark:#122f5c;
  --dp-teal:#4CC9C0;
  --dp-gold:#c9a227;
  --dp-sand:#F3F6F8;
  --dp-muted:rgba(27,66,131,.68);
  --dp-font:"Vazirmatn", Tahoma, sans-serif;
}
.delsa-dp,
.delsa-dp *{
  font-family:var(--dp-font) !important;
}

body.delsa-department-profile #main,
body.delsa-department-profile .site-main,
body.delsa-department-profile .page_spacing{
  padding-top:.75rem !important;
  padding-bottom:1.75rem !important;
  margin:0 !important;
  background:
    radial-gradient(ellipse 70% 40% at 100% 0%, rgba(76,201,192,.14), transparent 55%),
    radial-gradient(ellipse 50% 35% at 0% 30%, rgba(27,66,131,.07), transparent 50%),
    var(--dp-sand) !important;
}
body.delsa-department-profile #main > .container,
body.delsa-department-profile .site-main > .container{
  max-width:920px !important;
  width:100% !important;
  margin:0 auto !important;
  padding:0 1.15rem !important;
}
body.delsa-department-profile:not(:has(.delsa-dp)) #main,
body.delsa-department-profile:not(:has(.delsa-dp)) .site-main,
body.delsa-department-profile:not(:has(.delsa-dp)) .page_spacing{
  padding-top:1rem !important;
  padding-bottom:2rem !important;
  background:
    radial-gradient(ellipse 70% 40% at 100% 0%, rgba(76,201,192,.14), transparent 55%),
    radial-gradient(ellipse 50% 35% at 0% 30%, rgba(27,66,131,.07), transparent 50%),
    var(--dp-sand) !important;
}
body.delsa-department-profile:not(:has(.delsa-dp)) .entry-content,
body.delsa-department-profile:not(:has(.delsa-dp)) .wpb_wrapper{
  max-width:920px !important;
  margin:0 auto !important;
}
body.delsa-department-profile .page-title-block,
body.delsa-department-profile .page-banner,
body.delsa-department-profile .breadcrumb_s,
body.delsa-department-profile .page-breadcrumb,
body.delsa-department-profile .main-title-section-wrapper,
body.delsa-department-profile h2.hide,
body.delsa-department-profile .entry-content > h2.no-padding.no-margin.hide{
  display:none !important;
  height:0 !important;
  margin:0 !important;
  padding:0 !important;
}

.delsa-dp__hero{
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
  border:0;
  box-shadow:0 12px 32px rgba(18,47,92,.18);
}
.delsa-dp__hero-main{
  position:relative;
  z-index:1;
  min-width:0;
  flex:1 1 14rem;
}
.delsa-dp__crumb{
  display:flex;
  flex-wrap:wrap;
  align-items:center;
  gap:.35rem;
  margin:0 0 .45rem;
  font-size:12px;
  font-weight:500;
  color:rgba(255,255,255,.55);
}
.delsa-dp__crumb a{color:rgba(255,255,255,.82); text-decoration:none}
.delsa-dp__crumb a:hover{color:#fff}
.delsa-dp__label{
  display:inline-flex !important;
  align-items:center !important;
  margin:0 0 .5rem !important;
  padding:.4rem 1rem !important;
  font-size:12px !important;
  font-weight:700 !important;
  line-height:1.2 !important;
  color:#122f5c !important;
  background:var(--dp-teal) !important;
  border:0 !important;
  border-radius:999px !important;
  box-shadow:0 6px 16px rgba(76,201,192,.35) !important;
}
.delsa-dp__title{
  margin:0;
  font-size:clamp(1.4rem,1.2rem + .8vw,1.85rem);
  font-weight:800;
  line-height:1.35;
  color:#fff;
  letter-spacing:-.01em;
}
.delsa-dp__hero-actions{
  position:relative;
  z-index:1;
  display:flex;
  flex-wrap:wrap;
  gap:.5rem;
  flex:0 0 auto;
}
.delsa-dp__hero-btn{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  padding:.6rem 1.1rem;
  font-size:13px;
  font-weight:700;
  text-decoration:none !important;
  border-radius:999px;
  color:#122f5c !important;
  background:var(--dp-teal) !important;
  border:0 !important;
  transition:background .2s ease, transform .2s ease;
}
.delsa-dp__hero-btn:hover{background:#6dd4cd !important; transform:translateY(-1px)}
.delsa-dp__hero-btn--ghost{
  color:#fff !important;
  background:transparent !important;
  border:1.5px solid rgba(255,255,255,.45) !important;
}
.delsa-dp__hero-btn--ghost:hover{
  background:rgba(255,255,255,.12) !important;
  border-color:#fff !important;
}

.delsa-dp__card{
  background:#fff;
  border:1px solid #e4ebf1;
  border-radius:18px;
  padding:1.2rem 1.15rem 1.35rem;
  box-shadow:0 10px 28px rgba(18,47,92,.06);
  overflow:hidden;
}

body.delsa-department-profile .wp-block-media-text{
  gap:1.35rem !important;
  align-items:start !important;
  margin:0 !important;
}
body.delsa-department-profile .wp-block-media-text__media img,
body.delsa-department-profile .wp-block-media-text img,
body.delsa-department-profile .entry-content img,
body.delsa-department-profile .elementor-widget-image img{
  border-radius:16px !important;
  width:100% !important;
  height:auto !important;
  object-fit:cover !important;
  box-shadow:0 12px 28px rgba(18,47,92,.12) !important;
}
body.delsa-department-profile .wp-block-media-text__content > .wp-block-heading:first-child,
body.delsa-department-profile .entry-content h2.wp-block-heading:first-of-type,
body.delsa-department-profile .entry-content > h2:first-child{
  display:none !important;
}
body.delsa-department-profile .wp-block-separator,
body.delsa-department-profile hr{
  border:0 !important;
  height:1px !important;
  background:#e8eef3 !important;
  margin:1rem 0 1.15rem !important;
}

body.delsa-department-profile .entry-content h2,
body.delsa-department-profile .entry-content h3,
body.delsa-department-profile .wpb_wrapper h2,
body.delsa-department-profile .wpb_wrapper h3,
body.delsa-department-profile .vc_column-inner h2,
body.delsa-department-profile .vc_column-inner h3,
body.delsa-department-profile .elementor-widget-text-editor h2,
body.delsa-department-profile .elementor-widget-text-editor h3,
body.delsa-department-profile .elementor-text-editor h2,
body.delsa-department-profile .elementor-text-editor h3{
  margin:1.35rem 0 .65rem !important;
  padding:0 0 .4rem !important;
  font-size:clamp(1rem,.95rem + .25vw,1.15rem) !important;
  font-weight:800 !important;
  color:var(--dp-ink) !important;
  background:transparent !important;
  border:0 !important;
  border-bottom:3px solid #d8f0ed !important;
  border-radius:0 !important;
  line-height:1.5 !important;
  text-align:right !important;
}
body.delsa-department-profile .entry-content h2:first-child,
body.delsa-department-profile .entry-content h3:first-child,
body.delsa-department-profile .wpb_wrapper h2:first-child,
body.delsa-department-profile .wp-block-media-text__content > h3:first-of-type{
  margin-top:0 !important;
}

body.delsa-department-profile .entry-content ul,
body.delsa-department-profile .entry-content ul.wp-block-list,
body.delsa-department-profile .wpb_wrapper ul,
body.delsa-department-profile .vc_column-inner ul,
body.delsa-department-profile .elementor-widget-text-editor ul,
body.delsa-department-profile .elementor-text-editor ul{
  list-style:none !important;
  margin:0 0 .85rem !important;
  padding:0 !important;
  text-align:right !important;
}
body.delsa-department-profile .entry-content ul li,
body.delsa-department-profile .entry-content ul.wp-block-list li,
body.delsa-department-profile .wpb_wrapper ul li,
body.delsa-department-profile .vc_column-inner ul li,
body.delsa-department-profile .elementor-widget-text-editor ul li,
body.delsa-department-profile .elementor-text-editor ul li{
  position:relative !important;
  display:block !important;
  margin:0 !important;
  padding:.4rem 1.15rem .4rem 0 !important;
  font-size:14px !important;
  font-weight:400 !important;
  line-height:1.85 !important;
  color:rgba(27,66,131,.78) !important;
  text-align:right !important;
  background:transparent !important;
  border:0 !important;
  border-radius:0 !important;
  border-bottom:1px solid #eef2f5 !important;
  box-shadow:none !important;
}
body.delsa-department-profile .entry-content ul li:last-child,
body.delsa-department-profile .entry-content ul.wp-block-list li:last-child,
body.delsa-department-profile .wpb_wrapper ul li:last-child,
body.delsa-department-profile .vc_column-inner ul li:last-child,
body.delsa-department-profile .elementor-widget-text-editor ul li:last-child,
body.delsa-department-profile .elementor-text-editor ul li:last-child{
  border-bottom:0 !important;
}
body.delsa-department-profile .entry-content ul li::before,
body.delsa-department-profile .entry-content ul.wp-block-list li::before,
body.delsa-department-profile .wpb_wrapper ul li::before,
body.delsa-department-profile .vc_column-inner ul li::before,
body.delsa-department-profile .elementor-widget-text-editor ul li::before,
body.delsa-department-profile .elementor-text-editor ul li::before{
  content:"" !important;
  position:absolute !important;
  right:0 !important;
  top:.95rem !important;
  left:auto !important;
  width:6px !important;
  height:6px !important;
  border-radius:50% !important;
  background:var(--dp-teal) !important;
  margin:0 !important;
  float:none !important;
}
body.delsa-department-profile .entry-content ul li::after,
body.delsa-department-profile .entry-content ul.wp-block-list li::after,
body.delsa-department-profile .wpb_wrapper ul li::after,
body.delsa-department-profile .vc_column-inner ul li::after,
body.delsa-department-profile .elementor-widget-text-editor ul li::after,
body.delsa-department-profile .elementor-text-editor ul li::after{
  content:none !important;
  display:none !important;
}
body.delsa-department-profile .entry-content ul li::marker,
body.delsa-department-profile .entry-content li::marker,
body.delsa-department-profile .elementor-widget-text-editor li::marker{
  content:"" !important;
  font-size:0 !important;
}

body.delsa-department-profile ul li:empty,
body.delsa-department-profile .elementor-widget-text-editor ul li:empty,
body.delsa-department-profile .elementor-text-editor ul li:empty,
body.delsa-department-profile .entry-content ul li:empty{
  display:none !important;
  margin:0 !important;
  padding:0 !important;
  border:0 !important;
  height:0 !important;
  overflow:hidden !important;
}
body.delsa-department-profile ul li:empty::before,
body.delsa-department-profile .elementor-widget-text-editor ul li:empty::before{
  content:none !important;
  display:none !important;
}

body.delsa-department-profile .entry-content p,
body.delsa-department-profile .elementor-widget-text-editor p,
body.delsa-department-profile .wpb_wrapper p{
  font-size:14px !important;
  line-height:1.9 !important;
  color:rgba(27,66,131,.78) !important;
  text-align:justify !important;
  text-justify:inter-word !important;
  margin:0 0 .75rem !important;
}

body.delsa-department-profile .entry-content p,
body.delsa-department-profile .elementor-widget-text-editor p,
body.delsa-department-profile .wpb_wrapper p{
  font-size:15px !important;
  line-height:1.95 !important;
  color:rgba(27,66,131,.82) !important;
  text-align:justify !important;
  text-justify:inter-word !important;
  margin:0 0 .85rem !important;
}

body.delsa-department-profile .entry-content blockquote,
body.delsa-department-profile .wpb_wrapper blockquote,
body.delsa-department-profile .vc_column-inner blockquote{
  border:0 !important;
  border-right:4px solid var(--dp-teal) !important;
  padding:1rem 1.2rem !important;
  margin:1.25rem 0 !important;
  background:linear-gradient(90deg, #eefaf9 0%, #f7fbfc 100%) !important;
  border-radius:0 14px 14px 0 !important;
  box-shadow:0 8px 20px rgba(76,201,192,.12) !important;
}
body.delsa-department-profile .entry-content blockquote p,
body.delsa-department-profile .wpb_wrapper blockquote p{
  margin:0 !important;
  font-size:16px !important;
  line-height:1.9 !important;
  color:var(--dp-ink) !important;
  font-weight:600 !important;
}

body.delsa-department-profile .about-section,
body.delsa-department-profile .about-content{
  margin:0 0 1.35rem !important;
  padding:1.15rem 1.1rem !important;
  background:linear-gradient(145deg, #f8fbfc 0%, #fff 55%) !important;
  border:1px solid #e4ebf1 !important;
  border-radius:16px !important;
  box-shadow:0 8px 22px rgba(18,47,92,.05) !important;
}
body.delsa-department-profile .about-section.container-fluid,
body.delsa-department-profile .team-section.container-fluid{
  padding-left:0 !important;
  padding-right:0 !important;
}
body.delsa-department-profile .about-section .container,
body.delsa-department-profile .about-section .row{
  margin:0 !important;
  padding:0 !important;
  width:100% !important;
  max-width:100% !important;
}
body.delsa-department-profile .about-section [class*="col-"]{
  float:none !important;
  width:100% !important;
  max-width:100% !important;
  padding:0 !important;
}
@media(min-width:768px){
  body.delsa-department-profile .about-section .row{
    display:grid !important;
    grid-template-columns:minmax(0,1.15fr) minmax(0,.85fr) !important;
    gap:1.25rem !important;
    align-items:center !important;
  }
}
body.delsa-department-profile .about-content h5{
  display:none !important;
}
body.delsa-department-profile:not(:has(.delsa-dp__hero)) .about-content h5{
  display:block !important;
  margin:0 0 .85rem !important;
  padding:0 0 .45rem !important;
  font-size:clamp(1.25rem,1.05rem + .7vw,1.7rem) !important;
  font-weight:800 !important;
  color:var(--dp-ink) !important;
  border-bottom:3px solid var(--dp-teal) !important;
  line-height:1.4 !important;
}
body.delsa-department-profile .about-img{
  margin:0 !important;
  text-align:center !important;
}
body.delsa-department-profile .about-img img{
  max-width:100% !important;
  width:100% !important;
  margin:0 auto !important;
  border-radius:16px !important;
  box-shadow:0 12px 28px rgba(18,47,92,.12) !important;
}

body.delsa-department-profile .team-section{
  margin:1.35rem 0 !important;
  padding:1.15rem 1.1rem !important;
  background:#fff !important;
  border:1px solid #e4ebf1 !important;
  border-radius:16px !important;
  box-shadow:0 8px 22px rgba(18,47,92,.05) !important;
}
body.delsa-department-profile .team-section .container{
  max-width:100% !important;
  width:100% !important;
  padding:0 !important;
  margin:0 !important;
}
body.delsa-department-profile .team-section .section-header,
body.delsa-department-profile .team-section .section-header h3{
  margin:0 0 1rem !important;
  padding:0 0 .4rem !important;
  font-size:clamp(1rem,.95rem + .25vw,1.15rem) !important;
  font-weight:800 !important;
  color:var(--dp-ink) !important;
  border-bottom:3px solid #d8f0ed !important;
  text-align:right !important;
  background:transparent !important;
}
body.delsa-department-profile .team-carousel{
  display:flex !important;
  flex-wrap:wrap !important;
  justify-content:center !important;
  align-items:flex-start !important;
  gap:1rem 1.15rem !important;
  margin:0 !important;
  padding:0 !important;
  width:100% !important;
}
body.delsa-department-profile .team-carousel::before,
body.delsa-department-profile .team-carousel::after{
  display:none !important;
  content:none !important;
}
body.delsa-department-profile .team-carousel > .col-md-12,
body.delsa-department-profile .team-carousel > [class*="col-"]{
  width:auto !important;
  flex:0 1 200px !important;
  max-width:220px !important;
  min-width:155px !important;
  float:none !important;
  padding:0 !important;
  margin:0 !important;
}
body.delsa-department-profile .team-content{
  height:100% !important;
  text-align:center !important;
  padding:.85rem .7rem 1rem !important;
  background:linear-gradient(180deg, #f8fbfc 0%, #fff 100%) !important;
  border:1px solid #e8eef3 !important;
  border-radius:14px !important;
  transition:transform .2s ease, box-shadow .2s ease !important;
}
body.delsa-department-profile .team-content:hover{
  transform:translateY(-3px) !important;
  box-shadow:0 12px 24px rgba(18,47,92,.1) !important;
  border-color:#cde9e6 !important;
}
body.delsa-department-profile .team-box img{
  border-radius:12px !important;
  width:100% !important;
  height:auto !important;
  max-width:180px !important;
  margin:0 auto !important;
  display:block !important;
  box-shadow:0 8px 18px rgba(18,47,92,.1) !important;
}
body.delsa-department-profile .team-box h5{
  margin:.7rem 0 .2rem !important;
  padding:0 !important;
  font-size:14px !important;
  font-weight:800 !important;
  color:var(--dp-ink) !important;
  border:0 !important;
}
body.delsa-department-profile .team-catagory{
  display:block !important;
  font-size:11px !important;
  line-height:1.65 !important;
  color:var(--dp-muted) !important;
  margin-top:.35rem !important;
}

.delsa-dp__nav{
  margin:1.1rem 0 0;
  padding:1rem;
  border-radius:16px;
  background:linear-gradient(145deg,#fff 0%,#f4faf9 100%);
  border:1px solid #e4ebf1;
}
.delsa-dp__nav-label{
  margin:0 0 .65rem;
  font-size:12px;
  font-weight:700;
  color:rgba(27,66,131,.55);
  text-align:right;
}
.delsa-dp__nav-list{
  display:flex;
  flex-wrap:wrap;
  gap:.45rem;
}
.delsa-dp__nav-item{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  padding:.5rem .85rem;
  font-size:12px;
  font-weight:600;
  line-height:1.4;
  text-decoration:none !important;
  color:var(--dp-ink) !important;
  background:#fff;
  border:1px solid #d7e1ea;
  border-radius:999px;
  transition:background .2s ease, border-color .2s ease, color .2s ease;
}
.delsa-dp__nav-item:hover{
  border-color:var(--dp-teal);
  color:#168f88 !important;
  background:#f3fbfb;
}
.delsa-dp__nav-item.is-active{
  color:#122f5c !important;
  background:var(--dp-teal);
  border-color:var(--dp-teal);
}

body.delsa-department-profile .delsa-dp__card .premium-carousel-wrapper,
body.delsa-department-profile .delsa-dp__card .elementor-widget-premium-carousel-widget,
body.delsa-department-profile .delsa-dp__legacy-counselors{
  display:none !important;
}

body.delsa-department-profile .elementor-1649,
body.page-id-761 .elementor-1649{
  display:block !important;
  margin:1.25rem 0 !important;
  padding:1.15rem !important;
  background:#fff !important;
  border:1px solid #e4ebf1 !important;
  border-radius:16px !important;
  box-shadow:0 8px 22px rgba(18,47,92,.05) !important;
}
body.delsa-department-profile .elementor-1649 .swiper-wrapper,
body.page-id-761 .elementor-1649 .swiper-wrapper{
  display:flex !important;
  flex-wrap:nowrap !important;
  justify-content:center !important;
  gap:1rem !important;
}
body.delsa-department-profile .elementor-1649 .swiper-slide,
body.page-id-761 .elementor-1649 .swiper-slide{
  width:auto !important;
  max-width:220px !important;
  flex:0 0 auto !important;
}

.delsa-dp__counselors{
  margin:1.15rem 0 1.35rem;
  padding-top:1rem;
  border-top:1px solid #e8eef3;
}

body.delsa-department-profile .section-header-box{
  margin:0 0 1rem !important;
  padding:0 !important;
}
body.delsa-department-profile .section-header-box .container{
  max-width:100% !important;
  padding:0 !important;
}
body.delsa-department-profile .section-header-box .section-header h3{
  margin:0 !important;
  padding:0 0 .35rem !important;
  font-size:15px !important;
  font-weight:700 !important;
  color:var(--dp-ink) !important;
  border-bottom:2px solid #d8f0ed !important;
  text-align:right !important;
  background:transparent !important;
}

body.delsa-department-profile .elementor-section-stretched{
  position:relative !important;
  left:auto !important;
  width:100% !important;
  margin-left:0 !important;
  margin-right:0 !important;
}
body.delsa-department-profile .elementor-widget-loop-carousel{
  display:block !important;
  overflow:hidden;
  margin:0 0 1rem !important;
}
body.delsa-department-profile .elementor-widget-loop-carousel .elementor-loop-container.swiper{
  overflow:hidden !important;
  padding-bottom:.35rem !important;
}
body.delsa-department-profile .elementor-widget-loop-carousel .swiper-wrapper{
  display:flex !important;
  align-items:stretch !important;
}
body.delsa-department-profile .elementor-widget-loop-carousel .swiper-slide{
  width:220px !important;
  max-width:78vw !important;
  height:auto !important;
}
body.delsa-department-profile .elementor-widget-loop-carousel .elementor-swiper-button{
  color:var(--dp-ink) !important;
}
body.delsa-department-profile .elementor-widget-loop-carousel .elementor-button{
  background:var(--dp-teal) !important;
  color:#122f5c !important;
  border-radius:999px !important;
  font-size:12px !important;
  font-weight:700 !important;
}
body.delsa-department-profile .elementor-widget-loop-carousel img{
  border-radius:14px !important;
}
body.delsa-department-profile .elementor-widget-loop-carousel .elementor-heading-title{
  font-size:13px !important;
  line-height:1.55 !important;
  color:var(--dp-ink) !important;
}

body.delsa-department-profile .vc_row,
body.delsa-department-profile .wpb_row{
  margin:0 0 .35rem !important;
}
body.delsa-department-profile .vc_column_container,
body.delsa-department-profile .wpb_column{
  padding:0 !important;
}
body.delsa-department-profile .vc_column-inner{
  padding:0 .15rem !important;
}
body.delsa-department-profile .wpb_text_column{
  margin-bottom:.5rem !important;
}
body.delsa-department-profile .wpb_single_image img,
body.delsa-department-profile .vc_single_image img{
  border-radius:16px !important;
  box-shadow:0 12px 28px rgba(18,47,92,.12) !important;
}

.delsa-dp__cta{
  display:flex;
  flex-wrap:wrap;
  gap:.65rem;
  margin:1.1rem 0 0;
}
.delsa-dp__btn{
  display:inline-flex;
  align-items:center;
  justify-content:center;
  padding:.72rem 1.25rem;
  font-size:13px;
  font-weight:700;
  text-decoration:none !important;
  border-radius:999px;
  color:#122f5c !important;
  background:var(--dp-teal);
  transition:background .2s ease, transform .2s ease;
}
.delsa-dp__btn:hover{background:#6dd4cd; transform:translateY(-1px)}
.delsa-dp__btn--ghost{
  color:var(--dp-ink) !important;
  background:#fff;
  border:1px solid #d7e1ea;
}
.delsa-dp__btn--ghost:hover{background:#f3f7f9}

@media (max-width:781px){
  body.delsa-department-profile .wp-block-media-text{grid-template-columns:1fr !important}
  body.delsa-department-profile .wp-block-media-text__media{margin-bottom:.75rem}
  body.delsa-department-profile .about-section .row{display:block !important}
  body.delsa-department-profile .about-img{margin-top:1rem !important}
  body.delsa-department-profile .team-carousel > .col-md-12,
  body.delsa-department-profile .team-carousel > [class*="col-"]{
    flex:1 1 calc(50% - .6rem) !important;
    max-width:calc(50% - .6rem) !important;
    min-width:140px !important;
  }
  .delsa-dp__hero{padding:1.15rem 1rem 1.25rem}
  .delsa-dp__card{padding:1.1rem 1rem 1.2rem}
}
@media (max-width:480px){
  body.delsa-department-profile .team-carousel > .col-md-12,
  body.delsa-department-profile .team-carousel > [class*="col-"]{
    flex:1 1 100% !important;
    max-width:240px !important;
  }
}
CSS;
  }
}

Delsa_Department_Profiles::init();
