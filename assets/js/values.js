/* global elementorFrontend, elementorModules, jQuery */
(() => {
  'use strict';
  let registered = false;
  let lockOwner = null;
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
        let locked = false, released = false, busyUntil = 0, savedScroll = 0, savedBody = null;
        let previousTop = section.getBoundingClientRect().top;
        let viewportWidth = window.innerWidth;
        const bodyProperties = ['position', 'top', 'left', 'right', 'width', 'overflow'];
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
        const countedView = () => compact.matches && !staticView();
        const unlock = () => {
          if (!locked) return;
          locked = false; released = true; lockOwner = null;
          savedBody.forEach(({ key, value, priority }) => {
            if (value) document.body.style.setProperty(key, value, priority);
            else document.body.style.removeProperty(key);
          });
          savedBody = null;
          section.removeAttribute('data-scroll-locked');
          window.scrollTo({ top: savedScroll, behavior: 'instant' });
          previousTop = section.getBoundingClientRect().top;
        };
        const lock = fromBelow => {
          if (locked || lockOwner || !countedView() || document.hidden) return;
          const top = section.getBoundingClientRect().top;
          savedScroll = window.scrollY + top;
          savedBody = bodyProperties.map(key => ({ key, value: document.body.style.getPropertyValue(key), priority: document.body.style.getPropertyPriority(key) }));
          section.style.setProperty('--values-frame-height', `${window.innerHeight}px`);
          cardIndex = fromBelow ? cards.length - 1 : 0;
          locked = true; lockOwner = section; released = false;
          // The gesture that enters the section never also changes a card.
          if (touch) touch.consumed = true;
          wheelConsumed = true;
          window.scrollTo({ top: savedScroll, behavior: 'instant' });
          document.body.style.setProperty('position', 'fixed');
          document.body.style.setProperty('top', `${-savedScroll}px`);
          document.body.style.setProperty('left', '0');
          document.body.style.setProperty('right', '0');
          document.body.style.setProperty('width', '100%');
          document.body.style.setProperty('overflow', 'hidden');
          section.setAttribute('data-scroll-locked', 'yes');
          update();
        };
        const syncLock = () => {
          if (!countedView() || locked || document.hidden) return;
          const rect = section.getBoundingClientRect();
          const top = rect.top;
          if (released) {
            // Do not capture the same section again while the user is leaving it.
            if (Math.abs(top) > window.innerHeight * .5) released = false;
          } else if (Math.abs(top) < window.innerHeight * .5 &&
              ((previousTop > 0 && top <= 0) || (previousTop < 0 && top >= 0) || Math.abs(top) <= 1)) {
            lock(previousTop < 0);
          }
          previousTop = top;
        };
        const canStep = direction => direction > 0 ? cardIndex < cards.length - 1 : cardIndex > 0;
        const step = (direction, time) => {
          cardIndex = clamp(cardIndex + direction, 0, cards.length - 1);
          busyUntil = time + 450;
          update();
        };
        const block = event => { if (event.cancelable) event.preventDefault(); };
        window.addEventListener('touchstart', event => {
          touch = event.touches.length === 1
            ? { x: event.touches[0].clientX, y: event.touches[0].clientY, consumed: event.timeStamp < busyUntil } : null;
        }, { passive: true, signal });
        window.addEventListener('touchmove', event => {
          if (!touch || event.touches.length !== 1) { touch = null; return; }
          if (!locked) return;
          if (touch.consumed || event.timeStamp < busyUntil) { touch.consumed = true; block(event); return; }
          const dx = event.touches[0].clientX - touch.x;
          const dy = touch.y - event.touches[0].clientY;
          // Horizontal movement never unlocks vertical page scrolling.
          if (Math.abs(dx) > Math.abs(dy) || !dy) { block(event); return; }
          const direction = Math.sign(dy);
          if (!canStep(direction)) { unlock(); touch = null; return; }
          block(event);
          if (Math.abs(dy) >= 24) { touch.consumed = true; step(direction, event.timeStamp); }
        }, { passive: false, signal });
        const endTouch = () => { touch = null; };
        window.addEventListener('touchend', endTouch, { passive: true, signal });
        window.addEventListener('touchcancel', endTouch, { passive: true, signal });
        window.addEventListener('wheel', event => {
          if (!countedView() || event.ctrlKey) return;
          const fresh = event.timeStamp - wheelTime > 200;
          if (fresh) { wheelConsumed = false; wheelTotal = 0; }
          wheelTime = event.timeStamp;
          if (!locked) return;
          if (wheelConsumed || event.timeStamp < busyUntil) { wheelConsumed = true; block(event); return; }
          if (Math.abs(event.deltaX) > Math.abs(event.deltaY) || !event.deltaY) { block(event); return; }
          const direction = Math.sign(event.deltaY);
          if (!canStep(direction)) { unlock(); return; }
          block(event);
          const unit = event.deltaMode === 1 ? 16 : event.deltaMode === 2 ? window.innerHeight : 1;
          wheelTotal += event.deltaY * unit;
          if (Math.abs(wheelTotal) >= 40) { wheelConsumed = true; step(Math.sign(wheelTotal), event.timeStamp); }
        }, { passive: false, signal });
        window.addEventListener('keydown', event => {
          if (!locked) return;
          if (event.key === 'Escape' || event.key === 'Tab') { unlock(); return; }
          if (event.target?.closest?.('input, textarea, select, [contenteditable="true"]')) return;
          const direction = ['ArrowDown', 'PageDown', ' '].includes(event.key) ? (event.shiftKey ? -1 : 1)
            : ['ArrowUp', 'PageUp'].includes(event.key) ? -1 : 0;
          if (!direction) return;
          if (event.repeat || event.timeStamp < busyUntil) { block(event); return; }
          if (!canStep(direction)) { unlock(); return; }
          block(event); step(direction, event.timeStamp);
        }, { signal });
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
          unlock(); cancelScroll(); resetPointer();
          released = false; busyUntil = 0;
          touch = null; wheelConsumed = false; wheelTotal = 0; wheelTime = -Infinity;
          section.setAttribute('data-static', staticView() ? 'yes' : 'no');
          section.setAttribute('data-counted', compact.matches && !staticView() ? 'yes' : 'no');
          section.style.setProperty('--count', cards.length);
          cards.forEach(card => { stackProperties.forEach(key => card.style.removeProperty(key)); card.style.zIndex = ''; });
          previousTop = section.getBoundingClientRect().top;
          syncLock(); update();
        };
        window.addEventListener('scroll', () => { syncLock(); requestUpdate(); }, { passive:true, signal });
        window.addEventListener('resize', () => {
          if (window.innerWidth !== viewportWidth) {
            viewportWidth = window.innerWidth; configure();
          } else requestUpdate();
        }, { signal });
        reduced.addEventListener('change', configure, { signal });
        compact.addEventListener('change', configure, { signal });
        fine.addEventListener('change', resetPointer, { signal });
        document.addEventListener('visibilitychange', () => {
          if (document.hidden) { unlock(); touch = null; cancelScroll(); resetPointer(); } else requestUpdate();
        }, { signal });
        if (window.IntersectionObserver) {
          this.observer = new window.IntersectionObserver(entries => {
            visible = entries.some(entry => entry.isIntersecting);
            if (visible) requestUpdate(); else { cancelScroll(); resetPointer(); }
          });
          this.observer.observe(section);
        }
        this.cleanup = () => {
          unlock(); cancelScroll(); resetPointer(); section.setAttribute('data-static','yes');
          section.removeAttribute('data-counted'); section.style.removeProperty('--values-frame-height'); touch = null;
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
