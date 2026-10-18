/* global elementorFrontend, elementorModules, jQuery */
(() => {
  'use strict';
  let registered = false;
  function register() {
    if (registered) return;
    // Wait for the initialized APIs, rather than only the global objects.
    if (typeof window.elementorFrontend?.hooks?.addAction !== 'function' ||
        typeof window.elementorFrontend?.elementsHandler?.addHandler !== 'function' ||
        !window.elementorModules?.frontend?.handlers?.Base) return;
    registered = true;
    class HeroHandler extends elementorModules.frontend.handlers.Base {
      onInit() {
        super.onInit();
        this.controller = new AbortController();
        const { signal } = this.controller;
        const hero = this.$element[0].querySelector('.jn-hero');
        if (!hero) return;
        const frame = hero.querySelector('.jn-hero__frame');
        // The embedded header manages its own logo video and pause control.
        this.videos = [...hero.querySelectorAll('video')].filter(video => !video.closest('.jn-header'));
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)');
        const editing = elementorFrontend.isEditMode();
        hero.classList.toggle('is-editing', editing);
        const hasMotion = hero.dataset.float === 'yes' || this.videos.length > 0;
        const badges = [...frame.querySelectorAll(".hero-badge")];
        this.floatFrame = null;
        const updateHeroBadges = (event) => {
          const rect = frame.getBoundingClientRect();
          if (!rect.width || !rect.height) return;
          const x = ((event.clientX - rect.left) / rect.width - 0.5) * 22;
          const y = ((event.clientY - rect.top) / rect.height - 0.5) * 18;
      
          frame.style.setProperty("--hero-badge-x", `${x.toFixed(2)}px`);
          frame.style.setProperty("--hero-badge-y", `${y.toFixed(2)}px`);
        };
      
        const resetHeroBadges = () => {
          frame.style.setProperty("--hero-badge-x", "0px");
          frame.style.setProperty("--hero-badge-y", "0px");
        };
      
        const resetHeroBadgeFloat = () => {
          badges.forEach((badge) => {
            badge.style.setProperty("--hero-badge-float-x", "0px");
            badge.style.setProperty("--hero-badge-float-y", "0px");
          });
        };
      
        const animateHeroBadgeFloat = (time) => {
          if (reduce.matches || !hero.classList.contains('is-playing') || hero.dataset.float !== 'yes' || hero.classList.contains('is-text-editing')) {
            resetHeroBadgeFloat();
            this.floatFrame = null;
            return;
          }
      
          badges.forEach((badge, index) => {
            const phase = index * 1.74;
            const driftX = Math.sin(time * 0.00065 + phase) * 2.4;
            const driftY = Math.cos(time * 0.00078 + phase) * 3.4;
      
            badge.style.setProperty("--hero-badge-float-x", `${driftX.toFixed(2)}px`);
            badge.style.setProperty("--hero-badge-float-y", `${driftY.toFixed(2)}px`);
          });
      
          this.floatFrame = window.requestAnimationFrame(animateHeroBadgeFloat);
        };
      
        const syncHeroBadgeFloat = () => {
          if (this.floatFrame) {
            window.cancelAnimationFrame(this.floatFrame);
            this.floatFrame = null;
          }
      
          if (reduce.matches || !hero.classList.contains('is-playing') || hero.dataset.float !== 'yes' || hero.classList.contains('is-text-editing')) {
            resetHeroBadgeFloat();
            return;
          }
      
          this.floatFrame = window.requestAnimationFrame(animateHeroBadgeFloat);
        };
      
        this.stopFloat = () => {
          if (this.floatFrame !== null) window.cancelAnimationFrame(this.floatFrame);
          this.floatFrame = null;
        };
        const setPlaying = playing => {
          hero.classList.toggle('is-playing', playing);
          this.videos.forEach(video => {
            if (playing && !editing) video.play().catch(() => {});
            else video.pause();
          });
          if (!playing) resetHeroBadges();
          syncHeroBadgeFloat();
        };
        const syncPlaying = () => setPlaying(hasMotion && !reduce.matches && !document.hidden);
        reduce.addEventListener('change', syncPlaying, { signal });
        document.addEventListener('visibilitychange', syncPlaying, { signal });
        frame.addEventListener('pointermove', (event) => {
          if (editing || reduce.matches || !hero.classList.contains('is-playing') || hero.dataset.float !== 'yes' || event.pointerType === 'touch') return;
          updateHeroBadges(event);
        }, { signal });
        frame.addEventListener('pointerleave', resetHeroBadges, { signal });
        hero.addEventListener('input', (event) => {
          if (event.target.getAttribute('data-elementor-setting-key') === 'button_text') {
            hero.querySelector('.hero-copy .button-label-line[aria-hidden="true"]').textContent = event.target.textContent;
          }
        }, { signal });
        // Keep the badge animation visible in Elementor, pausing while typing.
        hero.addEventListener('focusin', (event) => {
          if (editing && event.target.closest('[contenteditable="true"]')) { hero.classList.add('is-text-editing'); syncHeroBadgeFloat(); }
        }, { signal });
        hero.addEventListener('focusout', () => { hero.classList.remove('is-text-editing'); syncHeroBadgeFloat(); }, { signal });
        syncPlaying();
      }
      onDestroy() {
        this.controller?.abort();
        this.stopFloat?.();
        this.videos?.forEach(video => video.pause());
        super.onDestroy();
      }
    }
    elementorFrontend.hooks.addAction('frontend/element_ready/jednotka-hero.default', ($element) => {
      elementorFrontend.elementsHandler.addHandler(HeroHandler, { $element });
    });
  }
  jQuery(window).on('elementor/frontend/init', register);
  register();
})();
