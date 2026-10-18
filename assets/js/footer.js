/* global elementorFrontend, elementorModules, jQuery */
(() => {
  'use strict';
  let registered = false;
  function register() {
    if (registered || !window.elementorFrontend?.hooks || !window.elementorFrontend?.elementsHandler || !window.elementorModules?.frontend?.handlers?.Base) return;
    registered = true;
    class FooterHandler extends elementorModules.frontend.handlers.Base {
      onInit() {
        super.onInit();
        const root = this.$element[0].querySelector('.jn-footer-only');
        if (!root) return;
        this.controller = new AbortController();
        const { signal } = this.controller;
        const editing = elementorFrontend.isEditMode();
        const form = root.querySelector('.footer-newsletter');
        const notice = event => {
          event.preventDefault();
          if (!editing) form.querySelector('.jn-newsletter-notice').hidden = false;
        };
        if (form) {
          form.addEventListener('submit', notice, { signal });
          form.querySelector('button')?.addEventListener('click', notice, { signal });
          form.addEventListener('keydown', event => { if (event.key === 'Enter') notice(event); }, { signal });
        }
        if (editing) root.addEventListener('click', event => { if (event.target.closest('a')) event.preventDefault(); }, { capture: true, signal });
        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
        this.videos = [...root.querySelectorAll('video')];
        const sync = () => this.videos.forEach(video => {
          if (editing || reduced.matches || document.hidden) video.pause();
          else video.play()?.catch(() => {});
        });
        document.addEventListener('visibilitychange', sync, { signal });
        reduced.addEventListener('change', sync, { signal });
        sync();
      }
      onDestroy() {
        this.controller?.abort();
        this.videos?.forEach(video => video.pause());
        super.onDestroy();
      }
    }
    elementorFrontend.hooks.addAction('frontend/element_ready/jednotka-footer.default', $element => {
      elementorFrontend.elementsHandler.addHandler(FooterHandler, { $element });
    });
  }
  jQuery(window).on('elementor/frontend/init', register);
  register();
})();
