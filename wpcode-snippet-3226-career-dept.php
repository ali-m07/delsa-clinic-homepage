<?php
/**
 * WPCode Snippet #3226 — دپارتمان مشاوره شغلی + Elementor Template 1649
 * نوع: PHP Snippet
 * محل: Run Everywhere
 *
 * صفحه: دپارتمان مشاوره شغلی (ID 761)
 * تمپلیت مشاوران: elementor-template id="1649"
 */

if (!defined('ABSPATH')) {
  exit;
}

final class Delsa_Career_Dept_Page {
  const PAGE_ID = 761;
  const TEAM_TEMPLATE_ID = 1649;
  const VERSION = '1.0.0';

  public static function init() {
    add_action('wp_enqueue_scripts', [__CLASS__, 'assets'], 45);
  }

  public static function is_page() {
    if (!is_singular('page')) {
      return false;
    }
    if ((int) get_queried_object_id() === self::PAGE_ID) {
      return true;
    }
    $slug = (string) get_post_field('post_name', get_queried_object_id());
    return $slug === 'دپارتمان-مشاوره-شغلی';
  }

  public static function assets() {
    if (!self::is_page()) {
      return;
    }

    wp_register_style('delsa-career-dept-3226', false, [], self::VERSION);
    wp_enqueue_style('delsa-career-dept-3226');
    wp_add_inline_style('delsa-career-dept-3226', self::css());
  }

  private static function css() {
    $tid = (int) self::TEAM_TEMPLATE_ID;

    return <<<CSS
body.page-id-761 #main,
body.page-id-761 .site-main,
body.page-id-761 .page_spacing{
  padding:1rem 0 2rem !important;
  background:#f3f6f8 !important;
}
body.page-id-761 #main > .container,
body.page-id-761 .site-main > .container,
body.page-id-761 .container.fixedlayout{
  max-width:920px !important;
  margin:0 auto !important;
  padding:0 1.15rem !important;
}
body.page-id-761 .page-title-block,
body.page-id-761 .page-banner,
body.page-id-761 .breadcrumb_s{display:none !important}

body.page-id-761 .about-section{
  margin:0 0 1.25rem !important;
  padding:1.15rem !important;
  background:linear-gradient(145deg,#f8fbfc 0%,#fff 55%) !important;
  border:1px solid #e4ebf1 !important;
  border-radius:16px !important;
}
@media(min-width:768px){
  body.page-id-761 .about-section .row{
    display:grid !important;
    grid-template-columns:minmax(0,1.15fr) minmax(0,.85fr) !important;
    gap:1.25rem !important;
    align-items:center !important;
  }
}
body.page-id-761 .about-content h5{
  margin:0 0 .85rem !important;
  padding:0 0 .45rem !important;
  font-size:1.45rem !important;
  font-weight:800 !important;
  color:#1B4283 !important;
  border-bottom:3px solid #4CC9C0 !important;
}
body.page-id-761 .about-content p,
body.page-id-761 .wpb_wrapper p{
  font-size:15px !important;
  line-height:1.95 !important;
  color:rgba(27,66,131,.82) !important;
  text-align:justify !important;
}
body.page-id-761 .wpb_wrapper h2{
  color:#1B4283 !important;
  border-bottom:3px solid #d8f0ed !important;
  padding-bottom:.4rem !important;
  font-weight:800 !important;
}

body.page-id-761 .elementor-{$tid},
body.page-id-761 .elementor-element.elementor-element-{$tid}{
  display:block !important;
  margin:1.25rem 0 !important;
  padding:1.15rem !important;
  background:#fff !important;
  border:1px solid #e4ebf1 !important;
  border-radius:16px !important;
}
body.page-id-761 .elementor-{$tid} .swiper-wrapper{
  display:flex !important;
  flex-wrap:nowrap !important;
  justify-content:center !important;
  gap:1rem !important;
}
body.page-id-761 .elementor-{$tid} .swiper-slide{
  width:auto !important;
  max-width:220px !important;
  flex:0 0 auto !important;
}
body.page-id-761 .ltr{direction:ltr !important}
CSS;
  }
}

Delsa_Career_Dept_Page::init();
