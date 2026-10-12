'use strict';
const assert = require('assert');
const fs = require('fs');
const path = require('path');
const vm = require('vm');
const source = fs.readFileSync(path.join(__dirname,'../assets/js/welcome-frequency.js'),'utf8');
function scenario({ loggedIn=false, blockedStorage=false, oldDismissal=0, sessionSeen=false }={}) {
  const listeners = {};
  const attrs = [];
  const controls = Array.from({length:3}, () => ({addEventListener(type,fn){listeners['control'+attrs.length]=fn;attrs.push(type);}}));
  const root={hidden:true,querySelectorAll(){return controls;}};
  let cookie=oldDismissal?('sabri_welcome_dismissed_at='+oldDismissal):'';
  const doc={getElementById(){return root;},get cookie(){return cookie;},set cookie(value){cookie=value;},addEventListener(name,fn){listeners[name]=fn;}};
  const sessionData={};
  if(sessionSeen)sessionData['sabri-shell-welcome:v1:'+(loggedIn?'user-7':'guest')]='1';
  const localData={};
  function store(data){return {getItem(k){return data[k]||null;},setItem(k,v){data[k]=String(v);}};}
  const calls=[];
  const win={sabriWelcomeFrequency:{loggedIn,seconds:2592000,scope:loggedIn?'user-7':'guest',cookieName:'sabri_welcome_dismissed_at',endpoint:'/wp-json/sabri-shell/v1/welcome-dismiss',nonce:'nonce'},location:{protocol:'https:'},fetch(url,opts){calls.push({url,opts});return Promise.resolve({ok:true});}};
  Object.defineProperty(win,'sessionStorage',{get(){if(blockedStorage)throw new Error('storage blocked');return store(sessionData);}});
  Object.defineProperty(win,'localStorage',{get(){if(blockedStorage)throw new Error('storage blocked');return store(localData);}});
  vm.runInNewContext(source,{window:win,document:doc,Date,Number,Array,console});
  return {root,listeners,controls,sessionData,localData,calls,get cookie(){return cookie;}};
}
const now=Math.floor(Date.now()/1000);
const guest=scenario();assert.strictEqual(guest.root.hidden,false,'first guest visit visible');
guest.listeners.control0();assert.strictEqual(guest.root.hidden,true,'close hides welcome');assert(guest.cookie.includes('Max-Age=2592000'),'30-day cookie');
assert.strictEqual(guest.sessionData['sabri-shell-welcome:v1:guest'],'1','same-session suppression');
const notDue=scenario({oldDismissal:now-90});assert.strictEqual(notDue.root.hidden,true,'guest 30-day suppression');
const due=scenario({oldDismissal:now-2592100});assert.strictEqual(due.root.hidden,false,'guest returns after 30 days');
const noStorage=scenario({blockedStorage:true});assert.strictEqual(noStorage.root.hidden,false,'storage denial must not hide eligible welcome');
noStorage.listeners.keydown({key:'Escape'});assert.strictEqual(noStorage.root.hidden,true,'Escape dismisses despite storage denial');
const member=scenario({loggedIn:true});member.listeners.control2();assert.strictEqual(member.calls.length,1,'member preference posted');assert.strictEqual(member.calls[0].opts.headers['X-WP-Nonce'],'nonce','authenticated nonce');
assert.strictEqual(scenario({loggedIn:true,sessionSeen:true}).root.hidden,true,'member session dedup');
console.log('File20 welcome JavaScript first visit, 30d, session, storage denial, escape, account POST PASS');
