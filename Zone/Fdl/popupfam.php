<?php
/**
 * Specific menu for family
 *
 * @author Anakeen 2000 
 * @version $Id: popupfam.php,v 1.22 2008/08/14 09:59:14 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


include_once("FDL/Class.Doc.php");
// -----------------------------------
function popupfam(&$action,&$tsubmenu) {
  // -----------------------------------
  // ------------------------------
  // define accessibility
  $docid = GetHttpVars("id");
  $abstract = (GetHttpVars("abstract",'N') == "Y");

  $action->lay->Set("SEP",false);
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc = new_Doc($dbaccess, $docid);

  //  if ($doc->doctype=="C") return; // not for familly


  $kdiv=1; // only one division

  $action->lay->Set("id", $docid);

  include_once("FDL/popup_util.php");



  // -------------------- Menu menu ------------------
  $lmenu = $doc->GetMenuAttributes();
  $tmenu = array();
  $km=0;

  foreach($lmenu as $k=>$v) {
    
    $confirm=false;
    $control=false;
    if (($v->getOption("onlyglobal")=="yes") && ($doc->doctype!="C")) continue;
    if (($v->getOption("global")!="yes") && ($doc->doctype=="C")) continue;
    if ($v->link[0] == '?') { 
      $v->link=substr($v->link,1);
      $confirm=true;
    }
    if ($v->getOption("lconfirm")=="yes") $confirm=true;
    if ($v->link[0] == 'C') { 
      $v->link=substr($v->link,1);
      $control=true;
    }
    if ($v->getOption("lcontrol")=="yes") $control=true;
    if (preg_match('/\[(.*)\](.*)/', $v->link, $reg)) {      
      $v->link=$reg[2];
      $tlink[$k]["target"] = $reg[1];
    } else {
      $tlink[$k]["target"] = $v->id;
    } 
    if ($v->getOption("ltarget")!="") $tlink[$k]["target"] = $v->getOption("ltarget");
    $tlink[$k]["idlink"] = $v->id;
    $tlink[$k]["descr"] = $v->getLabel();
    $tlink[$k]["url"] = addslashes($doc->urlWhatEncode($v->link));
    $tlink[$k]["confirm"]=$confirm?"true":"false";
    $tlink[$k]["control"]=$control;
    $tlink[$k]["tconfirm"]=sprintf(_("Sure %s ?"),addslashes($v->getLabel()));
    $tlink[$k]["visibility"]=MENU_ACTIVE;
    $tlink[$k]["barmenu"] = ($v->getOption("barmenu")=="yes")?"true":"false";
    if ($v->precond != "") $tlink[$k]["visibility"]=$doc->ApplyMethod($v->precond,MENU_ACTIVE);
    
    $tmenu[$km++] = $v->id;
    popupAddItem('popupcard',  $v->id); 
  }

  // -------------------- Menu action ------------------
  $lactions=$doc->GetActionAttributes();
  foreach($lactions as $k=>$v) {
    if ($v->getOption("submenu")!="") {
      $confirm=false;
      $control=false;
      $v->link=$v->getLink($doc->id);
      if ($v->getOption("lconfirm")=="yes") $confirm=true;
      if ($v->getOption("lcontrol")=="yes") $control=true;

      
      if (preg_match('/\[(.*)\](.*)/', $v->link, $reg)) {      
	$v->link=$reg[2];
	$tlink[$k]["target"] = $reg[1];
      } else {
	$tlink[$k]["target"] = $v->id;
      }
      $tlink[$k]["barmenu"] = ($v->getOption("barmenu")=="yes")?"true":"false";
      $tlink[$k]["idlink"] = $v->id;
      $tlink[$k]["descr"] = $v->getLabel();
      $tlink[$k]["url"] = addslashes($doc->urlWhatEncode($v->link));
      $tlink[$k]["confirm"]=$confirm?"true":"false";
      $tlink[$k]["control"]=$control;
      $tlink[$k]["tconfirm"]=sprintf(_("Sure %s ?"),addslashes($v->getLabel()));
      $tlink[$k]["visibility"]=MENU_ACTIVE;
       if ($v->precond != "") $tlink[$k]["visibility"]=$doc->ApplyMethod($v->precond,MENU_ACTIVE);
      
      $tmenu[$km++] = $v->id;
      popupAddItem('popupcard',  $v->id); 
    }
  }



  if (count($tmenu) ==  0) return;
  // ---------------------------
  // definition of popup menu

  // ---------------------------
  // definition of sub popup menu);  
  $lmenu=array_merge($lmenu,$lactions);

  foreach($lmenu as $k=>$v) {   
    $sm=$v->getOption("submenu");
    if ($sm != "") {
      $smid=base64_encode($sm);
      $tsubmenu[$smid]=array("idmenu"=>$smid,
			   "labelmenu"=>$sm);
      popupSubMenu('popupcard',$v->id,$smid);
    }
  }


  while(list($k,$v) = each($tmenu)) {
    if ($tlink[$v]["visibility"]==MENU_INVISIBLE) {
      Popupinvisible('popupcard',$kdiv,$v);
    } else {
      if ($tlink[$v]["url"] != "") {
	if ($tlink[$v]["visibility"]==MENU_INACTIVE) {
	  if ($tlink[$v]["control"])  PopupCtrlInactive('popupcard',$kdiv,$v);     
	  else  PopupInactive('popupcard',$kdiv,$v);
	} else {
	  if ($tlink[$v]["control"])  PopupCtrlActive('popupcard',$kdiv,$v);     
	  else  Popupactive('popupcard',$kdiv,$v);
	}
	
      } else PopupInactive('popupcard',$kdiv,$v);
    }
  }

  
  $noctrlkey=($action->getParam("FDL_CTRLKEY","yes")=="no");
  if ($noctrlkey) popupNoCtrlKey();            

  $action->lay->SetBlockData("ADDLINK",$tlink);
  $action->lay->Set("SEP",true);// to see separator
}
?>