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
    class ResultsHandler extends elementorModules.frontend.handlers.Base {
      onInit() {
        super.onInit();
        const section = this.$element[0].querySelector('.jn-results');
        if (!section) return;
        this.controller = new AbortController();
        this.frame = null;
        const { signal } = this.controller;
        const projectCards = [...section.querySelectorAll('.project-card')];
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        const editing = elementorFrontend.isEditMode();
        // Original examples/js/project-reveal.js calculations, scoped to this widget.
        const clampProgress = (value) => Math.min(Math.max(value, 0), 1);
        
        const setCardProgress = (card, progress) => {
          const eased = 1 - Math.pow(1 - progress, 3);
          const blur = (1 - eased) * 30;
          const translateY = (1 - eased) * 92;
          const scale = 0.88 + eased * 0.12;
          const opacity = 0.1 + eased * 0.9;
        
          card.style.opacity = opacity.toFixed(3);
          card.style.filter = `blur(${blur.toFixed(2)}px)`;
          card.style.transform = `translateY(${translateY.toFixed(2)}px) scale(${scale.toFixed(3)})`;
        };
        
        const resetCards = () => {
          projectCards.forEach((card) => {
            card.style.opacity = "";
            card.style.filter = "";
            card.style.transform = "";
          });
        };
        
        const updateProjectsOnScroll = () => {
          if (editing || reduceMotion.matches || section.dataset.animate !== 'yes') {
            resetCards();
            return;
          }
        
          const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
          const start = viewportHeight * 0.94;
          const end = viewportHeight * 0.42;
        
          projectCards.forEach((card, index) => {
            if (card.contains(document.activeElement)) { setCardProgress(card, 1); return; }
            const rect = card.getBoundingClientRect();
            const stagger = (index % 2) * 0.08;
            const progress = clampProgress((start - rect.top) / (start - end) - stagger);
        
            setCardProgress(card, progress);
          });
        };
        

        this.resetCards = resetCards;
        const requestProjectUpdate = () => {
          if (this.frame !== null) return;
          this.frame = window.requestAnimationFrame(() => { this.frame = null; updateProjectsOnScroll(); });
        };
        this.videos = [...section.querySelectorAll('video')];
        projectCards.forEach(card => {
          const videos = [...card.querySelectorAll('video')];
          const button = card.querySelector('.jn-results__video-toggle');
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
          if (!editing && !reduceMotion.matches) videos.forEach(video => video.play().catch(sync));
          sync();
        });
        section.addEventListener('click', event => {
          if (editing && event.target.closest('.project-card-link')) event.preventDefault();
        }, { capture: true, signal });
        // Keyboard users can always see the project receiving focus.
        section.addEventListener('focusin', event => {
          const card = event.target.closest('.project-card');
          if (card) { card.style.opacity = '1'; card.style.filter = 'none'; card.style.transform = 'none'; }
        }, { signal });
        section.addEventListener('focusout', requestProjectUpdate, { signal });
        window.addEventListener('scroll', requestProjectUpdate, { passive: true, signal });
        window.addEventListener('resize', requestProjectUpdate, { signal });
        reduceMotion.addEventListener('change', () => {
          if (reduceMotion.matches) this.videos.forEach(video => video.pause());
          updateProjectsOnScroll();
        }, { signal });
        updateProjectsOnScroll();
      }
      onDestroy() {
        this.controller?.abort();
        if (this.frame != null) window.cancelAnimationFrame(this.frame);
        this.videos?.forEach(video => video.pause());
        this.resetCards?.();
        super.onDestroy();
      }
    }
    elementorFrontend.hooks.addAction('frontend/element_ready/jednotka-results.default', $element => {
      elementorFrontend.elementsHandler.addHandler(ResultsHandler, { $element });
    });
  }
  jQuery(window).on('elementor/frontend/init', register);
  register();
})();
