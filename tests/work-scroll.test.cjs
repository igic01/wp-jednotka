const test = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
function setup({ editing = false, reduced = false, animate = 'yes', empty = false } = {}) {
  const classes = new Set(['is-active']);
  const timeline = { dataset: {}, top: 900, reads: 0, getBoundingClientRect() { this.reads++; return { top: this.top }; }, classList: { add: k => classes.add(k), remove: k => classes.delete(k), toggle(k, yes) { if (yes) classes.add(k); else classes.delete(k); }, contains: k => classes.has(k) } };
  const section = { dataset: { animate }, querySelector: () => empty ? null : timeline };
  const document = new EventTarget(); document.hidden = false;
  const media = new EventTarget(); media.matches = reduced;
  const frames = new Map(); let id = 0, hook, handler;
  const window = new EventTarget(); Object.assign(window, { innerHeight: 1000, matchMedia: () => media,
    requestAnimationFrame: fn => { frames.set(++id, fn); return id; }, cancelAnimationFrame: id => frames.delete(id),
    elementorFrontend: { isEditMode: () => editing, hooks: { addAction: (name, fn) => { hook = fn; } }, elementsHandler: { addHandler(Type, args) { handler = new Type(); Object.assign(handler, args); handler.onInit(); } } },
    elementorModules: { frontend: { handlers: { Base: class { onInit() {} onDestroy() {} } } } },
  });
  vm.runInNewContext(fs.readFileSync(__dirname + '/../assets/js/work.js', 'utf8'), { window, document, AbortController, elementorFrontend: window.elementorFrontend, elementorModules: window.elementorModules, jQuery: () => ({ on() {} }) });
  hook([{ querySelector: () => section }]);
  return { timeline, window, document, media, handler, frames,
    flush() { const queued = [...frames.values()]; frames.clear(); queued.forEach(fn => fn()); },
  };
}
test('timeline draws below the 78% line and rewinds above it, batching scroll events', () => {
  const s = setup(); assert.equal(s.timeline.classList.contains('is-active'), false);
  s.timeline.top = 779; s.window.dispatchEvent(new Event('scroll')); s.window.dispatchEvent(new Event('scroll')); s.window.dispatchEvent(new Event('resize'));
  assert.equal(s.frames.size, 1); const reads = s.timeline.reads; s.flush(); assert.equal(s.timeline.reads, reads + 1); assert.equal(s.timeline.classList.contains('is-active'), true);
  s.timeline.top = 780; s.window.dispatchEvent(new Event('scroll')); s.flush(); assert.equal(s.timeline.classList.contains('is-active'), false); s.handler.onDestroy();
});
test('editor, disabled animation and reduced motion are readable without scroll frames', () => {
  for (const options of [{ editing: true }, { reduced: true }, { animate: '' }]) {
    const s = setup(options); assert.equal(s.timeline.classList.contains('is-active'), true); assert.equal(s.timeline.dataset.animated, 'no');
    s.window.dispatchEvent(new Event('scroll')); assert.equal(s.frames.size, 0); assert.equal(s.timeline.reads, 0); s.handler.onDestroy();
  }
  const s = setup(); s.window.dispatchEvent(new Event('scroll')); s.media.matches = true; s.media.dispatchEvent(new Event('change'));
  assert.equal(s.frames.size, 0); assert.equal(s.timeline.classList.contains('is-active'), true);
  s.media.matches = false; s.media.dispatchEvent(new Event('change')); assert.equal(s.timeline.classList.contains('is-active'), false); s.handler.onDestroy();
});
test('hidden tabs cancel queued updates and teardown restores the fallback and removes listeners', () => {
  const s = setup(); s.window.dispatchEvent(new Event('scroll')); s.document.hidden = true; s.document.dispatchEvent(new Event('visibilitychange')); assert.equal(s.frames.size, 0);
  s.window.dispatchEvent(new Event('scroll')); assert.equal(s.frames.size, 0);
  s.timeline.top = 100; s.document.hidden = false; s.document.dispatchEvent(new Event('visibilitychange')); assert.equal(s.timeline.classList.contains('is-active'), true);
  s.window.dispatchEvent(new Event('scroll')); s.handler.onDestroy(); assert.equal(s.frames.size, 0); assert.equal(s.timeline.dataset.animated, 'no');
  s.window.dispatchEvent(new Event('scroll')); assert.equal(s.frames.size, 0);
  setup({ empty: true }).handler.onDestroy();
});
