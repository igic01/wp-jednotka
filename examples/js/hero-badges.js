document.querySelectorAll(".hero-frame, [data-hero-badges]").forEach((heroFrame) => {
  const badges = [...heroFrame.querySelectorAll(".hero-badge")];
  const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
  let floatFrame = null;

  const updateHeroBadges = (event) => {
    const rect = heroFrame.getBoundingClientRect();
    const x = ((event.clientX - rect.left) / rect.width - 0.5) * 22;
    const y = ((event.clientY - rect.top) / rect.height - 0.5) * 18;

    heroFrame.style.setProperty("--hero-badge-x", `${x.toFixed(2)}px`);
    heroFrame.style.setProperty("--hero-badge-y", `${y.toFixed(2)}px`);
  };

  const resetHeroBadges = () => {
    heroFrame.style.setProperty("--hero-badge-x", "0px");
    heroFrame.style.setProperty("--hero-badge-y", "0px");
  };

  const resetHeroBadgeFloat = () => {
    badges.forEach((badge) => {
      badge.style.setProperty("--hero-badge-float-x", "0px");
      badge.style.setProperty("--hero-badge-float-y", "0px");
    });
  };

  const animateHeroBadgeFloat = (time) => {
    if (reduceMotion.matches) {
      resetHeroBadgeFloat();
      floatFrame = null;
      return;
    }

    badges.forEach((badge, index) => {
      const phase = index * 1.74;
      const driftX = Math.sin(time * 0.00065 + phase) * 2.4;
      const driftY = Math.cos(time * 0.00078 + phase) * 3.4;

      badge.style.setProperty("--hero-badge-float-x", `${driftX.toFixed(2)}px`);
      badge.style.setProperty("--hero-badge-float-y", `${driftY.toFixed(2)}px`);
    });

    floatFrame = window.requestAnimationFrame(animateHeroBadgeFloat);
  };

  const syncHeroBadgeFloat = () => {
    if (floatFrame) {
      window.cancelAnimationFrame(floatFrame);
      floatFrame = null;
    }

    if (reduceMotion.matches) {
      resetHeroBadgeFloat();
      return;
    }

    floatFrame = window.requestAnimationFrame(animateHeroBadgeFloat);
  };

  heroFrame.addEventListener("pointermove", updateHeroBadges);
  heroFrame.addEventListener("pointerleave", resetHeroBadges);
  reduceMotion.addEventListener("change", syncHeroBadgeFloat);
  syncHeroBadgeFloat();
});
