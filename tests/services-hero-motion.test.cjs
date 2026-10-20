const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
function setup({ editing = false, reduced = false, auto = 'yes', floating = 'yes', observers = false, noTrack = false, trackWidth = 500 } = {}) {
  class Element extends EventTarget {
    constructor() { super(); this.style = { setProperty(key, value) { this[key] = value; } }; const classes = new Set(); this.classList = { add: k => classes.add(k), remove: k => classes.delete(k), contains: k => classes.has(k) }; }
    getBoundingClientRect() { return { left: 0, top: 0, width: this.cardWidth ?? 1000, height: 800 }; }
    cloneNode() { return new Element(); }
    setAttribute(key, value) { (this.attributes ||= {})[key] = value; }
    removeAttribute(key) { delete (this.attributes ||= {})[key]; }
    querySelectorAll() { return []; }
    matches() { return false; }
    appendChild(node) { node.parent = this; this.children.push(node); }
    remove() { if (this.parent) this.parent.children.splice(this.parent.children.indexOf(this), 1); }
    contains(node) { return node === this; }
    setPointerCapture(id) { this.capture = id; }
    hasPointerCapture(id) { return this.capture === id; }
    releasePointerCapture() { this.capture = null; }
  }
  const section = new Element(), hero = new Element(), track = new Element(), badges = [new Element(), new Element()];
  section.dataset = { auto, floating, parallax: 'yes', speed: '42' };
  let scroll = 0; Object.defineProperty(track, 'scrollLeft', { get: () => scroll, set: v => { scroll = Math.round(Math.max(0, Math.min(track.scrollWidth - track.clientWidth, v))); } });
  track.clientWidth = trackWidth; track.children = [new Element(), new Element()];
  track.children.forEach(card => { card.cardWidth = 734; });
  Object.defineProperty(track, 'scrollWidth', { get: () => track.children.length * 750 });
  section.querySelector = selector => selector === '.services-hero' ? hero : noTrack ? null : track;
  section.querySelectorAll = () => badges;
  const document = new EventTarget(); document.hidden = false;
  const media = new EventTarget(); media.matches = reduced;
  const window = new EventTarget(); const frames = new Map(), intersections = [], resizes = []; let seq = 0, now = 0, handler, hook;
  Object.assign(window, { getComputedStyle: () => ({ columnGap: '16px' }), performance: { now: () => now }, matchMedia: () => media,
    requestAnimationFrame: fn => { frames.set(++seq, fn); return seq; }, cancelAnimationFrame: id => frames.delete(id),
    elementorFrontend: { isEditMode: () => editing, hooks: { addAction: (name, fn) => { hook = fn; } }, elementsHandler: { addHandler(Type, args) { handler = new Type(); Object.assign(handler, args); handler.onInit(); } } },
    elementorModules: { frontend: { handlers: { Base: class { onInit() {} onDestroy() {} } } } },
  });
  if (observers) for (const [key, list] of [['IntersectionObserver', intersections], ['ResizeObserver', resizes]]) window[key] = class {
    constructor(callback) { this.callback = callback; this.targets = new Set(); list.push(this); } observe(node) { this.targets.add(node); } disconnect() { this.targets.clear(); }
  };
  vm.runInNewContext(fs.readFileSync(__dirname + '/../assets/js/services-hero.js', 'utf8'), { window, document, AbortController, elementorFrontend: window.elementorFrontend, elementorModules: window.elementorModules, jQuery: () => ({ on() {} }) });
  hook([{ querySelector: () => section }]);
  return { section, hero, track, badges, media, document, frames, handler, intersections, resizes,
    tick(time) { now = time; const queued = [...frames.values()]; frames.clear(); queued.forEach(fn => fn(time)); },
    event(target, type, values = {}) { const event = new Event(type, { cancelable: true }); for (const [key, value] of Object.entries(values)) Object.defineProperty(event, key, { value }); target.dispatchEvent(event); return event; },
  };
}
test('fractional auto scrolling progresses with rounded scrollLeft and wraps without reversing', () => {
  const s = setup({ floating: '' }); s.tick(0);
  for (let i = 1; i <= 60; i++) s.tick(i * 1000 / 60);
  assert.ok(Math.abs(s.track.scrollLeft - 42) <= 1);
  s.track.scrollLeft = 1499; s.event(s.track, 'scroll'); s.tick(2500);
  assert.ok(s.track.scrollLeft < 5); const wrapped = s.track.scrollLeft; s.tick(2550); assert.ok(s.track.scrollLeft > wrapped);
  s.handler.onDestroy(); assert.equal(s.frames.size, 0);
});
test('badges preserve the reference floating and pointer motion and ignore touch', () => {
  const s = setup({ auto: '' }); s.tick(1200);
  assert.equal(s.badges[0].style['--hero-badge-float-x'], `${(Math.sin(1200 * .00065) * 2.4).toFixed(2)}px`);
  s.event(s.hero, 'pointermove', { pointerType: 'mouse', clientX: 1000, clientY: 800 }); assert.equal(s.hero.style['--hero-badge-x'], '11.00px');
  s.event(s.hero, 'pointermove', { pointerType: 'touch', clientX: 0, clientY: 0 }); assert.equal(s.hero.style['--hero-badge-x'], '11.00px');
  s.event(s.hero, 'pointerleave'); assert.equal(s.hero.style['--hero-badge-x'], '0px'); s.handler.onDestroy();
});
test('clicks work, dragging suppresses its click and manual keyboard navigation remains available', () => {
  const s = setup();
  s.event(s.track, 'pointerdown', { pointerId: 1, clientX: 300, button: 0 });
  s.event(s.track, 'pointerup', { pointerId: 1 }); assert.equal(s.event(s.track, 'click', { detail: 1 }).defaultPrevented, false);
  s.event(s.track, 'pointerdown', { pointerId: 2, clientX: 300, button: 0 });
  s.event(s.track, 'pointermove', { pointerId: 99, clientX: 100 }); assert.equal(s.track.scrollLeft, 0);
  s.event(s.track, 'pointermove', { pointerId: 2, clientX: 200 }); assert.equal(s.track.scrollLeft, 100);
  s.event(s.track, 'pointerup', { pointerId: 2 }); assert.equal(s.track.classList.contains('is-dragging'), false);
  assert.equal(s.event(s.track, 'click', { detail: 1 }).defaultPrevented, true);
  assert.equal(s.event(s.track, 'click', { detail: 1 }).defaultPrevented, false);
  s.event(s.track, 'focusin'); const before = s.track.scrollLeft; s.tick(3000); s.tick(3050); assert.equal(s.track.scrollLeft, before);
  assert.equal(s.event(s.track, 'keydown', { key: 'End' }).defaultPrevented, true); assert.equal(s.track.scrollLeft, 1499);
  s.event(s.track, 'keydown', { key: 'Home' }); assert.equal(s.track.scrollLeft, 0);
  s.event(s.track, 'focusout', { relatedTarget: null }); s.tick(6000); s.tick(6050); assert.ok(s.track.scrollLeft > 0); s.handler.onDestroy();
});
test('editor, reduced motion, offscreen state, and teardown stop animation work', () => {
  for (const options of [{ editing: true }, { reduced: true }, { auto: '', floating: '' }]) {
    const s = setup(options); assert.equal(s.frames.size, 0); s.handler.onDestroy();
  }
  const s = setup({ observers: true }); assert.equal(s.frames.size, 1);
  s.intersections[0].callback([{ isIntersecting: false }]); assert.equal(s.frames.size, 0);
  s.intersections[0].callback([{ isIntersecting: true }]); assert.equal(s.frames.size, 1);
  s.document.hidden = true; s.event(s.document, 'visibilitychange'); assert.equal(s.frames.size, 0);
  s.document.hidden = false; s.event(s.document, 'visibilitychange'); assert.equal(s.frames.size, 1);
  s.media.matches = true; s.event(s.media, 'change'); assert.equal(s.frames.size, 0);
  s.handler.onDestroy(); assert.equal(s.resizes[0].targets.size, 0); assert.equal(s.intersections[0].targets.size, 0);
  s.media.matches = false; s.event(s.media, 'change'); assert.equal(s.frames.size, 0);
  const empty = setup({ noTrack: true }); empty.tick(100); empty.handler.onDestroy(); assert.equal(empty.frames.size, 0);
});

test('editor leaves text cursor keys and click propagation available for inline editing', () => {
  const s = setup({ editing: true });
  for (const key of ['ArrowLeft', 'ArrowRight', 'Home', 'End']) {
    assert.equal(s.event(s.track, 'keydown', { key }).defaultPrevented, false);
  }
  const click = s.event(s.track, 'click', { detail: 1 });
  assert.equal(click.defaultPrevented, true);
  assert.equal(click.cancelBubble, false);
  s.event(s.track, 'pointerdown', { pointerId: 1, clientX: 100, button: 0 });
  s.event(s.track, 'pointermove', { pointerId: 1, clientX: 50 });
  assert.equal(s.track.scrollLeft, 0);
  s.handler.onDestroy();
});


test('wide layouts have enough copies and resizing rebuilds without accumulating duplicates', () => {
  const s = setup({ trackWidth: 4000, observers: true });
  assert.ok(s.track.scrollWidth >= 4000 + 1500);
  const count = s.track.children.length;
  assert.ok(s.track.children.slice(2).every(card => card.attributes['aria-hidden'] === 'true'));
  s.resizes[0].callback();
  assert.equal(s.track.children.length, count);
  s.track.clientWidth = 8000; s.resizes[0].callback();
  assert.ok(s.track.scrollWidth >= 8000 + 1500);
  s.handler.onDestroy();
  assert.equal(s.track.children.length, 2);
});

test('dragging past the beginning wraps into the previous repeat', () => {
  const s = setup();
  s.event(s.track, 'pointerdown', { pointerId: 1, clientX: 100, button: 0 });
  s.event(s.track, 'pointermove', { pointerId: 1, clientX: 200 });
  assert.equal(s.track.scrollLeft, 1400);
  s.event(s.track, 'pointermove', { pointerId: 1, clientX: 300 });
  assert.equal(s.track.scrollLeft, 1300);
  s.event(s.track, 'pointerup', { pointerId: 1 });
  s.handler.onDestroy();
});
