<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Permission to execute actions
 *
 * @author Anakeen 2000
 * @version $Id: Class.Permission.php,v 1.10 2006/06/01 12:54:05 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
/**
 */

include_once ('Class.DbObj.php');
include_once ('Class.QueryDb.php');
include_once ('Class.Application.php');
include_once ('Class.Action.php');
include_once ('Class.Acl.php');
include_once ('Class.User.php');
include_once ('Class.Group.php');

class Permission extends DbObj
{
    var $fields = array(
        "id_user",
        "id_application",
        "id_acl",
        "computed"
    );
    
    var $id_fields = array(
        "id_user",
        "id_application"
    );
    
    var $dbtable = "permission";
    var $privileges = array(); // privileges array for a user (including group) in an application
    private $upprivileges = false; // specifific privileges array for a user in an application
    private $unprivileges = false; // specifific NO privileges array for a user in an application
    private $gprivileges = false; // privileges array for the group user
    var $sqlcreate = '
create table permission (id_user int not null,
                         id_application int not null,
                         id_acl int not null,
                         computed boolean default false);
create index permission_idx1 on permission(id_user);
create index permission_idx2 on permission(id_application);
create index permission_idx3 on permission(id_acl);
create index permission_idx4 on permission(computed);
                 ';
    
    public $id_user;
    public $id_application;
    public $id_acl;
    /**
     * @var bool
     */
    public $computed;
    
    var $actions = array(); // actions array for a user (including group) in an application
    function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0, $computed = true)
    {
        parent::__construct($dbaccess, $id, $res, $dbid);
        if (!$this->isAffected()) {
            
            if (is_array($id)) {
                $this->Affect(array(
                    "id_user" => $id[0],
                    "id_application" => $id[1],
                    "computed" => $id[2]
                ));
                $this->GetPrivileges(false, $computed);
            }
        }
    }
    function PostSelect()
    {
        // init privileges
        $this->GetPrivileges();
    }
    function PostDelete()
    {
        // update privileges
        $this->GetPrivileges();
    }
    
    function PostUpdate()
    {
        // update privileges
        $this->GetPrivileges();
    }
    
    function PreInsert()
    {
        // no duplicate items
        if ($this->Exists($this->id_user, $this->id_application, $this->id_acl)) return "Permission ({$this->id_user},{$this->id_application},{$this->id_acl}) already exists...";
        
        return "";
    }
    function postInsert()
    {
        if (!$this->computed) {
            $this->exec_query(sprintf("delete from permission where  id_application=%d and abs(id_acl)=%d and computed", $this->id_application, abs($this->id_acl)));
        }
        
        return "";
    }
    // Gives the list of Permission for a user on an application
    function ListUserPermissions($user, $app)
    {
        $query = new QueryDb($this->dbaccess, "Permission");
        $query->basic_elem->sup_where = array(
            "id_user='{$user->id}'",
            "id_application='{app->id}'"
        );
        $list = $query->Query();
        $res = array();
        $i = 0;
        while ($i < $query->nb) {
            $res[$i] = new Acl($this->dbaccess, $list[$i]->id_acl);
            $i++;
        }
        return ($res);
    }
    // Gives the list of application where a user has permission
    function ListUserApplications($user)
    {
        $query = new QueryDb($this->dbaccess, "Permission");
        $query->basic_elem->sup_where = array(
            "id_user='{$user->id}'"
        );
        
        $list = $query->Query();
        $res = array();
        $i = 0;
        while ($i < $query->nb) {
            $this->log->debug("ListUserApplicaion");
            $res[$i] = new Application($this->dbaccess, $list[$i]->id_application);
            $i++;
        }
        return ($res);
    }
    
    function ListApplicationUsers($app)
    {
        $query = new QueryDb($this->dbaccess, "Permission");
        $query->basic_elem->sup_where = array(
            "id_application='{$app->id}'"
        );
        
        $list = $query->Query();
        $res = array();
        $i = 0;
        while ($i < $query->nb) {
            $res[$i] = new User($this->dbaccess, $list[$i]->id_user);
            $i++;
        }
        return ($res);
    }
    
    function Exists($userid, $applicationid, $aclid = 0)
    {
        $query = new QueryDb($this->dbaccess, "Permission");
        $query->basic_elem->sup_where = array(
            "id_application='$applicationid'",
            "id_user='{$userid}'",
            "( computed = FALSE OR computed IS NULL )"
        );
        if ($aclid != 0) {
            $naclid = - $aclid;
            $query->AddQuery("(id_acl={$aclid}) OR (id_acl= {$naclid}) ");
        }
        $list = $query->Query(0, 0, "TABLE");
        
        return ($query->nb > 0);
    }
    
    function IsOver($user, $application, $acl)
    {
        $query = new QueryDb($this->dbaccess, "Permission");
        $query->basic_elem->sup_where = array(
            "id_application='{$application->id}'",
            "id_user='{$user->id}'"
        );
        $list = $query->Query();
        if ($query->nb == 0) return FALSE;
        $aclu = new Acl($this->dbaccess, $list[0]->id_acl);
        return ($aclu->grant_level >= $acl->grant_level);
    }
    
    function GrantLevel($user, $application)
    {
        $query = new QueryDb($this->dbaccess, "Permission");
        $query->basic_elem->sup_where = array(
            "id_application='{$application->id}'",
            "id_user='{$user->id}'"
        );
        $list = $query->Query();
        if ($query->nb == 0) return (0);
        $acl = new Acl($this->dbaccess, $list[0]->id_acl);
        return ($acl->grant_level);
    }
    
    function DelAppPerm($id)
    {
        $query = new QueryDb($this->dbaccess, "Permission");
        $query->basic_elem->sup_where = array(
            "id_application=$id"
        );
        $list = $query->Query();
        $this->log->debug("DEL APP PERM");
        if ($query->nb > 0) {
            while (list($k, $v) = each($list)) {
                $v->Delete();
            }
        } else {
            $this->log->debug("NO PERM");
        }
    }
    /**
     * return ACL up list for a user
     */
    public function GetUpPrivileges()
    {
        if ($this->upprivileges === false) {
            $this->GetPrivileges(true, false);
        }
        return $this->upprivileges;
    }
    /**
     * return ACL un list for a user
     */
    public function GetUnPrivileges()
    {
        if ($this->unprivileges === false) {
            $this->GetPrivileges(true, false);
        }
        return $this->unprivileges;
    }
    /**
     * return ACL un list for a user
     */
    public function GetGPrivileges()
    {
        if ($this->gprivileges === false) {
            $this->GetPrivileges(true, false);
        }
        return $this->gprivileges;
    }
    /**
     * Get all ACL for a given application
     */
    public function getAllAclForApplication($appid)
    {
        $query = new QueryDb($this->dbaccess, "acl");
        $query->basic_elem->sup_where = array(
            "id_application = '" . $appid . "'"
        );
        $res = $query->Query();
        $aclList = array();
        foreach ($res as $k => $v) {
            $aclList[] = $v->id;
        }
        return $aclList;
    }
    /**
     * Returns the resulting ACL for a given (user, application), computing
     * ACL value if they are empty.
     */
    public function GetComputedPrivileges($uid, $appid)
    {
        $query = new QueryDb($this->dbaccess, "permission");
        $query->basic_elem->sup_where = array(
            "id_application = '" . $appid . "'",
            "id_user = '" . $uid . "'",
            "computed = TRUE"
        );
        $computedAcl = array();
        $privileges = array();
        $list = $query->Query();
        if ($query->nb > 0) {
            while (list($k, $v) = each($list)) {
                $computedAcl[abs($v->id_acl) ] = $v->id_acl;
            }
        }
        $allAclList = $this->getAllAclForApplication($appid);
        foreach ($allAclList as $acl) {
            if (!array_key_exists($acl, $computedAcl)) {
                $computedAcl[abs($acl) ] = $this->computePerm($uid, $appid, abs($acl));
            }
        }
        return array_values($computedAcl);
    }
    /**
     * Return the ACL value for a given (user, app, acl), computing it if it's not
     * already computed, and storing the results.
     */
    public function computePerm($uid, $appid, $acl)
    {
        $db = new DbObj($this->dbaccess);
        $res = $db->exec_query(sprintf("SELECT computePerm(%d, %d, %d)", $uid, $appid, abs($acl)));
        $perm = $db->fetch_array(0);
        return $perm['computeperm'];
    }
    /**
     * return ACL list for a user
     */
    public function GetPrivileges($force = false, $computed = true)
    {
        global $session;
        
        if (!$force) {
            $privileges = "";
            if ($computed) {
                $privileges = $this->GetComputedPrivileges($this->id_user, $this->id_application);
                if (count($privileges) <= 0) {
                    $privileges = "";
                }
            }
            if ($privileges !== "") {
                $this->privileges = $privileges;
                return;
            }
        }
        $this->privileges = array();
        $this->upprivileges = array();
        $this->unprivileges = array();
        $this->gprivileges = array();
        // add groups privilege
        $ugroup = new Group($this->dbaccess, $this->id_user);
        
        while (list($k, $gid) = each($ugroup->groups)) {
            
            $gperm = new permission($this->dbaccess, array(
                $gid,
                $this->id_application,
                false
            ) , '', 0, $computed);
            // add group
            while (list($k2, $gacl) = each($gperm->privileges)) {
                if (!in_array($gacl, $this->privileges)) {
                    $this->gprivileges[] = $gacl;
                    $this->privileges[] = $gacl;
                }
            }
        }
        
        $query = new QueryDb($this->dbaccess, "Permission");
        $query->basic_elem->sup_where = array(
            "id_application='{$this->id_application}'",
            "id_user='{$this->id_user}'",
            (!$computed) ? "( computed = FALSE OR computed IS NULL )" : ""
        );
        $list = $query->Query();
        if ($query->nb > 0) {
            while (list($k, $v) = each($list)) {
                if ($v->id_acl > 0) {
                    // add privilege
                    $this->upprivileges[] = $v->id_acl;
                    if (!in_array($v->id_acl, $this->privileges)) {
                        $this->privileges[] = $v->id_acl;
                    }
                } else {
                    // suppress privilege
                    $this->unprivileges[] = - ($v->id_acl);
                    
                    $nk = array_search(-($v->id_acl) , $this->privileges, false);
                    if (is_integer($nk)) {
                        unset($this->privileges[$nk]);
                    }
                }
            }
        }
        
        return ($this->privileges);
    }
    
    function HasPrivilege($idacl)
    {
        return (($this->id_user == 1) || // admin user
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
            if ($acl->Set($aclname, $this->id_application)) {
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
    function GetActions()
    {
        
        $this->actions = array();
        
        $acls = $this->GetPrivileges();
        
        if (!count($acls)) return array();
        
        $sSql = " select distinct action.name from action inner join acl on
action.acl = acl.name where ";
        $sSql.= " action.id_application = '" . $this->id_application . "' AND ";
        $sSql.= " acl.id in ('" . implode("','", $acls) . "')";
        
        $res = pg_exec($this->dbid, $sSql);
        
        $i = 0;
        while ($arr = pg_fetch_array($res, $i)) {
            $this->actions[] = $arr[0];
            $i++;
        }
        return $this->actions;
    }
    /**
     * delete permissions
     */
    public function deletePermission($id_user = null, $id_application = null, $id_acl = null, $computed = null)
    {
        $sqlCond = array();
        if ($id_user != null) {
            $sqlCond[] = sprintf("( id_user = %d )", pg_escape_string($id_user));
        }
        if ($id_application != null) {
            $sqlCond[] = sprintf("( id_application = %d )", pg_escape_string($id_application));
        }
        if ($id_acl != null) {
            $sqlCond[] = sprintf("( abs(id_acl) = abs(%d) )", pg_escape_string($id_acl));
        }
        if ($computed != null) {
            if ($computed = true) {
                $sqlCond[] = "( computed = TRUE )";
            } else {
                $sqlCond[] = "( computed = FALSE OR computed IS NULL )";
            }
        }
        
        if (count($sqlCond) > 0) {
            return $this->exec_query(sprintf("DELETE FROM permission WHERE ( %s )", join(" AND ", $sqlCond)));
        }
        
        return false;
    }
}
?>
