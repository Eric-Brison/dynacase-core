<?php
// ---------------------------------------------------------------
// $Id: modify.php,v 1.2 2002/03/02 18:06:26 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Access/modify.php,v $
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
// $Log: modify.php,v $
// Revision 1.2  2002/03/02 18:06:26  eric
// correction et optimisation pour droit objet
//
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.2  2001/09/07 16:52:01  eric
// gestion des droits sur les objets
//
// Revision 1.1  2001/08/28 10:12:51  eric
// modification pour la prise en comptes des groupes d'utilisateurs
//
// Revision 1.2  2000/10/23 12:36:04  yannick
// Ajout de l'acces aux applications
//
// Revision 1.1  2000/10/23 09:10:27  marc
// Mise au point des utilisateurs
//
//
//
//
// ---------------------------------------------------------------
include_once("Class.QueryDb.php");
include_once("Class.Application.php");
include_once("Class.Acl.php");
include_once("Class.Permission.php");
include_once("Class.ObjectPermission.php");

// -----------------------------------
function modify(&$action) {
  // -----------------------------------

  // get all parameters
  $userId=GetHttpVars("userid");
  $appId=GetHttpVars("appid");
  $aclp=GetHttpVars("aclup"); // ACL + (more access)
  $acln=GetHttpVars("aclun"); // ACL - (less access)
  $coid=GetHttpVars("oid"); // oid for controlled object
  $returnact=GetHttpVars("returnact");


  //  print "oid=$coid";
  if (($coid == "") || ($coid == "0")) {
    // modif permission for a uncontrolled object
    $p=new Permission($action->dbaccess,array($userId,$appId));
    if (! $p-> IsAffected()) {
      $p->Affect(array("id_user" => $userId,
		       "id_application" => $appId));
      
    }
  } else {
    // test if current user could modify ACL 
      $p=new ObjectPermission($action->dbaccess,array($action->parent->user->id,
						      $coid));
      if (($err = $p-> ControlOid( $appId, "modifyacl")) != "") {
	$action -> ExitError($err);
      }
    

    // modif permission for a particular object
      $p=new ObjectPermission($action->dbaccess,array($userId,$coid));
    }
  
  
  // delete old permissions
  $p-> Delete();

  if (is_array($aclp)) {
    // create new permissions
    while (list($k,$v) = each($aclp)) {
      $p->id_acl=$v;
      $p->Add();
    }
  }

  if (is_array($acln)) {
    // create new permissions
    while (list($k,$v) = each($acln)) {
      $p->id_acl= -$v;
      $p->Add();
    }
  }
   
  if ($returnact == "") exit(0);
  redirect($action,"ACCESS",$returnact);

}
?>
