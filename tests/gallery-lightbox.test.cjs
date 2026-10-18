const { test } = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
function setup({editing=false, enabled=true, single=false}={}) {
  class Element extends EventTarget {
    constructor(name='') { super(); this.name=name; this.open=false; this.dataset={};this.isConnected=true;this.style={};this.attrs={};this.children={};this.complete=true;this.naturalWidth=1000; }
    querySelector(selector) {return this.children[selector];}
    querySelectorAll() {return this.items||[];}
    setAttribute(key,value){this.attrs[key]=value;}
    removeAttribute(key){delete this.attrs[key];}
    toggleAttribute(key,value){if(value)this.attrs[key]='';else delete this.attrs[key];}
    focus(){this.focused=true;}
    closest(selector){return selector===this.name?this:null;}
    showModal(){this.open=true;}
    close(){this.open=false;this.dispatchEvent(new Event('close'));}
    remove(){this.removed=true;}
    getBoundingClientRect(){return {left:0,top:0,width:100,height:100};}
    decode(){return Promise.resolve();}
    set innerHTML(value){this.children={img:new Element('img'),'[role="status"]':new Element('status'),'.jn-gallery-dialog__close':new Element('.jn-gallery-dialog__close')};}
  }
  const root=new Element(), media=new Element(), document={documentElement:{style:{overflow:'scroll'}},body:{append(dialog){document.dialog=dialog;}},createElement(){return new Element();}};
  media.matches=true;root.dataset.lightbox=enabled?'yes':'';
  root.items=Array.from({length:single?1:3},(_,i)=>{const a=new Element('.jn-gallery__item');a.href='/full-'+i+'.jpg';a.children.img=new Element('img');a.children.img.alt='Picture '+i;return a;});
  let handler;const window={matchMedia:()=>media,elementorFrontend:{isEditMode:()=>editing,hooks:{addAction(name,callback){callback([{querySelector:()=>root}]);}},elementsHandler:{addHandler(Type,args){handler=new Type();Object.assign(handler,args);handler.onInit();}}},elementorModules:{frontend:{handlers:{Base:class {onInit(){}onDestroy(){}}}}}};
  vm.runInNewContext(fs.readFileSync(__dirname+'/../assets/js/gallery.js','utf8'),{window,document,AbortController,elementorFrontend:window.elementorFrontend,elementorModules:window.elementorModules,jQuery:()=>({on(){}})});
  function click(target,receiver=root,extra={}){const e=new Event('click',{cancelable:true});Object.defineProperty(e,'target',{value:target});for(const [key,value] of Object.entries(extra))Object.defineProperty(e,key,{value});receiver.dispatchEvent(e);return e;}
  function key(value){const e=new Event('keydown',{cancelable:true});Object.defineProperty(e,'key',{value});document.dialog.dispatchEvent(e);return e;}
  return {root,document,handler,click,key};
}
test('lightbox opens full image, navigates and restores focus and scroll on Escape',async()=>{
 const s=setup();assert.equal(s.click(s.root.items[0]).defaultPrevented,true);const d=s.document.dialog;await Promise.resolve();assert.equal(d.open,true);assert.equal(d.querySelector('img').src,'/full-0.jpg');assert.equal(d.querySelector('img').alt,'Picture 0');assert.equal(s.document.documentElement.style.overflow,'hidden');
 s.key('ArrowLeft');assert.equal(d.querySelector('img').src,'/full-2.jpg');s.key('ArrowRight');assert.equal(d.querySelector('img').src,'/full-0.jpg');
 d.dispatchEvent(new Event('cancel',{cancelable:true}));assert.equal(d.open,false);assert.equal(s.document.documentElement.style.overflow,'scroll');assert.equal(s.root.items[0].focused,true);s.handler.onDestroy();
});
test('single-image controls, backdrop closing and teardown',()=>{
 const s=setup({single:true});s.click(s.root.items[0]);const d=s.document.dialog;assert.equal(d.attrs['data-single'],'');s.click(d,d);assert.equal(d.open,false);s.click(s.root.items[0]);s.handler.onDestroy();assert.equal(d.removed,true);assert.equal(s.document.documentElement.style.overflow,'scroll');
});
test('editing suppresses navigation and disabled lightbox retains normal image link',()=>{
 const editor=setup({editing:true});assert.equal(editor.click(editor.root.items[0]).defaultPrevented,true);assert.equal(editor.document.dialog,undefined);editor.handler.onDestroy();
 const disabled=setup({enabled:false});assert.equal(disabled.click(disabled.root.items[0]).defaultPrevented,false);assert.equal(disabled.document.dialog,undefined);disabled.handler.onDestroy();
 const modifier=setup();assert.equal(modifier.click(modifier.root.items[0],modifier.root,{ctrlKey:true}).defaultPrevented,false);assert.equal(modifier.document.dialog,undefined);modifier.handler.onDestroy();
});
