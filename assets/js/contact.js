/* global elementorFrontend, elementorModules, jQuery */
(() => {
  'use strict';
  let registered = false;
  function register() {
    if (registered || typeof window.elementorFrontend?.hooks?.addAction !== 'function' ||
        typeof window.elementorFrontend?.elementsHandler?.addHandler !== 'function' ||
        !window.elementorModules?.frontend?.handlers?.Base) return;
    registered = true;
    class ContactHandler extends elementorModules.frontend.handlers.Base {
      onInit() {
        super.onInit();
        const form = this.$element[0].querySelector('.jn-contact form');
        if (!form) return;
        this.controller = new AbortController();
        const { signal } = this.controller;
        const button = form.querySelector('button[type="submit"]');
        const status = form.querySelector('.jn-contact__status');
        let busy = false;
        const show = (message, error = false) => {
          status.textContent = message; status.dataset.error = error ? 'yes' : 'no'; status.hidden = false;
        };
        form.addEventListener('submit', async event => {
          event.preventDefault();
          if (elementorFrontend.isEditMode()) { show('Formulár môžete odoslať na zverejnenej stránke.'); return; }
          if (busy || !form.reportValidity()) return;
          busy = true; button.disabled = true; form.setAttribute('aria-busy', 'true');
          show('Odosielame správu…');
          try {
            const response = await window.fetch(form.action, { method: 'POST', body: new FormData(form), credentials: 'same-origin', signal });
            const result = await response.json();
            if (!response.ok || result.success !== true) {
              show(result.data?.message || 'Správu sa nepodarilo odoslať. Skúste to neskôr.', true); return;
            }
            form.reset();
            show(result.data?.message || 'Ďakujeme! Vaša správa bola odoslaná.');
          } catch (error) {
            if (error.name !== 'AbortError') show('Správu sa nepodarilo odoslať. Skontrolujte pripojenie a skúste znova.', true);
          } finally {
            busy = false; button.disabled = false; form.removeAttribute('aria-busy');
          }
        }, { signal });
      }
      onDestroy() { this.controller?.abort(); super.onDestroy(); }
    }
    elementorFrontend.hooks.addAction('frontend/element_ready/jednotka-contact.default', $element => {
      elementorFrontend.elementsHandler.addHandler(ContactHandler, { $element });
    });
  }
  jQuery(window).on('elementor/frontend/init', register);
  register();
})();
