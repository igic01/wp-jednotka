const clientsCta = document.querySelector(".clients-cta-section");

if (clientsCta) {
  const trailLayer = clientsCta.querySelector(".cursor-trail-layer");
  const floatingCards = [...clientsCta.querySelectorAll(".floating-project-card")];
  const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
  let lastTrail = 0;
  let floatFrame = null;

  const updateFloatingCards = (event) => {
    const rect = clientsCta.getBoundingClientRect();
    const x = ((event.clientX - rect.left) / rect.width - 0.5) * 28;
    const y = ((event.clientY - rect.top) / rect.height - 0.5) * 28;

    clientsCta.style.setProperty("--float-x", `${x.toFixed(2)}px`);
    clientsCta.style.setProperty("--float-y", `${y.toFixed(2)}px`);

    if (!trailLayer || event.timeStamp - lastTrail < 42) {
      return;
    }

    lastTrail = event.timeStamp;

    const dot = document.createElement("span");
    const size = 10 + Math.random() * 18;

    dot.className = "cursor-trail-dot";
    dot.style.left = `${event.clientX - rect.left}px`;
    dot.style.top = `${event.clientY - rect.top}px`;
    dot.style.setProperty("--trail-size", `${size.toFixed(1)}px`);
    trailLayer.appendChild(dot);

    window.setTimeout(() => dot.remove(), 950);
  };

  const resetFloatingCards = () => {
    clientsCta.style.setProperty("--float-x", "0px");
    clientsCta.style.setProperty("--float-y", "0px");
  };

  const resetFloatingCardDrift = () => {
    floatingCards.forEach((card) => {
      card.style.setProperty("--float-card-drift-x", "0px");
      card.style.setProperty("--float-card-drift-y", "0px");
    });
  };

  const animateFloatingCardDrift = (time) => {
    if (reduceMotion.matches) {
      resetFloatingCardDrift();
      floatFrame = null;
      return;
    }

    floatingCards.forEach((card, index) => {
      const phase = index * 1.31;
      const driftX = Math.sin(time * 0.00048 + phase) * 4.5;
      const driftY = Math.cos(time * 0.00058 + phase) * 6;

      card.style.setProperty("--float-card-drift-x", `${driftX.toFixed(2)}px`);
      card.style.setProperty("--float-card-drift-y", `${driftY.toFixed(2)}px`);
    });

    floatFrame = window.requestAnimationFrame(animateFloatingCardDrift);
  };

  const syncFloatingCardDrift = () => {
    if (floatFrame) {
      window.cancelAnimationFrame(floatFrame);
      floatFrame = null;
    }

    if (reduceMotion.matches) {
      resetFloatingCardDrift();
      return;
    }

    floatFrame = window.requestAnimationFrame(animateFloatingCardDrift);
  };

  clientsCta.addEventListener("pointermove", updateFloatingCards);
  clientsCta.addEventListener("pointerleave", resetFloatingCards);
  reduceMotion.addEventListener("change", syncFloatingCardDrift);
  syncFloatingCardDrift();
}
