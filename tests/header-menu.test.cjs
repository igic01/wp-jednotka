const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

function setup() {
  class Element extends EventTarget {
    constructor() {
      super(); this.attrs = {}; this.classes = new Set(); this.open = false;
      this.classList = { toggle: (key, on) => on ? this.classes.add(key) : this.classes.delete(key), contains: key => this.classes.has(key), remove: key => this.classes.delete(key) };
    }
    setAttribute(key, value) { this.attrs[key] = value; }
    focus() { this.focused = true; }
    showModal() { this.open = true; }
    close() { if (this.open) { this.open = false; this.dispatchEvent(new Event('close')); } }
    contains(target) { return target === this || Object.values(elements).includes(target); }
    closest(selector) { return selector === 'a' && this.link ? this : null; }
  }
  const elements = Object.fromEntries(['header', 'toggle', 'nav', 'menu', 'close', 'link'].map(key => [key, new Element()]));
  elements.link.link = true;
  elements.header.querySelector = selector => ({ '.jn-header__toggle': elements.toggle, '.jn-header__nav': elements.nav, '.jn-header__menu': elements.menu, '.jn-header__menu-close': elements.close })[selector] || null;
  const media = new EventTarget(); media.matches = true;
  const document = new EventTarget(); document.body = { style: { overflow: 'auto' } };
  let hook, handler;
  const frontend = { isEditMode: () => false, hooks: { addAction(name, callback) { hook = callback; } }, elementsHandler: { addHandler(Type, options) { handler = new Type(); Object.assign(handler, options); handler.onInit(); } } };
  const modules = { frontend: { handlers: { Base: class { onInit() {} onDestroy() {} } } } };
  vm.runInNewContext(fs.readFileSync(__dirname + '/../assets/js/header.js', 'utf8'), {
    window: { elementorFrontend: frontend, elementorModules: modules, matchMedia: () => media },
    elementorFrontend: frontend, elementorModules: modules, document, AbortController, jQuery: () => ({ on() {} }),
  });
  hook([{ querySelector: () => elements.header }]);
  return { ...elements, media, document, handler };
}

test('fullscreen menu locks scrolling, closes, and restores focus and scrolling', () => {
  const s = setup();
  s.toggle.dispatchEvent(new Event('click'));
  assert.equal(s.menu.open, true);
  assert.equal(s.toggle.attrs['aria-expanded'], 'true');
  assert.equal(s.document.body.style.overflow, 'hidden');
  s.close.dispatchEvent(new Event('click'));
  assert.equal(s.menu.open, false);
  assert.equal(s.toggle.focused, true);
  assert.equal(s.document.body.style.overflow, 'auto');
  s.toggle.dispatchEvent(new Event('click'));
  s.menu.close(); // Native Escape/cancel closure also synchronizes state.
  assert.equal(s.toggle.attrs['aria-expanded'], 'false');
  assert.equal(s.document.body.style.overflow, 'auto');
  s.handler.onDestroy();
});

test('resizing to desktop and destroying the widget release the modal and scroll lock', () => {
  const s = setup();
  s.toggle.dispatchEvent(new Event('click'));
  s.media.matches = false; s.media.dispatchEvent(new Event('change'));
  assert.equal(s.menu.open, false);
  assert.equal(s.document.body.style.overflow, 'auto');
  s.toggle.dispatchEvent(new Event('click'));
  assert.equal(s.menu.open, false);
  s.media.matches = true;
  s.toggle.dispatchEvent(new Event('click'));
  s.handler.onDestroy();
  assert.equal(s.menu.open, false);
  assert.equal(s.document.body.style.overflow, 'auto');
});

test('selecting a navigation link closes the menu', () => {
  const s = setup();
  s.toggle.dispatchEvent(new Event('click'));
  const event = new Event('click');
  Object.defineProperty(event, 'target', { value: s.link });
  s.nav.dispatchEvent(event);
  assert.equal(s.menu.open, false);
  assert.equal(s.document.body.style.overflow, 'auto');
  s.handler.onDestroy();
});
