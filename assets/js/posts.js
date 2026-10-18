/* Progressive enhancement: server-side search/filter/pagination; no post archive in JS. */
(function () {
  'use strict';
  const initialized = new WeakSet();
  function init(root) {
    if (initialized.has(root)) return;
    initialized.add(root);
    const form = root.querySelector('form');
    const search = form.querySelector('input[type="search"]');
    const category = form.querySelector('[data-current-category]');
    let controller;
    let timer;
    let revision = 0;
    const editing = window.elementorFrontend?.isEditMode?.();
    const invalidate = () => {
      clearTimeout(timer);
      controller?.abort();
      revision += 1;
      root.removeAttribute('aria-busy');
    };
    async function load(url, pagination) {
      invalidate();
      const request = revision;
      controller = new AbortController();
      root.setAttribute('aria-busy', 'true');
      root.querySelector('.blog-error')?.remove();
      try {
        const response = await fetch(url, { signal: controller.signal, credentials: 'same-origin' });
        if (!response.ok) throw new Error('Posts request failed');
        const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
        const next = doc.getElementById(root.id);
        if (!next?.querySelector('.blog-results')) throw new Error('Missing posts widget');
        if (request !== revision || !root.isConnected) return;
        root.querySelector('.blog-results').replaceWith(next.querySelector('.blog-results'));
        category.value = next.querySelector('[data-current-category]').value;
        form.querySelectorAll('.blog-filter').forEach(button => {
          const selected = button.dataset.category === category.value;
          button.setAttribute('aria-pressed', String(selected));
          button.value = selected ? '0' : button.dataset.category;
        });
        if (!editing) {
          history[pagination ? 'pushState' : 'replaceState']({ jednotkaPosts: true }, '', url);
        }
        if (pagination) {
          root.scrollIntoView({ behavior: 'instant', block: 'start' });
          const results = root.querySelector('.blog-results');
          results.tabIndex = -1;
          results.focus({ preventScroll: true });
        }
      } catch (error) {
        if (error.name !== 'AbortError' && request === revision && root.isConnected) {
          const message = document.createElement('p');
          message.className = 'blog-error';
          message.setAttribute('role', 'alert');
          message.textContent = root.dataset.error;
          form.after(message);
        }
      } finally {
        if (request === revision) root.removeAttribute('aria-busy');
      }
    }
    function formURL(button) {
      const url = new URL(form.action, location.href);
      const data = new FormData(form);
      if (button?.name) data.set(button.name, button.value);
      // Start at page one whenever the search or category changes.
      for (const [key, value] of data) url.searchParams.set(key, value);
      const pageKey = category.name.replace(/category$/, 'page');
      url.searchParams.delete(pageKey);
      return url;
    }
    form.addEventListener('submit', event => {
      event.preventDefault();
      load(formURL(event.submitter?.classList.contains('blog-filter') ? event.submitter : null), false);
    });
    search.addEventListener('input', () => {
      invalidate();
      timer = setTimeout(() => { if (root.isConnected) load(formURL(), false); }, 400);
    });
    root.addEventListener('click', event => {
      if (event.target.closest('[data-editor-post-link]')) { event.preventDefault(); return; }
      const link = event.target.closest('.blog-pagination a');
      if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) return;
      event.preventDefault();
      load(new URL(link.href), true);
    });
    if ('IntersectionObserver' in window) {
      const marker = document.createElement('span');
      marker.setAttribute('aria-hidden', 'true');
      root.prepend(marker);
      const observer = new IntersectionObserver(entries => {
        if (!root.isConnected) { observer.disconnect(); return; }
        form.classList.toggle('is-stuck', !entries[0].isIntersecting && entries[0].boundingClientRect.top < 0);
      });
      observer.observe(marker);
    }
  }
  const scan = scope => {
    const node = scope?.[0] || scope || document;
    if (node.matches?.('[data-posts-widget]')) init(node);
    node.querySelectorAll?.('[data-posts-widget]').forEach(init);
  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => scan(document));
  else scan(document);
  let registered = false;
  function register() {
    if (registered || !window.elementorFrontend?.hooks || !window.elementorFrontend?.elementsHandler || !window.elementorModules?.frontend?.handlers?.Base) return;
    registered = true;
    window.elementorFrontend.hooks.addAction('frontend/element_ready/jednotka-posts.default', scan);
  }
  if (window.jQuery) window.jQuery(window).on('elementor/frontend/init', register);
  register();
  window.addEventListener('popstate', () => { if (document.querySelector('[data-posts-widget]')) location.reload(); });
})();
