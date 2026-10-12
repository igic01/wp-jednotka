// Services page only — page-specific ScrollTrigger scenes.
// [data-reveal] elements are already handled by js/animations/reveal.js.

const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

const timelineSteps = document.querySelectorAll(".services-timeline-step");
const timelineLine = document.querySelector(".services-timeline-line");
const timelineNodes = document.querySelectorAll(".services-timeline-node");
const timelineTexts = document.querySelectorAll(".services-timeline-step h3, .services-timeline-step p");

if (timelineSteps.length && !reduceMotion) {
  gsap.set(timelineNodes, { scale: 0, transformOrigin: "50% 50%" });
  gsap.set(timelineTexts, { opacity: 0, y: 16 });

  const timelineTl = gsap.timeline({ paused: true });

  if (timelineLine) {
    timelineTl.fromTo(timelineLine, { scaleX: 0 }, { scaleX: 1, duration: 1.05, ease: "power1.inOut" }, 0);
  }

  timelineTl
    .to(timelineNodes, { scale: 1, duration: 0.45, ease: "back.out(2.6)", stagger: 0.26 }, 0.02)
    .to(timelineTexts, { opacity: 1, y: 0, duration: 0.5, ease: "power2.out", stagger: 0.26 }, 0.16);

  ScrollTrigger.create({
    trigger: ".services-timeline",
    start: "top 78%",
    onEnter: () => timelineTl.play(),
    onLeaveBack: () => timelineTl.reverse(),
  });
}
