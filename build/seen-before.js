!function(){"use strict";var e,n={738:function(){var e=window.wp.blocks,n=window.wp.element,r=(window.wp.i18n,window.wp.blockEditor),t=window.wp.components,o=window.wp.data,i=window.wp.coreData;(0,e.registerBlockType)("bigram/seen-before",{apiVersion:2,attributes:{seenBefore:{type:"string",source:"meta",meta:"_seen_before"}},edit:function(e){let{setAttributes:s,attributes:u}=e;const a=(0,r.useBlockProps)(),c=(0,o.useSelect)((e=>e("core/editor").getCurrentPostType()),[]),[f,l]=(0,i.useEntityProp)("postType",c,"meta"),p=f._seen_before;console.log(u.seenBefore);var w="1"===u.seenBefore?"time":"times";return(0,n.createElement)("div",a,(0,n.createElement)(t.TextControl,{label:"Seen before:",value:p,onChange:function(e){l({...f,_seen_before:e})}}),(0,n.createElement)("p",null,"Seen before: ",u.seenBefore," ",w))},save:function(){return null}})}},r={};function t(e){var o=r[e];if(void 0!==o)return o.exports;var i=r[e]={exports:{}};return n[e](i,i.exports,t),i.exports}t.m=n,e=[],t.O=function(n,r,o,i){if(!r){var s=1/0;for(f=0;f<e.length;f++){r=e[f][0],o=e[f][1],i=e[f][2];for(var u=!0,a=0;a<r.length;a++)(!1&i||s>=i)&&Object.keys(t.O).every((function(e){return t.O[e](r[a])}))?r.splice(a--,1):(u=!1,i<s&&(s=i));if(u){e.splice(f--,1);var c=o();void 0!==c&&(n=c)}}return n}i=i||0;for(var f=e.length;f>0&&e[f-1][2]>i;f--)e[f]=e[f-1];e[f]=[r,o,i]},t.o=function(e,n){return Object.prototype.hasOwnProperty.call(e,n)},function(){var e={529:0,763:0};t.O.j=function(n){return 0===e[n]};var n=function(n,r){var o,i,s=r[0],u=r[1],a=r[2],c=0;if(s.some((function(n){return 0!==e[n]}))){for(o in u)t.o(u,o)&&(t.m[o]=u[o]);if(a)var f=a(t)}for(n&&n(r);c<s.length;c++)i=s[c],t.o(e,i)&&e[i]&&e[i][0](),e[i]=0;return t.O(f)},r=self.webpackChunkbigram=self.webpackChunkbigram||[];r.forEach(n.bind(null,0)),r.push=n.bind(null,r.push.bind(r))}();var o=t.O(void 0,[763],(function(){return t(738)}));o=t.O(o)}();