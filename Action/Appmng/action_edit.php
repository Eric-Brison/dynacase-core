<?php
// ---------------------------------------------------------------
// $Id: action_edit.php,v 1.1 2002/01/08 12:41:33 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Appmng/action_edit.php,v $
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
// $Log: action_edit.php,v $
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
function action_edit(&$action) {
// -----------------------------------


  // Get all the params      
  global $HTTP_POST_VARS;
  $id=GetHttpVars("id");
  $appl_id=$action->Read("action_appl_id");

  if ($id == "") {
    $action->lay->Set("name","");
    $action->lay->Set("short_name","");
    $action->lay->Set("long_name","");
    $action->lay->Set("acl","");
    $action->lay->Set("root","");
    $action->lay->Set("toc","");
    $action->lay->Set("id","");
    $action->lay->Set("TITRE",$action->text("titlecreateaction"));
    $action->lay->Set("BUTTONTYPE",$action->text("butcreate"));
    if ($action->HasPermission("ADMIN")) {
      $seldom=1;
    } else if ($action->HasPermission("DOMAIN_MASTER")) {
      $seldom=$action->ActionCour->iddomain;
    } else {
      $action->info("Not Allowed Access Attempt");
    }
  } else {
    $ActionCour = new Action($action->GetParam("CORE_USERDB"),$id);
    $action->lay->Set("id",$id);
    $action->lay->Set("name",$ActionCour->name);
    $action->lay->Set("short_name",$ActionCour->short_name);
    $action->lay->Set("long_name",$ActionCour->long_name);
    $action->lay->Set("acl",$ActionCour->acl);
    $action->lay->Set("root",$ActionCour->root);
    $action->lay->Set("toc",$ActionCour->toc);
    $action->lay->Set("TITRE",$action->text("titlemodifyaction"));
    $action->lay->Set("BUTTONTYPE",$action->text("butmodify"));
  }
  $tab = array();
  if ($ActionCour->root=='Y') {
    $tab[0]["selected"] = "selected";
    $tab[1]["selected"] = "";
  } else {
    $tab[0]["selected"] = "";
    $tab[1]["selected"] = "selected";
  }
  $tab[0]["root"] = "Y";
  $tab[1]["root"] = "N";

  $action->lay->SetBlockData("SELECTROOT", $tab);

  unset($tab);
  $tab = array();
  if ($ActionCour->available=='Y') {
    $tab[0]["selected"] = "selected";
    $tab[1]["selected"] = "";
  } else {
    $tab[0]["selected"] = "";
    $tab[1]["selected"] = "selected";
  }
  $tab[0]["available"] = "Y";
  $tab[1]["available"] = "N";

  $action->lay->SetBlockData("SELECTAVAILABLE", $tab);


  unset($tab);
  $tab = array();
  if ($ActionCour->toc=='Y') {
    $tab[0]["selected"] = "selected";
    $tab[1]["selected"] = "";
  } else {
    $tab[0]["selected"] = "";
    $tab[1]["selected"] = "selected";
  }
  $tab[0]["toc"] = "Y";
  $tab[1]["toc"] = "N";

  $action->lay->SetBlockData("SELECTTOC", $tab);


  $form = new SubForm("edit");
  $form->SetParam("name");
  $form->SetParam("short_name");
  $form->SetParam("long_name");
  $form->SetParam("acl");
  $form->SetParam("root","","sel");
  $form->SetParam("toc","","sel");
  $form->SetParam("available","","sel");
  $form->SetParam("id");
  $action->parent->AddJsCode($form->GetSubJs());
  $control=$action->GetLayoutFile("action_control.js");
  $lay = new Layout($control);
  $action->parent->AddJsCode($lay->gen());

}
?>
