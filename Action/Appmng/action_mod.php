<?php
// ---------------------------------------------------------------
// $Id: action_mod.php,v 1.1 2002/01/08 12:41:33 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Appmng/action_mod.php,v $
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
// $Log: action_mod.php,v $
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.1  2001/02/06 11:40:52  marianne
// Prise en compte des styles, parametres et actions
//
//
// ---------------------------------------------------------------
include_once("Class.SubForm.php");
include_once("Class.Action.php");

// -----------------------------------
function action_mod(&$action) {
// -----------------------------------
  // Get all the params      
  $id=GetHttpVars("id");
  $appl_id=$action->Read("action_appl_id");

  if ($id == "") {
    $ActionCour = new Action($action->GetParam("CORE_USERDB"));
  } else {
    $ActionCour = new Action($action->GetParam("CORE_USERDB"),array( $id,$appl_id));
  }
  $ActionCour->name=GetHttpVars("name");
  $ActionCour->short_name=GetHttpVars("short_name");
  $ActionCour->long_name=GetHttpVars("long_name");
  $ActionCour->acl=GetHttpVars("acl");
  $ActionCour->root=GetHttpVars("root");
  $ActionCour->toc=GetHttpVars("toc");
  $ActionCour->available=GetHttpVars("available");
  $ActionCour->access_free=GetHttpVars("access_free");

  if ($id == "") {
    $res=$ActionCour->Add();
    if ($res != "") { 
      $txt = $action->text("err_add_action")." : $res";
      $action->Register("USERS_ERROR",AddSlashes($txt));
    }
  } else {
    $res=$ActionCour->Modify();
    if ($res != "") { 
      $txt = $action->text("err_mod_action")." : $res";
      $action->Register("USERS_ERROR",AddSlashes($txt));
    }
  }
  redirect($action,"APPMNG","ACTIONLIST");
}
?>
