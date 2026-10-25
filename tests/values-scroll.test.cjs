const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(__dirname + '/../assets/js/values.js', 'utf8');
function setup({ count=5, editing=false, reduced=false, animate=true, mode='stack', pointer=true, compact=false }={}) {
  class Element extends EventTarget {
    constructor() { super(); this.attributes={};this.dataset={};this.top=0;this.offsetWidth=600;this.offsetHeight=500;this.style={setProperty(k,v){this[k]=String(v);},removeProperty(k){delete this[k];}}; }
    setAttribute(k,v){this.attributes[k]=v;}
    removeAttribute(k){delete this.attributes[k];}
    getBoundingClientRect(){return{top:this.top,left:100,width:600,height:this.height??500};}
  }
  const section=new Element();section.height=3000;section.dataset={animate:animate?'yes':'',mode,pointer:pointer?'yes':''};
  const cards=Array.from({length:count},()=>{const card=new Element();card.top=200;return card;});section.querySelectorAll=()=>cards;
  const reduce=new EventTarget();reduce.matches=reduced;const fine=new EventTarget();fine.matches=true;
  const small=new EventTarget();small.matches=compact;
  const document=new EventTarget();document.hidden=false;
  const frames=new Map(),observers=[];let next=0,hook,handler;
  const window=new EventTarget();Object.assign(window,{
    innerHeight:1000,scrollY:0,scrollTo({top}){this.scrollY=top;section.top=0;},matchMedia:q=>q.includes('reduced')?reduce:q.includes('max-width')?small:fine,
    requestAnimationFrame:fn=>{frames.set(++next,fn);return next;},cancelAnimationFrame:id=>frames.delete(id),
    IntersectionObserver:class{constructor(fn){this.fn=fn;this.targets=new Set();observers.push(this);}observe(e){this.targets.add(e);}disconnect(){this.targets.clear();}},
    elementorFrontend:{isEditMode:()=>editing,hooks:{addAction:(name,fn)=>{assert.equal(name,'frontend/element_ready/jednotka-values.default');hook=fn;}},elementsHandler:{addHandler(Type,args){handler=new Type();Object.assign(handler,args);handler.onInit();}}},
    elementorModules:{frontend:{handlers:{Base:class{onInit(){}onDestroy(){}}}}},
  });
  vm.runInNewContext(source,{window,document,AbortController,elementorFrontend:window.elementorFrontend,elementorModules:window.elementorModules,jQuery:()=>({on(){}})});
  hook([{querySelector:()=>section}]);
  return{section,cards,frames,observers,reduce,fine,small,document,handler,
    touch(type,y=400,x=200,multi=false){const e=new Event(type,{cancelable:true});e.touches=multi?[{clientX:x,clientY:y},{clientX:x+20,clientY:y}]:[{clientX:x,clientY:y}];section.dispatchEvent(e);return e;},
    wheel(deltaY,timeStamp,extra={}){const e=new Event('wheel',{cancelable:true});Object.defineProperties(e,{deltaY:{value:deltaY},deltaX:{value:0},deltaMode:{value:0},timeStamp:{value:timeStamp},...Object.fromEntries(Object.entries(extra).map(([k,v])=>[k,{value:v}]))});section.dispatchEvent(e);return e;},
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

test('compact touch gestures advance exactly one card and allow leaving both ends',()=>{
 const s=setup({compact:true,count:3});
 assert.equal(s.section.attributes['data-counted'],'yes');
 s.touch('touchstart');assert.equal(s.touch('touchmove',460).defaultPrevented,false);
 s.touch('touchend');s.touch('touchstart');
 assert.equal(s.touch('touchmove',395).defaultPrevented,true);
 assert.equal(s.cards[1].style['--enter'],'1.0000');
 assert.equal(s.touch('touchmove',300).defaultPrevented,true);
 assert.equal(s.cards[1].style['--enter'],'0.0000');
 s.touch('touchmove',100);assert.equal(s.cards[2].style['--enter'],'1.0000');
 s.touch('touchend');s.touch('touchstart');s.touch('touchmove',300);
 assert.equal(s.cards[2].style['--enter'],'0.0000');
 assert.equal(s.touch('touchmove',200).defaultPrevented,true);
 s.touch('touchend');s.touch('touchstart');
 assert.equal(s.touch('touchmove',300).defaultPrevented,false);
 s.touch('touchend');s.touch('touchstart');s.touch('touchmove',460);
 assert.equal(s.cards[2].style['--enter'],'1.0000');s.handler.onDestroy();
});

test('compact wheel momentum cannot skip cards or exit until the next gesture',()=>{
 const s=setup({compact:true,count:3});
 assert.equal(s.wheel(120,0).defaultPrevented,true);
 assert.equal(s.cards[1].style['--enter'],'0.0000');
 for(const t of [20,40,80,140])s.wheel(120,t);
 assert.equal(s.cards[2].style['--enter'],'1.0000');
 s.wheel(120,400);assert.equal(s.cards[2].style['--enter'],'0.0000');
 assert.equal(s.wheel(120,450).defaultPrevented,true);
 assert.equal(s.wheel(120,800).defaultPrevented,false);
 s.wheel(-120,1200);assert.equal(s.cards[2].style['--enter'],'1.0000');
 s.handler.onDestroy();assert.equal(s.wheel(120,1600).defaultPrevented,false);
});

test('compact gestures preserve zoom, horizontal swipes, offscreen scrolling, and static modes',()=>{
 const s=setup({compact:true});
 assert.equal(s.wheel(120,0,{ctrlKey:true}).defaultPrevented,false);
 assert.equal(s.wheel(120,400,{deltaX:200}).defaultPrevented,false);
 s.touch('touchstart');assert.equal(s.touch('touchmove',395,300).defaultPrevented,false);
 s.touch('touchstart');assert.equal(s.touch('touchmove',300,200,true).defaultPrevented,false);
 s.section.top=600;assert.equal(s.wheel(120,800).defaultPrevented,false);
 s.section.top=0;s.small.matches=false;s.small.dispatchEvent(new Event('change'));
 assert.equal(s.section.attributes['data-counted'],'no');assert.equal(s.wheel(120,1200).defaultPrevented,false);
 s.handler.onDestroy();
 for(const options of [{editing:true},{reduced:true},{animate:false},{count:1}]){
  const view=setup({compact:true,...options});
  assert.equal(view.section.attributes['data-counted'],'no');
  assert.equal(view.wheel(120,0).defaultPrevented,false);view.handler.onDestroy();
 }
});
