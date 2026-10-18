const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(__dirname + '/../assets/js/our-story.js', 'utf8');
function setup({ editing = false, reduced = false, animate = true, colors = {} } = {}) {
  class Element extends EventTarget {
    constructor() { super(); this.children = []; this.dataset = {}; this.attributes = {}; this.value = ''; this.top = 0; this.style = { setProperty(key, value) { this[key] = value; } }; }
    set textContent(value) { this.value = value; this.children = []; }
    get textContent() { return this.children.length ? this.children.map(c => c.textContent).join('') : this.value; }
    setAttribute(k, v) { this.attributes[k] = v; }
    getAttribute(k) { return this.attributes[k] ?? null; }
    removeAttribute(k) { delete this.attributes[k]; }
    appendChild(c) { this.children.push(c); }
    getBoundingClientRect() { return { top: this.top, height: 3200 }; }
  }
  const section = new Element(), text = new Element();
  const original = 'Email, reklamu aj web riešime spolu.  Spoločný cieľ.';
  text.textContent = original;
  section.dataset = { animate: animate ? 'yes' : '', final: '#FFFFFF', muted: '#585858', highlight: '#8A5CFF', ...colors };
  const photos = [[.02,.27],[.27,.52],[.52,.77],[.77,.98]].map(([start,end]) => { const e = new Element(); e.dataset = { start:String(start),end:String(end) }; return e; });
  section.querySelector = () => text; section.querySelectorAll = () => photos;
  const document = new EventTarget(); document.hidden = false; document.createElement = () => new Element();
  const media = new EventTarget(); media.matches = reduced;
  const frames = new Map(), observers = []; let next = 0, hook, handler;
  const window = new EventTarget(); Object.assign(window, {
    innerHeight: 1000, matchMedia: () => media,
    requestAnimationFrame: fn => { frames.set(++next, fn); return next; }, cancelAnimationFrame: id => frames.delete(id),
    IntersectionObserver: class { constructor(fn) { this.fn = fn; this.targets = new Set(); observers.push(this); } observe(e) { this.targets.add(e); } disconnect() { this.targets.clear(); } },
    elementorFrontend: { isEditMode: () => editing, hooks: { addAction: (name, fn) => { assert.equal(name,'frontend/element_ready/jednotka-our-story.default'); hook = fn; } }, elementsHandler: { addHandler(Type, args) { handler = new Type(); Object.assign(handler, args); handler.onInit(); } } },
    elementorModules: { frontend: { handlers: { Base: class { onInit(){} onDestroy(){} } } } },
  });
  vm.runInNewContext(source, { window, document, AbortController, elementorFrontend: window.elementorFrontend, elementorModules: window.elementorModules, jQuery: () => ({ on() {} }) });
  hook([{ querySelector: () => section }]);
  return { section, text, photos, document, media, frames, observers, original, handler,
    flush() { const queued = [...frames.values()]; frames.clear(); queued.forEach(fn => fn()); },
    scroll(progress) { section.top = -progress * 2200; window.dispatchEvent(new Event('scroll')); this.flush(); },
    queue() { window.dispatchEvent(new Event('scroll')); },
  };
}

test('photos appear one at a time in their reference scroll windows', () => {
  const s = setup();
  for (const [index, progress] of [.145,.395,.645,.875].entries()) {
    s.scroll(progress);
    s.photos.forEach((photo,i) => assert.equal(photo.style['--visibility'], i===index?'1.000':'0.000'));
  }
  s.scroll(0); assert.ok(s.photos.every(p => p.style['--visibility']==='0.000'));
  s.scroll(1); assert.ok(s.photos.every(p => p.style['--visibility']==='0.000')); s.handler.onDestroy();
});

test('text wave preserves accessible statement and reveals final colors when complete', () => {
  const s = setup();
  assert.equal(s.text.attributes['aria-label'], s.original.replace(/\s+/g,' ').trim());
  assert.ok(s.text.children.every(c => c.attributes['aria-hidden']==='true'));
  s.scroll(1); assert.ok(s.text.children.every(c => c.style['--wave-color']==='rgb(255 255 255)'));
  s.handler.onDestroy(); assert.equal(s.text.textContent,s.original); assert.equal(s.text.getAttribute('aria-label'),null);
});

test('custom reveal color is used by the letter wave', () => {
  const s = setup({ colors:{final:'#00FF00'} }); s.scroll(1);
  assert.ok(s.text.children.every(c => c.style['--wave-color']==='rgb(0 255 0)')); s.handler.onDestroy();
});

test('editor, disabled motion, and reduced motion provide a readable static section', () => {
  for (const options of [{editing:true},{reduced:true},{animate:false}]) {
    const s = setup(options); assert.equal(s.section.attributes['data-static'],'yes');
    assert.equal(s.text.textContent,s.original); assert.equal(s.text.children.length,0);
    assert.ok(s.photos.every(p => p.style['--visibility']==='1')); s.queue(); assert.equal(s.frames.size,0); s.handler.onDestroy();
  }
});

test('scroll updates batch per frame and stop offscreen, in hidden tabs, or after teardown', () => {
  const s = setup(); s.queue(); s.queue(); assert.equal(s.frames.size,1);
  s.observers[0].fn([{isIntersecting:false}]); assert.equal(s.frames.size,0); s.queue(); assert.equal(s.frames.size,0);
  s.observers[0].fn([{isIntersecting:true}]); assert.equal(s.frames.size,1);
  s.document.hidden=true; s.document.dispatchEvent(new Event('visibilitychange')); assert.equal(s.frames.size,0);
  s.document.hidden=false; s.document.dispatchEvent(new Event('visibilitychange')); assert.equal(s.frames.size,1);
  s.handler.onDestroy(); assert.equal(s.frames.size,0); assert.equal(s.observers[0].targets.size,0); s.queue(); assert.equal(s.frames.size,0);
});

test('changing motion preferences restores text and images, and can re-enable the wave', () => {
  const s = setup(); s.queue(); s.media.matches=true; s.media.dispatchEvent(new Event('change'));
  assert.equal(s.frames.size,0); assert.equal(s.text.textContent,s.original); assert.equal(s.section.attributes['data-static'],'yes');
  s.media.matches=false; s.media.dispatchEvent(new Event('change')); assert.ok(s.text.children.length>0); assert.equal(s.section.attributes['data-static'],'no'); s.handler.onDestroy();
});
