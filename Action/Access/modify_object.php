<?php
// ---------------------------------------------------------------
// $Id: modify_object.php,v 1.3 2002/03/02 18:06:26 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Access/modify_object.php,v $
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
// $Log: modify_object.php,v $
// Revision 1.3  2002/03/02 18:06:26  eric
// correction et optimisation pour droit objet
//
// Revision 1.2  2002/02/18 10:55:16  eric
// modif id_fields de objectcontrol : cause pas unique
//
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.2  2001/12/03 13:56:45  eric
// pour modif libwhar 0.4.10
//
// Revision 1.1  2001/11/19 18:03:30  eric
// function edit_object completed
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
function modify_object(&$action) {
  // -----------------------------------

  
  // get all parameters
  $appId=GetHttpVars("appid");
  $acls=GetHttpVars("acls", array()); 
  $coid=GetHttpVars("oid"); // oid for controlled object
  $returnact=GetHttpVars("returnact");

  // test if current user can modify ACL 
  $op=new ObjectPermission($action->dbaccess,array($action->parent->user->id,
						   $coid));
  if (($err = $op-> ControlOid( $appId, "modifyacl")) != "") {
	$action -> ExitError($err);
  }
      
  // serach ACL of the oid class 
  $acl = new Acl($action->dbaccess);
  $defacls = $acl->getAclApplication($appId);




  while (list($userId,$aclon) = each ($acls)) {     
  
      // modif permission for a particular user
    $p=new ObjectPermission($action->dbaccess,array($userId,$coid));

    $gp = array_unique($p->gprivileges);



    // delete old permissions
        $p-> Delete();
    reset($defacls);
    while (list($k, $acl) = each($defacls)) {


      // change only if needed :: not already in group privileges
      if ((in_array( $acl->id, $gp)) &&
	  (! isset($aclon[$acl->id]))) {
	//		print "moins $p->id_user $acl->id $acl->name<BR>";
      $p->id_acl=-$acl->id;
      $p->Add();
      }else 
      if ((! in_array( $acl->id, $gp)) &&
	  (isset($aclon[$acl->id]))) {
	//		print "pluss $userId $acl->id $acl->name<BR>";
      $p->id_acl= $acl->id;
      $p->Add();
      }
    }


  }


  redirect($action,"ACCESS","EDIT_OBJECT&sole=Y&mod=app&isclass=yes&userid={$action->parent->user->id}&appid=$appId&oid=$coid");

}
?>
