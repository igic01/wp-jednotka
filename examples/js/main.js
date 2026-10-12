// Site-wide setup: smooth scroll (Lenis) wired to ScrollTrigger, header state.
// Runs on every page. Loaded after gsap-config.js, before page-specific animation files.

const lenis = new Lenis({
  duration: 1.1,
  easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
});

lenis.on("scroll", ScrollTrigger.update);

gsap.ticker.add((time) => {
  lenis.raf(time * 1000);
});
gsap.ticker.lagSmoothing(0);

// Shrink/style header once the page has scrolled a bit.
const header = document.querySelector(".site-header");
if (header) {
  ScrollTrigger.create({
    start: "top -80",
    onUpdate: (self) => {
      header.classList.toggle("is-scrolled", self.scroll() > 80);
    },
  });
}
