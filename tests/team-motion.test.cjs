const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(__dirname + '/../assets/js/team.js', 'utf8');

function setup({ reduced = false, editing = false, auto = true, loopWidth = 2000, stageWidth = 700, members = 5 } = {}) {
  class Element extends EventTarget {
    constructor() {
      super(); this.children = []; this.style = {}; this.dataset = {}; this.offsetWidth = loopWidth; this.clientWidth = stageWidth;
      this.attributes = {}; this.captured = null; const classes = new Set();
      this.classList = { add: c => classes.add(c), remove: c => classes.delete(c), contains: c => classes.has(c) };
    }
    get firstElementChild() { return this.children[0]; }
    get lastElementChild() { return this.children.at(-1); }
    appendChild(child) { this.children.push(child); child.parent = this; }
    remove() { this.parent.children = this.parent.children.filter(e => e !== this); }
    cloneNode() { const clone = new Element(); clone.children = [...this.children]; clone.attributes = { ...this.attributes }; return clone; }
    closest() { return null; }
    setPointerCapture(id) { this.captured = id; }
    hasPointerCapture(id) { return this.captured === id; }
    releasePointerCapture() { this.captured = null; this.dispatchEvent(new Event('lostpointercapture')); }
  }
  const section = new Element(), stage = new Element(), track = new Element(), group = new Element(), clone = new Element();
  group.children = Array.from({ length: members }, () => new Element()); clone.children = [...group.children]; clone.attributes = { 'aria-hidden': 'true', inert: '' };
  track.appendChild(group); track.appendChild(clone);
  section.dataset = { auto: auto ? 'yes' : '', speed: '45' };
  section.querySelector = selector => selector === '.team__stage' ? stage : track;
  const reduce = new EventTarget(); reduce.matches = reduced;
  const document = new EventTarget(); document.hidden = false;
  const frames = new Map(), intersectionObservers = [], resizeObservers = []; let id = 0, callback, handler;
  const window = new EventTarget(); Object.assign(window, {
    matchMedia: () => reduce,
    requestAnimationFrame: fn => { frames.set(++id, fn); return id; }, cancelAnimationFrame: id => frames.delete(id),
    IntersectionObserver: class { constructor(fn) { this.fn = fn; this.targets = new Set(); intersectionObservers.push(this); } observe(e) { this.targets.add(e); } disconnect() { this.targets.clear(); } },
    ResizeObserver: class { constructor(fn) { this.fn = fn; this.targets = new Set(); resizeObservers.push(this); } observe(e) { this.targets.add(e); } disconnect() { this.targets.clear(); } },
    elementorFrontend: { isEditMode: () => editing, hooks: { addAction: (name, fn) => { assert.equal(name, 'frontend/element_ready/jednotka-team.default'); callback = fn; } }, elementsHandler: { addHandler(Type, args) { handler = new Type(); Object.assign(handler, args); handler.onInit(); } } },
    elementorModules: { frontend: { handlers: { Base: class { onInit() {} onDestroy() {} } } } },
  });
  vm.runInNewContext(source, { window, document, AbortController, elementorFrontend: window.elementorFrontend, elementorModules: window.elementorModules, jQuery: () => ({ on() {} }) });
  callback([{ querySelector: () => section }]);
  return { stage, track, group, frames, reduce, document, intersectionObservers, resizeObservers, handler,
    visible(value = true) { intersectionObservers[0].fn([{ isIntersecting: value }]); },
    tick(time) { const callbacks = [...frames.values()]; frames.clear(); callbacks.forEach(fn => fn(time)); },
    x() { return Number(track.style.transform?.match(/translate3d\(([-\d.]+)px/)[1] || 0); },
    pointer(type, x, time = 0, pointerId = 1) { const e = new Event(type, { cancelable: true }); Object.defineProperties(e, { clientX: { value: x }, timeStamp: { value: time }, pointerId: { value: pointerId }, pointerType: { value: 'mouse' }, button: { value: 0 }, isPrimary: { value: true } }); stage.dispatchEvent(e); },
    key(key) { const e = new Event('keydown', { cancelable: true }); Object.defineProperty(e, 'key', { value: key }); stage.dispatchEvent(e); return e; },
    destroy() { handler.onDestroy(); },
  };
}

test('auto track moves at reference speed only while visible and stops in hidden tabs', () => {
  const s = setup(); assert.equal(s.frames.size, 0);
  s.visible(); s.tick(0); s.tick(50); s.tick(100); assert.equal(s.x(), -4.5);
  s.visible(false); assert.equal(s.frames.size, 0);
  s.visible(); s.document.hidden = true; s.document.dispatchEvent(new Event('visibilitychange')); assert.equal(s.frames.size, 0);
  s.document.hidden = false; s.document.dispatchEvent(new Event('visibilitychange')); assert.equal(s.frames.size, 1);
  s.destroy(); assert.equal(s.frames.size, 0);
});

test('one-member track has enough hidden repetitions to cover a wide stage', () => {
  const s = setup({ loopWidth: 220, stageWidth: 800, members: 1 });
  assert.equal(s.track.children.length, 5);
  assert.ok(s.track.children.slice(1).every(group => group.attributes['aria-hidden'] === 'true' && 'inert' in group.attributes));
  s.stage.clientWidth = 100; s.resizeObservers[0].fn(); assert.equal(s.track.children.length, 2);
  s.destroy();
});

test('pointer dragging renders immediately, supports either direction, and releases capture', () => {
  const s = setup(); s.visible(); s.pointer('pointerdown', 100); assert.equal(s.frames.size, 0);
  s.pointer('pointermove', 60, 16); assert.equal(s.x(), -40);
  s.pointer('pointermove', 80, 32); assert.equal(s.x(), -20);
  s.pointer('pointerup', 80, 48); assert.equal(s.stage.captured, null); assert.equal(s.stage.classList.contains('is-dragging'), false);
  assert.equal(s.frames.size, 1); s.tick(100); s.tick(150); assert.notEqual(s.x(), -20);
  s.destroy();
});

test('reduced motion, editor mode, and disabled auto remain manually draggable', () => {
  for (const options of [{ reduced: true }, { editing: true }, { auto: false }]) {
    const s = setup(options); s.visible(); assert.equal(s.frames.size, 0);
    s.pointer('pointerdown', 100); s.pointer('pointermove', 50, 16); assert.equal(s.x(), -50);
    s.pointer('pointercancel', 50, 32); assert.equal(s.frames.size, 0); s.destroy();
  }
});

test('keyboard navigation pauses automatic movement while focused and resumes on blur', () => {
  const s = setup(); s.visible(); s.stage.dispatchEvent(new Event('focusin')); assert.equal(s.frames.size, 0);
  assert.equal(s.key('ArrowRight').defaultPrevented, true); assert.equal(s.x(), -400);
  s.key('Home'); assert.equal(s.x(), 0);
  s.key('End'); assert.equal(s.x(), -1600);
  s.stage.dispatchEvent(new Event('focusout')); assert.equal(s.frames.size, 1);
  s.destroy();
});

test('empty sets do not animate; reduced-motion changes and teardown release resources', () => {
  const empty = setup({ loopWidth: 0, members: 0 }); empty.visible(); assert.equal(empty.frames.size, 0); empty.destroy();
  const s = setup(); s.visible(); s.reduce.matches = true; s.reduce.dispatchEvent(new Event('change')); assert.equal(s.frames.size, 0);
  s.pointer('pointerdown', 100); s.destroy(); assert.equal(s.stage.captured, null);
  assert.equal(s.intersectionObservers[0].targets.size, 0); assert.equal(s.resizeObservers[0].targets.size, 0);
  assert.equal(s.track.style.transform, '');
  s.pointer('pointermove', 200, 16); assert.equal(s.track.style.transform, '');
});
