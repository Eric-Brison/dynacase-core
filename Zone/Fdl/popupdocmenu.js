
/**
 * @author Anakeen
 */


var POPMENUINPROGRESSELT=false;
var POPMENUINPROGRESSEVENT=false;

//include_js('FDL/Layout/common.js');
//include_js('FDL/Layout/popupdoc.js');

function cloneEvent(e) {
  var c=new Object();
  var names="";
  for (var name in e) {
   try {
     c[name]=e[name];
   }
   catch (ex) {
     c[name]=false;
   }
  }
  return c;
}

function godocmenu(event,o) {
  if (window.event) {
    event=cloneEvent(window.event);  
  }
  POPMENUINPROGRESSELT = o;
  POPMENUINPROGRESSEVENT = event;
  //  displayPropertyNames(POPMENUINPROGRESSEVENT);
  setTimeout('setonclick()',200); // wait 200ms before send request for menu
}
function aborddocmenu(event) {
  POPMENUINPROGRESSELT = false;
}

function setonclick(event) {
  if (POPMENUINPROGRESSELT) {
    var o=POPMENUINPROGRESSELT;
    o.onclick.apply(o,[POPMENUINPROGRESSEVENT]);
    POPMENUINPROGRESSELT=false;
  }
}

function viewdocmenu(event,docid,onlyctrl,upobject,sourceobject, barmenu) {
  if (!event) event=POPMENUINPROGRESSEVENT;


    var e = (event.target) ? event.target : ((event.srcElement) ? event.srcElement : null);
    if ((! sourceobject) && e && e.getAttribute("oncontextmenu")) return true;
  POPMENUINPROGRESSELT=false;
  var corestandurl='?';
  var menuapp=MENUAPP;
  var menuaction=MENUACTION;
  var menuopt='';
  var coord=false;  
  
  if (onlyctrl) menuopt='&onlyctrl=yes';
  else {
    if (ctrlPushed(event) && altPushed(event)) {
      menuapp='FDL';
      menuaction='POPUPDOCDETAIL';
    } else if (altPushed(event)) {
      return true;
    } 
  }
  var menuurl=corestandurl+'app='+menuapp+'&action='+menuaction+menuopt+'&id='+docid+PDS;
  viewsubmenu(event,menuurl,upobject,sourceobject, barmenu);
    event.returnValue = false;
    stopPropagation(event);
  return false;
}


function viewdocsubmenu(event,docid,submenu,upobject, barmenu) {
  POPMENUINPROGRESSELT=false;
  var corestandurl='?sole=Y&';
  var menuapp=MENUAPP;
  var menuaction=MENUACTION;
  var menuopt='';
  var coord=false;



  if (submenu) menuopt='&submenu='+encodeURIComponent(submenu);
  else {
    if (ctrlPushed(event) && altPushed(event)) {
      menuapp='FDL';
      menuaction='POPUPDOCDETAIL';
    } 
  }

  var menuurl=corestandurl+'app='+menuapp+'&action='+menuaction+menuopt+'&id='+docid+PDS;
  viewsubmenu(event,menuurl,upobject, false, barmenu);
}

/* verify first if is open */
function bardocmenu(event,docid,onlyctrl,upobject,sourceobject) {
  var mid='bar'+docid;
  if (mid == MENUIDENTIFICATOR) {
    closeDocMenu();
  } else {
    viewdocmenu(event,docid,onlyctrl,upobject,sourceobject, true);
    MENUIDENTIFICATOR=mid;
  }
}
function bardocsubmenu(event,docid,submenu,upobject) {
  var mid='bar'+docid+submenu;
  if (mid == MENUIDENTIFICATOR) {
    closeDocMenu();
  } else {
    viewdocsubmenu(event,docid,submenu,upobject, true);
    MENUIDENTIFICATOR=mid;
  }
}
