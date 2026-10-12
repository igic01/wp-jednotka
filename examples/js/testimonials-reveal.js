const testimonialCards = document.querySelectorAll(".testimonial-card");
const statsSection = document.querySelector(".stats-section");
const testimonialReduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
const testimonialStackedLayout = window.matchMedia("(max-width: 900px)");
const testimonialCompactLayout = window.matchMedia("(max-width: 520px)");

const clampTestimonialProgress = (value) => Math.min(Math.max(value, 0), 1);

const getTestimonialTuning = () => {
  if (testimonialCompactLayout.matches) {
    return {
      cardBlur: 7,
      cardTranslateY: 34,
      statsBlur: 5,
      statsFade: 0.36,
      statsShift: -10,
      statsScale: 0.018,
      introShift: 14,
      revealEnd: 0.5,
      cardOpacityBase: 0.42,
      cardOpacityRange: 0.58,
    };
  }

  if (testimonialStackedLayout.matches) {
    return {
      cardBlur: 10,
      cardTranslateY: 44,
      statsBlur: 7,
      statsFade: 0.42,
      statsShift: -14,
      statsScale: 0.022,
      introShift: 18,
      revealEnd: 0.46,
      cardOpacityBase: 0.32,
      cardOpacityRange: 0.68,
    };
  }

  return {
    cardBlur: 24,
    cardTranslateY: 90,
    statsBlur: 18,
    statsFade: 0.72,
    statsShift: -22,
    statsScale: 0.035,
    introShift: 28,
    revealEnd: 0.34,
    cardOpacityBase: 0.12,
    cardOpacityRange: 0.88,
  };
};

const setTestimonialProgress = (card, progress) => {
  const tuning = getTestimonialTuning();
  const eased = 1 - Math.pow(1 - progress, 3);
  const blur = (1 - eased) * tuning.cardBlur;
  const translateY = (1 - eased) * tuning.cardTranslateY;
  const scale = 0.92 + eased * 0.08;
  const opacity = tuning.cardOpacityBase + eased * tuning.cardOpacityRange;
  const style = window.getComputedStyle(card);
  const x = style.getPropertyValue("--testimonial-x").trim() || "0px";
  const y = style.getPropertyValue("--testimonial-y").trim() || "0px";
  const rotate = style.getPropertyValue("--testimonial-rotate").trim() || "0deg";

  card.style.opacity = opacity.toFixed(3);
  card.style.filter = `blur(${blur.toFixed(2)}px)`;
  card.style.transform = testimonialStackedLayout.matches
    ? "none"
    : `translate3d(${x}, calc(${y} + ${translateY.toFixed(2)}px), 0) rotate(${rotate}) scale(${scale.toFixed(3)})`;
};

const resetTestimonials = () => {
  testimonialCards.forEach((card) => {
    card.style.opacity = "";
    card.style.filter = "";
    card.style.transform = "";
  });

  if (statsSection) {
    statsSection.style.setProperty("--stats-grid-opacity", "1");
    statsSection.style.setProperty("--stats-grid-blur", "0px");
    statsSection.style.setProperty("--stats-grid-shift", "0px");
    statsSection.style.setProperty("--stats-grid-scale", "1");
    statsSection.style.setProperty("--testimonials-intro-opacity", "1");
    statsSection.style.setProperty("--testimonials-intro-shift", "0px");
  }
};

const updateTestimonialsOnScroll = () => {
  if (testimonialReduceMotion.matches) {
    resetTestimonials();
    return;
  }

  const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
  const tuning = getTestimonialTuning();
  const start = viewportHeight * 0.96;
  const end = viewportHeight * tuning.revealEnd;

  if (statsSection) {
    const rect = statsSection.getBoundingClientRect();
    const focusStart = viewportHeight * 0.58;
    const focusEnd = viewportHeight * -0.05;
    const progress = clampTestimonialProgress((focusStart - rect.top) / (focusStart - focusEnd));

    statsSection.style.setProperty("--stats-grid-opacity", (1 - progress * tuning.statsFade).toFixed(3));
    statsSection.style.setProperty("--stats-grid-blur", `${(progress * tuning.statsBlur).toFixed(2)}px`);
    statsSection.style.setProperty("--stats-grid-shift", `${(progress * tuning.statsShift).toFixed(2)}px`);
    statsSection.style.setProperty("--stats-grid-scale", (1 - progress * tuning.statsScale).toFixed(3));
    statsSection.style.setProperty("--testimonials-intro-opacity", (0.58 + progress * 0.42).toFixed(3));
    statsSection.style.setProperty("--testimonials-intro-shift", `${((1 - progress) * tuning.introShift).toFixed(2)}px`);
  }

  testimonialCards.forEach((card, index) => {
    const rect = card.getBoundingClientRect();
    const stagger = index * 0.055;
    const progress = clampTestimonialProgress((start - rect.top) / (start - end) - stagger);

    setTestimonialProgress(card, progress);
  });
};

let testimonialTicking = false;

const requestTestimonialsUpdate = () => {
  if (testimonialTicking) {
    return;
  }

  testimonialTicking = true;
  window.requestAnimationFrame(() => {
    updateTestimonialsOnScroll();
    testimonialTicking = false;
  });
};

if (testimonialCards.length) {
  updateTestimonialsOnScroll();
  window.addEventListener("scroll", requestTestimonialsUpdate, { passive: true });
  window.addEventListener("resize", requestTestimonialsUpdate);
  testimonialReduceMotion.addEventListener("change", updateTestimonialsOnScroll);
  testimonialStackedLayout.addEventListener("change", updateTestimonialsOnScroll);
  testimonialCompactLayout.addEventListener("change", updateTestimonialsOnScroll);
}
