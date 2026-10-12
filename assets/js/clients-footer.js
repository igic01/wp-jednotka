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
    class ClientsFooterHandler extends elementorModules.frontend.handlers.Base {
      onInit() {
        super.onInit();
        const clientsCta = this.$element[0].querySelector('.jn-clients-footer');
        if (!clientsCta) return;
        this.controller = new AbortController();
        this.floatFrame = null;
        this.trailTimers = new Map();
        const { signal } = this.controller;
        const trailLayer = clientsCta.querySelector('.cursor-trail-layer');
        const floatingCards = [...clientsCta.querySelectorAll('.floating-project-card')];
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        const editing = elementorFrontend.isEditMode();
        const control = clientsCta.querySelector('.jn-clients-footer__motion');
        this.videos = [...clientsCta.querySelectorAll('video')];
        let playing = !reduceMotion.matches;
        let typing = false;
        let lastTrail = 0;
        const canAnimate = () => playing && !typing && !reduceMotion.matches && !document.hidden;
        const clearTrail = () => {
          this.trailTimers.forEach((dot, timer) => { window.clearTimeout(timer); dot.remove(); });
          this.trailTimers.clear();
        };
        const resetFloatingCards = () => {
          clientsCta.style.setProperty('--float-x', '0px');
          clientsCta.style.setProperty('--float-y', '0px');
        };
        const resetFloatingCardDrift = () => {
          floatingCards.forEach(card => {
            card.style.setProperty('--float-card-drift-x', '0px');
            card.style.setProperty('--float-card-drift-y', '0px');
          });
        };
        // Original examples/js/floating-projects.js pointer and drift calculations.
        const updateFloatingCards = event => {
          if (!canAnimate() || editing) return;
          const rect = clientsCta.getBoundingClientRect();
          if (!rect.width || !rect.height) return;
          if (clientsCta.dataset.parallax === 'yes') {
            const x = ((event.clientX - rect.left) / rect.width - 0.5) * 28;
            const y = ((event.clientY - rect.top) / rect.height - 0.5) * 28;
            clientsCta.style.setProperty('--float-x', `${x.toFixed(2)}px`);
            clientsCta.style.setProperty('--float-y', `${y.toFixed(2)}px`);
          }
          if (!trailLayer || clientsCta.dataset.trail !== 'yes' || event.timeStamp - lastTrail < 42) return;
          lastTrail = event.timeStamp;
          const dot = document.createElement('span');
          const size = 10 + Math.random() * 18;
          dot.className = 'cursor-trail-dot';
          dot.style.left = `${event.clientX - rect.left}px`;
          dot.style.top = `${event.clientY - rect.top}px`;
          dot.style.setProperty('--trail-size', `${size.toFixed(1)}px`);
          trailLayer.appendChild(dot);
          const timer = window.setTimeout(() => { dot.remove(); this.trailTimers.delete(timer); }, 950);
          this.trailTimers.set(timer, dot);
        };
        const animateFloatingCardDrift = time => {
          this.floatFrame = null;
          if (!canAnimate() || clientsCta.dataset.drift !== 'yes') { resetFloatingCardDrift(); return; }
          floatingCards.forEach((card, index) => {
            const phase = index * 1.31;
            const driftX = Math.sin(time * 0.00048 + phase) * 4.5;
            const driftY = Math.cos(time * 0.00058 + phase) * 6;
            card.style.setProperty('--float-card-drift-x', `${driftX.toFixed(2)}px`);
            card.style.setProperty('--float-card-drift-y', `${driftY.toFixed(2)}px`);
          });
          this.floatFrame = window.requestAnimationFrame(animateFloatingCardDrift);
        };
        const syncDrift = () => {
          if (this.floatFrame !== null) { window.cancelAnimationFrame(this.floatFrame); this.floatFrame = null; }
          if (!canAnimate() || clientsCta.dataset.drift !== 'yes') {
            resetFloatingCardDrift(); resetFloatingCards(); clearTrail(); return;
          }
          this.floatFrame = window.requestAnimationFrame(animateFloatingCardDrift);
        };
        const syncMedia = (userInitiated = false) => {
          this.videos.forEach(video => {
            if (playing && !typing && !document.hidden && (!editing || userInitiated)) video.play().catch(() => {});
            else video.pause();
          });
        };
        const setPlaying = (value, userInitiated = false) => {
          playing = value;
          control.textContent = playing ? 'Ⅱ' : '▶';
          control.setAttribute('aria-pressed', String(playing));
          control.setAttribute('aria-label', playing ? control.dataset.pauseLabel : control.dataset.playLabel);
          syncDrift(); syncMedia(userInitiated);
        };
        control.hidden = !this.videos.length && !['drift', 'parallax', 'trail'].some(key => clientsCta.dataset[key] === 'yes');
        control.addEventListener('click', () => setPlaying(!playing, true), { signal });
        clientsCta.addEventListener('pointermove', updateFloatingCards, { signal });
        clientsCta.addEventListener('pointerleave', resetFloatingCards, { signal });
        reduceMotion.addEventListener('change', () => { if (reduceMotion.matches) setPlaying(false); else syncDrift(); }, { signal });
        document.addEventListener('visibilitychange', () => { syncDrift(); syncMedia(); }, { signal });
        clientsCta.addEventListener('focusin', event => {
          if (editing && event.target.closest('[contenteditable="true"]')) { typing = true; syncDrift(); syncMedia(); }
        }, { signal });
        clientsCta.addEventListener('focusout', () => { typing = false; syncDrift(); syncMedia(); }, { signal });
        clientsCta.addEventListener('click', event => { if (editing && event.target.closest('a')) event.preventDefault(); }, { capture: true, signal });
        const form = clientsCta.querySelector('.footer-newsletter');
        if (form) {
          const showNotice = event => {
            event.preventDefault();
            if (!editing) form.querySelector('.jn-newsletter-notice').hidden = false;
          };
          form.addEventListener('submit', showNotice, { signal });
          form.querySelector('button').addEventListener('click', showNotice, { signal });
          // Block implicit submission even when the visitor presses Enter in the email field.
          form.addEventListener('keydown', event => { if (event.key === 'Enter') showNotice(event); }, { signal });
        }
        this.resetMotion = () => { clearTrail(); resetFloatingCards(); resetFloatingCardDrift(); };
        setPlaying(playing);
      }
      onDestroy() {
        this.controller?.abort();
        if (this.floatFrame != null) window.cancelAnimationFrame(this.floatFrame);
        this.resetMotion?.();
        this.videos?.forEach(video => video.pause());
        super.onDestroy();
      }
    }
    elementorFrontend.hooks.addAction('frontend/element_ready/jednotka-clients-footer.default', $element => {
      elementorFrontend.elementsHandler.addHandler(ClientsFooterHandler, { $element });
    });
  }
  jQuery(window).on('elementor/frontend/init', register);
  register();
})();
