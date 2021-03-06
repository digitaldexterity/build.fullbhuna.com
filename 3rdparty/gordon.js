/*
 *    Gordon: An open source Flash™ runtime written in pure JavaScript
 *
 *    Copyright (c) 2010 Tobias Schneider
 *    Gordon is freely distributable under the terms of the MIT license.
 */
(function(ba){function D(d){return d/A.PX_IN_TWIPS}function ta(){this.list=this.next=null}function ua(){this.n=this.b=this.e=0;this.t=null}function fa(d,h,k,c,b,s){this.BMAX=16;this.N_MAX=288;this.status=0;this.root=null;this.m=0;var a=new Array(this.BMAX+1),z,m,o,g,j,p,w,i=new Array(this.BMAX+1),e,l,n,q=new ua,r=new Array(this.BMAX);g=new Array(this.N_MAX);var t,x=new Array(this.BMAX+1),v,B,u;u=this.root=null;for(j=0;j<a.length;j++)a[j]=0;for(j=0;j<i.length;j++)i[j]=0;for(j=0;j<r.length;j++)r[j]=
null;for(j=0;j<g.length;j++)g[j]=0;for(j=0;j<x.length;j++)x[j]=0;z=h>256?d[256]:this.BMAX;e=d;l=0;j=h;do{a[e[l]]++;l++}while(--j>0);if(a[0]==h){this.root=null;this.status=this.m=0}else{for(p=1;p<=this.BMAX;p++)if(a[p]!=0)break;w=p;if(s<p)s=p;for(j=this.BMAX;j!=0;j--)if(a[j]!=0)break;o=j;if(s>j)s=j;for(v=1<<p;p<j;p++,v<<=1)if((v-=a[p])<0){this.status=2;this.m=s;return}if((v-=a[j])<0){this.status=2;this.m=s}else{a[j]+=v;x[1]=p=0;e=a;l=1;for(n=2;--j>0;)x[n++]=p+=e[l++];e=d;j=l=0;do if((p=e[l++])!=0)g[x[p]++]=
j;while(++j<h);h=x[o];x[0]=j=0;e=g;l=0;g=-1;t=i[0]=0;n=null;for(B=0;w<=o;w++)for(d=a[w];d-- >0;){for(;w>t+i[1+g];){t+=i[1+g];g++;B=(B=o-t)>s?s:B;if((m=1<<(p=w-t))>d+1){m-=d+1;for(n=w;++p<B;){if((m<<=1)<=a[++n])break;m-=a[n]}}if(t+p>z&&t<z)p=z-t;B=1<<p;i[1+g]=p;n=new Array(B);for(m=0;m<B;m++)n[m]=new ua;u=u==null?(this.root=new ta):(u.next=new ta);u.next=null;u.list=n;r[g]=n;if(g>0){x[g]=j;q.b=i[g];q.e=16+p;q.t=n;p=(j&(1<<t)-1)>>t-i[g];r[g-1][p].e=q.e;r[g-1][p].b=q.b;r[g-1][p].n=q.n;r[g-1][p].t=q.t}}q.b=
w-t;if(l>=h)q.e=99;else if(e[l]<k){q.e=e[l]<256?16:15;q.n=e[l++]}else{q.e=b[e[l]-k];q.n=c[e[l++]-k]}m=1<<w-t;for(p=j>>t;p<B;p+=m){n[p].e=q.e;n[p].b=q.b;n[p].n=q.n;n[p].t=q.t}for(p=1<<w-1;(j&p)!=0;p>>=1)j^=p;for(j^=p;(j&(1<<t)-1)!=x[g];){t-=i[g];g--}}this.m=i[1];this.status=v!=0&&o!=1?1:0}}}function Ca(){if(ka.length==pa)return-1;return ka.charCodeAt(pa++)&255}function G(d){for(;ca<d;){ga|=Ca()<<ca;ca+=8}}function I(d){return ga&Da[d]}function F(d){ga>>=d;ca-=d}function la(d,h,k){var c,b,s;if(k==0)return 0;
for(s=0;;){G(N);b=T.list[I(N)];for(c=b.e;c>16;){if(c==99)return-1;F(b.b);c-=16;G(c);b=b.t[I(c)];c=b.e}F(b.b);if(c==16){O&=X-1;d[h+s++]=U[O++]=b.n}else{if(c==15)break;G(c);J=b.n+I(c);F(c);G(aa);b=qa.list[I(aa)];for(c=b.e;c>16;){if(c==99)return-1;F(b.b);c-=16;G(c);b=b.t[I(c)];c=b.e}F(b.b);G(c);da=O-b.n-I(c);for(F(c);J>0&&s<k;){J--;da&=X-1;O&=X-1;d[h+s++]=U[O++]=U[da++]}}if(s==k)return k}V=-1;return s}function Ea(d,h,k){var c;c=ca&7;F(c);G(16);c=I(16);F(16);G(16);if(c!=(~ga&65535))return-1;F(16);J=c;
for(c=0;J>0&&c<k;){J--;O&=X-1;G(8);d[h+c++]=U[O++]=I(8);F(8)}if(J==0)V=-1;return c}function Fa(d,h,k){if(ma==null){var c,b=new Array(288);for(c=0;c<144;c++)b[c]=8;for(;c<256;c++)b[c]=9;for(;c<280;c++)b[c]=7;for(;c<288;c++)b[c]=8;na=7;c=new fa(b,288,257,va,wa,na);if(c.status!=0){alert("HufBuild error: "+c.status);return-1}ma=c.root;na=c.m;for(c=0;c<30;c++)b[c]=5;zip_fixed_bd=5;c=new fa(b,30,0,xa,ya,zip_fixed_bd);if(c.status>1){ma=null;alert("HufBuild error: "+c.status);return-1}za=c.root;zip_fixed_bd=
c.m}T=ma;qa=za;N=na;aa=zip_fixed_bd;return la(d,h,k)}function Ga(d,h,k){var c,b,s,a,z,m,o,g=new Array(316);for(c=0;c<g.length;c++)g[c]=0;G(5);m=257+I(5);F(5);G(5);o=1+I(5);F(5);G(4);c=4+I(4);F(4);if(m>286||o>30)return-1;for(b=0;b<c;b++){G(3);g[Aa[b]]=I(3);F(3)}for(;b<19;b++)g[Aa[b]]=0;N=7;b=new fa(g,19,19,null,null,N);if(b.status!=0)return-1;T=b.root;N=b.m;a=m+o;for(c=s=0;c<a;){G(N);z=T.list[I(N)];b=z.b;F(b);b=z.n;if(b<16)g[c++]=s=b;else if(b==16){G(2);b=3+I(2);F(2);if(c+b>a)return-1;for(;b-- >0;)g[c++]=
s}else{if(b==17){G(3);b=3+I(3);F(3)}else{G(7);b=11+I(7);F(7)}if(c+b>a)return-1;for(;b-- >0;)g[c++]=0;s=0}}N=Ha;b=new fa(g,m,257,va,wa,N);if(N==0)b.status=1;if(b.status!=0)return-1;T=b.root;N=b.m;for(c=0;c<o;c++)g[c]=g[c+m];aa=Ia;b=new fa(g,o,0,xa,ya,aa);qa=b.root;aa=b.m;if(aa==0&&m>257)return-1;if(b.status!=0)return-1;return la(d,h,k)}function Ja(){if(U==null)U=new Array(2*X);ca=ga=O=0;V=-1;ha=false;J=da=0;T=null}function Ka(d,h,k){var c,b;for(c=0;c<k;){if(ha&&V==-1)return c;if(J>0){if(V!=La)for(;J>
0&&c<k;){J--;da&=X-1;O&=X-1;d[h+c++]=U[O++]=U[da++]}else{for(;J>0&&c<k;){J--;O&=X-1;G(8);d[h+c++]=U[O++]=I(8);F(8)}if(J==0)V=-1}if(c==k)return c}if(V==-1){if(ha)break;G(1);if(I(1)!=0)ha=true;F(1);G(2);V=I(2);F(2);T=null;J=0}switch(V){case 0:b=Ea(d,h+c,k-c);break;case 1:b=T!=null?la(d,h+c,k-c):Fa(d,h+c,k-c);break;case 2:b=T!=null?la(d,h+c,k-c):Ga(d,h+c,k-c);break;default:b=-1;break}if(b==-1){if(ha)return 0;return-1}c+=b}return c}function Ba(d){var h,k,c;Ja();ka=d;pa=0;h=new Array(1024);for(d="";(k=
Ka(h,0,h.length))>0;)for(c=0;c<k;c++)d+=String.fromCharCode(h[c]);ka=null;return d}var A={qualityValues:{LOW:"low",AUTO_LOW:"autolow",AUTO_HIGH:"autohigh",MEDIUM:"medium",HIGH:"high",BEST:"best"},scaleValues:{SHOW_ALL:"showall",NO_ORDER:"noorder",EXACT_FIT:"exactfit"},validSignatures:{SWF:"FWS",COMPRESSED_SWF:"CWS"},readyStates:{LOADING:0,UNINITIALIZED:1,LOADED:2,INTERACTIVE:3,COMPLETE:4},tagCodes:{END:0,SHOW_FRAME:1,DEFINE_SHAPE:2,PLACE_OBJECT:4,REMOVE_OBJECT:5,DEFINE_BITS:6,DEFINE_BUTTON:7,JPEG_TABLES:8,
SET_BACKGROUND_COLOR:9,DEFINE_FONT:10,DEFINE_TEXT:11,DO_ACTION:12,DEFINE_FONT_INFO:13,DEFINE_SOUND:14,START_SOUND:15,DEFINE_BUTTON_SOUND:17,SOUND_STREAM_HEAD:18,SOUND_STREAM_BLOCK:19,DEFINE_BITS_LOSSLESS:20,DEFINE_BITS_JPEG2:21,DEFINE_SHAPE2:22,DEFINE_BUTTON_CXFORM:23,PROTECT:24,PLACE_OBJECT2:26,REMOVE_OBJECT2:28,DEFINE_SHAPE3:32,DEFINE_TEXT2:33,DEFINE_BUTTON2:34,DEFINE_BITS_JPEG3:35,DEFINE_BITS_LOSSLESS2:36,DEFINE_EDIT_TEXT:37,DEFINE_SPRITE:39,FRAME_LABEL:43,SOUND_STREAM_HEAD2:45,DEFINE_MORPH_SHAPE:46,
DEFINE_FONT2:48,EXPORT_ASSETS:56,IMPORT_ASSETS:57,ENABLE_DEBUGGER:58,DO_INIT_ACTION:59,DEFINE_VIDEO_STREAM:60,VIDEO_FRAME:61,DEFINE_FONT_INFO2:62,ENABLE_DEBUGGER2:64,SCRIPT_LIMITS:65,SET_TAB_INDEX:66,FILE_ATTRIBUTES:69,PLACE_OBJECT3:70,IMPORT_ASSETS2:71,DEFINE_FONT_ALIGN_ZONES:73,CSM_TEXT_SETTINGS:74,DEFINE_FONT3:75,SYMBOL_CLASS:76,METADATA:77,DEFINE_SCALING_GRID:78,DO_ABC:82,DEFINE_SHAPE4:83,DEFINE_MORPH_SHAPE2:84,DEFINE_SCENE_AND_FRAME_LABEL_DATA:86,DEFINE_BINARY_DATA:87,DEFINE_FONT_NAME:88,START_SOUND2:89,
DEFINE_BITS_JPEG4:90,DEFINE_FONT4:91},tagNames:{},tagHandlers:{},fillStyleTypes:{SOLID:0,LINEAR_GRADIENT:16,RADIAL_GRADIENT:18,FOCAL_RADIAL_GRADIENT:19,REPEATING_BITMAP:64,CLIPPED_BITMAP:65,NON_SMOOTHED_REPEATING_BITMAP:66,NON_SMOOTHED_CLIPPED_BITMAP:67},spreadModes:{PAD:0,REFLECT:1,REPEAT:2},interpolationModes:{RGB:0,LINEAR_RGB:1},styleChangeStates:{MOVE_TO:1,LEFT_FILL_STYLE:2,RIGHT_FILL_STYLE:4,LINE_STYLE:8,NEW_STYLES:16},buttonStates:{UP:1,OVER:2,DOWN:4,HIT:8},mouseButtons:{LEFT:1,RIGHT:2,MIDDLE:3},
textStyleFlags:{HAS_FONT:8,HAS_COLOR:4,HAS_XOFFSET:1,HAS_YOFFSET:2},actionCodes:{PLAY:6,STOP:7,NEXT_FRAME:4,PREVIOUS_FRAME:5,GOTO_FRAME:129,GOTO_LABEL:140,WAIT_FOR_FRAME:138,GET_URL:131,STOP_SOUNDS:9,TOGGLE_QUALITY:8,SET_TARGET:139},urlTargets:{SELF:"_self",BLANK:"_blank",PARENT:"_parent",TOP:"_top"},bitmapFormats:{COLORMAPPED:3,RGB15:4,RGB24:5},PX_IN_TWIPS:20};(function(){var d=A.tagCodes,h=A.tagNames,k=A.tagHandlers;for(var c in d){var b=d[c];h[b]=c;k[b]="_handle"+c.toLowerCase().replace(/(^|_)([a-z])/g,
function(s,a,z){return z.toUpperCase()})}})();var P=ba.document,Y=Array.prototype.push;(function(){var d=A.readyStates,h={id:null,width:0,height:0,autoplay:true,loop:true,quality:A.qualityValues.HIGH,scale:A.scaleValues.SHOW_ALL,bgcolor:null,renderer:null,onprogress:function(){},onreadystatechange:function(){},onenterframe:function(){}};A.Movie=function(k,c){var b=this;b.url=k;for(var s in h)b[s]=undefined!=c[s]?c[s]:h[s];if(!k)throw new Error("URL of a SWF movie file must be passed as first argument");
b._startTime=+new Date;b._readyState=d.UNINITIALIZED;b._changeReadyState(b._readyState);var a=new XMLHttpRequest;a.open("GET",k,false);a.overrideMimeType("text/plain; charset=x-user-defined");a.onreadystatechange=function(){a.readyState==2&&b._changeReadyState(d.LOADING)};a.send(null);if(200!=a.status)throw new Error("Unable to load "+k+" status: "+a.status);b._changeReadyState(d.LOADED);var z=b._dictionary={},m=b._timeline=[];b._framesLoaded=0;b._isPlaying=false;b._currFrame=-1;new A.Parser(a.responseText,
function(o){switch(o.type){case "header":for(var g in o)b["_"+g]=o[g];g=b._frameSize;o=g.right-g.left;g=g.bottom-g.top;if(!(b.width&&b.height)){b.width=o;b.height=g}var j=b.renderer=b.renderer||A.SvgRenderer;b._renderer=new j(b.width,b.height,o,g,b.quality,b.scale,b.bgcolor);break;case "frame":if((g=o.bgcolor)&&!b.bgcolor){b._renderer.setBgcolor(g);b.bgcolor=g}(g=o.action)&&eval("obj.action = function(){ ("+g+"(t)); }");m.push(o);g=++b._framesLoaded;b.onprogress(b.percentLoaded());if(g==1){if(b.id){o=
P.getElementById(b.id);o.innerHTML="";o.appendChild(b._renderer.getNode());b._changeReadyState(d.INTERACTIVE)}b.autoplay?b.play():b.gotoFrame(0)}else g==b._frameCount&&b._changeReadyState(d.COMPLETE);break;default:b._renderer.defineObject(o);z[o.id]=o}})};A.Movie.prototype={_changeReadyState:function(k){this._readyState=k;this.onreadystatechange(k);return this},play:function(){var k=this,c=k._currFrame,b=1100/k._frameRate;k._isPlaying=true;if(c<k._frameCount-1)if(k._framesLoaded>=c)k.gotoFrame(c+
1);else b=0;else if(k.loop)k.gotoFrame(0);else return k.stop();setTimeout(function(){k._isPlaying&&k.play()},b);return k},nextFrame:function(){var k=this,c=k._currFrame;k.gotoFrame(c<k._frameCount-1?c+1:0);return k},gotoFrame:function k(c){var b=this;k.caller!==b.play&&b.stop();if(b._currFrame!=c){if(c<b._currFrame)b._currFrame=-1;for(;b._currFrame!=c;){var s=b._timeline[++b._currFrame],a=s.displayList,z=b._renderer;for(var m in a){var o=a[m];o?z.placeCharacter(o):z.removeCharacter(m)}b.onenterframe(c);
(s=s.action)&&s()}}return b},stop:function(){this._isPlaying=false;return this},prevFrame:function(){var k=this,c=k._currFrame;k.gotoFrame(c>0?c-1:k._frameCount-1);return k},isPlaying:function(){return this._isPlaying},rewind:function(){this.gotoFrame(0);return this},totalFrames:function(){return this._frameCount},percentLoaded:function(){return Math.round(this._framesLoaded*100/this._frameCount)},currentFrame:function(){return this._currFrame},getURL:function(k,c){var b=A.urlTargets;switch(c){case b.BLANK:ba.open(k);
break;case b.PARENT:parent.location.href=k;break;case b.TOP:top.location.href=k;break;default:location.href=k}return this},toggleHighQuality:function k(){var c=k._orig,b=this,s=b.quality;if(c){s=b.quality=c;k._orig=null}else b.quality=k._orig=s;b._renderer.setQuality(s);return b}}})();(function(){var d=!!ba.JSON;if(P&&ba.Worker){for(var h=P.getElementsByTagName("script"),k=/(^|.*\/)gordon.(min\.)?js$/,c="gordon.min.js",b=h.length;b--;){var s=k.exec(h[b].src);if(s){c=s[0];break}}A.Parser=function(i,
e){var l=this;l.data=i;l.ondata=e;e=l._worker=new Worker(c);e.onerror=function(){};e.onmessage=function(n){l.ondata(d?JSON.parse(n.data):n.data)};e.postMessage(i)}}else{var a=currFrame=null,z={},m=0,o=null;A.Parser=function(i,e){if(e)this.ondata=e;a=new A.Stream(i);i=a.readString(3);e=A.validSignatures;if(i!=e.SWF&&i!=e.COMPRESSED_SWF)throw new Error(url+" is not a SWF movie file");var l=a.readUI8(),n=a.readUI32();i==e.COMPRESSED_SWF&&a.decompress();this.ondata({type:"header",version:l,fileLength:n,
frameSize:a.readRect(),frameRate:a.readUI16()/256,frameCount:a.readUI16()});i=A.tagHandlers;e=A.tagCodes.SHOW_FRAME;do{currFrame={type:"frame",displayList:{}};do{n=a.readUI16();l=n>>6;n=n&63;if(n>=63)n=a.readUI32();var q=i[l];this[q]?this[q](a.tell(),n):a.seek(n)}while(l&&l!=e)}while(l)};A.Parser.prototype={ondata:function(i){postMessage(d?JSON.stringify(i):i)},_handleShowFrame:function(){this.ondata(currFrame);return this},_handleDefineShape:function(){var i=a.readUI16(),e=a.readRect(),l=this,n=
l._readFillStyleArray(),q=l._readLineStyleArray(),r=a.readUB(4),t=a.readUB(4),x=[],v=true,B=[],u=rightFill=fsOffset=lsOffset=0,K={},Z={},M=line=0,ia={},S=A.styleChangeStates,Q=y1=x2=y2=0,W=countLineChanges=0,ea=true;do{var C=a.readUB(1),L=null;if(C){var Ma=a.readBool(),R=a.readUB(4)+2,ra=cy=null;Q=x2;y1=y2;if(Ma)if(a.readBool()){x2+=D(a.readSB(R));y2+=D(a.readSB(R))}else if(a.readBool())y2+=D(a.readSB(R));else x2+=D(a.readSB(R));else{ra=Q+D(a.readSB(R));cy=y1+D(a.readSB(R));x2=ra+D(a.readSB(R));y2=
cy+D(a.readSB(R))}x2=Math.round(x2*100)/100;y2=Math.round(y2*100)/100;x.push({i:M++,f:v,x1:Q,y1:y1,cx:ra,cy:cy,x2:x2,y2:y2});v=false}else{if(x.length){Y.apply(B,x);if(u){v=fsOffset+u;var E=K[v];E||(E=K[v]=[]);x.forEach(function(H){H=g(H);var $=H.x1,ja=H.y1;H.i=M++;H.x1=H.x2;H.y1=H.y2;H.x2=$;H.y2=ja;E.push(H)})}if(rightFill){v=fsOffset+rightFill;(E=Z[v])||(E=Z[v]=[]);Y.apply(E,x)}if(line){v=lsOffset+line;(E=ia[v])||(E=ia[v]=[]);Y.apply(E,x)}x=[];v=true}if(L=a.readUB(5)){if(L&S.MOVE_TO){R=a.readUB(5);
x2=D(a.readSB(R));y2=D(a.readSB(R))}if(L&S.LEFT_FILL_STYLE){u=a.readUB(r);W++}if(L&S.RIGHT_FILL_STYLE){rightFill=a.readUB(r);W++}if(L&S.LINE_STYLE){line=a.readUB(t);countLineChanges++}if(u&&rightFill||W+countLineChanges>2)ea=false;if(L&S.NEW_STYLES){Y.apply(n,l._readFillStyleArray());Y.apply(q,l._readLineStyleArray());r=a.readUB(4);t=a.readUB(4);fsOffset=n.length;lsOffset=q.length;ea=false}}}}while(C||L);a.align();r=null;var oa="s_"+i;if(ea){x=u||rightFill;r=j(B,x?n[fsOffset+x-1]:null,q[lsOffset+
line-1]);r.id=oa;r.bounds=e}else{B=[];for(M=n.length;M--;){x=M+1;E=K[x];fillEdges=[];E&&Y.apply(fillEdges,E);(E=Z[x])&&Y.apply(fillEdges,E);var sa={};fillEdges.forEach(function(H){var $=p(H.x1,H.y1),ja=sa[$];ja||(ja=sa[$]=[]);ja.push(H)});u=[];ea=fillEdges.length;for(t=0;t<ea&&!u[ea-1];t++){C=fillEdges[t];if(!C.c){x=[];S=p(C.x1,C.y1);W={};do{x.push(C);W[C.i]=true;C=p(C.x2,C.y2);if(C==S){for(C=x.length;C--;)x[C].c=true;Y.apply(u,x);break}E=sa[C];if(!(E&&E.length))break;v=fillEdges[t+1];Q=null;for(C=
0;E[C];C++){L=E[C];if(L==v&&!L.c){E.splice(C,1);Q=L}}if(!Q)for(C=0;E[C];C++){L=E[C];L.c||W[L.i]||(Q=L)}C=Q}while(C)}}if(u.length){r=j(u,n[M]);r.index=u.pop().i;B.push(r)}}n=[];for(M=q.length;M--;)if(u=ia[M+1]){r=j(u,null,q[M]);r.index=u.pop().i;n.push(r)}q=B.concat(n);q.sort(function(H,$){return H.index-$.index});if(q.length>1){q.forEach(function(H,$){H.id=oa+"_"+($+1)});r={type:"shape",id:oa,bounds:e,segments:q}}else{delete r.index;r.id=oa;r.bounds=e}}l.ondata(r);z[i]=r;return l},_readFillStyleArray:function(){var i=
a.readUI8();if(255==i)i=a.readUI16();var e=[];for(i=i;i--;){var l=a.readUI8(),n=A.fillStyleTypes;switch(l){case n.SOLID:e.push(a.readRGB());break;case n.LINEAR_GRADIENT:case n.RADIAL_GRADIENT:l={type:l==n.LINEAR_GRADIENT?"linear":"radial",matrix:a.readMatrix(),spread:a.readUB(2),interpolation:a.readUB(2),stops:[]};var q=a.readUB(4);n=l.stops;for(q=q;q--;)n.push({offset:a.readUI8()/255,color:a.readRGB()});e.push(l);break;case n.REPEATING_BITMAP:case n.CLIPPED_BITMAP:l=a.readUI16();l=z[l];n=a.readMatrix();
if(l){with(n){scaleX=D(scaleX);scaleY=D(scaleY);skewX=D(skewX);skewY=D(skewY)}e.push({type:"pattern",image:l,matrix:n})}else e.push(null);break}}return e},_readLineStyleArray:function(){var i=a.readUI8();if(255==i)i=a.readUI16();var e=[];for(i=i;i--;)e.push({width:D(a.readUI16()),color:a.readRGB()});return e},_handlePlaceObject:function(i,e){var l=a.readUI16(),n=a.readUI16();l={object:z[l].id,depth:n,matrix:a.readMatrix()};if(a.tell()-i!=e){i="x_"+ ++m;this.ondata({type:"filter",id:i,cxform:a.readCxform()});
l.filter=i}currFrame.displayList[n]=l;return this},_handleRemoveObject:function(){a.readUI16();var i=a.readUI16();currFrame.displayList[i]=null;return this},_handleDefineBits:function(i,e,l){i=a.readUI16();e=this._readJpeg(e-2);if(l)l=w(e.data);else{l=o.substr(0,o.length-2);l=w(l+e.data.substr(2))}l={type:"image",id:"i_"+i,uri:"data:image/jpeg;base64,"+l,width:e.width,height:e.height};this.ondata(l);z[i]=l;return this},_readJpeg:function(i){for(var e=a.tell(),l=r=0,n=0;n<i;n+=2){var q=a.readUI16(true);
a.readUI16(true);if(q==65472){a.seek(1);var r=a.readUI16(true);l=a.readUI16(true);break}}a.seek(e,true);return{data:a.readString(i),width:l,height:r}},_handleDefineButton:function(){var i=a.readUI16(),e={};do{var l=a.readUI8();if(l){var n=a.readUI16(),q=a.readUI16();n={object:z[n].id,depth:q,matrix:a.readMatrix()};for(var r=1;r<=8;){if(l&r){var t=e[r];t||(t=e[r]={});t[q]=n}r<<=1}}}while(l);e={type:"button",id:"b_"+i,states:e,action:this._readAction()};this.ondata(e);z[i]=e;return this},_readAction:function(){var i=
[];do{var e=a.readUI8(),l=e>128?a.readUI16():0,n=A.actionCodes;switch(e){case n.PLAY:i.push("t.play()");break;case n.STOP:i.push("t.stop()");break;case n.NEXT_FRAME:i.push("t.nextFrame()");break;case n.PREVIOUS_FRAME:i.push("t.prevFrame()");break;case n.GOTO_FRAME:l=a.readUI16();i.push("t.goto("+l+")");break;case n.GET_URL:l=a.readString();n=a.readString();i.push("t.getURL('"+l+"', '"+n+"')");break;case n.TOGGLE_QUALITY:i.push("t.toggleHighQuality()");break;default:a.seek(l)}}while(e);return"function(t){"+
i.join(";")+"}"},_handleJpegTables:function(i,e){o=a.readString(e);return this},_handleSetBackgroundColor:function(){currFrame.bgcolor=a.readRGB();return this},_handleDefineFont:function(){var i=a.readUI16(),e=a.readUI16()/2;a.seek(e*2-2);var l=A.styleChangeStates,n=[];for(e=e;e--;){var q=a.readUB(4);a.readUB(4);var r=y=0,t=[];do{var x=a.readUB(1),v=null;if(x){var B=a.readBool(),u=a.readUB(4)+2;if(B)if(a.readBool()){r+=a.readSB(u);y+=a.readSB(u);t.push("L",r,-y)}else if(a.readBool()){y+=a.readSB(u);
t.push("V",-y)}else{r+=a.readSB(u);t.push("H",r)}else{B=r+a.readSB(u);var K=y+a.readSB(u);r=B+a.readSB(u);y=K+a.readSB(u);t.push("Q",B,-K,r,-y)}}else if(v=a.readUB(5)){if(v&l.MOVE_TO){u=a.readUB(5);r=a.readSB(u);y=a.readSB(u);t.push("M",r,-y)}if(v&l.LEFT_FILL_STYLE||v&l.RIGHT_FILL_STYLE)a.readUB(q)}}while(x||v);a.align();n.push({commands:t.join(" ")})}l={type:"font",id:"f_"+i,glyphs:n};this.ondata(l);z[i]=l;return this},_handleDefineText:function(){var i=a.readUI16(),e={type:"text",id:"t_"+i,bounds:a.readRect(),
matrix:a.readMatrix(),strings:[]},l=a.readUI8(),n=a.readUI8(),q=fill=null,r=y=size=0,t=null,x=e.strings;do{var v=a.readUB(8);if(v)if(v>>7){if(t=v&15){var B=A.textStyleFlags;if(t&B.HAS_FONT)q=a.readUI16();if(t&B.HAS_COLOR)fill=a.readRGB();if(t&B.HAS_XOFFSET)r=D(a.readSI16());if(t&B.HAS_YOFFSET)y=D(a.readSI16());if(t&B.HAS_FONT)size=D(a.readUI16())}t={font:z[q].id,fill:fill,x:r,y:y,size:size};x.push(t)}else{var u=v&127;B=t.entries=[];for(u=u;u--;){var K={};K.index=a.readUB(l);K.advance=D(a.readSB(n));
B.push(K)}a.align()}}while(v);this.ondata(e);z[i]=e;return this},_handleDoAction:function(){currFrame.action=this._readAction();return this},_handleDefineFontInfo:function(){var i=a.readUI16(),e=z[i],l=e.info={name:a.readString(a.readUI8()),isSmall:a.readBool(3),isShiftJis:a.readBool(),isAnsi:a.readBool(),isItalic:a.readBool(),isBold:a.readBool(),codes:[]},n=a.readBool();l=l.codes;for(var q=e.glyphs.length;q--;){var r=n?a.readUI16():a.readUI8();l.push(r)}this.ondata(e);z[i]=e;return this},_handleDefineBitsJpeg2:function(i,
e){return this._handleDefineBits(i,e,true)},_handleDefineBitsLossless:function(i,e){var l=a.readUI16(),n=a.readUI8(),q=a.readUI16(),r=a.readUI16(),t=A.bitmapFormats;if(n==t.COLORMAPPED)var x=a.readUI8();a.seek(2);i=Ba(a.readString(e-(a.tell()-i)));switch(n){case t.COLORMAPPED:var v=[];for(n=0;n<=x;n++)v.push(i.substr(n*3,3));v=[];for(n=0;n<q*r;n++)v.push(i[n]);case t.RGB15:case t.RGB24:v=[];for(n=0;i[n];n++)v.push(i[++n],i[++n],i[++n]);break}q={type:"image",id:"i_"+l,data:v.join(""),width:q,height:r};
this.ondata(q);z[l]=q;return this},_handleDefineShape2:function(){return this._handleDefineShape.apply(this,arguments)},_handleDefineButtonCxform:function(){var i=a.readUI16(),e="x_"+ ++m;this.ondata({id:e,type:"filter",cxform:a.readCxform()});var l=z[i];l.filter=e;this.ondata(l);z[i]=l;return this},_handleProtect:function(i,e){a.seek(e);return this}};function g(i){with(i)return{i:b,f:f,x1:x1,y1:y1,cx:cx,cy:cy,x2:x2,y2:y2}}function j(i,e,l){var n=y1=x2=y2=0,q=[];i.forEach(function(r,t){n=r.x1;y1=
r.y1;if(n!=x2||y1!=y2||!t)q.push("M",n,y1);x2=r.x2;y2=r.y2;if(null==r.cx||null==r.cy)if(x2==n)q.push("V",y2);else y2==y1?q.push("H",x2):q.push("L",x2,y2);else q.push("Q",r.cx,r.cy,x2,y2)});return{type:"shape",commands:q.join(" "),fill:e,stroke:l}}function p(i,e){return(i+5E4)*1E5+e}function w(i){var e=0,l=prevBits=null;chars=[];for(var n=0;i[n];n++){e=n%3;l=i.charCodeAt(n)&255;switch(e){case 0:chars.push("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/"[l>>2]);break;case 1:chars.push("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/"[(prevBits&
3)<<4|l>>4]);break;case 2:chars.push("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/"[(prevBits&15)<<2|l>>6],"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/"[l&63]);break}prevBits=l}if(e)e==1&&chars.push("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/"[(prevBits&15)<<2],"=");else chars.push("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/"[(prevBits&3)<<4],"==");return chars.join("")}ba.onmessage=function(i){new A.Parser(i.data)}}})();
A.Stream=function(d){var h=this;h._buffer=d;h._length=h._buffer.length;h._offset=0;h._bitBuffer=null;h._bitOffset=8};A.Stream.prototype={decompress:function(){var d=this;d._offset+=2;var h=d._buffer.substr(0,d._offset),k=Ba(d._buffer.substr(d._offset));d._buffer=h+k;d._length=d._buffer.length;return d},readByteAt:function(d){return this._buffer.charCodeAt(d)&255},readNumber:function(d,h){var k=this,c=0;if(h)for(h=d;h--;)c=(c<<8)+k.readByteAt(k._offset++);else{var b=k._offset;for(h=b+d;h>b;)c=(c<<
8)+k.readByteAt(--h);k._offset+=d}k.align();return c},readSNumber:function(d,h){h=this.readNumber(d,h);d=d*8;if(h>>d-1)h-=Math.pow(2,d);return h},readSI8:function(){return this.readSNumber(1)},readSI16:function(d){return this.readSNumber(2,d)},readSI32:function(d){return this.readSNumber(4,d)},readUI8:function(){return this.readByteAt(this._offset++)},readUI16:function(d){return this.readNumber(2,d)},readUI24:function(d){return this.readNumber(3,d)},readUI32:function(d){return this.readNumber(4,d)},
readFixed:function(){return this._readFixedPoint(32,16)},_readFixedPoint:function(d,h){return this.readSB(d)*Math.pow(2,-h)},readFixed8:function(){return this._readFixedPoint(16,8)},readFloat:function(){return this._readFloatingPoint(8,23)},_readFloatingPoint:function(d,h){var k=1+d+h,c=k/8,b=this;if(c>4){for(var s=0,a=Math.ceil(c/4);a--;){for(var z=b._offset,m=z+c>=4?4:c%4;m>z;)s=(s<<8)+String.fromCharCode(b.readByteAt(--m));b._offset+=c;c-=c}c=1<<k-1;k=s&c;s=0;for(a=d;a--;){c>>=1;s|=buffer&c?1:
0;s<<=1}b=0;for(a=h;a--;){c>>=1;if(buffer&c)b+=Math.pow(2,a-1)}}else{k=b.readUB(1);s=b.readUB(d);b=b.readUB(h)}d=Math.pow(2,d);a=~~((d-1)/2);h=Math.pow(2,h);h=b/h;s=a?a<d?Math.pow(2,s-a)*(1+fact):h?NaN:Infinity:h?Math.pow(2,1-a)*h:0;if(s!=NaN&&k)s*=-1;return s},readFloat16:function(){return this._readFloatingPoint(5,10)},readDouble:function(){return this._readFloatingPoint(11,52)},readEncodedU32:function(){for(var d=0,h=5;h--;){var k=this.readByteAt(this._offset++);d=(d<<7)+(k&127);if(!(k&128))break}return d},
readSB:function(d){var h=this.readUB(d);if(h>>d-1)h-=Math.pow(2,d);return h},readUB:function(d){var h=this,k=0;for(d=d;d--;){if(8==h._bitOffset){h._bitBuffer=h.readUI8();h._bitOffset=0}k=(k<<1)+(h._bitBuffer&128>>h._bitOffset?1:0);h._bitOffset++}return k},readFB:function(d){return this._readFixedPoint(d,16)},readString:function(d){var h=this,k=h._buffer;if(d){k=k.substr(h._offset,d);h._offset+=d}else{d=h._length-h._offset;k=[];for(d=d;d--;){var c=h.readByteAt(h._offset++);if(c)k.push(String.fromCharCode(c));
else break}k=k.join("")}return k},readBool:function(d){return!!this.readUB(d||1)},readLanguageCode:function(){return this.readUI8()},readRGB:function(){return{red:this.readUI8(),green:this.readUI8(),blue:this.readUI8()}},readRGBA:function(){var d=this.readRGB();d.alpha=this.readUI8()/256;return d},readARGB:function(){var d=this.readUI8()/256,h=this.readRGB();h.alpha=d;return h},readRect:function(){var d=this;numBits=d.readUB(5);rect={left:D(d.readSB(numBits)),right:D(d.readSB(numBits)),top:D(d.readSB(numBits)),
bottom:D(d.readSB(numBits))};d.align();return rect},readMatrix:function(){var d=this;if(d.readBool())var h=d.readUB(5),k=d.readFB(h),c=d.readFB(h);else k=c=1;if(d.readBool()){h=d.readUB(5);var b=d.readFB(h),s=d.readFB(h)}else b=s=0;h=d.readUB(5);matrix={scaleX:k,scaleY:c,skewX:b,skewY:s,moveX:D(d.readSB(h)),moveY:D(d.readSB(h))};d.align();return matrix},readCxform:function(){return this._readCxform()},readCxformWithAlpha:function(){return this._readCxform(true)},_readCxform:function(d){var h=this;
hasAddTerms=h.readBool();hasMultTerms=h.readBool();numBits=h.readUB(4);if(hasMultTerms)var k=h.readSB(numBits)/256,c=h.readSB(numBits)/256,b=h.readSB(numBits)/256,s=d?h.readSB(numBits)/256:1;else k=c=b=s=1;if(hasAddTerms){var a=h.readSB(numBits),z=h.readSB(numBits),m=h.readSB(numBits);d=d?h.readSB(numBits):0}else a=z=m=d=0;k={multR:k,multG:c,multB:b,multA:s,addR:a,addG:z,addB:m,addA:d};h.align();return k},tell:function(){return this._offset},seek:function(d,h){this._offset=(h?0:this._offset)+d;return this},
reset:function(){this._offset=0;this.align();return this},align:function(){this._bitBuffer=null;this._bitOffset=8;return this}};(function(){function d(m){if("string"==typeof m)return/^#([0-9a-z]{1,2}){3}$/i.test(m)?m:null;with(m)return"rgb("+[red,green,blue]+")"}function h(m){with(m)return"matrix("+[scaleX,skewX,skewY,scaleY,moveX,moveY]+")"}function k(m){with(m)return[multR,0,0,0,addR,0,multG,0,0,addG,0,0,multB,0,addB,0,0,0,multA,addA].toString()}function c(m){with(m)return{object:object,depth:depth,
matrix:matrix,cxform:m.cxform}}var b=A.buttonStates,s={};for(var a in b)s[b[a]]=a.toLowerCase();var z=0;A.SvgRenderer=function(m,o,g,j,p,w,i){var e=this;e.width=m;e.height=o;e.viewWidth=g;e.viewHeight=j;e.quality=p||A.qualityValues.HIGH;e.scale=w||A.scaleValues.SHOW_ALL;e.bgcolor=i;p=e._node=e._createElement("svg");attr={width:m,height:o};if(g&&j&&(m!=g||o!=j)){attr.viewBox=[0,0,g,j].toString();if(w==A.scaleValues.EXACT_FIT)attr.preserveAspectRatio="none"}e._setAttributes(p,attr);e._defs=p.appendChild(e._createElement("defs"));
e._stage=p.appendChild(e._createElement("g"));e.setQuality(e.quality);i&&e.setBgcolor(i);e._dictionary={};e._displayList={};e._eventTarget=null};A.SvgRenderer.prototype={_createElement:function(m){return P.createElementNS("http://www.w3.org/2000/svg",m)},_setAttributes:function(m,o,g){for(var j in o){var p=o[j];j=j=="className"?"class":j.replace(/_/g,"-");g?m.setAttributeNS(g,j,p):m.setAttribute(j,p)}return m},setQuality:function(m){var o=A.qualityValues,g=this;switch(m){case o.LOW:var j={shape_rendering:"crispEdges",
image_rendering:"optimizeSpeed",text_rendering:"optimizeSpeed",color_rendering:"optimizeSpeed"};break;case o.AUTO_LOW:case o.AUTO_HIGH:j={shape_rendering:"auto",image_rendering:"auto",text_rendering:"auto",color_rendering:"auto"};break;case o.MEDIUM:j={shape_rendering:"optimizeSpeed",image_rendering:"optimizeSpeed",text_rendering:"optimizeLegibility",color_rendering:"optimizeSpeed"};break;case o.HIGH:j={shape_rendering:"geometricPrecision",image_rendering:"auto",text_rendering:"geometricPrecision",
color_rendering:"optimizeQuality"};break;case o.BEST:j={shape_rendering:"geometricPrecision",image_rendering:"optimizeQuality",text_rendering:"geometricPrecision",color_rendering:"optimizeQuality"};break}g._setAttributes(g._stage,j);g.quality=m;return g},getNode:function(){return this._node},setBgcolor:function(m){var o=this;if(!o.bgcolor){o._node.style.background=d(m);o.bgcolor=m}return o},defineObject:function(m){var o=m.type,g=this,j=null,p=m.id,w={id:p},i=g._dictionary;item=i[p];if(!item||!item.node){switch(o){case "shape":if(o=
m.segments){j=g._createElement("g");o.forEach(function(u){j.appendChild(g._buildShape(u))})}else j=g._buildShape(m);break;case "image":j=g._createElement("image");var e=m.width,l=m.height;if(m.data){o=new A.Stream(m.data);var n=e*l*4,q=P.createElement("canvas");q.width=e;q.height=l;for(var r=q.getContext("2d"),t=r.createImageData(e,l),x=t.data,v=0;v<n;v+=4){x[v]=o.readUI8();x[v+1]=o.readUI8();x[v+2]=o.readUI8();x[v+3]=255}r.putImageData(t,0,0);o=q.toDataURL()}else o=m.uri;g._setAttributes(j,{href:o},
"http://www.w3.org/1999/xlink");w.width=e;w.height=l;break;case "button":j=g._createElement("g");n=g._createElement("g");o=m.states;for(e in o){q=e==b.HIT?n:j.appendChild(g._createElement("g"));g._setAttributes(q,{className:s[e],opacity:e==b.UP?1:0});r=m.filter;t=states[e];for(l in t){if(r){x=c(t[l]);x.filter=r}else x=t[l];q.appendChild(g._buildCharacter(x))}}j.appendChild(n);break;case "font":if(o=m.info){j=g._createElement("font");e=j.appendChild(g._createElement("font-face"));g._setAttributes(e,
{font_family:o.name});var B=o.codes;m.glyphs.forEach(function(u,K){var Z=j.appendChild(g._createElement("glyph"));g._setAttributes(Z,{unicode:String.fromCharCode(B[K]),d:u.commands})})}break;case "text":j=g._createElement("g");o=m.strings;o.forEach(function(u){var K=j.appendChild(g._createElement("text")),Z=[],M=g._dictionary[u.font].object.info,ia=M.codes,S=[],Q=u.x;u.entries.forEach(function(W){Z.push(Q);S.push(String.fromCharCode(ia[W.index]));Q+=W.advance});g._setAttributes(K,{font_family:M.name,
font_size:u.size,fill:d(u.fill),x:Z.join(" "),y:u.y});K.appendChild(P.createTextNode(S.join("")))});w.transform=h(m.matrix);break;case "filter":j=g._createElement("filter");if(o=m.cxform){e=j.appendChild(g._createElement("feColorMatrix"));g._setAttributes(e,{type:"matrix",values:k(o)})}break}if(j){g._setAttributes(j,w);g._defs.appendChild(j)}i[p]={object:m,node:j}}else i[p].object=m;return g},_buildShape:function(m){var o=this,g=o._createElement("path"),j=m.fill,p=m.stroke,w={d:m.commands};if(j){var i=
j.type;if(j.type){j=o._defs.appendChild(o._buildFill(j));m=i[0]+m.id.substr(1);o._setAttributes(j,{id:m});w.fill="url(#"+m+")"}else w.fill=d(j);w.fill_rule="evenodd"}else w.fill="none";if(p){w.stroke=d(p.color);w.stroke_width=Math.max(p.width,1);w.stroke_linecap=w.stroke_linejoin="round"}o._setAttributes(g,w);return g},_buildFill:function(m){var o=m.type,g=this,j={};switch(o){case "linear":case "radial":var p=g._createElement(o+"Gradient");j.gradientUnits="userSpaceOnUse";j.gradientTransform=h(m.matrix);
if("linear"==o){j.x1=-819.2;j.x2=819.2}else{j.cx=j.cy=0;j.r=819.2}o=A.spreadModes;switch(m.spread){case o.REFLECT:j.spreadMethod="reflect";break;case o.REPEAT:j.spreadMethod="repeat";break}if(m.interpolation==A.interpolationModes.LINEAR_RGB)j.color_interpolation="linearRGB";m.stops.forEach(function(i){var e=p.appendChild(g._createElement("stop"));g._setAttributes(e,{offset:i.offset,stop_color:d(i.color)})});break;case "pattern":p=g._createElement("pattern");o=p.appendChild(g._createElement("use"));
var w=m.image;g._setAttributes(o,{href:"#"+w.id},"http://www.w3.org/1999/xlink");j.patternUnits="userSpaceOnUse";j.patternTransform=h(m.matrix);j.width=w.width;j.height=w.height;break}g._setAttributes(p,j);return p},placeCharacter:function(m){var o=m.depth,g=this,j=g._displayList,p=j[o];if(!p||p.character!==m){var w=g._buildCharacter(m),i=g._stage;p&&p.character!==m&&g.removeCharacter(o);if(1==o)i.insertBefore(w,i.firstChild);else{p=0;for(var e in j)if(e>o){p=e;break}p?i.insertBefore(w,j[p].node):
i.appendChild(w)}j[o]={character:m,node:w}}return g},_buildCharacter:function(m){var o=this,g=o._dictionary[m.object],j=g.object;switch(j.type){case "button":g=g.node.cloneNode(true);var p={};for(var w in s)p[w]=g.getElementsByClassName(s[w])[0];var i=A.mouseButtons,e=false,l=function(){if(!(z&i.LEFT)){if(e){q(b.OVER);j.action()}else q(b.UP);P.removeEventListener("mouseup",l,false);o.eventTarget=null}return false};with(p[b.HIT]){onmouseover=function(r){e=true;o.eventTarget||(z&i.LEFT?this.onmousedown(r):
q(b.OVER));return false};onmouseout=function(){e=false;o.eventTarget||q(this==o.eventTarget?b.OVER:b.UP);return false};onmousedown=function(){if(z&i.LEFT){q(b.DOWN);P.addEventListener("mouseup",l,false);o.eventTarget=this}return false};onmouseup=function(){q(b.OVER);return false}}var n=b.UP,q=function(r){o._setAttributes(p[n],{opacity:0});o._setAttributes(p[r],{opacity:1});n=r};break;default:g=o._createElement("use");o._setAttributes(g,{href:"#"+j.id},"http://www.w3.org/1999/xlink")}(w=m.filter)&&
o._setAttributes(g,{filter:"url(#"+w+")"});o._setAttributes(g,{transform:h(m.matrix)});return g},removeCharacter:function(m){var o=this._displayList,g=o[m].node;g.parentNode.removeChild(g);delete o[m];return this}};if(P){P.addEventListener("mousedown",function(m){z|=m.button},true);P.addEventListener("mouseup",function(m){z^=m.button},true)}})();var X=32768,La=0,Ha=9,Ia=6,U,O,ma=null,za,na,ga,ca,V,ha,J,da,T,qa,N,aa,ka,pa,Da=new Array(0,1,3,7,15,31,63,127,255,511,1023,2047,4095,8191,16383,32767,65535),
va=new Array(3,4,5,6,7,8,9,10,11,13,15,17,19,23,27,31,35,43,51,59,67,83,99,115,131,163,195,227,258,0,0),wa=new Array(0,0,0,0,0,0,0,0,1,1,1,1,2,2,2,2,3,3,3,3,4,4,4,4,5,5,5,5,0,99,99),xa=new Array(1,2,3,4,5,7,9,13,17,25,33,49,65,97,129,193,257,385,513,769,1025,1537,2049,3073,4097,6145,8193,12289,16385,24577),ya=new Array(0,0,0,0,1,1,2,2,3,3,4,4,5,5,6,6,7,7,8,8,9,9,10,10,11,11,12,12,13,13),Aa=new Array(16,17,18,0,8,7,9,6,10,5,11,4,12,3,13,2,14,1,15);ba.Gordon=A})(self);
