<?php
// ---------------------------------------------------------------
// $Id: param_mod.php,v 1.3 2002/05/23 16:14:40 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Appmng/param_mod.php,v $
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
// $Log: param_mod.php,v $
// Revision 1.3  2002/05/23 16:14:40  eric
// paramètres utilisateur
//
// Revision 1.2  2002/04/29 15:32:24  eric
// correction id pour cache multibase
//
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.1  2001/01/29 15:50:59  marianne
// prise en compte de la gestion des parametres
//
//
// ---------------------------------------------------------------
include_once("Class.SubForm.php");
include_once("Class.Param.php");

// -----------------------------------
function param_mod(&$action) {
  // -----------------------------------
    // Get all the params      
      $vtype=GetHttpVars("vtype");
  $name =GetHttpVars("aname");
  $atype=GetHttpVars("atype",PARAM_APP);
  $val  =GetHttpVars("val");
  
  $ParamCour = new Param($action->dbaccess,array($name,$atype,$vtype));
  if (! $ParamCour->isAffected()) {
    $ParamCour->vtype=$vtype;
    $ParamCour->type=$atype;
    $ParamCour->name=$name;
    $ParamCour->val=$val;
    $res=$ParamCour->Add();
    if ($res != "") { 
      $action->addLogMsg( $action->text("err_add_param")." : $res");
    }
  } else {
    $ParamCour->val=$val;
    $res=$ParamCour->Modify();
    if ($res != "") { 
      $action->addLogMsg( $action->text("err_mod_parameter")." : $res");
    }
  }
  redirect($action,"APPMNG",$action->Read("PARAM_ACT","PARAM_ALIST"));
}

// -----------------------------------
function param_umod(&$action) {
// -----------------------------------

 
  $atype=GetHttpVars("atype",PARAM_APP);
  $vtype=GetHttpVars("vtype");
  if ($atype != PARAM_USER) $action->exitError(_("only user parameters can be modified with its action"));
  if ($vtype != $action->user->id) $action->exitError(_("only current user parameters can be modified with its action"));

  param_mod(&$action);
}

?>
