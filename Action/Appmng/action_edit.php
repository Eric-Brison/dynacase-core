<?php
/**
 * Edit parameters for actions
 *
 * @author Anakeen 2000 
 * @version $Id: action_edit.php,v 1.4 2005/07/08 15:29:51 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage APPMNG
 */
 /**
 */


include_once("Class.SubForm.php");
include_once("Class.Action.php");

// -----------------------------------
function action_edit(&$action) {
// -----------------------------------


  // Get all the params      
  global $_POST;
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
    $ActionCour = new Action($action->GetParam("CORE_DB"),$id);
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
  
  unset($tab);
  $tab = array();
  if ($ActionCour->openaccess=='Y') {
    $tab[0]["selected"] = "selected";
    $tab[1]["selected"] = "";
  } else {
    $tab[0]["selected"] = "";
    $tab[1]["selected"] = "selected";
  }
  $tab[0]["openaccess"] = "Y";
  $tab[1]["openaccess"] = "N";

  $action->lay->SetBlockData("SELECTOPENACCESS", $tab);
  /*
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
  */

  $action->parent->AddJsRef("APPMNG/Layout/action_control.js");
}
?>
