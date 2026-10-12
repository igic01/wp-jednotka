const serviceRows = document.querySelectorAll(".service-row");
const servicesReduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

const clampServiceProgress = (value) => Math.min(Math.max(value, 0), 1);

const setServiceProgress = (row, progress) => {
  const eased = 1 - Math.pow(1 - progress, 3);
  const blur = (1 - eased) * 18;
  const translateY = (1 - eased) * 58;
  const scale = 0.96 + eased * 0.04;
  const opacity = 0.22 + eased * 0.78;
  const arrow = row.querySelector(".service-arrow");

  row.style.opacity = opacity.toFixed(3);
  row.style.filter = `blur(${blur.toFixed(2)}px)`;
  row.style.transform = `translateY(${translateY.toFixed(2)}px) scale(${scale.toFixed(3)})`;

  if (arrow) {
    const arrowOffset = (1 - eased) * 18;
    arrow.style.setProperty("--arrow-scroll-x", `${arrowOffset.toFixed(2)}px`);
    arrow.style.setProperty("--arrow-scroll-y", `${arrowOffset.toFixed(2)}px`);
  }
};

const resetServiceRows = () => {
  serviceRows.forEach((row) => {
    row.style.opacity = "";
    row.style.filter = "";
    row.style.transform = "";

    const arrow = row.querySelector(".service-arrow");

    if (arrow) {
      arrow.style.removeProperty("--arrow-scroll-x");
      arrow.style.removeProperty("--arrow-scroll-y");
    }
  });
};

const updateServicesOnScroll = () => {
  if (servicesReduceMotion.matches) {
    resetServiceRows();
    return;
  }

  const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
  const start = viewportHeight * 0.92;
  const end = viewportHeight * 0.48;

  serviceRows.forEach((row, index) => {
    const rect = row.getBoundingClientRect();
    const stagger = (index % 2) * 0.04;
    const progress = clampServiceProgress((start - rect.top) / (start - end) - stagger);

    setServiceProgress(row, progress);
  });
};

let servicesTicking = false;

const requestServicesUpdate = () => {
  if (servicesTicking) {
    return;
  }

  servicesTicking = true;
  window.requestAnimationFrame(() => {
    updateServicesOnScroll();
    servicesTicking = false;
  });
};

if (serviceRows.length) {
  updateServicesOnScroll();
  window.addEventListener("scroll", requestServicesUpdate, { passive: true });
  window.addEventListener("resize", requestServicesUpdate);
  servicesReduceMotion.addEventListener("change", updateServicesOnScroll);
}
