/* Floatbox v4.04 */
fb.extend(fb.proto,{AX:function(f,c){var b=fb,e=b.AZ[f],a=e.FY[c].img,d=a.getAttribute("data-fb-src")||a.getAttribute("longdesc");if(d&&d!=="done"){a.src=d;a.setAttribute("data-fb-src","done")}},AY:function(){var b=fb,a=b.AZ.length,d,c;while(a--){if((d=b.AZ[a])){c=d.showing+1;if(c>=d.FY.length){c=0}b.AX(a,c);if(b.preloadAll&&fb.GR.count&&d.FY[d.showing].href){setTimeout(function(){fb.preload(d.FY[d.showing].href)},200)}}}},BB:function(m,j,n){var q=fb,a=q.AZ[m],f=n?0:q.cycleFadeDuration*2;if(!a){return}if(j>=a.FY.length){j=0}if(j<0){j=a.FY.length-1}if(j===a.showing){return}var h=a.FY[a.showing],g=a.FY[j],l=typeof a.previous==="number"?a.FY[a.previous]:null,b=h.img,e=g.img,d=l&&l.img,p=h.span,o=g.span,c=l&&l.span,i=function(){q.HV(b,0,f/1.3,function(){h.style.display="none"},null,"cycleThisImg"+m)},k=q.JX.ie?function(){if(p){p.style.visibility="hidden"}if(o){o.style.visibility="visible"}}:null;q.AC("fade_cycleThisImg"+m);q.AC("fade_cycleNextImg"+m);q.AC("fade_cycleThisSpan"+m);q.AC("fade_cycleNextSpan"+m);q.HV(b,100,0);q.HV(d,0,0);q.HV(e,0,0);if(q.JX.ie){if(p){p.style.visibility="visible"}if(c){c.style.visibility="hidden"}if(o){o.style.visibility="hidden"}}else{q.HV(p,100,0);q.HV(c,0,0);q.HV(o,0,0)}if(l){l.style.display="none"}h.style.zIndex="10";g.style.zIndex="20";g.style.display="";if(a.div.offsetHeight<g.offsetHeight){a.div.style.height=g.offsetHeight+"px"}q.HV(e,100,f,i,k,"cycleNextImg"+m);if(!q.JX.ie){if(p){p.style.opacity="1";q.HV(p,0,f,null,null,"cycleThisSpan"+m)}if(o){q.HV(o,100,f,null,null,"cycleNextSpan"+m)}}a.previous=a.showing;a.showing=j;q.AY()},BC:function(){var c=fb,e,d,a=fb.instances.length,b=c.AZ.length;while(a--){if((d=fb.instances[a])&&d.fbBox&&d.modal){break}}while(b--){if((e=c.AZ[b])&&e.DR>=a&&!e.hovered){c.BB(b,e.showing+1)}}c.AZ.timer=setTimeout(c.BC,c.CI()*1000)},cycleGo:function(){var a=fb;a.cycleStop();a.AZ.timer=setTimeout(a.BC,a.CI()*600)},cycleStop:function(){var a=fb;clearTimeout(a.AZ.timer)},CI:function(){var c=fb,b=c.AZ.length,d,a;while(b--){if((d=c.AZ[b])&&(a=d.FY[d.showing].DL)){if((a=c.CJ(a))){return a}}}return c.cycleInterval},AW:function(m,e){var f=fb,p=m.length;while(p--){var g=m[p],o=fb.AZ.length;while(o--){if(fb.AZ[o].div===g){fb.AZ.splice(o,1)}}var c=g.childNodes,r=[],d=0;for(var o=0;o<c.length;o++){var l=c[o],w=f.BU(l);if(/^(a|div|img)$/.test(w)){if(w==="img"){var u=g.ownerDocument.createElement("div");u.style.display=fb.getStyle(l,"display");f.setInnerHTML(u,f.getOuterHTML(l));g.replaceChild(u,l);l=u}var v=l.getElementsByTagName("img")[0];if(v){v.style.display="inline";if(l.offsetWidth){d=r.length;if(g.offsetHeight<l.offsetHeight){g.style.height=l.offsetHeight+"px"}}l.DL=f.GK(l.getAttribute("data-fb-options")||l.getAttribute("rev")||"").cycleInterval||f.GK(v.getAttribute("data-fb-options")||v.getAttribute("rev")||"").cycleInterval||null;l.img=v;l.span=l.getElementsByTagName("span")[0];r.push(l)}}}if(r.length>1){var b=g.getAttribute("data-fb-options")||g.getAttribute("rev"),h=(b&&f.GK(b).cyclePauseOnHover),o=r.length;if(typeof h!=="boolean"){h=f.cyclePauseOnHover}while(o--){var l=r[o];if(h){l.BA=fb.AZ.length;l.onmouseover=function(){fb.AZ[this.BA].hovered=true};l.onmouseout=function(){fb.AZ[this.BA].hovered=false}}if(f.BU(r[o])==="a"){var n=f.anchors.length;while(n--){var s=f.anchors[n];if(l===s.E){var q=f.AZ.length;s.IA.preloadCycleItem="fb.AX("+q+", "+o+")";s.IA.setCycleItem="fb.BB("+q+", "+o+", true);";break}}}}f.AZ.push({FY:r,showing:d,DR:e,div:g})}}f.AY();if(!f.JB.cycle){f.cycleGo()}},cyclerLoaded:true});