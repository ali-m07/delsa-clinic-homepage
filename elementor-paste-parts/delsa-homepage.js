(function () {
  function loadScript(src) {
    return new Promise(function (resolve, reject) {
      if (window.gsap && src.indexOf("gsap.min") !== -1) { resolve(); return; }
      if (window.ScrollTrigger && src.indexOf("ScrollTrigger") !== -1) { resolve(); return; }
      var existing = document.querySelector('script[src="' + src + '"]');
      if (existing) { existing.addEventListener("load", function () { resolve(); }); resolve(); return; }
      var s = document.createElement("script");
      s.src = src;
      s.onload = function () { resolve(); };
      s.onerror = function () { resolve(); };
      document.head.appendChild(s);
    });
  }

  function start() {
    var reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

    var gsap = window.gsap;
    var hasGsap = !!gsap && !reduceMotion;
    if (hasGsap && window.ScrollTrigger) gsap.registerPlugin(window.ScrollTrigger);

    var mobileCta = document.getElementById("mobile-cta");
    var hero = document.getElementById("hero");
    if (mobileCta && hero && window.matchMedia("(max-width: 1023px)").matches) {
      var barObs = new IntersectionObserver(function (entries) {
        mobileCta.classList.toggle("visible", !entries[0].isIntersecting);
      }, { threshold: 0 });
      barObs.observe(hero);
    }

    var copy = document.querySelector("[data-hero-copy]");
    var heroItems = Array.prototype.slice.call(document.querySelectorAll("[data-hero-item]"));
    var slides = Array.prototype.slice.call(document.querySelectorAll("[data-hero-slide]"));
    var dotsWrap = document.getElementById("hero-dots");
    var reel = document.getElementById("hero-reel");
    var veil = document.querySelector(".hero__veil");
    var mesh = document.querySelector(".hero__mesh");
    var shine = document.querySelector(".hero__shine");
    var slideIndex = 0, slideTimer = null, kenTween = null, transitioning = false;

    function showIn(el) { el.classList.add("is-in"); }
    function setDots(active) {
      if (!dotsWrap) return;
      Array.prototype.forEach.call(dotsWrap.children, function (dot, i) {
        dot.classList.toggle("is-active", i === active);
      });
    }
    function startKenBurns(photo) {
      if (!hasGsap || !photo) return;
      if (kenTween) kenTween.kill();
      gsap.set(photo, { scale: 1.16, transformOrigin: "50% 42%" });
      kenTween = gsap.to(photo, { scale: 1.04, duration: 5.2, ease: "none" });
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
        current.classList.remove("is-active");
        upcoming.classList.add("is-active");
        transitioning = false;
        return;
      }
      if (kenTween) kenTween.kill();
      current.classList.add("is-leaving");
      upcoming.classList.add("is-active");
      gsap.timeline({
        defaults: { ease: "power2.inOut" },
        onComplete: function () {
          current.classList.remove("is-active", "is-leaving");
          gsap.set(current, { opacity: 0, scale: 1.16 });
          transitioning = false;
          startKenBurns(upcoming);
        }
      })
        .fromTo(upcoming, { opacity: 0, scale: 1.18 }, { opacity: 1, scale: 1.12, duration: 1.35, ease: "power2.out" }, 0)
        .to(current, { opacity: 0, scale: 1.02, duration: 1.2 }, 0.08);
    }

    if (dotsWrap && slides.length > 1) {
      slides.forEach(function (_, i) {
        var btn = document.createElement("button");
        btn.type = "button";
        btn.className = "hero__dot" + (i === 0 ? " is-active" : "");
        btn.setAttribute("aria-label", "اسلاید " + (i + 1));
        btn.addEventListener("click", function () {
          goToSlide(i);
          if (slideTimer) {
            clearInterval(slideTimer);
            slideTimer = setInterval(function () { goToSlide(slideIndex + 1); }, 5200);
          }
        });
        dotsWrap.appendChild(btn);
      });
    }

    if (hasGsap && hero) {
      gsap.set(slides, { opacity: 0, scale: 1.2, transformOrigin: "50% 42%" });
      if (slides[0]) {
        slides[0].classList.add("is-active");
        gsap.set(slides[0], { opacity: 1, scale: 1.18 });
      }
      if (veil) gsap.set(veil, { opacity: 0 });
      if (mesh) gsap.set(mesh, { opacity: 0, scale: 1.08 });
      if (shine) gsap.set(shine, { opacity: 0, xPercent: 40 });
      if (copy) gsap.set(copy, { opacity: 0, y: 28 });
      if (heroItems.length) gsap.set(heroItems, { opacity: 0, y: 22 });
      if (dotsWrap) gsap.set(dotsWrap, { opacity: 0, y: 12 });

      gsap.timeline({
        defaults: { ease: "power3.out" },
        onComplete: function () {
          if (dotsWrap) dotsWrap.classList.add("is-ready");
          if (copy) gsap.set(copy, { clearProps: "opacity", opacity: 1, y: 0 });
          if (heroItems.length) gsap.set(heroItems, { clearProps: "opacity", opacity: 1, y: 0 });
          startKenBurns(slides[slideIndex]);
          if (slides.length > 1) {
            slideTimer = setInterval(function () { goToSlide(slideIndex + 1); }, 5200);
          }
          if (reel && window.ScrollTrigger) {
            gsap.to(reel, {
              yPercent: 12, ease: "none",
              scrollTrigger: { trigger: hero, start: "top top", end: "bottom top", scrub: true }
            });
          }
        }
      })
        .addLabel("boot", 0)
        .to(slides[0], { scale: 1.1, duration: 1.8, ease: "power2.out" }, "boot")
        .to(veil, { opacity: 1, duration: 1.1 }, "boot+=0.15")
        .to(mesh, { opacity: 1, scale: 1, duration: 1.4 }, "boot+=0.2")
        .to(shine, { opacity: 1, xPercent: -30, duration: 1.6, ease: "power1.inOut" }, "boot+=0.35")
        .to(copy, { opacity: 1, y: 0, duration: 0.9 }, "boot+=0.45")
        .to(heroItems, { opacity: 1, y: 0, duration: 0.7, stagger: 0.12 }, "boot+=0.62")
        .to(dotsWrap, { opacity: 1, y: 0, duration: 0.55 }, "boot+=1.15");
    } else {
      heroItems.forEach(showIn);
      if (dotsWrap) dotsWrap.classList.add("is-ready");
      if (slides[0]) slides[0].classList.add("is-active");
      if (slides.length > 1 && !reduceMotion) {
        slideTimer = setInterval(function () { goToSlide(slideIndex + 1); }, 4500);
      }
    }

    var reveals = Array.prototype.slice.call(document.querySelectorAll("[data-reveal]"));
    if (reduceMotion) {
      reveals.forEach(showIn);
    } else if ("IntersectionObserver" in window) {
      var revObs = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          showIn(entry.target);
          revObs.unobserve(entry.target);
        });
      }, { threshold: 0.12, rootMargin: "0px 0px -8% 0px" });
      reveals.forEach(function (el) { revObs.observe(el); });
    } else {
      reveals.forEach(showIn);
    }

    function toFa(n) {
      return String(n).replace(/\d/g, function (d) { return "۰۱۲۳۴۵۶۷۸۹"[d]; });
    }
    function animateCount(el) {
      var target = parseInt(el.getAttribute("data-count"), 10);
      var prefix = el.getAttribute("data-prefix") || "";
      var suffix = el.getAttribute("data-suffix") || "";
      if (reduceMotion) { el.textContent = prefix + toFa(target) + suffix; return; }
      var start = null, dur = 1100;
      function frame(ts) {
        if (!start) start = ts;
        var p = Math.min(1, (ts - start) / dur);
        var eased = 1 - Math.pow(1 - p, 3);
        el.textContent = prefix + toFa(Math.round(target * eased)) + suffix;
        if (p < 1) requestAnimationFrame(frame);
      }
      requestAnimationFrame(frame);
    }
    var counters = Array.prototype.slice.call(document.querySelectorAll("[data-count]"));
    if ("IntersectionObserver" in window && !reduceMotion) {
      var cObs = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          animateCount(entry.target);
          cObs.unobserve(entry.target);
        });
      }, { threshold: 0.4 });
      counters.forEach(function (el) { cObs.observe(el); });
    } else {
      counters.forEach(animateCount);
    }
  }

  loadScript("https://cdn.jsdelivr.net/npm/gsap@3.12.7/dist/gsap.min.js")
    .then(function () { return loadScript("https://cdn.jsdelivr.net/npm/gsap@3.12.7/dist/ScrollTrigger.min.js"); })
    .then(start)
    .catch(start);
})();
