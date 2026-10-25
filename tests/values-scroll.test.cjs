const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const source = fs.readFileSync(__dirname + '/../assets/js/values.js', 'utf8');
function setup({ count=5, editing=false, reduced=false, animate=true, mode='stack', pointer=true, compact=false, top=0 }={}) {
  class Element extends EventTarget {
    constructor() { super(); this.attributes={};this.dataset={};this.top=0;this.offsetWidth=600;this.offsetHeight=500;this.style={setProperty(k,v){this[k]=String(v);},removeProperty(k){delete this[k];},getPropertyValue(k){return this[k]??'';},getPropertyPriority(){return '';}}; }
    setAttribute(k,v){this.attributes[k]=v;}
    removeAttribute(k){delete this.attributes[k];}
    getBoundingClientRect(){return{top:this.top,left:100,width:600,height:this.height??500};}
  }
  const section=new Element();section.height=compact?1000:3000;section.top=top;section.dataset={animate:animate?'yes':'',mode,pointer:pointer?'yes':''};
  const cards=Array.from({length:count},()=>{const card=new Element();card.top=200;return card;});section.querySelectorAll=()=>cards;
  const reduce=new EventTarget();reduce.matches=reduced;const fine=new EventTarget();fine.matches=true;
  const small=new EventTarget();small.matches=compact;
  const document=new EventTarget();document.hidden=false;document.body=new Element();
  const frames=new Map(),observers=[];let next=0,hook,handler,clock=1000;
  const window=new EventTarget();Object.assign(window,{
    innerHeight:1000,innerWidth:compact?800:1400,scrollY:0,scrollTo({top}){section.top-=top-this.scrollY;this.scrollY=top;},matchMedia:q=>q.includes('reduced')?reduce:q.includes('max-width')?small:fine,
    requestAnimationFrame:fn=>{frames.set(++next,fn);return next;},cancelAnimationFrame:id=>frames.delete(id),
    IntersectionObserver:class{constructor(fn){this.fn=fn;this.targets=new Set();observers.push(this);}observe(e){this.targets.add(e);}disconnect(){this.targets.clear();}},
    elementorFrontend:{isEditMode:()=>editing,hooks:{addAction:(name,fn)=>{assert.equal(name,'frontend/element_ready/jednotka-values.default');hook=fn;}},elementsHandler:{addHandler(Type,args){handler=new Type();Object.assign(handler,args);handler.onInit();}}},
    elementorModules:{frontend:{handlers:{Base:class{onInit(){}onDestroy(){}}}}},
  });
  vm.runInNewContext(source,{window,document,AbortController,elementorFrontend:window.elementorFrontend,elementorModules:window.elementorModules,jQuery:()=>({on(){}})});
  hook([{querySelector:()=>section}]);
  return{section,cards,frames,observers,reduce,fine,small,document,handler,window,
    tick(ms=500){clock+=ms;},
    touch(type,y=400,x=200,multi=false){const e=new Event(type,{cancelable:true});Object.defineProperty(e,'timeStamp',{value:clock});e.touches=multi?[{clientX:x,clientY:y},{clientX:x+20,clientY:y}]:[{clientX:x,clientY:y}];window.dispatchEvent(e);return e;},
    wheel(deltaY,timeStamp,extra={}){const e=new Event('wheel',{cancelable:true});Object.defineProperties(e,{deltaY:{value:deltaY},deltaX:{value:0},deltaMode:{value:0},timeStamp:{value:timeStamp},...Object.fromEntries(Object.entries(extra).map(([k,v])=>[k,{value:v}]))});window.dispatchEvent(e);return e;},
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

test('compact touch locks the page, advances once per swipe, and releases only on a fresh outward swipe',()=>{
 const s=setup({compact:true,count:3});
 assert.equal(s.section.attributes['data-scroll-locked'],'yes');
 assert.equal(s.document.body.style.position,'fixed');
 s.touch('touchstart');assert.equal(s.touch('touchmove',395).defaultPrevented,true);
 assert.equal(s.cards[1].style['--enter'],'1.0000');
 s.touch('touchmove',300);assert.equal(s.cards[1].style['--enter'],'0.0000');
 s.touch('touchmove',100);assert.equal(s.cards[2].style['--enter'],'1.0000');
 // Even a new swipe cannot skip the running animation.
 s.touch('touchend');s.touch('touchstart');s.touch('touchmove',300);
 assert.equal(s.cards[2].style['--enter'],'1.0000');
 s.touch('touchend');s.tick();s.touch('touchstart');s.touch('touchmove',300);
 assert.equal(s.cards[2].style['--enter'],'0.0000');
 s.tick();assert.equal(s.touch('touchmove',100).defaultPrevented,true);
 assert.equal(s.section.attributes['data-scroll-locked'],'yes');
 s.touch('touchend');s.touch('touchstart');
 assert.equal(s.touch('touchmove',300).defaultPrevented,false);
 assert.equal(s.section.attributes['data-scroll-locked'],undefined);
 assert.equal(s.document.body.style.position,undefined);s.handler.onDestroy();
});

test('reverse swipes animate backwards and leave above the first card',()=>{
 const s=setup({compact:true,count:3,mode:'fall'});
 s.touch('touchstart');s.touch('touchmove',300);s.touch('touchend');s.tick();
 assert.equal(s.cards[0].style['--fall'],'1.0000');
 s.touch('touchstart');s.touch('touchmove',460);s.touch('touchend');s.tick();
 assert.equal(s.cards[0].style['--fall'],'0.0000');
 assert.equal(s.document.body.style.position,'fixed');
 s.touch('touchstart');assert.equal(s.touch('touchmove',460).defaultPrevented,false);
 assert.equal(s.document.body.style.position,undefined);s.handler.onDestroy();
});

test('entry locks at the section and consumes the entering touch, and re-entry from below shows the last card',()=>{
 const s=setup({compact:true,top:300,count:3});
 assert.equal(s.document.body.style.position,undefined);
 s.touch('touchstart');s.scroll(.05);
 assert.equal(s.document.body.style.position,'fixed');
 assert.equal(s.section.top,0);assert.equal(s.window.scrollY,-100);
 assert.equal(s.touch('touchmove',100).defaultPrevented,true);
 assert.equal(s.cards[1].style['--enter'],'1.0000');
 s.touch('touchend');s.touch('touchstart');s.touch('touchmove',460);
 assert.equal(s.document.body.style.position,undefined);
 s.scroll(.4);s.scroll(.3);s.scroll(0);
 assert.equal(s.document.body.style.position,'fixed');
 assert.equal(s.cards[2].style['--enter'],'0.0000');s.handler.onDestroy();
});

test('compact wheel momentum and running animations cannot skip cards or exit early',()=>{
 const s=setup({compact:true,count:3});
 assert.equal(s.wheel(120,1000).defaultPrevented,true);
 assert.equal(s.cards[1].style['--enter'],'0.0000');
 for(const t of [1020,1040,1080,1140,1380])s.wheel(120,t);
 assert.equal(s.cards[2].style['--enter'],'1.0000');
 s.wheel(120,1700);assert.equal(s.cards[2].style['--enter'],'0.0000');
 assert.equal(s.wheel(120,1750).defaultPrevented,true);
 assert.equal(s.wheel(120,1900).defaultPrevented,true);
 assert.equal(s.section.attributes['data-scroll-locked'],'yes');
 assert.equal(s.wheel(120,2300).defaultPrevented,false);
 assert.equal(s.document.body.style.position,undefined);
 s.handler.onDestroy();assert.equal(s.wheel(120,2600).defaultPrevented,false);
});

test('zoom is allowed, horizontal gestures stay locked, and offscreen scrolling is untouched',()=>{
 const s=setup({compact:true});
 assert.equal(s.wheel(120,1000,{ctrlKey:true}).defaultPrevented,false);
 assert.equal(s.wheel(120,1400,{deltaX:200}).defaultPrevented,true);
 s.touch('touchstart');assert.equal(s.touch('touchmove',395,300).defaultPrevented,true);
 s.touch('touchstart');assert.equal(s.touch('touchmove',300,200,true).defaultPrevented,false);
 s.handler.onDestroy();
 const outside=setup({compact:true,top:600});
 assert.equal(outside.wheel(120,1000).defaultPrevented,false);outside.handler.onDestroy();
});

test('resize, reduced motion, hidden tabs, and teardown restore the body styles and scroll position',()=>{
 for(const reason of ['resize','reduced','hidden','destroy']){
  const s=setup({compact:true,top:100});
  s.document.body.style.setProperty('position','relative');
  s.document.body.style.setProperty('overflow','clip');
  s.scroll(.01);const saved=s.window.scrollY;
  assert.equal(s.document.body.style.position,'fixed');
  if(reason==='resize'){s.small.matches=false;s.small.dispatchEvent(new Event('change'));}
  if(reason==='reduced'){s.reduce.matches=true;s.reduce.dispatchEvent(new Event('change'));}
  if(reason==='hidden'){s.document.hidden=true;s.document.dispatchEvent(new Event('visibilitychange'));}
  if(reason==='destroy')s.handler.onDestroy();
  assert.equal(s.document.body.style.position,'relative');
  assert.equal(s.document.body.style.overflow,'clip');assert.equal(s.window.scrollY,saved);
  assert.equal(s.section.attributes['data-scroll-locked'],undefined);
  if(reason!=='destroy')s.handler.onDestroy();
 }
 for(const options of [{editing:true},{reduced:true},{animate:false},{count:1}]){
  const s=setup({compact:true,...options});
  assert.equal(s.document.body.style.position,undefined);
  assert.equal(s.wheel(120,1000).defaultPrevented,false);s.handler.onDestroy();
 }
});
