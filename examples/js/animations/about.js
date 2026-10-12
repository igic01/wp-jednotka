// About page only — scroll-triggered blur reveal for [data-blur-reveal] items.
// Items inside a shared [data-blur-reveal-group] stagger relative to each other.

// Stagger cycles through a short wave instead of growing linearly with the
// group size — with large grids (e.g. the masonry project wall), a plain
// index * delay leaves late items sitting invisible for seconds.
const STAGGER_STEP_MS = 70;
const STAGGER_WAVE_SIZE = 6;

document.querySelectorAll("[data-blur-reveal-group]").forEach((group) => {
  const items = [...group.querySelectorAll("[data-blur-reveal]")];
  items.forEach((item, index) => {
    item.style.setProperty("--reveal-delay", `${(index % STAGGER_WAVE_SIZE) * STAGGER_STEP_MS}ms`);
  });
});

const revealItems = document.querySelectorAll("[data-blur-reveal]");

if (revealItems.length) {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) {
          return;
        }

        entry.target.classList.add("is-visible");
        observer.unobserve(entry.target);
      });
    },
    {
      rootMargin: "0px 0px -12% 0px",
      threshold: 0.2,
    }
  );

  revealItems.forEach((item) => observer.observe(item));
}
