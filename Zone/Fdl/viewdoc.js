
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

include_js('WHAT/Layout/AnchorPosition.js');
include_js('FDL/Layout/common.js');;
include_js('WHAT/Layout/DHTMLapi.js');
include_js('WHAT/Layout/AnchorPosition.js');
include_js('WHAT/Layout/geometry.js');
include_js('FDL/Layout/iframe.js');


function popdoc(event,url,title) {
    
//    if(window.parent.Ext){
//        //alert('ExtJS is detected');
//        console.log(event,url);
//        window.parent.Ext.fdl.Interface.prototype.publish('openurl',url,"???",{opener:window});         
//        //return me;
//    } else {

  if (event) event.cancelBubble=true;     
  if (ctrlPushed(event)) {
    subwindow([FDL_HD2SIZE],[FDL_VD2SIZE],'_blank',url);
  } else {

    var dpopdoc = document.getElementById('POPDOC_s');
    var fpopdoc;
    var scrolly=window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
    if (! dpopdoc) {
    	if (! title) title='[TEXT:mini view]';
      new popUp([mgeox], [mgeoy] + scrolly, [mgeow], [mgeoh], 'POPDOC', url, 'white', '#00385c', '16pt serif', title, '[COLOR_B5]', '[CORE_TEXTBGCOLOR]', '[COLOR_B7]', '[CORE_BGCOLORALTERN]', '[CORE_BGCOLORALTERN]', true, true, true, true, true, false);
    } else {      
      if ((getObjectTop(dpopdoc) < scrolly) || 
	  (getObjectTop(dpopdoc) > (getInsideWindowHeight() + scrolly))	){
	// popup is not visible in scrolled window => move to visible part
	movePopup('POPDOC' ,[mgeox], [mgeoy]+scrolly);
      } 
      changecontent( 'POPDOC' , url );
      showbox( 'POPDOC');

    }
  }
  
//    }
}
function poptext(text,title) {   

	var dpopdoc = document.getElementById('POPDOC_s');
	var fpopdoc;
	var scrolly=window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
	if (! dpopdoc) {
		new popUp([mgeox], [mgeoy] + scrolly, [mgeow], [mgeoh], 'POPDOC', text, 'white', '#00385c', '16pt serif', title, '[COLOR_B5]', '[CORE_TEXTBGCOLOR]', '[COLOR_B7]', '[CORE_BGCOLORALTERN]', '[CORE_BGCOLORALTERN]', true, true, true, true, false, false);
	} else {      
		if ((getObjectTop(dpopdoc) < scrolly) || 
				(getObjectTop(dpopdoc) > (getInsideWindowHeight() + scrolly))	){
			// popup is not visible in scrolled window => move to visible part
			movePopup('POPDOC' ,[mgeox], [mgeoy]+scrolly);
		} 
		changecontent( 'POPDOC' , url );
		showbox( 'POPDOC');
	}
}

// create popup for insert div after
function newPopdiv(event,divtitle,x,y,w,h) {

  if (event) event.cancelBubble=true;     
    
    GetXY(event); 
  if (!x) x=Xpos;
  if (!y) y=Ypos;
  if (!w) w=[mgeow];
  if (!h) h=[mgeoh];

    var dpopdiv = document.getElementById('POPDIV_s');
    var fpopdiv;
    var scrolly=window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
    if (! dpopdiv) {
      new popUp(x, y, w, h, 'POPDIV', 'zou', '[CORE_BGCOLOR]', '[CORE_TEXTFGCOLOR]', '16pt serif', divtitle, '[COLOR_B5]', '[CORE_TEXTFGCOLOR]', '[COLOR_B7]', '[CORE_BGCOLORALTERN]', 'black', true, true, true, true, false, false,true);
    
    } else {
      if ((getObjectTop(dpopdiv) < scrolly) || 
	  (getObjectTop(dpopdiv) > (getInsideWindowHeight() +scrolly))	){
	// popup is not visible in scrolled window => move to visible part
	movePopup('POPDIV' ,[mgeox], [mgeoy]+scrolly);
      } 
      showbox( 'POPDIV');    
  }
    return document.getElementById('POPDIV_c');
}


function postit(url,x,y,w,h) {
		      
  if (!x) x=150;
  if (!y) y=110;
  if (!w) w=300;
  if (!h) h=200;
  var dpostit = document.getElementById('POSTIT_s');
  if (! dpostit) {
    new popUp(x, y, w, h, 'POSTIT', url, '#faff77', '#00385c', '16pt serif', '[TEXT:post it]', 'yellow', '[CORE_BGCOLORALTERN]', 'yellow', 'transparent', '#faff77', true, true, true, true, true, false,true);
    
  } else {
    if ((getObjectTop(dpostit) < document.body.scrollTop) || 
	(getObjectTop(dpostit) > (getInsideWindowHeight() +document.body.scrollTop))	){
      // popup is not visible in scrolled window => move to visible part
      movePopup('POSTIT' ,250, 210+document.body.scrollTop);
    } 
    changecontent( 'POSTIT' , url );
    showbox( 'POSTIT');
  }
}


function viewwask(url,x,y,w,h) {
		      
  if (!x) x=180;
  if (!y) y=210;
  if (!w) w=300;
  if (!h) h=200;
  var dviewwask = document.getElementById('VIEWWASK_s');
  if (! dviewwask) {
    new popUp(x, y, w, h, 'VIEWWASK', url, '[COLOR_WHITE]', '#00385c', '16pt serif', '[TEXT:ask]', '[COLOR_B5]', '[CORE_TEXTBGCOLOR]', '[COLOR_B7]', 'transparent', '[CORE_BGCOLORALTERN]', true, true, true, true, true, false,true);
    
  } else {
    if ((getObjectTop(dviewwask) < document.body.scrollTop) || 
	(getObjectTop(dviewwask) > (getInsideWindowHeight() +document.body.scrollTop))	){
      // popup is not visible in scrolled window => move to visible part
      movePopup('VIEWWASK' ,250, 210+document.body.scrollTop);
    } 
    changecontent( 'VIEWWASK' , url );
    showbox( 'VIEWWASK');
  }
}
function centerError() {
  CenterDiv('error');
}
function reloadWindow(w) {
  var h=w.location.href;

  var l=h.substring(h.length-1);
  if (l=='#') h=h.substring(0,h.length-1);
  w.location.href=h;

  
}
function refreshParentWindows() {  

  if (parent.flist) reloadWindow(parent.flist);
  else if (parent.fvfolder) reloadWindow(parent.fvfolder);
  else if (parent.ffoliolist) {
    reloadWindow(parent.ffoliolist);
    if (parent.ffoliotab) reloadWindow(parent.ffoliotab);
  } else if (window.opener && window.opener.document.needreload) reloadWindow(window.opener);
  
}
function updatePopDocTitle() {
  if (window.parent && window.name) {
       
      var l=window.name.substring(0,window.name.length - 5)+'_ti';      
    var fpopdoc_t= window.parent.document.getElementById(l);
    if (fpopdoc_t) {
      if (window.document && (window.document.title!="")) {
	fpopdoc_t.innerHTML=window.document.title;
      } else {
	fpopdoc_t.innerHTML="mini vue";
      }
    }
  }
}

function viewwaitbarmenu(thea,bar,title) {
    if (bar && thea) {
	var la=bar.getElementsByTagName('a');
	for (var i=0;i<la.length;i++) {
	    la[i].style.visibility='hidden';
	}
	thea.onclick='';
	thea.style.visibility='visible';
	globalcursor('wait');
	setTimeout('viewwait(true)',500); 
	setbodyopacity(0.5);
    }
}
// op 
function resetbodyopacity() {
  // alert(document.body);
  if (isIE) {
     document.body.style.filter='';
  } else {
     document.body.style.opacity=1.0;
  }
}


// op between 0..1.0
function setbodyopacity(op) {
  if (isIE) {
    op=parseInt(op*100);
     document.body.style.filter='alpha(opacity'+ op + ')';;
  } else {
     document.body.style.opacity=op;
  }
}
function viewwait(view) {
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
    if (view) {
      CenterDiv(wimgo.id);
      wimgo.style.display='inline';
    } else {
      wimgo.style.display='none';
    }
  }
}
addEvent(window,"load",updatePopDocTitle);
