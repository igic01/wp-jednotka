/* global elementorFrontend, elementorModules, jQuery */
(() => {
  'use strict';
  let registered = false;
  let active = null;
  function register() {
    if (registered || !window.elementorFrontend?.hooks || !window.elementorFrontend?.elementsHandler || !window.elementorModules?.frontend?.handlers?.Base) return;
    registered = true;
    class GalleryHandler extends elementorModules.frontend.handlers.Base {
      onInit() {
        super.onInit();
        const root = this.$element[0].querySelector('.jn-gallery');
        if (!root) return;
        this.controller = new AbortController();
        const { signal } = this.controller;
        const editing = elementorFrontend.isEditMode();
        const items = [...root.querySelectorAll('.jn-gallery__item')];
        let dialog, image, opener, index = 0, revision = 0, motion, closing = false, overflow;
        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
        const transform = thumb => {
          const from = thumb.getBoundingClientRect(), to = image.getBoundingClientRect();
          return to.width && to.height ? `translate(${from.left - to.left}px, ${from.top - to.top}px) scale(${from.width / to.width}, ${from.height / to.height})` : 'none';
        };
        const cleanup = () => {
          if (active !== this) return;
          revision++;
          motion?.cancel(); closing = false;
          document.documentElement.style.overflow = overflow;
          active = null;
          image.removeAttribute('src');
          if (opener?.isConnected) opener.focus({ preventScroll: true });
        };
        this.close = (immediate = false) => {
          if (!dialog?.open) return;
          revision++; motion?.cancel(); closing = true;
          const finish = () => { dialog.close(); cleanup(); };
          if (!immediate && !reduced.matches && image.complete && image.naturalWidth && image.animate) {
            motion = image.animate([{ transform: 'none', opacity: 1 }, { transform: transform(items[index]), opacity: .4 }], { duration: 350, easing: 'cubic-bezier(.7,0,.3,1)' });
            motion.finished.then(finish, () => {});
          } else finish();
        };
        const show = (next, fromThumbnail = false) => {
          if (closing) return;
          index = (next + items.length) % items.length;
          const ticket = ++revision;
          motion?.cancel();
          const thumb = items[index].querySelector('img');
          image.alt = thumb.alt;
          image.src = items[index].href;
          dialog.querySelector('[role="status"]').textContent = `${index + 1} / ${items.length}`;
          const ready = () => {
            if (ticket !== revision || !dialog.open || closing) return;
            if (!reduced.matches && image.animate) motion = image.animate(fromThumbnail ? [{ transform: transform(items[index]), opacity: .6 }, { transform: 'none', opacity: 1 }] : [{ opacity: .3 }, { opacity: 1 }], { duration: fromThumbnail ? 500 : 250, easing: 'cubic-bezier(.16,1,.3,1)' });
          };
          if (image.decode) image.decode().then(ready, () => {
            if (ticket === revision && dialog.open) dialog.querySelector('[role="status"]').textContent = 'Obrázok sa nepodarilo načítať.';
          });
          else if (image.complete) ready();
          else image.onload = ready;
        };
        const create = () => {
          dialog = document.createElement('dialog');
          dialog.className = 'jn-gallery-dialog';
          dialog.setAttribute('aria-label', 'Zväčšený obrázok');
          dialog.innerHTML = '<img class="jn-gallery-dialog__image" alt="" /><button class="jn-gallery-dialog__button jn-gallery-dialog__close" type="button" aria-label="Zavrieť"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" /></svg></button><button class="jn-gallery-dialog__button jn-gallery-dialog__prev" type="button" aria-label="Predchádzajúci obrázok"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 5-7 7 7 7" /></svg></button><button class="jn-gallery-dialog__button jn-gallery-dialog__next" type="button" aria-label="Ďalší obrázok"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 5 7 7-7 7" /></svg></button><span class="jn-gallery-dialog__status" role="status"></span>';
          document.body.append(dialog);
          image = dialog.querySelector('img');
          dialog.toggleAttribute('data-single', items.length < 2);
          dialog.addEventListener('click', event => {
            if (event.target === dialog || event.target.closest('.jn-gallery-dialog__close')) this.close();
            else if (event.target.closest('.jn-gallery-dialog__prev')) show(index - 1);
            else if (event.target.closest('.jn-gallery-dialog__next')) show(index + 1);
          }, { signal });
          dialog.addEventListener('cancel', event => { event.preventDefault(); this.close(); }, { signal });
          dialog.addEventListener('close', () => { if (!dialog.open) cleanup(); }, { signal });
          dialog.addEventListener('keydown', event => {
            if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') { event.preventDefault(); show(index + (event.key === 'ArrowLeft' ? -1 : 1)); }
          }, { signal });
        };
        root.addEventListener('click', event => {
          const item = event.target.closest('.jn-gallery__item');
          if (!item || !items.includes(item)) return;
          if (editing) { event.preventDefault(); return; }
          if (root.dataset.lightbox !== 'yes' || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button > 0) return;
          if (!dialog) create();
          if (!dialog.showModal) return; // Original image links remain usable in older browsers.
          event.preventDefault();
          if (active) active.close(true);
          active = this; opener = item; closing = false;
          overflow = document.documentElement.style.overflow;
          document.documentElement.style.overflow = 'hidden';
          dialog.showModal();
          show(items.indexOf(item), true);
          dialog.querySelector('.jn-gallery-dialog__close').focus({ preventScroll: true });
        }, { signal });
        reduced.addEventListener('change', () => { if (reduced.matches) { motion?.finish(); } }, { signal });
        this.dispose = () => { this.close(true); revision++; motion?.cancel(); dialog?.remove(); };
      }
      onDestroy() {
        this.dispose?.();
        this.controller?.abort();
        super.onDestroy();
      }
    }
    elementorFrontend.hooks.addAction('frontend/element_ready/jednotka-gallery.default', $element => {
      elementorFrontend.elementsHandler.addHandler(GalleryHandler, { $element });
    });
  }
  jQuery(window).on('elementor/frontend/init', register);
  register();
})();
