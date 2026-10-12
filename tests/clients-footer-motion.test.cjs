const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(__dirname + '/../assets/js/clients-footer.js', 'utf8');
const reference = fs.readFileSync(__dirname + '/../examples/js/floating-projects.js', 'utf8');

function setup({ original = false, editing = false, reduced = false, disabled = false, withVideo = false } = {}) {
  class Element extends EventTarget {
    constructor() {
      super(); this.dataset = {}; this.attributes = {}; this.children = [];
      this.style = { setProperty(key, value) { this[key] = value; } };
    }
    setAttribute(key, value) { this.attributes[key] = value; }
    getBoundingClientRect() { return { left: 100, top: 200, width: 1000, height: 1400 }; }
    appendChild(child) { this.children.push(child); child.parent = this; }
    remove() { if (this.parent) this.parent.children = this.parent.children.filter(child => child !== this); }
    closest(selector) { return selector === 'a' ? this : null; }
  }
  const section = new Element(); section.dataset = { drift: disabled ? '' : 'yes', parallax: disabled ? '' : 'yes', trail: disabled ? '' : 'yes' };
  const cards = Array.from({ length: 6 }, () => new Element());
  const trail = new Element(); const control = new Element(); control.dataset = { playLabel: 'Play animations', pauseLabel: 'Pause animations' };
  const form = new Element(); const submit = new Element(); const notice = new Element(); notice.hidden = true;
  form.querySelector = selector => selector === 'button' ? submit : notice;
  const video = new Element(); video.paused = true;
  video.play = () => { video.paused = false; return Promise.resolve(); };
  video.pause = () => { video.paused = true; };
  section.querySelector = selector => ({ '.cursor-trail-layer': trail, '.jn-clients-footer__motion': control, '.footer-newsletter': form })[selector];
  section.querySelectorAll = selector => selector === '.floating-project-card' ? cards : withVideo ? [video] : [];
  const document = new EventTarget(); document.hidden = false; document.querySelector = () => section; document.createElement = () => new Element();
  const reduce = new EventTarget(); reduce.matches = reduced;
  const frames = new Map(), timers = new Map(); let sequence = 0, hook, handler;
  const window = new EventTarget(); Object.assign(window, {
    matchMedia: () => reduce,
    requestAnimationFrame: fn => { frames.set(++sequence, fn); return sequence; }, cancelAnimationFrame: id => frames.delete(id),
    setTimeout: fn => { timers.set(++sequence, fn); return sequence; }, clearTimeout: id => timers.delete(id),
    elementorFrontend: { isEditMode: () => editing, hooks: { addAction: (name, fn) => { hook = fn; } }, elementsHandler: { addHandler(Type, settings) { handler = new Type(); Object.assign(handler, settings); handler.onInit(); } } },
    elementorModules: { frontend: { handlers: { Base: class { onInit() {} onDestroy() {} } } } },
  });
  const math = Object.create(Math); math.random = () => .5;
  vm.runInNewContext(original ? reference : source, { window, document, Math: math, AbortController, elementorFrontend: window.elementorFrontend, elementorModules: window.elementorModules, jQuery: () => ({ on() {} }) });
  if (!original) hook([{ querySelector: () => section }]);
  return { section, cards, trail, control, form, submit, notice, video, document, reduce, frames, timers, handler,
    tick(time) { const queued = [...frames.values()]; frames.clear(); queued.forEach(fn => fn(time)); },
    move(x = 450, y = 800, timeStamp = 100) { const event = new Event('pointermove'); Object.defineProperties(event, { clientX: { value: x }, clientY: { value: y }, timeStamp: { value: timeStamp } }); section.dispatchEvent(event); },
    offsets() { return cards.map(card => [card.style['--float-card-drift-x'], card.style['--float-card-drift-y']]); },
  };
}

test('floating offsets, pointer parallax, and trail match the reference', () => {
  const actual = setup(), expected = setup({ original: true });
  for (const time of [0, 1200, 4200, 9600]) { actual.tick(time); expected.tick(time); assert.deepEqual(actual.offsets(), expected.offsets()); }
  actual.move(); expected.move();
  for (const key of ['--float-x','--float-y']) assert.equal(actual.section.style[key], expected.section.style[key]);
  assert.equal(actual.trail.children.length, 1);
  for (const key of ['left','top','--trail-size']) assert.equal(actual.trail.children[0].style[key], expected.trail.children[0].style[key]);
  actual.move(500, 800, 110); assert.equal(actual.trail.children.length, 1, 'trail throttled');
  for (const callback of actual.timers.values()) callback();
  assert.equal(actual.trail.children.length, 0); assert.equal(actual.timers.size, 0);
  actual.handler.onDestroy();
});

test('pause and teardown stop frames, videos, pointer movement, and trail timers', () => {
  const s = setup({ withVideo: true }); s.tick(100); s.move();
  assert.equal(s.video.paused, false); assert.equal(s.timers.size, 1);
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
  const focus = new Event('focusin'); Object.defineProperty(focus, 'target', { value: { closest: () => ({}) } });
  s.section.dispatchEvent(focus); assert.equal(s.frames.size, 0);
  s.section.dispatchEvent(new Event('focusout')); assert.equal(s.frames.size, 1); assert.equal(s.video.paused, true);
  const click = new Event('click', { cancelable: true }); Object.defineProperty(click, 'target', { value: s.control });
  s.section.dispatchEvent(click); assert.equal(click.defaultPrevented, true); s.handler.onDestroy();
});
