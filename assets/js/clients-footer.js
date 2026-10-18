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
        const { signal } = this.controller;
        const glowLayer = clientsCta.querySelector('.cursor-trail-layer');
        const floatingCards = [...clientsCta.querySelectorAll('.floating-project-card')];
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        const editing = elementorFrontend.isEditMode();
        const control = clientsCta.querySelector('.jn-clients-footer__motion');
        this.videos = [...clientsCta.querySelectorAll('video')];
        let playing = !reduceMotion.matches;
        let typing = false;
        this.snapTimer = null;
        let snapped = false;
        const cancelSnap = () => {
          if (this.snapTimer !== null) window.clearTimeout(this.snapTimer);
          this.snapTimer = null;
        };
        const requestSnap = () => {
          cancelSnap();
          const top = clientsCta.getBoundingClientRect().top;
          if (Math.abs(top) > window.innerHeight * 0.65) snapped = false;
          if (editing || reduceMotion.matches || document.hidden || snapped ||
              clientsCta.dataset.snap !== 'yes' || clientsCta.contains?.(document.activeElement) ||
              Math.abs(top) < 2 || Math.abs(top) > window.innerHeight * 0.22) return;
          this.snapTimer = window.setTimeout(() => {
            this.snapTimer = null;
            if (reduceMotion.matches || document.hidden) return;
            const remaining = clientsCta.getBoundingClientRect().top;
            if (Math.abs(remaining) > window.innerHeight * 0.22) return;
            snapped = true;
            window.scrollTo({ top: window.scrollY + remaining, behavior: 'smooth' });
          }, 200);
        };
        window.addEventListener('scroll', requestSnap, { passive: true, signal });
        window.addEventListener('wheel', cancelSnap, { passive: true, signal });
        window.addEventListener('touchstart', cancelSnap, { passive: true, signal });
        window.addEventListener('keydown', cancelSnap, { signal });
        const canAnimate = () => playing && !typing && !reduceMotion.matches && !document.hidden;
        const revealCards = () => {
          this.revealObserver?.disconnect();
          floatingCards.forEach(card => {
            card.style.setProperty('--reveal-delay', '0ms');
            card.classList.remove('is-reveal-pending');
          });
        };
        const clearGlow = () => { glowLayer?.style.setProperty('--glow-opacity', '0'); };
        const resetFloatingCards = () => {
          clientsCta.style.setProperty('--float-x', '0px');
          clientsCta.style.setProperty('--float-y', '0px');
        };
        const resetFloatingCardDrift = () => {
          floatingCards.forEach(card => {
            card.style.setProperty('--float-card-drift-x', '0px');
            card.style.setProperty('--float-card-drift-y', '0px');
            card.style.setProperty('--float-card-tilt', '0deg');
            card.style.setProperty('--image-pan', '0%');
            card.style.setProperty('--image-zoom', '1');
          });
        };
        // Original examples/js/floating-projects.js pointer and drift calculations.
        const updateFloatingCards = event => {
          if (!canAnimate() || editing || event.pointerType === 'touch') return;
          const rect = clientsCta.getBoundingClientRect();
          if (!rect.width || !rect.height) return;
          if (clientsCta.dataset.parallax === 'yes') {
            const x = ((event.clientX - rect.left) / rect.width - 0.5) * 28;
            const y = ((event.clientY - rect.top) / rect.height - 0.5) * 28;
            clientsCta.style.setProperty('--float-x', `${x.toFixed(2)}px`);
            clientsCta.style.setProperty('--float-y', `${y.toFixed(2)}px`);
          }
          if (!glowLayer || clientsCta.dataset.trail !== 'yes') return;
          glowLayer.style.setProperty('--glow-x', `${event.clientX - rect.left}px`);
          glowLayer.style.setProperty('--glow-y', `${event.clientY - rect.top}px`);
          glowLayer.style.setProperty('--glow-opacity', '1');
        };
        const animateFloatingCardDrift = time => {
          this.floatFrame = null;
          if (!canAnimate() || clientsCta.dataset.drift !== 'yes') { resetFloatingCardDrift(); return; }
          floatingCards.forEach((card, index) => {
            const phase = index * 1.31;
            const driftX = Math.sin(time * 0.00048 + phase) * 9;
            const driftY = Math.cos(time * 0.00058 + phase) * 12;
            card.style.setProperty('--float-card-drift-x', `${driftX.toFixed(2)}px`);
            card.style.setProperty('--float-card-drift-y', `${driftY.toFixed(2)}px`);
            // Stronger idle sway and image pan/zoom continue through hover.
            card.style.setProperty('--float-card-tilt', `${(Math.sin(time * 0.00036 + phase) * 4).toFixed(2)}deg`);
            card.style.setProperty('--image-pan', `${(Math.sin(time * 0.00022 + phase) * 2).toFixed(2)}%`);
            card.style.setProperty('--image-zoom', (1.10 + Math.cos(time * 0.00028 + phase) * 0.035).toFixed(4));
          });
          this.floatFrame = window.requestAnimationFrame(animateFloatingCardDrift);
        };
        const syncDrift = () => {
          if (this.floatFrame !== null) { window.cancelAnimationFrame(this.floatFrame); this.floatFrame = null; }
          if (!canAnimate() || clientsCta.dataset.drift !== 'yes') {
            resetFloatingCardDrift(); resetFloatingCards(); clearGlow(); return;
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
          clientsCta.setAttribute('data-motion', playing ? 'playing' : 'paused');
          if (!playing) revealCards();
          if (control) {
            control.textContent = playing ? 'Ⅱ' : '▶';
            control.setAttribute('aria-pressed', String(playing));
            control.setAttribute('aria-label', playing ? control.dataset.pauseLabel : control.dataset.playLabel);
          }
          syncDrift(); syncMedia(userInitiated);
        };
        if (control) {
          control.hidden = !this.videos.length && !['reveal', 'drift', 'parallax', 'trail'].some(key => clientsCta.dataset[key] === 'yes');
          control.addEventListener('click', () => setPlaying(!playing, true), { signal });
        }
        clientsCta.addEventListener('pointerdown', cancelSnap, { signal });
        clientsCta.addEventListener('pointermove', updateFloatingCards, { signal });
        clientsCta.addEventListener('pointerleave', () => { resetFloatingCards(); clearGlow(); }, { signal });
        reduceMotion.addEventListener('change', () => { if (reduceMotion.matches) { cancelSnap(); setPlaying(false); } else syncDrift(); }, { signal });
        document.addEventListener('visibilitychange', () => { if (document.hidden) cancelSnap(); syncDrift(); syncMedia(); }, { signal });
        clientsCta.addEventListener('focusin', event => {
          cancelSnap();
          const focusedCard = event.target.closest('.floating-project-card');
          if (focusedCard) {
            focusedCard.style.setProperty('--reveal-delay', '0ms');
            focusedCard.classList.remove('is-reveal-pending');
            this.revealObserver?.unobserve(focusedCard);
          }
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
        this.resetMotion = () => { cancelSnap(); clearGlow(); resetFloatingCards(); resetFloatingCardDrift(); };
        this.revealCards = revealCards;
        setPlaying(playing);
        // Progressive enhancement: cards stay visible without observer support.
        if (clientsCta.dataset.reveal === 'yes' && !editing && canAnimate() && window.IntersectionObserver) {
          this.revealObserver = new window.IntersectionObserver(entries => {
            entries.forEach(entry => {
              if (!entry.isIntersecting) return;
              entry.target.classList.remove('is-reveal-pending');
              this.revealObserver.unobserve(entry.target);
            });
          }, { threshold: 0.15, rootMargin: '0px 0px -6% 0px' });
          floatingCards.forEach((card, index) => {
            card.style.setProperty('--reveal-delay', `${(index % 3) * 90}ms`);
            card.classList.add('is-reveal-pending');
            this.revealObserver.observe(card);
          });
        }
      }
      onDestroy() {
        this.controller?.abort();
        if (this.floatFrame != null) window.cancelAnimationFrame(this.floatFrame);
        this.resetMotion?.();
        this.revealCards?.();
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
