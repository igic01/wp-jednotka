/* global elementorFrontend, elementorModules, jQuery */
(() => {
  'use strict';
  let registered = false;
  function register() {
    if (registered || typeof window.elementorFrontend?.hooks?.addAction !== 'function' ||
        typeof window.elementorFrontend?.elementsHandler?.addHandler !== 'function' ||
        !window.elementorModules?.frontend?.handlers?.Base) return;
    registered = true;
    class TeamHandler extends elementorModules.frontend.handlers.Base {
      onInit() {
        super.onInit();
        const section = this.$element[0].querySelector('.jn-team');
        const stage = section?.querySelector('.team__stage');
        const track = section?.querySelector('.team__track');
        const original = track?.firstElementChild;
        const template = track?.children[1];
        if (!stage || !original || !template) return;
        this.controller = new AbortController();
        this.frame = null;
        const { signal } = this.controller;
        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
        const editing = elementorFrontend.isEditMode();
        const speed = Math.min(200, Math.max(5, Number(section.dataset.speed) || 45));
        let loopWidth = 0, x = 0, velocity = 0, pointerId = null;
        let lastPointerX = 0, lastPointerTime = 0, lastTime = null;
        let visible = !window.IntersectionObserver;
        let focused = false;
        const automatic = () => section.dataset.auto === 'yes' && !editing && !reduced.matches && !focused;
        const render = () => {
          x = loopWidth > 0 ? ((x % loopWidth) - loopWidth) % loopWidth : 0;
          track.style.transform = `translate3d(${x.toFixed(1)}px, 0, 0)`;
        };
        const stop = () => {
          if (this.frame !== null) window.cancelAnimationFrame(this.frame);
          this.frame = null; lastTime = null;
        };
        const tick = time => {
          this.frame = null;
          const dt = lastTime === null ? 0 : Math.min((time - lastTime) / 1000, .05);
          lastTime = time;
          x += velocity * dt;
          velocity *= Math.pow(.94, dt * 60);
          if (automatic()) x -= speed * dt;
          render();
          if (visible && !document.hidden && pointerId === null && loopWidth > 0 &&
              (automatic() || Math.abs(velocity) > 1)) this.frame = window.requestAnimationFrame(tick);
          else lastTime = null;
        };
        const sync = () => {
          stop();
          if (!visible || document.hidden || pointerId !== null || !loopWidth) return;
          if (automatic() || (!reduced.matches && Math.abs(velocity) > 1)) this.frame = window.requestAnimationFrame(tick);
        };
        const measure = () => {
          loopWidth = original.offsetWidth;
          if (loopWidth > 0) {
            // Enough repeated sets to cover the viewport even with one member.
            const count = Math.max(2, Math.ceil(stage.clientWidth / loopWidth) + 1);
            while (track.children.length > count) track.lastElementChild.remove();
            while (track.children.length < count) track.appendChild(template.cloneNode(true));
          }
          render(); sync();
        };
        stage.addEventListener('pointerdown', event => {
          if (event.isPrimary === false || (event.button !== undefined && event.button !== 0)) return;
          if (editing && event.target.closest('[contenteditable="true"]')) return;
          pointerId = event.pointerId; velocity = 0;
          lastPointerX = event.clientX; lastPointerTime = event.timeStamp;
          stage.classList.add('is-dragging'); stage.setPointerCapture(pointerId);
          if (event.pointerType === 'mouse') event.preventDefault();
          stop();
        }, { signal });
        stage.addEventListener('pointermove', event => {
          if (pointerId !== event.pointerId) return;
          const dx = event.clientX - lastPointerX;
          const dt = Math.max(8, event.timeStamp - lastPointerTime) / 1000;
          lastPointerX = event.clientX; lastPointerTime = event.timeStamp;
          x += dx;
          velocity = reduced.matches ? 0 : Math.max(-1800, Math.min(1800, dx / dt));
          render();
        }, { signal });
        const endDrag = event => {
          if (pointerId !== event.pointerId) return;
          const id = pointerId; pointerId = null;
          if (event.type === 'pointercancel') velocity = 0;
          if (stage.hasPointerCapture(id)) stage.releasePointerCapture(id);
          stage.classList.remove('is-dragging'); sync();
        };
        stage.addEventListener('pointerup', endDrag, { signal });
        stage.addEventListener('pointercancel', endDrag, { signal });
        stage.addEventListener('lostpointercapture', () => {
          if (pointerId === null) return;
          pointerId = null; velocity = 0; stage.classList.remove('is-dragging'); sync();
        }, { signal });
        stage.addEventListener('keydown', event => {
          const step = loopWidth / Math.max(1, original.children.length);
          const actions = { ArrowRight: x - step, ArrowLeft: x + step, Home: 0, End: -loopWidth + step };
          if (!(event.key in actions)) return;
          event.preventDefault(); velocity = 0; x = actions[event.key]; render();
        }, { signal });
        stage.addEventListener('focusin', () => { focused = true; velocity = 0; sync(); }, { signal });
        stage.addEventListener('focusout', () => { focused = false; sync(); }, { signal });
        reduced.addEventListener('change', () => { if (reduced.matches) velocity = 0; sync(); }, { signal });
        document.addEventListener('visibilitychange', sync, { signal });
        if (window.ResizeObserver) {
          this.resizeObserver = new window.ResizeObserver(measure);
          this.resizeObserver.observe(stage); this.resizeObserver.observe(original);
        } else window.addEventListener('resize', measure, { signal });
        if (window.IntersectionObserver) {
          this.visibilityObserver = new window.IntersectionObserver(entries => {
            visible = entries.some(entry => entry.isIntersecting); sync();
          });
          this.visibilityObserver.observe(section);
        }
        measure();
        this.cleanup = () => {
          stop(); velocity = 0;
          if (pointerId !== null && stage.hasPointerCapture(pointerId)) stage.releasePointerCapture(pointerId);
          pointerId = null; stage.classList.remove('is-dragging');
          track.style.transform = '';
        };
      }
      onDestroy() {
        this.controller?.abort();
        this.resizeObserver?.disconnect(); this.visibilityObserver?.disconnect();
        this.cleanup?.();
        super.onDestroy();
      }
    }
    elementorFrontend.hooks.addAction('frontend/element_ready/jednotka-team.default', $element => {
      elementorFrontend.elementsHandler.addHandler(TeamHandler, { $element });
    });
  }
  jQuery(window).on('elementor/frontend/init', register);
  register();
})();
