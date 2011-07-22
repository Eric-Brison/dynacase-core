<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: user_umod.php,v 1.2 2003/08/18 15:46:41 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage USERS
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: user_umod.php,v 1.2 2003/08/18 15:46:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Users/user_umod.php,v $
// ---------------------------------------------------------------


include_once("Class.SubForm.php");
include_once("Class.User.php");

// -----------------------------------
function user_umod(&$action) {
  // -----------------------------------

  // Get all the params      
  $id=$action->user->id; // himself
  $user=$action->user;

  if ($id == 0) $action->exitError(_("the user identification is unknow"));
  

  $papp = GetHttpVars("papp","APPMNG");
  $paction = GetHttpVars("paction","PARAM_CUACCOUNT");
  $pargs = GetHttpVars("pargs","");

  $user->firstname=GetHttpVars("firstname");
  $user->lastname=GetHttpVars("lastname");

 

  if (GetHttpVars("passwd")!="") {
    $user->password_new=GetHttpVars("passwd");
    $user->expires=0; // means recompute expire date if needed
  }
  $user->Modify();
  
    
  
  


  redirect($action,$papp,$paction);
  
}
?>
