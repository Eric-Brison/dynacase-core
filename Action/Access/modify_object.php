<?php
// ---------------------------------------------------------------
// $Id: modify_object.php,v 1.4 2002/03/05 18:14:51 eric Exp $
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
						   $coid,
						   $appId));
  if (($err = $op-> Control( $appId, "modifyacl")) != "") {
	$action -> ExitError($err);
  }
      
  // serach ACL of the oid class 
  $acl = new Acl($action->dbaccess);
  $defacls = $acl->getAclApplication($appId);




  while (list($userId,$aclon) = each ($acls)) {     
  
    // modif permission for a particular user
      $p=new ObjectPermission($action->dbaccess,array($userId,$coid,$appId));
    $p->GetGroupPrivileges();
    
    $gp = array_unique($p->gprivileges);
    
    
    
    // delete old permissions
      $p-> Delete();
    reset($defacls);
    while (list($k, $acl) = each($defacls)) {
      
      
      // change only if needed :: not already in group privileges
	if ((in_array( $acl->id, $gp)) &&
	    (! isset($aclon[$acl->id]))) {
	  //		print "moins $p->id_user $acl->id $acl->name<BR>";
	  $p->AddAcl(-$acl->id);
	}else 
	  if ((! in_array( $acl->id, $gp)) &&
	      (isset($aclon[$acl->id]))) {
	    //		print "pluss $userId $acl->id $acl->name<BR>";
	    $p->AddAcl($acl->id);
	  }
    }
    $p->Add();
    
  }
  
  
  redirect($action,"ACCESS","EDIT_OBJECT&sole=Y&mod=app&isclass=yes&userid={$action->parent->user->id}&appid=$appId&oid=$coid");
  
}
?>
