<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: app_mod.php,v 1.5 2005/07/08 15:29:51 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage APPMNG
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: app_mod.php,v 1.5 2005/07/08 15:29:51 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Appmng/app_mod.php,v $
// ---------------------------------------------------------------
// $Log: app_mod.php,v $
// Revision 1.5  2005/07/08 15:29:51  eric
// suppress CORE_USERDB
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
// Revision 1.2  2001/01/23 00:10:35  marianne
// maj variables err_mod/err_add_application
//
// Revision 1.1  2001/01/18 11:57:23  marianne
// Ajout modification appli
//
//
// ---------------------------------------------------------------
include_once("Class.SubForm.php");
include_once("Class.Application.php");

// -----------------------------------
function app_mod(&$action) {
// -----------------------------------
  // Get all the params      
  $id=GetHttpVars("id");

  if ($id == "") {
    $AppCour = new Application($action->GetParam("CORE_DB"));
  } else {
    $AppCour = new Application($action->GetParam("CORE_DB"),$id);
  }
  $AppCour->name=GetHttpVars("name");
  $AppCour->short_name=GetHttpVars("short_name");
  $AppCour->description=GetHttpVars("description");
  $AppCour->displayable=GetHttpVars("displayable");
  $AppCour->available=GetHttpVars("available");
  $AppCour->access_free=GetHttpVars("access_free");
  $AppCour->machine=GetHttpVars("machine");
  $AppCour->ssl=GetHttpVars("ssl");

  if ($id == "") {
    $res=$AppCour->Add();
    if ($res != "") { 
      $txt = $action->text("err_add_application")." : $res";
      $action->Register("USERS_ERROR",AddSlashes($txt));
    }
  } else {
    $res=$AppCour->Modify();
    if ($res != "") { 
      $txt = $action->text("err_mod_application")." : $res";
      $action->Register("USERS_ERROR",AddSlashes($txt));
    }
  }
  redirect($action,"APPMNG","");
}
?>
