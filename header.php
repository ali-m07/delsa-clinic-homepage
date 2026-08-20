<?php
if (!defined('ABSPATH')) {
  exit;
}

/** مخفی کردن هدر تم Doctor */
add_action('wp_head', function () {
  if (is_admin()) {
    return;
  }
  ?>
  <style id="delsa-global-header-css">
    /* Doctor Theme header — دقیق همین کلاس‌ها */
    header.header_s,
    .header_s,
    .top-header,
    #slidepanel,
    nav.ownavigation,
    .boxed-container > header,
    body.wp-theme-doctor > .boxed-container > header.header_s {
      display: none !important;
      height: 0 !important;
      overflow: hidden !important;
      visibility: hidden !important;
      pointer-events: none !important;
    }

    /* عنوان بنر تم */
    .page-title, .page-title-block, .page-banner, .title-header,
    .breadcrumb_s, .page-breadcrumb {
      /* فقط بنر عنوان تم؛ hero دلسا را نزن */
    }

    /* هدر ثابت — با اسکرول هاید نمی‌شود */
    #delsa-site-header {
      position: fixed !important;
      top: 0 !important;
      left: 0 !important;
      right: 0 !important;
      z-index: 99999 !important;
      width: 100% !important;
      max-width: 100% !important;
      background: #ffffff !important;
      border-bottom: 1px solid rgba(27, 66, 131, 0.08) !important;
      font-family: 'Vazirmatn', Tahoma, sans-serif !important;
      box-sizing: border-box !important;
      transform: none !important;
      opacity: 1 !important;
      visibility: visible !important;
      pointer-events: auto !important;
      transition: box-shadow 0.3s ease !important;
    }
    #delsa-header-root {
      display: block !important;
    }
    /* جای خالی زیر هدر ثابت تا محتوا زیرش نرود */
    body.admin-bar #delsa-site-header {
      top: 32px !important;
    }
    @media (max-width: 782px) {
      body.admin-bar #delsa-site-header {
        top: 46px !important;
      }
    }
    body {
      padding-top: 72px !important;
    }
    body.admin-bar {
      padding-top: 104px !important;
    }
    @media (max-width: 782px) {
      body.admin-bar {
        padding-top: 118px !important;
      }
    }
    #delsa-site-header *, #delsa-site-header *::before, #delsa-site-header *::after {
      box-sizing: border-box !important;
    }
    #delsa-site-header.scrolled {
      box-shadow: 0 4px 24px rgba(27, 66, 131, 0.1) !important;
    }
    #delsa-site-header .delsa-h__wrap {
      max-width: 1200px !important;
      margin: 0 auto !important;
      padding: 0 1.25rem !important;
    }
    #delsa-site-header .delsa-h__bar {
      display: flex !important;
      align-items: center !important;
      justify-content: space-between !important;
      min-height: 72px !important;
      gap: 1rem !important;
    }
    #delsa-site-header .delsa-h__logo {
      display: flex !important;
      align-items: center !important;
      gap: 0.75rem !important;
      text-decoration: none !important;
      flex-shrink: 0 !important;
    }
    #delsa-site-header .delsa-h__logo img {
      width: 40px !important;
      height: 40px !important;
      object-fit: contain !important;
      display: block !important;
    }
    #delsa-site-header .delsa-h__title {
      display: block !important;
      font-size: 16px !important;
      font-weight: 700 !important;
      color: #1B4283 !important;
      line-height: 1.3 !important;
    }
    #delsa-site-header .delsa-h__tag {
      display: none !important;
      font-size: 12px !important;
      color: rgba(27, 66, 131, 0.62) !important;
      font-weight: 400 !important;
    }
    @media (min-width: 640px) {
      #delsa-site-header .delsa-h__tag { display: block !important; }
    }
    #delsa-site-header .delsa-h__nav {
      display: none !important;
      align-items: center !important;
      gap: 1.5rem !important;
      flex: 1 1 auto !important;
      justify-content: center !important;
      margin: 0 1rem !important;
    }
    @media (min-width: 1100px) {
      #delsa-site-header .delsa-h__nav { display: flex !important; }
    }
    #delsa-site-header .delsa-h__nav > a,
    #delsa-site-header .delsa-h__drop > button {
      position: relative !important;
      font-size: 14px !important;
      font-weight: 500 !important;
      color: #1B4283 !important;
      text-decoration: none !important;
      background: none !important;
      border: 0 !important;
      padding: 0 !important;
      cursor: pointer !important;
      font-family: inherit !important;
      white-space: nowrap !important;
    }
    #delsa-site-header .delsa-h__nav > a:hover,
    #delsa-site-header .delsa-h__drop > button:hover {
      color: #3ab5ad !important;
    }
    #delsa-site-header .delsa-h__drop { position: relative !important; }
    #delsa-site-header .delsa-h__drop > button {
      display: inline-flex !important;
      align-items: center !important;
      gap: 0.25rem !important;
    }
    #delsa-site-header .delsa-h__menu {
      position: absolute !important;
      top: calc(100% + 12px) !important;
      right: 0 !important;
      min-width: 230px !important;
      background: #fff !important;
      border: 1px solid rgba(27, 66, 131, 0.1) !important;
      border-radius: 16px !important;
      box-shadow: 0 16px 48px rgba(27, 66, 131, 0.12) !important;
      opacity: 0 !important;
      visibility: hidden !important;
      transform: translateY(8px) !important;
      transition: 0.25s ease !important;
      z-index: 60 !important;
      overflow: hidden !important;
    }
    #delsa-site-header .delsa-h__drop:hover .delsa-h__menu,
    #delsa-site-header .delsa-h__drop:focus-within .delsa-h__menu {
      opacity: 1 !important;
      visibility: visible !important;
      transform: none !important;
    }
    #delsa-site-header .delsa-h__menu a {
      display: block !important;
      padding: 11px 18px !important;
      font-size: 13px !important;
      color: #1B4283 !important;
      text-decoration: none !important;
      border-bottom: 1px solid rgba(27, 66, 131, 0.06) !important;
    }
    #delsa-site-header .delsa-h__menu a:last-child { border-bottom: 0 !important; }
    #delsa-site-header .delsa-h__menu a:hover {
      background: #eef8f7 !important;
      color: #3ab5ad !important;
    }
    #delsa-site-header .delsa-h__actions {
      display: flex !important;
      align-items: center !important;
      gap: 0.75rem !important;
      flex-shrink: 0 !important;
    }
    #delsa-site-header .delsa-h__phone {
      display: none !important;
      align-items: center !important;
      gap: 0.4rem !important;
      font-size: 13px !important;
      color: #1B4283 !important;
      text-decoration: none !important;
      font-weight: 500 !important;
      white-space: nowrap !important;
    }
    #delsa-site-header .delsa-h__phone svg {
      width: 16px !important;
      height: 16px !important;
      flex-shrink: 0 !important;
    }
    @media (min-width: 480px) {
      #delsa-site-header .delsa-h__phone { display: inline-flex !important; }
    }
    #delsa-site-header .delsa-h__cta {
      display: none !important;
      align-items: center !important;
      justify-content: center !important;
      padding: 0.65rem 1.15rem !important;
      font-size: 13px !important;
      font-weight: 600 !important;
      color: #122f5c !important;
      background: #4CC9C0 !important;
      border-radius: 999px !important;
      text-decoration: none !important;
      white-space: nowrap !important;
      border: 0 !important;
      font-family: inherit !important;
    }
    @media (min-width: 480px) {
      #delsa-site-header .delsa-h__cta { display: inline-flex !important; }
    }
    #delsa-site-header .delsa-h__burger {
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      width: 40px !important;
      height: 40px !important;
      background: none !important;
      border: 0 !important;
      color: #1B4283 !important;
      cursor: pointer !important;
      font-size: 22px !important;
      line-height: 1 !important;
    }
    @media (min-width: 1100px) {
      #delsa-site-header .delsa-h__burger { display: none !important; }
    }

    #delsa-sidebar-backdrop {
      position: fixed !important;
      inset: 0 !important;
      background: rgba(18, 47, 92, 0.55) !important;
      opacity: 0 !important;
      visibility: hidden !important;
      transition: 0.35s ease !important;
      z-index: 100000 !important;
    }
    #delsa-sidebar-backdrop.open {
      opacity: 1 !important;
      visibility: visible !important;
    }
    #delsa-sidebar {
      position: fixed !important;
      top: 0 !important;
      right: 0 !important;
      width: min(320px, 88vw) !important;
      height: 100% !important;
      background: #1B4283 !important;
      transform: translateX(100%) !important;
      transition: transform 0.35s ease !important;
      z-index: 100001 !important;
      overflow-y: auto !important;
      font-family: 'Vazirmatn', Tahoma, sans-serif !important;
    }
    #delsa-sidebar.open { transform: none !important; }
    #delsa-sidebar .delsa-s__head {
      padding: 1.35rem !important;
      border-bottom: 1px solid rgba(255,255,255,0.08) !important;
    }
    #delsa-sidebar .delsa-s__row {
      display: flex !important;
      align-items: center !important;
      justify-content: space-between !important;
      margin-bottom: 1rem !important;
    }
    #delsa-sidebar .delsa-s__brand {
      display: flex !important;
      align-items: center !important;
      gap: 0.7rem !important;
      color: #fff !important;
      text-decoration: none !important;
      font-weight: 600 !important;
    }
    #delsa-sidebar .delsa-s__brand img {
      width: 36px !important;
      height: 36px !important;
    }
    #delsa-sidebar .delsa-s__close {
      background: none !important;
      border: 0 !important;
      color: rgba(255,255,255,0.55) !important;
      cursor: pointer !important;
      font-size: 20px !important;
    }
    #delsa-sidebar .delsa-s__tag {
      margin: 0 !important;
      font-size: 12px !important;
      color: rgba(255,255,255,0.35) !important;
      line-height: 1.6 !important;
    }
    #delsa-sidebar .delsa-s__nav {
      padding: 1rem 1.35rem 2rem !important;
      display: flex !important;
      flex-direction: column !important;
    }
    #delsa-sidebar .delsa-s__nav a {
      display: block !important;
      padding: 0.7rem 0 !important;
      color: rgba(255,255,255,0.85) !important;
      text-decoration: none !important;
      font-size: 14px !important;
      border-bottom: 1px solid rgba(255,255,255,0.06) !important;
    }
    #delsa-sidebar .delsa-s__nav a.sub {
      padding-right: 1rem !important;
      font-size: 13px !important;
      color: rgba(255,255,255,0.55) !important;
      border: 0 !important;
    }
    #delsa-sidebar .delsa-s__nav a:hover { color: #4CC9C0 !important; }
    #delsa-sidebar .delsa-s__label {
      margin: 1rem 0 0.3rem !important;
      font-size: 11px !important;
      font-weight: 700 !important;
      letter-spacing: 0.08em !important;
      color: rgba(76,201,192,0.65) !important;
    }

    /* هدر قدیمی داخل HTML صفحه اصلی */
    body.home header#site-header:not(#delsa-site-header),
    body.home #site-header.site-header {
      display: none !important;
    }
  </style>
  <?php
}, 99);

/** HTML هدر — تم Doctor گاهی wp_body_open ندارد، پس در footer چاپ و با JS می‌بریم بالا */
function delsa_global_header_markup() {
  static $done = false;
  if ($done || is_admin()) {
    return '';
  }
  $done = true;

  $home = home_url('/');
  $book = home_url('/%d9%81%d8%b1%d9%85-%d9%86%d9%88%d8%a8%d8%aa-%d8%af%d9%87%db%8c/');
  $about = home_url('/%d8%af%d8%b1%d8%a8%d8%a7%d8%b1%d9%87-%d9%85%d8%a7/');
  $team = home_url('/%d9%85%d8%b4%d8%a7%d9%88%d8%b1%d8%a7%d9%86/');
  $blog = home_url('/blog/');
  $logo = 'https://delsaclinic.com/wp-content/uploads/2021/12/DelsaClinicLogo-120x120.png';

  $depts = [
    home_url('/%d8%af%d9%be%d8%a7%d8%b1%d8%aa%d9%85%d8%a7%d9%86-%d8%b1%d9%88%d8%a7%d9%86%d9%be%d8%b2%d8%b4%da%a9%db%8c/') => 'روان‌پزشکی',
    home_url('/%d8%af%d9%be%d8%a7%d8%b1%d8%aa%d9%85%d8%a7%d9%86-%d8%b1%d9%88%d8%a7%d9%86-%d8%af%d8%b1%d9%85%d8%a7%d9%86%db%8c/') => 'روان‌درمانی',
    home_url('/%d8%af%d9%be%d8%a7%d8%b1%d8%aa%d9%85%d8%a7%d9%86-%d8%b2%d9%88%d8%ac-%d9%88-%d8%ae%d8%a7%d9%86%d9%88%d8%a7%d8%af%d9%87/') => 'زوج و خانواده',
    home_url('/%d8%af%d9%be%d8%a7%d8%b1%d8%aa%d9%85%d8%a7%d9%86-%da%a9%d9%88%d8%af%da%a9-%d9%88-%d9%86%d9%88%d8%ac%d9%88%d8%a7%d9%86/') => 'کودک و نوجوان',
    home_url('/%d8%af%d9%be%d8%a7%d8%b1%d8%aa%d9%85%d8%a7%d9%86-%d8%aa%d8%b1%da%a9-%d8%a7%d8%b9%d8%aa%db%8c%d8%a7%d8%af/') => 'ترک اعتیاد',
    home_url('/%d8%af%d9%be%d8%a7%d8%b1%d8%aa%d9%85%d8%a7%d9%86-%d9%85%d8%b4%d8%a7%d9%88%d8%b1%d9%87-%d8%b4%d8%ba%d9%84%db%8c/') => 'مشاوره شغلی',
  ];

  ob_start();
  ?>
  <div id="delsa-header-root">
  <header id="delsa-site-header">
    <div class="delsa-h__wrap">
      <div class="delsa-h__bar">
        <a class="delsa-h__logo" href="<?php echo esc_url($home); ?>">
          <img src="<?php echo esc_url($logo); ?>" alt="لوگوی کلینیک دلسا" width="40" height="40">
          <span>
            <span class="delsa-h__title">کلینیک دلسا</span>
            <span class="delsa-h__tag">گروه تخصصی روان‌شناسی</span>
          </span>
        </a>

        <nav class="delsa-h__nav" aria-label="منوی اصلی">
          <a href="<?php echo esc_url($home); ?>">خانه</a>
          <div class="delsa-h__drop">
            <button type="button">دپارتمان‌ها ▾</button>
            <div class="delsa-h__menu">
              <?php foreach ($depts as $url => $label) : ?>
                <a href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a>
              <?php endforeach; ?>
            </div>
          </div>
          <a href="<?php echo esc_url($team); ?>">مشاوران</a>
          <a href="<?php echo esc_url($blog); ?>">وبلاگ</a>
          <a href="<?php echo esc_url($about); ?>">درباره ما</a>
          <a href="<?php echo esc_url($book); ?>">فرم نوبت‌دهی</a>
        </nav>

        <div class="delsa-h__actions">
          <a class="delsa-h__phone" href="tel:+989025680372">
            <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
            <span dir="ltr">۰۹۰۲-۵۶۸۰۳۷۲</span>
          </a>
          <a class="delsa-h__cta" href="<?php echo esc_url($book); ?>">درخواست وقت ملاقات</a>
          <button type="button" class="delsa-h__burger" id="delsa-sidebar-open" aria-label="باز کردن منو">☰</button>
        </div>
      </div>
    </div>
  </header>

  <div id="delsa-sidebar-backdrop" aria-hidden="true"></div>
  <aside id="delsa-sidebar" aria-hidden="true">
    <div class="delsa-s__head">
      <div class="delsa-s__row">
        <a class="delsa-s__brand" href="<?php echo esc_url($home); ?>">
          <img src="<?php echo esc_url($logo); ?>" alt="" width="36" height="36">
          <span>کلینیک دلسا</span>
        </a>
        <button type="button" class="delsa-s__close" id="delsa-sidebar-close" aria-label="بستن">✕</button>
      </div>
      <p class="delsa-s__tag">گروه تخصصی مشاوره و خدمات روان‌شناختی</p>
    </div>
    <nav class="delsa-s__nav">
      <a href="<?php echo esc_url($home); ?>">خانه</a>
      <p class="delsa-s__label">دپارتمان‌ها</p>
      <?php foreach ($depts as $url => $label) : ?>
        <a class="sub" href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a>
      <?php endforeach; ?>
      <a href="<?php echo esc_url($team); ?>">مشاوران</a>
      <a href="<?php echo esc_url($blog); ?>">وبلاگ</a>
      <a href="<?php echo esc_url($about); ?>">درباره ما</a>
      <a href="<?php echo esc_url($book); ?>">فرم نوبت‌دهی</a>
    </nav>
  </aside>
  </div>
  <?php
  return ob_get_clean();
}

add_action('wp_body_open', function () {
  echo delsa_global_header_markup();
}, 1);

add_action('wp_footer', function () {
  $html = delsa_global_header_markup();
  if ($html) {
    echo $html;
  }
  ?>
  <script>
  (function () {
    var root = document.getElementById('delsa-header-root');
    if (!root) return;

    var box = document.querySelector('.boxed-container');
    if (box && box.firstChild) {
      box.insertBefore(root, box.firstChild);
    } else {
      document.body.insertBefore(root, document.body.firstChild);
    }

    document.querySelectorAll('header.header_s, .top-header').forEach(function (el) {
      el.style.setProperty('display', 'none', 'important');
    });

    var header = document.getElementById('delsa-site-header');
    if (header) {
      header.style.setProperty('position', 'fixed', 'important');
      header.style.setProperty('top', document.body.classList.contains('admin-bar') ? (window.innerWidth <= 782 ? '46px' : '32px') : '0', 'important');
      header.style.setProperty('transform', 'none', 'important');
      header.style.setProperty('opacity', '1', 'important');
      header.style.setProperty('visibility', 'visible', 'important');

      var onScroll = function () {
        header.classList.toggle('scrolled', window.scrollY > 12);
      };
      window.addEventListener('scroll', onScroll, { passive: true });
      onScroll();
    }

    var side = document.getElementById('delsa-sidebar');
    var back = document.getElementById('delsa-sidebar-backdrop');
    var openBtn = document.getElementById('delsa-sidebar-open');
    var closeBtn = document.getElementById('delsa-sidebar-close');
    if (!side || !back || !openBtn || !closeBtn) return;

    function open() {
      side.classList.add('open');
      back.classList.add('open');
      document.body.style.overflow = 'hidden';
    }
    function close() {
      side.classList.remove('open');
      back.classList.remove('open');
      document.body.style.overflow = '';
    }
    openBtn.addEventListener('click', open);
    closeBtn.addEventListener('click', close);
    back.addEventListener('click', close);
    side.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', close); });
  })();
  </script>
  <?php
}, 5);
