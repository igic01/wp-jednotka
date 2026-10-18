/* global elementorFrontend, elementorModules, jQuery */
(() => {
  'use strict';
  let registered = false;
  function register() {
    if (registered || typeof window.elementorFrontend?.hooks?.addAction !== 'function' ||
        typeof window.elementorFrontend?.elementsHandler?.addHandler !== 'function' ||
        !window.elementorModules?.frontend?.handlers?.Base) return;
    registered = true;
    class ProjectsHandler extends elementorModules.frontend.handlers.Base {
      onInit() {
        super.onInit();
        const section = this.$element[0].querySelector('.jn-projects');
        if (!section) return;
        this.controller = new AbortController();
        const { signal } = this.controller;
        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
        const editing = elementorFrontend.isEditMode();
        const update = () => {
          const staticView = editing || reduced.matches || section.dataset.auto !== 'yes';
          section.dataset.static = staticView ? 'yes' : 'no';
          section.dataset.running = !staticView ? 'yes' : 'no';
        };
        reduced.addEventListener('change', update, { signal });
        this.cleanup = () => { section.dataset.static = 'yes'; section.dataset.running = 'no'; };
        update();
      }
      onDestroy() {
        this.controller?.abort(); this.cleanup?.(); super.onDestroy();
      }
    }
    elementorFrontend.hooks.addAction('frontend/element_ready/jednotka-projects.default', $element => {
      elementorFrontend.elementsHandler.addHandler(ProjectsHandler, { $element });
    });
  }
  jQuery(window).on('elementor/frontend/init', register);
  register();
})();
