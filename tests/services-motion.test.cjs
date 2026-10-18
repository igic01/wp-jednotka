const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(__dirname + '/../assets/js/services.js', 'utf8');
const reference = fs.readFileSync(__dirname + '/../examples/js/services-reveal.js', 'utf8');

function setup({ original = false, editing = false, reduced = false, animate = 'yes', withVideos = false, withDetails = false, hash = '', preview = false } = {}) {
  class Element extends EventTarget {
    constructor() { super(); this.dataset = {}; this.style = { setProperty(key,value) { this[key] = value; }, removeProperty(key) { delete this[key]; } }; this.attributes = {}; const classes = new Set(); this.classList = { add: (...keys) => keys.forEach(k=>classes.add(k)), remove: (...keys) => keys.forEach(k=>classes.delete(k)), contains: k=>classes.has(k), toggle(k, yes) { if(yes) classes.add(k); else classes.delete(k); } }; }
    setAttribute(key, value) { this.attributes[key] = value; }
    removeAttribute(key) { delete this.attributes[key]; }
    focus() { document.activeElement = this; }
    scrollIntoView(options) { this.scrolled = options; }
    getBoundingClientRect() { return { top: this.top }; }
    contains(element) { return element === this; }
    closest(selector) { if(selector.includes('[data-sv-open]')) return (this.dataset.svOpen || this.dataset.svEditorOpen) ? this : null; if(selector === '[data-sv-close]')return this.isBack ? this : null; if(selector === '[data-sv-edit]')return this.dataset.svEdit ? this : null; return selector === '.service-row' ? this : null; }
  }
  const cards = Array.from({ length: 6 }, (_, i) => { const card = new Element(); card.top = 500 + i * 250; return card; });
  const section = new Element(); section.dataset.animate = animate; section.dataset.editorPreview = preview ? 'yes' : ''; section.dataset.previewIndex = '2'; section.top=0;
  const list = new Element(), panel = new Element(), viewport = new Element(), back = new Element(); back.isBack=true; list.offsetHeight=1500; panel.offsetHeight=900;
  const details = ['web','email'].map((id,index)=>{const d=new Element();d.dataset={svDetail:id,svHash:'jn-services-test-'+id,serviceIndex:String(index),defaultHeading:'Detail '+id,defaultBullets:'First\nSecond',defaultCta:'Contact '+id};d.hidden=true;return d;});
  const openers = details.map(d=>{const b=new Element();b.dataset.svOpen=d.dataset.svDetail;return b;});
  section.querySelector = selector => withDetails ? ({ '[data-sv-list]':list, '[data-sv-panel]':panel, '.sv-viewport':viewport, '[data-sv-close]':back })[selector]||null : null;
  section.contains = node => [section,...openers,back,...details].includes(node);
  const media = Array.from({ length: 2 }, () => {
    const video = new Element(); video.paused = true;
    video.play = () => { video.paused = false; video.dispatchEvent(new Event('play')); return Promise.resolve(); };
    video.pause = () => { video.paused = true; video.dispatchEvent(new Event('pause')); };
    return video;
  });
  const button = new Element(); button.dataset = { playLabel: 'Play service video', pauseLabel: 'Pause service video' };
  const arrows = cards.map(() => new Element());
  const wrappers = cards.map((card, i) => {
    const wrapper = new Element(); card.parentElement = wrapper;
    wrapper.contains = el => el === card || (i === 0 && el === button);
    wrapper.querySelectorAll = () => withVideos && i === 0 ? media : [];
    wrapper.querySelector = selector => selector === '.service-row' ? card : withVideos && i === 0 ? button : null;
    card.querySelector = () => arrows[i];
    return wrapper;
  });
  section.querySelectorAll = selector => selector === '.sv-cta[href]' ? [] : selector === '[data-sv-detail]' ? (withDetails ? details : []) : selector.includes('[data-sv-open]') ? openers : selector === '.service-row' ? cards : selector === '.jn-service-item' ? wrappers : withVideos ? media : [];
  const reduce = new EventTarget(); reduce.matches = reduced;
  const frames = new Map(); let sequence = 0, hook, handler;
  const window = new EventTarget(); Object.assign(window, {
    location: { pathname:'/services/',search:'?test=1',hash }, history: { pushState(state,title,url) { const u=new URL(url, 'https://example.test/services/?test=1');Object.assign(window.location,{pathname:u.pathname,search:u.search,hash:u.hash}); } }, innerHeight: 1000, matchMedia: () => reduce,
    requestAnimationFrame: fn => { frames.set(++sequence, fn); return sequence; }, cancelAnimationFrame: id => frames.delete(id),
    elementorFrontend: { isEditMode: () => editing, hooks: { addAction: (name, fn) => { hook = fn; } }, elementsHandler: { addHandler(Type, settings) { handler = new Type(); Object.assign(handler, settings); handler.onInit(); } } },
    elementorModules: { frontend: { handlers: { Base: class { onInit() {} onDestroy() {} } } } },
  });
  const models = details.map((d,index)=>({ values:{detail_cta:index===0?'Custom CTA':''},get(key){return this.values[key];},set(key,value){this.values[key]=value;} }));
  window.parent={elementor:{getContainer:()=>({settings:{get:()=>({at:index=>models[index]})}})}};
  const document = new EventTarget(); Object.assign(document,{querySelectorAll:()=>cards,querySelector:()=>section,activeElement:null,hidden:false});
  vm.runInNewContext(original ? reference : source, { window, document, AbortController, elementorFrontend: window.elementorFrontend, elementorModules: window.elementorModules, jQuery: () => ({ on() {} }) });
  const wrapper = { querySelector: () => section, getAttribute: () => 'test' };
  if (!original) hook([wrapper]);
  return { cards, arrows, section, window, document, frames, handler, reduce, media, button, list, panel, viewport, back, details, openers, models,
    reinitialize() { handler.onDestroy(); hook([wrapper]); return handler; },
    click(target) { const e=new Event('click',{cancelable:true});Object.defineProperty(e,'target',{value:target});section.dispatchEvent(e);return e; },
    scroll(offset) { cards.forEach((card, i) => { card.top = 500 + i * 250 - offset; }); window.dispatchEvent(new Event('scroll')); const queued = [...frames.values()]; frames.clear(); queued.forEach(fn => fn()); },
    styles() { return cards.map((card,i) => [card.style.opacity,card.style.filter,card.style.transform,arrows[i].style['--arrow-scroll-x'],arrows[i].style['--arrow-scroll-y']]); },
  };
}

test('matches reference service reveal and arrow offsets throughout scrolling', () => {
  const widget = setup(), original = setup({ original: true });
  for (const offset of [0, 300, 900, 1800, -300, 600]) {
    widget.scroll(offset); original.scroll(offset);
    assert.deepEqual(widget.styles(), original.styles());
  }
  widget.handler.onDestroy();
});

test('editor, disabled reveal, and reduced motion keep all services visible', () => {
  for (const config of [{ editing: true }, { reduced: true }, { animate: '' }]) {
    const s = setup(config); s.scroll(-500);
    assert.ok(s.cards.every(card => card.style.opacity === '' && card.style.filter === '' && card.style.transform === ''));
    s.handler.onDestroy();
  }
});

test('keyboard-focused card remains visible while scrolling and teardown removes pending work', () => {
  const s = setup(); s.document.activeElement = s.cards[4]; s.scroll(-500);
  assert.equal(s.cards[4].style.opacity, '1.000'); assert.equal(s.cards[4].style.filter, 'blur(0.00px)');
  s.window.dispatchEvent(new Event('scroll')); assert.equal(s.frames.size, 1);
  s.handler.onDestroy(); assert.equal(s.frames.size, 0);
  assert.ok(s.cards.every(card => card.style.opacity === ''));
  assert.ok(s.arrows.every(arrow => arrow.style['--arrow-scroll-x'] === undefined));
  s.window.dispatchEvent(new Event('scroll')); assert.equal(s.frames.size, 0);
});

test('video controls pause both service and arrow media; reduced motion pauses and clears reveal', () => {
  const s = setup({ withVideos: true }); assert.ok(s.media.every(video => !video.paused));
  assert.equal(s.button.attributes['aria-label'], 'Pause service video');
  s.button.dispatchEvent(new Event('click')); assert.ok(s.media.every(video => video.paused));
  s.button.dispatchEvent(new Event('click')); assert.ok(s.media.every(video => !video.paused));
  s.reduce.matches = true; s.reduce.dispatchEvent(new Event('change'));
  assert.ok(s.media.every(video => video.paused)); assert.ok(s.cards.every(card => card.style.opacity === ''));
  s.handler.onDestroy();
  for (const config of [{ editing: true }, { reduced: true }]) {
    const paused = setup({ ...config, withVideos: true }); assert.ok(paused.media.every(video => video.paused)); paused.handler.onDestroy();
  }
});

test('service clicks do not navigate while editing', () => {
  const s = setup({ editing: true });
  const event = new Event('click', { cancelable: true }); Object.defineProperty(event, 'target', { value: s.cards[0] });
  s.section.dispatchEvent(event); assert.equal(event.defaultPrevented, true); s.handler.onDestroy();
});

test('detail navigation slides panes, updates height/history, and returns focus on close', () => {
  const s = setup({ withDetails:true });
  assert.equal(s.viewport.style.height,'1500px'); assert.equal(s.panel.inert,true);
  s.click(s.openers[0]); assert.equal(s.section.classList.contains('is-detail'),true);
  assert.equal(s.details[0].hidden,false); assert.equal(s.details[1].hidden,true);
  assert.equal(s.list.inert,true); assert.equal(s.panel.inert,false); assert.equal(s.viewport.style.height,'900px');
  assert.equal(s.document.activeElement,s.back); assert.equal(s.window.location.hash,'#jn-services-test-web');
  assert.equal(s.openers[0].attributes['aria-expanded'],'true');
  s.click(s.back); assert.equal(s.list.inert,false); assert.equal(s.panel.inert,true);
  assert.equal(s.document.activeElement,s.openers[0]); assert.equal(s.window.location.hash,''); assert.equal(s.viewport.style.height,'1500px');
  s.handler.onDestroy(); assert.equal(s.viewport.style.height,''); assert.equal(s.section.classList.contains('is-enhanced'),false);
});
test('scoped and legacy deep links, Back/Forward and external hash changes select the correct pane', () => {
  for (const hash of ['#jn-services-test-email','#email']) {
    const s=setup({withDetails:true,hash}); assert.equal(s.details[1].hidden,false);
    s.window.location.hash='';s.window.dispatchEvent(new Event('popstate'));assert.equal(s.section.classList.contains('is-detail'),false);assert.equal(s.document.activeElement,s.openers[1]);
    s.window.location.hash='#jn-services-test-web';s.window.dispatchEvent(new Event('popstate'));assert.equal(s.details[0].hidden,false);assert.equal(s.details[1].hidden,true);
    s.window.location.hash='#elsewhere';s.window.dispatchEvent(new Event('hashchange'));assert.equal(s.section.classList.contains('is-detail'),false);assert.equal(s.window.location.hash,'#elsewhere');
    s.handler.onDestroy();
  }
  const s=setup({withDetails:true,hash:'#previous'});s.click(s.openers[0]);s.click(s.back);assert.equal(s.window.location.hash,'#previous');s.handler.onDestroy();
});
test('editor previews never alter history, Escape closes, videos pause in detail and teardown removes listeners', () => {
  const editor=setup({withDetails:true,editing:true,preview:true});assert.equal(editor.details[1].hidden,false);assert.equal(editor.window.location.hash,'');editor.handler.onDestroy();
  const s=setup({withDetails:true,withVideos:true});assert.ok(s.media.every(v=>!v.paused));s.click(s.openers[0]);assert.ok(s.media.every(v=>v.paused));
  const escape=new Event('keydown',{cancelable:true});Object.defineProperty(escape,'key',{value:'Escape'});s.section.dispatchEvent(escape);
  assert.equal(escape.defaultPrevented,true);assert.equal(s.section.classList.contains('is-detail'),false);assert.ok(s.media.every(v=>!v.paused));
  s.click(s.openers[0]);s.handler.onDestroy();assert.ok(s.media.every(v=>v.paused));assert.equal(s.list.inert,false);
  s.window.location.hash='#email';s.window.dispatchEvent(new Event('hashchange'));assert.equal(s.section.classList.contains('is-detail'),false);assert.equal(s.frames.size,0);
});


test('editor arrows open every service and retain the selection across widget rerenders', () => {
  const s=setup({withDetails:true,editing:true});
  assert.equal(s.models[0].get('detail_heading'),'Detail web');
  assert.equal(s.models[0].get('detail_cta'),'Custom CTA');
  assert.equal(s.models[1].get('detail_bullets'),'First\nSecond');
  s.click(s.openers[1]);assert.equal(s.details[1].hidden,false);assert.equal(s.window.location.hash,'');
  const next=s.reinitialize();assert.equal(s.details[1].hidden,false);assert.equal(s.section.classList.contains('is-services-editor'),true);
  s.click(s.back);assert.equal(s.section.classList.contains('is-detail'),false);next.onDestroy();
  const restored=s.reinitialize();assert.equal(s.section.classList.contains('is-detail'),false);restored.onDestroy();
});


test('editor tools open the matching sidebar row, benefits input, and gallery picker', () => {
  const s=setup({withDetails:true,editing:true});
  const calls=[];
  const controls={detail_bullets:{applySavedValue:()=>calls.push('refresh-benefits'),el:{scrollIntoView(){},querySelector:()=>({focus:()=>calls.push('focus-benefits')})}},detail_images:{applySavedValue:()=>calls.push('refresh-images'),el:{scrollIntoView(){}},hasImages:()=>true,onGalleryThumbnailsClick:()=>calls.push('open-gallery')}};
  const rows=s.details.map((d,index)=>({getChildControlView:key=>{calls.push('control-'+index+'-'+key);return controls[key];}}));
  const repeater={children:{findByIndex:i=>rows[i]},editRow(row){this.currentEditableChild=row;calls.push('row-'+rows.indexOf(row));}};
  const page={activateSection:name=>calls.push(name),activateTab:tab=>calls.push(tab),getControlViewByName:()=>repeater};
  s.window.parent.elementor.getContainer=()=>({model:{},view:{}});
  s.window.parent.elementor.getPanelView=()=>({getCurrentPageView:()=>page});
  s.window.parent.$e={run:command=>calls.push(command)};
  s.click(s.openers[1]);assert.ok(calls.includes('row-1'));
  const edit=key=>({dataset:{svEdit:key},closest:selector=>selector==='[data-sv-edit]'?editTargets[key]:null});
  const editTargets={detail_bullets:edit('detail_bullets'),detail_images:edit('detail_images')};
  s.click(editTargets.detail_bullets);assert.ok(calls.includes('control-1-detail_bullets'));assert.ok(calls.includes('focus-benefits'));
  s.click(editTargets.detail_images);assert.ok(calls.includes('control-1-detail_images'));assert.ok(calls.includes('open-gallery'));assert.equal(s.window.location.hash,'');
  s.handler.onDestroy();
});
