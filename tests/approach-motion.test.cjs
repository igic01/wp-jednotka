const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(__dirname + '/../assets/js/approach.js', 'utf8');
const referencePath = __dirname + '/../examples/js/gradient-wave-text.js';
const reference = fs.existsSync(referencePath) ? fs.readFileSync(referencePath, 'utf8') : null;

function setup({ original = false, editing = false, reduced = false, phone = false, animate = 'yes', final = '#ffffff', video = false } = {}) {
  class Element extends EventTarget {
    constructor() { super(); this.dataset = {}; this.attributes = {}; this.children = []; this.value = ''; this.style = { setProperty(key, value) { this[key] = value; } }; }
    set textContent(value) { this.value = value; this.children = []; }
    get textContent() { return this.value + this.children.map(child => child.textContent).join(''); }
    setAttribute(key, value) { this.attributes[key] = value; }
    getAttribute(key) { return this.attributes[key] ?? null; }
    removeAttribute(key) { delete this.attributes[key]; }
    appendChild(child) { this.children.push(child); }
    getBoundingClientRect() { return { top: this.top, height: 2400 }; }
    closest() { return section; }
  }
  const section = new Element(); section.top = 0;
  section.dataset = { animate, final, muted: '#585858', highlight: '#8a5cff' };
  const text = new Element(); text.textContent = 'Email, reklamu aj web riešime spolu. Výsledok je jednoduchý: viac ľudí, ktorí u vás nakúpia.';
  const originalText = text.textContent;
  const media = new Element(); media.paused = true;
  media.play = () => { media.paused = false; media.dispatchEvent(new Event('play')); return Promise.resolve(); };
  media.pause = () => { media.paused = true; media.dispatchEvent(new Event('pause')); };
  const button = new Element(); button.dataset = { playLabel: 'Play video', pauseLabel: 'Pause video' };
  section.querySelector = selector => selector === 'video' ? (video ? media : null) : selector === '.jn-approach__video-toggle' ? (video ? button : null) : text;
  const reduce = new EventTarget(); reduce.matches = reduced;
  const mobile = new EventTarget(); mobile.matches = phone;
  const frames = new Map(); let sequence = 0, hook, handler;
  const window = new EventTarget(); Object.assign(window, {
    innerHeight: 1000, matchMedia: query => query === '(max-width: 767px)' ? mobile : reduce,
    requestAnimationFrame: fn => { frames.set(++sequence, fn); return sequence; }, cancelAnimationFrame: id => frames.delete(id),
    elementorFrontend: { isEditMode: () => editing, hooks: { addAction: (name, fn) => { hook = fn; } }, elementsHandler: { addHandler(Type, settings) { handler = new Type(); Object.assign(handler, settings); handler.onInit(); } } },
    elementorModules: { frontend: { handlers: { Base: class { onInit() {} onDestroy() {} } } } },
  });
  const document = { createElement: () => new Element(), querySelectorAll: () => [text] };
  vm.runInNewContext(original ? reference : source, { window, document, AbortController, elementorFrontend: window.elementorFrontend, elementorModules: window.elementorModules, jQuery: () => ({ on() {} }) });
  if (!original) hook([{ querySelector: () => section }]);
  return { section, text, originalText, frames, handler, reduce, mobile, media, button, window,
    scroll(top) { section.top = top; window.dispatchEvent(new Event('scroll')); const queued = [...frames.values()]; frames.clear(); queued.forEach(fn => fn()); },
    colors() { return text.children.map(child => child.style['--wave-color']); },
  };
}

test('matches original wave colors at start, middle, end, and reverse scroll', { skip: reference === null ? 'Optional examples reference is not available' : false }, () => {
  const widget = setup(), original = setup({ original: true });
  for (const top of [0, -560, -1400, -200, 300]) {
    widget.scroll(top); original.scroll(top);
    assert.deepEqual(widget.colors(), original.colors());
  }
  assert.equal(widget.text.getAttribute('aria-label'), widget.originalText);
  assert.ok(widget.text.children.every(char => char.getAttribute('aria-hidden') === 'true'));
  widget.handler.onDestroy();
});

test('editor, disabled wave, and reduced motion leave text unsplit and editable', () => {
  for (const config of [{ editing: true }, { reduced: true }, { animate: '' }]) {
    const s = setup(config);
    assert.equal(s.text.children.length, 0);
    assert.equal(s.text.textContent, s.originalText);
    s.text.textContent = 'Client edit';
    s.handler.onDestroy(); assert.equal(s.text.textContent, 'Client edit');
  }
});

test('uses custom colors, responds to reduced motion, and removes pending work', () => {
  const s = setup({ final: '#123456' });
  s.scroll(-1400); assert.ok(s.colors().every(color => color === 'rgb(18 52 86)'));
  s.reduce.matches = true; s.reduce.dispatchEvent(new Event('change'));
  assert.equal(s.text.textContent, s.originalText); assert.equal(s.text.children.length, 0);
  s.reduce.matches = false; s.reduce.dispatchEvent(new Event('change'));
  assert.ok(s.text.children.length > 0);
  s.window.dispatchEvent(new Event('scroll')); assert.equal(s.frames.size, 1);
  s.handler.onDestroy(); assert.equal(s.frames.size, 0);
  assert.equal(s.text.getAttribute('aria-label'), null);
  s.window.dispatchEvent(new Event('scroll')); assert.equal(s.frames.size, 0);
});

test('phones show static text and restore the wave when resized to desktop', () => {
  const s = setup({ phone: true });
  assert.equal(s.text.children.length, 0);
  assert.equal(s.text.textContent, s.originalText);
  s.window.dispatchEvent(new Event('scroll'));
  assert.equal(s.frames.size, 0);
  s.mobile.matches = false; s.mobile.dispatchEvent(new Event('change'));
  assert.ok(s.text.children.length > 0);
  s.scroll(-1400);
  assert.ok(s.colors().every(color => color === 'rgb(255 255 255)'));
  s.window.dispatchEvent(new Event('scroll'));
  assert.equal(s.frames.size, 1);
  s.mobile.matches = true; s.mobile.dispatchEvent(new Event('change'));
  assert.equal(s.frames.size, 0);
  assert.equal(s.text.children.length, 0);
  assert.equal(s.text.textContent, s.originalText);
  assert.equal(s.text.getAttribute('aria-label'), null);
  s.handler.onDestroy();
  s.mobile.matches = false; s.mobile.dispatchEvent(new Event('change'));
  assert.equal(s.text.children.length, 0);
});

test('videos play/pause with accurate button state and stay paused in editor/reduced motion', () => {
  const s = setup({ video: true });
  assert.equal(s.media.paused, false); assert.equal(s.button.getAttribute('aria-label'), 'Pause video');
  s.button.dispatchEvent(new Event('click')); assert.equal(s.media.paused, true);
  s.button.dispatchEvent(new Event('click')); assert.equal(s.media.paused, false);
  s.reduce.matches = true; s.reduce.dispatchEvent(new Event('change')); assert.equal(s.media.paused, true);
  s.handler.onDestroy();
  for (const config of [{ editing: true }, { reduced: true }]) {
    const paused = setup({ ...config, video: true }); assert.equal(paused.media.paused, true); paused.handler.onDestroy();
  }
});
