/* global jednotkaContact */
/* Sends the Kontakt page's HTML-block form to the plugin's contact handler. */
(() => {
  'use strict';
  const config = window.jednotkaContact;
  if (!config) return;
  const names = { meno: 'name', telefon: 'phone', oblast: 'interest', sprava: 'message', subor: 'attachment' };

  const payload = form => {
    const data = new FormData();
    for (const [key, value] of new FormData(form)) {
      if (key === 'suhlas' || (key === 'subor' && !value.size)) continue;
      data.append(names[key] || key, value);
    }
    data.append('action', 'jednotka_contact');
    data.append('nonce', config.nonce);
    return data;
  };

  document.querySelectorAll('.jdn-form').forEach(form => {
    const button = form.querySelector('.jdn-button');
    const status = document.createElement('p');
    status.setAttribute('role', 'status');
    status.hidden = true;
    status.style.margin = '16px 0 0';
    form.append(status);
    const show = (message, error = false) => {
      status.textContent = message;
      status.style.color = error ? '#ff6b7d' : '#fff';
      status.hidden = false;
    };
    let busy = false;

    // The block's own script runs first and cancels the event when a field is invalid.
    form.addEventListener('submit', async event => {
      if (event.defaultPrevented) return;
      event.preventDefault();
      if (busy) return;
      busy = true;
      button.disabled = true;
      show('Odosielame správu…');
      try {
        const response = await window.fetch(config.url, { method: 'POST', body: payload(form), credentials: 'same-origin' });
        const result = await response.json();
        if (!response.ok || result.success !== true) {
          show(result.data?.message || 'Správu sa nepodarilo odoslať. Skúste to neskôr alebo nám zavolajte.', true);
          return;
        }
        show(result.data?.message || 'Ďakujeme! Vaša správa bola odoslaná.');
        form.replaceChildren(status);
      } catch {
        show('Správu sa nepodarilo odoslať. Skúste to neskôr alebo nám zavolajte.', true);
      } finally {
        busy = false;
        button.disabled = false;
      }
    });
  });
})();
