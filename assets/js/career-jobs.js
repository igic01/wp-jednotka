/* global elementorFrontend, elementorModules, jQuery */
(() => {
  'use strict';
  let registered = false;
  function register() {
    if (registered || !window.elementorFrontend?.hooks || !window.elementorFrontend?.elementsHandler || !window.elementorModules?.frontend?.handlers?.Base) return;
    registered = true;
    class CareerJobsHandler extends elementorModules.frontend.handlers.Base {
      onInit() {
        super.onInit();
        const root = this.$element[0].querySelector('.jn-career-jobs');
        if (!root) return;
        this.controller = new AbortController();
        const { signal } = this.controller;
        if (elementorFrontend.isEditMode()) {
          root.addEventListener('click', event => { if (event.target.closest('a')) event.preventDefault(); }, { signal });
          return;
        }
        const input = root.querySelector('input[type="search"]');
        const jobs = [...root.querySelectorAll('.career-job')];
        const empty = root.querySelector('.career-jobs-empty');
        const normalize = text => text.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        const searchable = jobs.map(job => ({ job, text: normalize(job.querySelector('.career-job-copy').textContent) }));
        const apply = () => {
          const query = normalize(input.value.trim());
          let shown = 0;
          searchable.forEach(({job, text}) => { job.hidden = !text.includes(query); if (!job.hidden) shown++; });
          empty.hidden = shown > 0;
          root.querySelector('[data-jobs-empty]').hidden = jobs.length > 0;
          root.querySelector('[data-jobs-no-results]').hidden = jobs.length === 0;
        };
        input.addEventListener('input', apply, { signal });
        apply();
      }
      onDestroy() { this.controller?.abort(); super.onDestroy(); }
    }
    elementorFrontend.hooks.addAction('frontend/element_ready/jednotka-career-jobs.default', $element => { elementorFrontend.elementsHandler.addHandler(CareerJobsHandler, { $element }); });
  }
  jQuery(window).on('elementor/frontend/init', register);
  register();
})();
