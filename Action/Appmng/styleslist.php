<?php
// ---------------------------------------------------------------
// $Id: styleslist.php,v 1.1 2002/01/08 12:41:33 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Appmng/Attic/styleslist.php,v $
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
// $Log: styleslist.php,v $
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.2  2001/10/11 13:59:07  eric
// mise à jour pour libwhat 0.4.8
//
// Revision 1.1  2001/02/06 11:40:11  marianne
// Prise en compte des styles, parametres et actions
//
//
// ---------------------------------------------------------------
include_once("Class.TableLayout.php");
include_once("Class.QueryDb.php");
include_once("Class.SubForm.php");

// -----------------------------------
function styleslist(&$action) {
// -----------------------------------

    // Set the globals elements

  $baseurl=$action->GetParam("CORE_BASEURL");
  $standurl=$action->GetParam("CORE_STANDURL");

  $err = $action->Read("USERS_ERROR");
  if ($err != "") {
    $action->lay->Set("ERR_MSG",$err);
    $action->Unregister("USERS_ERROR");
  } else {
    $action->lay->Set("ERR_MSG","");
  }


  // Set the form element
  $form = new SubForm("edit",350,330,$standurl."app=APPMNG&action=STYLES_MOD",
                                     $standurl."app=APPMNG&action=STYLES_EDIT");
  $form->SetParam("id","-1");
  $form->SetParam("name");
  $form->SetParam("description");

  $form->SetKey("id");

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsCode($form->GetMainJs());
  $action->lay->set("MAINFORM",$form->GetMainForm());

  if ($action->HasPermission("ADMIN")) {
    $add_icon = new Layout($action->GetLayoutFile("add_icon_styles.xml"),$action);
    $add_icon->set("JSCALL",$form->GetEmptyJsMainCall());
    $action->lay->set("ADD_ICON",$add_icon->gen());
  } else {
    $action->lay->set("ADD_ICON","");
  }


  // Set the table element
  $tablelay= new TableLayout($action->lay);

  $query = new QueryDb("","Style");
  $tablelay->start=GetHttpVars("start");
  $tablelay->slice=15;
  $tablelay->array = $query->Query($tablelay->start,$tablelay->slice,"TABLE");
  
  // Affect the modif icons
  if (is_array($tablelay->array)) {
    reset ($tablelay->array);
    while(list($k,$v) = each($tablelay->array)) {
      $tablelay->array[$k]["update"] = "";
      $tablelay->array[$k]["edit"] = "";
      $tablelay->array[$k]["delete"] = "";
    }
  }

  $tablelay->fields= array("id", "update","edit","delete","name","description");

  $tablelay->prev="<img border=0 src=\"".$action->GetImageUrl("prev.png")."\">";
  $tablelay->next="<img  border=0 src=\"".$action->GetImageUrl("next.png")."\">";
  
  $template = $action->GetLayoutFile("tableapp.xml");


  $action->lay->Set("TABLE", $tablelay->Set());
  $action->lay->Set("IMGHELP", $action->GetImageUrl("help.gif"));
  $action->lay->Set("IMGPRINT", $action->GetImageUrl("print.gif"));
  $action->lay->Set("IMGEDIT", $action->GetImageUrl("edit.gif"));
  $action->lay->Set("IMGSEARCH", $action->GetImageUrl("search.gif"));
  $action->lay->Set("STYLELIST",$action->text("titleaction"));

}
?>
