import { useEffect, useState, type ReactNode } from 'react'
import {
  AnimatePresence,
  motion,
  useReducedMotion,
  type Transition,
} from 'motion/react'
import './App.css'

const CLINIC = [
  'https://delsaclinic.com/wp-content/uploads/2026/08/20260819_150534-scaled.jpg',
  'https://delsaclinic.com/wp-content/uploads/2026/08/20260819_151844-scaled.jpg',
  'https://delsaclinic.com/wp-content/uploads/2026/08/IMG_20260819_180527_618-scaled.jpg',
  'https://delsaclinic.com/wp-content/uploads/2026/08/IMG_20260819_180503_498-scaled.jpg',
  'https://delsaclinic.com/wp-content/uploads/2026/08/20260819_150736-scaled.jpg',
  'https://delsaclinic.com/wp-content/uploads/2026/08/20260819_150725-scaled.jpg',
  'https://delsaclinic.com/wp-content/uploads/2026/08/20260819_150627-scaled.jpg',
] as const

const SERVICES = [
  {
    title: 'روان‌درمانی',
    desc: 'اگر احساساتتان سنگین شده، با رویکرد علمی کنارتان هستیم.',
    href: 'https://delsaclinic.com/%D8%AF%D9%BE%D8%A7%D8%B1%D8%AA%D9%85%D8%A7%D9%86-%D8%B1%D9%88%D8%A7%D9%86-%D8%AF%D8%B1%D9%85%D8%A7%D9%86%DB%8C/',
    img: 'https://ali-m07.github.io/delsa-clinic-homepage/static/img/services/anxiety.png',
  },
  {
    title: 'زوج و خانواده',
    desc: 'برای بهبود رابطه و گفت‌وگوی سالم‌تر، همراهتان هستیم.',
    href: 'https://delsaclinic.com/%D8%AF%D9%BE%D8%A7%D8%B1%D8%AA%D9%85%D8%A7%D9%86-%D8%B2%D9%88%D8%AC-%D9%88-%D8%AE%D8%A7%D9%86%D9%88%D8%A7%D8%AF%D9%87/',
    img: 'https://ali-m07.github.io/delsa-clinic-homepage/static/img/services/couple.png',
  },
  {
    title: 'کودک و نوجوان',
    desc: 'در مسیر رشد سالم فرزندتان، پشتیبان شما.',
    href: 'https://delsaclinic.com/%D8%AF%D9%BE%D8%A7%D8%B1%D8%AA%D9%85%D8%A7%D9%86-%DA%A9%D9%88%D8%AF%DA%A9-%D9%88-%D9%86%D9%88%D8%AC%D9%88%D8%A7%D9%86/',
    img: 'https://ali-m07.github.io/delsa-clinic-homepage/static/img/services/child.png',
  },
  {
    title: 'روان‌پزشکی',
    desc: 'ارزیابی و درمان تخصصی، با آرامش و دقت.',
    href: 'https://delsaclinic.com/%D8%AF%D9%BE%D8%A7%D8%B1%D8%AA%D9%85%D8%A7%D9%86-%D8%B1%D9%88%D8%A7%D9%86%D9%BE%D8%B2%D8%B4%DA%A9%DB%8C/',
    img: 'https://ali-m07.github.io/delsa-clinic-homepage/static/img/services/brain.png',
  },
  {
    title: 'ترک اعتیاد',
    desc: 'درمان و حمایت همراه با احترام و محرمانگی.',
    href: 'https://delsaclinic.com/%D8%AF%D9%BE%D8%A7%D8%B1%D8%AA%D9%85%D8%A7%D9%86-%D8%AA%D8%B1%DA%A9-%D8%A7%D8%B9%D8%AA%DB%8C%D8%A7%D8%AF/',
    img: 'https://ali-m07.github.io/delsa-clinic-homepage/static/img/services/freedom.png',
  },
  {
    title: 'مشاوره شغلی',
    desc: 'راهنمایی برای انتخاب، تغییر یا ادامه مسیر حرفه‌ای.',
    href: 'https://delsaclinic.com/%D8%AF%D9%BE%D8%A7%D8%B1%D8%AA%D9%85%D8%A7%D9%86-%D9%85%D8%B4%D8%A7%D9%88%D8%B1%D9%87-%D8%B4%D8%BA%D9%84%DB%8C/',
    img: 'https://ali-m07.github.io/delsa-clinic-homepage/static/img/services/support.png',
  },
] as const

const TESTIMONIALS = [
  {
    text: 'فضای کلینیک آرام بود و برای اولین بار احساس کردم واقعاً شنیده می‌شوم.',
    name: 'سارا م.',
    role: 'مراجع روان‌درمانی',
  },
  {
    text: 'هماهنگی نوبت سریع بود و مشاور با دقت و احترام مسیر را برامون روشن کرد.',
    name: 'امیر و ن.',
    role: 'مراجع زوج‌درمانی',
  },
  {
    text: 'برای فرزندمان فضای امن و صمیمی بود؛ بعد از چند جلسه آرامش بیشتری در خانه حس کردیم.',
    name: 'مریم ک.',
    role: 'والد مراجع کودک',
  },
  {
    text: 'هم حضوری و هم آنلاین کیفیت یکسانی داشت؛ حس می‌کنم تیم واقعاً کنارم است.',
    name: 'رضا پ.',
    role: 'مراجع مشاوره شغلی',
  },
] as const

const BOOK =
  'https://delsaclinic.com/%D9%81%D8%B1%D9%85-%D9%86%D9%88%D8%A8%D8%AA-%D8%AF%D9%87%DB%8C/'
const LIVE = 'https://delsaclinic.com'
const LOGO =
  'https://delsaclinic.com/wp-content/uploads/2021/12/DelsaClinicLogo-120x120.png'

const DEPTS = [
  { label: 'روان‌درمانی', href: `${LIVE}/%D8%AF%D9%BE%D8%A7%D8%B1%D8%AA%D9%85%D8%A7%D9%86-%D8%B1%D9%88%D8%A7%D9%86-%D8%AF%D8%B1%D9%85%D8%A7%D9%86%DB%8C/` },
  { label: 'زوج و خانواده', href: `${LIVE}/%D8%AF%D9%BE%D8%A7%D8%B1%D8%AA%D9%85%D8%A7%D9%86-%D8%B2%D9%88%D8%AC-%D9%88-%D8%AE%D8%A7%D9%86%D9%88%D8%A7%D8%AF%D9%87/` },
  { label: 'کودک و نوجوان', href: `${LIVE}/%D8%AF%D9%BE%D8%A7%D8%B1%D8%AA%D9%85%D8%A7%D9%86-%DA%A9%D9%88%D8%AF%DA%A9-%D9%88-%D9%86%D9%88%D8%AC%D9%88%D8%A7%D9%86/` },
  { label: 'روان‌پزشکی', href: `${LIVE}/%D8%AF%D9%BE%D8%A7%D8%B1%D8%AA%D9%85%D8%A7%D9%86-%D8%B1%D9%88%D8%A7%D9%86%D9%BE%D8%B2%D8%B4%DA%A9%DB%8C/` },
  { label: 'ترک اعتیاد', href: `${LIVE}/%D8%AF%D9%BE%D8%A7%D8%B1%D8%AA%D9%85%D8%A7%D9%86-%D8%AA%D8%B1%DA%A9-%D8%A7%D8%B9%D8%AA%DB%8C%D8%A7%D8%AF/` },
  { label: 'مشاوره شغلی', href: `${LIVE}/%D8%AF%D9%BE%D8%A7%D8%B1%D8%AA%D9%85%D8%A7%D9%86-%D9%85%D8%B4%D8%A7%D9%88%D8%B1%D9%87-%D8%B4%D8%BA%D9%84%DB%8C/` },
] as const

function SiteHeader() {
  const [open, setOpen] = useState(false)

  useEffect(() => {
    document.body.style.overflow = open ? 'hidden' : ''
    return () => {
      document.body.style.overflow = ''
    }
  }, [open])

  return (
    <>
      <header className="site-header" id="site-header">
        <div className="hdr-wrap">
          <a className="hdr-logo" href={LIVE}>
            <img src={LOGO} alt="کلینیک دلسا" width={40} height={40} />
            <span>
              <span className="hdr-logo__name">کلینیک دلسا</span>
              <span className="hdr-logo__tag">مشاوره و روان‌درمانی</span>
            </span>
          </a>

          <nav className="hdr-nav" aria-label="منوی اصلی">
            <a className="hdr-nav__link" href={LIVE}>
              خانه
            </a>
            <a className="hdr-nav__link" href={`${LIVE}/%D8%AF%D8%B1%D8%A8%D8%A7%D8%B1%D9%87-%D9%85%D8%A7/`}>
              درباره ما
            </a>
            <div className="nav-dropdown">
              <span className="hdr-nav__link hdr-nav__drop">دپارتمان‌ها</span>
              <div className="nav-dropdown-menu" role="menu">
                {DEPTS.map((d) => (
                  <a key={d.label} href={d.href} role="menuitem">
                    {d.label}
                  </a>
                ))}
              </div>
            </div>
            <a className="hdr-nav__link" href={`${LIVE}/%D9%85%D8%B4%D8%A7%D9%88%D8%B1%D8%A7%D9%86/`}>
              مشاوران
            </a>
            <a className="hdr-nav__link" href={`${LIVE}/blog/`}>
              وبلاگ
            </a>
          </nav>

          <div className="hdr-actions">
            <a href="tel:+989025680372" className="hdr-phone dir-ltr">
              ۰۹۰۲-۵۶۸۰۳۷۲
            </a>
            <a href={BOOK} className="hdr-cta">
              درخواست وقت ملاقات
            </a>
            <button
              type="button"
              className="hdr-burger"
              aria-label="منوی موبایل"
              aria-expanded={open}
              onClick={() => setOpen(true)}
            >
              <span />
              <span />
              <span />
            </button>
          </div>
        </div>
      </header>

      <div
        className={`sidebar-backdrop${open ? ' is-open' : ''}`}
        aria-hidden={!open}
        onClick={() => setOpen(false)}
      />
      <aside
        className={`sidebar-panel${open ? ' is-open' : ''}`}
        aria-hidden={!open}
        aria-label="منوی موبایل"
      >
        <div className="sidebar-head">
          <a className="sidebar-brand" href={LIVE} onClick={() => setOpen(false)}>
            <img src={LOGO} alt="" width={36} height={36} />
            کلینیک دلسا
          </a>
          <button type="button" className="sidebar-close" aria-label="بستن منو" onClick={() => setOpen(false)}>
            ×
          </button>
        </div>
        <nav className="sidebar-nav">
          <a href={LIVE} onClick={() => setOpen(false)}>
            خانه
          </a>
          <a href={`${LIVE}/%D8%AF%D8%B1%D8%A8%D8%A7%D8%B1%D9%87-%D9%85%D8%A7/`} onClick={() => setOpen(false)}>
            درباره ما
          </a>
          <span className="sidebar-section-label">دپارتمان‌ها</span>
          {DEPTS.map((d) => (
            <a key={d.label} className="sidebar-sublink" href={d.href} onClick={() => setOpen(false)}>
              {d.label}
            </a>
          ))}
          <a href={`${LIVE}/%D9%85%D8%B4%D8%A7%D9%88%D8%B1%D8%A7%D9%86/`} onClick={() => setOpen(false)}>
            مشاوران
          </a>
          <a href={`${LIVE}/blog/`} onClick={() => setOpen(false)}>
            وبلاگ
          </a>
          <a className="sidebar-cta" href={BOOK} onClick={() => setOpen(false)}>
            درخواست وقت ملاقات
          </a>
        </nav>
      </aside>
    </>
  )
}

function SiteFooter() {
  return (
    <footer id="contact" className="site-footer">
      <div className="footer-inner">
        <div className="footer-grid">
          <div className="footer-col">
            <div className="footer-brand-row">
              <img src={LOGO} alt="کلینیک دلسا" width={48} height={48} />
              <p className="footer-brand-name">کلینیک دلسا</p>
            </div>
            <p className="footer-brand-desc">گروه تخصصی مشاوره و خدمات روان‌شناختی</p>
            <h4>دسترسی سریع</h4>
            <ul className="footer-quick">
              <li>
                <a href={LIVE}>خانه</a>
              </li>
              <li>
                <a href={`${LIVE}/%D9%85%D8%B4%D8%A7%D9%88%D8%B1%D8%A7%D9%86/`}>مشاوران</a>
              </li>
              <li>
                <a href={`${LIVE}/blog/`}>وبلاگ</a>
              </li>
              <li>
                <a href={`${LIVE}/%D8%AF%D8%B1%D8%A8%D8%A7%D8%B1%D9%87-%D9%85%D8%A7/`}>درباره ما</a>
              </li>
              <li>
                <a href={BOOK}>فرم نوبت‌دهی</a>
              </li>
            </ul>
          </div>

          <div className="footer-col">
            <h4>ارتباط با ما</h4>
            <p className="footer-clinic-title">کلینیک دلسا — سعادت‌آباد</p>
            <p className="footer-address">
              سعادت‌آباد، خیابان علامه جنوبی، نبش خیابان حق‌طلب غربی، پلاک ۸۰، ساختمان علامه، طبقه ۶،
              واحد ۴
            </p>
            <a
              href="https://maps.google.com/?q=35.779,51.375"
              target="_blank"
              rel="noopener noreferrer"
              className="footer-maps-btn"
            >
              مسیریابی در Google Maps
            </a>
            <div className="footer-phones">
              <p className="footer-phones-label">تلفن</p>
              <a href="tel:+989025680372" className="footer-phone-main dir-ltr">
                ۰۹۰۲-۵۶۸۰۳۷۲
              </a>
              <a href="tel:+982122091743" className="footer-phone-sub dir-ltr">
                ۰۲۱-۲۲۰۹۱۷۴۳
              </a>
            </div>
            <div className="footer-email">
              <p className="footer-phones-label">ایمیل</p>
              <a href="mailto:info@delsaclinic.com" className="dir-ltr">
                info@delsaclinic.com
              </a>
            </div>
          </div>

          <div className="footer-col footer-col--map">
            <iframe
              src="https://www.openstreetmap.org/export/embed.html?bbox=51.365%2C35.774%2C51.385%2C35.784&layer=mapnik&marker=35.779%2C51.375"
              loading="lazy"
              title="موقعیت کلینیک دلسا"
            />
          </div>
        </div>
        <div className="footer-bottom">
          <p>© 2026 Delsa Clinic. All Rights Reserved</p>
        </div>
      </div>
    </footer>
  )
}

function Reveal({
  children,
  className = '',
  delay = 0,
}: {
  children: ReactNode
  className?: string
  delay?: number
}) {
  const reduce = useReducedMotion()
  const transition: Transition = reduce
    ? { duration: 0 }
    : { duration: 0.55, ease: [0.22, 1, 0.36, 1], delay }

  return (
    <motion.div
      className={className}
      initial={reduce ? false : { opacity: 0, y: 18 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, amount: 0.2 }}
      transition={transition}
    >
      {children}
    </motion.div>
  )
}

function Hero() {
  const reduce = useReducedMotion()
  const [index, setIndex] = useState(0)

  useEffect(() => {
    if (reduce) return
    const id = window.setInterval(() => {
      setIndex((i) => (i + 1) % CLINIC.length)
    }, 5200)
    return () => window.clearInterval(id)
  }, [reduce])

  const fade = reduce
    ? { duration: 0 }
    : { duration: 1.1, ease: [0.22, 1, 0.36, 1] as const }

  return (
    <section className="hero" id="hero">
      <div className="hero__media" aria-hidden="true">
        <AnimatePresence mode="sync">
          <motion.img
            key={CLINIC[index]}
            className="hero__photo"
            src={CLINIC[index]}
            alt=""
            initial={reduce ? false : { opacity: 0, scale: 1.04 }}
            animate={{ opacity: 1, scale: 1 }}
            exit={reduce ? undefined : { opacity: 0 }}
            transition={fade}
          />
        </AnimatePresence>
        <div className="hero__mesh" />
        <div className="hero__veil" />
      </div>

      <div className="hero__inner">
        <motion.div
          className="hero__copy"
          initial={reduce ? false : 'hidden'}
          animate="show"
          variants={{
            hidden: {},
            show: {
              transition: { staggerChildren: reduce ? 0 : 0.12, delayChildren: 0.15 },
            },
          }}
        >
          {(
            [
              <p className="hero__brand" key="b">
                کلینیک دلسا
              </p>,
              <h1 className="hero__title" key="t">
                اینجا امن هستید.
                <br />
                اینجا دیده می‌شوید.
              </h1>,
              <p className="hero__lead" key="l">
                مسیر سلامت روان را ساده، امن و تخصصی طی کنید؛ با تیمی که واقعاً کنارتان
                می‌ماند.
              </p>,
            ] as const
          ).map((node) => (
            <motion.div
              key={node.key}
              variants={{
                hidden: { opacity: 0, y: 22 },
                show: { opacity: 1, y: 0 },
              }}
              transition={
                reduce ? { duration: 0 } : { duration: 0.55, ease: [0.22, 1, 0.36, 1] }
              }
            >
              {node}
            </motion.div>
          ))}
          <motion.div
            className="hero__actions"
            variants={{
              hidden: { opacity: 0, y: 18 },
              show: { opacity: 1, y: 0 },
            }}
            transition={reduce ? { duration: 0 } : { duration: 0.5 }}
          >
            <motion.a
              className="btn btn--primary"
              href={BOOK}
              whileHover={reduce ? undefined : { y: -2 }}
              whileTap={reduce ? undefined : { scale: 0.98 }}
            >
              درخواست وقت ملاقات
            </motion.a>
            <a className="btn btn--ghost" href="tel:+989025680372">
              تماس تلفنی
            </a>
          </motion.div>
        </motion.div>
      </div>

      <div className="hero__dots" role="tablist" aria-label="اسلایدهای فضای کلینیک">
        {CLINIC.map((src, i) => (
          <button
            key={src}
            type="button"
            className={`hero__dot${i === index ? ' is-active' : ''}`}
            aria-label={`اسلاید ${i + 1}`}
            aria-selected={i === index}
            onClick={() => setIndex(i)}
          />
        ))}
      </div>
    </section>
  )
}

function Testimonials() {
  const reduce = useReducedMotion()
  const [index, setIndex] = useState(0)

  useEffect(() => {
    if (reduce) return
    const id = window.setInterval(() => {
      setIndex((i) => (i + 1) % TESTIMONIALS.length)
    }, 5200)
    return () => window.clearInterval(id)
  }, [reduce])

  const t = TESTIMONIALS[index]

  return (
    <div className="testimonials">
      <p className="testimonials__label">نظر مراجعان</p>
      <div className="testimonials__viewport">
        <AnimatePresence mode="wait">
          <motion.blockquote
            key={t.name}
            initial={reduce ? false : { opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            exit={reduce ? undefined : { opacity: 0, y: -6 }}
            transition={reduce ? { duration: 0 } : { duration: 0.35 }}
          >
            <p className="testimonials__text">«{t.text}»</p>
            <footer className="testimonials__meta">
              <span className="testimonials__name">{t.name}</span>
              <span className="testimonials__role">{t.role}</span>
            </footer>
          </motion.blockquote>
        </AnimatePresence>
      </div>
      <div className="testimonials__dots">
        {TESTIMONIALS.map((item, i) => (
          <button
            key={item.name}
            type="button"
            className={`testimonials__dot${i === index ? ' is-active' : ''}`}
            aria-label={`نظر ${i + 1}`}
            onClick={() => setIndex(i)}
          />
        ))}
      </div>
    </div>
  )
}

export default function App() {
  const reduce = useReducedMotion()

  return (
    <div className="delsa-home" data-home-version="motion-1.1">
      <a className="skip" href="#main">
        رفتن به محتوا
      </a>
      <SiteHeader />
      <main id="main">
        <Hero />

        <div className="home-body">
          <div className="wrap home-stack">
            <Reveal>
              <ul className="trust-row" aria-label="آمار کلینیک">
                <li>
                  <strong>+۱۵</strong>
                  <span>سال تجربه تخصصی</span>
                </li>
                <li>
                  <strong>۶</strong>
                  <span>دپارتمان چندتخصصی</span>
                </li>
                <li>
                  <strong>۲۴/۷</strong>
                  <span>پشتیبانی هماهنگی</span>
                </li>
              </ul>
            </Reveal>

            <Reveal>
              <section className="home-panel" aria-labelledby="services-title">
                <header className="home-panel__head">
                  <div>
                    <p className="home-kicker">دپارتمان‌ها</p>
                    <h2 id="services-title">خدمات تخصصی کلینیک</h2>
                    <p className="home-panel__lead">
                      شش دپارتمان تخصصی؛ برای فرد، رابطه، کودک و مسیر رشد.
                    </p>
                  </div>
                </header>
                <div className="services-grid">
                  {SERVICES.map((s, i) => (
                    <motion.a
                      key={s.title}
                      className="tile"
                      href={s.href}
                      initial={reduce ? false : { opacity: 0, y: 16 }}
                      whileInView={{ opacity: 1, y: 0 }}
                      viewport={{ once: true, amount: 0.2 }}
                      transition={
                        reduce
                          ? { duration: 0 }
                          : { duration: 0.45, delay: i * 0.05, ease: [0.22, 1, 0.36, 1] }
                      }
                      whileHover={reduce ? undefined : { y: -4 }}
                    >
                      <div className="tile__media">
                        <img src={s.img} alt="" loading="lazy" />
                        <span className="tile__shade" />
                      </div>
                      <div className="tile__body">
                        <h3>{s.title}</h3>
                        <p>{s.desc}</p>
                        <span className="tile__cta">مشاهده دپارتمان</span>
                      </div>
                    </motion.a>
                  ))}
                </div>
              </section>
            </Reveal>

            <Reveal>
              <section className="home-panel" aria-labelledby="why-title">
                <header className="home-panel__head">
                  <div>
                    <p className="home-kicker">چرا ما</p>
                    <h2 id="why-title">چرا دلسا</h2>
                    <p className="home-panel__lead">
                      اعتماد، تخصص و دسترسی آسان، در فضایی که برای شنیده شدن طراحی شده
                      است.
                    </p>
                  </div>
                </header>
                <div className="why-grid">
                  {[
                    ['تیم تخصصی', 'روان‌شناسان و روان‌پزشکان مجرب با رویکرد علمی و انسانی'],
                    ['هماهنگی سریع', 'ثبت درخواست آنلاین و پاسخگویی تیم پذیرش در اسرع وقت'],
                    ['حضوری و آنلاین', 'انتخاب روش مشاوره متناسب با شرایط و نیاز شما'],
                  ].map(([title, body]) => (
                    <article className="why-card" key={title}>
                      <h3>{title}</h3>
                      <p>{body}</p>
                    </article>
                  ))}
                </div>
                <Testimonials />
              </section>
            </Reveal>

            <Reveal>
              <section className="home-panel" aria-labelledby="space-title">
                <header className="home-panel__head">
                  <div>
                    <p className="home-kicker">محیط</p>
                    <h2 id="space-title">فضای کلینیک</h2>
                    <p className="home-panel__lead">
                      اتاق‌هایی با نور ملایم، رنگ‌های آرام و محیطی امن برای گفت‌وگو.
                    </p>
                  </div>
                </header>
                <div className="space-mosaic">
                  {CLINIC.map((src, i) => (
                    <figure
                      key={src}
                      className={
                        i === 0
                          ? 'space-mosaic__main'
                          : `space-mosaic__cell space-mosaic__cell--${i}`
                      }
                    >
                      <motion.img
                        src={src}
                        alt={i === 0 ? 'فضای کلینیک دلسا' : ''}
                        loading="lazy"
                        whileHover={reduce ? undefined : { scale: 1.04 }}
                        transition={{ type: 'spring', stiffness: 280, damping: 26 }}
                      />
                    </figure>
                  ))}
                </div>
              </section>
            </Reveal>

            <Reveal>
              <section className="home-panel home-panel--cta" aria-labelledby="cta-title">
                <header className="home-panel__head home-panel__head--center">
                  <div>
                    <p className="home-kicker">نوبت‌دهی</p>
                    <h2 id="cta-title">آماده‌اید شروع کنید؟</h2>
                  </div>
                </header>
                <p className="cta-lead">هماهنگی نوبت حضوری، تلفنی یا آنلاین در چند دقیقه.</p>
                <motion.a
                  className="btn btn--primary"
                  href={BOOK}
                  whileHover={reduce ? undefined : { y: -2 }}
                  whileTap={reduce ? undefined : { scale: 0.98 }}
                >
                  درخواست وقت ملاقات
                </motion.a>
              </section>
            </Reveal>
          </div>
        </div>
      </main>
      <SiteFooter />
      <div className="mobile-cta-bar">
        <a href="tel:+989025680372" className="mobile-cta-bar__call">
          تماس
        </a>
        <a href={BOOK} className="mobile-cta-bar__book">
          درخواست وقت
        </a>
      </div>
    </div>
  )
}
