<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: user_umod.php,v 1.2 2003/08/18 15:46:41 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage USERS
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: user_umod.php,v 1.2 2003/08/18 15:46:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Users/user_umod.php,v $
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
