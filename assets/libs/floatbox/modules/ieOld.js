/* Floatbox v3.52.2 */
Floatbox.prototype.pngFix=function(){var B=this,A=[B.fbShadowTop,B.fbShadowRight,B.fbShadowBottom,B.fbShadowLeft];while(A.length){var C=A.pop();if(C){DD_belatedPNG.fixPng(C)}}};Floatbox.prototype.stretchOverlay=function(){var E=fb.lastChild;if(arguments.length===1){E.clearTimeout("stretch");E.setTimeout("stretch",E.stretchOverlay,100)}else{delete E.timeouts.stretch;if(!E.fbBox){return }var F=E.fbBox.offsetLeft+E.fbBox.offsetWidth,B=E.fbBox.offsetTop+E.fbBox.offsetHeight,G=E.getDisplaySize(),A=E.getScroll(),D=E.fbOverlay.style;D.width=D.height="0";var C=(E.rtl&&A.left)?E.docEl.clientWidth-E.docEl.scrollWidth:0;D.left=C+"px";D.width=Math.max(F,E.bod.scrollWidth,E.bod.clientWidth,E.docEl.clientWidth,G.width+A.left)+"px";D.height=Math.max(B,E.bod.scrollHeight,E.bod.clientHeight,E.docEl.clientHeight,G.height+A.top)+"px"}};var DD_belatedPNG={ns:"DD_belatedPNG",imgSize:{},createVmlNameSpace:function(){if(document.namespaces&&!document.namespaces[this.ns]){document.namespaces.add(this.ns,"urn:schemas-microsoft-com:vml")}},createVmlStyleSheet:function(){var A=document.createElement("style");document.documentElement.firstChild.insertBefore(A,document.documentElement.firstChild.firstChild);var B=A.styleSheet;B.addRule(this.ns+"\\:*","{behavior:url(#default#VML)}");B.addRule(this.ns+"\\:shape","position:absolute;");B.addRule("img."+this.ns+"_sizeFinder","behavior:none; border:none; position:absolute; z-index:-1; top:-10000px; visibility:hidden;");this.styleSheet=B},readPropertyChange:function(){var B=event.srcElement;if(event.propertyName.search("background")!=-1||event.propertyName.search("border")!=-1){DD_belatedPNG.applyVML(B)}if(event.propertyName=="style.display"){var C=(B.currentStyle.display=="none")?"none":"block";for(var A in B.vml){B.vml[A].shape.style.display=C}}if(event.propertyName.search("filter")!=-1){DD_belatedPNG.vmlOpacity(B)}},vmlOpacity:function(B){if(B.currentStyle.filter.search("lpha")!=-1){var A=B.currentStyle.filter;A=parseInt(A.substring(A.lastIndexOf("=")+1,A.lastIndexOf(")")),10)/100;B.vml.color.shape.style.filter=B.currentStyle.filter;B.vml.image.fill.opacity=A}},handlePseudoHover:function(A){setTimeout(function(){DD_belatedPNG.applyVML(A)},1)},fix:function(A){var C=A.split(",");for(var B=0;B<C.length;B++){this.styleSheet.addRule(C[B],"behavior:expression(DD_belatedPNG.fixPng(this))")}},applyVML:function(A){A.runtimeStyle.cssText="";this.vmlFill(A);this.vmlOffsets(A);this.vmlOpacity(A);if(A.isImg){this.copyImageBorders(A)}},attachHandlers:function(F){var C=this;var B={resize:"vmlOffsets",move:"vmlOffsets"};if(F.nodeName=="A"){var D={mouseleave:"handlePseudoHover",mouseenter:"handlePseudoHover",focus:"handlePseudoHover",blur:"handlePseudoHover"};for(var A in D){B[A]=D[A]}}for(var E in B){F.attachEvent("on"+E,function(){C[B[E]](F)})}F.attachEvent("onpropertychange",this.readPropertyChange)},giveLayout:function(A){A.style.zoom=1;if(A.currentStyle.position=="static"){A.style.position="relative"}},copyImageBorders:function(B){var C={borderStyle:true,borderWidth:true,borderColor:true};for(var A in C){B.vml.color.shape.style[A]=B.currentStyle[A]}},vmlFill:function(E){if(!E.currentStyle){return }else{var D=E.currentStyle}for(var C in E.vml){E.vml[C].shape.style.zIndex=D.zIndex}E.runtimeStyle.backgroundColor="";E.runtimeStyle.backgroundImage="";var A=(D.backgroundColor=="transparent");var F=true;if(D.backgroundImage!="none"||E.isImg){if(!E.isImg){E.vmlBg=D.backgroundImage;E.vmlBg=E.vmlBg.substr(5,E.vmlBg.lastIndexOf('")')-5)}else{E.vmlBg=E.src}var G=this;if(!G.imgSize[E.vmlBg]){var B=document.createElement("img");G.imgSize[E.vmlBg]=B;B.className=G.ns+"_sizeFinder";B.runtimeStyle.cssText="behavior:none; position:absolute; left:-10000px; top:-10000px; border:none;";B.attachEvent("onload",function(){this.width=this.offsetWidth;this.height=this.offsetHeight;G.vmlOffsets(E)});B.src=E.vmlBg;B.removeAttribute("width");B.removeAttribute("height");document.body.insertBefore(B,document.body.firstChild)}E.vml.image.fill.src=E.vmlBg;F=false}E.vml.image.fill.on=!F;E.vml.image.fill.color="none";E.vml.color.shape.style.backgroundColor=D.backgroundColor;E.runtimeStyle.backgroundImage="none";E.runtimeStyle.backgroundColor="transparent"},vmlOffsets:function(B){var F=B.currentStyle;var M={W:B.clientWidth+1,H:B.clientHeight+1,w:this.imgSize[B.vmlBg].width,h:this.imgSize[B.vmlBg].height,L:B.offsetLeft,T:B.offsetTop,bLW:B.clientLeft,bTW:B.clientTop};var A=(M.L+M.bLW==1)?1:0;var C=function(N,P,Q,O,R,S){N.coordsize=O+","+R;N.coordorigin=S+","+S;N.path="m0,0l"+O+",0l"+O+","+R+"l0,"+R+" xe";N.style.width=O+"px";N.style.height=R+"px";N.style.left=P+"px";N.style.top=Q+"px"};C(B.vml.color.shape,(M.L+(B.isImg?0:M.bLW)),(M.T+(B.isImg?0:M.bTW)),(M.W-1),(M.H-1),0);C(B.vml.image.shape,(M.L+M.bLW),(M.T+M.bTW),(M.W),(M.H),1);var E={X:0,Y:0};var L=function(P,N){var O=true;switch(N){case"left":case"top":E[P]=0;break;case"center":E[P]=0.5;break;case"right":case"bottom":E[P]=1;break;default:if(N.search("%")!=-1){E[P]=parseInt(N,10)*0.01}else{O=false}}var Q=(P=="X");E[P]=Math.ceil(O?((M[Q?"W":"H"]*E[P])-(M[Q?"w":"h"]*E[P])):parseInt(N,10));if(E[P]===0){E[P]++}};for(var H in E){L(H,F["backgroundPosition"+H])}B.vml.image.fill.position=(E.X/M.W)+","+(E.Y/M.H);var K=F.backgroundRepeat;var D={T:1,R:M.W+A,B:M.H,L:1+A};var J={X:{b1:"L",b2:"R",d:"W"},Y:{b1:"T",b2:"B",d:"H"}};if(K!="repeat"){var G={T:(E.Y),R:(E.X+M.w),B:(E.Y+M.h),L:(E.X)};if(K.search("repeat-")!=-1){var I=K.split("repeat-")[1].toUpperCase();G[J[I].b1]=1;G[J[I].b2]=M[J[I].d]}if(G.B>M.H){G.B=M.H}B.vml.image.shape.style.clip="rect("+G.T+"px "+(G.R+A)+"px "+G.B+"px "+(G.L+A)+"px)"}else{B.vml.image.shape.style.clip="rect("+D.T+"px "+D.R+"px "+D.B+"px "+D.L+"px)"}},fixPng:function(B){B.style.behavior="none";if(B.nodeName=="BODY"||B.nodeName=="TD"||B.nodeName=="TR"){return }B.isImg=false;if(B.nodeName=="IMG"){if(B.src.toLowerCase().search(/\.png$/)!=-1){B.isImg=true;B.style.visibility="hidden"}else{return }}else{if(B.currentStyle.backgroundImage.toLowerCase().search(".png")==-1){return }}var F=DD_belatedPNG;B.vml={color:{},image:{}};var A={shape:{},fill:{}};for(var C in B.vml){for(var E in A){var D=F.ns+":"+E;B.vml[C][E]=document.createElement(D)}B.vml[C].shape.stroked=false;B.vml[C].shape.appendChild(B.vml[C].fill);B.parentNode.insertBefore(B.vml[C].shape,B)}B.vml.image.shape.fillcolor="none";B.vml.image.fill.type="tile";B.vml.color.fill.on=false;F.attachHandlers(B);F.giveLayout(B);F.giveLayout(B.offsetParent);F.applyVML(B)}};try{document.execCommand("BackgroundImageCache",false,true)}catch(r){}(function(){try{DD_belatedPNG.createVmlNameSpace();DD_belatedPNG.createVmlStyleSheet();DD_belatedPNG.ready=true}catch(A){setTimeout(arguments.callee,50)}})();if(!fb.ieOldInitialized&&fb.fbOverlay){fb.stretchOverlay();window.attachEvent("onresize",fb.stretchOverlay);window.attachEvent("onscroll",fb.stretchOverlay);fb.pngFix();fb.ieOldInitialized=true};
