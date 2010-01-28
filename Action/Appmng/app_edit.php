<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: app_edit.php,v 1.6 2005/07/08 15:29:51 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage APPMNG
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: app_edit.php,v 1.6 2005/07/08 15:29:51 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Appmng/app_edit.php,v $
// ---------------------------------------------------------------
// $Log: app_edit.php,v $
// Revision 1.6  2005/07/08 15:29:51  eric
// suppress CORE_USERDB
//
// Revision 1.5  2004/03/22 15:21:40  eric
// change HTTP variable name to put register_globals = Off
//
// Revision 1.4  2003/08/18 15:46:41  eric
// phpdoc
//
// Revision 1.3  2002/08/26 13:04:58  eric
// application multi-machine
//
// Revision 1.2  2002/02/04 14:44:36  eric
// https
//
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.1  2001/01/18 11:57:19  marianne
// Ajout modification appli
//
//
// ---------------------------------------------------------------
include_once("Class.SubForm.php");
include_once("Class.Application.php");

// -----------------------------------
function app_edit(&$action) {
// -----------------------------------


  // Get all the params      
  global $_POST;
  $id=GetHttpVars("id");

  if ($id == "") {
    $action->lay->Set("name","");
    $action->lay->Set("short_name","");
    $action->lay->Set("description","");
    $action->lay->Set("passwd","");
    $action->lay->Set("id","");
    $action->lay->Set("TITRE",$action->text("titlecreate"));
    $action->lay->Set("BUTTONTYPE",$action->text("butcreate"));
    if ($action->HasPermission("ADMIN")) {
      $seldom=1;
    } else if ($action->HasPermission("DOMAIN_MASTER")) {
      $seldom=$action->AppCour->iddomain;
    } else {
      $action->info("Not Allowed Access Attempt");
    }
  } else {
    $AppCour = new Application($action->GetParam("CORE_DB"),$id);
    $action->lay->Set("id",$id);
    $action->lay->Set("name",$AppCour->name);
    $action->lay->Set("machine",$AppCour->machine);
    $action->lay->Set("short_name",$AppCour->short_name);
    $action->lay->Set("description",$AppCour->description);
    $action->lay->Set("passwd","");
    $action->lay->Set("TITRE",$action->text("titlemodify"));
    $action->lay->Set("BUTTONTYPE",$action->text("butmodify"));
  }
  $tab = array();
  if ($AppCour->access_free=='Y') {
    $tab[0]["selected"] = "selected";
    $tab[1]["selected"] = "";
  } else {
    $tab[0]["selected"] = "";
    $tab[1]["selected"] = "selected";
  }
  $tab[0]["access_free"] = "Y";
  $tab[1]["access_free"] = "N";

  $action->lay->SetBlockData("SELECTACCESS", $tab);
  unset($tab);

  $tab = array();
  if ($AppCour->ssl=='Y') {
    $tab[0]["selected"] = "selected";
    $tab[1]["selected"] = "";
  } else {
    $tab[0]["selected"] = "";
    $tab[1]["selected"] = "selected";
  }
  $tab[0]["ssl"] = "Y";
  $tab[1]["ssl"] = "N";

  $action->lay->SetBlockData("SELECTSSL", $tab);

  unset($tab);
  $tab = array();
  if ($AppCour->available=='Y') {
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
  if ($AppCour->displayable=='Y') {
    $tab[0]["selected"] = "selected";
    $tab[1]["selected"] = "";
  } else {
    $tab[0]["selected"] = "";
    $tab[1]["selected"] = "selected";
  }
  $tab[0]["displayable"] = "Y";
  $tab[1]["displayable"] = "N";

  $action->lay->SetBlockData("SELECTDISPLAYABLE", $tab);


  unset($tab);
  $tab = array();
  if ($AppCour->displayable=='Y') {
    $tab[0]["selected"] = "selected";
    $tab[1]["selected"] = "";
  } else {
    $tab[0]["selected"] = "";
    $tab[1]["selected"] = "selected";
  }
  $tab[0]["displayable"] = "Y";
  $tab[1]["displayable"] = "N";

  $action->lay->SetBlockData("SELECTDISPLAYABLE", $tab);


  $form = new SubForm("edit");
  $form->SetParam("name");
  $form->SetParam("short_name");
  $form->SetParam("description");
  $form->SetParam("available","","sel");
  $form->SetParam("displayable","","sel");
  $form->SetParam("access_free","","sel");
  $form->SetParam("ssl","","sel");
  $form->SetParam("machine");
  $form->SetParam("id");
  $action->parent->AddJsCode($form->GetSubJs());
  $control=$action->GetLayoutFile("app_control.js");
  $lay = new Layout($control);
  $action->parent->AddJsCode($lay->gen());

}
?>
