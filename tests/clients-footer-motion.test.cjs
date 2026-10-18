const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(__dirname + '/../assets/js/clients-footer.js', 'utf8');
const reference = fs.readFileSync(__dirname + '/../examples/js/floating-projects.js', 'utf8');

function setup({ original = false, editing = false, reduced = false, disabled = false, withVideo = false, observerSupport = false, withControl = true, snap = false } = {}) {
  class Element extends EventTarget {
    constructor() {
      super(); this.dataset = {}; this.attributes = {}; this.children = [];
      const classes = new Set();
      this.classList = { add: key => classes.add(key), remove: key => classes.delete(key), contains: key => classes.has(key) };
      this.style = { setProperty(key, value) { this[key] = value; } };
    }
    setAttribute(key, value) { this.attributes[key] = value; }
    getBoundingClientRect() { return { left: 100, top: this.top ?? 200, width: 1000, height: 1400 }; }
    appendChild(child) { this.children.push(child); child.parent = this; }
    remove() { if (this.parent) this.parent.children = this.parent.children.filter(child => child !== this); }
    closest(selector) { return selector === 'a' ? this : null; }
  }
  const section = new Element(); section.dataset = { snap: snap ? 'yes' : '', reveal: disabled ? '' : 'yes', drift: disabled ? '' : 'yes', parallax: disabled ? '' : 'yes', trail: disabled ? '' : 'yes' };
  const cards = Array.from({ length: 6 }, () => new Element());
  const trail = new Element(); const control = new Element(); control.dataset = { playLabel: 'Play animations', pauseLabel: 'Pause animations' };
  const form = new Element(); const submit = new Element(); const notice = new Element(); notice.hidden = true;
  form.querySelector = selector => selector === 'button' ? submit : notice;
  const video = new Element(); video.paused = true;
  video.play = () => { video.paused = false; return Promise.resolve(); };
  video.pause = () => { video.paused = true; };
  section.querySelector = selector => ({ '.cursor-trail-layer': trail, '.jn-clients-footer__motion': withControl ? control : null, '.footer-newsletter': form })[selector];
  section.querySelectorAll = selector => selector === '.floating-project-card' ? cards : withVideo ? [video] : [];
  const document = new EventTarget(); document.hidden = false; document.querySelector = () => section; document.createElement = () => new Element();
  const reduce = new EventTarget(); reduce.matches = reduced;
  const scrolls = [];
  const frames = new Map(), timers = new Map(), observers = []; let sequence = 0, hook, handler;
  const window = new EventTarget(); Object.assign(window, {
    innerHeight: 1000, scrollY: 500, scrollTo: options => scrolls.push(options),
    matchMedia: () => reduce,
    requestAnimationFrame: fn => { frames.set(++sequence, fn); return sequence; }, cancelAnimationFrame: id => frames.delete(id),
    setTimeout: fn => { const id = ++sequence; timers.set(id, () => { timers.delete(id); fn(); }); return id; }, clearTimeout: id => timers.delete(id),
    elementorFrontend: { isEditMode: () => editing, hooks: { addAction: (name, fn) => { hook = fn; } }, elementsHandler: { addHandler(Type, settings) { handler = new Type(); Object.assign(handler, settings); handler.onInit(); } } },
    elementorModules: { frontend: { handlers: { Base: class { onInit() {} onDestroy() {} } } } },
  });
  if (observerSupport) window.IntersectionObserver = class {
    constructor(callback, options) { this.callback = callback; this.options = options; this.targets = new Set(); observers.push(this); }
    observe(target) { this.targets.add(target); }
    unobserve(target) { this.targets.delete(target); }
    disconnect() { this.targets.clear(); }
  };
  const math = Object.create(Math); math.random = () => .5;
  vm.runInNewContext(original ? reference : source, { window, document, Math: math, AbortController, elementorFrontend: window.elementorFrontend, elementorModules: window.elementorModules, jQuery: () => ({ on() {} }) });
  if (!original) hook([{ querySelector: () => section }]);
  return { window, scrolls, section, cards, trail, control, form, submit, notice, video, document, reduce, frames, timers, observers, handler,
    tick(time) { const queued = [...frames.values()]; frames.clear(); queued.forEach(fn => fn(time)); },
    move(x = 450, y = 800, timeStamp = 100) { const event = new Event('pointermove'); Object.defineProperties(event, { clientX: { value: x }, clientY: { value: y }, timeStamp: { value: timeStamp } }); section.dispatchEvent(event); },
    offsets() { return cards.map(card => [card.style['--float-card-drift-x'], card.style['--float-card-drift-y']]); },
  };
}

test('stronger floating keeps reference timing; mouse glow follows pointer without ripple nodes', () => {
  const actual = setup(), expected = setup({ original: true });
  for (const time of [0, 1200, 4200, 9600]) {
    actual.tick(time); expected.tick(time);
    actual.offsets().forEach((offset, index) => offset.forEach((value, axis) => {
      assert.ok(Math.abs(parseFloat(value) - 2 * parseFloat(expected.offsets()[index][axis])) <= .021);
    }));
  }
  actual.move(); expected.move();
  for (const key of ['--float-x', '--float-y']) assert.equal(actual.section.style[key], expected.section.style[key]);
  assert.equal(actual.trail.style['--glow-x'], '350px');
  assert.equal(actual.trail.style['--glow-y'], '600px');
  assert.equal(actual.trail.style['--glow-opacity'], '1');
  actual.move(500, 700);
  assert.equal(actual.trail.style['--glow-x'], '400px');
  assert.equal(actual.trail.children.length, 0);
  assert.equal(actual.timers.size, 0);
  actual.section.dispatchEvent(new Event('pointerleave'));
  assert.equal(actual.trail.style['--glow-opacity'], '0');
  actual.handler.onDestroy();
});

test('idle sway and image pan/zoom animate without pointer input and reset on pause', () => {
  const s = setup();
  s.tick(1200);
  const properties = ['--float-card-tilt', '--image-pan', '--image-zoom'];
  const before = properties.map(key => s.cards[0].style[key]);
  s.tick(4200);
  assert.ok(Math.abs(parseFloat(s.cards[0].style['--float-card-tilt'])) > 3);
  properties.forEach((key, index) => assert.notEqual(s.cards[0].style[key], before[index]));
  s.control.dispatchEvent(new Event('click'));
  assert.equal(s.cards[0].style['--float-card-tilt'], '0deg');
  assert.equal(s.cards[0].style['--image-pan'], '0%');
  assert.equal(s.cards[0].style['--image-zoom'], '1');
  s.handler.onDestroy();
});

test('pause and teardown stop frames, videos, pointer movement, and trail timers', () => {
  const s = setup({ withVideo: true }); s.tick(100); s.move();
  assert.equal(s.video.paused, false); assert.equal(s.timers.size, 0);
  s.control.dispatchEvent(new Event('click'));
  assert.equal(s.frames.size, 0); assert.equal(s.timers.size, 0); assert.equal(s.trail.children.length, 0); assert.equal(s.video.paused, true);
  s.move(); assert.equal(s.trail.children.length, 0);
  s.control.dispatchEvent(new Event('click')); assert.equal(s.frames.size, 1);
  s.move(100, 200, 200); s.handler.onDestroy();
  assert.equal(s.frames.size, 0); assert.equal(s.timers.size, 0); assert.equal(s.video.paused, true);
  s.move(100, 200, 300); assert.equal(s.trail.children.length, 0);
});

test('newsletter button, Enter, and submit cannot submit the email and show an honest notice', () => {
  const s = setup();
  for (const [target, type] of [[s.submit,'click'],[s.form,'submit'],[s.form,'keydown']]) {
    s.notice.hidden = true;
    const event = new Event(type, { cancelable: true }); Object.defineProperty(event, 'key', { value: 'Enter' });
    target.dispatchEvent(event); assert.equal(event.defaultPrevented, true); assert.equal(s.notice.hidden, false);
  }
  s.handler.onDestroy();
});

test('reduced motion and hidden tabs stop motion; disabled switches avoid animation work', () => {
  const reduced = setup({ reduced: true, withVideo: true }); reduced.move();
  assert.equal(reduced.frames.size, 0); assert.equal(reduced.trail.children.length, 0); assert.equal(reduced.video.paused, true); reduced.handler.onDestroy();
  const s = setup({ withVideo: true }); s.move(); s.document.hidden = true; s.document.dispatchEvent(new Event('visibilitychange'));
  assert.equal(s.frames.size, 0); assert.equal(s.timers.size, 0); assert.equal(s.video.paused, true);
  s.document.hidden = false; s.document.dispatchEvent(new Event('visibilitychange')); assert.equal(s.frames.size, 1);
  s.reduce.matches = true; s.reduce.dispatchEvent(new Event('change')); assert.equal(s.frames.size, 0); assert.equal(s.video.paused, true); s.handler.onDestroy();
  const disabled = setup({ disabled: true }); disabled.move(); assert.equal(disabled.frames.size, 0); assert.equal(disabled.trail.children.length, 0); assert.equal(disabled.control.hidden, true); disabled.handler.onDestroy();
});

test('editor pauses while typing, prevents link navigation, and keeps videos paused', () => {
  const s = setup({ editing: true, withVideo: true }); assert.equal(s.video.paused, true);
  s.move(); assert.equal(s.trail.children.length, 0);
  const focus = new Event('focusin'); Object.defineProperty(focus, 'target', { value: { closest: selector => selector === '[contenteditable="true"]' ? {} : null } });
  s.section.dispatchEvent(focus); assert.equal(s.frames.size, 0);
  s.section.dispatchEvent(new Event('focusout')); assert.equal(s.frames.size, 1); assert.equal(s.video.paused, true);
  const click = new Event('click', { cancelable: true }); Object.defineProperty(click, 'target', { value: s.control });
  s.section.dispatchEvent(click); assert.equal(click.defaultPrevented, true); s.handler.onDestroy();
});

test('project entrances reveal once on intersection while floating drift continues', () => {
  const s = setup({ observerSupport: true });
  const observer = s.observers[0];
  assert.equal(observer.targets.size, 6);
  assert.equal(s.cards[0].classList.contains('is-reveal-pending'), true);
  observer.callback([{ target: s.cards[0], isIntersecting: false }]);
  assert.equal(s.cards[0].classList.contains('is-reveal-pending'), true);
  observer.callback([{ target: s.cards[0], isIntersecting: true }]);
  assert.equal(s.cards[0].classList.contains('is-reveal-pending'), false);
  assert.equal(observer.targets.has(s.cards[0]), false);
  s.tick(1200); assert.equal(s.frames.size, 1);
  s.handler.onDestroy();
  assert.equal(observer.targets.size, 0);
  assert.ok(s.cards.every(card => !card.classList.contains('is-reveal-pending')));
});

test('pause, reduced motion, editor mode, and missing observers keep projects visible', () => {
  for (const options of [{ reduced: true }, { editing: true }, { disabled: true }]) {
    const s = setup({ observerSupport: true, ...options });
    assert.equal(s.observers.length, 0);
    assert.ok(s.cards.every(card => !card.classList.contains('is-reveal-pending')));
    s.handler.onDestroy();
  }
  const noObserver = setup();
  assert.ok(noObserver.cards.every(card => !card.classList.contains('is-reveal-pending')));
  noObserver.handler.onDestroy();
  for (const pause of [s => s.control.dispatchEvent(new Event('click')), s => {
    s.reduce.matches = true; s.reduce.dispatchEvent(new Event('change'));
  }]) {
    const s = setup({ observerSupport: true }); pause(s);
    assert.equal(s.observers[0].targets.size, 0);
    assert.ok(s.cards.every(card => !card.classList.contains('is-reveal-pending')));
    s.handler.onDestroy();
  }
});

test('footer animates without a motion button and still stops for reduced motion', () => {
  const s = setup({ withControl: false, withVideo: true, observerSupport: true });
  s.tick(1200); assert.equal(s.frames.size, 1); assert.equal(s.video.paused, false);
  s.move(); assert.equal(s.trail.style['--glow-opacity'], '1');
  s.reduce.matches = true; s.reduce.dispatchEvent(new Event('change'));
  assert.equal(s.frames.size, 0); assert.equal(s.video.paused, true); assert.equal(s.trail.children.length, 0);
  s.handler.onDestroy();
});

test('keyboard focus reveals a linked project immediately', () => {
  const s = setup({ observerSupport: true });
  const focus = new Event('focusin');
  Object.defineProperty(focus, 'target', { value: { closest: selector => selector === '.floating-project-card' ? s.cards[2] : null } });
  s.section.dispatchEvent(focus);
  assert.equal(s.cards[2].classList.contains('is-reveal-pending'), false);
  assert.equal(s.cards[2].style['--reveal-delay'], '0ms');
  assert.equal(s.observers[0].targets.has(s.cards[2]), false);
  s.handler.onDestroy();
});

test('CTA snaps once after scroll settles and cancels for user interaction or reduced motion', () => {
  const s = setup({ snap: true, withControl: false });
  s.section.top = 120;
  s.window.dispatchEvent(new Event('scroll')); assert.equal(s.timers.size, 1);
  for (const callback of [...s.timers.values()]) callback();
  assert.equal(s.scrolls.length, 1); assert.equal(s.scrolls[0].top, 620); assert.equal(s.scrolls[0].behavior, 'smooth');
  s.window.dispatchEvent(new Event('scroll')); assert.equal(s.timers.size, 0);
  s.section.top = 800; s.window.dispatchEvent(new Event('scroll'));
  s.section.top = 120; s.window.dispatchEvent(new Event('scroll'));
  s.window.dispatchEvent(new Event('wheel')); assert.equal(s.timers.size, 0);
  s.window.dispatchEvent(new Event('scroll'));
  s.reduce.matches = true; s.reduce.dispatchEvent(new Event('change')); assert.equal(s.timers.size, 0);
  s.handler.onDestroy();
  for (const options of [{ editing: true }, { reduced: true }, { snap: false }]) {
    const noSnap = setup({ snap: true, ...options });
    noSnap.window.dispatchEvent(new Event('scroll')); assert.equal(noSnap.timers.size, 0); noSnap.handler.onDestroy();
  }
});
