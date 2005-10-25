<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: ngheader.php,v 1.2 2005/10/25 08:39:35 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: ngheader.php,v 1.2 2005/10/25 08:39:35 marc Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Core/Attic/ngheader.php,v $
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

function ngheader(&$action) {

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/AnchorPosition.js");
 
  
  $list = array( "WGCAL", "USERCARD", "FREEDOM" ); 

  $i=0;
  foreach($list as $k => $appname) {
    $appli = new Application();
    $appli->Set($appname, $action->parent);
    if ($appli->access_free == "N") {
      $action->log->debug("Access not free for :".$appli->name);
      if (isset($action->user)) {
	if ($action->user->id != 1) { // no control for user Admin
	  // search  acl for root action
	  $queryact=new QueryDb($action->dbaccess,"Action");
	  $queryact->AddQuery("id_application=".$appli->id);
	  $queryact->AddQuery("root='Y'");
	  $listact = $queryact->Query(0,0,"TABLE");
	  $root_acl_name=$listact[0]["acl"];
	} 
      } else { 
	continue; 
      }
    }
    $tappli["name"]= $appli->name;
    $tappli["description"]= $action->text($appli->description);
    $tappli["descriptionsla"]= addslashes($appli->description);
    if ($appli->machine!="") $tappli["pubdir"]= "http://".$appli->machine."/what";
    else $tappli["pubdir"]=$action->getParam("CORE_PUBURL");

    $tappli["iconsrc"]=$action->GetImageUrl($appli->icon);
    if ($tappli["iconsrc"]=="CORE/Images/noimage.png") $tappli["iconsrc"]=$appli->name."/Images/".$appli->icon;
    
    $tab[$i++]=$tappli;
  }
  $action->lay->set("DATE",strftime("%a %d %B %Y  %H:%M",time()));
  $action->lay->SetBlockData("FUNCTION",$tab);
  
  
  // update application name
  global $_GET;
  $app = new Application();
  
  if (isset($_GET["capp"]))
    $app->Set($_GET["capp"], $action->parent);
  else
    $app->Set($_GET["app"], $action->parent);
  
  $action->lay->set("APP_TITLE", _($app->description));
  $action->lay->set("SESSION",$action->session->id);
  
  $action->lay->set("sid",session_id());
  
  $jslauch=$action->GetLayoutFile("lauch_action.js");
  $lay = new Layout($jslauch, $action);
  $action->parent->AddJsCode($lay->gen());
  
  

  if ($action->GetParam("CORE_USECACHE") == "yes") $action->lay->set("dcache","inline");
  else $action->lay->set("dcache","none");
  $action->lay->set("USER",ucwords(strtolower($action->user->firstname." ".$action->user->lastname)));

}


function userlogin(&$action) {

// This function is used to show curent user if set
// TODO

  if ((!isset($action->user)) || ($action->user->id == 0) || ($action->user->id == ANONYMOUS_ID)) {
    $action->lay->set("USER",$action->user->firstname." ".$action->user->lastname);
  }
}

?>
