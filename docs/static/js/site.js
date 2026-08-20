/* site.js — GSAP hero timeline + site motion */
(function () {
  'use strict';

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  document.documentElement.classList.add('js-motion');

  var gsap = window.gsap;
  var hasGsap = !!gsap && !reduceMotion;
  if (hasGsap) document.documentElement.classList.add('js-gsap');
  if (hasGsap && window.ScrollTrigger) gsap.registerPlugin(window.ScrollTrigger);

  // Header
  var header = document.getElementById('site-header');
  if (header) {
    var onScroll = function () {
      header.classList.toggle('scrolled', window.scrollY > 16);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  // Sidebar
  var sidebar = document.getElementById('sidebar');
  var backdrop = document.getElementById('sidebar-backdrop');
  var openBtn = document.getElementById('sidebar-open');
  var closeBtn = document.getElementById('sidebar-close');
  var mobileBar = document.getElementById('mobile-cta-bar');
  var waFloat = document.getElementById('whatsapp-float');

  function setOverlay(active) {
    if (mobileBar) mobileBar.classList.toggle('hidden-by-overlay', active);
    if (waFloat) waFloat.classList.toggle('hidden-by-overlay', active);
  }

  if (sidebar && backdrop && openBtn && closeBtn) {
    function openSidebar() {
      sidebar.classList.add('open');
      backdrop.classList.add('open');
      sidebar.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
      setOverlay(true);
    }
    function closeSidebar() {
      sidebar.classList.remove('open');
      backdrop.classList.remove('open');
      sidebar.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
      setOverlay(false);
    }
    openBtn.addEventListener('click', openSidebar);
    closeBtn.addEventListener('click', closeSidebar);
    backdrop.addEventListener('click', closeSidebar);
    sidebar.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', closeSidebar);
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && sidebar.classList.contains('open')) closeSidebar();
    });
  }

  // Mobile CTA
  var hero = document.getElementById('hero');
  if (mobileBar && hero && window.matchMedia('(max-width: 1023px)').matches) {
    var barObs = new IntersectionObserver(function (entries) {
      mobileBar.classList.toggle('visible', !entries[0].isIntersecting);
    }, { threshold: 0 });
    barObs.observe(hero);
  }

  function showIn(el) {
    el.classList.add('is-in');
  }

  // ─── Hero: real GSAP master timeline ─────────────────────
  var glass = document.querySelector('[data-hero-glass]');
  var heroItems = Array.prototype.slice.call(document.querySelectorAll('[data-hero-item]'));
  var slides = Array.prototype.slice.call(document.querySelectorAll('[data-hero-slide]'));
  var dotsWrap = document.getElementById('hero-dots');
  var reel = document.querySelector('.hero__reel');
  var veil = document.querySelector('.hero__veil');
  var mesh = document.querySelector('.hero__mesh');
  var shine = document.querySelector('.hero__shine');
  var slideIndex = 0;
  var slideTimer = null;
  var kenTween = null;
  var transitioning = false;

  function setDots(active) {
    if (!dotsWrap) return;
    Array.prototype.forEach.call(dotsWrap.children, function (dot, i) {
      dot.classList.toggle('is-active', i === active);
      dot.setAttribute('aria-selected', i === active ? 'true' : 'false');
    });
  }

  function startKenBurns(photo) {
    if (!hasGsap || !photo) return;
    if (kenTween) kenTween.kill();
    gsap.set(photo, { scale: 1.16, transformOrigin: '50% 42%' });
    kenTween = gsap.to(photo, {
      scale: 1.04,
      duration: 5.2,
      ease: 'none',
    });
  }

  function goToSlide(nextIndex) {
    if (slides.length < 2 || transitioning) return;
    var next = (nextIndex + slides.length) % slides.length;
    if (next === slideIndex) return;
    transitioning = true;

    var current = slides[slideIndex];
    var upcoming = slides[next];
    slideIndex = next;
    setDots(slideIndex);

    if (!hasGsap) {
      current.classList.remove('is-active');
      current.classList.add('is-leaving');
      upcoming.classList.add('is-active');
      window.setTimeout(function () {
        current.classList.remove('is-leaving');
        transitioning = false;
      }, 900);
      return;
    }

    if (kenTween) kenTween.kill();
    current.classList.add('is-leaving');
    upcoming.classList.add('is-active');

    var cross = gsap.timeline({
      defaults: { ease: 'power2.inOut' },
      onComplete: function () {
        current.classList.remove('is-active', 'is-leaving');
        gsap.set(current, { opacity: 0, scale: 1.16 });
        transitioning = false;
        startKenBurns(upcoming);
      },
    });

    cross
      .fromTo(
        upcoming,
        { opacity: 0, scale: 1.18 },
        { opacity: 1, scale: 1.12, duration: 1.35, ease: 'power2.out' },
        0
      )
      .to(current, { opacity: 0, scale: 1.02, duration: 1.2 }, 0.08)
      .fromTo(
        veil,
        { opacity: 0.85 },
        { opacity: 1, duration: 0.55, yoyo: true, repeat: 1, ease: 'sine.inOut' },
        0
      );
  }

  function buildDots() {
    if (!dotsWrap || slides.length < 2) return;
    dotsWrap.innerHTML = '';
    slides.forEach(function (_, i) {
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'hero__dot' + (i === 0 ? ' is-active' : '');
      btn.setAttribute('role', 'tab');
      btn.setAttribute('aria-label', 'اسلاید ' + (i + 1));
      btn.addEventListener('click', function () {
        goToSlide(i);
        if (slideTimer) {
          window.clearInterval(slideTimer);
          slideTimer = window.setInterval(function () {
            goToSlide(slideIndex + 1);
          }, 5200);
        }
      });
      dotsWrap.appendChild(btn);
    });
  }

  buildDots();

  if (hasGsap && hero) {
    // Initial layered state
    gsap.set(slides, { opacity: 0, scale: 1.2, transformOrigin: '50% 42%' });
    if (slides[0]) {
      slides[0].classList.add('is-active');
      gsap.set(slides[0], { opacity: 1, scale: 1.18 });
    }
    if (veil) gsap.set(veil, { opacity: 0 });
    if (mesh) gsap.set(mesh, { opacity: 0, scale: 1.08 });
    if (shine) gsap.set(shine, { opacity: 0, xPercent: 40 });
    if (glass) gsap.set(glass, { opacity: 0, y: 36, scale: 0.92, filter: 'blur(8px)' });
    if (heroItems.length) gsap.set(heroItems, { opacity: 0, y: 22 });
    if (dotsWrap) gsap.set(dotsWrap, { opacity: 0, y: 12 });

    var intro = gsap.timeline({
      defaults: { ease: 'power3.out' },
      onComplete: function () {
        if (dotsWrap) dotsWrap.classList.add('is-ready');
        startKenBurns(slides[slideIndex]);
        if (slides.length > 1) {
          slideTimer = window.setInterval(function () {
            goToSlide(slideIndex + 1);
          }, 5200);
        }
      },
    });

    intro
      .addLabel('boot', 0)
      // Photo layer wakes up
      .to(
        slides[0],
        { scale: 1.1, duration: 1.8, ease: 'power2.out' },
        'boot'
      )
      // Atmosphere layers
      .to(veil, { opacity: 1, duration: 1.1 }, 'boot+=0.15')
      .to(mesh, { opacity: 1, scale: 1, duration: 1.4 }, 'boot+=0.2')
      .to(shine, { opacity: 1, xPercent: -30, duration: 1.6, ease: 'power1.inOut' }, 'boot+=0.35')
      // Glass card arrives
      .to(
        glass,
        {
          opacity: 1,
          y: 0,
          scale: 1,
          filter: 'blur(0px)',
          duration: 0.95,
          ease: 'power3.out',
        },
        'boot+=0.45'
      )
      // Copy stagger inside glass
      .to(
        heroItems,
        {
          opacity: 1,
          y: 0,
          duration: 0.7,
          stagger: 0.12,
          ease: 'power3.out',
        },
        'boot+=0.62'
      )
      // Dots
      .to(dotsWrap, { opacity: 1, y: 0, duration: 0.55 }, 'boot+=1.15')
      // Soft settle on glass
      .to(
        glass,
        {
          boxShadow: '0 28px 70px rgba(10,24,32,0.34), inset 0 1px 0 rgba(255,255,255,0.4)',
          duration: 1.2,
          yoyo: true,
          repeat: -1,
          ease: 'sine.inOut',
        },
        'boot+=1.4'
      );

    // Scroll parallax on photo reel
    if (reel && window.ScrollTrigger) {
      gsap.to(reel, {
        yPercent: 14,
        ease: 'none',
        scrollTrigger: {
          trigger: hero,
          start: 'top top',
          end: 'bottom top',
          scrub: true,
        },
      });
      gsap.to(glass, {
        y: -18,
        opacity: 0.92,
        ease: 'none',
        scrollTrigger: {
          trigger: hero,
          start: 'top top',
          end: 'bottom top',
          scrub: true,
        },
      });
    }
  } else {
    // Fallback without GSAP
    if (glass) showIn(glass);
    heroItems.forEach(showIn);
    if (dotsWrap) {
      dotsWrap.classList.add('is-ready');
      gsap && gsap.set(dotsWrap, { clearProps: 'all' });
    }
    if (slides[0]) slides[0].classList.add('is-active');
    if (slides.length > 1 && !reduceMotion) {
      slideTimer = window.setInterval(function () {
        goToSlide(slideIndex + 1);
      }, 4500);
    }
  }

  // Scroll reveals
  var reveals = Array.prototype.slice.call(
    document.querySelectorAll('[data-reveal], .reveal')
  );
  if (reduceMotion) {
    reveals.forEach(showIn);
  } else if ('IntersectionObserver' in window) {
    var revObs = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          showIn(entry.target);
          revObs.unobserve(entry.target);
        });
      },
      { threshold: 0.12, rootMargin: '0px 0px -8% 0px' }
    );
    reveals.forEach(function (el) {
      if (el.closest('.hero')) return;
      revObs.observe(el);
    });
  } else {
    reveals.forEach(showIn);
  }
  window.setTimeout(function () { reveals.forEach(showIn); }, 2800);

  // Count-up
  function toFa(n) {
    return String(n).replace(/\d/g, function (d) {
      return '۰۱۲۳۴۵۶۷۸۹'[d];
    });
  }

  function animateCount(el) {
    var target = parseInt(el.getAttribute('data-count'), 10);
    if (!target && target !== 0) return;
    var prefix = el.getAttribute('data-prefix') || '';
    var suffix = el.getAttribute('data-suffix') || '';
    if (reduceMotion) {
      el.textContent = prefix + toFa(target) + suffix;
      return;
    }
    var start = null;
    var dur = 1100;
    function frame(ts) {
      if (!start) start = ts;
      var p = Math.min(1, (ts - start) / dur);
      var eased = 1 - Math.pow(1 - p, 3);
      el.textContent = prefix + toFa(Math.round(target * eased)) + suffix;
      if (p < 1) requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
  }

  var counters = Array.prototype.slice.call(document.querySelectorAll('[data-count]'));
  if (counters.length) {
    if (reduceMotion) {
      counters.forEach(animateCount);
    } else if ('IntersectionObserver' in window) {
      var cObs = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            animateCount(entry.target);
            cObs.unobserve(entry.target);
          });
        },
        { threshold: 0.4 }
      );
      counters.forEach(function (el) { cObs.observe(el); });
    } else {
      counters.forEach(animateCount);
    }
  }

  // FAQ
  document.querySelectorAll('.faq__item').forEach(function (item) {
    item.addEventListener('toggle', function () {
      if (!item.open) return;
      document.querySelectorAll('.faq__item[open]').forEach(function (other) {
        if (other !== item) other.open = false;
      });
    });
  });

  // Card tilt
  if (hasGsap) {
    gsap.utils.toArray('.service-tile, .why-card, .article-card, .step').forEach(function (card) {
      card.addEventListener('mousemove', function (e) {
        var rect = card.getBoundingClientRect();
        var x = (e.clientX - rect.left) / rect.width - 0.5;
        var y = (e.clientY - rect.top) / rect.height - 0.5;
        gsap.to(card, {
          rotateY: x * -5,
          rotateX: y * 5,
          transformPerspective: 800,
          duration: 0.35,
          ease: 'power2.out',
        });
      });
      card.addEventListener('mouseleave', function () {
        gsap.to(card, { rotateY: 0, rotateX: 0, duration: 0.5, ease: 'power3.out' });
      });
    });
  }
})();
