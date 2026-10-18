/* global elementorFrontend, elementorModules, jQuery */
(() => {
  'use strict';
  let registered = false;
  function register() {
    if (registered || typeof window.elementorFrontend?.hooks?.addAction !== 'function' ||
        typeof window.elementorFrontend?.elementsHandler?.addHandler !== 'function' ||
        !window.elementorModules?.frontend?.handlers?.Base) return;
    registered = true;
    class ServicesHeroHandler extends elementorModules.frontend.handlers.Base {
      onInit() {
        super.onInit();
        const section = this.$element[0].querySelector('.jn-services-hero');
        if (!section) return;
        const hero = section.querySelector('.services-hero');
        const track = section.querySelector('.services-cases');
        const badges = [...section.querySelectorAll('.hero-badge')];
        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
        const editing = elementorFrontend.isEditMode();
        this.controller = new AbortController(); this.frame = null;
        const { signal } = this.controller;
        const speed = Math.max(5, Math.min(200, Number(section.dataset.speed) || 42));
        let visible = true, focused = false, lastTime = null, pauseUntil = 0, direction = 1;
        let position = track?.scrollLeft || 0, lastKnown = position;
        let pointer = null, startX = 0, startScroll = 0, dragged = false, suppressClick = false;
        const maxScroll = () => track ? Math.max(0, track.scrollWidth - track.clientWidth) : 0;
        const canFloat = () => !editing && !reduced.matches && section.dataset.floating === 'yes' && badges.length > 0;
        const canScroll = () => track && !editing && !reduced.matches && !focused && pointer === null && section.dataset.auto === 'yes' && maxScroll() > 0;
        const resetPointer = () => {
          hero.style.setProperty('--hero-badge-x', '0px'); hero.style.setProperty('--hero-badge-y', '0px');
        };
        const resetFloat = () => badges.forEach(badge => {
          badge.style.setProperty('--hero-badge-float-x', '0px'); badge.style.setProperty('--hero-badge-float-y', '0px');
        });
        const stop = () => {
          if (this.frame !== null) window.cancelAnimationFrame(this.frame);
          this.frame = null; lastTime = null;
        };
        const tick = time => {
          this.frame = null;
          const dt = lastTime === null ? 0 : Math.min((time - lastTime) / 1000, .05);
          lastTime = time;
          if (canFloat()) badges.forEach((badge, index) => {
            const phase = index * 1.74;
            badge.style.setProperty('--hero-badge-float-x', `${(Math.sin(time * .00065 + phase) * 2.4).toFixed(2)}px`);
            badge.style.setProperty('--hero-badge-float-y', `${(Math.cos(time * .00078 + phase) * 3.4).toFixed(2)}px`);
          });
          if (canScroll() && time >= pauseUntil) {
            // Keep fractional progress ourselves: browsers may round scrollLeft on readback.
            position += direction * speed * dt;
            const max = maxScroll();
            if (position >= max) { position = max; direction = -1; }
            if (position <= 0) { position = 0; direction = 1; }
            track.scrollLeft = position; lastKnown = track.scrollLeft;
          }
          if (visible && !document.hidden && (canFloat() || canScroll())) this.frame = window.requestAnimationFrame(tick);
          else lastTime = null;
        };
        const sync = () => {
          stop();
          if (visible && !document.hidden && (canFloat() || canScroll())) this.frame = window.requestAnimationFrame(tick);
        };
        hero.addEventListener('pointermove', event => {
          if (editing || reduced.matches || document.hidden || !visible || event.pointerType === 'touch' || section.dataset.parallax !== 'yes') return;
          const rect = hero.getBoundingClientRect();
          if (!rect.width || !rect.height) return;
          hero.style.setProperty('--hero-badge-x', `${(((event.clientX - rect.left) / rect.width - .5) * 22).toFixed(2)}px`);
          hero.style.setProperty('--hero-badge-y', `${(((event.clientY - rect.top) / rect.height - .5) * 18).toFixed(2)}px`);
        }, { signal });
        hero.addEventListener('pointerleave', resetPointer, { signal });
        if (track) {
          track.addEventListener('pointerdown', event => {
            if (editing || event.isPrimary === false || (event.button !== undefined && event.button !== 0)) return;
            pointer = event.pointerId; startX = event.clientX; startScroll = track.scrollLeft;
            dragged = false; suppressClick = false; sync();
          }, { signal });
          track.addEventListener('pointermove', event => {
            if (pointer === null || pointer !== event.pointerId) return;
            const distance = event.clientX - startX;
            if (!dragged && Math.abs(distance) <= 4) return;
            if (!dragged) { dragged = true; track.classList.add('is-dragging'); track.setPointerCapture(pointer); }
            track.scrollLeft = Math.max(0, Math.min(maxScroll(), startScroll - distance));
            position = track.scrollLeft; lastKnown = position;
          }, { signal });
          const end = event => {
            if (pointer === null || pointer !== event.pointerId) return;
            const id = pointer; pointer = null;
            suppressClick = dragged && event.type !== 'pointercancel';
            pauseUntil = window.performance.now() + 1400;
            position = track.scrollLeft; lastKnown = position;
            track.classList.remove('is-dragging');
            if (track.hasPointerCapture(id)) track.releasePointerCapture(id);
            sync();
          };
          ['pointerup', 'pointercancel', 'lostpointercapture'].forEach(type => track.addEventListener(type, end, { signal }));
          track.addEventListener('pointerleave', event => { if (!dragged) end(event); }, { signal });
          track.addEventListener('click', event => {
            if (editing) { event.preventDefault(); return; }
            if (suppressClick && event.detail !== 0) { event.preventDefault(); event.stopPropagation(); }
            suppressClick = false;
          }, { capture: true, signal });
          track.addEventListener('dragstart', event => event.preventDefault(), { signal });
          track.addEventListener('scroll', () => {
            const current = track.scrollLeft;
            if (Math.abs(current - lastKnown) > 6) { pauseUntil = window.performance.now() + 1400; position = current; }
            lastKnown = current;
          }, { passive: true, signal });
          track.addEventListener('keydown', event => {
            if (editing) return;
            const step = track.clientWidth * .8;
            const destinations = { ArrowRight: track.scrollLeft + step, ArrowLeft: track.scrollLeft - step, Home: 0, End: maxScroll() };
            if (!(event.key in destinations)) return;
            event.preventDefault(); position = Math.max(0, Math.min(maxScroll(), destinations[event.key]));
            track.scrollLeft = position; lastKnown = track.scrollLeft; pauseUntil = window.performance.now() + 1400;
          }, { signal });
          track.addEventListener('focusin', () => { focused = true; sync(); }, { signal });
          track.addEventListener('focusout', event => { focused = track.contains(event.relatedTarget); position = track.scrollLeft; lastKnown = position; sync(); }, { signal });
        }
        reduced.addEventListener('change', () => { resetPointer(); resetFloat(); sync(); }, { signal });
        document.addEventListener('visibilitychange', sync, { signal });
        const resize = () => { position = Math.max(0, Math.min(maxScroll(), track?.scrollLeft || 0)); lastKnown = position; sync(); };
        if (track && window.ResizeObserver) {
          this.resizeObserver = new window.ResizeObserver(resize); this.resizeObserver.observe(track);
          [...track.children].forEach(card => this.resizeObserver.observe(card));
        } else window.addEventListener('resize', resize, { signal });
        if (window.IntersectionObserver) {
          this.observer = new window.IntersectionObserver(entries => { visible = entries.some(entry => entry.isIntersecting); sync(); });
          this.observer.observe(section);
        }
        this.cleanup = () => {
          stop(); resetPointer(); resetFloat();
          if (track) {
            const id = pointer; pointer = null;
            if (id !== null && track.hasPointerCapture(id)) track.releasePointerCapture(id);
            track.classList.remove('is-dragging');
          }
        };
        sync();
      }
      onDestroy() { this.controller?.abort(); this.resizeObserver?.disconnect(); this.observer?.disconnect(); this.cleanup?.(); super.onDestroy(); }
    }
    elementorFrontend.hooks.addAction('frontend/element_ready/jednotka-services-hero.default', $element => {
      elementorFrontend.elementsHandler.addHandler(ServicesHeroHandler, { $element });
    });
  }
  jQuery(window).on('elementor/frontend/init', register);
  register();
})();
