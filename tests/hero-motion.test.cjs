const { test } = require('node:test');
const assert = require('node:assert/strict');
const vm = require('node:vm');
const fs = require('node:fs');
function setup(editing=false) {
 const hero=new EventTarget(), frame=new EventTarget(), media=new EventTarget(), document=new EventTarget(), frames=new Map();document.hidden=false;media.matches=false;hero.dataset={float:'yes'};
 const classes=new Set();hero.classList={toggle:(key,value)=>value?classes.add(key):classes.delete(key),contains:key=>classes.has(key),add:key=>classes.add(key),remove:key=>classes.delete(key)};
 frame.style={setProperty(){}};frame.querySelectorAll=()=>[];
 const video={closest:()=>null,playing:false,play(){this.playing=true;return Promise.resolve();},pause(){this.playing=false;}};
 hero.querySelector=selector=>selector==='.jn-hero__frame'?frame:null;hero.querySelectorAll=()=>[video];
 let handler,serial=0;const window={matchMedia:()=>media,requestAnimationFrame:callback=>{frames.set(++serial,callback);return serial;},cancelAnimationFrame:id=>frames.delete(id),elementorFrontend:{isEditMode:()=>editing,hooks:{addAction(name,callback){callback([{querySelector:()=>hero}]);}},elementsHandler:{addHandler(Type,args){handler=new Type();Object.assign(handler,args);handler.onInit();}}},elementorModules:{frontend:{handlers:{Base:class{onInit(){}onDestroy(){}}}}}};
 vm.runInNewContext(fs.readFileSync(__dirname+'/../assets/js/hero.js','utf8'),{window,document,AbortController,elementorFrontend:window.elementorFrontend,elementorModules:window.elementorModules,jQuery:()=>({on(){}})});
 return {hero,media,document,video,frames,handler};
}
test('hero animates without a pause button and follows motion preference and tab visibility',()=>{
 const s=setup();assert.equal(s.video.playing,true);assert.equal(s.frames.size,1);
 s.media.matches=true;s.media.dispatchEvent(new Event('change'));assert.equal(s.video.playing,false);assert.equal(s.frames.size,0);
 s.media.matches=false;s.media.dispatchEvent(new Event('change'));assert.equal(s.video.playing,true);
 s.document.hidden=true;s.document.dispatchEvent(new Event('visibilitychange'));assert.equal(s.video.playing,false);assert.equal(s.frames.size,0);
 s.document.hidden=false;s.document.dispatchEvent(new Event('visibilitychange'));assert.equal(s.video.playing,true);
 s.handler.onDestroy();assert.equal(s.frames.size,0);assert.equal(s.video.playing,false);
});
test('hero video stays paused in Elementor without the removed button',()=>{const s=setup(true);assert.equal(s.video.playing,false);s.handler.onDestroy();});
