const test = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
function setup({ editing = false, reduced = false, mobile = false, auto = 'yes' } = {}) {
  const section = new EventTarget(); section.dataset = { auto }; section.contains = node => node === section;
  const document = new EventTarget(); document.hidden = false;
  const reduce = new EventTarget(); reduce.matches = reduced;
  const narrow = new EventTarget(); narrow.matches = mobile;
  let hook, handler, observer;
  const window = {
    matchMedia: query => query.includes('800px') ? narrow : reduce,
    IntersectionObserver: class {
      constructor(callback) { this.callback = callback; observer = this; }
      observe(target) { this.target = target; }
      disconnect() { this.target = null; }
    },
    elementorFrontend: { isEditMode: () => editing, hooks: { addAction: (name, fn) => { hook = fn; } }, elementsHandler: { addHandler(Type, args) { handler = new Type(); Object.assign(handler, args); handler.onInit(); } } },
    elementorModules: { frontend: { handlers: { Base: class { onInit() {} onDestroy() {} } } } },
  };
  vm.runInNewContext(fs.readFileSync(__dirname + '/../assets/js/projects.js', 'utf8'), { window, document, AbortController, elementorFrontend: window.elementorFrontend, elementorModules: window.elementorModules, jQuery: () => ({ on() {} }) });
  hook([{ querySelector: () => section }]);
  return { section, document, reduce, narrow, handler, observer };
}
test('project loops keep running during scrolling, hover, focus and visibility changes', () => {
  for (const mobile of [false, true]) {
    const s = setup({ mobile });
    for (const event of ['scroll', 'pointerenter', 'focusin', 'focusout']) {
      s.section.dispatchEvent(new Event(event));
      assert.equal(s.section.dataset.static, 'no');
      assert.equal(s.section.dataset.running, 'yes');
    }
    s.document.hidden = true; s.document.dispatchEvent(new Event('visibilitychange'));
    assert.equal(s.section.dataset.running, 'yes');
    assert.equal(s.observer, undefined);
    s.handler.onDestroy();
  }
});
test('editor, disabled scrolling and reduced motion retain the static gallery', () => {
  for (const options of [{ editing: true }, { reduced: true }, { auto: '' }]) {
    const s = setup(options); assert.equal(s.section.dataset.static, 'yes'); assert.equal(s.section.dataset.running, 'no'); s.handler.onDestroy();
  }
  const s = setup();
  s.reduce.matches = true; s.reduce.dispatchEvent(new Event('change')); assert.equal(s.section.dataset.static, 'yes');
  s.reduce.matches = false; s.reduce.dispatchEvent(new Event('change')); assert.equal(s.section.dataset.running, 'yes');
  s.handler.onDestroy();
  assert.equal(s.section.dataset.static, 'yes');
  s.reduce.dispatchEvent(new Event('change')); assert.equal(s.section.dataset.running, 'no');
});
