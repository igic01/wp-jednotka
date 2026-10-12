const projectCards = document.querySelectorAll(".project-card");
const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

const clampProgress = (value) => Math.min(Math.max(value, 0), 1);

const setCardProgress = (card, progress) => {
  const eased = 1 - Math.pow(1 - progress, 3);
  const blur = (1 - eased) * 30;
  const translateY = (1 - eased) * 92;
  const scale = 0.88 + eased * 0.12;
  const opacity = 0.1 + eased * 0.9;

  card.style.opacity = opacity.toFixed(3);
  card.style.filter = `blur(${blur.toFixed(2)}px)`;
  card.style.transform = `translateY(${translateY.toFixed(2)}px) scale(${scale.toFixed(3)})`;
};

const resetCards = () => {
  projectCards.forEach((card) => {
    card.style.opacity = "";
    card.style.filter = "";
    card.style.transform = "";
  });
};

const updateProjectsOnScroll = () => {
  if (reduceMotion.matches) {
    resetCards();
    return;
  }

  const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
  const start = viewportHeight * 0.94;
  const end = viewportHeight * 0.42;

  projectCards.forEach((card, index) => {
    const rect = card.getBoundingClientRect();
    const stagger = (index % 2) * 0.08;
    const progress = clampProgress((start - rect.top) / (start - end) - stagger);

    setCardProgress(card, progress);
  });
};

let ticking = false;

const requestProjectUpdate = () => {
  if (ticking) {
    return;
  }

  ticking = true;
  window.requestAnimationFrame(() => {
    updateProjectsOnScroll();
    ticking = false;
  });
};

if (projectCards.length) {
  updateProjectsOnScroll();
  window.addEventListener("scroll", requestProjectUpdate, { passive: true });
  window.addEventListener("resize", requestProjectUpdate);
  reduceMotion.addEventListener("change", updateProjectsOnScroll);
}
