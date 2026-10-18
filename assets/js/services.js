/* global elementorFrontend, elementorModules, jQuery */
(() => {
  'use strict';
  let registered = false;
  const editorViews = new Map();
  function register() {
    if (registered) return;
    if (typeof window.elementorFrontend?.hooks?.addAction !== 'function' ||
        typeof window.elementorFrontend?.elementsHandler?.addHandler !== 'function' ||
        !window.elementorModules?.frontend?.handlers?.Base) return;
    registered = true;
    class ServicesHandler extends elementorModules.frontend.handlers.Base {
      onInit() {
        super.onInit();
        const section = this.$element[0].querySelector('.jn-services');
        if (!section) return;
        this.controller = new AbortController();
        this.frame = null;
        const { signal } = this.controller;
        const serviceRows = [...section.querySelectorAll('.service-row')];
        const servicesReduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
        const editing = elementorFrontend.isEditMode();
        // Original examples/js/services-reveal.js calculations.
        const clampServiceProgress = (value) => Math.min(Math.max(value, 0), 1);
        
        const setServiceProgress = (row, progress) => {
          const eased = 1 - Math.pow(1 - progress, 3);
          const blur = (1 - eased) * 18;
          const translateY = (1 - eased) * 58;
          const scale = 0.96 + eased * 0.04;
          const opacity = 0.22 + eased * 0.78;
          const arrow = row.querySelector(".service-arrow");
        
          row.style.opacity = opacity.toFixed(3);
          row.style.filter = `blur(${blur.toFixed(2)}px)`;
          row.style.transform = `translateY(${translateY.toFixed(2)}px) scale(${scale.toFixed(3)})`;
        
          if (arrow) {
            const arrowOffset = (1 - eased) * 18;
            arrow.style.setProperty("--arrow-scroll-x", `${arrowOffset.toFixed(2)}px`);
            arrow.style.setProperty("--arrow-scroll-y", `${arrowOffset.toFixed(2)}px`);
          }
        };
        
        const resetServiceRows = () => {
          serviceRows.forEach((row) => {
            row.style.opacity = "";
            row.style.filter = "";
            row.style.transform = "";
        
            const arrow = row.querySelector(".service-arrow");
        
            if (arrow) {
              arrow.style.removeProperty("--arrow-scroll-x");
              arrow.style.removeProperty("--arrow-scroll-y");
            }
          });
        };
        
        const updateServicesOnScroll = () => {
          if (editing || servicesReduceMotion.matches || section.dataset.animate !== 'yes') {
            resetServiceRows();
            return;
          }
        
          const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
          const start = viewportHeight * 0.92;
          const end = viewportHeight * 0.48;
        
          serviceRows.forEach((row, index) => {
            if (row.parentElement.contains(document.activeElement)) { setServiceProgress(row, 1); return; }
            const rect = row.getBoundingClientRect();
            const stagger = (index % 2) * 0.04;
            const progress = clampServiceProgress((start - rect.top) / (start - end) - stagger);
        
            setServiceProgress(row, progress);
          });
        };
        

        this.resetCards = resetServiceRows;
        const requestServicesUpdate = () => {
          if (this.frame !== null) return;
          this.frame = window.requestAnimationFrame(() => { this.frame = null; updateServicesOnScroll(); });
        };
        this.videos = [...section.querySelectorAll('video')];
        section.querySelectorAll('.jn-service-item').forEach(card => {
          const videos = [...card.querySelectorAll('video')];
          const button = card.querySelector('.jn-services__video-toggle');
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
          if (!editing && !servicesReduceMotion.matches) videos.forEach(video => video.play().catch(sync));
          sync();
        });
        section.addEventListener('click', event => {
          if (editing && event.target.closest('.service-row') && !event.target.closest('.elementor-inline-editing, [contenteditable="true"]')) event.preventDefault();
        }, { capture: true, signal });
        // Keyboard users can always see the service receiving focus.
        section.addEventListener('focusin', event => {
          const row = event.target.closest('.jn-service-item')?.querySelector('.service-row');
          if (row) setServiceProgress(row, 1);
        }, { signal });
        section.addEventListener('focusout', requestServicesUpdate, { signal });
        window.addEventListener('scroll', requestServicesUpdate, { passive: true, signal });
        window.addEventListener('resize', requestServicesUpdate, { signal });
        servicesReduceMotion.addEventListener('change', () => {
          if (servicesReduceMotion.matches) this.videos.forEach(video => video.pause());
          updateServicesOnScroll();
        }, { signal });
        updateServicesOnScroll();
        // Optional detail panes: also tolerate cached markup from the earlier list-only widget.
        const list = section.querySelector('[data-sv-list]');
        const panel = section.querySelector('[data-sv-panel]');
        const viewport = section.querySelector('.sv-viewport');
        const back = section.querySelector('[data-sv-close]');
        const details = [...section.querySelectorAll('[data-sv-detail]')];
        if (list && panel && viewport && back && details.length) {
          section.classList.add('is-enhanced');
          let active = null, opener = null, pausedVideos = [];
          const widget = this.$element[0];
          const widgetKey = widget.getAttribute?.('data-id') || widget;
          const previewSignature = `${section.dataset.editorPreview}:${section.dataset.previewIndex}`;
          const remember = detail => { if (editing) editorViews.set(widgetKey, { id: detail?.dataset.svDetail || null, index: details.indexOf(detail), signature: previewSignature }); };
          const editorLinks = [];
          if (editing) {
            section.classList.add('is-services-editor');
            section.querySelectorAll('.sv-wall').forEach(wall => wall.removeAttribute('aria-hidden'));
            section.querySelectorAll('.sv-cta[href]').forEach(link => { editorLinks.push({ link, href: link.getAttribute('href') }); link.removeAttribute('href'); });
            // Materialize displayed fallback text in the editor model before native inline editing reads it.
            const hydrateDefaults = () => {
              try {
                const container = window.parent?.elementor?.getContainer?.(widget.getAttribute?.('data-id'));
                const services = container?.settings?.get('services');
                details.forEach(detail => {
                  const index = Number(detail.dataset.serviceIndex);
                  const model = services?.at?.(index) || services?.models?.[index];
                  if (!model) return;
                  const defaults = { detail_heading: detail.dataset.defaultHeading, detail_bullets: detail.dataset.defaultBullets, detail_cta: detail.dataset.defaultCta };
                  Object.entries(defaults).forEach(([key, value]) => { if (!model.get(key) && value) model.set(key, value, { silent: true }); });
                });
              } catch { /* Elementor may still be constructing the model during the first preview render. */ }
            };
            hydrateDefaults();
            section.addEventListener('pointerdown', hydrateDefaults, { capture: true, signal });
          }
          let returnUrl = window.location.pathname + window.location.search;
          const first = document.querySelector('.jn-services[data-sv]') === section;
          const buttons = [...section.querySelectorAll(editing ? '[data-sv-open], [data-sv-editor-open]' : '[data-sv-open]')];
          const editService = (detail, field = null, media = false) => {
            if (!editing || !detail) return;
            try {
              const editor = window.parent?.elementor;
              const container = editor?.getContainer?.(widget.getAttribute?.('data-id'));
              if (!container?.model || !container?.view) return;
              window.parent.$e.run('panel/editor/open', { model: container.model, view: container.view });
              const current = editor.getPanelView().getCurrentPageView();
              const page = current.content?.currentView || current;
              page.activateSection('services_section'); page.activateTab('content');
              const repeater = page.getControlViewByName('services');
              const row = repeater.children.findByIndex(Number(detail.dataset.serviceIndex));
              if (!row) return;
              if (repeater.currentEditableChild !== row) repeater.editRow(row);
              if (!field) return;
              const control = row.getChildControlView(field);
              if (!control) return;
              control.applySavedValue?.();
              control.el?.scrollIntoView?.({ block: 'nearest' });
              if (media) {
                if (control.hasImages?.()) control.onGalleryThumbnailsClick?.();
                else control.onAddImagesClick?.();
              } else control.el?.querySelector?.('textarea')?.focus();
            } catch { /* The ordinary Services repeater remains available during editor startup. */ }
          };
          const syncHeight = () => {
            viewport.style.height = `${(active ? panel : list).offsetHeight}px`;
          };
          const reveal = (initial = false) => {
            if (editing || Math.abs(section.getBoundingClientRect().top) <= 8) return;
            section.scrollIntoView({ behavior: initial || servicesReduceMotion.matches ? 'instant' : 'smooth', block: 'start' });
          };
          const show = detail => {
            if (!active) {
              pausedVideos = this.videos.filter(video => !video.paused);
              pausedVideos.forEach(video => video.pause());
            }
            active = detail; remember(detail);
            details.forEach(item => { item.hidden = item !== detail; });
            // Measure the newly visible pane before starting the slide.
            panel.getBoundingClientRect();
            section.classList.add('is-detail');
            list.inert = true; panel.inert = false; panel.removeAttribute('aria-hidden');
            buttons.forEach(button => button.setAttribute('aria-expanded', String((button.dataset.svOpen || button.dataset.svEditorOpen) === detail.dataset.svDetail)));
            syncHeight();
          };
          const hide = (focus = false, resume = true) => {
            const previous = active;
            active = null; remember(null);
            section.classList.remove('is-detail'); list.inert = false;
            panel.inert = true; panel.setAttribute('aria-hidden', 'true');
            buttons.forEach(button => button.setAttribute('aria-expanded', 'false'));
            syncHeight();
            if (focus) (opener || buttons.find(button => (button.dataset.svOpen || button.dataset.svEditorOpen) === previous?.dataset.svDetail))?.focus({ preventScroll: true });
            if (resume && !editing && !servicesReduceMotion.matches && !document.hidden) pausedVideos.forEach(video => video.play().catch(() => {}));
            pausedVideos = [];
            requestServicesUpdate();
          };
          const detailFromHash = () => {
            const raw = window.location.hash.slice(1);
            let hash;
            try { hash = decodeURIComponent(raw); } catch { return null; }
            return details.find(detail => [raw, hash].includes(detail.dataset.svHash) || (first && [raw, hash].includes(detail.dataset.svDetail)));
          };
          const push = url => { try { window.history.pushState(null, '', url); } catch { /* A preview may restrict history. */ } };
          const open = (detail, trigger) => {
            if (!detail) return;
            if (!active && !detailFromHash()) returnUrl = window.location.pathname + window.location.search + window.location.hash;
            opener = trigger; show(detail); reveal();
            if (!editing && window.location.hash !== `#${detail.dataset.svHash}`) push(`#${detail.dataset.svHash}`);
            back.focus({ preventScroll: true });
            if (editing) editService(detail);
          };
          const close = () => {
            if (!active) return;
            hide(true); reveal();
            if (!editing && detailFromHash()) push(returnUrl);
          };
          section.addEventListener('click', event => {
            if (editing) {
              const edit = event.target.closest('[data-sv-edit]');
              const wall = event.target.closest('.sv-wall');
              if (edit || wall) {
                event.preventDefault(); event.stopPropagation();
                editService(active, edit?.dataset.svEdit || 'detail_images', !edit || edit.dataset.svEdit === 'detail_images');
                return;
              }
            }
            const trigger = event.target.closest(editing ? '[data-sv-open], [data-sv-editor-open]' : '[data-sv-open]');
            if (trigger && section.contains(trigger)) {
              event.preventDefault(); if (editing) event.stopPropagation(); open(details.find(detail => detail.dataset.svDetail === (trigger.dataset.svOpen || trigger.dataset.svEditorOpen)), trigger);
            } else if (event.target.closest('[data-sv-close]')) { event.preventDefault(); if (editing) event.stopPropagation(); close(); }
          }, { signal });
          section.addEventListener('keydown', event => {
            if (event.key === 'Escape' && active) { event.preventDefault(); close(); }
          }, { signal });
          const followLocation = () => {
            if (editing) return;
            const detail = detailFromHash();
            if (detail) { if (detail !== active) show(detail); }
            else if (active) hide(true);
          };
          window.addEventListener('popstate', followLocation, { signal });
          window.addEventListener('hashchange', () => { followLocation(); if (active) reveal(); }, { signal });
          window.addEventListener('resize', syncHeight, { signal });
          window.addEventListener('load', syncHeight, { signal });
          if (window.ResizeObserver) {
            this.detailObserver = new window.ResizeObserver(syncHeight);
            this.detailObserver.observe(list); this.detailObserver.observe(panel);
          }
          document.addEventListener('visibilitychange', () => {
            section.classList.toggle('is-wall-paused', document.hidden);
          }, { signal });
          const remembered = editing ? editorViews.get(widgetKey) : null;
          if (remembered && remembered.signature === previewSignature) {
            const detail = remembered.id ? details.find(item => item.dataset.svDetail === remembered.id) || details[remembered.index] : null;
            if (detail) show(detail); else hide(false, false);
          } else if (editing && section.dataset.editorPreview === 'yes') {
            show(details[Math.min(details.length - 1, Math.max(0, Number(section.dataset.previewIndex || 1) - 1))]);
          } else if (!editing && detailFromHash()) { show(detailFromHash()); reveal(true); }
          else hide(false, false);
          this.cleanupDetails = () => {
            pausedVideos = []; active = null;
            section.classList.remove('is-detail', 'is-enhanced', 'is-wall-paused', 'is-services-editor');
            editorLinks.forEach(({ link, href }) => link.setAttribute('href', href));
            if (editing) section.querySelectorAll('.sv-wall').forEach(wall => wall.setAttribute('aria-hidden', 'true'));
            list.inert = false; panel.inert = true; panel.setAttribute('aria-hidden', 'true');
            viewport.style.height = '';
            details.forEach(detail => { detail.hidden = true; });
            buttons.forEach(button => button.removeAttribute('aria-expanded'));
          };
        }

      }
      onDestroy() {
        this.controller?.abort();
        this.detailObserver?.disconnect();
        this.cleanupDetails?.();
        if (this.frame != null) window.cancelAnimationFrame(this.frame);
        this.videos?.forEach(video => video.pause());
        this.resetCards?.();
        super.onDestroy();
      }
    }
    elementorFrontend.hooks.addAction('frontend/element_ready/jednotka-services.default', $element => {
      elementorFrontend.elementsHandler.addHandler(ServicesHandler, { $element });
    });
  }
  jQuery(window).on('elementor/frontend/init', register);
  register();
})();
