<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: actionlist.php,v 1.5 2003/08/18 15:46:41 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage APPMNG
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: actionlist.php,v 1.5 2003/08/18 15:46:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Appmng/actionlist.php,v $
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
// $Log: actionlist.php,v $
// Revision 1.5  2003/08/18 15:46:41  eric
// phpdoc
//
// Revision 1.4  2002/08/26 13:04:58  eric
// application multi-machine
//
// Revision 1.3  2002/03/21 17:52:37  eric
// prise en compte application répartie sur plusieurs machines
//
// Revision 1.2  2002/01/30 13:44:54  eric
// i18n & remise en marche modif action
//
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.3  2001/09/10 16:46:49  eric
// modif pour libwhat 4.8 : accessibilté objet
//
// Revision 1.2  2001/02/26 16:57:13  yannick
// remove tablelayout bug
//
// Revision 1.1  2001/02/06 11:40:52  marianne
// Prise en compte des styles, parametres et actions
//
//
// ---------------------------------------------------------------
include_once("Class.TableLayout.php");
include_once("Class.QueryDb.php");
include_once("Class.QueryGen.php");
include_once("Class.Action.php");
include_once("Class.SubForm.php");
// -----------------------------------
function actionlist(&$action) {
// -----------------------------------

    // Set the globals elements
  $baseurl=$action->GetParam("CORE_BASEURL");
  $standurl=$action->GetParam("CORE_STANDURL");

  $action->lay->set("ACTION_CHG","ACTION_APPL_CHG");

  $err = $action->Read("err_add_parameter");
  if ($err != "") {
    $action->lay->Set("ERR_MSG",$err);
    $action->Unregister("err_add_parameter");
  } else {
    $action->lay->Set("ERR_MSG","");
  }

  // select the first user if not set
  $appl_id=$action->Read("action_appl_id");
  $action->log->debug("appl_id : $appl_id");
  if ($appl_id == "") $appl_id=0;

  // affect the select form elements
  $query = new QueryDb("","Application");
  $query-> AddQuery("(objectclass != 'Y' ) OR ( objectclass isnull)");
  $query->order_by = "name";
  $applist = $query->Query();
  unset($query);
  $tab=array();
  $appl_sel="";
  $i=0;
  reset($applist);
  while(list($k,$v)=each($applist)) {

      if ($appl_id == 0) {
	$appl_id=$v->id;
	$action->Register("action_appl_id",$appl_id);
      }
      $tab[$i]["text"]=$v->name;
      $tab[$i]["id"]=$v->id;
      if ($appl_id == $v->id) {
	$appl_sel=$v;
	$tab[$i]["selected"]="selected";
      } else {
	$tab[$i]["selected"]="";
      }
      $i++;
    
  }

  $action->lay->SetBlockData("SELAPPLI",$tab);
  $action->parent->AddJsRef("change_acl.js");


  // Set the form element
  $form = new SubForm("edit",350,330,$standurl."app=APPMNG&action=ACTION_MOD",
                                     $standurl."app=APPMNG&action=ACTION_EDIT");
  $form->SetParam("id","-1");
  $form->SetParam("creation","");
  $form->SetParam("acl","");
  $form->SetParam("toc","");
  $form->SetParam("available","");
  $form->SetParam("root","");
  $form->SetParam("name","");
  $form->SetParam("short_name","");
  $form->SetParam("long_name","");
  $form->SetParam("script","");

  $form->SetKey("id");

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsCode($form->GetMainJs());
  $action->lay->set("MAINFORM",$form->GetMainForm());


  




  // Set the table element
  

  $query = new QueryGen("","Action",$action);
  
  $query->AddQuery("id_application=$appl_id");
  $query->slice=20;
  $query->order_by = "name";
  
  $query->table->headsortfields = array ( "name" => "name");
  $query->table->headcontent = array ("name" => $action->text("name"));
   $query->Query();
  
  // Affect the modif icons

  $jsscript=$form->GetLinkJsMainCall();
  while(list($k,$v) = each($query->table->array)) {
    $query->table->array[$k]["imgedit"] = "<img border=0 src=\"".$action->GetImageUrl("edit.gif")."\" alt=\"".$action->text("edit")."\">";
    $query->table->array[$k]["short_name"] = $action->text($query->table->array[$k]["short_name"]);

  }
    

  $query->table->fields= array("id","name","imgedit","short_name","script","layout","available","acl","root","toc","toc_order");




  $action->lay->Set("TABLE", $query->table->Set());




}
?>
