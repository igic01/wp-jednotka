const waveTexts = document.querySelectorAll("[data-gradient-wave-text]");

const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

const mixChannel = (from, to, amount) => Math.round(from + (to - from) * amount);

const whiteColor = [255, 255, 255];
const mutedColor = [88, 88, 88];
const purpleColor = [138, 92, 255];

const colorString = (color) => `rgb(${color[0]} ${color[1]} ${color[2]})`;

waveTexts.forEach((text) => {
  const section = text.closest("[data-wave-scroll-root], .approach-section") || text;
  const label = text.textContent.replace(/\s+/g, " ").trim();
  const chars = [];

  text.setAttribute("aria-label", label);
  text.textContent = "";

  [...label].forEach((char, index) => {
    const span = document.createElement("span");
    span.className = "wave-char";
    span.textContent = char;
    span.dataset.index = index;
    span.setAttribute("aria-hidden", "true");
    text.appendChild(span);
    chars.push(span);
  });

  const updateWave = () => {
    const rect = section.getBoundingClientRect();
    const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
    const scrollable = Math.max(1, rect.height - viewportHeight);
    const progress = clamp(-rect.top / scrollable, 0, 1);

    const direction = progress * (chars.length + 32) - 16;
    const band = 26;

    chars.forEach((span) => {
      const index = Number(span.dataset.index);
      const relativePosition = index - direction;
      const distance = Math.abs(relativePosition);
      const strength = clamp(1 - distance / band, 0, 1);
      const leadAmount = clamp((relativePosition + band * 0.55) / (band * 1.1), 0, 1);
      const waveColor = whiteColor.map((channel, channelIndex) => mixChannel(channel, purpleColor[channelIndex], leadAmount));
      const hasPassed = index < direction - band * 0.35;
      const restColor = hasPassed ? whiteColor : mutedColor;
      const color = strength > 0 ? waveColor : restColor;

      span.style.setProperty("--wave-color", colorString(color));
    });
  };

  updateWave();
  window.addEventListener("scroll", updateWave, { passive: true });
  window.addEventListener("resize", updateWave);
});
