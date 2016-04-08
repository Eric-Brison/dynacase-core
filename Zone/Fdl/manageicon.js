
/**
 * @author Anakeen
 */



var oldBgcolor=0;
var oldBdstyle=0;
function highlight(th) {


  th.className="select";
  //oldBgcolor=th.style.backgroundColor;
  //oldBdstyle=th.style.borderStyle;
  //th.style.backgroundColor='[CORE_BGCOLORALTERN]';
  //th.style.borderStyle='solid';
  
}
function unhighlight(th) {
  th.className="unselect";
  //th.className="icon";
  //th.style.backgroundColor='[CORE_BGCELLCOLOR]';
  //th.style.borderStyle='none';
  //th.style.backgroundColor=oldBgcolor;
    //  th.style.borderStyle=oldBdstyle;
}

var isNetscape = navigator.appName=="Netscape";

var docid = 0;




// align automatically icon with screen width 
function placeicons(dy) {

  if (! dy) dy=30;
      winW=getFrameWidth();
	nbicons=[nbdiv];
	nbcol = Math.floor(winW/70);
	if (nbcol < 1) nbcol=1;

 	for (i=1; i <= nbicons; i++) {
         div = document.getElementById('d'+i);

	 div.style.left = ((i-1)%nbcol) * (div.offsetWidth);
	 div.style.top = Math.floor((i-1)/nbcol)*(div.offsetHeight) + dy;
	 div.style.visibility = 'visible';
	}
  }
var diva=document.getElementById('a1');


// select document
function select(th, id, divid) {

  if (diva) {
      diva.style.visibility='hidden';
  }

  if (selobjid)  unhighlight(document.getElementById(selobjid));
  highlight(th);
  docid=id;
  document.docid = docid;
  selid = divid;
  selobjid = th.id;
  imgid="i"+divid;
}

var odocid=false;
// select on mouse over document
function oselect(id) {
  odocid=id;
}
// select on mouse over document
function ounselect() {
  odocid=false;
}

function viewabstract(event) {
  
  diva = document.getElementById('a'+selid);
  if (!diva) return;
  div = document.getElementById('d'+selid);
  

      //diva.innerHTML = diva.innerHTML;

      //alert('diva');
      diva.style.visibility='visible';


	if ((div.offsetLeft+div.offsetWidth-10 + diva.offsetWidth) > getFrameWidth()) 
	     diva.style.left = div.offsetLeft-diva.offsetWidth+10;         
	else diva.style.left = div.offsetLeft+div.offsetWidth-10;

	if ((div.offsetTop+div.offsetHeight-60 + diva.offsetHeight) > getFrameHeight()) 
	     diva.style.top = div.offsetTop-diva.offsetHeight;
	else diva.style.top = div.offsetTop+div.offsetHeight-60;
      diva.style.zIndex = 5;
       
}



function openMenuOrAbstract(event) {
    var button, shiftKey;

    if (! event) event=window.event;
    if (event.which) {
        button=event.which;
    } else {
        button=(event.button & 1)?1:0;
    }
    shiftKey = event.shiftKey

  if (button == 1) {
    if (shiftKey ) {
      openMenu(event,'popup');
     } else {
      viewabstract(event)
    }
  }

}


function openMenuOrProperties(event,menuid,itemid,wtarget) {
  var target;
    var button, shiftKey;

    if (! event) event=window.event;
    if (event.which) {
        button=event.which;
    } else {
        button=(event.button & 1)?1:0;
    }
    shiftKey = event.shiftKey

  //window.status=shiftKey+"/"+button;
  if (button == 1) {
    if (parent.parent.ffoliolist && (parent.parent.ffoliolist!=self)) {
      // copy to portfolio
      addToBasket(event,'ffoliolist',parent.parent.ffoliolist.document.dirid,true);
    } else {

      if (wtarget) target=wtarget;
      else if (docTarget)  target=docTarget;
      else target='fdoc';
      if ((target=='fdoc') && (! parent.fdoc)) target='_blank'; // open in new because can be unvisible target

      if (shiftKey ) {
	openMenu(event,menuid, itemid);
      } else {
	subwindow([FDL_VD2SIZE],[FDL_HD2SIZE],target,'[CORE_STANDURL]&app=FDL&action=FDL_CARD&props=N&abstract=N&id='+docid);
      }
    }
  }
}

function sendFirstFile(docid) {
  var url='[CORE_STANDURL]&app=FDL&action=EXPORTFIRSTFILE&docid='+docid;

  we = window.open('[IMG:1x1.gif]','','resizable=yes,scrollbars=yes');
  we.document.location.href=url;
}

function openFld(docid) {
  var url='[CORE_STANDURL]&app=FREEDOM&action=FREEDOM_VIEW&dirid='+docid;
  subwindow([FDL_HD2SIZE],[FDL_VD2SIZE],'fvfolder',url);
}
//--------------------- DRAG & DROP  --------------------------
var drag=0;




//document.onmousemove = GetXY;;

//document.onkeypress = trackKey;
addEvent(document,"keypress",trackKey);

function trackKey(event) {
  var intKeyCode,altKey,ctrlKey ;

  if (!event) event=window.event;
  if (isNetscape) {
    intKeyCode = event.which;
    altKey = event.altKey;
    ctrlKey = event.ctrlKey;
  }  else {
    intKeyCode = window.event.keyCode;
    altKey = window.event.altKey;
    ctrlKey = window.event.ctrlKey;
  }
 
  //window.status=intKeyCode + ':'+altKey;
  if (ctrlKey &&  ( (intKeyCode == 99)||(intKeyCode == 67))) { // Ctrl-C key
    // activedrag(event); 
    if (parent.parent.ffoliolist) {
      addToBasket(event,'ffoliolist',parent.parent.ffoliolist.document.dirid,true); 
    } else     addToBasket(event); 
    return false;
  } else
    return true;
}

function addToBasket(event,rtarget,dirid,folio) {
  if (!dirid) dirid='[FREEDOM_IDBASKET]';
  if (!rtarget) rtarget='basket';
  var url='[CORE_STANDURL]&app=FREEDOM&action=ADDDIRFILE&dirid='+dirid+'&docid=';
  var bsend=false;
  if (odocid) {    
    bsend=true;
    url+=odocid;
  } else if (docid) {
    bsend=true;
    url+=docid;
  }
  if (folio) url+='&folio=Y';
  if  (bsend)  subwindow([FDL_VD2SIZE],[FDL_HD2SIZE],rtarget,url);
}

function moveicon(event) {
    
//    window.status="drag="+document.drag;
  if (drag) {
    GetXY(event);
    micon.style.top = Ypos+2; 
    micon.style.left = Xpos+2; 
  }
}

var micon;
function initmicon() {
    micon = document.getElementById('micon');

	}

  

var selid=0; // selected object
var imgid=0;
var selobjid=0; // HTML object selected
function activedrag(event)
{

  document.onmousemove= moveicon;



  drag=1;
    micon.src=document.getElementById(imgid).src;


    GetXY(event);
    window.status=Xpos+"+"+Ypos;
    micon.style.visibility = 'visible';
    micon.style.top = Ypos+2; 
    micon.style.left = Xpos+2; 
    micon.style.zIndex = 14; 
    //document.body.style.cursor='move';
  return false;
}
function deactivedrag(th)
{
  document.onmousemove= "";
  drag=0;
    document.body.style.cursor='auto';
  return true;
}
