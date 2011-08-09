
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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

function viewdocmenu(event,docid,onlyctrl,upobject,sourceobject) {
  if (!event) event=POPMENUINPROGRESSEVENT;

  POPMENUINPROGRESSELT=false;
  var corestandurl=window.location.pathname+'?sole=Y&';
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
  viewsubmenu(event,menuurl,upobject,sourceobject);
  return false;
}


function viewdocsubmenu(event,docid,submenu,upobject) {
  POPMENUINPROGRESSELT=false;
  var corestandurl=window.location.pathname+'?sole=Y&';
  var menuapp=MENUAPP;
  var menuaction=MENUACTION;
  var menuopt='';
  var coord=false;
  if (submenu) menuopt='&submenu='+submenu;
  else {
    if (ctrlPushed(event) && altPushed(event)) {
      menuapp='FDL';
      menuaction='POPUPDOCDETAIL';
    } 
  }

  var menuurl=corestandurl+'app='+menuapp+'&action='+menuaction+menuopt+'&id='+docid+PDS;
  viewsubmenu(event,menuurl,upobject);
}

/* verify first if is open */
function bardocmenu(event,docid,onlyctrl,upobject,sourceobject) {
  var mid='bar'+docid;
  if (mid == MENUIDENTIFICATOR) {
    closeDocMenu();
  } else {
    viewdocmenu(event,docid,onlyctrl,upobject,sourceobject);
    MENUIDENTIFICATOR=mid;
  }
}
function bardocsubmenu(event,docid,submenu,upobject) {
  var mid='bar'+docid+submenu;
  if (mid == MENUIDENTIFICATOR) {
    closeDocMenu();
  } else {
    viewdocsubmenu(event,docid,submenu,upobject);
    MENUIDENTIFICATOR=mid;
  }
}
