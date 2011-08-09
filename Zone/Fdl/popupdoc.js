
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

var CTRLKEYMENU=false;
var INPROGRESSMENU=false;
var MENUCIBLE=null;
var MENUSOURCE=null;
var MENUREQ=null;
var MENUOUT=true;
var MENUOUTTIMER=false;
var XMENU,YMENU;
var OPENSUBMENU=false;
var DIVPOPUPMENU=document.createElement("div");
var MENUSOURCETOPBORDER,MENUSOURCEBOTTOMBORDER;
var MENUSOURCECLASS;
var MENUIDENTIFICATOR=false;

//include_js('WHAT/Layout/geometry.js');
//include_js('WHAT/Layout/DHTMLapi.js');
//include_js('WHAT/Layout/AnchorPosition.js');

addEvent(window,"load",function adddivpop() {document.body.appendChild(DIVPOPUPMENU)});
function reqViewMenu() {
  INPROGRESSMENU=false; 
  document.body.style.cursor='auto';
  var o=MENUCIBLE;
 
  if (MENUREQ.readyState == 4) {
    // only if "OK"
    //dump('readyState\n');
    if (MENUREQ.status == 200) {
      // ...processing statements go here...
      //  alert(MENUREQ.responseText);
      unglobalcursor();

      if (MENUREQ.responseXML) {
	var elts = MENUREQ.responseXML.getElementsByTagName("status");

	if (elts.length == 1) {
	  var elt=elts[0];
	  var code=elt.getAttribute("code");
	  var delay=elt.getAttribute("delay");
	  var c=elt.getAttribute("count");
	  var w=elt.getAttribute("warning");

	  if (w != '') alert(w);
	  if (code != 'OK') {
	    alert('code not OK\n'+MENUREQ.responseText);
	    return;
	  }
	  elts = MENUREQ.responseXML.getElementsByTagName("branch");
	  elt=elts[0].firstChild.nodeValue;
	  // alert(elt);
	  if (o) {
	    if (c > 0)       o.style.display='';
	    o.style.left = '0px';
	    o.style.top  = '0px';
	    o.innerHTML=elt;
	    openDocMenu(false,'popupdoc');
	  }
	  
	} else {
	viewTextError();
	//alert('no status\n'+MENUREQ.responseText);
	  return;
	}
      } else {
	viewTextError();
	//	alert('no xml\n'+MENUREQ.responseText);
	return;
      } 	  
    } else {
      alert("There was a problem retrieving the XML data:\n" +
	    MENUREQ.statusText);
      return;
    }
  } 
}
function viewTextError() {
  MENUCIBLE.innerHTML=MENUREQ.responseText;
  MENUCIBLE.style.position='absolute';

  MENUCIBLE.style.left = XMENU+'px';
  MENUCIBLE.style.top  = YMENU+'px';
  MENUCIBLE.style.width='100px';
  MENUCIBLE.style.display='';
  setTimeout("MENUCIBLE.style.display='none'",2000);  
}
function menuSend(event,menuurl,cible,coord) {
  if (INPROGRESSMENU) return false; // one request only
    // branch for native XMLHttpRequest object
    if (window.XMLHttpRequest) {
        MENUREQ = new XMLHttpRequest(); 
    } else if (window.ActiveXObject) {
      // branch for IE/Windows ActiveX version
      isIE = true;
      MENUREQ = new ActiveXObject("Microsoft.XMLHTTP");
    }
    if (MENUREQ) {
        MENUREQ.onreadystatechange = reqViewMenu ;
        MENUREQ.open("POST", menuurl,true); //'index.php?sole=Y&app=FDL&action=POPUPDOCDETAIL&id='+docid, true);
	MENUREQ.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); 
	MENUCIBLE=cible;

	globalcursor('wait');


	MENUREQ.send('');
	
	
	INPROGRESSMENU=true;
	document.body.style.cursor='progress';	

	cible.style.left='0px';
	cible.style.top='0px';
	cible.style.width  = '30px';
	
	//	clipboardWait(cible);
	return true;
    }    
    return false;
}

function viewmenu(event,murl,source,coord) {
  closeDocMenu()
  CTRLKEYMENU=ctrlPushed(event);
  MENUSOURCE=source;	
  if (coord) {    
    XMENU = coord.x;
    YMENU  = coord.y;
    if (isNetscape) {      
      YMENU+=3;
    }
  } else {
    GetXY(event);
    XMENU=Xpos;
    YMENU=Ypos;
    if (isNetscape) {
      XMENU+=3;
      YMENU+=3;
    }
  }
  //   MENUSOURCE.style.borderStyle='solid';
  // MENUSOURCE.style.borderColor='black';
  //MENUSOURCE.style.borderWidth='1px';

  if (MENUSOURCE) {
    //    MENUSOURCE.style.borderTopStyle='dashed none dashed none';
    MENUSOURCETOPBORDER=MENUSOURCE.style.borderStyle;
    MENUSOURCEBOTTOMBORDER=MENUSOURCE.style.borderStyle;
    MENUSOURCECLASS=MENUSOURCE.className;
    MENUSOURCE.className='popupsource';

    // MENUSOURCE.style.borderTop='dashed 1px #777777';
    //MENUSOURCE.style.borderBottom='dashed 1px #777777';
  }
  menuSend(event,murl,DIVPOPUPMENU,coord);
}

function viewsubmenu(event,murl,upobject,source) {
  var coord=false;
  // var source=false;
  if (upobject) {    
    coord=new Object();;
    coord.x=AnchorPosition_getPageOffsetLeft(upobject);
    coord.y=AnchorPosition_getPageOffsetTop(upobject)+getObjectHeight(upobject);
  } 

  viewmenu(event,murl,source,coord);
}
function closeDocMenu() {
  var o =DIVPOPUPMENU;
  if (o) o.style.display='none';
  if (MENUSOURCE) {
    MENUSOURCE.className=MENUSOURCECLASS;
    //MENUSOURCE.style.borderTop=MENUSOURCETOPBORDER;
    // MENUSOURCE.style.borderBottom=MENUSOURCEBOTTOMBORDER;
  }
  MENUIDENTIFICATOR=false;
}
function sendMenuUrl(th, url, wname,bar,w,h) {
  if ((th.className == 'menuItem') || (th.className == 'menuItemCtrl')) {


    if ((wname == "")||(wname == "_self")||(wname == "_download")) {
      //      setTimeout('viewwait()',1000);    
    	if (wname != "_download") {
	globalcursor('wait');  
	setTimeout('viewwait(true)',500); 
	setbodyopacity(0.5);
    	}
      window.location.href=url;
    } else {
      if (bar) subwindowm(h,w,wname,url);
      else subwindow(h,w,wname,url);
    }
   
  }
}

function openDocMenu(event, menuid) {
  var el, x, y;
  var cy,h1,hf;
  
 
  x=XMENU;y=YMENU;
  if ((x==0) && (y==0)) {
    x=Xold;
    y=Yold;
    if ((x==0) && (y==0)) {x=100;y=100;}
  }

  x -= 2; y -= 2;



  //  closeSubMenu(menuid);
  OPENSUBMENU=false;
  activeMenuDocItem(event,menuid);

  el = document.getElementById(menuid);
  el.style.left = "0px";
  el.style.top  = y + "px";
  //el.style.width  =  "100%";
  el.style.visibility = "hidden";


  // complete sub menus
  


  // test if it is on right of the window
  //  w2=getObjectWidth(document.body);
  w2=getFrameWidth();
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
function openSubDocMenu(event, th, menuid) {
  var xy=getAnchorPosition(th.id);
  var dx=th.parentNode.offsetWidth;
  var el,cy,hf,hh;
  var x1,x2,w1,w2,dw;
  var x=xy.x;
  var y=xy.y;
  
  if (OPENSUBMENU) {
    //    OPENSUBMENU.style.display='none';
    OPENSUBMENU.style.visibility='hidden';
  }

  el=document.getElementById(menuid);
  OPENSUBMENU=el;
  // close sub menu before
  // closeSubMenu(th.parentNode.id);
  

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

  //  openMenuXY(event,menuid,x2,y);
    el.style.top = y + "px";
    el.style.left = x2 + "px";
    el.style.display = "none";
    el.style.display = "";
    el.style.visibility = "visible";
}
function menuover() {
  MENUOUT=false;
  if (MENUOUTTIMER) window.clearTimeout(MENUOUTTIMER);
  MENUOUTTIMER=false;
}

function menuout() {
  MENUOUTTIMER=window.setTimeout('closeDocMenu()',1500);

}
function activeMenuDocItem(event,menuid) {
  //window.status="menu:"+menuid+itemid;
  // active css for animation for 'selid' object
  var o=document.getElementById(menuid);
  if (o) {
    var ta=o.getElementsByTagName("a");
    var mitem;
    var submenu;
    var menuitem;

    for (var i=0; i<ta.length; i++) {

      //      alert(tdivid[menuid][i]);
      mitem = ta[i];
      visibility=mitem.getAttribute('visibility');
      if (visibility == 1) {
	mitem.className='menuItem';
	
      } else      if (visibility == 2) {
	mitem.className='menuItemInvisible';
	
      } else   if (visibility == 3) {
	if (CTRLKEYMENU) mitem.className='menuItemCtrl';
	else  mitem.className='menuItemInvisible';
	
      } else  if (visibility == 4) {
	if (CTRLKEYMENU) mitem.className='menuItemCtrlDisabled';
	else  mitem.className='menuItemInvisible';
	
      } else if (visibility == 0) {
	mitem.className = 'menuItemDisabled';
	mitem.onclick= function () {closeDocMenu();}
      } 
      //      mitem.onmouseover=menuover;
      addEvent(mitem,'mouseover',menuover);
      addEvent(mitem,'mouseout',menuout);
    }    

    // complete sub menu
    for (var i=0; i<ta.length; i++) {

      mitem = ta[i];      
      submenu=mitem.getAttribute('submenu');
      if (submenu != "") {
	sdiv=document.getElementById('popup'+submenu);
	if (sdiv) {
	   sdiv.appendChild(mitem);
	   i--;
	   menuitem=document.getElementById(submenu);
	   if (menuitem) {
	     if (mitem.className=='menuItem') menuitem.className='menuItem';
	     else if (CTRLKEYMENU && (mitem.className!='menuItemInvisible')) menuitem.className='menuItem';
	   }
	}
      }
    }

  }
  
}
