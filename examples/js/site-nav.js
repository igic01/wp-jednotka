const siteHeader = document.querySelector(".site-header");
const navToggle = siteHeader?.querySelector(".site-nav-toggle");
const siteNav = siteHeader?.querySelector(".site-nav");

if (siteHeader && navToggle && siteNav) {
  const closeMenu = () => {
    siteHeader.classList.remove("is-open");
    navToggle.setAttribute("aria-expanded", "false");
  };

  navToggle.addEventListener("click", () => {
    const isOpen = siteHeader.classList.toggle("is-open");
    navToggle.setAttribute("aria-expanded", String(isOpen));
  });

  siteNav.addEventListener("click", (event) => {
    if (event.target.closest("a")) closeMenu();
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") closeMenu();
  });

  document.addEventListener("click", (event) => {
    if (!siteHeader.contains(event.target)) closeMenu();
  });

  window.addEventListener("resize", () => {
    if (window.innerWidth > 700) closeMenu();
  });
}
