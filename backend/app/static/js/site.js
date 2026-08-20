/* site.js — motion that works even without GSAP */
(function () {
  'use strict';

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  document.documentElement.classList.add('js-motion');

  // Header scroll
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

  // Reveal on scroll
  var reveals = Array.prototype.slice.call(document.querySelectorAll('.reveal'));
  function showReveal(el) {
    el.classList.add('is-in');
  }

  if (reduceMotion) {
    reveals.forEach(showReveal);
  } else {
    var heroReveals = reveals.filter(function (el) {
      return el.closest('.hero') || el.closest('.trust-strip');
    });
    heroReveals.forEach(function (el, i) {
      window.setTimeout(function () {
        showReveal(el);
      }, 80 + i * 90);
    });

    var rest = reveals.filter(function (el) {
      return !el.closest('.hero') && !el.closest('.trust-strip');
    });
    if ('IntersectionObserver' in window) {
      var revObs = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) {
              showReveal(entry.target);
              revObs.unobserve(entry.target);
            }
          });
        },
        { threshold: 0.1, rootMargin: '0px 0px -6% 0px' }
      );
      rest.forEach(function (el) {
        revObs.observe(el);
      });
    } else {
      rest.forEach(showReveal);
    }
  }

  window.setTimeout(function () {
    reveals.forEach(showReveal);
  }, 2200);

  // Hero cinematic reel
  var slides = Array.prototype.slice.call(document.querySelectorAll('[data-hero-slide]'));
  var dotsWrap = document.getElementById('hero-dots');
  var index = 0;
  var timer = null;

  function setDots(active) {
    if (!dotsWrap) return;
    Array.prototype.forEach.call(dotsWrap.children, function (dot, i) {
      dot.classList.toggle('is-active', i === active);
    });
  }

  function goTo(nextIndex) {
    if (!slides.length) return;
    var current = slides[index];
    index = (nextIndex + slides.length) % slides.length;
    var next = slides[index];
    if (current === next) return;
    current.classList.remove('is-active');
    current.classList.add('is-leaving');
    // restart ken-burns
    next.style.animation = 'none';
    // force reflow
    void next.offsetWidth;
    next.style.animation = '';
    next.classList.add('is-active');
    setDots(index);
    window.setTimeout(function () {
      current.classList.remove('is-leaving');
    }, 950);
  }

  if (slides.length > 1 && dotsWrap) {
    slides.forEach(function (_, i) {
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'hero__dot' + (i === 0 ? ' is-active' : '');
      btn.setAttribute('aria-label', 'اسلاید ' + (i + 1));
      btn.addEventListener('click', function () {
        goTo(i);
        if (timer) {
          window.clearInterval(timer);
          timer = window.setInterval(function () {
            goTo(index + 1);
          }, 3200);
        }
      });
      dotsWrap.appendChild(btn);
    });
  }

  if (slides.length > 1 && !reduceMotion) {
    timer = window.setInterval(function () {
      goTo(index + 1);
    }, 3200);
  }

  // Horizontal services rail — scroll-linked nudge + wheel support
  var rail = document.querySelector('[data-services-rail]');
  if (rail && !reduceMotion) {
    rail.addEventListener(
      'wheel',
      function (e) {
        if (Math.abs(e.deltaY) <= Math.abs(e.deltaX)) return;
        var rtl = getComputedStyle(document.documentElement).direction === 'rtl';
        rail.scrollBy({ left: e.deltaY * (rtl ? 1 : -1) });
        e.preventDefault();
      },
      { passive: false }
    );
  }

  // Optional GSAP enrichment
  if (!window.gsap || reduceMotion) return;
  var gsap = window.gsap;
  if (window.ScrollTrigger) gsap.registerPlugin(window.ScrollTrigger);

  if (document.querySelector('.hero__reel') && window.ScrollTrigger) {
    gsap.to('.hero__reel', {
      yPercent: 8,
      ease: 'none',
      scrollTrigger: {
        trigger: '.hero',
        start: 'top top',
        end: 'bottom top',
        scrub: true,
      },
    });
  }

  if (rail && window.ScrollTrigger) {
    gsap.from('.service-tile', {
      opacity: 0,
      y: 28,
      scale: 0.96,
      duration: 0.7,
      stagger: 0.08,
      ease: 'power3.out',
      scrollTrigger: {
        trigger: '.services-section',
        start: 'top 78%',
        once: true,
      },
    });
  }

  gsap.utils.toArray('.service-tile, .consultant-card, .why-card').forEach(function (card) {
    card.addEventListener('mousemove', function (e) {
      var rect = card.getBoundingClientRect();
      var x = (e.clientX - rect.left) / rect.width - 0.5;
      var y = (e.clientY - rect.top) / rect.height - 0.5;
      gsap.to(card, {
        rotateY: x * -4,
        rotateX: y * 4,
        transformPerspective: 700,
        duration: 0.35,
        ease: 'power2.out',
      });
    });
    card.addEventListener('mouseleave', function () {
      gsap.to(card, { rotateY: 0, rotateX: 0, duration: 0.45, ease: 'power3.out' });
    });
  });
})();
