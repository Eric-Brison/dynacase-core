
/**
 * @author Anakeen
 */

var isNetscape = navigator.appName=="Netscape";

var BARMENUIDENTIFICATOR=false;


function changeClass(th, name)
{ th.className=name;return true}



// return true is shift key is pushed
function shiftKeyPushed(event) {

  if (window.event) shiftKey = window.event.shiftKey	
    else shiftKey = event.shiftKey	

  return shiftKey;
}

// return true is shift key is pushed
function ctrlKeyPushed(event) {

  if (window.event) ctrlKey = window.event.ctrlKey	
    else ctrlKey = event.ctrlKey	

  return ctrlKey;
}

// 1 for first : 1 | 2 | 3
function buttonNumber(event) {
  if (window.event) return button=window.event.button;
  else return button= event.button +1;
}

function getScrollYOffset() {
  if (document.all) return document.body.scrollTop;
  else return window.pageYOffset;
}


function openSubMenu(event, th, menuid) {
  var xy=getAnchorPosition(th.id);
  var dx=th.parentNode.offsetWidth;
  var el,cy,hf,hh;
  var x1,x2,w1,w2,dw;
  var x=xy.x;
  var y=xy.y;

  // close sub menu before
  closeSubMenu(th.parentNode.id);
  activeMenuItem(event,menuid, 1);

  el = document.getElementById(menuid);
  w1=getObjectWidth(el);
  w2=getObjectWidth(document.body);
  x2=x+dx;
  if (x+w1+dx > w2) {
	if (w1<w2) {
	  x2=x-w1;
	} 
  } 

  cy=(window.event)?window.event.clientY:event.clientY;
  hf=getFrameHeight();
  h1=getObjectHeight(el);
  hh=getObjectHeight(th);
  //  alert(h1+'-'+cy+'-'+hf+'-'+xy.y);
  if (cy+h1>hf) y=y-h1+hh;

  openMenuXY(event,menuid,x2,y);
}

function closeSubMenu(menuid) {  
 
  var sm = document.body.getElementsByTagName('div');
 
  for (var i=0; i<sm.length; i++) {
    //alert(sm[i].id+':'+sm[i].getAttribute('name'));
    if (sm[i].getAttribute('name') == menuid)    closeMenu(sm[i].id);
  }
}
var Xold; // for short cut key
var Yold;
function openMenu(event, menuid, itemid) {

  var el, x, y;
  var cy,h1,hf;
  GetXY(event);
  if ((Xpos>0) && (Ypos>0)) {
   Xold=Xpos;
   Yold=Ypos;
  }
  x=Xpos;y=Ypos;
  if ((x==0) && (y==0)) {
    x=Xold;
    y=Yold;
    if ((x==0) && (y==0)) {x=100;y=100;}
  }

  x -= 2; y -= 2;


  el = document.getElementById(menuid);
  el.style.left = "0px";
  el.style.top  = y + "px";
  el.style.visibility = "hidden";
  el.style.display = "";

  closeSubMenu(menuid);
  activeMenuItem(event,menuid, itemid);
  // test if it is on right of the window
  w2=getObjectWidth(document.body);
      // display right or left to maximize width
  w1=getObjectWidth(el);

      x2=x;
      if (x+w1 > w2) {
	if (w1<w2) {
	  x2=w2-w1;
	} else {
	  x2=0;
	}
      } 

  cy=(window.event)?window.event.clientY:event.clientY;
  h1=getObjectHeight(el);
  hf=getFrameHeight();
  if (cy+h1 > hf) {
    y=y-h1+4;
    if (cy-h1 < 0) y=0;
  }
  if (h1 > hf) y=0;
    el.style.left = x2 + "px";
    el.style.top  = y + "px";
    el.style.display = "none";
    el.style.display = "";
    el.style.visibility = "visible";


 // event.stopPropagation();
  return false; // no navigator context menu
}

function viewwait() {
  var wimgo = document.getElementById('WIMG');
  if (! wimgo) {
    wimgo = document.createElement('img');
    wimgo.setAttribute('src','Images/loading.gif');
    wimgo.setAttribute('id','WIMG');
    wimgo.style.display='none';
    wimgo.style.position='absolute';
    wimgo.style.backgroundColor='#FFFFFF';
    wimgo.style.border='groove black 2px';
    wimgo.style.padding='4px';
    wimgo.style.MozBorderRadius='4px';
    document.body.appendChild(wimgo);
  }
  if (wimgo) {
    wimgo.style.display='inline';
    CenterDiv(wimgo.id);
  }
}

function openMenuXY(event, menuid, x, y) {

 
  var el,menudiv;

  var x1,x2,w1,w2,dw;
  var bm=document.getElementById('barmenu');
  el = document.getElementById(menuid);
  if (el) {
    if (isNetscape) el.style.position = "fixed";
    if (isNetscape && (el.style.position=='fixed')) {
      y -= getScrollYOffset();
    } 
    activeMenuItem(event,menuid, 1); // first item (no context : only one item)
    
    el.style.display = "none";
    el.style.top  = y + "px";
    el.style.left = "0px";
    el.style.visibility = "hidden";
    el.style.display = "";
    if (bm) {
      //w2=getObjectWidth(document.getElementById('barmenu'));
      w2=getFrameWidth();
      // display right or left to maximize width
      w1=getObjectWidth(el);


      if (x+w1 > w2) {
	if (w1<w2) {
	  x2=w2-w1;
	} else {
	  x2=0;
	}
      } else {
	x2=x;
      }

    } else {
      x2=x;
    }
    //    alert('x2:'+x2+',w1:'+w1+'w2:'+w2);
    el.style.left = x2 + "px";
    el.style.display = "none";
    el.style.display = "";
    el.style.visibility = "visible";

    el.style.overflow = "";
    el.style.height = "";
    if( document.body.clientHeight < el.clientHeight ) {
      el.style.overflow = "scroll";
      el.style.height = "100%";
    }
  }
  return false; // no navigator context menu
}

function openBarMenu(event, th, menuid) { 
  var mid='bar'+menuid;
  var x=th.offsetLeft;
  var y=th.offsetTop+th.offsetHeight+getScrollYOffset();
  
  if (mid == BARMENUIDENTIFICATOR) {
    closeMenu(menuid);
    unSelectMenu();    
  } else {
    closeAllMenu();
    selectMenu(th);
    BARMENUIDENTIFICATOR=mid;
    openMenuXY(event, menuid, x, y);
  }
}
var menusel=null;
function selectMenu(th) {
  unSelectMenu();
  th.className='MenuSelected';
  menusel=th;  
}
function unSelectMenu() {
  var bm=document.getElementById('barmenu');
  if (bm) {
    var ttd=bm.getElementsByTagName("td");
    for (var i=0;i<ttd.length;i++) {
      if (ttd[i].className=='MenuSelected')  ttd[i].className='MenuInactive';
    }
  }
}
function ActiveMenu(th) {
  if (th.className!='MenuSelected') th.className='MenuActive';
}
function DeactiveMenu(th) {
  if (th.className!='MenuSelected')  th.className='MenuInactive';  
}
function activeMenuItem(event,menuid, itemid) {
  //window.status="menu:"+menuid+itemid;
  // active css for animation for 'selid' object
    for (i=0; i<nbmitem[menuid]; i++) {

      //      alert(tdivid[menuid][i]);
      mitem = document.getElementById(tdivid[menuid][i]);
      if (tdiv[menuid][itemid][i] == 1) {
	mitem.className='menuItem';
	
      } else      if (tdiv[menuid][itemid][i] == 2) {
	mitem.className='menuItemInvisible';
	
      }else   if (tdiv[menuid][itemid][i] == 3) {
	if (ctrlKeyPushed(event)) mitem.className='menuItemCtrl';
	else  mitem.className='menuItemInvisible';
	
      }else  if (tdiv[menuid][itemid][i] == 4) {
	if (ctrlKeyPushed(event)) mitem.className='menuItemCtrlDisabled';
	else  mitem.className='menuItemInvisible';
	
      }else {
	mitem.className = 'menuItemDisabled';
	mitem.onclick= function () {closeMenu(menuid);}
      } 
    }
  
}


function closeMenu(menuid) {
  //  alert('closeMenu:'+menuid);
  closeSubMenu(menuid);
  if (document.getElementById) { // DOM3 = IE5, NS6
         divpop = document.getElementById(menuid);
	 if (divpop) divpop.style.visibility = 'hidden';
	 if (this.className == 'MenuSelected') this.className='MenuInactive';
  }    

  BARMENUIDENTIFICATOR=false;
  return false;
}

function activate(th, url, wname,bar,w,h) {
      var pWindow=getParentWindow();
  if ((th.className == 'menuItem') || (th.className == 'menuItemCtrl')) {
    // add referer url for client doesn't not support it
    //  var urlref;
  //   if (isNetscape) urlref=url;
//     else urlref= url+'&http_referer='+escape(window.location.href);

    if ((wname == "")||(wname == "_self")) {
      setTimeout('viewwait()',1000);      
      window.location.href=url;
    } else {
      if (!w) w=fdl_hd2size;
      if (!h) h=fdl_vd2size;

      if (wname == 'fdoc') {
          if (pWindow == window) {
              wname='_blank';
          } else {
            if (pWindow) {
              var mif = pWindow.frames.length;
              var foundFdoc = false;
              for (var i = 0; i < mif; i++) {
                if (pWindow.frames[i].name == wname) {
                  foundFdoc = true;
                  break;
                }
              }
              if (!foundFdoc) {
                wname = '_blank';
              }
            }
          }
      }
      if (bar) subwindowm(h,w,wname,url);
      else subwindow(h,w,wname,url);
    }
   
  }
}

function sendandreload(th, url) {
  if (th.className == 'menuItem') {
        subwindow(fdl_vd2size,fdl_hd2size,'doc_properties',url);
	//	closeMenu();
	//if (window.name != 'doc_properties')
	//  document.location.reload(true);
  }
}

function addSubMenuItems(mname,smname,divid) {
  var od=document.getElementById(divid);
  var e;
  if (od) {
    for (var i=0;i<tdivsmenu[mname].length;i++) {

      if (tdivsmenu[mname][i]==smname) {
	e=document.getElementById(tdivid[mname][i]);
	if (e) 	  od.appendChild(e);	
      }
    }
  }
}

var tdiv= new Array();
var tdivid= new Array();
var tdivsmenu= new Array();
var nbmitem= new Array();

