const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

function environment(ready = true) {
  const listeners = [];
  const hooks = [];
  const window = {
    document: { createElement() { return { set innerHTML(value) { this.textContent = value.replace(/<[^>]*>/g, '').replace(/&amp;/g, '&').replace(/&nbsp;/g, '\u00a0'); } }; } },
  };
  function initialize() {
    window.$e = {
      modules: { hookUI: { Before: class {
        register() { window.$e.hooks.registerUIBefore(this); }
        run(args) { if (this.getConditions(args)) return this.apply(args); }
      } } },
      hooks: { registerUIBefore: hook => hooks.push(hook) },
    };
  }
  if (ready) initialize();
  vm.runInNewContext(fs.readFileSync(path.join(__dirname, '../assets/js/editor.js'), 'utf8'), {
    window,
    jQuery: () => ({ on: (event, callback) => {
      assert.equal(event, 'elementor/init');
      listeners.push(callback);
    } }),
  });
  return {
    hooks,
    initialize,
    init: () => listeners.forEach(callback => callback()),

  };
}

test('initialization works before or after Elementor is ready and registers only once', () => {
  for (const ready of [false, true]) {
    const env = environment(ready);
    if (!ready) {
      assert.equal(env.hooks.length, 0);
      env.initialize();
    }
    env.init();
    env.init();
    assert.equal(env.hooks.length, 1);
  }
});


test('inline service benefits remain plain lines and other widget settings are untouched', () => {
  const env = environment();
  const hook = env.hooks.find(h => h.getCommand() === 'document/elements/settings');
  const widget = { model: { get: key => key === 'widgetType' ? 'jednotka-services' : null } };
  const args = { container: { parent: widget }, options: { external: true }, settings: { detail_bullets: '<div>First &amp; best<br>Second</div><div>Third&nbsp;point</div>' } };
  hook.run(args); assert.equal(args.settings.detail_bullets, 'First & best\nSecond\nThird point');
  const other = { container: {}, options: { external: true }, settings: { detail_bullets: '<b>Unchanged</b>' } };
  hook.run(other); assert.equal(other.settings.detail_bullets, '<b>Unchanged</b>');
  const panel = { ...args, options: {}, settings: { detail_bullets: '<b>Panel text</b>' } };
  hook.run(panel); assert.equal(panel.settings.detail_bullets, '<b>Panel text</b>');
});
