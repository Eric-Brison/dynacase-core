<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: param_list.php,v 1.7 2005/04/14 09:53:50 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage APPMNG
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: param_list.php,v 1.7 2005/04/14 09:53:50 marc Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Zone/Appmng/param_list.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2000
// O*O  Anakeen development team
//  O   dev@anakeen.com
// ---------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or (at
//  your option) any later version.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
// or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
// for more details.
//
// You should have received a copy of the GNU General Public License along
// with this program; if not, write to the Free Software Foundation, Inc.,
// 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
// ---------------------------------------------------------------
// $Log: param_list.php,v $
// Revision 1.7  2005/04/14 09:53:50  marc
// Show/Hide parameters by application
//
// Revision 1.6  2003/08/18 15:46:42  eric
// phpdoc
//
// Revision 1.5  2002/08/26 13:04:58  eric
// application multi-machine
//
// Revision 1.4  2002/05/28 09:00:26  eric
// mise au point paramètres & style
//
// Revision 1.3  2002/05/27 14:51:30  eric
// ajout gestion des styles
//
// Revision 1.2  2002/05/24 09:23:07  eric
// changement structure table paramv
//
// Revision 1.1  2002/05/23 16:14:40  eric
// paramètres utilisateur
//
// Revision 1.2  2002/03/21 17:52:38  eric
// prise en compte application répartie sur plusieurs machines
//
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.3  2001/09/10 16:46:49  eric
// modif pour libwhat 4.8 : accessibilté objet
//
// Revision 1.2  2001/02/26 16:57:14  yannick
// remove tablelayout bug
//
// Revision 1.1  2001/01/29 15:50:59  marianne
// prise en compte de la gestion des parametres
//
// ---------------------------------------------------------------
include_once("Class.TableLayout.php");
include_once("Class.QueryDb.php");
include_once("Class.Param.php");
include_once("Class.SubForm.php");
// -----------------------------------
function param_list(&$action) {
  // -----------------------------------

    // Get Param

  $userid=GetHttpVars("userid");
  $styleid=GetHttpVars("styleid");
  $pview=GetHttpVars("pview"); // set to "all" or "single" if user parameters


    // Set the globals elements
      $baseurl=$action->GetParam("CORE_BASEURL");
  $standurl=$action->GetParam("CORE_STANDURL");

  
  
  
  
  
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/AnchorPosition.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/PopupWindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/ColorPicker2.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/OptionPicker.js");
  
  
  $tincparam=array();
  $appinc=array();
  



    switch ($pview) {
    case "allapp":
      $tparam = $action->parent->param->GetApps();
      break;
    case "alluser":
      if ($userid == "") $tparam = array();
      else $tparam = $action->parent->param->GetUser($userid);
      uasort($tparam,"cmpappid");
      break;
    case "singleuser":
      if ($userid == "") $tparam = array();
      else $tparam = $action->parent->param->GetUser($userid,$action->getParam("STYLE"));
      uasort($tparam,"cmpappid");
      break;
    case "allstyle":
      if ($styleid == "") $tparam = array();
      else $tparam = $action->parent->param->GetStyle($styleid);
      break;
    }
    
    
    
    
      $vsection="appid";
    
    $precApp=0;
  $tincparam=array();
  $applist = "";
    while (list($k,$v)= each ($tparam)) {
      if (isset($v[$vsection])) {
	if ($v[$vsection] != $precApp) {
	  
	  $action->lay->SetBlockData("PARAM$precApp",$tincparam);
	  $tincparam=array();
	  $precApp = $v[$vsection];
	  
	  $app1=new Application($action->dbaccess,$precApp);


	  $appinc[$precApp]["appname"]=$app1->name;
          $applist .= ($applist==""?"":",");
          $applist .= "'".$app1->name."'";
	  $appinc[$precApp]["appdesc"]=$action->text($app1->short_name);
	  $appinc[$precApp]["PARAM"]="PARAM$precApp";
          
	}
	$tincparam[$k]=$v;
	// to show difference between global, user and application parameters
	  if ($v["type"][0] == PARAM_APP) $tincparam[$k]["classtype"]="aparam";
	  else if ($v["type"][0] == PARAM_USER) $tincparam[$k]["classtype"]="uparam";
	  else if ($v["type"][0] == PARAM_STYLE) $tincparam[$k]["classtype"]="sparam";
	  else $tincparam[$k]["classtype"]="gparam";
	$tincparam[$k]["sval"]=addslashes($v["val"]);
	
	// force type user if user mode
	  if ($userid > 0) $tincparam[$k]["type"]=PARAM_USER.$userid;
	else if ($styleid != "") $tincparam[$k]["type"]=PARAM_STYLE.$styleid;

	if ($tincparam[$k]["descr"]=="") $tincparam[$k]["descr"]=$tincparam[$k]["name"];
	else $tincparam[$k]["descr"]=_($tincparam[$k]["descr"]);
	$tincparam[$k]["tooltip"] = $tincparam[$k]["name"] . " : " .$tincparam[$k]["descr"];

      }
    }
    
    $action->lay->SetBlockData("PARAM$precApp",$tincparam);
    if ($pview == "singleuser") { // chg action because of acl USER/ADMIN
      $action->lay->Set("ACTIONDEL","PARAM_UDELETE");     
      $action->lay->Set("ACTIONMOD","PARAM_UMOD");     
    } else {
      $action->lay->Set("ACTIONDEL","PARAM_DELETE");    
      $action->lay->Set("ACTIONMOD","PARAM_MOD");     
    }

  

  uasort($appinc,"cmpappname");
  $action->lay->set("AppList",$applist);
  $action->lay->SetBlockData("APPLI",$appinc);
  
  
}
function cmpappid($a, $b) {
  if ($a["appid"] == $b["appid"]) return 0;
  if ($a["appid"] > $b["appid"]) return 1;
  return -1;
  
}

function cmpappname($a, $b) {
  if ($a["appname"] == $b["appname"]) return 0;
  if ($a["appname"] > $b["appname"]) return 1;
  return -1;
  
}
?>
