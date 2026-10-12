/* global elementorFrontend, elementorModules, jQuery */
(() => {
  'use strict';
  let registered = false;
  function register() {
    if (registered) return;
    if (typeof window.elementorFrontend?.hooks?.addAction !== 'function' ||
        typeof window.elementorFrontend?.elementsHandler?.addHandler !== 'function' ||
        !window.elementorModules?.frontend?.handlers?.Base) return;
    registered = true;

    class LogoStripHandler extends elementorModules.frontend.handlers.Base {
      onInit() {
        super.onInit();
        this.controller = new AbortController();
        const { signal } = this.controller;
        const strip = this.$element[0].querySelector('.jn-logo-strip');
        if (!strip) return;
        const groups = [...strip.querySelectorAll('.logo-strip-group')];
        const originals = [...groups[0].children];
        const control = strip.querySelector('.jn-logo-strip__motion');
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)');
        const editing = elementorFrontend.isEditMode();
        let playing = strip.dataset.animate === 'yes' && !reduce.matches;
        let hovered = false;
        let allowEditorVideo = false;
        const syncVideos = () => {
          this.videos = [...strip.querySelectorAll('video')];
          this.videos.forEach(video => {
            if (playing && !hovered && (!editing || allowEditorVideo)) video.play().catch(() => {});
            else video.pause();
          });
        };
        const setPlaying = (value, userInitiated = false) => {
          playing = value;
          if (userInitiated) allowEditorVideo = true;
          strip.classList.toggle('is-paused', !playing);
          control.textContent = playing ? 'Ⅱ' : '▶';
          control.setAttribute('aria-pressed', String(playing));
          control.setAttribute('aria-label', playing ? control.dataset.pauseLabel : control.dataset.playLabel);
          syncVideos();
        };
        control.hidden = strip.dataset.animate !== 'yes' && !strip.querySelector('video');
        control.addEventListener('click', () => setPlaying(!playing, true), { signal });
        reduce.addEventListener('change', () => { if (reduce.matches) setPlaying(false); }, { signal });
        strip.addEventListener('pointerenter', () => {
          if (strip.dataset.pauseHover === 'yes') { hovered = true; syncVideos(); }
        }, { signal });
        strip.addEventListener('pointerleave', () => { hovered = false; syncVideos(); }, { signal });

        // Preserve the reference's two equal groups. Repeat short replacement
        // media within both groups only when needed to cover the viewport.
        const fill = () => {
          const style = window.getComputedStyle(groups[0]);
          const contentWidth = originals.reduce((sum, media) => sum + media.getBoundingClientRect().width, 0);
          const width = contentWidth + (parseFloat(style.columnGap) || 0) * Math.max(0, originals.length - 1) + (parseFloat(style.paddingRight) || 0);
          if (!originals.length || contentWidth <= 0 || width <= 0) return;
          const copies = Math.max(1, Math.ceil(strip.getBoundingClientRect().width / width));
          groups.forEach(group => {
            group.querySelectorAll('[data-fill-copy]').forEach(media => {
              if (media.tagName === 'VIDEO') media.pause();
              media.remove();
            });
            for (let copy = 1; copy < copies; copy++) {
              originals.forEach(media => {
                const clone = media.cloneNode(true);
                clone.dataset.fillCopy = 'true';
                clone.setAttribute('aria-hidden', 'true');
                if (clone.tagName === 'IMG') clone.alt = '';
                group.append(clone);
              });
            }
          });
          syncVideos();
        };
        this.observer = new ResizeObserver(fill);
        this.observer.observe(strip);
        originals.forEach(media => {
          this.observer.observe(media);
          media.addEventListener(media.tagName === 'VIDEO' ? 'loadedmetadata' : 'load', fill, { signal });
        });
        setPlaying(playing);
        fill();
      }

      onDestroy() {
        this.controller?.abort();
        this.observer?.disconnect();
        this.videos?.forEach(video => video.pause());
        super.onDestroy();
      }
    }
    elementorFrontend.hooks.addAction('frontend/element_ready/jednotka-logo-strip.default', ($element) => {
      elementorFrontend.elementsHandler.addHandler(LogoStripHandler, { $element });
    });
  }
  jQuery(window).on('elementor/frontend/init', register);
  register();
})();
