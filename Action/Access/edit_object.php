<?php
// ---------------------------------------------------------------
// $Id: edit_object.php,v 1.2 2002/02/18 10:55:16 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Access/edit_object.php,v $
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
// ---------------------------------------------------------------
include_once("Class.User.php");
include_once("Class.ControlObject.php");
include_once("Class.ObjectPermission.php");
// -----------------------------------
function edit_object(&$action) {
// -----------------------------------



  $coid = GetHttpVars("oid",0);
  $appid = GetHttpVars("appid",0);
    // contruct user id list

  // write title : oid description
  $oid = new ControlObject($action->dbaccess, array($coid,$appid));
  $action->lay->Set("title",$oid->description);
  $action->lay->Set("appid",$appid);
  $action->lay->Set("oid",$coid);


  // compute the head of table : acl definition
  $acl = new Acl($action->dbaccess);
  $appacls = $acl->getAclApplication($oid->id_class);
  $tacldef=array();
  while(list($k,$v) = each($appacls)) {
      $tacldef[$k]["description"]= _($v->description);
      $tacldef[$k]["name"]=$v->name;
  }
  $action->lay->SetBlockData("DEFACL",$tacldef); 


  // define ACL for  each user
    $ouser = new User();
  //$tiduser = $ouser->GetUserAndGroupList();
    $tiduser = $ouser->GetGroupList();
    $userids= array();
    while(list($k,$v) = each($tiduser)) {
      if ($v->id == 1) continue; // except admin : don't need privilege
      $userids[$k]["userid"]= $v->id;
      $userids[$k]["descuser"]=$v->firstname." ".$v->lastname;
      $userids[$k]["SELECTACL"]= "selectacl_$k";

      // compute acl for userId
      $uperm = new ObjectPermission($action->dbaccess,array($v->id, $coid));

      $tacl=array();
      reset($appacls);
      while(list($ka,$acl) = each($appacls)) {
	$tacl[$ka]["aclid"] = $acl->id;
	if (in_array($acl->id, $uperm->privileges)) {
	  $tacl[$ka]["selected"]="checked";
	} else $tacl[$ka]["selected"]="";
      }
      $action->lay->SetBlockData($userids[$k]["SELECTACL"],$tacl);
    }


    $action->lay->SetBlockData("USERS",$userids); 
}



?>
