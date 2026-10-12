// Home page only — the pinned, scroll-scrubbed "story" section.
// Each .story__frame fades in, holds, then fades out as the user scrolls
// through the pinned section, Apple-style.

const frames = gsap.utils.toArray(".story__frame");

if (frames.length) {
  gsap.set(frames, { autoAlpha: 0 });
  gsap.set(frames[0], { autoAlpha: 1 });

  const tl = gsap.timeline({
    scrollTrigger: {
      trigger: ".story",
      start: "top top",
      end: () => "+=" + frames.length * 800,
      scrub: 1,
      pin: true,
    },
  });

  frames.forEach((frame, i) => {
    if (i === 0) return;
    tl.to(frames[i - 1], { autoAlpha: 0, duration: 0.5 })
      .to(frame, { autoAlpha: 1, duration: 0.5 }, "<");
  });
}
