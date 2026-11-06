const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

class FakeFormData {
  constructor(form) { this.items = form ? [...form.entries] : []; }
  append(key, value) { this.items.push([key, value]); }
  [Symbol.iterator]() { return this.items[Symbol.iterator](); }
}

function setup({ fetch, entries = [] } = {}) {
  const button = { disabled: false };
  const form = new EventTarget();
  form.entries = entries; form.kept = null; form.added = [];
  form.querySelector = () => button;
  form.append = element => form.added.push(element);
  form.replaceChildren = element => { form.kept = element; };
  const calls = [];
  const window = {
    jednotkaContact: { url: '/wp-admin/admin-ajax.php', nonce: 'abc' },
    fetch: (...args) => { calls.push(args); return fetch(...args); },
  };
  const document = {
    querySelectorAll: () => [form],
    createElement: () => ({ style: {}, setAttribute() {}, hidden: false }),
  };
  vm.runInNewContext(fs.readFileSync(__dirname + '/../assets/js/contact-page.js', 'utf8'), { window, document, FormData: FakeFormData });
  return { form, button, calls, status: form.added[0] };
}

const submit = async (form, prevented = false) => {
  const event = new Event('submit', { cancelable: true });
  if (prevented) event.preventDefault();
  form.dispatchEvent(event);
  await new Promise(resolve => setTimeout(resolve));
};

test('invalid forms cancelled by the block script are not sent', async () => {
  const s = setup({ fetch: async () => ({}) });
  await submit(s.form, true);
  assert.equal(s.calls.length, 0);
});

test('maps block fields to the handler and skips consent and empty files', async () => {
  const entries = [['meno', 'Jana'], ['email', 'j@example.com'], ['telefon', '123'], ['oblast', 'Iné'], ['subor', { size: 0 }], ['sprava', 'Ahoj'], ['suhlas', 'on']];
  const s = setup({ entries, fetch: async () => ({ ok: true, json: async () => ({ success: true, data: { message: 'Ďakujeme' } }) }) });
  await submit(s.form);
  const sent = Object.fromEntries(s.calls[0][1].body.items);
  assert.deepEqual({ ...sent }, { name: 'Jana', email: 'j@example.com', phone: '123', interest: 'Iné', message: 'Ahoj', action: 'jednotka_contact', nonce: 'abc' });
  assert.equal(s.status.textContent, 'Ďakujeme');
  assert.equal(s.form.kept, s.status);
});

test('sends a chosen file as the attachment', async () => {
  const file = { size: 5 };
  const s = setup({ entries: [['subor', file]], fetch: async () => ({ ok: true, json: async () => ({ success: true, data: {} }) }) });
  await submit(s.form);
  assert.ok(s.calls[0][1].body.items.some(([key, value]) => key === 'attachment' && value === file));
});

test('server and network failures keep the form and show an error', async () => {
  const rejected = setup({ fetch: async () => ({ ok: false, json: async () => ({ success: false, data: { message: 'Zlý súbor' } }) }) });
  await submit(rejected.form);
  assert.equal(rejected.status.textContent, 'Zlý súbor');
  assert.equal(rejected.form.kept, null);
  assert.equal(rejected.button.disabled, false);
  const offline = setup({ fetch: async () => { throw new Error('offline'); } });
  await submit(offline.form);
  assert.match(offline.status.textContent, /nepodarilo/);
  assert.equal(offline.status.style.color, '#ff6b7d');
});
