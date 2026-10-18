/* global elementorFrontend, elementorModules, jQuery */
(() => {
  'use strict';
  let registered = false;
  function register() {
    if (registered || typeof window.elementorFrontend?.hooks?.addAction !== 'function' ||
        typeof window.elementorFrontend?.elementsHandler?.addHandler !== 'function' ||
        !window.elementorModules?.frontend?.handlers?.Base) return;
    registered = true;
    class FAQHandler extends elementorModules.frontend.handlers.Base {
      onInit() {
        super.onInit();
        const section = this.$element[0].querySelector('.jn-faq');
        const list = section?.querySelector('.services-faq-list');
        if (!list || elementorFrontend.isEditMode()) return;
        const details = [...list.querySelectorAll('details')];
        // Keep the first initially expanded answer; subsequent opens replace it.
        let initialOpen = false;
        details.forEach(detail => { if (detail.open) { if (initialOpen) detail.open = false; else initialOpen = true; } });
        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
        this.controller = new AbortController(); this.frame = null;
        const { signal } = this.controller;
        const motions = new Map();
        let destroyed = false, width = 0;
        const finishAll = () => [...motions.values()].forEach(state => state.animation.finish());
        const measure = () => {
          this.frame = null;
          if (destroyed) return;
          finishAll();
          width = list.getBoundingClientRect().width;
          if (section.dataset.stableHeight !== 'yes') { list.style.minHeight = ''; return; }
          const states = details.map(detail => detail.open);
          // Reserve the closed list plus its tallest single answer.
          details.forEach(detail => { detail.open = false; });
          list.style.minHeight = '';
          let height = list.getBoundingClientRect().height;
          details.forEach(detail => {
            detail.open = true;
            height = Math.max(height, list.getBoundingClientRect().height);
            detail.open = false;
          });
          height = Math.ceil(height);
          details.forEach((detail, index) => { detail.open = states[index]; });
          list.style.minHeight = `${height}px`;
        };
        const requestMeasure = () => {
          if (this.frame === null) this.frame = window.requestAnimationFrame(measure);
        };
        const animateTo = (detail, open) => {
          const previous = motions.get(detail);
          const from = detail.getBoundingClientRect().height;
          if (previous) { previous.animation.onfinish = null; previous.animation.cancel(); motions.delete(detail); }
          detail.classList.remove('is-animating');
          if (reduced.matches || typeof detail.animate !== 'function') { detail.open = open; return; }
          detail.open = open;
          const to = detail.getBoundingClientRect().height;
          // Keep native details content visible during its closing animation.
          detail.open = true;
          detail.classList.add('is-animating');
          const animation = detail.animate([{ height: `${from}px` }, { height: `${to}px` }], { duration: 320, easing: 'cubic-bezier(.22, 1, .36, 1)' });
          const state = { animation, open };
          motions.set(detail, state);
          animation.onfinish = () => {
            if (motions.get(detail) !== state) return;
            detail.open = open; detail.classList.remove('is-animating'); motions.delete(detail);
          };
        };
        details.forEach(detail => {
          const summary = detail.querySelector('summary');
          if (!summary) return;
          summary.addEventListener('click', event => {
            if (event.target.closest('a, button, input, [contenteditable="true"]')) return;
            event.preventDefault();
            const open = !(motions.get(detail)?.open ?? detail.open);
            if (open) details.forEach(other => {
              if (other !== detail && (motions.get(other)?.open ?? other.open)) animateTo(other, false);
            });
            animateTo(detail, open);
          }, { signal });
        });
        window.addEventListener('resize', requestMeasure, { signal });
        reduced.addEventListener('change', finishAll, { signal });
        if (window.ResizeObserver) {
          this.observer = new window.ResizeObserver(() => {
            if (Math.abs(list.getBoundingClientRect().width - width) > .5) requestMeasure();
          });
          this.observer.observe(list);
        }
        this.cleanup = () => {
          destroyed = true;
          if (this.frame !== null) window.cancelAnimationFrame(this.frame);
          this.frame = null;
          motions.forEach((state, detail) => {
            state.animation.onfinish = null; state.animation.cancel(); detail.open = state.open; detail.classList.remove('is-animating');
          });
          motions.clear(); list.style.minHeight = '';
        };
        measure();
        document.fonts?.ready?.then(() => { if (!destroyed) measure(); });
      }
      onDestroy() { this.controller?.abort(); this.observer?.disconnect(); this.cleanup?.(); super.onDestroy(); }
    }
    elementorFrontend.hooks.addAction('frontend/element_ready/jednotka-faq.default', $element => {
      elementorFrontend.elementsHandler.addHandler(FAQHandler, { $element });
    });
  }
  jQuery(window).on('elementor/frontend/init', register);
  register();
})();
