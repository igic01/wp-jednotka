const counters = document.querySelectorAll("[data-count-to]");

const formatCounter = (value, suffix = "") => `${value}${suffix}`;

const animateCounter = (counter) => {
  const target = Number(counter.dataset.countTo);
  const suffix = counter.dataset.countSuffix || "";
  const duration = 1200;
  const startTime = performance.now();

  counter.textContent = formatCounter(1, suffix);

  const tick = (now) => {
    const progress = Math.min((now - startTime) / duration, 1);
    const eased = 1 - Math.pow(1 - progress, 3);
    const current = Math.round(1 + (target - 1) * eased);

    counter.textContent = formatCounter(current, suffix);

    if (progress < 1) {
      requestAnimationFrame(tick);
    }
  };

  requestAnimationFrame(tick);
};

if (counters.length) {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) {
          return;
        }

        animateCounter(entry.target);
        observer.unobserve(entry.target);
      });
    },
    { threshold: 0.5 },
  );

  counters.forEach((counter) => observer.observe(counter));
}
