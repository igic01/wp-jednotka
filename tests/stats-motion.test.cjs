const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

function setup({ editing = false, reduced = false, stacked = false, serverEditor = false } = {}) {
  class Element extends EventTarget {
    constructor(dataset = {}) {
      super(); this.dataset = dataset; this.textContent = ''; this.attributes = {};
      this.style = { setProperty(key, value) { this[key] = value; } };
      this.classes = new Set();
      this.classList = { toggle: (key, on) => on ? this.classes.add(key) : this.classes.delete(key), add: key => this.classes.add(key) };
    }
    setAttribute(key, value) { this.attributes[key] = value; }
    getBoundingClientRect() { return { top: this.top ?? 0 }; }
  }
  const counter = new Element({ countTo: '100', countSuffix: '+' }); counter.textContent = '100+';
  const card = new Element(); card.top = 1000;
  const section = new Element({ count: 'yes', reveal: 'yes', drift: 'yes' });
  section.dataset.editor = serverEditor ? 'yes' : 'no';
  section.querySelectorAll = selector => ({ '.testimonial-card': [card], '[data-count-to]': [counter], video: [] })[selector];
  section.querySelector = () => null;
  const frames = new Map(); let sequence = 0, observer, handler, hook;
  const media = new Map();
  const window = new EventTarget();
  Object.assign(window, {
    innerHeight: 900,
    matchMedia(query) { const result = new EventTarget(); result.matches = (query.includes('reduced') && reduced) || (query.includes('900px') && stacked); media.set(query, result); return result; },
    getComputedStyle: () => ({ getPropertyValue: () => '' }),
    requestAnimationFrame(callback) { frames.set(++sequence, callback); return sequence; },
    cancelAnimationFrame(id) { frames.delete(id); },
    elementorFrontend: { isEditMode: () => editing, hooks: { addAction: (name, callback) => { hook = callback; } }, elementsHandler: { addHandler(Type, settings) { handler = new Type(); Object.assign(handler, settings); handler.onInit(); } } },
    elementorModules: { frontend: { handlers: { Base: class { onInit() {} onDestroy() {} } } } },
  });
  vm.runInNewContext(fs.readFileSync(__dirname + '/../assets/js/stats.js', 'utf8'), {
    window, elementorFrontend: window.elementorFrontend, elementorModules: window.elementorModules,
    jQuery: () => ({ on() {} }), AbortController, performance: { now: () => 0 },
    IntersectionObserver: class { constructor(callback) { observer = this; this.callback = callback; this.targets = new Set(); } observe(el) { this.targets.add(el); } unobserve(el) { this.targets.delete(el); } disconnect() { this.targets.clear(); } },
  });
  hook([{ querySelector: () => section }]);
  return { handler, counter, card, section, window, media, frames, get observer() { return observer; }, tick(time) { const pending = [...frames.values()]; frames.clear(); pending.forEach(fn => fn(time)); } };
}

test('counts once when visible, reveals on scroll, pauses, and cleans up', () => {
  const s = setup();
  assert.equal(s.counter.textContent, '100+');
  s.observer.callback([{ target: s.counter, isIntersecting: true }]);
  assert.equal(s.counter.textContent, '1+');
  assert.equal(s.observer.targets.size, 0);
  s.tick(600); assert.equal(s.counter.textContent, '88+');
  s.tick(1200); assert.equal(s.counter.textContent, '100+');
  assert.equal(s.card.style.opacity, '0.120');
  s.card.top = 0; s.window.dispatchEvent(new Event('scroll')); s.tick(1300);
  assert.equal(s.card.style.opacity, '1.000');
  const reduced = s.media.get('(prefers-reduced-motion: reduce)');
  reduced.matches = true; reduced.dispatchEvent(new Event('change'));
  assert.ok(s.section.classes.has('is-paused') && s.section.classes.has('is-static'));
  reduced.matches = false; reduced.dispatchEvent(new Event('change'));
  assert.equal(s.section.classes.has('is-paused'), false);
  s.window.dispatchEvent(new Event('scroll'));
  s.handler.onDestroy(); assert.equal(s.frames.size, 0);
  s.window.dispatchEvent(new Event('scroll')); assert.equal(s.frames.size, 0);
});

test('editor and reduced-motion visitors see final editable values without counting', () => {
  for (const config of [{ editing: true }, { serverEditor: true }, { reduced: true }]) {
    const s = setup(config);
    assert.equal(s.observer, undefined);
    assert.equal(s.counter.textContent, '100+');
    assert.ok(s.section.classes.has('is-static'));
    assert.ok(s.section.classes.has('is-paused'));
    const reduced = s.media.get('(prefers-reduced-motion: reduce)');
    reduced.matches = true; reduced.dispatchEvent(new Event('change'));
    reduced.matches = false; reduced.dispatchEvent(new Event('change'));
    if (config.editing || config.serverEditor) {
      assert.ok(s.section.classes.has('is-paused'));
      assert.equal(s.card.style.opacity, '');
    }
    s.handler.onDestroy();
  }
});

test('stacked cards stay visible and clear desktop reveal styles when resizing', () => {
  const s = setup({ stacked: true });
  assert.equal(s.card.style.opacity, '');
  assert.equal(s.card.style.filter, '');
  assert.equal(s.card.style.transform, '');
  const stacked = s.media.get('(max-width: 900px)');
  stacked.matches = false; stacked.dispatchEvent(new Event('change'));
  assert.equal(s.card.style.opacity, '0.120');
  stacked.matches = true; stacked.dispatchEvent(new Event('change'));
  assert.equal(s.card.style.opacity, '');
  assert.equal(s.card.style.filter, '');
  assert.equal(s.card.style.transform, '');
  s.window.dispatchEvent(new Event('scroll')); s.tick(100);
  assert.equal(s.card.style.opacity, '');
  s.handler.onDestroy();
});

test('changing reduced-motion preference completes and cancels active counters', () => {
  const s = setup();
  s.observer.callback([{ target: s.counter, isIntersecting: true }]);
  const media = s.media.get('(prefers-reduced-motion: reduce)');
  media.matches = true; media.dispatchEvent(new Event('change'));
  assert.equal(s.counter.textContent, '100+');
  assert.equal(s.frames.size, 0);
  assert.ok(s.section.classes.has('is-paused'));
  s.handler.onDestroy();
});
