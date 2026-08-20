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
  const VERSION = '3.5.0';

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

      <div class="da-body">
        <div class="da-wrap da-stack">
          <section class="da-panel" data-da-reveal aria-labelledby="da-story-title">
            <header class="da-panel__head">
              <span class="da-panel__idx" aria-hidden="true">۰۱</span>
              <div>
                <p class="da-kicker">درباره ما</p>
                <h2 id="da-story-title">اینجا کنار شماییم</h2>
              </div>
            </header>
            <div class="da-story__grid">
              <div class="da-story__copy">
                <p>دلسا گروهی از مشاوران و روان‌شناسان است که در مسیر فردی، زوجی و خانواده همراهتان می‌مانند. کارمان علمی است، اما زبانش انسانی.</p>
                <p>فضای کلینیک آرام و محرمانه است تا بتوانید راحت حرف بزنید و قدم‌به‌قدم جلو بروید.</p>
              </div>
              <figure class="da-story__media">
                <img src="<?php echo esc_url(self::img('story')); ?>" alt="فضای کلینیک دلسا" width="880" height="660" loading="lazy" decoding="async">
              </figure>
            </div>
            <div class="da-gallery" aria-label="فضای کلینیک">
              <img src="<?php echo esc_url(self::img('a')); ?>" alt="" width="640" height="420" loading="lazy" decoding="async">
              <img src="<?php echo esc_url(self::img('b')); ?>" alt="" width="640" height="420" loading="lazy" decoding="async">
              <img src="<?php echo esc_url(self::img('c')); ?>" alt="" width="640" height="420" loading="lazy" decoding="async">
            </div>
          </section>

          <?php if ($team) : ?>
          <section class="da-panel" data-da-reveal aria-labelledby="da-team-title">
            <header class="da-panel__head da-panel__head--row">
              <div class="da-panel__head-main">
                <span class="da-panel__idx" aria-hidden="true">۰۲</span>
                <div>
                  <p class="da-kicker">مشاوران</p>
                  <h2 id="da-team-title">تیم ما</h2>
                </div>
              </div>
              <a class="da-textlink" href="<?php echo esc_url($list); ?>">همه مشاوران</a>
            </header>
            <div class="da-team__row">
              <?php foreach ($team as $person) : ?>
                <a class="da-face" href="<?php echo esc_url($person['url']); ?>">
                  <span class="da-face__photo">
                    <?php if ($person['image'] !== '') : ?>
                      <img src="<?php echo esc_url($person['image']); ?>" alt="<?php echo esc_attr($person['name']); ?>" width="200" height="240" loading="lazy" decoding="async">
                    <?php endif; ?>
                  </span>
                  <span class="da-face__meta">
                    <span class="da-face__name"><?php echo esc_html($person['name']); ?></span>
                    <?php if ($person['role'] !== '') : ?>
                      <span class="da-face__role"><?php echo esc_html($person['role']); ?></span>
                    <?php endif; ?>
                  </span>
                </a>
              <?php endforeach; ?>
            </div>
          </section>
          <?php endif; ?>

          <section class="da-panel da-panel--cta" data-da-reveal aria-labelledby="da-cta-title">
            <header class="da-panel__head da-panel__head--center">
              <span class="da-panel__idx" aria-hidden="true">۰۳</span>
              <div>
                <p class="da-kicker">نوبت‌دهی</p>
                <h2 id="da-cta-title">وقت یک گفت‌وگوی امن است؟</h2>
              </div>
            </header>
            <p class="da-cta__lead">نوبت حضوری یا هماهنگی از طریق فرم، هر طور راحت‌ترید.</p>
            <a class="da-btn da-btn--primary" href="<?php echo esc_url($book); ?>">درخواست وقت ملاقات</a>
          </section>
        </div>
      </div>
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
  --navy:#0F2740;--teal:#1FA8A0;--teal-deep:#178F88;
  --ivory:#F3F5F7;--panel:#FFFFFF;--muted:#5B6B7C;--line:rgba(15,39,64,.12);
  --font:Vazirmatn,Tahoma,sans-serif;--radius:16px;--ease:cubic-bezier(.22,1,.36,1);
  font-family:var(--font);color:var(--navy);background:var(--ivory);
  width:100vw;max-width:100vw;margin-right:calc(50% - 50vw);margin-left:calc(50% - 50vw);
  overflow-x:clip;box-sizing:border-box;line-height:1.7;letter-spacing:-.01em
}
.delsa-about *,.delsa-about *::before,.delsa-about *::after{box-sizing:border-box}
.delsa-about img{max-width:100%;height:auto;display:block}
.delsa-about .da-wrap{width:min(1080px,calc(100% - 1.75rem));margin:0 auto}

.delsa-about .da-hero{
  position:relative !important;height:280px !important;min-height:280px !important;max-height:280px !important;
  display:flex !important;align-items:flex-end !important;color:#fff;overflow:hidden !important
}
.delsa-about .da-hero__media{position:absolute;inset:0;overflow:hidden}
.delsa-about .da-hero__photo{
  width:100%;height:100%;object-fit:cover;object-position:center 40%;
  transform:scale(1.08);transform-origin:50% 42%;will-change:transform;
  animation:da-ken 12s ease-in-out infinite alternate
}
.delsa-about .da-hero__veil{position:absolute;inset:0;background:
  radial-gradient(ellipse 50% 40% at 20% 80%,rgba(31,168,160,.12),transparent 55%),
  linear-gradient(90deg,transparent 0%,transparent 40%,rgba(18,42,50,.24) 70%,rgba(18,42,50,.5) 100%);
  opacity:0;animation:da-fade .75s var(--ease) .05s forwards
}
.delsa-about .da-hero__inner{
  position:relative;z-index:1;width:min(1080px,calc(100% - 1.75rem));margin:0 auto;
  padding:1.25rem 0 1.2rem;text-shadow:0 6px 22px rgba(8,18,24,.25)
}
.delsa-about .da-hero h1{
  margin:0 0 .4rem;font-size:clamp(2.2rem,1.5rem + 2.4vw,3.4rem);font-weight:800;
  letter-spacing:-.05em;line-height:.98;text-wrap:balance
}
.delsa-about .da-hero__lead{
  margin:0 0 1rem;max-width:34rem;font-size:clamp(1.02rem,.98rem + .2vw,1.12rem);
  line-height:1.75;color:rgba(255,255,255,.93)
}
.delsa-about .da-hero__actions{display:flex;flex-wrap:wrap;gap:.6rem}
@keyframes da-ken{from{transform:scale(1.08)}to{transform:scale(1.02)}}
@keyframes da-fade{from{opacity:0}to{opacity:1}}
@keyframes da-up{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:none}}
.delsa-about [data-da-reveal]{opacity:0;transform:translateY(12px)}
.delsa-about [data-da-reveal].is-in{animation:da-up .55s var(--ease) forwards}
.delsa-about .da-hero [data-da-reveal].is-in:nth-child(1){animation-delay:.04s}
.delsa-about .da-hero [data-da-reveal].is-in:nth-child(2){animation-delay:.1s}
.delsa-about .da-hero [data-da-reveal].is-in:nth-child(3){animation-delay:.16s}
@media(prefers-reduced-motion:reduce){
  .delsa-about .da-hero__photo{animation:none;transform:none}
  .delsa-about .da-hero__veil,.delsa-about [data-da-reveal]{opacity:1;transform:none;animation:none}
}

.delsa-about .da-btn{
  display:inline-flex;align-items:center;justify-content:center;
  padding:.78rem 1.3rem;border-radius:12px;font-size:1rem;font-weight:700;font-family:inherit;
  text-decoration:none;transition:transform .2s var(--ease),background .2s,border-color .2s
}
.delsa-about .da-btn--primary{background:var(--teal);color:#fff;box-shadow:0 10px 22px rgba(31,168,160,.25)}
.delsa-about .da-btn--primary:hover{background:var(--teal-deep);transform:translateY(-1px)}
.delsa-about .da-btn--ghost{background:rgba(255,255,255,.16);color:#fff;border:1.5px solid rgba(255,255,255,.55);backdrop-filter:blur(8px)}
.delsa-about .da-btn--ghost:hover{background:rgba(255,255,255,.24)}
.delsa-about .da-textlink{font-size:.98rem;font-weight:700;color:var(--teal-deep);text-decoration:none;white-space:nowrap}

.delsa-about .da-body{padding:1.15rem 0 1.75rem;background:var(--ivory)}
.delsa-about .da-stack{display:grid;gap:1rem}
.delsa-about .da-panel{
  background:var(--panel);border:1px solid var(--line);border-radius:var(--radius);
  padding:1.15rem 1.15rem 1.25rem;box-shadow:0 8px 24px rgba(15,39,64,.04)
}
.delsa-about .da-panel--cta{text-align:center;padding:1.4rem 1.2rem 1.5rem;
  background:linear-gradient(160deg,#FFFFFF 0%,#F4F8F8 100%)}
.delsa-about .da-panel__head{display:flex;align-items:flex-start;gap:.85rem;margin:0 0 1rem;padding-bottom:.85rem;border-bottom:1px solid var(--line)}
.delsa-about .da-panel__head--row{align-items:center;justify-content:space-between;gap:1rem}
.delsa-about .da-panel__head--center{flex-direction:column;align-items:center;text-align:center;border-bottom:0;padding-bottom:0;margin-bottom:.65rem}
.delsa-about .da-panel__head-main{display:flex;align-items:flex-start;gap:.85rem;min-width:0}
.delsa-about .da-panel__idx{
  flex:0 0 auto;width:2.35rem;height:2.35rem;border-radius:10px;
  display:inline-flex;align-items:center;justify-content:center;
  background:rgba(31,168,160,.12);color:var(--teal-deep);
  font-size:.92rem;font-weight:800;letter-spacing:-.02em
}
.delsa-about .da-kicker{margin:0 0 .25rem;font-size:.82rem;font-weight:700;color:var(--teal-deep);letter-spacing:.03em}
.delsa-about .da-panel__head h2{
  margin:0;font-size:clamp(1.35rem,1.15rem + .7vw,1.85rem);font-weight:800;
  letter-spacing:-.035em;line-height:1.2
}

.delsa-about .da-story__grid{display:grid;gap:1rem;align-items:stretch}
@media(min-width:860px){.delsa-about .da-story__grid{grid-template-columns:1.05fr .95fr;gap:1.15rem}}
.delsa-about .da-story__copy p{
  margin:0 0 .75rem;font-size:1.02rem;line-height:1.85;color:rgba(15,39,64,.8)
}
.delsa-about .da-story__copy p:last-child{margin-bottom:0}
.delsa-about .da-story__media{
  margin:0;border-radius:12px;overflow:hidden;aspect-ratio:5/4;background:#d7e2ea;
  border:1px solid var(--line)
}
.delsa-about .da-story__media img{width:100%;height:100%;object-fit:cover}

.delsa-about .da-gallery{
  margin-top:1rem;padding-top:1rem;border-top:1px solid var(--line);
  display:grid;grid-template-columns:1.1fr 1fr 1fr;gap:.55rem
}
.delsa-about .da-gallery img{
  width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:10px;background:#d7e2ea;
  border:1px solid var(--line)
}
@media(max-width:640px){
  .delsa-about .da-gallery{grid-template-columns:1fr 1fr}
  .delsa-about .da-gallery img:last-child{display:none}
}

.delsa-about .da-team__row{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:.65rem}
@media(max-width:980px){.delsa-about .da-team__row{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:560px){.delsa-about .da-team__row{grid-template-columns:repeat(2,minmax(0,1fr));gap:.55rem}}
.delsa-about .da-face{
  display:flex;flex-direction:column;min-width:0;color:inherit;text-decoration:none;
  border:1px solid var(--line);border-radius:12px;overflow:hidden;background:#FAFBFC;
  transition:border-color .2s,transform .2s var(--ease),box-shadow .2s
}
.delsa-about .da-face:hover{border-color:rgba(31,168,160,.45);transform:translateY(-2px);box-shadow:0 10px 22px rgba(15,39,64,.08)}
.delsa-about .da-face__photo{display:block;aspect-ratio:4/5;background:#d7e2ea;overflow:hidden}
.delsa-about .da-face__photo img{width:100%;height:100%;object-fit:cover;object-position:center top;transition:transform .45s var(--ease)}
.delsa-about .da-face:hover .da-face__photo img{transform:scale(1.04)}
.delsa-about .da-face__meta{padding:.65rem .7rem .75rem}
.delsa-about .da-face__name{display:block;font-size:.98rem;font-weight:750;line-height:1.3;letter-spacing:-.02em}
.delsa-about .da-face__role{display:block;margin-top:.25rem;font-size:.82rem;line-height:1.5;color:var(--muted)}

.delsa-about .da-cta__lead{margin:0 auto 1rem;max-width:32rem;color:var(--muted);font-size:1.02rem;line-height:1.8}
@media(max-width:640px){
  .delsa-about .da-hero{height:250px !important;min-height:250px !important;max-height:250px !important}
  .delsa-about .da-hero__inner{padding:1.05rem 0 1rem}
  .delsa-about .da-btn{flex:1 1 auto}
  .delsa-about .da-panel{padding:1rem}
  .delsa-about .da-panel__head--row{flex-wrap:wrap}
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
