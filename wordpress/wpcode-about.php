<?php
/**
 * WPCode Snippet — صفحه درباره ما
 * نوع: PHP Snippet · Run Everywhere
 * شورت‌کد: [delsa_about]
 *
 * فقط UI درباره ما — CPT مشاوران را ثبت نمی‌کند.
 * تیم از پست‌های publish نوع delsa_consultant خوانده می‌شود.
 */

if (!defined('ABSPATH')) {
  exit;
}

final class Delsa_About_Page {
  const CPT = 'delsa_consultant';
  const VERSION = '3.2.0';

  public static function init() {
    add_shortcode('delsa_about', [__CLASS__, 'shortcode_about']);
    add_action('wp_enqueue_scripts', [__CLASS__, 'assets'], 45);
    add_filter('body_class', [__CLASS__, 'body_class']);
  }

  public static function is_about_request() {
    if (!is_singular()) {
      return false;
    }
    $post = get_post();
    if (!$post) {
      return false;
    }
    if (has_shortcode((string) $post->post_content, 'delsa_about')) {
      return true;
    }
    $slug = (string) $post->post_name;
    $title = (string) $post->post_title;
    $about_slugs = ['about-us', 'about', 'درباره-ما', 'درباره_ما'];
    if (in_array($slug, $about_slugs, true) || $title === 'درباره ما' || $title === 'About Us' || $title === 'About') {
      return true;
    }
    $data = get_post_meta($post->ID, '_elementor_data', true);
    if ($data) {
      $blob = is_string($data) ? $data : wp_json_encode($data);
      if (strpos($blob, 'delsa_about') !== false) {
        return true;
      }
    }
    return false;
  }

  public static function body_class($classes) {
    if (self::is_about_request()) {
      $classes[] = 'delsa-about-page';
    }
    return $classes;
  }

  public static function assets() {
    if (!self::is_about_request()) {
      return;
    }
    wp_register_style('delsa-about-page', false, [], self::VERSION);
    wp_enqueue_style('delsa-about-page');
    wp_add_inline_style('delsa-about-page', self::css());

    wp_register_script('delsa-about-page', false, [], self::VERSION, true);
    wp_enqueue_script('delsa-about-page');
    wp_add_inline_script('delsa-about-page', self::js());
  }

  private static function img($file) {
    $map = [
      'hero' => 'https://ali-m07.github.io/delsa-clinic-homepage/static/img/clinic/room-01.png',
      'story' => 'https://ali-m07.github.io/delsa-clinic-homepage/static/img/clinic/room-08.png',
      'a' => 'https://ali-m07.github.io/delsa-clinic-homepage/static/img/clinic/room-05.png',
      'b' => 'https://ali-m07.github.io/delsa-clinic-homepage/static/img/clinic/room-02.png',
      'c' => 'https://ali-m07.github.io/delsa-clinic-homepage/static/img/clinic/room-06.png',
    ];
    return $map[$file] ?? $map['hero'];
  }

  private static function consultants_url() {
    $archive = get_post_type_archive_link(self::CPT);
    if (is_string($archive) && $archive !== '') {
      return $archive;
    }
    return home_url('/consultant/');
  }

  private static function book_url() {
    return home_url('/%d9%81%d8%b1%d9%85-%d9%86%d9%88%d8%a8%d8%aa-%d8%af%d9%87%db%8c/');
  }

  private static function get_consultants($limit = 5) {
    if (!post_type_exists(self::CPT)) {
      return [];
    }
    $q = new WP_Query([
      'post_type' => self::CPT,
      'post_status' => 'publish',
      'posts_per_page' => (int) $limit,
      'orderby' => ['menu_order' => 'ASC', 'title' => 'ASC'],
      'no_found_rows' => true,
    ]);

    $items = [];
    foreach ($q->posts as $p) {
      $role = (string) get_post_meta($p->ID, '_delsa_role', true);
      if ($role === '') {
        $role = (string) get_post_meta($p->ID, 'delsa_specialty', true);
      }
      if ($role === '') {
        $role = trim(wp_strip_all_tags((string) $p->post_excerpt));
      }
      if (mb_strlen($role) > 70) {
        $role = mb_substr($role, 0, 70) . '…';
      }
      $items[] = [
        'name' => get_the_title($p),
        'url' => get_permalink($p),
        'role' => $role,
        'image' => get_the_post_thumbnail_url($p, 'medium_large') ?: get_the_post_thumbnail_url($p, 'large') ?: '',
      ];
    }
    wp_reset_postdata();
    return $items;
  }

  public static function shortcode_about($atts = []) {
    $atts = shortcode_atts([
      'show_team' => '1',
    ], $atts, 'delsa_about');

    $book = self::book_url();
    $list = self::consultants_url();
    $team = $atts['show_team'] === '1' ? self::get_consultants(5) : [];

    ob_start();
    ?>
    <div class="delsa-about" dir="rtl" data-da-version="<?php echo esc_attr(self::VERSION); ?>">
      <section class="da-hero" aria-labelledby="da-title">
        <div class="da-hero__media" aria-hidden="true">
          <img class="da-hero__photo" src="<?php echo esc_url(self::img('hero')); ?>" alt="" width="1600" height="900" decoding="async" fetchpriority="high">
        </div>
        <div class="da-hero__veil" aria-hidden="true"></div>
        <div class="da-hero__inner">
          <h1 id="da-title" data-da-reveal>کلینیک دلسا</h1>
          <p class="da-hero__lead" data-da-reveal>جایی برای شنیده شدن، بدون قضاوت. مشاوره و روان‌شناسی در سعادت‌آباد.</p>
          <div class="da-hero__actions" data-da-reveal>
            <a class="da-btn da-btn--primary" href="<?php echo esc_url($book); ?>">رزرو وقت</a>
            <a class="da-btn da-btn--ghost" href="<?php echo esc_url($list); ?>">مشاوران</a>
          </div>
        </div>
      </section>

      <section class="da-section da-story" data-da-reveal>
        <div class="da-wrap da-story__grid">
          <div class="da-story__copy">
            <h2>اینجا کنار شماییم</h2>
            <p>دلسا گروهی از مشاوران و روان‌شناسان است که در مسیر فردی، زوجی و خانواده همراهتان می‌مانند. کارمان علمی است، اما زبانش انسانی.</p>
            <p>فضای کلینیک آرام و محرمانه است تا بتوانید راحت حرف بزنید و قدم‌به‌قدم جلو بروید.</p>
          </div>
          <figure class="da-story__media">
            <img src="<?php echo esc_url(self::img('story')); ?>" alt="فضای کلینیک دلسا" width="880" height="660" loading="lazy" decoding="async">
          </figure>
        </div>
      </section>

      <section class="da-strip" data-da-reveal aria-label="فضای کلینیک">
        <div class="da-strip__track">
          <img src="<?php echo esc_url(self::img('a')); ?>" alt="" width="640" height="420" loading="lazy" decoding="async">
          <img src="<?php echo esc_url(self::img('b')); ?>" alt="" width="640" height="420" loading="lazy" decoding="async">
          <img src="<?php echo esc_url(self::img('c')); ?>" alt="" width="640" height="420" loading="lazy" decoding="async">
        </div>
      </section>

      <?php if ($team) : ?>
      <section class="da-section da-team" data-da-reveal>
        <div class="da-wrap">
          <div class="da-team__head">
            <h2>تیم ما</h2>
            <a class="da-textlink" href="<?php echo esc_url($list); ?>">همه مشاوران</a>
          </div>
          <div class="da-team__row">
            <?php foreach ($team as $person) : ?>
              <a class="da-face" href="<?php echo esc_url($person['url']); ?>">
                <span class="da-face__photo">
                  <?php if ($person['image'] !== '') : ?>
                    <img src="<?php echo esc_url($person['image']); ?>" alt="<?php echo esc_attr($person['name']); ?>" width="200" height="240" loading="lazy" decoding="async">
                  <?php endif; ?>
                </span>
                <span class="da-face__name"><?php echo esc_html($person['name']); ?></span>
                <?php if ($person['role'] !== '') : ?>
                  <span class="da-face__role"><?php echo esc_html($person['role']); ?></span>
                <?php endif; ?>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </section>
      <?php endif; ?>

      <section class="da-section da-close" data-da-reveal>
        <div class="da-wrap da-close__box">
          <h2>وقت یک گفت‌وگوی امن است؟</h2>
          <p>نوبت حضوری یا هماهنگی از طریق فرم، هر طور راحت‌ترید.</p>
          <a class="da-btn da-btn--primary" href="<?php echo esc_url($book); ?>">درخواست وقت ملاقات</a>
        </div>
      </section>
    </div>
    <?php
    return ob_get_clean();
  }

  private static function css() {
    return <<<'CSS'
body.delsa-about-page .entry-header,
body.delsa-about-page .page-header,
body.delsa-about-page .breadcrumb,
body.delsa-about-page .entry-title,
body.delsa-about-page #secondary,
body.delsa-about-page .widget-area,
body.delsa-about-page .sidebar-right,
body.delsa-about-page .blog-image,
body.delsa-about-page .entry-cover{display:none !important}
body.delsa-about-page #primary,
body.delsa-about-page .content-area,
body.delsa-about-page .content-left,
body.delsa-about-page .col-md-8,
body.delsa-about-page .col-md-12,
body.delsa-about-page .entry-content,
body.delsa-about-page .elementor,
body.delsa-about-page .elementor-section,
body.delsa-about-page .elementor-container,
body.delsa-about-page .elementor-widget-wrap,
body.delsa-about-page .elementor-widget-container{
  width:100% !important;max-width:100% !important;float:none !important;padding-left:0 !important;padding-right:0 !important;margin:0 !important
}
body.delsa-about-page .elementor-section.elementor-section-boxed>.elementor-container{max-width:100% !important}
body.delsa-about-page .site-content,
body.delsa-about-page #content,
body.delsa-about-page .container-fluid{padding-left:0 !important;padding-right:0 !important}

.delsa-about{
  --navy:#0F2740;--navy-deep:#0A1B2E;--teal:#1FA8A0;--teal-deep:#178F88;
  --ivory:#F7F8FA;--ivory-2:#EEF1F4;--muted:#5B6B7C;--line:rgba(15,39,64,.1);
  --font:Vazirmatn,Tahoma,sans-serif;--radius:16px;--ease:cubic-bezier(.22,1,.36,1);
  --shadow:0 14px 36px rgba(15,39,64,.08);
  font-family:var(--font);color:var(--navy);background:var(--ivory);
  width:100vw;max-width:100vw;margin-right:calc(50% - 50vw);margin-left:calc(50% - 50vw);
  overflow-x:clip;box-sizing:border-box;line-height:1.7;letter-spacing:-.01em
}
.delsa-about *,.delsa-about *::before,.delsa-about *::after{box-sizing:border-box}
.delsa-about img{max-width:100%;height:auto;display:block}
.delsa-about .da-wrap{width:min(1080px,calc(100% - 2rem));margin:0 auto}
.delsa-about .da-section{padding:2.25rem 0}
@media(min-width:900px){.delsa-about .da-section{padding:2.75rem 0}}

.delsa-about .da-hero{
  position:relative !important;height:260px !important;min-height:260px !important;max-height:260px !important;
  display:flex !important;align-items:flex-end !important;color:#fff;overflow:hidden !important
}
.delsa-about .da-hero__media{position:absolute;inset:0;overflow:hidden}
.delsa-about .da-hero__photo{
  width:100%;height:100%;object-fit:cover;object-position:center 38%;
  transform:scale(1.1);transform-origin:50% 40%;will-change:transform;
  animation:da-ken 12s ease-in-out infinite alternate
}
.delsa-about .da-hero__veil{position:absolute;inset:0;background:
  linear-gradient(100deg,rgba(10,27,46,.78) 0%,rgba(15,39,64,.45) 48%,rgba(15,39,64,.12) 100%),
  linear-gradient(0deg,rgba(10,27,46,.42) 0%,transparent 55%);
  opacity:0;animation:da-fade .8s var(--ease) .05s forwards
}
.delsa-about .da-hero__inner{position:relative;z-index:1;width:min(1080px,calc(100% - 2rem));margin:0 auto;padding:1.35rem 0 1.2rem}
.delsa-about .da-hero h1{margin:0 0 .45rem;font-size:clamp(1.55rem,1.3rem + .9vw,2rem);font-weight:750;line-height:1.25;letter-spacing:-.03em}
.delsa-about .da-hero__lead{margin:0 0 .9rem;max-width:28rem;font-size:.92rem;line-height:1.75;color:rgba(255,255,255,.88)}
.delsa-about .da-hero__actions{display:flex;flex-wrap:wrap;gap:.55rem}
@keyframes da-ken{from{transform:scale(1.1)}to{transform:scale(1.03)}}
@keyframes da-fade{from{opacity:0}to{opacity:1}}
@keyframes da-up{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}
.delsa-about [data-da-reveal]{opacity:0;transform:translateY(14px)}
.delsa-about [data-da-reveal].is-in{animation:da-up .65s var(--ease) forwards}
.delsa-about .da-hero [data-da-reveal].is-in:nth-child(1){animation-delay:.04s}
.delsa-about .da-hero [data-da-reveal].is-in:nth-child(2){animation-delay:.12s}
.delsa-about .da-hero [data-da-reveal].is-in:nth-child(3){animation-delay:.2s}
@media(prefers-reduced-motion:reduce){
  .delsa-about .da-hero__photo{animation:none;transform:none}
  .delsa-about .da-hero__veil,.delsa-about [data-da-reveal]{opacity:1;transform:none;animation:none}
}

.delsa-about .da-btn{
  display:inline-flex;align-items:center;justify-content:center;
  padding:.72rem 1.2rem;border-radius:12px;font-size:.875rem;font-weight:700;font-family:inherit;
  text-decoration:none;transition:transform .22s var(--ease),background .22s var(--ease),border-color .22s var(--ease)
}
.delsa-about .da-btn--primary{background:var(--teal);color:#fff}
.delsa-about .da-btn--primary:hover{background:var(--teal-deep);transform:translateY(-1px)}
.delsa-about .da-btn--ghost{background:transparent;color:#fff;border:1.5px solid rgba(255,255,255,.42)}
.delsa-about .da-btn--ghost:hover{background:rgba(255,255,255,.1)}
.delsa-about .da-textlink{font-size:.875rem;font-weight:700;color:var(--teal-deep);text-decoration:none;white-space:nowrap}

.delsa-about .da-story{background:var(--ivory)}
.delsa-about .da-story__grid{display:grid;gap:1.5rem;align-items:center}
@media(min-width:860px){.delsa-about .da-story__grid{grid-template-columns:1fr .92fr;gap:2.25rem}}
.delsa-about .da-story__copy h2{margin:0 0 .75rem;font-size:clamp(1.3rem,1.15rem + .5vw,1.65rem);font-weight:750;line-height:1.35}
.delsa-about .da-story__copy p{margin:0 0 .85rem;font-size:.98rem;line-height:1.95;color:rgba(15,39,64,.78)}
.delsa-about .da-story__copy p:last-child{margin-bottom:0}
.delsa-about .da-story__media{margin:0;border-radius:var(--radius);overflow:hidden;aspect-ratio:5/4;box-shadow:var(--shadow);background:#d7e2ea}
.delsa-about .da-story__media img{width:100%;height:100%;object-fit:cover}

.delsa-about .da-strip{padding:0 0 .25rem;background:var(--ivory)}
.delsa-about .da-strip__track{
  display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.55rem;
  width:min(1080px,calc(100% - 2rem));margin:0 auto
}
.delsa-about .da-strip__track img{
  width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:14px;background:#d7e2ea
}
@media(max-width:640px){
  .delsa-about .da-strip__track{grid-template-columns:1.1fr 1fr;gap:.45rem}
  .delsa-about .da-strip__track img:last-child{display:none}
}

.delsa-about .da-team{background:var(--ivory-2)}
.delsa-about .da-team__head{display:flex;align-items:baseline;justify-content:space-between;gap:1rem;margin:0 0 1.15rem}
.delsa-about .da-team__head h2{margin:0;font-size:clamp(1.25rem,1.1rem + .45vw,1.5rem);font-weight:750}
.delsa-about .da-team__row{
  display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:.85rem
}
@media(max-width:980px){.delsa-about .da-team__row{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:560px){.delsa-about .da-team__row{grid-template-columns:repeat(2,minmax(0,1fr));gap:.7rem}}
.delsa-about .da-face{display:flex;flex-direction:column;min-width:0;color:inherit;text-decoration:none}
.delsa-about .da-face__photo{
  display:block;margin:0 0 .55rem;border-radius:14px;overflow:hidden;aspect-ratio:4/5;background:#d7e2ea;box-shadow:var(--shadow)
}
.delsa-about .da-face__photo img{width:100%;height:100%;object-fit:cover;object-position:center top;transition:transform .5s var(--ease)}
.delsa-about .da-face:hover .da-face__photo img{transform:scale(1.04)}
.delsa-about .da-face__name{font-size:.9rem;font-weight:750;line-height:1.35}
.delsa-about .da-face__role{margin-top:.2rem;font-size:.75rem;line-height:1.55;color:var(--muted)}

.delsa-about .da-close{padding-bottom:2.75rem}
.delsa-about .da-close__box{
  text-align:center;padding:1.75rem 1.25rem;border-radius:18px;
  background:linear-gradient(135deg,rgba(15,39,64,.95),rgba(23,100,110,.9)),
    url("https://ali-m07.github.io/delsa-clinic-homepage/static/img/clinic/room-06.png") center/cover;
  color:#fff;box-shadow:var(--shadow)
}
.delsa-about .da-close__box h2{margin:0 0 .45rem;font-size:clamp(1.2rem,1.05rem + .45vw,1.45rem);font-weight:750}
.delsa-about .da-close__box p{margin:0 0 1rem;color:rgba(255,255,255,.84);font-size:.92rem;line-height:1.75}
@media(max-width:640px){
  .delsa-about .da-hero{height:240px !important;min-height:240px !important;max-height:240px !important}
  .delsa-about .da-hero__inner{padding:1.1rem 0 1rem}
  .delsa-about .da-btn{flex:1 1 auto}
}
CSS;
  }

  private static function js() {
    return <<<'JS'
(function () {
  var root = document.querySelector(".delsa-about");
  if (!root) return;
  var reduce = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var nodes = Array.prototype.slice.call(root.querySelectorAll("[data-da-reveal]"));
  function show(el) { el.classList.add("is-in"); }
  if (reduce || !("IntersectionObserver" in window)) {
    nodes.forEach(show);
    return;
  }
  root.querySelectorAll(".da-hero [data-da-reveal]").forEach(function (el, i) {
    window.setTimeout(function () { show(el); }, 60 + i * 80);
  });
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) return;
      show(entry.target);
      io.unobserve(entry.target);
    });
  }, { threshold: 0.14, rootMargin: "0px 0px -6% 0px" });
  nodes.forEach(function (el) {
    if (el.closest(".da-hero")) return;
    io.observe(el);
  });
})();
JS;
  }
}

Delsa_About_Page::init();
