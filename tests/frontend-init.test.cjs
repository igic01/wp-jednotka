const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

function environment() {
  const listeners = [];
  const actions = [];
  const context = {
    window: { addEventListener() {} },
    document: { readyState: 'loading', addEventListener() {} },
    jQuery: () => ({ on: (event, callback) => {
      assert.equal(event, 'elementor/frontend/init');
      listeners.push(callback);
    } }),
  };
  context.window.jQuery = context.jQuery;
  const ready = () => {
    context.elementorFrontend = context.window.elementorFrontend = {
      hooks: { addAction: (name, callback) => actions.push({ name, callback }) },
      elementsHandler: { addHandler() {} },
    };
    context.elementorModules = context.window.elementorModules = {
      frontend: { handlers: { Base: class {} } },
    };
  };
  const load = () => {
    for (const filename of ['header.js', 'hero.js', 'logo-strip.js', 'stats.js', 'approach.js', 'results.js', 'services.js', 'clients-footer.js', 'showcase.js', 'team.js', 'our-story.js', 'values.js', 'projects.js', 'contact.js', 'services-hero.js', 'work.js', 'faq.js', 'posts.js', 'footer.js', 'gallery.js', 'career-jobs.js']) {
      const source = fs.readFileSync(path.join(__dirname, '../assets/js', filename), 'utf8');
      vm.runInNewContext(source, context, { filename });
    }
  };
  const init = () => listeners.forEach(callback => callback());
  return { context, actions, ready, load, init };
}

function assertRegistered(env) {
  assert.deepEqual(env.actions.map(action => action.name), [
    'frontend/element_ready/jednotka-header.default',
    'frontend/element_ready/jednotka-hero.default',
    'frontend/element_ready/jednotka-services-hero.default',
    'frontend/element_ready/jednotka-hero.default',
    'frontend/element_ready/jednotka-logo-strip.default',
    'frontend/element_ready/jednotka-stats.default',
    'frontend/element_ready/jednotka-approach.default',
    'frontend/element_ready/jednotka-results.default',
    'frontend/element_ready/jednotka-services.default',
    'frontend/element_ready/jednotka-clients-footer.default',
    'frontend/element_ready/jednotka-showcase.default',
    'frontend/element_ready/jednotka-team.default',
    'frontend/element_ready/jednotka-our-story.default',
    'frontend/element_ready/jednotka-values.default',
    'frontend/element_ready/jednotka-projects.default',
    'frontend/element_ready/jednotka-contact.default',
    'frontend/element_ready/jednotka-services-hero.default',
    'frontend/element_ready/jednotka-work.default',
    'frontend/element_ready/jednotka-faq.default',
    'frontend/element_ready/jednotka-posts.default',
    'frontend/element_ready/jednotka-footer.default',
    'frontend/element_ready/jednotka-gallery.default',
    'frontend/element_ready/jednotka-career-jobs.default',
  ]);
}

test('global objects existing before hooks are initialized do not cause a crash', () => {
  const env = environment();
  env.ready();
  delete env.context.elementorFrontend.hooks;
  env.load();
  assert.equal(env.actions.length, 0);
  env.ready();
  env.init();
  assertRegistered(env);
  env.init();
  assertRegistered(env);
});

test('scripts can load before Elementor creates its global objects', () => {
  const env = environment();
  env.load();
  env.init();
  assert.equal(env.actions.length, 0);
  env.ready();
  env.init();
  assertRegistered(env);
});

test('late-loaded scripts register immediately and ignore duplicate init events', () => {
  const env = environment();
  env.ready();
  env.load();
  assertRegistered(env);
  env.init();
  assertRegistered(env);
});

for (const missing of ['elementsHandler', 'Base']) {
  test(`registration waits for ${missing} and can retry`, () => {
    const env = environment();
    env.ready();
    if (missing === 'Base') delete env.context.elementorModules.frontend.handlers.Base;
    else delete env.context.elementorFrontend.elementsHandler;
    env.load();
    assert.equal(env.actions.length, 0);
    env.ready();
    env.init();
    assertRegistered(env);
  });
}
