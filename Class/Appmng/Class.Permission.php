<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Class.Permission.php,v 1.6 2005/06/28 13:53:24 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

// $Id: Class.Permission.php,v 1.6 2005/06/28 13:53:24 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Class/Appmng/Class.Permission.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2000
// O*O  Anakeen Development Team
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

//
$CLASS_PERMISSION_PHP = '$Id: Class.Permission.php,v 1.6 2005/06/28 13:53:24 eric Exp $';
include_once('Class.DbObj.php');
include_once('Class.QueryDb.php');
include_once('Class.Application.php');
include_once('Class.Action.php');
include_once('Class.Acl.php');
include_once('Class.User.php');
include_once('Class.Group.php');

Class Permission extends DbObj
{
  var $fields = array ( "id_user","id_application","id_acl");

  var $id_fields = array ( "id_user","id_application");

  var $dbtable = "permission";
  var $privileges = array(); // privileges array for a user (including group) in an application
  var $upprivileges = array(); // specifific privileges array for a user in an application
  var $unprivileges = array(); // specifific NO privileges array for a user in an application
  var $gprivileges = array(); // privileges array for the group user

  var $sqlcreate = '
create table permission (id_user int not null,
                         id_application int not null,
                         id_acl int not null);
create index permission_idx1 on permission(id_user);
create index permission_idx2 on permission(id_application);
create index permission_idx3 on permission(id_acl);
                 ';

  var $actions = array(); // actions array for a user (including group) in an application

  function __construct($dbaccess='', $id='',$res='',$dbid=0)
    {
      parent::__construct($dbaccess, $id,$res,$dbid);
      if (! $this->isAffected()) {
      
	if (is_array($id)) {
	  $this->Affect(array("id_user" => $id[0],
			      "id_application" => $id[1]));
	  $this-> GetPrivileges();
	}
      }

    }
  function PostSelect()
    {
      // init privileges
      $this-> GetPrivileges();
    }
  function PostDelete()
    {
      // update privileges
      $this-> GetPrivileges();
    }

  function PostUpdate()
    {
      // update privileges
      $this-> GetPrivileges();
    }

  function PreInsert( )
    {
      // no duplicate items
      if ($this->Exists($this->id_user,$this->id_application,$this->id_acl)) return "Permission ({$this->id_user},{$this->id_application},{$this->id_acl}) already exists...";  
  
      return "";
  
    }
  // Gives the list of Permission for a user on an application
  function ListUserPermissions($user,$app)
    {
      $query = new QueryDb($this->dbaccess,"Permission");
      $query->basic_elem->sup_where = array ("id_user='{$user->id}'",
					     "id_application='{app->id}'");
      $list = $query->Query();
      $res = array();
      $i=0;
      while ($i < $query->nb) {
	$res[$i] = new Acl($this->dbaccess,$list[$i]->id_acl);
	$i++;
      }
      return($res);
    }

  // Gives the list of application where a user has permission
  function ListUserApplications($user) {
    $query = new QueryDb($this->dbaccess,"Permission");
    $query->basic_elem->sup_where = array ("id_user='{$user->id}'");

    $list = $query->Query();
    $res = array();
    $i=0;
    while ($i < $query->nb) {
      $this->log->debug("ListUserApplicaion");
      $res[$i] = new Application($this->dbaccess,$list[$i]->id_application);
      $i++;
    }
    return($res);
  }

  function ListApplicationUsers($app) {
    $query = new QueryDb($this->dbaccess,"Permission");
    $query->basic_elem->sup_where = array ("id_application='{$app->id}'");

    $list = $query->Query();
    $res = array();
    $i=0;
    while ($i < $query->nb) {
      $res[$i] = new User($this->dbaccess,$list[$i]->id_user);
      $i++;
    }
    return($res);
  }

  function Exists($userid,$applicationid,$aclid=0) {
    $query = new QueryDb($this->dbaccess,"Permission");
    $query->basic_elem->sup_where = array ("id_application='$applicationid'",
					   "id_user='{$userid}'");
    if ($aclid != 0) {
      $naclid= - $aclid;
      $query->AddQuery("(id_acl={$aclid}) OR (id_acl= {$naclid}) ");
    }
    $list = $query->Query();

    return($query->nb>0);
  }

  function IsOver($user,$application,$acl) {
    $query = new QueryDb($this->dbaccess,"Permission");
    $query->basic_elem->sup_where = array ("id_application='{$application->id}'",
					   "id_user='{$user->id}'");
    $list = $query->Query();
    if ($query->nb == 0) return FALSE;
    $aclu = new Acl($this->dbaccess,$list[0]->id_acl);
    return ($aclu->grant_level >= $acl->grant_level);
  }

  function GrantLevel($user,$application) {
    $query = new QueryDb($this->dbaccess,"Permission");
    $query->basic_elem->sup_where = array ("id_application='{$application->id}'",
					   "id_user='{$user->id}'");
    $list = $query->Query();
    if ($query->nb == 0) return(0);
    $acl = new Acl($this->dbaccess,$list[0]->id_acl);
    return($acl->grant_level);
  }


  function DelAppPerm($id) {
    $query = new QueryDb($this->dbaccess,"Permission");
    $query->basic_elem->sup_where = array ("id_application=$id");
    $list = $query->Query();
    $this->log->debug("DEL APP PERM");
    if ($query->nb >0) {
      while (list($k,$v) = each($list)) {
	$v->Delete();
      }
    } else {
      $this->log->debug("NO PERM");
    }
  }


  // return ACL list for a user
  function GetPrivileges() {

    $this->privileges= array();
    $this->upprivileges= array();
    $this->unprivileges= array();
    $this->gprivileges= array();

    // add groups privilege
    $ugroup = new Group($this->dbaccess,
			$this->id_user);

    while (list($k,$gid) = each($ugroup->groups)) {

      $gperm = new permission($this->dbaccess, 
			      array($gid, 
				    $this->id_application));

      // add group 

      while (list($k2,$gacl) = each($gperm->privileges)) {
	if (! in_array($gacl, $this->privileges)) {
	  $this->gprivileges[]= $gacl;
	  $this->privileges[]= $gacl;
	}    
      }
    }


    $query = new QueryDb($this->dbaccess,"Permission");
    $query->basic_elem->sup_where = array ("id_application='{$this->id_application}'",
					   "id_user='{$this->id_user}'");
    $list = $query->Query();
    if ($query->nb > 0) {
      while (list($k,$v) = each($list)) {
	if ($v->id_acl > 0) {
	  // add privilege
	  $this->upprivileges[]= $v->id_acl;
	  if (! in_array($v->id_acl, $this->privileges)) {
	    $this->privileges[]= $v->id_acl;
	  } 
	}else { 
	  // suppress privilege
	  $this->unprivileges[]= -($v->id_acl);
	
	  $nk=array_search(-($v->id_acl), $this->privileges, false);
	  if (is_integer($nk)) {
	    unset($this->privileges[$nk]);
	  }
	}
      
      }
    }


    return($this->privileges);
  }

  function HasPrivilege($idacl)
    {
      return(($this->id_user == 1) || // admin user
	     (in_array($idacl, $this->privileges)));
    }

  // id_user field must be set before
  function AddUserPermission($appname, $aclname)
    {
      $app = new Application($this->dbaccess);
      $appid = $app->GetIdFromName($appname);
      if ($appid != 0) {

	$this->id_application = $appid;

	$acl = new Acl($this->dbaccess);
	if ($acl -> Set($aclname, $this->id_application)) {
	  $this->id_acl = $acl->id;
	  $this->Add();
	}
      }
    }



  /** 
   * return ACTION list for a user  
   * 
   * @author Philippe VALENCIA <pvalencia@fram.fr>
   * @return array actions available for current user
   */
  function GetActions() {

    $this->actions = array();

    $acls = $this->GetPrivileges();

    if (!count($acls)) return array();

    $sSql = " select distinct action.name from action inner join acl on
action.acl = acl.name where ";
    $sSql .= " action.id_application = '" . $this->id_application . "' AND ";
    $sSql .= " acl.id in ('" . implode("','", $acls) . "')";

    $res = pg_exec($this->dbid, $sSql);

    $i=0;
    while ($arr = pg_fetch_array ($res, $i)) {
      $this->actions[] = $arr[0];
      $i++;
    }
    return $this->actions;

  }
}


?>
