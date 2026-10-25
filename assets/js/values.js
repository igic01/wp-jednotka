/* global elementorFrontend, elementorModules, jQuery */
(() => {
  'use strict';
  let registered = false;
  function register() {
    if (registered || typeof window.elementorFrontend?.hooks?.addAction !== 'function' ||
        typeof window.elementorFrontend?.elementsHandler?.addHandler !== 'function' ||
        !window.elementorModules?.frontend?.handlers?.Base) return;
    registered = true;
    class ValuesHandler extends elementorModules.frontend.handlers.Base {
      onInit() {
        super.onInit();
        const section = this.$element[0].querySelector('.jn-values');
        if (!section) return;
        const cards = [...section.querySelectorAll('.values__card')];
        this.controller = new AbortController(); this.scrollFrame = null; this.tiltFrame = null;
        const { signal } = this.controller;
        const editing = elementorFrontend.isEditMode();
        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
        const fine = window.matchMedia('(hover: hover) and (pointer: fine)');
        const compact = window.matchMedia('(max-width: 1024px)');
        const clamp = (value, min = 0, max = 1) => Math.max(min, Math.min(max, value));
        const ease = value => 1 - Math.pow(1 - value, 3);
        const poses = [{x:-8,y:-6,rot:-6},{x:7,y:5,rot:5},{x:-5,y:8,rot:-3},{x:9,y:-4,rot:6},{x:-7,y:3,rot:-5},{x:6,y:-7,rot:4}];
        const stackProperties = ['--pose-x','--pose-y','--pose-rot','--enter','--settle','--fall','--dir','--behind','--drift'];
        const pointerProperties = ['--rx','--ry','--px','--py','--lift','--ox','--oy'];
        const states = new Map(), active = new Map();
        let visible = true;
        let cardIndex = 0, touch = null, wheelTime = -Infinity, wheelTotal = 0, wheelConsumed = false;
        const staticView = () => editing || reduced.matches || section.dataset.animate !== 'yes' || cards.length < 2;
        const cancelScroll = () => {
          if (this.scrollFrame !== null) window.cancelAnimationFrame(this.scrollFrame);
          this.scrollFrame = null;
        };
        const resetPointer = () => {
          if (this.tiltFrame !== null) window.cancelAnimationFrame(this.tiltFrame);
          this.tiltFrame = null; active.clear();
          cards.forEach(card => pointerProperties.forEach(key => card.style.removeProperty(key)));
          states.clear();
        };
        const update = () => {
          if (staticView()) return;
          const rect = section.getBoundingClientRect();
          const progress = clamp(-rect.top / Math.max(1, rect.height - window.innerHeight));
          if (compact.matches) {
            // Returning from either end starts with the card nearest that end.
            if (rect.top >= window.innerHeight) cardIndex = 0;
            else if (rect.top + rect.height <= 0) cardIndex = cards.length - 1;
          }
          const position = compact.matches ? cardIndex : clamp(progress / .92) * (cards.length - 1);
          cards.forEach((card, index) => {
            const pose = poses[index % poses.length];
            card.style.setProperty('--pose-x', pose.x);
            card.style.setProperty('--pose-y', pose.y);
            card.style.setProperty('--pose-rot', pose.rot);
            card.style.setProperty('--drift', (clamp(position - index + 1, 0, 2) / 2).toFixed(4));
            if (section.dataset.mode === 'fall') {
              card.style.setProperty('--fall', Math.pow(clamp(position - index), 2).toFixed(4));
              card.style.setProperty('--dir', pose.rot < 0 ? -1 : 1);
              card.style.setProperty('--behind', clamp(index - position, 0, 3).toFixed(4));
              card.style.zIndex = cards.length - index;
            } else {
              card.style.setProperty('--enter', (index === 0 ? 0 : 1 - ease(clamp(position - index + 1))).toFixed(4));
              card.style.setProperty('--settle', ease(clamp(position - index)).toFixed(4));
              card.style.zIndex = index + 1;
            }
          });
        };
        const requestUpdate = () => {
          if (staticView() || !visible || document.hidden || this.scrollFrame !== null) return;
          this.scrollFrame = window.requestAnimationFrame(() => { this.scrollFrame = null; update(); });
        };
        const gestureEnabled = event => compact.matches && !staticView() && visible && !document.hidden &&
          !event.target?.closest?.('a, button, input, textarea, select, [contenteditable="true"]');
        const nearFrame = () => Math.abs(section.getBoundingClientRect().top) <= window.innerHeight * .35;
        const canStep = direction => direction > 0 ? cardIndex < cards.length - 1 : cardIndex > 0;
        const step = direction => {
          cardIndex = clamp(cardIndex + direction, 0, cards.length - 1);
          // Align the section once, then count gestures rather than page distance.
          const top = section.getBoundingClientRect().top;
          if (Math.abs(top) > 1) window.scrollTo({ top: window.scrollY + top, behavior: 'instant' });
          update();
        };
        section.addEventListener('touchstart', event => {
          touch = gestureEnabled(event) && event.touches.length === 1 && nearFrame()
            ? { x: event.touches[0].clientX, y: event.touches[0].clientY, consumed: false } : null;
        }, { passive: true, signal });
        section.addEventListener('touchmove', event => {
          if (!touch || !gestureEnabled(event) || event.touches.length !== 1) { touch = null; return; }
          if (touch.consumed) { if (event.cancelable) event.preventDefault(); return; }
          const dx = event.touches[0].clientX - touch.x;
          const dy = touch.y - event.touches[0].clientY;
          if (Math.abs(dx) > Math.abs(dy)) { touch = null; return; }
          const direction = Math.sign(dy);
          if (!direction) return;
          if (!canStep(direction) || !event.cancelable) { touch = null; return; }
          event.preventDefault();
          if (Math.abs(dy) < 24) return;
          touch.consumed = true; step(direction);
        }, { passive: false, signal });
        const endTouch = () => { touch = null; };
        section.addEventListener('touchend', endTouch, { passive: true, signal });
        section.addEventListener('touchcancel', endTouch, { passive: true, signal });
        section.addEventListener('wheel', event => {
          if (!gestureEnabled(event) || event.ctrlKey || Math.abs(event.deltaX) > Math.abs(event.deltaY)) return;
          // Trackpad momentum belongs to the same gesture until it has gone quiet.
          if (event.timeStamp - wheelTime > 200) { wheelConsumed = false; wheelTotal = 0; }
          wheelTime = event.timeStamp;
          if (wheelConsumed) { if (event.cancelable) event.preventDefault(); return; }
          const direction = Math.sign(event.deltaY);
          if (!direction || !nearFrame() || !canStep(direction) || !event.cancelable) return;
          event.preventDefault();
          const unit = event.deltaMode === 1 ? 16 : event.deltaMode === 2 ? window.innerHeight : 1;
          wheelTotal += event.deltaY * unit;
          if (Math.abs(wheelTotal) >= 40) { wheelConsumed = true; step(Math.sign(wheelTotal)); }
        }, { passive: false, signal });
        const pointerEnabled = () => !staticView() && fine.matches && section.dataset.pointer === 'yes' && visible && !document.hidden;
        const tiltTick = () => {
          this.tiltFrame = null;
          if (!pointerEnabled()) { resetPointer(); return; }
          active.forEach((state, card) => {
            let difference = 0;
            for (const key of ['rx','ry','px','py','lift','ox','oy']) {
              state[key] += (state.target[key] - state[key]) * .1;
              difference += Math.abs(state.target[key] - state[key]);
              card.style.setProperty(`--${key}`, key === 'ox' || key === 'oy' ? `${state[key].toFixed(2)}%` : state[key].toFixed(3));
            }
            if (difference < .01) active.delete(card);
          });
          if (active.size) this.tiltFrame = window.requestAnimationFrame(tiltTick);
        };
        const wake = () => {
          if (pointerEnabled() && active.size && this.tiltFrame === null) this.tiltFrame = window.requestAnimationFrame(tiltTick);
        };
        cards.forEach(card => {
          card.addEventListener('pointermove', event => {
            if (event.pointerType === 'touch' || !pointerEnabled()) return;
            const rect = card.getBoundingClientRect();
            if (!rect.width || !card.offsetWidth || !card.offsetHeight) return;
            const scale = card.offsetWidth / rect.width;
            const fx = clamp((event.clientX - rect.left) * scale / card.offsetWidth);
            const fy = clamp((event.clientY - rect.top) * scale / card.offsetHeight);
            const nx = fx * 2 - 1, ny = fy * 2 - 1;
            let state = states.get(card);
            if (!state) { state = {rx:0,ry:0,px:0,py:0,lift:0,ox:50,oy:50,target:{}}; states.set(card,state); }
            state.target = {rx:-ny*9,ry:nx*9,px:nx,py:ny,lift:1,ox:fx*100,oy:fy*100};
            active.set(card,state); wake();
          }, { signal });
          card.addEventListener('pointerleave', () => {
            const state = states.get(card); if (!state) return;
            state.target = {rx:0,ry:0,px:0,py:0,lift:0,ox:50,oy:50}; active.set(card,state); wake();
          }, { signal });
        });
        const configure = () => {
          cancelScroll(); resetPointer();
          touch = null; wheelConsumed = false; wheelTotal = 0; wheelTime = -Infinity;
          section.setAttribute('data-static', staticView() ? 'yes' : 'no');
          section.setAttribute('data-counted', compact.matches && !staticView() ? 'yes' : 'no');
          section.style.setProperty('--count', cards.length);
          cards.forEach(card => { stackProperties.forEach(key => card.style.removeProperty(key)); card.style.zIndex = ''; });
          update();
        };
        window.addEventListener('scroll', requestUpdate, { passive:true, signal });
        window.addEventListener('resize', requestUpdate, { signal });
        reduced.addEventListener('change', configure, { signal });
        compact.addEventListener('change', configure, { signal });
        fine.addEventListener('change', resetPointer, { signal });
        document.addEventListener('visibilitychange', () => {
          if (document.hidden) { cancelScroll(); resetPointer(); } else requestUpdate();
        }, { signal });
        if (window.IntersectionObserver) {
          this.observer = new window.IntersectionObserver(entries => {
            visible = entries.some(entry => entry.isIntersecting);
            if (visible) requestUpdate(); else { cancelScroll(); resetPointer(); }
          });
          this.observer.observe(section);
        }
        this.cleanup = () => {
          cancelScroll(); resetPointer(); section.setAttribute('data-static','yes');
          section.removeAttribute('data-counted'); touch = null;
          cards.forEach(card => { stackProperties.forEach(key => card.style.removeProperty(key)); card.style.zIndex = ''; });
        };
        configure();
      }
      onDestroy() {
        this.controller?.abort(); this.observer?.disconnect(); this.cleanup?.(); super.onDestroy();
      }
    }
    elementorFrontend.hooks.addAction('frontend/element_ready/jednotka-values.default', $element => {
      elementorFrontend.elementsHandler.addHandler(ValuesHandler, { $element });
    });
  }
  jQuery(window).on('elementor/frontend/init', register);
  register();
})();
