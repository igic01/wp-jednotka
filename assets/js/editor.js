/* global jQuery */
(() => {
  'use strict';
  let registered = false;

  function register() {
    const api = window.$e;
    const Before = api?.modules?.hookUI?.Before;
    if (registered || !Before || !api?.hooks?.registerUIBefore) return;

    class PlainServiceBenefits extends Before {
      getCommand() { return 'document/elements/settings'; }
      getId() { return 'jednotka-services-plain-benefits'; }
      getConditions(args) {
        if (!args.options?.external || typeof args.settings?.detail_bullets !== 'string') return false;
        let container = args.container;
        for (let i = 0; container && i < 8; i++, container = container.parent) {
          if (container.model?.get?.('widgetType') === 'jednotka-services') return true;
        }
        return false;
      }
      apply(args) {
        // Elementor's inline editor returns HTML; the benefits control stores one plain line per item.
        const plain = window.document.createElement('div');
        plain.innerHTML = args.settings.detail_bullets.replace(/<br\s*\/?>|<\/(?:p|div|li)>/gi, '\n');
        args.settings.detail_bullets = plain.textContent.replace(/\u00a0/g, ' ').trim();
        return true;
      }
    }

    new PlainServiceBenefits().register();
    registered = true;
  }

  jQuery(window).on('elementor/init', register);
  register();
})();
