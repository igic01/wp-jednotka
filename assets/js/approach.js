/* global elementorFrontend, elementorModules, jQuery */
(() => {
  'use strict';
  let registered = false;
  function register() {
    if (registered) return;
    if (typeof window.elementorFrontend?.hooks?.addAction !== 'function' ||
        typeof window.elementorFrontend?.elementsHandler?.addHandler !== 'function' ||
        !window.elementorModules?.frontend?.handlers?.Base) return;
    registered = true;
    class ApproachHandler extends elementorModules.frontend.handlers.Base {
      onInit() {
        super.onInit();
        const section = this.$element[0].querySelector('.jn-approach');
        if (!section) return;
        this.controller = new AbortController();
        this.frame = null;
        const { signal } = this.controller;
        const text = section.querySelector('[data-gradient-wave-text]');
        const editing = elementorFrontend.isEditMode();
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        const phone = window.matchMedia('(max-width: 767px)');
        const originalText = text.textContent;
        const originalLabel = text.getAttribute('aria-label');
        let chars = [];
        // The reference colors can be changed through Elementor's color controls.
        const channels = hex => {
          let value = hex.slice(1);
          if (value.length === 3) value = [...value].map(char => char + char).join('');
          return [0, 2, 4].map(offset => parseInt(value.slice(offset, offset + 2), 16));
        };
        const whiteColor = channels(section.dataset.final);
        const mutedColor = channels(section.dataset.muted);
        const purpleColor = channels(section.dataset.highlight);
        const clamp = (value, min, max) => Math.min(Math.max(value, min), max);
        const mixChannel = (from, to, amount) => Math.round(from + (to - from) * amount);
        const colorString = color => `rgb(${color[0]} ${color[1]} ${color[2]})`;

        // Original examples/js/gradient-wave-text.js calculations, scoped to this widget.
        const updateWave = () => {
          if (!chars.length) return;
          const rect = section.getBoundingClientRect();
          const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
          const scrollable = Math.max(1, rect.height - viewportHeight);
          const progress = clamp(-rect.top / scrollable, 0, 1);
          const direction = progress * (chars.length + 32) - 16;
          const band = 26;
          chars.forEach(span => {
            const index = Number(span.dataset.index);
            const relativePosition = index - direction;
            const distance = Math.abs(relativePosition);
            const strength = clamp(1 - distance / band, 0, 1);
            const leadAmount = clamp((relativePosition + band * 0.55) / (band * 1.1), 0, 1);
            const waveColor = whiteColor.map((channel, channelIndex) => mixChannel(channel, purpleColor[channelIndex], leadAmount));
            const hasPassed = index < direction - band * 0.35;
            const restColor = hasPassed ? whiteColor : mutedColor;
            const color = strength > 0 ? waveColor : restColor;
            span.style.setProperty('--wave-color', colorString(color));
          });
        };
        const restoreText = () => {
          if (!chars.length) return;
          text.textContent = originalText;
          if (originalLabel === null) text.removeAttribute('aria-label');
          else text.setAttribute('aria-label', originalLabel);
          chars = [];
        };
        this.restoreText = restoreText;
        const configureWave = () => {
          if (this.frame !== null) { window.cancelAnimationFrame(this.frame); this.frame = null; }
          if (editing || reduceMotion.matches || phone.matches || section.dataset.animate !== 'yes') {
            restoreText();
            return;
          }
          if (!chars.length) {
            const label = originalText.replace(/\s+/g, ' ').trim();
            text.setAttribute('aria-label', label);
            text.textContent = '';
            [...label].forEach((char, index) => {
              const span = document.createElement('span');
              span.className = 'wave-char';
              span.textContent = char;
              span.dataset.index = index;
              span.setAttribute('aria-hidden', 'true');
              text.appendChild(span);
              chars.push(span);
            });
          }
          updateWave();
        };
        const requestUpdate = () => {
          if (!chars.length || this.frame !== null) return;
          this.frame = window.requestAnimationFrame(() => { this.frame = null; updateWave(); });
        };
        window.addEventListener('scroll', requestUpdate, { passive: true, signal });
        window.addEventListener('resize', requestUpdate, { signal });
        this.video = section.querySelector('video');
        const button = section.querySelector('.jn-approach__video-toggle');
        const syncVideoButton = () => {
          if (!this.video || !button) return;
          const playing = !this.video.paused;
          button.textContent = playing ? 'Ⅱ' : '▶';
          button.setAttribute('aria-pressed', String(playing));
          button.setAttribute('aria-label', playing ? button.dataset.pauseLabel : button.dataset.playLabel);
        };
        if (this.video && button) {
          this.video.addEventListener('play', syncVideoButton, { signal });
          this.video.addEventListener('pause', syncVideoButton, { signal });
          button.addEventListener('click', () => {
            if (this.video.paused) this.video.play().catch(syncVideoButton);
            else this.video.pause();
          }, { signal });
          if (!editing && !reduceMotion.matches) this.video.play().catch(syncVideoButton);
          syncVideoButton();
        }
        reduceMotion.addEventListener('change', () => {
          if (this.frame !== null) { window.cancelAnimationFrame(this.frame); this.frame = null; }
          if (reduceMotion.matches) this.video?.pause();
          configureWave();
        }, { signal });
        phone.addEventListener('change', configureWave, { signal });
        configureWave();
      }
      onDestroy() {
        this.controller?.abort();
        if (this.frame != null) window.cancelAnimationFrame(this.frame);
        this.video?.pause();
        this.restoreText?.();
        super.onDestroy();
      }
    }
    elementorFrontend.hooks.addAction('frontend/element_ready/jednotka-approach.default', $element => {
      elementorFrontend.elementsHandler.addHandler(ApproachHandler, { $element });
    });
  }
  jQuery(window).on('elementor/frontend/init', register);
  register();
})();
