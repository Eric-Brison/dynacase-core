<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Class.User.php,v 1.18 2003/08/18 15:46:42 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: Class.User.php,v 1.18 2003/08/18 15:46:42 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Class/Appmng/Class.User.php,v $
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

$CLASS_USER_PHP = '$Id: Class.User.php,v 1.18 2003/08/18 15:46:42 eric Exp $';
include_once('Class.DbObj.php');
include_once('Class.QueryDb.php');
include_once('Class.Log.php');
include_once('Class.Application.php');
include_once('Class.Group.php');

define("ANONYMOUS_ID", 3);

Class User extends DbObj
{
  var $fields = array ( "id","iddomain","lastname","firstname","login","password","isgroup","expires","passdelay","status");

  var $id_fields = array ("id");

  var $dbtable = "users";

  var $order_by="lastname, isgroup desc";

  var $fulltextfields = array ("login","lastname","firstname");

  var $sqlcreate = "
create table users ( id      int not null,
                     iddomain int not null,
                primary key (id),
                        lastname   text,
                        firstname  text,
                        login      text not null,
                        password   varchar(30) not null,
                        isgroup    char,
                        expires    int,
                        passdelay  int,
                        status     char);
create index users_idx1 on users(id);
create index users_idx2 on users(lastname);
create index users_idx3 on users(login);
create sequence seq_id_users start 10";



  function SetLoginName($loginDomain)
    {
      $query = new QueryDb($this->dbaccess,"User");
      if (ereg("(.*)@(.*)",$loginDomain, $reg)) {
    
	$queryd = new QueryDb($this->dbaccess,"Domain");
	$queryd->AddQuery("name='".$reg[2]."'");
	$list = $queryd->Query();

	if ($queryd->nb == 1) {
	  $domainId=$list[0]->iddomain;
	  $query->AddQuery("iddomain='$domainId'");
	  $query->AddQuery("login='".$reg[1]."'");
	} else {
	  return false;
	}
    
      } else {

	$query->AddQuery("login='$loginDomain'");
      }
      $list = $query->Query();

      if ($query->nb == 1) {
	$this=$list[0];
      } else {
	return FALSE;
      }

      return TRUE;
    }
  function SetLogin($login,$domain)
    {
      $query = new QueryDb($this->dbaccess,"User");

      $query->basic_elem->sup_where=array("login='$login'",
					  "iddomain=$domain");

      $list = $query->Query();

      if ($query->nb != 0) {
	$this=$list[0];
      } else {
	return FALSE;
      }

      return TRUE;
    }

  function PreInsert()
    {

      if ($this->Setlogin($this->login,$this->iddomain)) return "this login exists";
      if ($this->id == "") {
	$res = pg_exec($this->dbid, "select nextval ('seq_id_users')");
	$arr = pg_fetch_array ($res, 0);
	$this->id = $arr[0];
      }
      if (isset($this->isgroup) && ($this->isgroup == "Y")) {
	$this->password_new="no"; // no passwd for group
      } else {
	$this->isgroup = "N";
      }
      $this->login = strtolower($this->login);
      if (isset($this->password_new) && ($this->password_new!="")) {
	$this->computepass($this->password_new, $this->password);
      }
    }

  function PostInsert()     
    {
      // create default ACL for each application
      // only for group
      //    if ($this->isgroup == "Y") {
      // 	$app = new Application();
      // 	$app-> UpdateUserAcl($this->id);
      //       }
      $this->FreedomWhatUser();
  
    }
  function PostUpdate()     
    {
      $this->FreedomWhatUser();  
    }

  function PreUpdate()
    {
      if (isset($this->password_new) && ($this->password_new!="")) {
	$this->computepass($this->password_new, $this->password);
      }
      if (intval($this->passdelay) == 0) $this->expires="0"; // nether expire
      else if (intval($this->expires)==0) $this->expires=time()+$this->passdelay;
    }
  function PostDelete()
    {
      // delete reference in group table
      $group = new Group($this->dbaccess, $this->id);
      $group-> Delete();
    }

  function FreedomWhatUser() {
  
    $wsh = GetParam("CORE_PUBDIR")."/wsh.php";
    $cmd = $wsh . " --api=usercard_iuser --whatid={$this->id}";


    exec($cmd);
  }
  // --------------------------------------------------------------------
  function computepass($pass, &$passk)
    {
      srand((double)microtime()*1000000);
      $salt = chr(rand(59,122)).chr(rand(59,122));
      $passk = crypt($pass, $salt);
    }

  function checkpassword($pass)
    {
      if ($this->isgroup == 'Y') return false; // don't log in group 
      return($this->checkpass($pass,$this->password));
    }    

  // --------------------------------------------------------------------
  function checkpass($pass, $passk)
    {
      $salt = substr($passk, 0, 2);
      $passres = crypt($pass, $salt);
      return ($passres == $passk);
    } 

  function PostInit() {


    $group = new group($this->dbaccess);

    // Create admin user
    $this->iddomain=1;
    $this->id=1;
    $this->lastname="Master";
    $this->firstname="What";
    $this->password_new="anakeen";
    $this->login="admin";
    $this->Add();
    $group->iduser=$this->id;

    // Create default group
    $this->iddomain=1;
    $this->id=2;
    $this->lastname="Default";
    $this->firstname="What Group";
    $this->login="all";
    $this->isgroup="Y";
    $this->Add();
    $group->idgroup=$this->id;
    $group->Add();
  
  
    // Create anonymous user
    $this->iddomain=1;
    $this->id=ANONYMOUS_ID;
    $this->lastname="anonymous";
    $this->firstname="guest";
    $this->login="anonymous";
    $this->isgroup="N";
    $this->Add();


    // Store error messages
     
  }

  // get All Users (not group)
  function GetUserList($qtype="LIST") {
    $query = new QueryDb($this->dbaccess,"User");
    $query->order_by="lastname";
    $query-> AddQuery("(isgroup != 'Y') OR (isgroup isnull)");
    return($query->Query(0,0,$qtype));
  }

  // get All groups
  function GetGroupList($qtype="LIST") {
    $query = new QueryDb($this->dbaccess,"User");
    $query->order_by="lastname";
    $query-> AddQuery("isgroup = 'Y'");
    return($query->Query(0,0,$qtype));
  }

  // get All users & groups
  function GetUserAndGroupList() {
    $query = new QueryDb($this->dbaccess,"User");
    $query->order_by="isgroup desc, lastname";
    return($query->Query());
  }


  // get All ascendant group ids of the object user
  function GetGroupsId() {
    $query = new QueryDb($this->dbaccess, "Group");

    $query-> AddQuery("iduser='{$this->id}'");

    $list = $query->Query(0,0,"TABLE");
    $groupsid=array();

    if ($query->nb >0) {
      while (list($k,$v) = each($list)) {
	$groupsid[] = $v["idgroup"];
      }
    
    } 

    return $groupsid;

  }

  
  // for group :: get All user & groups ids in all descendant(recursive);
  function GetRUsersList($id) {
    $query = new QueryDb($this->dbaccess, "User");
    $list = $query->Query(0,0,"TABLE",
			  "select users.* from users, groups where ".
			  "groups.iduser=users.id and ".
			  "idgroup=$id ;");


    $uid=array();

    if ($query->nb >0) {
      while (list($k,$v) = each($list)) {
	$uid[$v["id"]] = $v;
	if ($v["isgroup"]=="Y") {
	  $uid += $this->GetRUsersList($v["id"]);
	}
      }
    
    } 

    return $uid;

  }

  
  function GetUsersGroupList($gid) {
    $query = new QueryDb($this->dbaccess, "User");
    $list = $query->Query(0,0,"TABLE",
			  "select users.* from users, groups where ".
			  "groups.iduser=users.id and ".
			  "idgroup=$gid ;");


    $uid=array();

    if ($query->nb >0) {
      while (list($k,$v) = each($list)) {
	$uid[$v["id"]] = $v;	
      }
    
    } 

    return $uid;

  }

  // only use for group
  // get user member of group
  function getGroupUserList($qtype="LIST", $withgroup=false) {
    $query = new QueryDb($this->dbaccess,"User");
    $query->order_by="isgroup desc, lastname";
    $selgroup = "and (isgroup != 'Y' or isgroup is null)";
    if ($withgroup) $selgroup = "";
    return ($query->Query(0,0,$qtype,
			  "select users.* from users, groups where ".
			  "groups.iduser=users.id and ".
			  "idgroup={$this->id} {$selgroup};"));
  }
}
?>
