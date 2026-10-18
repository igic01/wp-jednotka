const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
function setup({ editing = false, valid = true, fetch } = {}) {
  const button = { disabled: false }, status = { dataset: {}, hidden: true };
  const form = new EventTarget(); form.action = '/wp-admin/admin-ajax.php'; form.attrs = {}; form.resets = 0;
  form.reportValidity = () => valid; form.reset = () => form.resets++;
  form.setAttribute = (key, value) => { form.attrs[key] = value; }; form.removeAttribute = key => { delete form.attrs[key]; };
  form.querySelector = selector => selector.startsWith('button') ? button : status;
  const calls = []; let handler, hook;
  const window = { fetch: (...args) => { calls.push(args); return fetch(...args); },
    elementorFrontend: { isEditMode: () => editing, hooks: { addAction: (name, fn) => { hook = fn; } }, elementsHandler: { addHandler(Type, args) { handler = new Type(); Object.assign(handler, args); handler.onInit(); } } },
    elementorModules: { frontend: { handlers: { Base: class { onInit() {} onDestroy() {} } } } },
  };
  vm.runInNewContext(fs.readFileSync(__dirname + '/../assets/js/contact.js', 'utf8'), { window, AbortController, FormData: class { constructor(form) { this.form = form; } }, elementorFrontend: window.elementorFrontend, elementorModules: window.elementorModules, jQuery: () => ({ on() {} }) });
  hook([{ querySelector: () => form }]);
  return { form, button, status, handler, calls, submit() { const event = new Event('submit', { cancelable: true }); form.dispatchEvent(event); assert.equal(event.defaultPrevented, true); }, flush: () => new Promise(resolve => setImmediate(resolve)) };
}
test('successful submission sends once, locks the button and resets only after confirmation', async () => {
  let resolve;
  const s = setup({ fetch: () => new Promise(r => { resolve = r; }) });
  s.submit(); s.submit(); assert.equal(s.calls.length, 1); assert.equal(s.button.disabled, true); assert.equal(s.form.resets, 0);
  assert.equal(s.calls[0][1].credentials, 'same-origin');
  resolve({ ok: true, json: async () => ({ success: true, data: { message: 'Sent' } }) }); await s.flush();
  assert.equal(s.status.textContent, 'Sent'); assert.equal(s.form.resets, 1); assert.equal(s.button.disabled, false); assert.equal(s.form.attrs['aria-busy'], undefined); s.handler.onDestroy();
});
test('server, network, and non-JSON errors retain the entered data and permit another attempt', async () => {
  for (const fetch of [async () => ({ ok: false, json: async () => ({ success: false, data: { message: 'Try later' } }) }), async () => { throw new Error('offline'); }, async () => ({ ok: true, json: async () => { throw new Error('HTML response'); } })]) {
    const s = setup({ fetch }); s.submit(); await s.flush();
    assert.equal(s.status.dataset.error, 'yes'); assert.equal(s.form.resets, 0); assert.equal(s.button.disabled, false); s.handler.onDestroy();
  }
});
test('editor and invalid forms never send, and destruction aborts an active submission', async () => {
  for (const options of [{ editing: true }, { valid: false }]) {
    const s = setup({ ...options, fetch: () => { throw new Error('unexpected fetch'); } }); s.submit(); await s.flush(); assert.equal(s.calls.length, 0); s.handler.onDestroy();
  }
  const s = setup({ fetch: (url, { signal }) => new Promise((resolve, reject) => signal.addEventListener('abort', () => reject(Object.assign(new Error('aborted'), { name: 'AbortError' })))) });
  s.submit(); s.handler.onDestroy(); await s.flush(); assert.equal(s.calls[0][1].signal.aborted, true); assert.notEqual(s.status.dataset.error, 'yes');
  s.form.dispatchEvent(new Event('submit')); assert.equal(s.calls.length, 1);
});
