document.querySelectorAll("[data-drag-scroll]").forEach((track) => {
  let isDown = false;
  let startX = 0;
  let scrollStart = 0;
  let didDrag = false;
  let suppressClick = false;
  let animationFrame = null;
  let lastFrameTime = 0;
  let autoDirection = 1;
  let pauseAutoUntil = 0;
  let lastKnownScrollLeft = track.scrollLeft;
  // Owns the sub-pixel position ourselves: re-reading track.scrollLeft each
  // frame and adding a sub-1px delta to it silently stalls, because the
  // browser can round the readback to an integer before we ever accumulate.
  let virtualScrollLeft = track.scrollLeft;
  const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
  const autoScrollSpeed = Number(track.dataset.autoScrollSpeed || 8);
  const autoScrollMode = track.dataset.autoScrollMode || "bounce";
  const hasAutoScroll = track.hasAttribute("data-auto-scroll");
  // A single auto-scroll frame step never exceeds ~2px; anything bigger reaching
  // the scroll event is a real user gesture (wheel, trackpad, scrollbar).
  const userScrollTolerance = 6;

  const start = (event) => {
    isDown = true;
    didDrag = false;
    startX = event.clientX;
    scrollStart = track.scrollLeft;
    pauseAutoUntil = Infinity;
    track.classList.add("is-dragging");

    if (event.pointerId !== undefined) {
      track.setPointerCapture(event.pointerId);
    }
  };

  const move = (event) => {
    if (!isDown) return;
    const dragDistance = event.clientX - startX;

    if (Math.abs(dragDistance) > 4) {
      didDrag = true;
    }

    track.scrollLeft = scrollStart - dragDistance;
  };

  const end = (event) => {
    if (!isDown) return;
    isDown = false;
    suppressClick = didDrag;
    pauseAutoUntil = performance.now() + 1400;
    virtualScrollLeft = track.scrollLeft;
    track.classList.remove("is-dragging");

    if (event?.pointerId !== undefined && track.hasPointerCapture(event.pointerId)) {
      track.releasePointerCapture(event.pointerId);
    }
  };

  const animateAutoScroll = (time) => {
    const maxScroll = track.scrollWidth - track.clientWidth;

    if (!lastFrameTime) {
      lastFrameTime = time;
    }

    const deltaSeconds = Math.min((time - lastFrameTime) / 1000, 0.05);
    lastFrameTime = time;

    if (!reduceMotion.matches && maxScroll > 0 && !isDown && time > pauseAutoUntil) {
      virtualScrollLeft += autoDirection * autoScrollSpeed * deltaSeconds;

      if (virtualScrollLeft >= maxScroll) {
        virtualScrollLeft = maxScroll;
        autoDirection = autoScrollMode === "one-way" ? 1 : -1;
      } else if (virtualScrollLeft <= 0) {
        virtualScrollLeft = 0;
        autoDirection = 1;
      }

      track.scrollLeft = virtualScrollLeft;
      lastKnownScrollLeft = track.scrollLeft;
    }

    animationFrame = window.requestAnimationFrame(animateAutoScroll);
  };

  const onScroll = () => {
    const delta = Math.abs(track.scrollLeft - lastKnownScrollLeft);
    lastKnownScrollLeft = track.scrollLeft;

    if (delta > userScrollTolerance) {
      pauseAutoUntil = performance.now() + 1400;
      virtualScrollLeft = track.scrollLeft;
    }
  };

  track.addEventListener("pointerdown", (event) => {
    if (event.button !== undefined && event.button !== 0) return;
    start(event);
  });
  track.addEventListener("pointermove", move);
  track.addEventListener("pointerup", end);
  track.addEventListener("pointercancel", end);
  track.addEventListener("pointerleave", end);
  track.addEventListener(
    "click",
    (event) => {
      if (!suppressClick) return;
      event.preventDefault();
      event.stopPropagation();
      suppressClick = false;
    },
    true
  );
  track.addEventListener("scroll", onScroll, { passive: true });

  if (hasAutoScroll) {
    animationFrame = window.requestAnimationFrame(animateAutoScroll);
  }
});
