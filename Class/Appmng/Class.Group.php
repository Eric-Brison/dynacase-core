<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * User Group Definition
 *
 * @author Anakeen 2000
 * @version $Id: Class.Group.php,v 1.22 2007/03/12 08:25:55 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
/**
 */

include_once ('Class.DbObj.php');
include_once ('Class.QueryDb.php');
include_once ('Class.Log.php');
include_once ('Class.Application.php');

class Group extends DbObj
{
    var $fields = array(
        "iduser",
        "idgroup"
    );
    
    var $id_fields = array(
        "iduser"
    );
    
    var $dbtable = "groups";
    
    var $sqlcreate = "
create table groups ( iduser      int not null,
                      idgroup    int not null);
create index groups_idx1 on groups(iduser);
create unique index groups_idx2 on groups(iduser,idgroup);
create trigger t_nogrouploop before insert or update on groups for each row execute procedure nogrouploop();";
    
    var $groups = array(); // user groups
    public $iduser;
    public $idgroup;
    private $allgroups;
    private $levgid;
    /**
     * get groups of a user
     * set groups attribute. This attribute containt id of group of a user
     * @return bool true if at least one group
     */
    function GetGroups()
    {
        $query = new QueryDb($this->dbaccess, "Group");
        
        $query->AddQuery("iduser='{$this->iduser}'");
        $sql = sprintf("SELECT groups.idgroup as gid from groups, users where groups.idgroup=users.id and users.accounttype!='R' and groups.iduser=%d order by accounttype, lastname", $this->iduser);
        simpleQuery($this->dbaccess, $sql, $groupIds, true, false);
        $this->groups = $groupIds;
        
        return (count($groupIds) > 0);
    }
    /**
     * suppress a user from the group
     *
     * @param int $uid user identificator to suppress
     * @param bool $nopost set to to true to not perform postDelete methods
     * @return string error message
     */
    function SuppressUser($uid, $nopost = false)
    {
        $err = "";
        
        if (($this->iduser > 0) && ($uid > 0)) {
            $err = $this->exec_query("delete from groups where idgroup=" . $this->iduser . " and iduser=$uid");
            $err = $this->exec_query("delete from sessions where userid=$uid");
            
            $dbf = getParam("FREEDOM_DB");
            $g = new Group($dbf);
            $err = $g->exec_query("delete from groups where idgroup=" . $this->iduser . " and iduser=$uid");
            
            if (!$nopost) $this->PostDelete($uid);
        }
        return $err;
    }
    /**
     * initialise groups for a user
     */
    function PostSelect($id)
    {
        $this->GetGroups();
    }
    
    function preInsert()
    {
        // verify is exists
        $err = $this->exec_query(sprintf("select * from groups where idgroup=%s and iduser=%s", $this->idgroup, $this->iduser));
        if ($this->numrows() > 0) {
            $err = "OK"; // just to say it is not a real error
            
        }
        return $err;
    }
    
    function PostDelete($uid = 0)
    {
        if ($uid) $u = new Account("", $uid);
        else $u = new Account("", $this->iduser);
        $u->updateMemberOf();
        if ($u->accounttype != "U") {
            // recompute all doc profil
            $this->resetAccountMemberOf();
        } else {
            $dbf = getParam("FREEDOM_DB");
            $g = new Group($dbf);
            $g->iduser = $this->iduser;
            $g->idgroup = $this->idgroup;
            $err = $g->exec_query("delete from groups where idgroup=" . $this->iduser . " and iduser=" . $u->id);
            if ($err == "") {
                // if it is a user (not a group)
                $g->exec_query("delete from permission where computed");
                
                $p = new Permission($this->dbaccess);
                $p->deletePermission($g->iduser, null, null, true);
            }
        }
    }
    
    function PostInsert()
    {
        $err = $this->exec_query(sprintf("delete from sessions where userid=%d", $this->iduser));
        //    $this->FreedomCopyGroup();
        $u = new Account("", $this->iduser);
        
        $u->updateMemberOf();
        
        if ($u->accounttype != "U") {
            // recompute all doc profil
            $this->resetAccountMemberOf();
        } else {
            $dbf = getParam("FREEDOM_DB");
            $g = new Group($dbf);
            $g->iduser = $this->iduser;
            $g->idgroup = $this->idgroup;
            $err = $g->Add(true);
            if ($err == "" || $err == "OK") {
                // if it is a user (not a group)
                $g->exec_query("delete from permission where computed");
                
                $p = new Permission($this->dbaccess);
                $p->deletePermission($g->iduser, null, null, true);
            }
        }
        
        return $err;
    }
    /**
     * recompute all memberof properties of user accounts
     */
    function resetAccountMemberOf($synchro = false)
    {
        $err = $this->exec_query(sprintf("delete from sessions where userid=%d", $this->iduser));
        $err = $this->exec_query("delete from permission where computed");
        
        if ($synchro) {
            simpleQuery($this->dbaccess, "select * from users order by id", $tusers);
            $u = new Account($this->dbaccess);
            foreach ($tusers as $tu) {
                $u->affect($tu);
                $u->updateMemberOf();
            }
        } else {
            $wsh = getWshCmd();
            $cmd = $wsh . " --api=initViewPrivileges --reset-account=yes";
            
            bgexec(array(
                $cmd
            ) , $result, $err);
        }
    }
    /**
     * get ascendant direct group and group of group
     */
    function GetAllGroups()
    {
        $allg = $this->groups;
        while (list($k, $gid) = each($this->groups)) {
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
    function getChildsGroupId($pgid)
    {
        $this->_initAllGroup();
        
        $groupsid = array();
        
        if ($this->allgroups) {
            foreach ($this->allgroups as $k => $v) {
                if ($v["idgroup"] == $pgid) {
                    $uid = $v["iduser"];
                    $groupsid[$uid] = $uid;
                    //	  $groupsid=array_merge($groupsid, $this->getChildsGroup($v["iduser"]));
                    $groupsid+= $this->getChildsGroupId($uid);
                }
            }
        }
        return $groupsid;
    }
    /**
     * get all parent (ascendant) group of this group
     * @return array id
     */
    function getParentsGroupId($pgid, $level = 0)
    {
        $this->_initAllGroup();
        
        $groupsid = array();
        
        if ($this->allgroups) {
            foreach ($this->allgroups as $k => $v) {
                if ($v["iduser"] == $pgid) {
                    $gid = $v["idgroup"];
                    $groupsid[$gid] = $gid;
                    $this->levgid[$gid] = max($level, $this->levgid[$gid]);
                    
                    $groupsid+= $this->getParentsGroupId($gid, $level + 1);
                }
            }
        }
        return $groupsid;
    }
    /**
     * get all parent (ascendant) group of this group
     * @return array id
     */
    function getDirectParentsGroupId($pgid = "", &$uasid)
    {
        $this->levgid = array();
        $this->getParentsGroupId($pgid);
        //print_r2($this->levgid);
        $groupsid = array();
        asort($this->levgid);
        foreach ($this->levgid as $k => $v) {
            if ($v == 0) $groupsid[$k] = $k;
            else $uasid[$k] = $k;
        }
        return $groupsid;
    }
    
    private function _initAllGroup()
    {
        if (!isset($this->allgroups)) {
            $query = new QueryDb($this->dbaccess, "Group");
            $list = $query->Query(0, 0, "TABLE", "select * from groups where iduser in (select id from users where accounttype='G')");
            if ($list) {
                foreach ($list as $v) {
                    $this->allgroups[] = $v;
                }
            }
        }
    }
}
