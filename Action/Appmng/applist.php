<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: applist.php,v 1.7 2004/10/26 06:29:51 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage APPMNG
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: applist.php,v 1.7 2004/10/26 06:29:51 marc Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Appmng/applist.php,v $
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
// $Log: applist.php,v $
// Revision 1.7  2004/10/26 06:29:51  marc
// Add version in application list
//
// Revision 1.6  2003/08/18 15:46:41  eric
// phpdoc
//
// Revision 1.5  2002/08/26 13:04:58  eric
// application multi-machine
//
// Revision 1.4  2002/03/21 17:52:37  eric
// prise en compte application répartie sur plusieurs machines
//
// Revision 1.3  2002/02/04 14:44:36  eric
// https
//
// Revision 1.2  2002/01/30 13:44:01  eric
// i18n
//
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.8  2001/10/11 13:59:07  eric
// mise à jour pour libwhat 0.4.8
//
// Revision 1.7  2001/09/10 16:46:49  eric
// modif pour libwhat 4.8 : accessibilté objet
//
// Revision 1.6  2001/02/06 11:40:11  marianne
// Prise en compte des styles, parametres et actions
//
// Revision 1.5  2001/01/29 15:50:59  marianne
// prise en compte de la gestion des parametres
//
// Revision 1.4  2001/01/18 11:58:48  marianne
// Ajout modification appli et affichage access_free et displayable
//
// Revision 1.3  2000/10/19 16:46:45  marc
// Evo TableLayout
//
// Revision 1.2  2000/10/19 10:55:44  marc
// Suppresion lien HTML dans PHP !!!!!
//
// Revision 1.1.1.1  2000/10/16 08:52:39  yannick
// Importation initiale
//
//
//
// ---------------------------------------------------------------
include_once("Class.TableLayout.php");
include_once("Class.QueryGen.php");
include_once("Class.SubForm.php");
include_once("Class.Param.php");

// -----------------------------------
function applist(&$action) {
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
  $form = new SubForm("edit",350,330,$standurl."app=APPMNG&action=APP_MOD",
                                     $standurl."app=APPMNG&action=APP_EDIT");
  $form->SetParam("id","-1");
  $form->SetParam("name");
  $form->SetParam("short_name");
  $form->SetParam("description");
  $form->SetParam("available");
  $form->SetParam("displayable");
  $form->SetParam("access_free");
  $form->SetParam("ssl");
  $form->SetParam("machine");

  $form->SetKey("id");

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsCode($form->GetMainJs());
  $action->lay->set("MAINFORM",$form->GetMainForm());

  if ($action->HasPermission("ADMIN")) {
    $add_icon = new Layout($action->GetLayoutFile("add_icon.xml"),$action);
    $add_icon->set("JSCALL",$form->GetEmptyJsMainCall());
    $action->lay->set("ADD_ICON",$add_icon->gen());
  } else {
    $action->lay->set("ADD_ICON","");
  }


  // Set the table element
  

  $query = new QueryGen("","Application",$action);
  $query-> AddQuery("(objectclass != 'Y' ) OR ( objectclass isnull)");
  $query->slice=20;
  
   $query->Query();
  
  // Affect the modif icons

  while(list($k,$v) = each($query->table->array)) {
    
      $id = $query->table->array[$k]["id"];
      $p = new Param($action->dbaccess, array("VERSION", PARAM_APP, $id));
      $version = (isset($p->val)?$p->val:"");

      $query->table->array[$k]["update"] = "";
      $query->table->array[$k]["edit"] = "";
      $query->table->array[$k]["delete"] = "";
      $query->table->array[$k]["version"] = $version;
      $query->table->array[$k]["description"] = $action->text($query->table->array[$k]["description"]);
    
  }
    

  $query->table->fields= array("id", "update","edit","delete","name", "version", "description","available","access_free","displayable","ssl","machine");


  




  $action->lay->Set("TABLE", $query->table->Set());
  $action->lay->Set("IMGHELP", $action->GetImageUrl("help.gif"));
  $action->lay->Set("IMGPRINT", $action->GetImageUrl("print.gif"));
  $action->lay->Set("IMGEDIT", $action->GetImageUrl("edit.gif"));
  $action->lay->Set("IMGSEARCH", $action->GetImageUrl("search.gif"));
  $action->lay->Set("APPLIST",$action->text("title"));

}
?>
