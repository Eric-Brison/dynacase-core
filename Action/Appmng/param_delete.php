<?php
// ---------------------------------------------------------------
// $Id: param_delete.php,v 1.2 2002/05/23 16:14:40 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Appmng/param_delete.php,v $
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

include_once("Class.Param.php");

// -----------------------------------
function param_delete(&$action) {
// -----------------------------------

  $name=GetHttpVars("id");
  $vtype=GetHttpVars("vtype");
  $atype=GetHttpVars("atype",PARAM_APP);


  $parametre = new Param($action->dbaccess,array($name,$atype,$vtype));
  if ($parametre->isAffected()) {
    $action->log->info(_("Remove parameter").$parametre->name);
    $parametre->Delete();
  } else $action->addLogMsg(sprintf(_("the '%s' parameter cannot be removed"),$name));
  redirect($action,"APPMNG",$action->Read("PARAM_ACT","PARAM_ALIST"));
}

// -----------------------------------
function param_udelete(&$action) {
// -----------------------------------

 
  $atype=GetHttpVars("atype",PARAM_APP);
  $vtype=GetHttpVars("vtype");
  if ($atype != PARAM_USER) $action->exitError(_("only user parameters can be deleted with its action"));
  if ($vtype != $action->user->id) $action->exitError(_("only current user parameters can be deleted with its action"));


  param_delete(&$action);
}

?>
