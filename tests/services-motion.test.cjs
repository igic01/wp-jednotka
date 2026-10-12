const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(__dirname + '/../assets/js/services.js', 'utf8');
const reference = fs.readFileSync(__dirname + '/../examples/js/services-reveal.js', 'utf8');

function setup({ original = false, editing = false, reduced = false, animate = 'yes', withVideos = false } = {}) {
  class Element extends EventTarget {
    constructor() { super(); this.dataset = {}; this.style = { setProperty(key,value) { this[key] = value; }, removeProperty(key) { delete this[key]; } }; this.attributes = {}; }
    setAttribute(key, value) { this.attributes[key] = value; }
    getBoundingClientRect() { return { top: this.top }; }
    contains(element) { return element === this; }
    closest(selector) { return selector === '.service-row' ? this : null; }
  }
  const cards = Array.from({ length: 6 }, (_, i) => { const card = new Element(); card.top = 500 + i * 250; return card; });
  const section = new Element(); section.dataset.animate = animate;
  const media = Array.from({ length: 2 }, () => {
    const video = new Element(); video.paused = true;
    video.play = () => { video.paused = false; video.dispatchEvent(new Event('play')); return Promise.resolve(); };
    video.pause = () => { video.paused = true; video.dispatchEvent(new Event('pause')); };
    return video;
  });
  const button = new Element(); button.dataset = { playLabel: 'Play service video', pauseLabel: 'Pause service video' };
  const arrows = cards.map(() => new Element());
  const wrappers = cards.map((card, i) => {
    const wrapper = new Element(); card.parentElement = wrapper;
    wrapper.contains = el => el === card || (i === 0 && el === button);
    wrapper.querySelectorAll = () => withVideos && i === 0 ? media : [];
    wrapper.querySelector = selector => selector === '.service-row' ? card : withVideos && i === 0 ? button : null;
    card.querySelector = () => arrows[i];
    return wrapper;
  });
  section.querySelectorAll = selector => selector === '.service-row' ? cards : selector === '.jn-service-item' ? wrappers : withVideos ? media : [];
  const reduce = new EventTarget(); reduce.matches = reduced;
  const frames = new Map(); let sequence = 0, hook, handler;
  const window = new EventTarget(); Object.assign(window, {
    innerHeight: 1000, matchMedia: () => reduce,
    requestAnimationFrame: fn => { frames.set(++sequence, fn); return sequence; }, cancelAnimationFrame: id => frames.delete(id),
    elementorFrontend: { isEditMode: () => editing, hooks: { addAction: (name, fn) => { hook = fn; } }, elementsHandler: { addHandler(Type, settings) { handler = new Type(); Object.assign(handler, settings); handler.onInit(); } } },
    elementorModules: { frontend: { handlers: { Base: class { onInit() {} onDestroy() {} } } } },
  });
  const document = { querySelectorAll: () => cards, activeElement: null };
  vm.runInNewContext(original ? reference : source, { window, document, AbortController, elementorFrontend: window.elementorFrontend, elementorModules: window.elementorModules, jQuery: () => ({ on() {} }) });
  if (!original) hook([{ querySelector: () => section }]);
  return { cards, arrows, section, window, document, frames, handler, reduce, media, button,
    scroll(offset) { cards.forEach((card, i) => { card.top = 500 + i * 250 - offset; }); window.dispatchEvent(new Event('scroll')); const queued = [...frames.values()]; frames.clear(); queued.forEach(fn => fn()); },
    styles() { return cards.map((card,i) => [card.style.opacity,card.style.filter,card.style.transform,arrows[i].style['--arrow-scroll-x'],arrows[i].style['--arrow-scroll-y']]); },
  };
}

test('matches reference service reveal and arrow offsets throughout scrolling', () => {
  const widget = setup(), original = setup({ original: true });
  for (const offset of [0, 300, 900, 1800, -300, 600]) {
    widget.scroll(offset); original.scroll(offset);
    assert.deepEqual(widget.styles(), original.styles());
  }
  widget.handler.onDestroy();
});

test('editor, disabled reveal, and reduced motion keep all services visible', () => {
  for (const config of [{ editing: true }, { reduced: true }, { animate: '' }]) {
    const s = setup(config); s.scroll(-500);
    assert.ok(s.cards.every(card => card.style.opacity === '' && card.style.filter === '' && card.style.transform === ''));
    s.handler.onDestroy();
  }
});

test('keyboard-focused card remains visible while scrolling and teardown removes pending work', () => {
  const s = setup(); s.document.activeElement = s.cards[4]; s.scroll(-500);
  assert.equal(s.cards[4].style.opacity, '1.000'); assert.equal(s.cards[4].style.filter, 'blur(0.00px)');
  s.window.dispatchEvent(new Event('scroll')); assert.equal(s.frames.size, 1);
  s.handler.onDestroy(); assert.equal(s.frames.size, 0);
  assert.ok(s.cards.every(card => card.style.opacity === ''));
  assert.ok(s.arrows.every(arrow => arrow.style['--arrow-scroll-x'] === undefined));
  s.window.dispatchEvent(new Event('scroll')); assert.equal(s.frames.size, 0);
});

test('video controls pause both service and arrow media; reduced motion pauses and clears reveal', () => {
  const s = setup({ withVideos: true }); assert.ok(s.media.every(video => !video.paused));
  assert.equal(s.button.attributes['aria-label'], 'Pause service video');
  s.button.dispatchEvent(new Event('click')); assert.ok(s.media.every(video => video.paused));
  s.button.dispatchEvent(new Event('click')); assert.ok(s.media.every(video => !video.paused));
  s.reduce.matches = true; s.reduce.dispatchEvent(new Event('change'));
  assert.ok(s.media.every(video => video.paused)); assert.ok(s.cards.every(card => card.style.opacity === ''));
  s.handler.onDestroy();
  for (const config of [{ editing: true }, { reduced: true }]) {
    const paused = setup({ ...config, withVideos: true }); assert.ok(paused.media.every(video => video.paused)); paused.handler.onDestroy();
  }
});

test('service clicks do not navigate while editing', () => {
  const s = setup({ editing: true });
  const event = new Event('click', { cancelable: true }); Object.defineProperty(event, 'target', { value: s.cards[0] });
  s.section.dispatchEvent(event); assert.equal(event.defaultPrevented, true); s.handler.onDestroy();
});
