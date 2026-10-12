// Shared GSAP setup — loaded on every page, before page-specific animation files.
gsap.registerPlugin(ScrollTrigger);

// Sensible defaults so individual animations stay short to write.
ScrollTrigger.defaults({
  toggleActions: "play none none reverse",
});
