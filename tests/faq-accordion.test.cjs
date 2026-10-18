const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
function setup({ editing = false, reduced = false, stable = 'yes', answerHeights = [60,60,60], initiallyOpen = [1] } = {}) {
  const animations = [], frames = new Map(); let id = 0, hook, handler;
  const details = [0, 1, 2].map((i) => {
    const summary = new EventTarget(), classes = new Set();
    const detail = { open: initiallyOpen.includes(i), visual: null, classList: { add:k=>classes.add(k), remove:k=>classes.delete(k) }, getBoundingClientRect:()=>({height:detail.visual ?? (detail.open ? 80 + answerHeights[i] : 80)}), querySelector:()=>summary,
      animate(keyframes, options) { const a={ detail,keyframes,options,onfinish:null,cancel(){detail.visual=null;this.cancelled=true;},finish(){detail.visual=null;this.onfinish?.();} }; animations.push(a);return a; }, summary };
    return detail;
  });
  const list={style:{},querySelectorAll:()=>details,getBoundingClientRect:()=>({width:600,height:Math.max(parseFloat(list.style.minHeight)||0,details.reduce((n,d)=>n+d.getBoundingClientRect().height,0))})};
  const section={dataset:{stableHeight:stable},querySelector:()=>list};
  const media=new EventTarget();media.matches=reduced;
  const window=new EventTarget();Object.assign(window,{matchMedia:()=>media,requestAnimationFrame:fn=>{frames.set(++id,fn);return id;},cancelAnimationFrame:id=>frames.delete(id),
    elementorFrontend:{isEditMode:()=>editing,hooks:{addAction:(name,fn)=>{hook=fn;}},elementsHandler:{addHandler(Type,args){handler=new Type();Object.assign(handler,args);handler.onInit();}}},
    elementorModules:{frontend:{handlers:{Base:class{onInit(){} onDestroy(){}}}}},
  });
  vm.runInNewContext(fs.readFileSync(__dirname+'/../assets/js/faq.js','utf8'),{window,document:{},AbortController,elementorFrontend:window.elementorFrontend,elementorModules:window.elementorModules,jQuery:()=>({on(){}})});
  hook([{querySelector:()=>section}]);
  return {details,list,window,media,handler,animations,frames,click(index){const e=new Event('click',{cancelable:true});Object.defineProperty(e,'target',{value:{closest:()=>null}});details[index].summary.dispatchEvent(e);return e;}};
}
test('reserves only the tallest answer and closes the previous question when opening another',()=>{
  const s=setup({answerHeights:[40,100,70]});assert.equal(s.list.style.minHeight,'340px');assert.equal(s.details[0].open,false);assert.equal(s.details[1].open,true);
  s.click(0);const opening=s.animations.find(a=>a.detail===s.details[0]);const closing=s.animations.find(a=>a.detail===s.details[1]);
  assert.equal(opening.keyframes[0].height,'80px');assert.equal(opening.keyframes[1].height,'120px');assert.equal(closing.keyframes[1].height,'80px');
  closing.finish();opening.finish();assert.equal(s.details[0].open,true);assert.equal(s.details[1].open,false);assert.equal(s.list.style.minHeight,'340px');
  s.click(0);s.animations.at(-1).finish();assert.equal(s.details[0].open,false);s.handler.onDestroy();
});
test('rapid clicks reverse from the current height and cancel the previous animation',()=>{
  const s=setup();s.click(0);const opening=s.animations.at(-1);s.details[0].visual=110;s.click(0);
  assert.equal(opening.cancelled,true);assert.equal(s.animations.at(-1).keyframes[0].height,'110px');assert.equal(s.animations.at(-1).keyframes[1].height,'80px');s.animations.at(-1).finish();assert.equal(s.details[0].open,false);s.handler.onDestroy();
});
test('multiple initial open flags are normalized to one answer on the frontend',()=>{
  const s=setup({initiallyOpen:[0,1,2]});assert.deepEqual(s.details.map(d=>d.open),[true,false,false]);assert.equal(s.list.style.minHeight,'300px');s.handler.onDestroy();
});
test('reduced motion is instant, editor keeps native editing, and teardown cancels queued work',()=>{
  const reduced=setup({reduced:true});reduced.click(0);assert.equal(reduced.details[0].open,true);assert.equal(reduced.animations.length,0);assert.equal(reduced.details[1].open,false);reduced.handler.onDestroy();
  const editor=setup({editing:true});assert.equal(editor.click(0).defaultPrevented,false);assert.equal(editor.list.style.minHeight,undefined);editor.handler.onDestroy();
  const s=setup();s.click(0);s.media.matches=true;s.media.dispatchEvent(new Event('change'));assert.equal(s.details[0].open,true);
  s.window.dispatchEvent(new Event('resize'));assert.equal(s.frames.size,1);s.handler.onDestroy();assert.equal(s.frames.size,0);assert.equal(s.list.style.minHeight,'');s.window.dispatchEvent(new Event('resize'));assert.equal(s.frames.size,0);
  const fluid=setup({stable:''});assert.equal(fluid.list.style.minHeight,'');fluid.handler.onDestroy();
});
