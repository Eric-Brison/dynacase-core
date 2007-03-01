<?php
/**
 * User Group Definition
 *
 * @author Anakeen 2000 
 * @version $Id: Class.Group.php,v 1.19 2007/03/01 09:35:34 eric Exp $
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
  function SuppressUser($uid,$nopost=false) {
      $err="";

      if (($this->iduser>0) && ($uid > 0)) {
	$err = $this->exec_query("delete from groups where idgroup=".$this->iduser." and iduser=$uid");
	$err = $this->exec_query("delete from sessions where userid=$uid");
	
	if (usefreedomuser()) {
	  $dbf=getParam("FREEDOM_DB");
	  $g=new Group($dbf);
	  $err = $g->exec_query("delete from groups where idgroup=".$this->iduser." and iduser=$uid");	  
	}
	if (!$nopost) $this->PostDelete();
      }
      return $err;			   
  }
  /**
   * initialise groups for a user
   */
  function PostSelect() {      
      $this->GetGroups();
    }

  function preInsert() {
    // verify is exists    
    $err = $this->exec_query(sprintf("select * from groups where idgroup=%s and iduser=%s",
				     $this->idgroup,$this->iduser));
    if ($this->numrows() > 0) {
      $err="OK"; // just to say it is not a real error
    }
    return $err;
  }


  function PostDelete() {
    // delete unavailable group
    // $err = $this->exec_query("delete from groups where idgroup not in (select id from users)");
    // $err = $this->exec_query("delete from groups where iduser not in (select id from users)");
    //    $this->FreedomCopyGroup();    

    if (usefreedomuser()) {
      $dbf=getParam("FREEDOM_DB");
      $g=new Group($dbf);
      $g->iduser=$this->iduser;
      $g->idgroup=$this->idgroup;
      $err=$g->Delete(true);
      if ($err=="") {
	$g->exec_query("delete from docperm where userid=".$g->iduser); // if it is a user (not a group)
      }
    }

  }
  function PostInsert() {
    $err = $this->exec_query("delete from sessions where userid=".$this->iduser);
    //    $this->FreedomCopyGroup();
    if (usefreedomuser()) {
      $dbf=getParam("FREEDOM_DB");
      $g=new Group($dbf);
      $g->iduser=$this->iduser;
      $g->idgroup=$this->idgroup;
      $err=$g->Add(true);
      if ($err=="") {
	$g->exec_query("delete from docperm where userid=".$g->iduser); // if it is a user (not a group)
      }
    }
    return $err;
    
  } 

  
  function FreedomCopyGroup() {
    $err = $this->exec_query("delete from sessions where userid=".$this->iduser);

    if (usefreedomuser()) {
      $wsh = getWshCmd();
      $cmd = $wsh . " --api=freedom_groups";

      exec($cmd);
//       $wsh = "nice -n 1 ".GetParam("CORE_PUBDIR")."/wsh.php";
//       $cmd = $wsh . " --api=usercard_iuser >/dev/null 2>&1 &";

//       exec($cmd);
    }
  }

  /**
   * get ascendant direct group and group of group
   */
  function GetAllGroups()
    {
      $allg = $this->groups;
      while (list($k,$gid) = each($this->groups)) {
	$og = new Group($this->dbaccess, $gid);
	$allg = array_merge($allg, $og->GetAllGroups());
      }
      $allg = array_unique($allg);

      return $allg;
    }

  /**
   * get all child (descendant) group of this group
   * @return array id
   */
  function getChildsGroupId($pgid="") {
    if ($pgid=="") $pgid=$this->id;
    $this->_initAllGroup();

      
    $groupsid=array();

    if ($this->allgroups) {
      foreach($this->allgroups as $k=>$v) {
	if ($v["idgroup"]==$pgid) {
	  $uid=$v["iduser"];
	  $groupsid[$uid] = $uid;
	  //	  $groupsid=array_merge($groupsid, $this->getChildsGroup($v["iduser"]));
	  $groupsid += $this->getChildsGroupId($uid);
	}
      }
    }
    return $groupsid;
  } 
  /**
   * get all parent (ascendant) group of this group
   * @return array id
   */
  function getParentsGroupId($pgid="", $level=0) {
    if ($pgid=="") $pgid=$this->id;
    $this->_initAllGroup();
      
    $groupsid=array();

    if ($this->allgroups) {
      foreach($this->allgroups as $k=>$v) {
	if ($v["iduser"]==$pgid) {
	  $gid=$v["idgroup"];
	  $groupsid[$gid] = $gid;
	  $this->levgid[$gid] = max($level,$this->levgid[$gid]);

	  $groupsid += $this->getParentsGroupId($gid,$level+1);
	}
      }
    }
    return $groupsid;
  }

  /**
   * get all parent (ascendant) group of this group
   * @return array id
   */
  function getDirectParentsGroupId($pgid="",&$uasid) {
    $this->levgid=array();
    $this->getParentsGroupId($pgid);
    //print_r2($this->levgid);
    $groupsid=array();
    asort($this->levgid);
    foreach ($this->levgid as $k=>$v) {
      if ($v==0) $groupsid[$k]=$k;
      else $uasid[$k]=$k;
    }
    return $groupsid;

  }
  function _initAllGroup() {
    if (!isset($this->allgroups)) {
      /* alone groups : not needed
      $query = new QueryDb($this->dbaccess, "User");  
      $query->AddQuery("isgroup='Y'");
      $list= $query->Query(0,0,"TABLE");
      
      foreach ($list as $v) {
	$this->allgroups[$v["id"]]="";
      }
      */
      $query = new QueryDb($this->dbaccess, "Group");           
      $list = $query->Query(0,0,"TABLE","select * from groups where iduser in (select id from users where isgroup='Y')");
      if ($list) {
	foreach ($list as $v) {
	  $this->allgroups[]=$v;
	}
      }
    }
  }
}
?>
