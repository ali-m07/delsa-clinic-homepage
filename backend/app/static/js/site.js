/* site.js — alive motion for Delsa */
(function () {
  'use strict';

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  document.documentElement.classList.add('js-motion');

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

  // Hero entrance
  var glass = document.querySelector('[data-hero-glass]');
  var heroItems = Array.prototype.slice.call(document.querySelectorAll('[data-hero-item]'));
  if (reduceMotion) {
    if (glass) showIn(glass);
    heroItems.forEach(showIn);
  } else {
    if (glass) {
      window.setTimeout(function () { showIn(glass); }, 80);
    }
    heroItems.forEach(function (el, i) {
      window.setTimeout(function () { showIn(el); }, 220 + i * 120);
    });
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

  // Count-up stats
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

  // Hero reel
  var slides = Array.prototype.slice.call(document.querySelectorAll('[data-hero-slide]'));
  var dotsWrap = document.getElementById('hero-dots');
  var index = 0;
  var timer = null;

  function setDots(active) {
    if (!dotsWrap) return;
    Array.prototype.forEach.call(dotsWrap.children, function (dot, i) {
      dot.classList.toggle('is-active', i === active);
      dot.setAttribute('aria-selected', i === active ? 'true' : 'false');
    });
  }

  function goTo(nextIndex) {
    if (slides.length < 2) return;
    var current = slides[index];
    index = (nextIndex + slides.length) % slides.length;
    var next = slides[index];
    if (current === next) return;
    current.classList.remove('is-active');
    current.classList.add('is-leaving');
    next.style.animation = 'none';
    void next.offsetWidth;
    next.style.animation = '';
    next.classList.add('is-active');
    setDots(index);
    window.setTimeout(function () {
      current.classList.remove('is-leaving');
    }, 1200);
  }

  if (slides.length > 1 && dotsWrap) {
    slides.forEach(function (_, i) {
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'hero__dot' + (i === 0 ? ' is-active' : '');
      btn.setAttribute('role', 'tab');
      btn.setAttribute('aria-label', 'اسلاید ' + (i + 1));
      btn.addEventListener('click', function () {
        goTo(i);
        if (timer) {
          window.clearInterval(timer);
          timer = window.setInterval(function () { goTo(index + 1); }, 4500);
        }
      });
      dotsWrap.appendChild(btn);
    });
  }

  if (slides.length > 1 && !reduceMotion) {
    timer = window.setInterval(function () { goTo(index + 1); }, 4500);
  }

  // FAQ soft open (details)
  document.querySelectorAll('.faq__item').forEach(function (item) {
    item.addEventListener('toggle', function () {
      if (!item.open) return;
      document.querySelectorAll('.faq__item[open]').forEach(function (other) {
        if (other !== item) other.open = false;
      });
    });
  });

  // GSAP enrichment
  if (!window.gsap || reduceMotion) return;
  var gsap = window.gsap;
  if (window.ScrollTrigger) gsap.registerPlugin(window.ScrollTrigger);

  if (document.querySelector('.hero__reel') && window.ScrollTrigger) {
    gsap.to('.hero__reel', {
      yPercent: 12,
      ease: 'none',
      scrollTrigger: {
        trigger: '.hero',
        start: 'top top',
        end: 'bottom top',
        scrub: true,
      },
    });
  }

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
})();
