const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(__dirname + '/../assets/js/showcase.js', 'utf8');

function setup({ editing = false, reduced = false, animate = true, observers = true, snap = false, comparison = true } = {}) {
  class Element extends EventTarget {
    constructor() {
      super(); this.dataset = {}; this.attributes = {}; this.value = '65'; this.offsetHeight = 2200;
      this.offsetWidth = 1000; this.clientWidth = 1120; this.top = 600; this.captured = null;
      const classes = new Set();
      this.classList = { toggle: (key, value) => value ? classes.add(key) : classes.delete(key), contains: key => classes.has(key) };
      this.style = { setProperty(key, value) { this[key] = value; }, removeProperty(key) { delete this[key]; } };
    }
    setAttribute(key, value) { this.attributes[key] = value; }
    getBoundingClientRect() { const scale = Number(this.style['--grow'] || 1); return { left: 100, top: this.top, width: this.offsetWidth * scale, height: 600 }; }
    closest() { return null; }
    focus() { this.dispatchEvent(new Event('focus')); }
    setPointerCapture(id) { this.captured = id; }
    hasPointerCapture(id) { return this.captured === id; }
    releasePointerCapture() { this.captured = null; this.dispatchEvent(new Event('lostpointercapture')); }
  }
  const roots = [new Element(), new Element()];
  const children = roots.map(root => {
    root.dataset = { snap: snap ? 'yes' : '', position: '65', animate: animate ? 'yes' : '' };
    const split = new Element(), compare = new Element(), slider = new Element(), range = new Element();
    const before = { textContent: 'Before' }, after = { textContent: 'After' };
    root.querySelector = selector => ({ '.jn-showcase__split': split, '.jn-showcase__compare': comparison ? compare : null, '.jn-showcase__slider': comparison ? slider : null, '.jn-showcase__range': comparison ? range : null, '.jn-showcase__label--before': before, '.jn-showcase__label--after': after })[selector];
    return { split, compare, slider, range };
  });
  const timers = new Map(), scrolls = [];
  const frames = new Map(), resizeObservers = [], intersectionObservers = [];
  const reducedMedia = new EventTarget(); reducedMedia.matches = reduced;
  const document = new EventTarget(); document.hidden = false;
  let nextFrame = 0, hook;
  const handlers = [];
  const window = new EventTarget(); Object.assign(window, {
    innerHeight: 1000, scrollY: 500,
    setTimeout: callback => { const id = ++nextFrame; timers.set(id, callback); return id; },
    clearTimeout: id => timers.delete(id),
    scrollTo: options => scrolls.push(options),
    matchMedia: () => reducedMedia,
    requestAnimationFrame: callback => { frames.set(++nextFrame, callback); return nextFrame; },
    cancelAnimationFrame: id => frames.delete(id),
    elementorFrontend: {
      isEditMode: () => editing,
      hooks: { addAction: (name, callback) => { assert.equal(name, 'frontend/element_ready/jednotka-showcase.default'); hook = callback; } },
      elementsHandler: { addHandler(Type, args) { const h = new Type(); Object.assign(h, args); h.onInit(); handlers.push(h); } },
    },
    elementorModules: { frontend: { handlers: { Base: class { onInit() {} onDestroy() {} } } } },
  });
  if (observers) {
    window.ResizeObserver = class {
      constructor(callback) { this.callback = callback; this.targets = new Set(); resizeObservers.push(this); }
      observe(target) { this.targets.add(target); }
      disconnect() { this.targets.clear(); }
    };
    window.IntersectionObserver = class {
      constructor(callback) { this.callback = callback; this.targets = new Set(); intersectionObservers.push(this); }
      observe(target) { this.targets.add(target); }
      disconnect() { this.targets.clear(); }
    };
  }
  vm.runInNewContext(source, { window, document, AbortController, elementorFrontend: window.elementorFrontend, elementorModules: window.elementorModules, jQuery: () => ({ on() {} }) });
  hook([{ querySelector: () => roots[0] }]);
  return { ...children[0], children, roots, frames, timers, scrolls, window, document, reducedMedia, resizeObservers, intersectionObservers, handlers,
    addSecond() { hook([{ querySelector: () => roots[1] }]); },
    tick(time) { const callbacks = [...frames.values()]; frames.clear(); callbacks.forEach(callback => callback(time)); },
    settle() { const callbacks = [...timers.values()]; timers.clear(); callbacks.forEach(callback => callback()); },
    key(key) { const e = new Event('keydown', { cancelable: true }); Object.defineProperty(e, 'key', { value: key }); children[0].range.dispatchEvent(e); return e; },
    pointer(type, x, id = 1) { const e = new Event(type, { cancelable: true }); Object.defineProperties(e, { clientX: { value: x }, pointerId: { value: id }, button: { value: 0 }, isPrimary: { value: true } }); children[0].slider.dispatchEvent(e); return e; },
    destroy() { handlers.forEach(h => h.onDestroy()); },
  };
}

test('comparison responds to keyboard, clamps extremes, and exposes accessible percentages', () => {
  const s = setup({ animate: false });
  assert.equal(s.range.value, '65');
  assert.equal(s.key('ArrowLeft').defaultPrevented, true);
  assert.equal(s.range.value, '60');
  s.key('Home'); assert.equal(s.range.value, '0');
  assert.equal(s.slider.attributes['data-edge'], 'left');
  assert.equal(s.range.attributes['aria-valuetext'], 'Before 0%, After 100%');
  s.key('ArrowDown'); assert.equal(s.range.value, '0');
  s.key('End'); s.key('ArrowRight'); assert.equal(s.range.value, '100');
  assert.equal(s.slider.attributes['data-edge'], 'right');
  s.key('ArrowLeft'); assert.equal(s.slider.attributes['data-edge'], 'none');
  assert.equal(s.key('Tab').defaultPrevented, false);
  s.destroy();
});

test('dragging captures the pointer without edge zoom and cancels on touch scroll', () => {
  const s = setup({ animate: false });
  s.key('End'); assert.equal(s.slider.style['--grow'], undefined);
  s.pointer('pointerdown', 500); assert.equal(s.range.value, '40'); assert.equal(s.slider.captured, 1);
  s.pointer('pointermove', 800, 2); assert.equal(s.range.value, '40');
  s.pointer('pointermove', 700); assert.equal(s.range.value, '60');
  s.pointer('pointercancel', 700); assert.equal(s.slider.captured, null);
  s.pointer('pointermove', 900); assert.equal(s.range.value, '60');
  s.destroy();
});

test('visible comparison sweeps from 100 to configured 65 in 1400ms; interaction cancels it', () => {
  const s = setup(); assert.equal(s.range.value, '100');
  s.intersectionObservers[0].callback([{ isIntersecting: false }]); assert.equal(s.frames.size, 0);
  s.intersectionObservers[0].callback([{ isIntersecting: true }]);
  s.tick(0); s.tick(700); assert.equal(s.range.value, String(100 - 35 * .875));
  s.tick(1400); assert.equal(s.range.value, '65'); assert.equal(s.frames.size, 0);
  s.destroy();
  const interrupted = setup(); interrupted.key('ArrowLeft');
  assert.equal(interrupted.range.value, '95'); assert.equal(interrupted.intersectionObservers[0].targets.size, 0);
  interrupted.destroy();
});

test('editor, reduced motion, disabled intro and missing observers show the configured position', () => {
  for (const options of [{ editing: true }, { reduced: true }, { animate: false }, { observers: false }]) {
    const s = setup(options); assert.equal(s.range.value, '65'); assert.equal(s.intersectionObservers.length, 0);
    s.destroy();
  }
  const s = setup({ animate: false }); s.key('Home'); assert.equal(s.slider.style['--grow'], undefined); s.destroy();
});

test('resize measures the sticky gallery, scroll removes corners, and instances are independent', () => {
  const s = setup({ animate: false }); s.addSecond();
  assert.equal(s.split.style['--split-h'], '2200px');
  s.split.offsetHeight = 2800; s.resizeObservers[0].callback(); assert.equal(s.split.style['--split-h'], '2800px');
  s.compare.top = 0; s.window.dispatchEvent(new Event('scroll')); s.tick(0); assert.equal(s.compare.classList.contains('is-full'), true);
  s.key('Home'); assert.equal(s.children[1].range.value, '65');
  s.destroy(); assert.equal(s.resizeObservers[0].targets.size, 0);
  assert.equal(s.split.style['--split-h'], undefined);
});

test('reduced motion, hidden tabs, and teardown stop intro frames and event listeners', () => {
  for (const stop of [s => { s.reducedMedia.matches = true; s.reducedMedia.dispatchEvent(new Event('change')); }, s => {
    s.document.hidden = true; s.document.dispatchEvent(new Event('visibilitychange'));
  }]) {
    const s = setup(); s.intersectionObservers[0].callback([{ isIntersecting: true }]); s.tick(0);
    stop(s); assert.equal(s.frames.size, 0); assert.equal(s.range.value, '65');
    assert.equal(s.intersectionObservers[0].targets.size, 0); s.destroy();
  }
  const s = setup(); s.intersectionObservers[0].callback([{ isIntersecting: true }]);
  s.window.dispatchEvent(new Event('scroll')); s.destroy(); assert.equal(s.frames.size, 0);
  s.key('Home'); assert.equal(s.range.value, '65');
  s.window.dispatchEvent(new Event('scroll')); assert.equal(s.frames.size, 0);
});

test('comparison snaps smoothly after scroll settles, once per approach', () => {
  const s = setup({ animate: false, snap: true });
  s.compare.top = 120;
  s.window.dispatchEvent(new Event('scroll')); s.tick(0);
  assert.equal(s.scrolls.length, 0); assert.equal(s.timers.size, 1);
  s.settle(); assert.equal(s.scrolls[0].top, 620); assert.equal(s.scrolls[0].behavior, 'smooth');
  s.window.dispatchEvent(new Event('scroll')); s.tick(0); s.settle(); assert.equal(s.scrolls.length, 1);
  s.compare.top = 800; s.window.dispatchEvent(new Event('scroll')); s.tick(0);
  s.compare.top = 100; s.window.dispatchEvent(new Event('scroll')); s.tick(0); s.settle();
  assert.equal(s.scrolls.length, 2); s.destroy();
});

test('snap respects reduced motion, editor mode, user gestures, and teardown', () => {
  for (const options of [{ reduced: true }, { editing: true }, { snap: false }]) {
    const s = setup({ snap: true, animate: false, ...options }); s.compare.top = 120;
    s.window.dispatchEvent(new Event('scroll')); s.tick(0); s.settle();
    assert.equal(s.scrolls.length, 0); s.destroy();
  }
  const s = setup({ snap: true, animate: false }); s.compare.top = 120;
  s.window.dispatchEvent(new Event('scroll')); s.tick(0);
  s.window.dispatchEvent(new Event('wheel')); s.settle(); assert.equal(s.scrolls.length, 0);
  s.window.dispatchEvent(new Event('scroll')); s.tick(0); s.key('Home'); s.settle(); assert.equal(s.scrolls.length, 0);
  s.window.dispatchEvent(new Event('scroll')); s.tick(0); s.destroy();
  assert.equal(s.timers.size, 0);
});

test('gallery-only mode measures its height and cleans up without comparison or snapping', () => {
  for (const observers of [true, false]) {
    const s = setup({ comparison: false, observers, snap: true });
    assert.equal(s.split.style['--split-h'], '2200px');
    s.split.offsetHeight = 2600;
    if (observers) s.resizeObservers[0].callback();
    else s.window.dispatchEvent(new Event('resize'));
    assert.equal(s.split.style['--split-h'], '2600px');
    assert.equal(s.intersectionObservers.length, 0);
    assert.equal(s.timers.size, 0);
    s.destroy();
    assert.equal(s.split.style['--split-h'], undefined);
    if (observers) assert.equal(s.resizeObservers[0].targets.size, 0);
  }
});
