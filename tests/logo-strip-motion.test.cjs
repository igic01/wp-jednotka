const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
function setup(editing=false) {
 const strip=new EventTarget(), reduce=new EventTarget(), classes=new Set();reduce.matches=false;strip.dataset={animate:'yes',pauseHover:'yes'};strip.classList={toggle:(key,on)=>on?classes.add(key):classes.delete(key)};
 const groups=[{children:[]},{children:[]}];const video={playing:false,play(){this.playing=true;return Promise.resolve();},pause(){this.playing=false;}};
 strip.querySelectorAll=selector=>selector==='.logo-strip-group'?groups:[video];
 let handler;const window={matchMedia:()=>reduce,getComputedStyle:()=>({}),elementorFrontend:{isEditMode:()=>editing,hooks:{addAction(name,cb){cb([{querySelector:()=>strip}]);}},elementsHandler:{addHandler(Type,args){handler=new Type();Object.assign(handler,args);handler.onInit();}}},elementorModules:{frontend:{handlers:{Base:class{onInit(){}onDestroy(){}}}}}};
 vm.runInNewContext(fs.readFileSync(__dirname+'/../assets/js/logo-strip.js','utf8'),{window,AbortController,elementorFrontend:window.elementorFrontend,elementorModules:window.elementorModules,jQuery:()=>({on(){}}),ResizeObserver:class{observe(){}disconnect(){}}});
 return{strip,reduce,classes,video,handler};
}
test('logo strip runs without a button, pauses on hover and respects reduced motion',()=>{
 const s=setup();assert.equal(s.video.playing,true);assert.equal(s.classes.has('is-paused'),false);
 s.strip.dispatchEvent(new Event('pointerenter'));assert.equal(s.video.playing,false);s.strip.dispatchEvent(new Event('pointerleave'));assert.equal(s.video.playing,true);
 s.reduce.matches=true;s.reduce.dispatchEvent(new Event('change'));assert.equal(s.video.playing,false);assert.equal(s.classes.has('is-paused'),true);
 s.reduce.matches=false;s.reduce.dispatchEvent(new Event('change'));assert.equal(s.video.playing,true);s.handler.onDestroy();assert.equal(s.video.playing,false);
});
test('logo videos stay paused in the editor',()=>{const s=setup(true);assert.equal(s.video.playing,false);s.handler.onDestroy();});
