<?php
if (!defined('ABSPATH')) {
  exit;
}

add_action('wp_head', function () {
  if (is_admin()) {
    return;
  }
  ?>
  <style id="delsa-global-footer-css">
    /* مخفی کردن فوتر تم Doctor */
    #footer-main,
    .footer-main,
    footer.footer_s,
    .footer_s,
    .footer-section,
    .site-footer:not(#delsa-site-footer):not(#contact),
    .copyright,
    .footer-copyright,
    .footer-bottom,
    #footer-bottom,
    .boxed-container > footer:not(#delsa-site-footer),
    body .footer-widgets,
    .footer-widget-area {
      display: none !important;
      height: 0 !important;
      overflow: hidden !important;
      visibility: hidden !important;
      pointer-events: none !important;
      margin: 0 !important;
      padding: 0 !important;
      border: 0 !important;
    }

    /* اگر صفحه اصلی خودش فوتر دلسا دارد، فوتر گلوبال را نشان نده */
    body.home #delsa-site-footer,
    body.page-template-elementor_header_footer.home #delsa-site-footer {
      /* home معمولاً فوتر داخل HTML ویجت دارد؛ پایین با JS تصمیم می‌گیریم */
    }

    #delsa-site-footer {
      display: block !important;
      width: 100% !important;
      max-width: 100% !important;
      margin: 0 !important;
      padding: 0 !important;
      background: #122f5c !important;
      color: #fff !important;
      font-family: 'Vazirmatn', Tahoma, sans-serif !important;
      box-sizing: border-box !important;
      clear: both !important;
      position: relative !important;
      z-index: 20 !important;
    }
    #delsa-site-footer *,
    #delsa-site-footer *::before,
    #delsa-site-footer *::after {
      box-sizing: border-box !important;
    }
    #delsa-site-footer .delsa-f__inner {
      width: 100% !important;
      max-width: 1200px !important;
      margin: 0 auto !important;
      padding: 3rem 1.25rem 1.5rem !important;
    }
    #delsa-site-footer .delsa-f__grid {
      display: grid !important;
      grid-template-columns: 1fr !important;
      gap: 2rem !important;
      width: 100% !important;
      min-width: 0 !important;
    }
    @media (min-width: 900px) {
      #delsa-site-footer .delsa-f__grid {
        grid-template-columns: 1fr 1.1fr 1fr !important;
        gap: 2.25rem !important;
        align-items: start !important;
      }
    }
    #delsa-site-footer .delsa-f__brand-row {
      display: flex !important;
      align-items: center !important;
      gap: 0.75rem !important;
      margin-bottom: 0.75rem !important;
    }
    #delsa-site-footer .delsa-f__logo {
      width: 48px !important;
      height: 48px !important;
      border-radius: 12px !important;
      background: rgba(255,255,255,.08) !important;
      padding: 6px !important;
      object-fit: contain !important;
      display: block !important;
    }
    #delsa-site-footer .delsa-f__name {
      margin: 0 !important;
      font-size: 15px !important;
      font-weight: 700 !important;
      color: #fff !important;
      line-height: 1.35 !important;
    }
    #delsa-site-footer .delsa-f__tag {
      margin: 0 0 1.25rem !important;
      font-size: 13px !important;
      line-height: 1.7 !important;
      color: rgba(255,255,255,.4) !important;
    }
    #delsa-site-footer h4 {
      margin: 0 0 0.85rem !important;
      font-size: 11px !important;
      font-weight: 700 !important;
      letter-spacing: .08em !important;
      text-transform: uppercase !important;
      color: rgba(76,201,192,.7) !important;
    }
    #delsa-site-footer ul {
      list-style: none !important;
      margin: 0 !important;
      padding: 0 !important;
    }
    #delsa-site-footer ul li {
      margin: 0 0 0.45rem !important;
    }
    #delsa-site-footer a {
      color: rgba(255,255,255,.65) !important;
      text-decoration: none !important;
      font-size: 13px !important;
      line-height: 1.7 !important;
      transition: color .25s ease !important;
    }
    #delsa-site-footer a:hover {
      color: #4CC9C0 !important;
    }
    #delsa-site-footer .delsa-f__title {
      margin: 0 0 0.4rem !important;
      font-size: 14px !important;
      font-weight: 600 !important;
      color: rgba(255,255,255,.92) !important;
    }
    #delsa-site-footer .delsa-f__address {
      margin: 0 0 1rem !important;
      font-size: 13px !important;
      line-height: 1.85 !important;
      color: rgba(255,255,255,.55) !important;
    }
    #delsa-site-footer .delsa-f__maps {
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      margin: 0 0 1.25rem !important;
      padding: 0.55rem 1rem !important;
      font-size: 12px !important;
      font-weight: 700 !important;
      color: #122f5c !important;
      background: #4CC9C0 !important;
      border-radius: 999px !important;
    }
    #delsa-site-footer .delsa-f__maps:hover {
      color: #122f5c !important;
      background: #6dd9d2 !important;
    }
    #delsa-site-footer .delsa-f__label {
      margin: 0 0 0.25rem !important;
      font-size: 11px !important;
      font-weight: 600 !important;
      color: rgba(76,201,192,.65) !important;
    }
    #delsa-site-footer .delsa-f__phones,
    #delsa-site-footer .delsa-f__email {
      margin: 0 0 1rem !important;
    }
    #delsa-site-footer .delsa-f__phone {
      display: block !important;
      direction: ltr !important;
      text-align: right !important;
      font-size: 14px !important;
      font-weight: 600 !important;
      color: rgba(255,255,255,.78) !important;
    }
    #delsa-site-footer .delsa-f__phone--sub {
      font-weight: 500 !important;
      color: rgba(255,255,255,.55) !important;
      font-size: 13px !important;
    }
    #delsa-site-footer .delsa-f__map {
      border-radius: 16px !important;
      overflow: hidden !important;
      min-height: 180px !important;
      background: rgba(0,0,0,.22) !important;
      border: 1px solid rgba(255,255,255,.08) !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
    }
    #delsa-site-footer .delsa-f__map-load {
      appearance: none !important;
      border: 1px solid rgba(255,255,255,.28) !important;
      background: rgba(255,255,255,.08) !important;
      color: #fff !important;
      font-family: inherit !important;
      font-size: 13px !important;
      font-weight: 600 !important;
      padding: .7rem 1.1rem !important;
      border-radius: 999px !important;
      cursor: pointer !important;
    }
    #delsa-site-footer .delsa-f__map-load:hover {
      background: rgba(76,201,192,.25) !important;
      border-color: rgba(76,201,192,.55) !important;
    }
    #delsa-site-footer .delsa-f__map iframe {
      width: 100% !important;
      height: 100% !important;
      min-height: 220px !important;
      border: 0 !important;
      display: block !important;
    }
    @media (min-width: 900px) {
      #delsa-site-footer .delsa-f__map,
      #delsa-site-footer .delsa-f__map iframe {
        min-height: 280px !important;
      }
    }
    #delsa-site-footer .delsa-f__bottom {
      margin-top: 2rem !important;
      padding-top: 1.25rem !important;
      border-top: 1px solid rgba(255,255,255,.08) !important;
      text-align: center !important;
    }
    #delsa-site-footer .delsa-f__bottom p {
      margin: 0 !important;
      font-size: 12px !important;
      color: rgba(255,255,255,.35) !important;
    }
  </style>
  <?php
}, 30);

function delsa_global_footer_markup() {
  $home = home_url('/');
  $logo = 'https://delsaclinic.com/wp-content/uploads/2021/12/DelsaClinicLogo-120x120.png';
  $year = (int) gmdate('Y');

  $links = [
    ['خانه', $home],
    ['مشاوران', home_url('/%d9%85%d8%b4%d8%a7%d9%88%d8%b1%d8%a7%d9%86/')],
    ['وبلاگ', home_url('/blog/')],
    ['درباره ما', home_url('/%d8%af%d8%b1%d8%a8%d8%a7%d8%b1%d9%87-%d9%85%d8%a7/')],
    ['فرم نوبت‌دهی', home_url('/%d9%81%d8%b1%d9%85-%d9%86%d9%88%d8%a8%d8%aa-%d8%af%d9%87%db%8c/')],
  ];

  ob_start();
  ?>
  <footer id="delsa-site-footer" role="contentinfo">
    <div class="delsa-f__inner">
      <div class="delsa-f__grid">
        <div>
          <div class="delsa-f__brand-row">
            <img class="delsa-f__logo" src="<?php echo esc_url($logo); ?>" alt="لوگوی کلینیک دلسا" width="48" height="48" loading="lazy">
            <p class="delsa-f__name">کلینیک دلسا</p>
          </div>
          <p class="delsa-f__tag">گروه تخصصی مشاوره و خدمات روان‌شناختی</p>
          <h4>دسترسی سریع</h4>
          <ul>
            <?php foreach ($links as $item) : ?>
              <li><a href="<?php echo esc_url($item[1]); ?>"><?php echo esc_html($item[0]); ?></a></li>
            <?php endforeach; ?>
          </ul>
        </div>

        <div>
          <h4>ارتباط با ما</h4>
          <p class="delsa-f__title">کلینیک دلسا، سعادت‌آباد</p>
          <p class="delsa-f__address">سعادت‌آباد، خیابان علامه جنوبی، نبش خیابان حق‌طلب غربی، پلاک ۸۰، ساختمان علامه، طبقه ۶، واحد ۴</p>
          <a class="delsa-f__maps" href="https://maps.google.com/?q=35.779,51.375" target="_blank" rel="noopener noreferrer">مسیریابی در Google Maps</a>
          <div class="delsa-f__phones">
            <p class="delsa-f__label">تلفن</p>
            <a class="delsa-f__phone" href="tel:+989025680372">۰۹۰۲-۵۶۸۰۳۷۲</a>
            <a class="delsa-f__phone delsa-f__phone--sub" href="tel:+982188682003">۰۲۱-۸۸۶۸۲۰۰۳</a>
          </div>
          <div class="delsa-f__email">
            <p class="delsa-f__label">ایمیل</p>
            <a class="delsa-f__phone" href="mailto:info@delsaclinic.com">info@delsaclinic.com</a>
          </div>
        </div>

        <div class="delsa-f__map" id="delsa-f-map">
          <button type="button" class="delsa-f__map-load" id="delsa-f-map-btn" aria-label="نمایش نقشه">
            نمایش نقشه موقعیت کلینیک
          </button>
        </div>
      </div>

      <div class="delsa-f__bottom">
        <p>© <?php echo esc_html((string) $year); ?> کلینیک دلسا. همه حقوق محفوظ است.</p>
      </div>
    </div>
  </footer>
  <?php
  return ob_get_clean();
}

add_action('wp_footer', function () {
  if (is_admin()) {
    return;
  }
  echo delsa_global_footer_markup();
  ?>
  <script>
  (function () {
    // مخفی کردن فوتر تم اگر هنوز دیده می‌شود
    var hideSel = '#footer-main, .footer-main, footer.footer_s, .copyright, .footer-copyright, #footer-bottom';
    document.querySelectorAll(hideSel).forEach(function (el) {
      el.style.setProperty('display', 'none', 'important');
    });

    var ours = document.getElementById('delsa-site-footer');
    if (!ours) return;

    // اگر صفحه خودش فوتر دلسا دارد (مثل صفحه اصلی)، گلوبال را بردار تا دوبل نشود
    var existing = document.querySelector('.delsa-footer-wrap, footer#contact.site-footer');
    if (existing && !existing.contains(ours) && existing !== ours) {
      ours.remove();
      return;
    }

    // ببر انتهای محتوا
    var box = document.querySelector('.boxed-container') || document.body;
    box.appendChild(ours);

    // نقشه فقط با کلیک لود شود (OpenStreetMap در هر صفحه سایت را کند می‌کرد)
    var mapBox = document.getElementById('delsa-f-map');
    var mapBtn = document.getElementById('delsa-f-map-btn');
    if (mapBox && mapBtn) {
      mapBtn.addEventListener('click', function () {
        var iframe = document.createElement('iframe');
        iframe.src = 'https://www.openstreetmap.org/export/embed.html?bbox=51.365%2C35.774%2C51.385%2C35.784&layer=mapnik&marker=35.779%2C51.375';
        iframe.title = 'موقعیت کلینیک دلسا، سعادت‌آباد';
        iframe.loading = 'lazy';
        mapBox.innerHTML = '';
        mapBox.appendChild(iframe);
      }, { once: true });
    }
  })();
  </script>
  <?php
}, 20);