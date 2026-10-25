const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(__dirname + '/../assets/js/values.js', 'utf8');
function setup({ count=5, editing=false, reduced=false, animate=true, mode='stack', pointer=true }={}) {
  class Element extends EventTarget {
    constructor() { super(); this.attributes={};this.dataset={};this.top=0;this.offsetWidth=600;this.offsetHeight=500;this.style={setProperty(k,v){this[k]=String(v);},removeProperty(k){delete this[k];}}; }
    setAttribute(k,v){this.attributes[k]=v;}
    getBoundingClientRect(){return{top:this.top,left:100,width:600,height:this.height??500};}
  }
  const section=new Element();section.height=3000;section.dataset={animate:animate?'yes':'',mode,pointer:pointer?'yes':''};
  const sticky=new Element();sticky.offsetHeight=1000;section.querySelector=()=>sticky;
  const cards=Array.from({length:count},()=>{const card=new Element();card.top=200;return card;});section.querySelectorAll=()=>cards;
  const reduce=new EventTarget();reduce.matches=reduced;const fine=new EventTarget();fine.matches=true;
  const document=new EventTarget();document.hidden=false;
  const frames=new Map(),observers=[],frameObservers=[];let next=0,hook,handler;
  const window=new EventTarget();Object.assign(window,{
    innerHeight:1000,matchMedia:q=>q.includes('reduced')?reduce:fine,
    requestAnimationFrame:fn=>{frames.set(++next,fn);return next;},cancelAnimationFrame:id=>frames.delete(id),
    IntersectionObserver:class{constructor(fn){this.fn=fn;this.targets=new Set();observers.push(this);}observe(e){this.targets.add(e);}disconnect(){this.targets.clear();}},
    ResizeObserver:class{constructor(fn){this.fn=fn;this.targets=new Set();frameObservers.push(this);}observe(e){this.targets.add(e);}disconnect(){this.targets.clear();}},
    elementorFrontend:{isEditMode:()=>editing,hooks:{addAction:(name,fn)=>{assert.equal(name,'frontend/element_ready/jednotka-values.default');hook=fn;}},elementsHandler:{addHandler(Type,args){handler=new Type();Object.assign(handler,args);handler.onInit();}}},
    elementorModules:{frontend:{handlers:{Base:class{onInit(){}onDestroy(){}}}}},
  });
  vm.runInNewContext(source,{window,document,AbortController,elementorFrontend:window.elementorFrontend,elementorModules:window.elementorModules,jQuery:()=>({on(){}})});
  hook([{querySelector:()=>section}]);
  return{section,sticky,cards,frames,observers,frameObservers,reduce,fine,document,handler,window,
    gesture(type){const event=new Event(type,{cancelable:true});window.dispatchEvent(event);return event;},
    flush(){const callbacks=[...frames.values()];frames.clear();callbacks.forEach(fn=>fn());},
    queue(){window.dispatchEvent(new Event('scroll'));},
    scroll(p){section.top=-p*2000;this.queue();this.flush();},
    move(type='mouse'){const e=new Event('pointermove');Object.defineProperties(e,{clientX:{value:550},clientY:{value:300},pointerType:{value:type}});cards[0].dispatchEvent(e);},
  };
}

test('build-stack progression matches reference entry, settling, poses, and last-card hold',()=>{
 const s=setup();assert.equal(s.section.style['--count'],'5');
 assert.equal(s.cards[0].style['--enter'],'0.0000');assert.equal(s.cards[1].style['--enter'],'1.0000');
 assert.equal(s.cards[1].style['--pose-x'],'7');assert.equal(s.cards[1].style['--pose-rot'],'5');
 s.scroll(.23);assert.equal(s.cards[1].style['--enter'],'0.0000');assert.equal(s.cards[0].style['--settle'],'1.0000');
 s.scroll(.92);assert.equal(s.cards[4].style['--enter'],'0.0000');assert.equal(s.cards[4].style['--settle'],'0.0000');
 const last=s.cards[4].style['--enter'];s.scroll(1);assert.equal(s.cards[4].style['--enter'],last);s.handler.onDestroy();
});

test('fall mode drops the first card and reveals the next using squared progress',()=>{
 const s=setup({mode:'fall'});assert.equal(s.cards[0].style.zIndex,5);assert.equal(s.cards[0].style['--dir'],'-1');
 s.scroll(.115);assert.equal(s.cards[0].style['--fall'],'0.2500');assert.equal(s.cards[1].style['--fall'],'0.0000');
 s.scroll(.23);assert.equal(s.cards[0].style['--fall'],'1.0000');s.handler.onDestroy();
});

test('mouse tilt and glow follow smoothly, settle without continuous frames, and ease back',()=>{
 const s=setup();s.move();assert.equal(s.frames.size,1);s.flush();
 assert.equal(s.cards[0].style['--lift'],'0.100');assert.equal(s.cards[0].style['--ry'],'0.450');
 for(let i=0;i<160;i++)s.flush();assert.equal(s.frames.size,0);assert.ok(parseFloat(s.cards[0].style['--ox'])>74.9);
 s.cards[0].dispatchEvent(new Event('pointerleave'));for(let i=0;i<160;i++)s.flush();
 assert.equal(s.frames.size,0);assert.ok(parseFloat(s.cards[0].style['--lift'])<.001);s.handler.onDestroy();
});

test('static modes keep every card readable without long scroll space or pointer frames',()=>{
 for(const options of[{editing:true},{reduced:true},{animate:false},{count:1},{count:0}]){
  const s=setup(options);assert.equal(s.section.attributes['data-static'],'yes');s.queue();assert.equal(s.frames.size,0);
  assert.ok(s.cards.every(card=>card.style['--enter']===undefined));s.handler.onDestroy();
 }
 const s=setup({pointer:false});s.move();assert.equal(s.frames.size,0);s.handler.onDestroy();
 const touch=setup();touch.move('touch');assert.equal(touch.frames.size,0);touch.handler.onDestroy();
});

test('scroll batches per frame and offscreen or hidden sections stop pending animation',()=>{
 const s=setup();s.queue();s.queue();assert.equal(s.frames.size,1);
 s.observers[0].fn([{isIntersecting:false}]);assert.equal(s.frames.size,0);s.queue();assert.equal(s.frames.size,0);
 s.observers[0].fn([{isIntersecting:true}]);s.flush();s.move();assert.equal(s.frames.size,1);
 s.document.hidden=true;s.document.dispatchEvent(new Event('visibilitychange'));assert.equal(s.frames.size,0);
 assert.equal(s.cards[0].style['--lift'],undefined);s.handler.onDestroy();
});

test('preference changes and teardown reset transforms and remove listeners',()=>{
 const s=setup();s.move();s.flush();s.reduce.matches=true;s.reduce.dispatchEvent(new Event('change'));
 assert.equal(s.section.attributes['data-static'],'yes');assert.equal(s.frames.size,0);assert.equal(s.cards[0].style['--pose-x'],undefined);
 s.reduce.matches=false;s.reduce.dispatchEvent(new Event('change'));assert.equal(s.section.attributes['data-static'],'no');
 s.queue();s.handler.onDestroy();assert.equal(s.frames.size,0);assert.equal(s.observers[0].targets.size,0);s.queue();s.move();assert.equal(s.frames.size,0);
});

test('the same physical scroll advances less when the section scroll distance is increased',()=>{
 const normal=setup(), longer=setup();
 longer.section.height=5000;
 for(const s of [normal,longer]){s.section.top=-460;s.queue();s.flush();}
 assert.equal(normal.cards[1].style['--enter'],'0.0000');
 assert.ok(Number(longer.cards[1].style['--enter'])>0);
 assert.ok(Number(longer.cards[0].style['--settle'])<Number(normal.cards[0].style['--settle']));
 normal.handler.onDestroy();longer.handler.onDestroy();
});

test('swipes and wheel gestures are not intercepted and cannot advance cards without scrolling',()=>{
 const s=setup();
 for(const type of ['touchstart','touchmove','touchend','wheel']){
  assert.equal(s.gesture(type).defaultPrevented,false);
  assert.equal(s.cards[1].style['--enter'],'1.0000');
  assert.equal(s.frames.size,0);
 }
 s.handler.onDestroy();
});

test('browser bar resizing does not change progress when the content frame is unchanged',()=>{
 const s=setup();s.scroll(.115);
 const before=s.cards.map(card=>[card.style['--enter'],card.style['--settle']]);
 s.window.innerHeight=700;s.window.dispatchEvent(new Event('resize'));s.flush();
 assert.deepEqual(s.cards.map(card=>[card.style['--enter'],card.style['--settle']]),before);
 assert.equal(s.section.style['--values-frame-height'],'1000px');
 s.sticky.offsetHeight=800;s.frameObservers[0].fn();s.flush();
 assert.equal(s.section.style['--values-frame-height'],'800px');
 assert.notDeepEqual(s.cards.map(card=>[card.style['--enter'],card.style['--settle']]),before);
 s.handler.onDestroy();assert.equal(s.frameObservers[0].targets.size,0);
 assert.equal(s.section.style['--values-frame-height'],undefined);
});
