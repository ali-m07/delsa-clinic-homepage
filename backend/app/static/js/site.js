/* site.js — shared page interactions */
(function () {
  'use strict';

  // AOS
  if (window.AOS) {
    var prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    AOS.init({
      duration: prefersReduced ? 0 : 1100,
      easing: 'ease-out-cubic',
      once: true,
      offset: 50,
      disable: prefersReduced,
    });
  }

  // Header scroll shadow
  var header = document.getElementById('site-header');
  if (header) {
    var onScroll = function () {
      header.classList.toggle('scrolled', window.scrollY > 20);
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
      sidebar.classList.add('open'); backdrop.classList.add('open');
      sidebar.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
      setOverlay(true);
    }
    function closeSidebar() {
      sidebar.classList.remove('open'); backdrop.classList.remove('open');
      sidebar.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
      setOverlay(false);
    }
    openBtn.addEventListener('click', openSidebar);
    closeBtn.addEventListener('click', closeSidebar);
    backdrop.addEventListener('click', closeSidebar);
    sidebar.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', closeSidebar); });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && sidebar.classList.contains('open')) closeSidebar();
    });
  }

  // Mobile CTA bar — show after hero leaves viewport
  var hero = document.getElementById('hero');
  if (mobileBar && hero && window.matchMedia('(max-width: 1023px)').matches) {
    var obs = new IntersectionObserver(function (entries) {
      mobileBar.classList.toggle('visible', !entries[0].isIntersecting);
    }, { threshold: 0 });
    obs.observe(hero);
  }
})();
