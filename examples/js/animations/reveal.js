// Generic "fade/slide up on scroll into view" for any element with [data-reveal].
// Used across all pages — page-specific files handle the bigger storytelling scenes.

document.querySelectorAll("[data-reveal]").forEach((el) => {
  ScrollTrigger.create({
    trigger: el,
    start: "top 85%",
    onEnter: () => el.classList.add("is-visible"),
    onLeaveBack: () => el.classList.remove("is-visible"),
  });
});
