/* global elementorFrontend, elementorModules, jQuery */
(() => {
  'use strict';
  let registered = false;

  function register() {
    if (registered) return;
    // Elementor creates its global object before init() creates these APIs.
    if (typeof window.elementorFrontend?.hooks?.addAction !== 'function' ||
        typeof window.elementorFrontend?.elementsHandler?.addHandler !== 'function' ||
        !window.elementorModules?.frontend?.handlers?.Base) return;
    registered = true;

    class HeaderHandler extends elementorModules.frontend.handlers.Base {
      onInit() {
        super.onInit();
        this.controller = new AbortController();
        const { signal } = this.controller;
        const header = this.$element[0].querySelector('.jn-header');
        if (!header) return;
        const toggle = header.querySelector('.jn-header__toggle');
        const nav = header.querySelector('.jn-header__nav');
        const menu = header.querySelector('.jn-header__menu');
        const close = header.querySelector('.jn-header__menu-close');
        const mobile = window.matchMedia('(max-width: 1024px)');
        const editing = elementorFrontend.isEditMode();
        let previousOverflow = null;
        const syncClosed = () => {
          header.classList.remove('is-open');
          toggle.setAttribute('aria-expanded', 'false');
          if (previousOverflow !== null) {
            document.body.style.overflow = previousOverflow;
            previousOverflow = null;
          }
        };
        const setOpen = (open, returnFocus = false) => {
          open = open && mobile.matches;
          if (open && !menu.open) {
            previousOverflow = document.body.style.overflow;
            menu.showModal();
            document.body.style.overflow = 'hidden';
          } else if (!open) {
            menu.close();
            syncClosed();
          }
          header.classList.toggle('is-open', open);
          toggle.setAttribute('aria-expanded', String(open));
          if (returnFocus) toggle.focus();
        };
        this.closeMenu = () => setOpen(false);
        close.addEventListener('click', () => setOpen(false, true), { signal });
        menu.addEventListener('close', syncClosed, { signal });
        toggle.addEventListener('click', () => setOpen(!header.classList.contains('is-open')), { signal });
        nav.addEventListener('click', (event) => {
          if (!editing && event.target.closest('a')) setOpen(false);
        }, { signal });
        header.addEventListener('keydown', (event) => {
          if (event.key === 'Escape') setOpen(false, true);
        }, { signal });
        document.addEventListener('pointerdown', (event) => {
          if (!header.contains(event.target)) setOpen(false);
        }, { signal });
        mobile.addEventListener('change', () => setOpen(false), { signal });
        header.addEventListener('input', (event) => {
          if (event.target.matches('.jn-header__button-text')) {
            header.querySelector('.jn-header__button-copy').textContent = event.target.textContent;
          }
        }, { signal });

        this.video = header.querySelector('video');
        if (this.video) {
          const video = this.video;
          const control = header.querySelector('.jn-header__video-toggle');
          const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
          const update = () => {
            control.textContent = video.paused ? '▶' : 'Ⅱ';
            control.setAttribute('aria-label', video.paused ? control.dataset.playLabel : control.dataset.pauseLabel);
            control.setAttribute('aria-pressed', String(!video.paused));
          };
          const play = () => { video.play().catch(update); };
          video.addEventListener('play', update, { signal });
          video.addEventListener('pause', update, { signal });
          control.addEventListener('click', () => video.paused ? play() : video.pause(), { signal });
          reducedMotion.addEventListener('change', () => { if (reducedMotion.matches) video.pause(); }, { signal });
          if (!reducedMotion.matches && !editing) play();
          update();
        }
      }

      onDestroy() {
        this.closeMenu?.();
        this.controller?.abort();
        this.video?.pause();
        super.onDestroy();
      }
    }

    const attachHeader = ($element) => {
      if (!$element[0].querySelector('.jn-header')) return;
      elementorFrontend.elementsHandler.addHandler(HeaderHandler, { $element });
    };
    elementorFrontend.hooks.addAction('frontend/element_ready/jednotka-header.default', attachHeader);
    elementorFrontend.hooks.addAction('frontend/element_ready/jednotka-hero.default', attachHeader);
    elementorFrontend.hooks.addAction('frontend/element_ready/jednotka-services-hero.default', attachHeader);
  }

  jQuery(window).on('elementor/frontend/init', register);
  register();
})();
