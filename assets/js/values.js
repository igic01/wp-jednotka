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
        const sticky = section.querySelector('.values__sticky');
        if (!sticky) return;
        const cards = [...section.querySelectorAll('.values__card')];
        this.controller = new AbortController(); this.scrollFrame = null; this.tiltFrame = null;
        const { signal } = this.controller;
        const editing = elementorFrontend.isEditMode();
        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
        const fine = window.matchMedia('(hover: hover) and (pointer: fine)');
        const clamp = (value, min = 0, max = 1) => Math.max(min, Math.min(max, value));
        const ease = value => 1 - Math.pow(1 - value, 3);
        const poses = [{x:-8,y:-6,rot:-6},{x:7,y:5,rot:5},{x:-5,y:8,rot:-3},{x:9,y:-4,rot:6},{x:-7,y:3,rot:-5},{x:6,y:-7,rot:4}];
        const stackProperties = ['--pose-x','--pose-y','--pose-rot','--enter','--settle','--fall','--dir','--behind','--drift'];
        const pointerProperties = ['--rx','--ry','--px','--py','--lift','--ox','--oy'];
        const states = new Map(), active = new Map();
        let visible = true, frameHeight = 0;
        const measureFrame = () => {
          const height = sticky.offsetHeight;
          if (height === frameHeight) return;
          frameHeight = height;
          section.style.setProperty('--values-frame-height', `${height}px`);
        };
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
          const progress = clamp(-rect.top / Math.max(1, rect.height - frameHeight));
          const position = clamp(progress / .92) * (cards.length - 1);
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
          section.setAttribute('data-static', staticView() ? 'yes' : 'no');
          section.style.setProperty('--count', cards.length);
          measureFrame();
          cards.forEach(card => { stackProperties.forEach(key => card.style.removeProperty(key)); card.style.zIndex = ''; });
          update();
        };
        window.addEventListener('scroll', requestUpdate, { passive:true, signal });
        window.addEventListener('resize', () => { measureFrame(); requestUpdate(); }, { signal });
        reduced.addEventListener('change', configure, { signal });
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
        if (window.ResizeObserver) {
          this.frameObserver = new window.ResizeObserver(() => { measureFrame(); requestUpdate(); });
          this.frameObserver.observe(sticky);
        }
        this.cleanup = () => {
          cancelScroll(); resetPointer(); section.setAttribute('data-static','yes');
          section.style.removeProperty('--values-frame-height');
          cards.forEach(card => { stackProperties.forEach(key => card.style.removeProperty(key)); card.style.zIndex = ''; });
        };
        configure();
      }
      onDestroy() {
        this.controller?.abort(); this.observer?.disconnect(); this.frameObserver?.disconnect(); this.cleanup?.(); super.onDestroy();
      }
    }
    elementorFrontend.hooks.addAction('frontend/element_ready/jednotka-values.default', $element => {
      elementorFrontend.elementsHandler.addHandler(ValuesHandler, { $element });
    });
  }
  jQuery(window).on('elementor/frontend/init', register);
  register();
})();
