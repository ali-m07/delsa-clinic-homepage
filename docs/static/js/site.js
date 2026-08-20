(function () {
  var openBtn = document.querySelector("[data-sidebar-open]");
  var closeBtn = document.querySelector("[data-sidebar-close]");
  var backdrop = document.querySelector("[data-sidebar-backdrop]");
  var sidebar = document.getElementById("delsa-sidebar");
  var header = document.getElementById("delsa-site-header");

  function openSidebar() {
    if (!sidebar || !backdrop) return;
    sidebar.classList.add("open");
    backdrop.classList.add("open");
  }
  function closeSidebar() {
    if (!sidebar || !backdrop) return;
    sidebar.classList.remove("open");
    backdrop.classList.remove("open");
  }

  if (openBtn) openBtn.addEventListener("click", openSidebar);
  if (closeBtn) closeBtn.addEventListener("click", closeSidebar);
  if (backdrop) backdrop.addEventListener("click", closeSidebar);

  window.addEventListener("scroll", function () {
    if (!header) return;
    header.classList.toggle("scrolled", window.scrollY > 8);
  });
})();
