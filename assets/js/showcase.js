/* global elementorFrontend, elementorModules, jQuery */
(() => {
  'use strict';
  let registered = false;
  function register() {
    if (registered || typeof window.elementorFrontend?.hooks?.addAction !== 'function' ||
        typeof window.elementorFrontend?.elementsHandler?.addHandler !== 'function' ||
        !window.elementorModules?.frontend?.handlers?.Base) return;
    registered = true;

    class ShowcaseHandler extends elementorModules.frontend.handlers.Base {
      onInit() {
        super.onInit();
        const root = this.$element[0].querySelector('.jn-showcase');
        if (!root) return;
        const split = root.querySelector('.jn-showcase__split');
        const compare = root.querySelector('.jn-showcase__compare');
        const slider = root.querySelector('.jn-showcase__slider');
        const range = root.querySelector('.jn-showcase__range');
        if (!split) return;
        this.scrollFrame = null;
        if (!compare || !slider || !range) {
          // The gallery still needs its measured height when comparison is disabled.
          this.controller = new AbortController();
          const measure = () => split.style.setProperty('--split-h', `${split.offsetHeight}px`);
          if (window.ResizeObserver) {
            this.resizeObserver = new window.ResizeObserver(measure);
            this.resizeObserver.observe(split);
          } else {
            window.addEventListener('resize', measure, { signal: this.controller.signal });
          }
          this.reset = () => split.style.removeProperty('--split-h');
          measure();
          return;
        }
        this.controller = new AbortController();
        const { signal } = this.controller;
        const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');
        const editing = elementorFrontend.isEditMode();
        const configured = Number(root.dataset.position);
        const target = Number.isFinite(configured) ? Math.min(100, Math.max(0, configured)) : 65;
        let current = target;
        let pointerId = null;
        let interacted = false;
        this.introFrame = null;
        this.scrollFrame = null;
        this.snapTimer = null;
        let snapped = false;
        const cancelSnap = () => {
          if (this.snapTimer !== null) window.clearTimeout(this.snapTimer);
          this.snapTimer = null;
        };
        const requestSnap = () => {
          cancelSnap();
          const viewport = window.innerHeight;
          const top = compare.getBoundingClientRect().top;
          if (Math.abs(top) > viewport * 0.65) snapped = false;
          if (editing || reduced.matches || document.hidden || root.dataset.snap !== 'yes' ||
              snapped || pointerId !== null || compare.contains?.(document.activeElement) ||
              Math.abs(top) < 2 || Math.abs(top) > viewport * 0.22) return;
          // Settle after the user's scroll gesture, then snap once per approach.
          this.snapTimer = window.setTimeout(() => {
            this.snapTimer = null;
            if (reduced.matches || document.hidden || pointerId !== null) return;
            const remaining = compare.getBoundingClientRect().top;
            if (Math.abs(remaining) > window.innerHeight * 0.22) return;
            snapped = true;
            window.scrollTo({ top: window.scrollY + remaining, behavior: 'smooth' });
          }, 200);
        };

        const setPosition = percent => {
          current = Math.min(100, Math.max(0, percent));
          slider.style.setProperty('--cx', `${current}%`);
          slider.setAttribute('data-edge', current <= 0 ? 'left' : current >= 100 ? 'right' : 'none');
          range.value = String(current);
          const before = root.querySelector('.jn-showcase__label--before')?.textContent.trim() || 'Before';
          const after = root.querySelector('.jn-showcase__label--after')?.textContent.trim() || 'After';
          range.setAttribute('aria-valuetext', `${before} ${Math.round(current)}%, ${after} ${Math.round(100 - current)}%`);

        };
        const cancelIntro = () => {
          if (this.introFrame !== null) window.cancelAnimationFrame(this.introFrame);
          this.introFrame = null;
        };
        const stopAutomaticMotion = () => {
          cancelIntro();
          this.introObserver?.disconnect();
          setPosition(interacted ? current : target);
        };
        const moveTo = clientX => {
          const rect = slider.getBoundingClientRect();
          const width = slider.offsetWidth;
          if (!width) return;
          const left = rect.left + (rect.width - width) / 2;
          setPosition(((clientX - left) / width) * 100);
        };
        const beginInteraction = () => {
          cancelSnap();
          interacted = true;
          cancelIntro();
          this.introObserver?.disconnect();
        };
        slider.addEventListener('pointerdown', event => {
          if (event.isPrimary === false || (event.button !== undefined && event.button !== 0)) return;
          if (editing && event.target.closest('[contenteditable="true"]')) return;
          beginInteraction();
          pointerId = event.pointerId;
          range.focus({ preventScroll: true });
          slider.setPointerCapture(pointerId);
          event.preventDefault();
          moveTo(event.clientX);
        }, { signal });
        slider.addEventListener('pointermove', event => {
          if (pointerId === event.pointerId) moveTo(event.clientX);
        }, { signal });
        const stopDragging = event => {
          if (pointerId !== event.pointerId) return;
          if (slider.hasPointerCapture(pointerId)) slider.releasePointerCapture(pointerId);
          pointerId = null;
        };
        slider.addEventListener('pointerup', stopDragging, { signal });
        slider.addEventListener('pointercancel', stopDragging, { signal });
        slider.addEventListener('lostpointercapture', () => { pointerId = null; }, { signal });
        range.addEventListener('input', () => { beginInteraction(); setPosition(Number(range.value)); }, { signal });
        range.addEventListener('focus', beginInteraction, { signal });
        range.addEventListener('keydown', event => {
          const values = { ArrowLeft: current - 5, ArrowDown: current - 5, ArrowRight: current + 5, ArrowUp: current + 5, Home: 0, End: 100 };
          if (!(event.key in values)) return;
          event.preventDefault();
          beginInteraction();
          setPosition(values[event.key]);
        }, { signal });

        const updateLayout = () => {
          split.style.setProperty('--split-h', `${split.offsetHeight}px`);
          setPosition(current);
        };
        const updateCorners = () => {
          compare.classList.toggle('is-full', compare.getBoundingClientRect().top <= 2);
        };
        const onScroll = () => {
          if (this.scrollFrame !== null) return;
          this.scrollFrame = window.requestAnimationFrame(() => { this.scrollFrame = null; updateCorners(); requestSnap(); });
        };
        if (window.ResizeObserver) {
          this.resizeObserver = new window.ResizeObserver(updateLayout);
          this.resizeObserver.observe(split);
          this.resizeObserver.observe(slider);
        } else {
          window.addEventListener('resize', updateLayout, { signal });
        }
        window.addEventListener('scroll', onScroll, { passive: true, signal });
        window.addEventListener('resize', onScroll, { signal });
        window.addEventListener('wheel', cancelSnap, { passive: true, signal });
        window.addEventListener('touchstart', cancelSnap, { passive: true, signal });
        window.addEventListener('keydown', cancelSnap, { signal });
        reduced.addEventListener('change', () => { if (reduced.matches) { cancelSnap(); stopAutomaticMotion(); } else setPosition(current); }, { signal });
        document.addEventListener('visibilitychange', () => { if (document.hidden) { cancelSnap(); stopAutomaticMotion(); } }, { signal });
        updateLayout();
        updateCorners();

        if (!editing && !reduced.matches && root.dataset.animate === 'yes' && window.IntersectionObserver && target !== 100) {
          setPosition(100);
          this.introObserver = new window.IntersectionObserver(entries => {
            if (!entries.some(entry => entry.isIntersecting) || interacted) return;
            this.introObserver.disconnect();
            let start = null;
            const tick = time => {
              if (start === null) start = time;
              const progress = Math.min(1, (time - start) / 1400);
              setPosition(100 + (target - 100) * (1 - Math.pow(1 - progress, 3)));
              this.introFrame = progress < 1 ? window.requestAnimationFrame(tick) : null;
            };
            this.introFrame = window.requestAnimationFrame(tick);
          }, { threshold: 0.6 });
          this.introObserver.observe(slider);
        }
        this.reset = () => {
          cancelSnap();
          stopAutomaticMotion();
          if (pointerId !== null && slider.hasPointerCapture(pointerId)) slider.releasePointerCapture(pointerId);
          pointerId = null;
          split.style.removeProperty('--split-h');
        };
      }
      onDestroy() {
        this.controller?.abort();
        this.resizeObserver?.disconnect();
        if (this.scrollFrame !== null) window.cancelAnimationFrame(this.scrollFrame);
        this.reset?.();
        super.onDestroy();
      }
    }
    elementorFrontend.hooks.addAction('frontend/element_ready/jednotka-showcase.default', $element => {
      elementorFrontend.elementsHandler.addHandler(ShowcaseHandler, { $element });
    });
  }
  jQuery(window).on('elementor/frontend/init', register);
  register();
})();
