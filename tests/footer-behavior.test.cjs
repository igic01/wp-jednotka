const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
function setup(editing = false) {
  const form = new EventTarget(), button = new EventTarget(), root = new EventTarget();
  const notice = { hidden: true }, media = new EventTarget(), document = new EventTarget();
  media.matches = false; document.hidden = false;
  const video = { playing: false, play() { this.playing = true; return Promise.resolve(); }, pause() { this.playing = false; } };
  form.querySelector = selector => selector === 'button' ? button : notice;
  root.querySelector = () => form; root.querySelectorAll = () => [video];
  let handler;
  const window = { matchMedia: () => media, elementorFrontend: {
    isEditMode: () => editing,
    hooks: { addAction(name, callback) { assert.equal(name, 'frontend/element_ready/jednotka-footer.default'); callback([{ querySelector: () => root }]); } },
    elementsHandler: { addHandler(Type, args) { handler = new Type(); Object.assign(handler,args); handler.onInit(); } },
  }, elementorModules: { frontend: { handlers: { Base: class { onInit() {} onDestroy() {} } } } } };
  vm.runInNewContext(fs.readFileSync(__dirname+'/../assets/js/footer.js','utf8'), { window, document, AbortController, elementorFrontend: window.elementorFrontend, elementorModules: window.elementorModules, jQuery: () => ({on(){}}) });
  return { form, button, notice, video, media, document, handler };
}
test('newsletter click, Enter and form submit show notice without submitting', () => {
  const s = setup();
  for (const [target,type] of [[s.button,'click'],[s.form,'submit'],[s.form,'keydown']]) {
    s.notice.hidden = true;
    const event = new Event(type,{cancelable:true}); if(type==='keydown')Object.defineProperty(event,'key',{value:'Enter'});
    target.dispatchEvent(event);assert.equal(event.defaultPrevented,true);assert.equal(s.notice.hidden,false);
  }
  s.handler.onDestroy();s.notice.hidden=true;s.button.dispatchEvent(new Event('click'));assert.equal(s.notice.hidden,true);
});
test('logo videos stop for reduced motion, hidden tabs, and teardown', () => {
  const s=setup();assert.equal(s.video.playing,true);
  s.media.matches=true;s.media.dispatchEvent(new Event('change'));assert.equal(s.video.playing,false);
  s.media.matches=false;s.media.dispatchEvent(new Event('change'));assert.equal(s.video.playing,true);
  s.document.hidden=true;s.document.dispatchEvent(new Event('visibilitychange'));assert.equal(s.video.playing,false);
  s.document.hidden=false;s.document.dispatchEvent(new Event('visibilitychange'));assert.equal(s.video.playing,true);
  s.handler.onDestroy();assert.equal(s.video.playing,false);
});
test('editor keeps video paused and newsletter notice hidden', () => {
  const s=setup(true);assert.equal(s.video.playing,false);const e=new Event('submit',{cancelable:true});s.form.dispatchEvent(e);assert.equal(e.defaultPrevented,true);assert.equal(s.notice.hidden,true);s.handler.onDestroy();
});
