<?php
/**
 * User Group Definition
 *
 * @author Anakeen 2000 
 * @version $Id: Class.Group.php,v 1.7 2004/03/01 08:34:16 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */


include_once('Class.DbObj.php');
include_once('Class.QueryDb.php');
include_once('Class.Log.php');
include_once('Class.Application.php');

Class Group extends DbObj
{
  var $fields = array ( "iduser","idgroup");

  var $id_fields = array ("iduser");

  var $dbtable = "groups";


  var $sqlcreate = "
create table groups ( iduser      int not null,
                      idgroup    int not null);
create index groups_idx1 on groups(iduser);
create unique index groups_idx2 on groups(iduser,idgroup);
create trigger t_nogrouploop before insert or update on groups for each row execute procedure nogrouploop();";

  var $groups = array(); // user groups


  /**
   * get groups of a user
   * set groups attribute. This attribute containt id of group of a user
   * @return bool true if at least one group
   */
  function GetGroups() {
      $query = new QueryDb($this->dbaccess, "Group");

      $query-> AddQuery("iduser='{$this->iduser}'");

      $list = $query->Query();
      if ($query->nb >0) {
	while (list($k,$v) = each($list)) {
	  $this->groups[] = $v->idgroup;
	}
      } else {
	return false;
      }

      return true;
    }
  /**
   * suppress a user from the group 
   *
   * @param int $uid user identificator to suppress
   * @return string error message 
   */
  function SuppressUser($uid) {
      $err="";

      if (($this->iduser>0) && ($uid > 0)) {
	$err = $this->exec_query("delete from groups where idgroup=".$this->iduser." and iduser=$uid");
	$this->PostDelete();
      }
      return $err;			   
  }
  /**
   * initialise groups for a user
   */
  function PostSelect() {      
      $this->GetGroups();
    }

  function PostUpdate() {
    $this->FreedomCopyGroup();
  }
  function PostDelete() {
    $this->FreedomCopyGroup();
  }
  function PostInsert() {
    $this->FreedomCopyGroup();
  }
  function FreedomCopyGroup() {
  
    $wsh = GetParam("CORE_PUBDIR")."/wsh.php";
    $cmd = $wsh . " --api=freedom_groups";

    exec($cmd);
    $wsh = "nice -n 1 ".GetParam("CORE_PUBDIR")."/wsh.php";
    $cmd = $wsh . " --api=usercard_iuser >/dev/null 2>&1 &";

    exec($cmd);
  }

  /**
   * get direct group and group of group
   */
  function GetAllGroups()
    {
      $allg = $this->groups;
      while (list($k,$gid) = each($this->groups)) {
	$og = new Group($this->dbaccess, $gid);
	$allg = array_merge($allg, $og-> GetAllGroups());
      }
      $allg = array_unique($allg);

      return $allg;
    }


}
?>
