<?php
// ---------------------------------------------------------------
// $Id: styles_edit.php,v 1.1 2002/01/08 12:41:33 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Appmng/Attic/styles_edit.php,v $
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
// $Log: styles_edit.php,v $
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
function styles_edit(&$action) {
// -----------------------------------


  // Get all the params      
  global $HTTP_POST_VARS;
  $id=GetHttpVars("id");

  if ($id == "") {
    $StyleCour = new Style($action->GetParam("CORE_USERDB"));
    $action->lay->Set("name","");
    $action->lay->Set("description","");
    $action->lay->Set("id","");
    $action->lay->Set("TITRE",$action->text("titlestylecreate"));
    $action->lay->Set("BUTTONTYPE",$action->text("butcreate"));
    if ($action->HasPermission("ADMIN")) {
      $seldom=1;
    } else if ($action->HasPermission("DOMAIN_MASTER")) {
      $seldom=$action->StyleCour->iddomain;
    } else {
      $action->info("Not Allowed Access Attempt");
    }
  } else {
    $StyleCour = new Style($action->GetParam("CORE_USERDB"),$id);
    $action->lay->Set("id",$id);
    $action->lay->Set("name",$StyleCour->name);
    $action->lay->Set("description",$StyleCour->description);
    $action->lay->Set("TITRE",$action->text("titlestylemodify"));
    $action->lay->Set("BUTTONTYPE",$action->text("butmodify"));
  }

  $form = new SubForm("edit");
  $form->SetParam("name");
  $form->SetParam("description");
  $form->SetParam("id");
  $action->parent->AddJsCode($form->GetSubJs());
  $control=$action->GetLayoutFile("style_control.js");
  $lay = new Layout($control);
  $action->parent->AddJsCode($lay->gen());

}
?>
