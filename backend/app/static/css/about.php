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
  const VERSION = '3.1.0';

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
    // عکس‌های جدید کلینیک روی GitHub Pages + fallback وردپرس
    $map = [
      'hero' => 'https://ali-m07.github.io/delsa-clinic-homepage/static/img/clinic/room-01.png',
      'intro' => 'https://ali-m07.github.io/delsa-clinic-homepage/static/img/clinic/room-08.png',
      'space' => 'https://ali-m07.github.io/delsa-clinic-homepage/static/img/clinic/room-05.png',
      'calm' => 'https://ali-m07.github.io/delsa-clinic-homepage/static/img/clinic/room-02.png',
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
      if (mb_strlen($role) > 80) {
        $role = mb_substr($role, 0, 80) . '…';
      }
      $items[] = [
        'name' => get_the_title($p),
        'url' => get_permalink($p),
        'role' => $role,
        'image' => get_the_post_thumbnail_url($p, 'large') ?: '',
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
          <img class="da-hero__photo" src="<?php echo esc_url(self::img('hero')); ?>" alt="" width="1600" height="1000" decoding="async" fetchpriority="high">
        </div>
        <div class="da-hero__veil" aria-hidden="true"></div>
        <div class="da-hero__inner">
          <p class="da-hero__brand" data-da-reveal>کلینیک دلسا</p>
          <h1 id="da-title" data-da-reveal>درباره ما</h1>
          <p class="da-hero__lead" data-da-reveal>گروه تخصصی مشاوره و خدمات روان‌شناختی در سعادت‌آباد — همراه شما در مسیر شناخت، تغییر و بهتر زندگی کردن.</p>
          <div class="da-hero__actions" data-da-reveal>
            <a class="da-btn da-btn--primary" href="<?php echo esc_url($book); ?>">درخواست وقت ملاقات</a>
            <a class="da-btn da-btn--ghost" href="<?php echo esc_url($list); ?>">مشاهده مشاوران</a>
          </div>
        </div>
      </section>

      <section class="da-section da-intro" data-da-reveal>
        <div class="da-wrap">
          <div class="da-intro__grid">
            <div class="da-intro__copy">
              <h2>مرکز تخصصی مشاوره و روان‌شناسی</h2>
              <p>در کلینیک دلسا، خدمات روان‌شناسی و مشاوره — فردی، زوج، خانواده، کودک و نوجوان — توسط متخصصین با تجربه و فارغ‌التحصیل رشته‌های روان‌شناسی و مشاوره ارائه می‌شود.</p>
              <p>تیم ما با رویکردهای به‌روز، سنجش دقیق و فضای امن و محرمانه، کنار شماست تا مسیر تغییر را قدم‌به‌قدم طی کنید.</p>
            </div>
            <figure class="da-intro__media">
              <img src="<?php echo esc_url(self::img('intro')); ?>" alt="فضای کلینیک دلسا" width="900" height="720" loading="lazy" decoding="async">
            </figure>
          </div>
        </div>
      </section>

      <section class="da-section da-pillars" data-da-reveal>
        <div class="da-wrap">
          <h2 class="da-section-title">چرا دلسا</h2>
          <p class="da-section-lead">سه اصل ساده که کار ما را شکل می‌دهد.</p>
          <div class="da-pillars__grid">
            <article class="da-pillar">
              <h3>علم و تجربه</h3>
              <p>مداخلات مبتنی بر رویکردهای معتبر روان‌شناسی، با تیمی که سال‌ها در دانشگاه و کلینیک کار کرده است.</p>
            </article>
            <article class="da-pillar">
              <h3>فضای امن</h3>
              <p>محرمانگی، احترام و بدون قضاوت — تا بتوانید با خیال راحت حرف بزنید و دیده شوید.</p>
            </article>
            <article class="da-pillar">
              <h3>همراهی واقعی</h3>
              <p>از اولین تماس تا ادامه مسیر درمان، همراهی شفاف و قابل اتکا برای شما و خانواده‌تان.</p>
            </article>
          </div>
        </div>
      </section>

      <section class="da-section da-space" data-da-reveal>
        <div class="da-wrap da-space__grid">
          <figure class="da-space__shot">
            <img src="<?php echo esc_url(self::img('space')); ?>" alt="اتاق مشاوره کلینیک دلسا" width="800" height="600" loading="lazy" decoding="async">
          </figure>
          <figure class="da-space__shot da-space__shot--offset">
            <img src="<?php echo esc_url(self::img('calm')); ?>" alt="فضای آرام کلینیک دلسا" width="800" height="600" loading="lazy" decoding="async">
          </figure>
          <div class="da-space__copy">
            <h2>فضایی آرام برای شروع</h2>
            <p>کلینیک در سعادت‌آباد، خیابان علامه جنوبی قرار دارد؛ محیطی آرام و حرفه‌ای برای جلسات حضوری، با امکان هماهنگی نوبت آنلاین.</p>
            <a class="da-btn da-btn--primary" href="<?php echo esc_url($book); ?>">رزرو نوبت</a>
          </div>
        </div>
      </section>

      <?php if ($team) : ?>
      <section class="da-section da-team" data-da-reveal>
        <div class="da-wrap">
          <div class="da-team__head">
            <h2>تیم مشاوران</h2>
            <p>متخصصین دلسا با سوابق دانشگاهی و بالینی، آماده همراهی شما هستند.</p>
          </div>
          <div class="da-team__grid">
            <?php foreach ($team as $person) : ?>
              <article class="da-person">
                <a class="da-person__link" href="<?php echo esc_url($person['url']); ?>">
                  <div class="da-person__photo">
                    <?php if ($person['image'] !== '') : ?>
                      <img src="<?php echo esc_url($person['image']); ?>" alt="<?php echo esc_attr($person['name']); ?>" width="320" height="400" loading="lazy" decoding="async">
                    <?php endif; ?>
                  </div>
                  <h3><?php echo esc_html($person['name']); ?></h3>
                  <?php if ($person['role'] !== '') : ?>
                    <p><?php echo esc_html($person['role']); ?></p>
                  <?php endif; ?>
                </a>
              </article>
            <?php endforeach; ?>
          </div>
          <div class="da-team__more">
            <a class="da-btn da-btn--primary" href="<?php echo esc_url($list); ?>">همه مشاوران</a>
          </div>
        </div>
      </section>
      <?php else : ?>
      <section class="da-section da-team da-team--cta-only" data-da-reveal>
        <div class="da-wrap da-cta-band">
          <h2>مشاوران دلسا</h2>
          <p>معرفی کامل متخصصین، سوابق و حوزه‌های کاری را در صفحه مشاوران ببینید.</p>
          <div class="da-hero__actions">
            <a class="da-btn da-btn--primary" href="<?php echo esc_url($list); ?>">مشاهده مشاوران</a>
            <a class="da-btn da-btn--ghost-dark" href="<?php echo esc_url($book); ?>">درخواست وقت</a>
          </div>
        </div>
      </section>
      <?php endif; ?>

      <section class="da-section da-final" data-da-reveal>
        <div class="da-wrap da-final__inner">
          <h2>آماده‌اید شروع کنید؟</h2>
          <p>برای رزرو وقت، فرم نوبت‌دهی را پر کنید یا با کلینیک تماس بگیرید.</p>
          <a class="da-btn da-btn--primary" href="<?php echo esc_url($book); ?>">فرم نوبت‌دهی</a>
        </div>
      </section>
    </div>
    <?php
    return ob_get_clean();
  }

  private static function css() {
    return <<<'CSS'
/* Full-bleed about — warm light, matches homepage craft */
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
  --font:Vazirmatn,Tahoma,sans-serif;--radius:18px;--ease:cubic-bezier(.22,1,.36,1);
  --shadow:0 16px 40px rgba(15,39,64,.08);
  font-family:var(--font);color:var(--navy);background:var(--ivory);
  width:100vw;max-width:100vw;margin-right:calc(50% - 50vw);margin-left:calc(50% - 50vw);
  overflow-x:clip;box-sizing:border-box;line-height:1.75;letter-spacing:-.01em
}
.delsa-about *,.delsa-about *::before,.delsa-about *::after{box-sizing:border-box}
.delsa-about img{max-width:100%;height:auto;display:block}
.delsa-about .da-wrap{width:min(1120px,calc(100% - 2.5rem));margin:0 auto}
.delsa-about .da-section{padding:3.25rem 0}
@media(min-width:900px){.delsa-about .da-section{padding:4.25rem 0}}
.delsa-about .da-section-title{margin:0 0 .65rem;font-size:clamp(1.45rem,1.2rem + .7vw,1.85rem);font-weight:700;color:var(--navy);line-height:1.4}
.delsa-about .da-section-lead{margin:0 0 2rem;max-width:36rem;font-size:1rem;color:var(--muted);line-height:1.85}

.delsa-about .da-hero{
  position:relative !important;
  height:340px !important;
  min-height:340px !important;
  max-height:340px !important;
  display:flex !important;
  align-items:flex-end !important;
  color:#fff;
  overflow:hidden !important
}
.delsa-about .da-hero__media{position:absolute;inset:0;overflow:hidden}
.delsa-about .da-hero__photo{
  width:100%;height:100%;object-fit:cover;object-position:center 40%;
  transform:scale(1.12);transform-origin:50% 42%;
  will-change:transform;
  animation:da-ken 14s ease-in-out infinite alternate
}
.delsa-about .da-hero__veil{position:absolute;inset:0;background:
  linear-gradient(105deg,rgba(10,27,46,.82) 0%,rgba(15,39,64,.55) 42%,rgba(15,39,64,.22) 72%,rgba(15,39,64,.08) 100%),
  linear-gradient(0deg,rgba(10,27,46,.5) 0%,transparent 48%);
  opacity:0;animation:da-fade .9s var(--ease) .1s forwards
}
.delsa-about .da-hero__inner{position:relative;z-index:1;width:min(1120px,calc(100% - 2.5rem));margin:0 auto;padding:1.75rem 0 1.5rem}
.delsa-about .da-hero__brand{margin:0 0 .55rem;font-size:clamp(1.05rem,.95rem + .4vw,1.25rem);font-weight:700;letter-spacing:-.02em}
.delsa-about .da-hero h1{margin:0 0 .55rem;font-size:clamp(1.65rem,1.35rem + 1vw,2.15rem);font-weight:700;line-height:1.25}
.delsa-about .da-hero__lead{margin:0 0 1rem;max-width:34rem;font-size:.95rem;line-height:1.8;color:rgba(255,255,255,.86)}
.delsa-about .da-hero__actions{display:flex;flex-wrap:wrap;gap:.65rem}
@keyframes da-ken{from{transform:scale(1.12)}to{transform:scale(1.04)}}
@keyframes da-fade{from{opacity:0}to{opacity:1}}
@keyframes da-up{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:none}}
.delsa-about [data-da-reveal]{opacity:0;transform:translateY(18px)}
.delsa-about [data-da-reveal].is-in{animation:da-up .7s var(--ease) forwards}
.delsa-about .da-hero [data-da-reveal].is-in:nth-child(1){animation-delay:.05s}
.delsa-about .da-hero [data-da-reveal].is-in:nth-child(2){animation-delay:.14s}
.delsa-about .da-hero [data-da-reveal].is-in:nth-child(3){animation-delay:.24s}
.delsa-about .da-hero [data-da-reveal].is-in:nth-child(4){animation-delay:.34s}
@media(prefers-reduced-motion:reduce){
  .delsa-about .da-hero__photo{animation:none;transform:none}
  .delsa-about .da-hero__veil,[data-da-reveal]{opacity:1;transform:none;animation:none}
}

.delsa-about .da-btn{display:inline-flex;align-items:center;justify-content:center;padding:.85rem 1.45rem;border-radius:14px;font-size:.9rem;font-weight:700;font-family:inherit;text-decoration:none;transition:transform .25s var(--ease),background .25s var(--ease),color .25s var(--ease),border-color .25s var(--ease)}
.delsa-about .da-btn--primary{background:var(--teal);color:#fff}
.delsa-about .da-btn--primary:hover{background:var(--teal-deep);transform:translateY(-1px)}
.delsa-about .da-btn--ghost{background:transparent;color:#fff;border:1.5px solid rgba(255,255,255,.45)}
.delsa-about .da-btn--ghost:hover{background:rgba(255,255,255,.1)}
.delsa-about .da-btn--ghost-dark{background:transparent;color:var(--navy);border:1.5px solid var(--line)}
.delsa-about .da-btn--soft{background:rgba(31,168,160,.12);color:var(--teal-deep);border:1.5px solid rgba(31,168,160,.28)}
.delsa-about .da-btn--soft:hover{background:rgba(31,168,160,.18)}

.delsa-about .da-intro{background:
  radial-gradient(ellipse 60% 50% at 100% 0%,rgba(31,168,160,.08),transparent 55%),
  var(--ivory)}
.delsa-about .da-intro__grid{display:grid;gap:2rem;align-items:center}
@media(min-width:900px){.delsa-about .da-intro__grid{grid-template-columns:1.05fr .95fr;gap:3rem}}
.delsa-about .da-intro__copy h2{margin:0 0 1rem;font-size:clamp(1.45rem,1.2rem + .7vw,1.9rem);font-weight:700;line-height:1.4}
.delsa-about .da-intro__copy p{margin:0 0 1rem;font-size:1.02rem;line-height:2;color:rgba(15,39,64,.78)}
.delsa-about .da-intro__copy p:last-child{margin-bottom:0}
.delsa-about .da-intro__media{margin:0;border-radius:var(--radius);overflow:hidden;aspect-ratio:5/4;box-shadow:var(--shadow);background:#d7e2ea}
.delsa-about .da-intro__media img{width:100%;height:100%;object-fit:cover}

.delsa-about .da-pillars{background:var(--ivory-2)}
.delsa-about .da-pillars__grid{display:grid;gap:1.5rem}
@media(min-width:760px){.delsa-about .da-pillars__grid{grid-template-columns:repeat(3,minmax(0,1fr));gap:1.75rem}}
.delsa-about .da-pillar{padding:0;border:0;background:transparent}
.delsa-about .da-pillar h3{margin:0 0 .55rem;font-size:1.1rem;font-weight:700}
.delsa-about .da-pillar p{margin:0;font-size:.95rem;line-height:1.9;color:var(--muted)}

.delsa-about .da-space{background:var(--ivory)}
.delsa-about .da-space__grid{display:grid;gap:1.25rem;align-items:center}
@media(min-width:900px){
  .delsa-about .da-space__grid{grid-template-columns:1fr 1fr 1.05fr;gap:1.5rem}
  .delsa-about .da-space__shot--offset{transform:translateY(1.25rem)}
}
.delsa-about .da-space__shot{margin:0;border-radius:var(--radius);overflow:hidden;aspect-ratio:4/3;box-shadow:var(--shadow);background:#d7e2ea}
.delsa-about .da-space__shot img{width:100%;height:100%;object-fit:cover}
.delsa-about .da-space__copy h2{margin:0 0 .85rem;font-size:clamp(1.35rem,1.15rem + .55vw,1.7rem);font-weight:700}
.delsa-about .da-space__copy p{margin:0;font-size:1rem;line-height:1.95;color:rgba(15,39,64,.78)}
.delsa-about .da-space__copy .da-btn{margin-top:1rem}

.delsa-about .da-team{background:
  radial-gradient(ellipse 55% 40% at 0% 0%,rgba(31,168,160,.1),transparent 55%),
  var(--ivory-2)}
.delsa-about .da-team__head{max-width:36rem;margin:0 0 2rem}
.delsa-about .da-team__head h2{margin:0 0 .65rem;font-size:clamp(1.45rem,1.2rem + .7vw,1.85rem);font-weight:700}
.delsa-about .da-team__head p{margin:0;color:var(--muted);line-height:1.85}
.delsa-about .da-team__grid{display:grid;grid-template-columns:1fr;gap:1.25rem}
@media(min-width:560px){.delsa-about .da-team__grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(min-width:980px){.delsa-about .da-team__grid{grid-template-columns:repeat(5,minmax(0,1fr));gap:1rem}}
.delsa-about .da-person{min-width:0}
.delsa-about .da-person__link{display:block;color:inherit;text-decoration:none}
.delsa-about .da-person__photo{margin:0 0 .85rem;border-radius:16px;overflow:hidden;aspect-ratio:4/5;background:#d7e2ea;box-shadow:var(--shadow)}
.delsa-about .da-person__photo img{width:100%;height:100%;object-fit:cover;object-position:center top;transition:transform .55s var(--ease)}
.delsa-about .da-person__link:hover .da-person__photo img{transform:scale(1.04)}
.delsa-about .da-person h3{margin:0 0 .35rem;font-size:1rem;font-weight:700}
.delsa-about .da-person p{margin:0;font-size:.8125rem;line-height:1.7;color:var(--muted)}
.delsa-about .da-team__more{margin-top:2rem;text-align:center}
.delsa-about .da-cta-band{text-align:center;max-width:36rem;margin:0 auto}
.delsa-about .da-cta-band h2{margin:0 0 .65rem;font-size:clamp(1.45rem,1.2rem + .7vw,1.85rem);font-weight:700}
.delsa-about .da-cta-band p{margin:0 0 1.35rem;color:var(--muted);line-height:1.85}
.delsa-about .da-cta-band .da-hero__actions{justify-content:center}

.delsa-about .da-final{padding-bottom:4.5rem;background:var(--ivory)}
.delsa-about .da-final__inner{
  text-align:center;padding:2.75rem 1.5rem;border-radius:22px;
  background:linear-gradient(135deg,rgba(15,39,64,.96),rgba(23,100,110,.92)),
    url("https://ali-m07.github.io/delsa-clinic-homepage/static/img/clinic/room-06.png") center/cover;
  color:#fff;box-shadow:var(--shadow)
}
.delsa-about .da-final__inner h2{margin:0 0 .65rem;font-size:clamp(1.4rem,1.2rem + .6vw,1.8rem);font-weight:700}
.delsa-about .da-final__inner p{margin:0 0 1.35rem;color:rgba(255,255,255,.82);line-height:1.85}
@media(max-width:640px){
  .delsa-about .da-hero{height:300px !important;min-height:300px !important;max-height:300px !important}
  .delsa-about .da-hero__inner{padding:1.35rem 0 1.25rem}
  .delsa-about .da-btn{width:100%}
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
  // hero copy in immediately
  root.querySelectorAll(".da-hero [data-da-reveal]").forEach(function (el, i) {
    window.setTimeout(function () { show(el); }, 80 + i * 90);
  });
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (entry) {
      if (!entry.isIntersecting) return;
      show(entry.target);
      io.unobserve(entry.target);
    });
  }, { threshold: 0.16, rootMargin: "0px 0px -8% 0px" });
  nodes.forEach(function (el) {
    if (el.closest(".da-hero")) return;
    io.observe(el);
  });
})();
JS;
  }
}

Delsa_About_Page::init();
