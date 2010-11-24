/********************************************************************

Popup Windows - V 4.3
Author: Brian Gosselin
Site URL: http://scriptasylum.com
Read the "releasenotes.txt" for supported features and release notes.

************** EDIT THE LINES BELOW AT YOUR OWN RISK ****************/

var w3c=(document.getElementById)? true: false;
var ns4=(document.layers)?true:false;
var ie5=(w3c && document.all)? true : false;
var ns6=(w3c && !document.all)? true: false;
var d=document;
currIDb=null; xoff=0; yoff=0;
currRS=null; rsxoff=0; rsyoff=0;
oldac=null; newac=null; zdx=1; mx=0; my=0;

//******* START OF EXPOSED FUNCTIONS. THESE CAN BE USED IN HYPERLINKS. *******\\

function hidebox(id){
if(w3c){
  d.getElementById(id+'_c').style.display='none';
  d.getElementById(id+'_b').style.display='none';
  d.getElementById(id+'_s').style.display='none';
}}

function showbox(id){
if(w3c){
var bx=d.getElementById(id+'_b');
var sh=d.getElementById(id+'_s');
bx.style.display='';
 if (sh.style.display!='none') sh.style.display='';
sh.style.zIndex=++zdx;
bx.style.zIndex=++zdx;
changez(bx);
d.getElementById(id+'_c').style.display='';
}}

function changecontent(id,text){
if(!document.getElementById(id+'_b').isExt){
var d=document.getElementById(id+'_c');
if(ns6)d.style.overflow="hidden";
d.innerHTML=text;
if(ns6)d.style.overflow="block";
}else document.getElementById(id+'_ifrm').src=text;
}

function movePopup(ids,x,y){
if(w3c){
	
	//if (isNaN(x) || isNaN(y)) return;
	if (isNaN(x)) console.log('x',x);
	//if (isNaN(y)) y=0;
var idb=document.getElementById(ids+'_b');
var ids=document.getElementById(ids+'_s');
idb.style.left=x+'px';
ids.style.left=x+8+'px';
idb.style.top=y+'px';
ids.style.top=y+8+'px';
}}

function resizePopup(ids,rx,ry){
if(w3c){
if(d.getElementById(ids+'_rs').rsEnable){
d.gEl=d.getElementById;
d.gEl(ids+"_extWA").style.display="block";
d.gEl(ids+"_rs").style.left=Math.max(rx,((ie5)?88:92))+'px';
d.gEl(ids+"_rs").style.top=Math.max(ry,((ie5)?68:72))+'px';
d.gEl(ids+"_b").style.width=Math.max(rx+((ie5)?12:8),100)+'px';
d.gEl(ids+"_b").style.height=Math.max(ry+((ie5)?12:8),80)+'px';
d.gEl(ids+"_t").style.width=Math.max(rx+((ie5)?4:3),((ns6)?95:92))+'px';
d.gEl(ids+"_btt").style.left=parseInt(d.gEl(ids+"_t").style.width)-48+'px';
d.gEl(ids+"_s").style.width=Math.max(rx+12,((ie5)?100:104))+'px';
d.gEl(ids+"_s").style.height=Math.max(ry+((ie5)?12:13),((ie5)?80:86))+'px';
d.gEl(ids+"_c").style.width=Math.max(rx-((ie5)?-5:5),((ie5)?92:87))+'px';
d.gEl(ids+"_c").style.height=Math.max(ry-((ie5)?24:28),44)+'px';
d.gEl(ids+"_min").h=parseInt(d.gEl(ids+"_b").style.height);
}}}

//******* END OF EXPOSED FUNCTIONS *******\\

function preloadBttns(){
var btns=new Array();
btns[0]=new Image(); btns[0].src="Images/byellow.png";
btns[1]=new Image(); btns[1].src="Images/bgreen.png";
btns[2]=new Image(); btns[2].src="Images/bred.png";
btns[3]=new Image(); btns[3].src="Images/resize.gif";
}
preloadBttns();

  
function savegeo(){
  var g;
  var w,h,xy;

  xy=getAnchorPosition(this.cid+"_b");
  w=parseInt(d.getElementById(this.cid+"_b").style.width);
  h=parseInt(d.getElementById(this.cid+"_b").style.height);

  g=(xy.x)+"+"+(xy.y)+"+"+w+"x"+h;
  
  d.getElementById('geometry').value=g;
  d.getElementById('savegeo').submit();  
}

function minimize(){
  if(w3c){
    if (d.getElementById(this.cid+"_c").style.display=='none') {
    
      d.getElementById(this.cid+"_b").style.height=this.h+'px';
      d.getElementById(this.cid+"_s").style.height=(ie5)? this.h+'px':this.h+5+'px';
      d.getElementById(this.cid+"_c").style.display='block';
      d.getElementById(this.cid+"_rs").style.display='block';
    } else {
      d.getElementById(this.cid+"_b").style.height=(ie5)? '28px':'24px';
      d.getElementById(this.cid+"_s").style.height='28px';
      d.getElementById(this.cid+"_c").style.display='none';
      d.getElementById(this.cid+"_rs").style.display='none';

    }
    ns6bugfix();
  }
}

function restore(){
if(w3c){
d.getElementById(this.cid+"_b").style.height=this.h+'px';
d.getElementById(this.cid+"_s").style.height=(ie5)? this.h+'px':this.h+5+'px';
d.getElementById(this.cid+"_c").style.display='block';
d.getElementById(this.cid+"_rs").style.display='block';
ns6bugfix();
}}

function ns6bugfix(){
  return;
  if(ns6)setTimeout('self.resizeBy(0,1); self.resizeBy(0,-1);', 100);
}

function trackmouse(evt){
	GetXY(evt);
mx=Xpos;
my=Ypos;
if(!ns6)movepopup();
if((currIDb!=null)||(currRS!=null))return false;
}

function movepopup(){
if((currIDb!=null)&&w3c)movePopup(currIDb.cid,mx+xoff,my+yoff);
if((currRS!=null)&&w3c)resizePopup(currRS.cid,mx+rsxoff,my+rsyoff);
return false;
}

function stopRS(){
d.getElementById(this.cid+"_extWA").style.display="none";
currRS=null;
}

function startRS(evt){
	GetXY(evt);
var ex=Xpos;
var ey=Ypos;
rsxoff=parseInt(this.style.left)-ex;
rsyoff=parseInt(this.style.top)-ey;
currRS=this;
//if(ns6)d.getElementById(this.cid+"_c").style.overflow='hidden';

return false;
}

function changez(v){
var th=(v!=null)?v:this;
if(oldac!=null)d.getElementById(oldac.cid+"_t").style.backgroundColor=oldac.inactivecolor;
//if(ns6)d.getElementById(th.cid+"_c").style.overflow='auto';
oldac=th;
d.getElementById(th.cid+"_t").style.backgroundColor=th.activecolor;
d.getElementById(th.cid+"_s").style.zIndex=++zdx;
th.style.zIndex=++zdx;
d.getElementById(th.cid+"_rs").style.zIndex=++zdx;
}

function stopdrag(){
currIDb=null;
document.getElementById(this.cid+"_extWA").style.display="none";
ns6bugfix();
}

function grab_id(evt){
	GetXY(evt);
	var ex=Xpos;
var ey=Ypos;
xoff=parseInt(d.getElementById(this.cid+"_b").style.left)-ex;
yoff=parseInt(d.getElementById(this.cid+"_b").style.top)-ey;
currIDb=d.getElementById(this.cid+"_b");
currIDs=d.getElementById(this.cid+"_s");
d.getElementById(this.cid+"_extWA").style.display="block";
return false;
}

function subBox(x,y,w,h,bgc,id){
var v=d.createElement('div');
v.setAttribute('id',id);
v.style.position='absolute';
v.style.left=x+'px';
v.style.top=y+'px';
v.style.width=w+'px';
v.style.height=h+'px';
if(bgc!='')v.style.backgroundColor=bgc;
v.style.visibility='visible';
v.style.padding='0px';
return v;
}

function get_cookie(Name) {
var search=Name+"=";
var returnvalue="";
if(d.cookie.length>0){
offset=d.cookie.indexOf(search);
if(offset!=-1){
offset+=search.length;
end=d.cookie.indexOf(";",offset);
if(end==-1)end=d.cookie.length;
returnvalue=unescape(d.cookie.substring(offset,end));
}}
return returnvalue;
}

function popUp(x,y,w,h,cid,text,bgcolor,textcolor,fontstyleset,title,titlecolor,titletextcolor,bordercolor,scrollcolor,shadowcolor,showonstart,isdrag,isresize,oldOK,isExt,popOnce,noDecoration){
  var okPopUp=false;
  if (popOnce){
    if (get_cookie(cid)==""){
      okPopUp=true;
      d.cookie=cid+"=yes";
    }}
  else okPopUp=true;
  if(okPopUp){
    if(w3c){
      w=Math.max(w,100);
      h=Math.max(h,80);
      var rdiv=new subBox(w-((ie5)?12:8),h-((ie5)?12:8),7,7,'',cid+'_rs');
      if(isresize){
	rdiv.innerHTML='<img style="position:absolute;cursor:se-resize" src="Images/resize.gif" width="7" height="7">';
	rdiv.style.cursor='se-resize';
      }
      rdiv.rsEnable=isresize;
      var tw=(ie5)?w:w+4;
      y+=document.body.scrollTop;
      var th=(ie5)?h:h+6;
      var shadow=new subBox(x+8,y+8,tw,th,shadowcolor,cid+'_s');
      if(ie5)shadow.style.filter="alpha(opacity=50)";
      else shadow.style.MozOpacity=.5;
      if (noDecoration) shadow.style.display='none';
      shadow.style.zIndex=++zdx;
      var outerdiv=new subBox(x,y,w,h,bordercolor,cid+'_b');
      outerdiv.style.borderStyle="outset";
      outerdiv.style.borderWidth="2px";
      outerdiv.style.borderColor=bordercolor;
      outerdiv.style.zIndex=++zdx;
      tw=(ie5)?w-8:w-5;
      th=(ie5)?h+4:h-4;
      var ht=15; // height of title bar
      var titlebar=new subBox(2,2,tw,ht,titlecolor,cid+'_t');
      titlebar.style.overflow="hidden";
      titlebar.style.cursor="move";
      titlebar.style.backgroundImage="url('Images/tabvig.png')";
      var bsavegeo=(noDecoration)?'<div id="'+cid+'_max" class="btn_max" style="display:none;background-image:url(Images/byellow.png);height:10px;width:10px;float:right;"></div>':'<div id="'+cid+'_max" class="btn_max" style="cursor:pointer;background-image:url(Images/byellow.png);height:10px;width:10px;float:right;"></div>'; // <img style="cursor:default" src="Images/byellow.png" id="'+cid+'_max">
      var tmp=(isresize)?bsavegeo+'<div id="'+cid+'_min" class="btn_min" style="cursor:pointer;background-image:url(Images/bgreen.png);height:10px;width:10px;float:right;"></div>':'';//'<img title="Minimise/Maximise" style="cursor:default" src="Images/bgreen.png"  id="'+cid+'_min">'+bsavegeo:'';
      titlebar.innerHTML='<span title="Close" id="'+cid+'_ti" style="position:absolute; left:3px; top:1px; font:bold 9pt sans-serif; color:'+titletextcolor+'; height:18px; overflow:hidden; clip-height:16px;">'+title+'</span><div id="'+cid+'_btt" style="position:absolute; width:48px; left:'+(tw-48)+'px; top:2px; text-align:right">'+'<div id="'+cid+'_cls" class="btn_cls" style="cursor:pointer;background-image:url(Images/bred.png);height:10px;width:10px;float:right;"></div>'+tmp+'</div>';//'<img style="cursor:default" src="Images/bred.png" title="Close" id="'+cid+'_cls"></div>';
      tw=(ie5)?w-7:w-13;
      var content=new subBox(2,ht+4,tw,h-15-ht,bgcolor,cid+'_c');
      content.style.borderColor=bordercolor;
      content.style.borderWidth="2px";
      if(isExt){
	content.innerHTML='<iframe style="border:none;padding:0px" name="'+cid+'_ifrm" id="'+cid+'_ifrm" src="'+text+'" width="100%" height="100%"></iframe>';
	content.style.overflow="hidden";
      }else{
	if(ie5)content.style.scrollbarBaseColor=scrollcolor;
	content.style.borderStyle="inset";
	content.style.overflow="auto";
	content.style.padding="0px 2px 0px 4px";
	content.innerHTML=text;
	content.style.font=fontstyleset;
	content.style.color=textcolor;
      }
      var extWA=new subBox(2,24,0,0,'',cid+'_extWA');
      extWA.style.display="none";
      extWA.style.width='100%';
      extWA.style.height='100%';
      outerdiv.appendChild(titlebar);
      outerdiv.appendChild(content);
      outerdiv.appendChild(extWA);
      outerdiv.appendChild(rdiv);
      d.body.appendChild(shadow);
      d.body.appendChild(outerdiv);
      d.gEl=d.getElementById;
      if(!showonstart)hidebox(cid);
      var wB=d.gEl(cid+'_b');
      wB.cid=cid;
      wB.isExt=(isExt)?true:false;
      var wT=d.gEl(cid+'_t');
      wT.cid=cid;
      if(isresize){
	var wRS=d.gEl(cid+'_rs');
	wRS.cid=cid;
	var wMIN=d.gEl(cid+'_min');
	wMIN.cid=cid;
	var wMAX=d.gEl(cid+'_max');
	wMIN.h=h;
	wMAX.cid=cid;
	wMIN.onclick=minimize;
	wMAX.onclick=savegeo;
	wRS.onmousedown=startRS;
	wRS.onmouseup=stopRS;
      }
      var wCLS=d.gEl(cid+'_cls'); //8
      var wEXTWA=d.gEl(cid+'_extWA'); //11
      wB.activecolor=titlecolor;
      wB.inactivecolor=scrollcolor;
      if(oldac!=null)d.gEl(oldac.cid+"_t").style.backgroundColor=oldac.inactivecolor;
      oldac=wB;
      wCLS.onclick=new Function("hidebox('"+cid+"');");
      wB.onmousedown=function(){ changez(this) }
      if(isdrag){
	wT.onmousedown=grab_id;
	wT.onmouseup=stopdrag;
      }
    }else{
      if(oldOK){
	var ctr=new Date();
	ctr=ctr.getTime();
	var t=(isExt)?text:'';
	var posn=(ns4)? 'screenX='+x+',screenY='+y: 'left='+x+',top='+y;
	var win=window.open(t , "abc"+ctr , "status=no,menubar=no,width="+w+",height="+h+",resizable="+((isresize)?"yes":"no")+",scrollbars=yes,"+posn);
	if(!isExt){
	  t='<html><head><title>'+title+'</title></head><body bgcolor="'+bgcolor+'"><font style="font:'+fontstyleset+'; color:'+textcolor+'">'+text+'</font></body></html>';
	  win.document.write(t);
	  win.document.close();
	}}}}}

if(ns6)setInterval('movepopup()',40);

if(w3c){
d.onmousemove=trackmouse;
d.onmouseup=new Function("currRS=null");
}


  
