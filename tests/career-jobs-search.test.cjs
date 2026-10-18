const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
function setup({editing=false,texts=['Grafický dizajnér Bratislava Full-time','Web developer Remote Part-time']}={}) {
 const root=new EventTarget(), input=new EventTarget();input.value='';const empty={hidden:texts.length>0},noPositions={hidden:false},noResults={hidden:true};
 const jobs=texts.map(text=>({hidden:false,querySelector:()=>({textContent:text})}));
 root.querySelector=selector=>({'input[type="search"]':input,'.career-jobs-empty':empty,'[data-jobs-empty]':noPositions,'[data-jobs-no-results]':noResults})[selector];root.querySelectorAll=()=>jobs;
 let Type;const window={elementorFrontend:{isEditMode:()=>editing,hooks:{addAction(name,callback){callback([{querySelector:()=>root}]);}},elementsHandler:{addHandler(Handler,args){Type=new Handler();Object.assign(Type,args);Type.onInit();}}},elementorModules:{frontend:{handlers:{Base:class{onInit(){}onDestroy(){}}}}}};
 vm.runInNewContext(fs.readFileSync(__dirname+'/../assets/js/career-jobs.js','utf8'),{window,AbortController,elementorFrontend:window.elementorFrontend,elementorModules:window.elementorModules,jQuery:()=>({on(){}})});
 return {jobs,empty,noPositions,noResults,input,root,search(value){input.value=value;input.dispatchEvent(new Event('input'));},destroy(){Type.onDestroy();}};
}
test('search ignores accents and case, includes metadata, and can be cleared',()=>{
 const s=setup();s.search('GRAFICKY DIZAJNER');assert.deepEqual(s.jobs.map(j=>j.hidden),[false,true]);s.search('remote');assert.deepEqual(s.jobs.map(j=>j.hidden),[true,false]);s.search('part-time');assert.deepEqual(s.jobs.map(j=>j.hidden),[true,false]);s.search('');assert.deepEqual(s.jobs.map(j=>j.hidden),[false,false]);assert.equal(s.empty.hidden,true);s.destroy();
});
test('no-results state differs from no positions; widget instances are isolated',()=>{
 const a=setup(),b=setup({texts:[]});a.search('missing');assert.equal(a.empty.hidden,false);assert.equal(a.noResults.hidden,false);assert.equal(a.noPositions.hidden,true);assert.equal(b.noResults.hidden,true);assert.equal(b.noPositions.hidden,false);a.search('web');assert.equal(a.empty.hidden,true);a.destroy();b.destroy();
});
test('editor keeps all positions available for inline editing and prevents navigation',()=>{
 const s=setup({editing:true});s.search('missing');assert.deepEqual(s.jobs.map(j=>j.hidden),[false,false]);const e=new Event('click',{cancelable:true});Object.defineProperty(e,'target',{value:{closest:()=>true}});s.root.dispatchEvent(e);assert.equal(e.defaultPrevented,true);s.destroy();
});
test('teardown removes input listeners',()=>{
 const s=setup();s.destroy();s.search('missing');assert.deepEqual(s.jobs.map(j=>j.hidden),[false,false]);
});
