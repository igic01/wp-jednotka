/* global elementorFrontend, elementorModules, jQuery */
(() => {
  'use strict';
  let registered = false;
  function register() {
    if (registered || typeof window.elementorFrontend?.hooks?.addAction !== 'function' ||
        typeof window.elementorFrontend?.elementsHandler?.addHandler !== 'function' ||
        !window.elementorModules?.frontend?.handlers?.Base) return;
    registered = true;
    class OurStoryHandler extends elementorModules.frontend.handlers.Base {
      onInit() {
        super.onInit();
        const section = this.$element[0].querySelector('.jn-our-story');
        const text = section?.querySelector('.jn-our-story__text');
        if (!text) return;
        this.controller = new AbortController(); this.frame = null;
        const { signal } = this.controller;
        const editing = elementorFrontend.isEditMode();
        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
        const originalText = text.textContent;
        const originalLabel = text.getAttribute('aria-label');
        const images = [...section.querySelectorAll('.jn-our-story__image')].map(element => {
          const start = Math.max(0, Math.min(.99, Number(element.dataset.start) || 0));
          return { element, start, end: Math.max(start + .01, Math.min(1, Number(element.dataset.end) || 1)) };
        });
        const channels = (hex, fallback) => {
          let value = /^#[\da-f]{3}([\da-f]{3})?$/i.test(hex || '') ? hex.slice(1) : fallback;
          if (value.length === 3) value = [...value].map(c => c + c).join('');
          return [0, 2, 4].map(i => parseInt(value.slice(i, i + 2), 16));
        };
        const white = channels(section.dataset.final, 'ffffff');
        const muted = channels(section.dataset.muted, '585858');
        const purple = channels(section.dataset.highlight, '8a5cff');
        const clamp = value => Math.max(0, Math.min(1, value));
        let chars = [], visible = true;
        const staticView = () => editing || reduced.matches || section.dataset.animate !== 'yes';
        const cancelFrame = () => {
          if (this.frame !== null) window.cancelAnimationFrame(this.frame);
          this.frame = null;
        };
        const restoreText = () => {
          if (!chars.length) return;
          text.textContent = originalText;
          if (originalLabel === null) text.removeAttribute('aria-label');
          else text.setAttribute('aria-label', originalLabel);
          chars = [];
        };
        const update = () => {
          if (staticView()) return;
          const rect = section.getBoundingClientRect();
          const progress = clamp(-rect.top / Math.max(1, rect.height - window.innerHeight));
          const direction = progress * (chars.length + 32) - 16;
          chars.forEach((span, index) => {
            const relative = index - direction;
            const strength = clamp(1 - Math.abs(relative) / 26);
            const lead = clamp((relative + 26 * .55) / (26 * 1.1));
            const color = strength > 0 ? white.map((channel, i) => Math.round(channel + (purple[i] - channel) * lead)) : index < direction - 26 * .35 ? white : muted;
            span.style.setProperty('--wave-color', `rgb(${color[0]} ${color[1]} ${color[2]})`);
          });
          images.forEach(({ element, start, end }) => {
            const local = clamp((progress - start) / (end - start));
            const visibility = clamp(Math.min(local / .25, (1 - local) / .25));
            element.style.setProperty('--visibility', visibility.toFixed(3));
          });
        };
        const requestUpdate = () => {
          if (staticView() || !visible || document.hidden || this.frame !== null) return;
          this.frame = window.requestAnimationFrame(() => { this.frame = null; update(); });
        };
        const configure = () => {
          cancelFrame();
          section.setAttribute('data-static', staticView() ? 'yes' : 'no');
          if (staticView()) {
            restoreText(); images.forEach(({ element }) => element.style.setProperty('--visibility', '1'));
            return;
          }
          if (!chars.length) {
            const label = originalText.replace(/\s+/g, ' ').trim();
            text.setAttribute('aria-label', label); text.textContent = '';
            chars = [...label].map(char => {
              const span = document.createElement('span');
              span.className = 'wave-char'; span.textContent = char; span.setAttribute('aria-hidden', 'true');
              text.appendChild(span); return span;
            });
          }
          update();
        };
        window.addEventListener('scroll', requestUpdate, { passive: true, signal });
        window.addEventListener('resize', requestUpdate, { signal });
        reduced.addEventListener('change', configure, { signal });
        document.addEventListener('visibilitychange', () => { if (document.hidden) cancelFrame(); else requestUpdate(); }, { signal });
        if (window.IntersectionObserver) {
          this.observer = new window.IntersectionObserver(entries => {
            visible = entries.some(entry => entry.isIntersecting);
            if (visible) requestUpdate(); else cancelFrame();
          });
          this.observer.observe(section);
        }
        this.cleanup = () => {
          cancelFrame(); restoreText(); section.setAttribute('data-static', 'yes');
          images.forEach(({ element }) => element.style.setProperty('--visibility', '1'));
        };
        configure();
      }
      onDestroy() {
        this.controller?.abort(); this.observer?.disconnect(); this.cleanup?.();
        super.onDestroy();
      }
    }
    elementorFrontend.hooks.addAction('frontend/element_ready/jednotka-our-story.default', $element => {
      elementorFrontend.elementsHandler.addHandler(OurStoryHandler, { $element });
    });
  }
  jQuery(window).on('elementor/frontend/init', register);
  register();
})();
