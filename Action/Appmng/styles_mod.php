<?php
// ---------------------------------------------------------------
// $Id: styles_mod.php,v 1.1 2002/01/08 12:41:33 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Appmng/Attic/styles_mod.php,v $
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
// $Log: styles_mod.php,v $
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.1  2001/02/06 11:40:11  marianne
// Prise en compte des styles, parametres et actions
//
//
// ---------------------------------------------------------------
include_once("Class.SubForm.php");
include_once("Class.Style.php");

// -----------------------------------
function styles_mod(&$action) {
// -----------------------------------
  // Get all the params      
  $style_id=GetHttpVars("id");
  if ($style_id == "") {
    $StyleCour = new Style($action->GetParam("CORE_USERDB"));
  } else {
    $StyleCour = new Style($action->GetParam("CORE_USERDB"),$style_id);
  }
  $StyleCour->key=$style_id;
  $StyleCour->name=GetHttpVars("name");
  $StyleCour->description=GetHttpVars("description");
  if ($style_id == "") {
    $res=$StyleCour->Add();
    if ($res != "") { 
      $txt = $action->text("err_add_style")." : $res";
      $action->Register("err_add_style",AddSlashes($txt));
    }
  } else {
    $res=$StyleCour->Modify();
    if ($res != "") { 
      $txt = $action->text("err_mod_style")." : $res";
      $action->Register("err_add_style",AddSlashes($txt));
    }
  }
  redirect($action,"APPMNG","STYLESLIST");
}
?>
