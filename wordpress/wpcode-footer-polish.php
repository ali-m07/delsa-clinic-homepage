<?php
/**
 * WPCode — فوتر: نقشه واقعی + ترتیب دسترسی سریع + ارقام فارسی کپی‌رایت
 * نوع: PHP Snippet · Run Everywhere (یا فقط Frontend)
 * VERSION 1.0.0
 */

if (!defined('ABSPATH')) {
  exit;
}

add_action('wp_footer', function () {
  if (is_admin()) {
    return;
  }
  $map_src = 'https://www.openstreetmap.org/export/embed.html?bbox=51.365%2C35.774%2C51.385%2C35.784&layer=mapnik&marker=35.779%2C51.375';
  ?>
  <script>
  (function () {
    function toFa(s) {
      return String(s).replace(/\d/g, function (d) { return "۰۱۲۳۴۵۶۷۸۹"[d]; });
    }

    var foot = document.querySelector("footer#contact, footer.site-footer, .delsa-footer-wrap");
    if (!foot) return;

    /* ارقام فارسی در کپی‌رایت و متون ساده فوتر */
    foot.querySelectorAll("p, span, a, li, h4").forEach(function (node) {
      if (node.children.length) return;
      if (!/\d/.test(node.textContent || "")) return;
      node.textContent = toFa(node.textContent);
    });

    /* ترتیب دسترسی سریع = هدر:
       خانه، فرم نوبت‌دهی، درباره ما، وبلاگ، مشاوران */
    var quick = foot.querySelector("ul.footer-quick, .site-footer__links ul, .footer-brand ul");
    if (quick) {
      var wanted = ["خانه", "فرم نوبت", "درباره", "وبلاگ", "مشاوران"];
      var items = Array.prototype.slice.call(quick.querySelectorAll("li"));
      function score(li) {
        var t = (li.textContent || "").trim();
        for (var i = 0; i < wanted.length; i++) {
          if (t.indexOf(wanted[i]) !== -1) return i;
        }
        return 99;
      }
      items.sort(function (a, b) { return score(a) - score(b); });
      items.forEach(function (li) { quick.appendChild(li); });
    }

    /* اگر iframe نقشه نیست، اضافه کن */
    if (!foot.querySelector("iframe[src*='openstreetmap'], iframe[src*='google.com/maps'], .delsa-footer-map iframe")) {
      var host = foot.querySelector(".footer-col--map, .footer-map, .site-footer__map");
      if (!host) {
        var grid = foot.querySelector(".site-footer__grid, .footer-grid, .grid");
        host = document.createElement("div");
        host.className = "delsa-footer-map footer-map";
        if (grid) grid.appendChild(host);
        else foot.appendChild(host);
      } else {
        host.classList.add("delsa-footer-map");
      }
      if (!host.querySelector("iframe")) {
        var iframe = document.createElement("iframe");
        iframe.src = <?php echo wp_json_encode($map_src); ?>;
        iframe.loading = "lazy";
        iframe.title = "موقعیت کلینیک دلسا";
        iframe.setAttribute("referrerpolicy", "no-referrer-when-downgrade");
        host.appendChild(iframe);
      }
    }
  })();
  </script>
  <?php
}, 60);
