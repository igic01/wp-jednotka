// Scroll-scrubbed gradient reveal: an element's opacity tracks scroll
// position directly (GSAP ScrollTrigger's `scrub`), so scrolling back up
// un-reveals it in exact reverse — no replay, no re-trigger, no timer.
//
// Usage: <div data-scroll-gradient> inside a section, with a gradient
// background set in CSS and opacity: 0 as its base state.
const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

if (window.gsap && window.ScrollTrigger && !reduceMotion) {
  gsap.registerPlugin(ScrollTrigger);

  document.querySelectorAll("[data-scroll-gradient]").forEach((gradient) => {
    const section = gradient.closest("section") || gradient.parentElement;

    gsap.to(gradient, {
      opacity: 1,
      ease: "power3.out",
      scrollTrigger: {
        trigger: section,
        start: "top bottom",
        end: "top top",
        scrub: true,
      },
    });
  });
}
