/* Floatbox v4.04 */
fb.extend(fb.proto,{FK:function(){var a=this;a.HN=50;a.HO=a.HN*0.001;a.HP=0;a.IY=a.IZ=a.EI=a.EJ=a.IK=-1;a.target=null;a.CE=function(b){b=b||a.JQ.event;a.IY=b.clientX+a.dx;a.IZ=b.clientY+a.dy;a.target=b.target||b.srcElement;if(a.EI===-1){a.EI=a.IY}if(a.EJ===-1){a.EJ=a.IZ}if(typeof a.EL==="function"){a.EL()}};a.HL=function(){var c=a.IY-a.EI,b=a.IZ-a.EJ;a.IK=Math.sqrt(c*c+b*b)/a.HO;a.EI=a.IY;a.EJ=a.IZ;a.HP+=a.HN;if(a.HP&&typeof a.HM==="function"){a.HM()}};a.IO=function(c){a.BI=c||document;a.JQ=a.BI.defaultView||a.BI.parentWindow;a.JI=fb.getScroll(fb.JQ);var d=fb.CK(a.JQ);if(d){var b=fb.getLayout(d);a.dx=b.left;a.dy=b.top}else{a.dx=a.JI.left;a.dy=a.JI.top}if(a.BI.captureEvents){a.BI.captureEvents(Event.MOUSEMOVE)}fb.addEvent(a.BI,"mousemove",a.CE)};a.IU=function(){fb.removeEvent(a.BI,"mousemove",a.CE);if(a.BI&&a.BI.releaseEvents){a.BI.releaseEvents(Event.MOUSEMOVE)}a.EL=null};a.IP=function(){a.HP=-a.HN;a.EI=a.EJ=a.IK=-1;a.DL=setInterval(a.HL,a.HN);a.HL()};a.IV=function(){clearInterval(a.DL);a.HM=null}},JF:function(d,h){var m=this,l={doAnimations:false,color:"white",scrolling:"no",roundCorners:"all",cornerRadius:4,shadowType:"drop",shadowSize:4,showClose:false,titleAsCaption:false,enableKeyboardNav:false,padding:0,outerBorder:1,innerBorder:0,enableDragResize:false,enableDragMove:false,centerOnResize:false,attachToHost:false,moveWithMouse:false,placeAbove:false,timeout:0,delay:80,mouseSpeed:120,fadeDuration:3,defaultCursor:false,DV:true};function k(){var r=fb.JJ,i=r.IA,q=r.mo,j=m.getLayout(r.node);q.IV();n();r.IQ=q.IY-q.JI.left;r.IR=q.IZ-q.JI.top;if(i.attachToHost){q.IU();i.DE=j.height;i.boxTop=j.top-q.JI.top+(i.shadowType==="halo"?i.shadowSize:0)}else{r.DF=j.top;r.DD=j.top+j.height;i.DE=m.EU(i.shadowSize+1,m.JX.ie?19:21);i.boxTop=r.IR;if(i.placeAbove){i.boxTop-=5}}if(i.moveWithMouse){q.EL=function(){if(!fb.JJ){return}var u=fb.JJ,t=u.mo,v=fb.B;if(v){if(t.IZ<u.DF||t.IZ>u.DD){p()}else{if(v.fbBox&&typeof u.T==="number"){var s=v.fbBox.style;s.left=(u.T+t.IY-u.IQ)+"px";s.top=(u.U+t.IZ-u.IR)+"px"}}}}}m.start(null,i);if(i.timeout){if(r.JC){clearTimeout(r.JC)}r.JC=setTimeout(function(){r.JC=null;p()},i.timeout*1000)}}function f(){m.AC("ttEnd");if(fb.JJ){m.HZ("ttEnd",p,50)}}function n(){m.AC("ttEnd")}function p(){if(!fb.JJ){return}var i=fb.JJ;i.mo.IV();i.mo.IU();clearTimeout(i.JC);fb.JJ=i.T=i.U=i.node.GF=null;if(fb.B){if(m.JX.ie){m.end(fb.B.DR)}else{m.HV(fb.B.fbBox,0,i.IA.fadeDuration,function(){if(fb.B){m.end(fb.B.DR)}})}}}m.extend(l,m.CR.JL.tooltip,m.GH.JL.tooltip);var e=d.length;while(e--){var o=false,b=d[e],c=fb.JD.length;while(c--){if(fb.JD[c].node===b){o=true}}if(o){continue}var g=m.extend({},l),a={IA:g,node:b,DR:h};m.extend(g,m.GK(b.getAttribute("data-fb-tooltip")));if(!g.source){continue}fb.JD.push(a);if(g.attachToHost){g.moveWithMouse=false}if(g.roundCorners==="none"){g.cornerRadius=0}if(g.shadowType==="none"){g.shadowSize=0}if(g.shadowSize>16){g.shadowSize=16}g.resizeDuration=0;g.modal=g.disableScroll=g.sameBox=false;if(m.JX.KJ){g.attachToMouse=false}g.afterBoxStart=function(){var i=fb.B.fbBox;i.onmouseover=n;i.onmouseout=f;if(!m.JX.ie){m.HV(i,0)}};if(!m.JX.ie){g.afterItemStart=function(){m.HV(fb.B.fbBox,100,g.fadeDuration)}}if(g.defaultCursor){b.style.cursor="default"}b.removeAttribute("title");a.BI=b.ownerDocument;a.mo=a.BI.FJ||(a.BI.FJ=new m.FK);b.onmouseover=function(t){this.GF=true;var q=fb.JD.length;while(q--){if(this===fb.JD[q].node){break}}if(q===-1){return}n();var s=fb.JD[q],r=s.mo,j=s.IA;if(!fb.JJ||this!==fb.JJ.node){if(fb.B){p()}fb.JJ=s;clearInterval(r.DL);r.IO(s.BI);r.CE(t||r.JQ.event);r.HM=function(){if(!fb.B&&(!j.delay||(r.IK<j.mouseSpeed&&r.HP>j.delay))){k()}};r.IP()}};b.onmousemove=function(i){if(!this.GF&&this.onmouseover){this.onmouseover(i)}this.onmousemove=null};b.onmouseout=function(i){if(fb.JJ){f()}}}},tooltipLoaded:true});