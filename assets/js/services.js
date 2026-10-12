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
    class ServicesHandler extends elementorModules.frontend.handlers.Base {
      onInit() {
        super.onInit();
        const section = this.$element[0].querySelector('.jn-services');
        if (!section) return;
        this.controller = new AbortController();
        this.frame = null;
        const { signal } = this.controller;
        const serviceRows = [...section.querySelectorAll('.service-row')];
        const servicesReduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        const editing = elementorFrontend.isEditMode();
        // Original examples/js/services-reveal.js calculations.
        const clampServiceProgress = (value) => Math.min(Math.max(value, 0), 1);
        
        const setServiceProgress = (row, progress) => {
          const eased = 1 - Math.pow(1 - progress, 3);
          const blur = (1 - eased) * 18;
          const translateY = (1 - eased) * 58;
          const scale = 0.96 + eased * 0.04;
          const opacity = 0.22 + eased * 0.78;
          const arrow = row.querySelector(".service-arrow");
        
          row.style.opacity = opacity.toFixed(3);
          row.style.filter = `blur(${blur.toFixed(2)}px)`;
          row.style.transform = `translateY(${translateY.toFixed(2)}px) scale(${scale.toFixed(3)})`;
        
          if (arrow) {
            const arrowOffset = (1 - eased) * 18;
            arrow.style.setProperty("--arrow-scroll-x", `${arrowOffset.toFixed(2)}px`);
            arrow.style.setProperty("--arrow-scroll-y", `${arrowOffset.toFixed(2)}px`);
          }
        };
        
        const resetServiceRows = () => {
          serviceRows.forEach((row) => {
            row.style.opacity = "";
            row.style.filter = "";
            row.style.transform = "";
        
            const arrow = row.querySelector(".service-arrow");
        
            if (arrow) {
              arrow.style.removeProperty("--arrow-scroll-x");
              arrow.style.removeProperty("--arrow-scroll-y");
            }
          });
        };
        
        const updateServicesOnScroll = () => {
          if (editing || servicesReduceMotion.matches || section.dataset.animate !== 'yes') {
            resetServiceRows();
            return;
          }
        
          const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
          const start = viewportHeight * 0.92;
          const end = viewportHeight * 0.48;
        
          serviceRows.forEach((row, index) => {
            if (row.parentElement.contains(document.activeElement)) { setServiceProgress(row, 1); return; }
            const rect = row.getBoundingClientRect();
            const stagger = (index % 2) * 0.04;
            const progress = clampServiceProgress((start - rect.top) / (start - end) - stagger);
        
            setServiceProgress(row, progress);
          });
        };
        

        this.resetCards = resetServiceRows;
        const requestServicesUpdate = () => {
          if (this.frame !== null) return;
          this.frame = window.requestAnimationFrame(() => { this.frame = null; updateServicesOnScroll(); });
        };
        this.videos = [...section.querySelectorAll('video')];
        section.querySelectorAll('.jn-service-item').forEach(card => {
          const videos = [...card.querySelectorAll('video')];
          const button = card.querySelector('.jn-services__video-toggle');
          if (!button || !videos.length) return;
          const sync = () => {
            const playing = videos.some(video => !video.paused);
            button.textContent = playing ? 'Ⅱ' : '▶';
            button.setAttribute('aria-pressed', String(playing));
            button.setAttribute('aria-label', playing ? button.dataset.pauseLabel : button.dataset.playLabel);
          };
          videos.forEach(video => {
            video.addEventListener('play', sync, { signal });
            video.addEventListener('pause', sync, { signal });
          });
          button.addEventListener('click', () => {
            const playing = videos.some(video => !video.paused);
            videos.forEach(video => { if (playing) video.pause(); else video.play().catch(sync); });
          }, { signal });
          if (!editing && !servicesReduceMotion.matches) videos.forEach(video => video.play().catch(sync));
          sync();
        });
        section.addEventListener('click', event => {
          if (editing && event.target.closest('.service-row')) event.preventDefault();
        }, { capture: true, signal });
        // Keyboard users can always see the service receiving focus.
        section.addEventListener('focusin', event => {
          const row = event.target.closest('.jn-service-item')?.querySelector('.service-row');
          if (row) setServiceProgress(row, 1);
        }, { signal });
        section.addEventListener('focusout', requestServicesUpdate, { signal });
        window.addEventListener('scroll', requestServicesUpdate, { passive: true, signal });
        window.addEventListener('resize', requestServicesUpdate, { signal });
        servicesReduceMotion.addEventListener('change', () => {
          if (servicesReduceMotion.matches) this.videos.forEach(video => video.pause());
          updateServicesOnScroll();
        }, { signal });
        updateServicesOnScroll();
      }
      onDestroy() {
        this.controller?.abort();
        if (this.frame != null) window.cancelAnimationFrame(this.frame);
        this.videos?.forEach(video => video.pause());
        this.resetCards?.();
        super.onDestroy();
      }
    }
    elementorFrontend.hooks.addAction('frontend/element_ready/jednotka-services.default', $element => {
      elementorFrontend.elementsHandler.addHandler(ServicesHandler, { $element });
    });
  }
  jQuery(window).on('elementor/frontend/init', register);
  register();
})();
