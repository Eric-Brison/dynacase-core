<?php
// ---------------------------------------------------------------
// $Id: head.php,v 1.11 2002/08/26 13:04:58 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Core/head.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2001
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


include_once('Class.QueryDb.php');
include_once('Class.Application.php');

function head(&$action) {

// This function is used to show all available applications
// in the main header
  $query=new QueryDb($action->dbaccess,"Application");
  $query->basic_elem->sup_where=array("available='Y'","displayable='Y'");
  $list = $query->Query(0,0,"TABLE");



  // remove applications that need access perm
  $tab = array();
  if ($query->nb > 0) {
    $i=0;
    while(list($k,$appli)=each($list)) {
      //      if (! $action->AppInstalled($appli["name"])) continue;
      if ($appli["access_free"] == "N") {
        $action->log->debug("Access not free for :".$appli["name"]);
        if (isset($action->user)) {
	  if ($action->user->id != 1) { // no control for user Admin
	    $p = new Permission($action->dbaccess,array($action->user->id, $appli["id"]));
	    //if ($p->id_acl == "") continue;

	    // test if acl of root action is granted
	  
	  
	    // search  acl for root action
	    $queryact=new QueryDb($action->dbaccess,"Action");
	    $queryact->AddQuery("id_application=".$appli["id"]);
	    $queryact->AddQuery("root='Y'");
	    $listact = $queryact->Query();
	    $root_acl_name=$listact[0]->acl;

	    // Get the id acl from acl name
	    $acl=new Acl($action->dbaccess);
	    if ( ! $acl->Set($root_acl_name,$appli["id"])) {
	      $action->log->warning("Acl $root_acl_name not available for App ".$appli["id"]);
	      continue;
	    }

	    if (! $p->HasPrivilege($acl->id)) continue;
	  }
	  
        } else { continue; }
      }
      $appli["description"]= $action->text($appli["description"]); // translate
      $appli["descriptionsla"]= addslashes($appli["description"]); // because its in between '' in layout
      if ($appli["machine"] != "") $appli["pubdir"]= "http://".$appli["machine"]."/what";
      else $appli["pubdir"]=$action->getParam("CORE_PUBURL");
      $tab[$i++]=$appli;
    }
  }

  $action->lay->set("DATE",strftime("%a %d %B %Y  %H:%M",time()));

  //  $action->lay->SetBlockCorresp("FUNCTION","NAME","name");
  //$action->lay->SetBlockCorresp("FUNCTION","IMAGE","icon");
  //$action->lay->SetBlockCorresp("FUNCTION","DESCR","description");

  $action->lay->SetBlockData("FUNCTION",$tab);
  

  // update application name
  global $HTTP_GET_VARS;
  $app = new Application();

  if (isset($HTTP_GET_VARS["capp"]))
    $app->Set($HTTP_GET_VARS["capp"], $action->parent);
  else
    $app->Set($HTTP_GET_VARS["app"], $action->parent);
    
  $action->lay->set("APP_TITLE", _($app->description));
  $action->lay->set("SESSION",$action->session->id);

 $action->lay->set("sid",session_id());

  $jslauch=$action->GetLayoutFile("lauch_action.js");
  $lay = new Layout($jslauch, $action);
  $action->parent->AddJsCode($lay->gen());


  if ($action->GetParam("CORE_USECACHE") == "yes") $action->lay->set("dcache","inline");
  else $action->lay->set("dcache","none");
  // update username
  userlogin($action);

}


function userlogin(&$action) {

// This function is used to show curent user if set
// TODO

  if ((!isset($action->user)) || ($action->user->id == 0) || ($action->user->id == ANONYMOUS_ID)) {
    $action->lay->set("USER","");
    $action->lay->set("ONOUT",$action->parent->GetImageUrl("bblue.gif"));
    $action->lay->set("ONOVER",$action->parent->GetImageUrl("bgreen.gif"));
    $action->lay->set("ALTLOGINOUT","login");
    $action->lay->set("ACTION","");
    $action->lay->set("anim","true");
  } else {
    $action->lay->set("USER",$action->user->firstname." ".$action->user->lastname);
    $action->lay->set("ONOUT",$action->parent->GetImageUrl("bgreen.gif"));
    $action->lay->set("ONOVER",$action->parent->GetImageUrl("bred.gif"));
    $action->lay->set("ALTLOGINOUT","logout");
    $action->lay->set("ACTION","LOGOUT");
    $action->lay->set("OUT","");
    $action->lay->set("anim","false");
  }
}

?>
