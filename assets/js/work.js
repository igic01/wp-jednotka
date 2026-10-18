/* global elementorFrontend, elementorModules, jQuery */
(() => {
  'use strict';
  let registered = false;
  function register() {
    if (registered || typeof window.elementorFrontend?.hooks?.addAction !== 'function' ||
        typeof window.elementorFrontend?.elementsHandler?.addHandler !== 'function' ||
        !window.elementorModules?.frontend?.handlers?.Base) return;
    registered = true;
    class WorkHandler extends elementorModules.frontend.handlers.Base {
      onInit() {
        super.onInit();
        const section = this.$element[0].querySelector('.jn-work');
        const timeline = section?.querySelector('.services-timeline');
        if (!timeline) return;
        this.controller = new AbortController(); this.frame = null;
        const { signal } = this.controller;
        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
        const editing = elementorFrontend.isEditMode();
        const cancel = () => {
          if (this.frame !== null) window.cancelAnimationFrame(this.frame);
          this.frame = null;
        };
        const enabled = () => !editing && !reduced.matches && section.dataset.animate === 'yes';
        const update = () => {
          this.frame = null;
          timeline.classList.toggle('is-active', !enabled() || timeline.getBoundingClientRect().top < window.innerHeight * .78);
        };
        const requestUpdate = () => {
          if (enabled() && !document.hidden && this.frame === null) this.frame = window.requestAnimationFrame(update);
        };
        const configure = () => {
          cancel();
          const starting = enabled() && timeline.dataset.animated !== 'yes';
          timeline.dataset.animated = enabled() ? 'yes' : 'no';
          if (starting) timeline.classList.remove('is-active');
          // update measures the hidden state before starting the CSS transition.
          update();
        };
        window.addEventListener('scroll', requestUpdate, { passive: true, signal });
        window.addEventListener('resize', requestUpdate, { signal });
        reduced.addEventListener('change', configure, { signal });
        document.addEventListener('visibilitychange', () => { cancel(); if (!document.hidden) update(); }, { signal });
        this.cleanup = () => { cancel(); timeline.dataset.animated = 'no'; timeline.classList.add('is-active'); };
        configure();
      }
      onDestroy() { this.controller?.abort(); this.cleanup?.(); super.onDestroy(); }
    }
    elementorFrontend.hooks.addAction('frontend/element_ready/jednotka-work.default', $element => {
      elementorFrontend.elementsHandler.addHandler(WorkHandler, { $element });
    });
  }
  jQuery(window).on('elementor/frontend/init', register);
  register();
})();
