/***************************************************************************
* Floatbox v3.52.2  (frame constrained version)
* June 07, 2009
*
* Copyright (c) 2008-2009 Byron McGregor
* Website: http://randomous.com/floatbox
* License: Attribution-Noncommercial-No Derivative Works 3.0 Unported
*          http://creativecommons.org/licenses/by-nc-nd/3.0/
* Use on any commercial site requires purchase and registration.
* See http://randomous.com/floatbox/license for details.
* This comment block must be retained in all deployments and distributions.
***************************************************************************/

function Floatbox() {
this.defaultOptions = {

/***** BEGIN OPTIONS CONFIGURATION *****/
// See docs/options.html for detailed descriptions.
// All options can be overridden with rev/data-fb-options tag or page options (see docs/instructions.html).

/*** <General Options> ***/
theme:            'auto'    ,// 'auto'|'black'|'white'|'blue'|'yellow'|'red'|'custom'
padding:           24       ,// pixels
panelPadding:      8        ,// pixels
overlayOpacity:    55       ,// 0-100
shadowType:       'drop'    ,// 'drop'|'halo'|'none'
shadowSize:        12       ,// 8|12|16|24
roundCorners:     'all'     ,// 'all'|'top'|'none'
cornerRadius:      12       ,// 8|12|20
roundBorder:       1        ,// 0|1
outerBorder:       4        ,// pixels
innerBorder:       1        ,// pixels
autoFitImages:     true     ,// true|false
resizeImages:      true     ,// true|false
autoFitOther:      false    ,// true|false
resizeOther:       false    ,// true|false
resizeTool:       'cursor'  ,// 'cursor'|'topleft'|'both'
infoPos:          'bl'      ,// 'tl'|'tc'|'tr'|'bl'|'bc'|'br'
controlPos:       'br'      ,// 'tl'|'tr'|'bl'|'br'
centerNav:         false    ,// true|false
boxLeft:          'auto'    ,// 'auto'|pixels|'[-]xx%'
boxTop:           'auto'    ,// 'auto'|pixels|'[-]xx%'
enableDragMove:    false    ,// true|false
stickyDragMove:    true     ,// true|false
enableDragResize:  false    ,// true|false
stickyDragResize:  true     ,// true|false
draggerLocation:  'frame'   ,// 'frame'|'content'
minContentWidth:   140      ,// pixels
minContentHeight:  100      ,// pixels
centerOnResize:    true     ,// true|false
showCaption:       true     ,// true|false
showItemNumber:    true     ,// true|false
showClose:         true     ,// true|false
hideFlash:         true     ,// true|false
hideJava:          true     ,// true|false
disableScroll:     false    ,// true|false
randomOrder:       false    ,// true|false
preloadAll:        true     ,// true|false
autoGallery:       false    ,// true|false
autoTitle:        ''        ,// common caption string to use with autoGallery
printCSS:         ''        ,// path to css file or inline css string to apply to print pages (see showPrint)
language:         'auto'    ,// 'auto'|'en'|... (see the languages folder)
graphicsType:     'auto'    ,// 'auto'|'international'|'english'
/*** </General Options> ***/

/*** <Animation Options> ***/
doAnimations:         true   ,// true|false
resizeDuration:       3.5    ,// 0-10
imageFadeDuration:    3      ,// 0-10
overlayFadeDuration:  4      ,// 0-10
startAtClick:         true   ,// true|false
zoomImageStart:       true   ,// true|false
liveImageResize:      true   ,// true|false
splitResize:         'no'    ,// 'no'|'auto'|'wh'|'hw'
/*** </Animation Options> ***/

/*** <Navigation Options> ***/
navType:            'both'    ,// 'overlay'|'button'|'both'|'none'
navOverlayWidth:     35       ,// 0-50
navOverlayPos:       30       ,// 0-100
showNavOverlay:     'never'   ,// 'always'|'once'|'never'
showHints:          'once'    ,// 'always'|'once'|'never'
enableWrap:          true     ,// true|false
enableKeyboardNav:   true     ,// true|false
outsideClickCloses:  true     ,// true|false
imageClickCloses:    false    ,// true|false
numIndexLinks:       0        ,// number, -1 = no limit
indexLinksPanel:    'control' ,// 'info'|'control'
showIndexThumbs:     true     ,// true|false
/*** </Navigation Options> ***/

/*** <Slideshow Options> ***/
doSlideshow:    false  ,// true|false
slideInterval:  4.5    ,// seconds
endTask:       'exit'  ,// 'stop'|'exit'|'loop'
showPlayPause:  true   ,// true|false
startPaused:    false  ,// true|false
pauseOnResize:  true   ,// true|false
pauseOnPrev:    true   ,// true|false
pauseOnNext:    false   // true|false
/*** </Slideshow Options> ***/
};

/*** <New Child Window Options> ***/
// Will inherit from the primary floatbox options unless overridden here.
// Add any you like.
this.childOptions = {
padding:             16,
overlayOpacity:      45,
resizeDuration:       3,
imageFadeDuration:    3,
overlayFadeDuration:  0
};
/*** </New Child Window Options> ***/

/*** <Custom Paths> ***/
// Normally leave these blank.
// Floatbox will auto-find folders based on the location of floatbox.js and background-images.
// If you have a custom odd-ball configuration, fill in the details here.
// (Trailing slashes please)
this.customPaths = {
	jsModules: ''   ,// default: <floatbox.js>/modules/
	cssModules: ''  ,// default: <floatbox.js>/modules/
	languages: ''   ,// default: <floatbox.js>/languages/
	graphics: ''     // default: background-image:url(<parsed folder>);
};
/*** </Custom Paths> ***/

/***** END OPTIONS CONFIGURATION *****/
this.init();}
Floatbox.prototype={magicClass:"floatbox",panelGap:20,infoLinkGap:16,draggerSize:12,controlOpacity:60,showHintsTime:1600,zoomPopBorder:1,controlSpacing:8,minInfoWidth:80,minIndexWidth:120,ctrlJump:5,slowLoadDelay:750,autoFitSpace:5,maxInitialSize:120,minInitialSize:70,defaultWidth:"85%",defaultHeight:"82%",init:function(){var G=this;G.doc=document;G.docEl=G.doc.documentElement;G.head=G.doc.getElementsByTagName("head")[0];G.bod=G.doc.getElementsByTagName("body")[0];G.getGlobalOptions();G.currentSet=[];G.nodes=[];G.hiddenEls=[];G.timeouts={};G.pos={};var D=navigator.userAgent,F=navigator.appVersion;G.mac=F.indexOf("Macintosh")!==-1;if(window.opera){G.opera=true;G.operaOld=parseFloat(F)<9.5}else{if(document.all){G.ie=true;G.ieOld=parseInt(F.substr(F.indexOf("MSIE")+5),10)<7;G.ie7=parseInt(F.substr(F.indexOf("MSIE")+5),10)===7;G.ieXP=parseInt(F.substr(F.indexOf("Windows NT")+11),10)<6}else{if(D.indexOf("Firefox")!==-1){G.ff=true;G.ffOld=parseInt(D.substr(D.indexOf("Firefox")+8),10)<3;G.ffNew=!G.ffOld;G.ffMac=G.mac}else{if(F.indexOf("WebKit")!==-1){G.webkit=true;G.webkitMac=G.mac}else{if(D.indexOf("SeaMonkey")!==-1){G.seaMonkey=true}}}}}G.browserLanguage=(navigator.language||navigator.userLanguage||navigator.systemLanguage||navigator.browserLanguage||"en").substring(0,2);G.isChild=!!self.fb;if(!G.isChild){G.parent=G.lastChild=G;G.anchors=[];G.children=[];G.popups=[];G.preloads={};G.base=(location.protocol+"//"+location.host).toLowerCase();var B=function(J){return J},A=function(J){return J&&G.doAnimations},I=function(J){return A(J)&&G.resizeDuration};G.modules={enableKeyboardNav:{files:["keydownHandler.js"],test:B},enableDragMove:{files:["mousedownHandler.js"],test:B},enableDragResize:{files:["mousedownHandler.js"],test:B},centerOnResize:{files:["resizeHandler.js"],test:B},showPrint:{files:["printContents.js"],test:B},imageFadeDuration:{files:["setOpacity.js"],test:A},overlayFadeDuration:{files:["setOpacity.js"],test:A},resizeDuration:{files:["setSize.js"],test:A},startAtClick:{files:["getLeftTop.js"],test:I},zoomImageStart:{files:["getLeftTop.js","zoomInOut.js"],test:I},loaded:{}};G.installFolder=G.getPath("script","src",/(.*)f(?:loat|rame)box.js(?:\?|$)/i)||"/floatbox/";G.jsModulesFolder=G.customPaths.jsModules;G.cssModulesFolder=G.customPaths.cssModules;G.languagesFolder=G.customPaths.languages;if(!(G.jsModulesFolder&&G.cssModulesFolder&&G.languagesFolder)){if(!G.jsModulesFolder){G.jsModulesFolder=G.installFolder+"modules/"}if(!G.cssModulesFolder){G.cssModulesFolder=G.installFolder+"modules/"}if(!G.languagesFolder){G.languagesFolder=G.installFolder+"languages/"}}G.graphicsFolder=G.customPaths.graphics;if(!G.graphicsFolder){var E,C=G.doc.createElement("div");C.id="fbPathChecker";G.bod.appendChild(C);if((E=/(?:url\()?["']?(.*)blank.gif["']?\)?$/i.exec(G.getStyle(C,"background-image")))){G.graphicsFolder=E[1]}G.bod.removeChild(C);delete C;if(!G.graphicsFolder){G.graphicsFolder=(G.getPath("link","href",/(.*)floatbox.css(?:\?|$)/i)||"/floatbox/")+"graphics/"}}G.rtl=G.getStyle(G.bod,"direction")==="rtl"||G.getStyle(G.docEl,"direction")==="rtl"}else{G.parent=fb.lastChild;fb.lastChild=G;fb.children.push(G);G.anchors=fb.anchors;G.popups=fb.popups;G.preloads=fb.preloads;G.modules=fb.modules;G.jsModulesFolder=fb.jsModulesFolder;G.cssModulesFolder=fb.cssModulesFolder;G.languagesFolder=fb.languagesFolder;G.graphicsFolder=fb.graphicsFolder;G.strings=fb.strings;G.rtl=fb.rtl;if(G.parent.isSlideshow){G.parent.setPause(true)}}var H=G.graphicsFolder;G.resizeUpCursor=H+"magnify_plus.cur";G.resizeDownCursor=H+"magnify_minus.cur";G.notFoundImg=H+"404.jpg";G.blank=H+"blank.gif";G.zIndex={base:90000+(G.isChild?12*fb.children.length:0),fbOverlay:1,fbBox:2,fbCanvas:3,fbContent:4,fbMainLoader:5,fbLeftNav:6,fbRightNav:6,fbOverlayPrev:7,fbOverlayNext:7,fbResizer:8,fbInfoPanel:9,fbControlPanel:9,fbDragger:10,fbZoomDiv:11};var E=/\bautoStart=(.+?)(?:&|$)/i.exec(location.search);G.autoHref=E?E[1]:false},tagAnchors:function(C){var B=this;function A(F){var G=C.getElementsByTagName(F);for(var E=0,D=G.length;E<D;E++){B.tagOneAnchor(G[E],false)}}A("a");A("area");B.getModule("licenseKey.js");B.getModule("core.js");B.getModules(B.defaultOptions,true);B.getModules(B.pageOptions,false);if(B.popups.length){B.getModule("getLeftTop.js");B.getModule("setOpacity.js");B.getModule("tagPopup.js");if(B.tagPopup){while(B.popups.length){B.tagPopup(B.popups.pop())}}}if(B.ieOld){B.getModule("ieOld.js")}},tagOneAnchor:function(F,I){var L=this,A=!!F.getAttribute,H;if(A){var J={href:F.getAttribute("href")||"",rev:F.getAttribute("data-fb-options")||F.getAttribute("rev")||"",rel:F.getAttribute("rel")||"",title:F.getAttribute("title")||"",className:F.className||"",ownerDoc:F.ownerDocument,anchor:F,thumb:(F.getElementsByTagName("img")||[])[0]}}else{var J=F;J.anchor=J.thumb=J.ownerDoc=false}if((H=new RegExp("(?:^|\\s)"+L.magicClass+"(\\S*)","i").exec(J.className))){J.tagged=true;if(H[1]){J.group=H[1]}}if(L.autoGallery&&!J.tagged&&L.fileType(J.href)==="img"&&J.rel.toLowerCase()!=="nofloatbox"&&J.className.toLowerCase().indexOf("nofloatbox")===-1){J.tagged=true;J.group=".autoGallery";if(L.autoTitle&&!J.title){J.title=L.autoTitle}}if(!J.tagged){if((H=/^(?:floatbox|gallery|iframe|slideshow|lytebox|lyteshow|lyteframe|lightbox)(.*)/i.exec(J.rel))){J.tagged=true;J.group=H[1];if(/^(slide|lyte)show/i.test(J.rel)){J.rev+=" doSlideshow:true"}else{if(/^(i|lyte)frame/i.test(J.rel)){J.rev+=" type:iframe"}}}}if(J.thumb&&((H=/(?:^|\s)fbPop(up|down)(?:\s|$)/i.exec(F.className)))){J.popup=true;J.popupType=H[1];L.popups.push(J)}if(I!==false){J.tagged=true}if(J.tagged){J.options=L.parseOptionString(J.rev);J.href=J.options.href||J.href;J.group=J.options.group||J.group||"";if(!J.href&&J.options.showThis!==false){return }J.level=fb.children.length+(fb.lastChild.fbBox&&!J.options.sameBox?1:0);var E=L.anchors.length;while(E--){var G=L.anchors[E];if(G.href===J.href&&G.rev===J.rev&&G.rel===J.rel&&G.title===J.title&&G.level===J.level&&(G.anchor===J.anchor||(J.ownerDoc&&J.ownerDoc!==L.doc))){G.anchor=J.anchor;G.thumb=J.thumb;break}}if(E===-1){if(J.options.type){
J.options.type=J.options.type.replace(/^(flash|quicktime|silverlight)$/i,"media:$1")}if(J.html){J.type="direct"}else{J.type=J.options.type||L.fileType(J.href)}if(J.type==="html"){J.type="iframe";var H=/#(\w+)/.exec(J.href);if(H){var K=document;if(J.anchor){K=J.ownerDoc||K}if(K===document&&L.itemToShow&&L.itemToShow.anchor){K=L.itemToShow.ownerDoc||K}var C=K.getElementById(H[1]);if(C){J.type="inline";J.sourceEl=C}}}L.anchors.push(J);L.getModules(J.options,false);if(J.type.indexOf("media")===0){L.getModule("mediaHTML.js")}if(L.autoHref){if(J.options.showThis!==false&&L.autoHref===J.href.substr(J.href.length-L.autoHref.length)){L.autoStart=J}}else{if(J.options.autoStart===true){L.autoStart=J}else{if(J.options.autoStart==="once"){var H=/fbAutoShown=(.+?)(?:;|$)/.exec(document.cookie),D=H?H[1]:"",B=escape(J.href);if(D.indexOf(B)===-1){L.autoStart=J;document.cookie="fbAutoShown="+D+B+"; path=/"}}}}if(L.ieOld&&J.anchor){J.anchor.hideFocus="true"}}if(A){F.onclick=function(N){if(!N){var M=this.ownerDocument;N=M&&M.parentWindow&&M.parentWindow.event}if(!(N&&(N.ctrlKey||N.metaKey||N.shiftKey||N.altKey))||J.options.showThis===false||!/img|iframe/.test(J.type)){var O=function(){if(L.start){L.start(F)}else{setTimeout(O,100)}return L.stopEvent(N)};O()}}}}if(I===true){return J}},fileType:function(A){var C=this,D=(A||"").toLowerCase(),B=D.indexOf("?");if(B!==-1){D=D.substr(0,B)}D=D.substr(D.lastIndexOf(".")+1);if(/^(jpe?g|png|gif|bmp)$/.test(D)){return"img"}if(D==="swf"||/^(http:)?\/\/(www.)?(youtube|dailymotion)\.com\/(v|swf)\//i.test(A)){return"media:flash"}if(/^(mov|mpe?g|movie|3gp|3g2|m4v|mp4|qt)$/.test(D)){return"media:quicktime"}if(D==="xap"){return"media:silverlight"}return"html"},getGlobalOptions:function(){var C=this;if(!C.isChild){C.setOptions(C.defaultOptions);if(typeof setFloatboxOptions==="function"){setFloatboxOptions()}C.pageOptions=typeof fbPageOptions==="object"?fbPageOptions:{}}else{for(var B in C.defaultOptions){if(C.defaultOptions.hasOwnProperty(B)){C[B]=C.parent[B]}}C.setOptions(C.childOptions);C.pageOptions={};for(var B in C.parent.pageOptions){if(C.parent.pageOptions.hasOwnProperty(B)){C.pageOptions[B]=C.parent.pageOptions[B]}}if(typeof fbChildOptions==="object"){for(var B in fbChildOptions){if(fbChildOptions.hasOwnProperty(B)){C.pageOptions[B]=fbChildOptions[B]}}}}C.setOptions(C.pageOptions);if(C.pageOptions.enableCookies){var A=/fbOptions=(.+?)(;|$)/.exec(document.cookie);if(A){C.setOptions(C.parseOptionString(A[1]))}}C.setOptions(C.parseOptionString(location.search.substring(1)))},parseOptionString:function(H){var K=this;if(!H){return{}}var G=[],E,C=/`([^`]*?)`/g;C.lastIndex=0;while((E=C.exec(H))){G.push(E[1])}if(G.length){H=H.replace(C,"``")}H=H.replace(/\s*[:=]\s*/g,":");H=H.replace(/\s*[;&]\s*/g," ");H=H.replace(/^\s+|\s+$/g,"");H=H.replace(/(:\d+)px\b/gi,function(L,M){return M});var B={},F=H.split(" "),D=F.length;while(D--){var J=F[D].split(":"),A=J[0],I=J[1];if(typeof I==="string"){if(!isNaN(I)){I=+I}else{if(I==="true"){I=true}else{if(I==="false"){I=false}}}}if(I==="``"){I=G.pop()||""}B[A]=I}return B},setOptions:function(C){var B=this;for(var A in C){if(B.defaultOptions.hasOwnProperty(A)){B[A]=C[A]}}},getModule:function(E){var D=this;if(D.modules.loaded[E]){return }if(E.slice(-3)===".js"){var B="script",A={type:"text/javascript",src:(E.indexOf("licenseKey")===-1?D.jsModulesFolder:D.installFolder)+E}}else{var B="link",A={rel:"stylesheet",type:"text/css",href:D.cssModulesFolder+E}}var F=D.doc.createElement(B);for(var C in A){if(A.hasOwnProperty(C)){F.setAttribute(C,A[C])}}D.head.appendChild(F);D.modules.loaded[E]=true},getModules:function(C,G){var F=this;for(var B in C){if(F.modules.hasOwnProperty(B)){var E=F.modules[B],H=G?F[B]:C[B],A=0,D=E.files.length;while(D--){if(E.test(H)){F.getModule(E.files[D]);A++}}if(A===E.files.length){delete F.modules[B]}}}},getStyle:function(A,C){if(!(A&&C)){return""}if(A.currentStyle){return A.currentStyle[C.replace(/-(\w)/g,function(D,E){return E.toUpperCase()})]||""}else{var B=A.ownerDocument.defaultView||A.ownerDocument.parentWindow;return(B.getComputedStyle&&B.getComputedStyle(A,"").getPropertyValue(C))||""}},getPath:function(B,A,G){var C,E=document.getElementsByTagName(B),D=E.length;while(D--){if((C=G.exec(E[D][A]))){var F=C[1].replace("compressed/","");return F||"./"}}return""},addEvent:function(B,C,A){if(B.addEventListener){B.addEventListener(C,A,false)}else{if(B.attachEvent){B.attachEvent("on"+C,A)}else{B["prior"+C]=B["on"+C];B["on"+C]=A}}},removeEvent:function(B,C,A){if(B.removeEventListener){B.removeEventListener(C,A,false)}else{if(B.detachEvent){B.detachEvent("on"+C,A)}else{B["on"+C]=B["prior"+C];delete B["prior"+C]}}},stopEvent:function(B){if(B){if(B.stopPropagation){B.stopPropagation()}if(B.preventDefault){B.preventDefault()}try{B.cancelBubble=true}catch(A){}try{B.returnValue=false}catch(A){}}return false},preloadImages:function(A,C){var B=this;setTimeout(function(){B.preloadImages(A,C)},100)}};var fb;function initfb(){if(arguments.callee.done){return }arguments.callee.done=true;if(document.compatMode==="BackCompat"){alert("Floatbox does not support quirks mode.\nPage needs to have a valid doctype declaration.");return }fb=new Floatbox();fb.tagAnchors(self.document.getElementsByTagName("body")[0])}if(document.addEventListener){document.addEventListener("DOMContentLoaded",initfb,false)};(function(){/*@cc_on if(document.body){try{document.createElement('div').doScroll('left');return initfb();}catch(e){}}/*@if (false) @*/if(/loaded|complete/.test(document.readyState))return initfb();/*@end @*/if(!initfb.done)setTimeout(arguments.callee,30);})();fb_prevOnload=window.onload;window.onload=function(){if(arguments.callee.done){return }arguments.callee.done=true;if(typeof fb_prevOnload==="function"){fb_prevOnload()}initfb();(function(){if(!(self.fb&&self.fb.start)){return setTimeout(arguments.callee,50)}if(fb.autoStart&&fb.autoStart.ownerDoc){if(fb.autoStart.ownerDoc===self.document){fb.setTimeout("start",function(){fb.start(fb.autoStart)},100)}}else{setTimeout(function(){if(typeof fb.preloads.count==="undefined"){fb.preloadImages("",true)}},200)}})()};
