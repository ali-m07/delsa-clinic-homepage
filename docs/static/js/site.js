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

  // Reveal on scroll (always works)
  var reveals = Array.prototype.slice.call(document.querySelectorAll('.reveal'));
  function showReveal(el) {
    el.classList.add('is-in');
  }

  if (reduceMotion) {
    reveals.forEach(showReveal);
  } else {
    // Hero text first, staggered
    var heroReveals = reveals.filter(function (el) {
      return el.closest('.hero');
    });
    heroReveals.forEach(function (el, i) {
      window.setTimeout(function () {
        showReveal(el);
      }, 120 + i * 110);
    });

    var rest = reveals.filter(function (el) {
      return !el.closest('.hero');
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
        { threshold: 0.12, rootMargin: '0px 0px -8% 0px' }
      );
      rest.forEach(function (el) {
        revObs.observe(el);
      });
    } else {
      rest.forEach(showReveal);
    }
  }

  // Safety: never leave content invisible
  window.setTimeout(function () {
    reveals.forEach(showReveal);
  }, 2500);

  // Hero cinematic reel (photo crossfade = video-like motion)
  var slides = Array.prototype.slice.call(document.querySelectorAll('[data-hero-slide]'));
  if (slides.length > 1 && !reduceMotion) {
    var index = 0;
    window.setInterval(function () {
      var current = slides[index];
      index = (index + 1) % slides.length;
      var next = slides[index];
      current.classList.remove('is-active');
      current.classList.add('is-leaving');
      next.classList.add('is-active');
      window.setTimeout(function () {
        current.classList.remove('is-leaving');
      }, 1400);
    }, 4200);
  }

  // Optional GSAP enrichment
  if (!window.gsap || reduceMotion) return;
  var gsap = window.gsap;
  if (window.ScrollTrigger) gsap.registerPlugin(window.ScrollTrigger);

  var activePhoto = document.querySelector('.hero__photo.is-active');
  if (activePhoto && window.ScrollTrigger) {
    gsap.to('.hero__reel', {
      yPercent: 10,
      ease: 'none',
      scrollTrigger: {
        trigger: '.hero',
        start: 'top top',
        end: 'bottom top',
        scrub: true,
      },
    });
  }

  gsap.to('.stat-card', {
    y: -4,
    duration: 2.4,
    yoyo: true,
    repeat: -1,
    ease: 'sine.inOut',
    stagger: 0.35,
  });
})();
