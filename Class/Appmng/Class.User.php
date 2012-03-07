<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Users Definition
 *
 * @author Anakeen 2000
 * @version $Id: Class.User.php,v 1.65 2008/08/11 14:14:14 marc Exp $
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
include_once ('Class.Group.php');

require_once 'PEAR.php';
require_once 'Crypt/CHAP.php';

define("GALL_ID", 2);
define("ANONYMOUS_ID", 3);
define("GADMIN_ID", 4);

class User extends DbObj
{
    var $fields = array(
        "id",
        "lastname",
        "firstname",
        "login",
        "password",
        "isgroup",
        "accounttype",
        "memberof",
        "expires",
        "passdelay",
        "status",
        "mail",
        "fid"
    );
    
    public $id;
    public $lastname;
    public $firstname;
    public $login;
    public $password;
    /**
     * @deprecated
     * @var string
     */
    public $isgroup;
    public $expires;
    public $passdelay;
    public $status;
    public $mail;
    public $fid;
    public $memberof;
    /**
     * @var string U|G|R
     */
    public $accounttype;
    /**
     * family identificator of user document default is IUSER/IGROUP
     * @var string
     */
    public $famid;
    /**
     * @var string new password
     */
    public $password_new;
    var $id_fields = array(
        "id"
    );
    
    var $dbtable = "users";
    
    var $order_by = "lastname, isgroup desc";
    
    var $fulltextfields = array(
        "login",
        "lastname",
        "firstname"
    );
    
    var $sqlcreate = "
create table users ( id      int not null,
                primary key (id),
                        lastname   text,
                        firstname  text,
                        login      text not null,
                        password   text not null,
                        isgroup    char,
                        accounttype char,
                        memberof   int[],
                        expires    int,
                        passdelay  int,
                        status     char,
                        mail       text,
                        fid int);
create index users_idx2 on users(lastname);
CREATE UNIQUE INDEX users_login on users (login);
create sequence seq_id_users start 10;";
    /** 
     * affect user from login name
     * @param string $login login
     * @return boolean true if ok
     */
    function setLoginName($login)
    {
        $login = trim(mb_strtolower($login));
        $query = new QueryDb($this->dbaccess, "User");
        $query->AddQuery("login='" . pg_escape_string($login) . "'");
        
        $list = $query->Query(0, 0, "TABLE");
        if ($query->nb > 0) {
            $this->Affect($list[0]);
            return true;
        }
        
        return false;
    }
    /**
     * affect user from its login
     *
     * @param string $login login
     * @deprecated
     * @return boolean true if ok
     */
    function setLogin($login, $unused = '0')
    {
        return $this->setLoginName($login);
    }
    /**
     * affect user from its document id
     *
     * @param int $fid
     * @return boolean true if ok
     */
    function setFid($fid)
    {
        $query = new QueryDb($this->dbaccess, "User");
        $query->AddQuery(sprintf("fid = %d", $fid));
        $list = $query->Query(0, 0, "TABLE");
        if ($query->nb != 0) {
            $this->Affect($list[0]);
        } else {
            return false;
        }
        return true;
    }
    
    function preInsert()
    {
        $err = '';
        if ($this->setloginName($this->login)) return _("this login exists");
        if ($this->login == "") return _("login must not be empty");
        if ($this->id == "") {
            $res = pg_exec($this->dbid, "select nextval ('seq_id_users')");
            $arr = pg_fetch_array($res, 0);
            $this->id = $arr["nextval"];
        }
        
        if (isset($this->isgroup) && ($this->isgroup == "Y")) {
            $this->password_new = "no"; // no passwd for group
            
        } else {
            $this->isgroup = "N";
        }
        
        $this->login = mb_strtolower($this->login);
        
        if (isset($this->password_new) && ($this->password_new != "")) {
            $this->computepass($this->password_new, $this->password);
            if ($this->id == 1) {
                $this->setAdminHtpasswd($this->password_new);
            }
        }
        //expires and passdelay
        $this->GetExpires();
        return $err;
    }
    
    function PostInsert()
    {
        //Add default group to user
        $group = new group($this->dbaccess);
        $group->iduser = $this->id;
        $gid = GALL_ID; //2 = default group
        $group->idgroup = $gid;
        // not added here it is added by freedom (generally)
        //    if (! $this->fid)   $group->Add();
        $err = $this->FreedomWhatUser();
        return $err;
    }
    
    function postUpdate()
    {
        return $this->FreedomWhatUser();
    }
    
    function preUpdate()
    {
        if (isset($this->password_new) && ($this->password_new != "")) {
            
            $this->computepass($this->password_new, $this->password);
            if ($this->id == 1) {
                $this->setAdminHtpasswd($this->password_new);
            }
        }
        //expires and passdelay
        $this->GetExpires();
    }
    
    function postDelete()
    {
        
        include_once ("WHAT/Class.Session.php");
        // delete reference in group table
        $group = new Group($this->dbaccess, $this->id);
        $ugroups = $group->groups;
        $err = $group->Delete();
        if ($err == "") {
            
            refreshGroups($ugroups, true);
        }
        
        global $action;
        $action->session->CloseUsers($this->id);
        
        return $err;
    }
    /**
     * @deprecated
     * @param $login
     * @param $domain
     * @param $whatid
     * @return bool
     */
    function CheckLogin($login, $unused, $whatid)
    {
        $query = new QueryDb($this->dbaccess, "User");
        
        $query->basic_elem->sup_where = array(
            "login='" . pg_escape_string($login) . "'"
        );
        
        $list = $query->Query();
        if ($query->nb == 0 or ($query->nb == 1 and $list[0]->id == $whatid)) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * return display name of a user
     * @param int $uid user identificator
     * @return string|null firstname and lastname or false if not found
     */
    static function getDisplayName($uid)
    {
        static $tdn = array();
        
        $uid = intval($uid);
        if ($uid > 0) {
            if (isset($tdn[$uid])) return $tdn[$uid];
            $dbid = getDbId(getDbAccess());
            $res = pg_exec($dbid, "select firstname, lastname  from users where id=$uid");
            if (pg_num_rows($res) > 0) {
                $arr = pg_fetch_array($res, 0);
                if ($arr["firstname"]) $tdn[$uid] = $arr["firstname"] . ' ' . $arr["lastname"];
                else $tdn[$uid] = $arr["lastname"];
                return $tdn[$uid];
            }
            return null;
        }
        return null;
    }
    /**
     * return system user identificator from user document reference
     * @static
     * @param $fid
     * @return int
     */
    static function getUidFromFid($fid)
    {
        $uid = 0;
        if ($fid) {
            simpleQuery('', sprintf("select id from users where fid=%d", $fid) , $uid, true, true);
        }
        return $uid;
    }
    /**
     * update user from IUSER document
     * @deprecated replace by updateUser
     * @param int $fid document id
     * @param string $login login
     */
    function setUsers($fid, $lname, $fname, $expires, $passdelay, $login, $status, $pwd1, $pwd2, $unused = '', $extmail = '')
    {
        return $this->updateUser($fid, $lname, $fname, $expires, $passdelay, $login, $status, $pwd1, $pwd2, $extmail);
    }
    /**
     * update user from IUSER document
     * @param int $fid document id
     * @param string $lname  last name
     * @param string $fname first name
     * @param string $expires expiration date
     * @param int $passdelay password delay
     * @param string $login login
     * @param string $status 'A' (Activate) , 'D' (Desactivated)
     * @param string $pwd1 password one
     * @param string $pwd2 password two
     * @param string $extmail mail address
     * @return string error message
     */
    function updateUser($fid, $lname, $fname, $expires, $passdelay, $login, $status, $pwd1, $pwd2, $extmail = '')
    {
        
        $this->lastname = $lname;
        $this->firstname = $fname;
        $this->status = $status;
        if ($this->login == "") $this->login = $login;
        //don't modify password in database even if force constraint
        if ($pwd1 == $pwd2 and $pwd1 <> "") {
            $this->password_new = $pwd2;
        }
        
        if ($extmail != "") {
            $this->mail = trim($extmail);
        } else {
            $this->mail = $this->getMail();
        }
        
        if ($expires > 0) $this->expires = $expires;
        if ($passdelay > 0) $this->passdelay = $passdelay;
        elseif ($passdelay == - 1) { // suppress expire date
            $this->expires = 0;
            $this->passdelay = 0;
        }
        
        $this->fid = $fid;
        if (!$this->isAffected()) {
            $err = $this->Add();
        } else {
            $err = $this->Modify();
        }
        
        return $err;
    }
    /**
     * update user from FREEDOM IGROUP document
     * @param int $fid document id
     * @param string $gname group name
     * @param string $login login
     */
    function setGroups($fid, $gname, $login)
    {
        if ($gname != "") $this->lastname = $gname;
        if (($this->login == "") && ($login != "")) $this->login = $login;
        
        $this->mail = $this->getMail();
        $this->fid = $fid;
        if (!$this->isAffected()) {
            $this->isgroup = "Y";
            $this->accounttype = 'G';
            $err = $this->Add();
        } else {
            $err = $this->Modify();
        }
        
        return $err;
    }
    //Add and Update expires and passdelay for password
    //Call in PreUpdate and PreInsert
    function getExpires()
    {
        if (intval($this->passdelay) == 0) {
            $this->expires = "0";
            $this->passdelay = "0";
        } // neither expire
        else if (intval($this->expires) == 0) {
            $this->expires = time() + $this->passdelay;
        }
    }
    
    function FreedomWhatUser()
    {
        $err = '';
        $dbaccess = GetParam("FREEDOM_DB");
        if ($dbaccess == "") return _("no freedom DB access");
        if ($this->fid <> "") {
            /**
             * @var _IUSER $iuser
             */
            $iuser = new_Doc($dbaccess, $this->fid);
            
            $err = $iuser->RefreshDocUser();
        } //Update from what
        else {
            include_once ("FDL/Lib.Dir.php");
            if ($this->famid != "") $fam = $this->famid;
            elseif ($this->isgroup == "Y") $fam = "IGROUP";
            else $fam = "IUSER";;
            $filter = array(
                "us_whatid = '" . $this->id . "'"
            );
            $tdoc = getChildDoc($dbaccess, 0, 0, "ALL", $filter, 1, "LIST", $fam);
            if (count($tdoc) == 0) {
                //Create a new doc IUSER
                $iuser = createDoc($dbaccess, $fam);
                $iuser->SetValue("US_WHATID", $this->id);
                $iuser->Add();
                $this->fid = $iuser->id;
                $this->modify(true, array(
                    'fid'
                ) , true);
                $err = $iuser->RefreshDocUser();
            } else {
                $this->fid = $tdoc[0]->id;
                $this->modify(true, array(
                    'fid'
                ) , true);
                $err = $tdoc[0]->RefreshDocUser();
            }
        }
        return $err;
    }
    // --------------------------------------------------------------------
    function computepass($pass, &$passk)
    {
        $salt_space = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ./";
        srand((double)microtime() * 1000000);
        $salt = $salt_space[rand(0, strlen($salt_space) - 1) ];
        $salt.= $salt_space[rand(0, strlen($salt_space) - 1) ];
        $passk = crypt($pass, $salt);
    }
    
    function checkpassword($pass)
    {
        if ($this->isgroup == 'Y') return false; // don't log in group
        return ($this->checkpass($pass, $this->password));
    }
    // --------------------------------------------------------------------
    function checkpass($pass, $passk)
    {
        $salt = substr($passk, 0, 2);
        $passres = crypt($pass, $salt);
        return ($passres == $passk);
    }
    /**
     * return mail adress
     * @param bool $reinit recompute adress from mail account
     * @return string mail address empty if no mail
     */
    function getMail()
    {
        return $this->mail;
    }
    function PostInit()
    {
        
        $group = new group($this->dbaccess);
        // Create admin user
        $this->id = 1;
        $this->lastname = "Master";
        $freedomctx = getFreedomContext();
        if ($freedomctx == "") $this->firstname = "Dynacase Platform";
        else $this->firstname = ucfirst("$freedomctx");
        $this->password_new = "anakeen";
        $this->login = "admin";
        $this->Add(true);
        $group->iduser = $this->id;
        // Create default group
        $this->id = GALL_ID;
        $this->lastname = "Utilisateurs";
        $this->firstname = "";
        $this->login = "all";
        $this->isgroup = "Y";
        $this->accounttype = "G";
        $this->Add(true);
        $group->idgroup = $this->id;
        $group->Add(true);
        // Create anonymous user
        $this->id = ANONYMOUS_ID;
        $this->lastname = "anonymous";
        $this->firstname = "guest";
        $this->login = "anonymous";
        $this->isgroup = "N";
        $this->accounttype = "G";
        $this->Add(true);
        // Create admin group
        $this->id = GADMIN_ID;
        $this->lastname = "Administrateurs";
        $this->firstname = "";
        $this->login = "gadmin";
        $this->isgroup = "Y";
        $this->accounttype = "G";
        $this->Add(true);
        $group->idgroup = GALL_ID;
        $group->iduser = GADMIN_ID;
        $group->Add(true);
        // Store error messages
        
    }
    // get All Users (not group)
    function getUserList($qtype = "LIST", $start = 0, $slice = 0, $filteruser = false)
    {
        $query = new QueryDb($this->dbaccess, "User");
        $query->order_by = "lastname";
        $query->AddQuery("(accountType='U')");
        if ($filteruser) $query->AddQuery("(login ~* '" . pg_escape_string($filteruser) . "')" . " or " . "(lastname ~* '" . pg_escape_string($filteruser) . "')");
        return ($query->Query($start, $slice, $qtype));
    }
    // get All groups
    function getGroupList($qtype = "LIST")
    {
        $query = new QueryDb($this->dbaccess, "User");
        $query->order_by = "lastname";
        $query->AddQuery("(accountType='G')");
        return ($query->Query(0, 0, $qtype));
    }
    // get All users & groups
    function getUserAndGroupList($qtype = "LIST")
    {
        $query = new QueryDb($this->dbaccess, "User");
        $query->AddQuery("(accountType='G' or accountType='U')");
        
        $query->order_by = "isgroup desc, lastname";
        return ($query->Query(0, 0, $qtype));
    }
    /**
     * get All ascendant group ids of the user object
     */
    function getGroupsId()
    {
        $query = new QueryDb($this->dbaccess, "Group");
        $query->AddQuery("iduser='{$this->id}'");
        
        $list = $query->Query(0, 0, "TABLE");
        $groupsid = array();
        
        if ($query->nb > 0) {
            while (list($k, $v) = each($list)) {
                $groupsid[$v["idgroup"]] = $v["idgroup"];
            }
        }
        
        return $groupsid;
    }
    /**
     * for group :: get All user & groups ids in all descendant(recursive);
     * @param int $id group identificator
     * @return array of user array
     */
    function getRUsersList($id, $r = array())
    {
        $query = new QueryDb($this->dbaccess, "User");
        $list = $query->Query(0, 0, "TABLE", "select users.* from users, groups where " . "groups.iduser=users.id and " . "idgroup=$id ;");
        
        $uid = array();
        
        if ($query->nb > 0) {
            foreach ($list as $k => $v) {
                $uid[$v["id"]] = $v;
                if ($v["isgroup"] == "Y") {
                    if (!in_array($v["id"], $r)) {
                        array_push($r, $v["id"]);
                        $uid+= $this->GetRUsersList($v["id"], $r);
                    }
                }
            }
        }
        
        return $uid;
    }
    /**
     * for group :: get All direct user & groups ids
     * @param int $id group identificator
     * @param bool $onlygroup set to true if you want only child groups
     */
    function getUsersGroupList($gid, $onlygroup = false)
    {
        $query = new QueryDb($this->dbaccess, "User");
        $optgroup = '';
        if ($onlygroup) $optgroup = " and users.isgroup='Y' ";
        
        $list = $query->Query(0, 0, "TABLE", "select users.* from users, groups where " . "groups.iduser=users.id and " . "idgroup=$gid $optgroup;");
        
        $uid = array();
        if ($query->nb > 0) {
            foreach ($list as $k => $v) {
                $uid[$v["id"]] = $v;
            }
        }
        
        return $uid;
    }
    /**
     * return all user members (recursive)
     * @return array of user values ["login"=>, "id"=>, "fid"=>,...)
     */
    public function getUserMembers()
    {
        $tr = array();
        
        $g = new Group($this->dbaccess);
        $lg = $g->getChildsGroupId($this->id);
        $lg[] = $this->id;
        $cond = getSqlCond($lg, "idgroup", true);
        if (!$cond) $cond = "true";
        
        $condname = "";
        
        $sort = 'lastname';
        $sql = sprintf("SELECT distinct on (%s, users.id) users.id, users.login, users.firstname , users.lastname, users.mail,users.fid from users, groups where %s and (groups.iduser=users.id) %s and accounttype='U' order by %s", $sort, $cond, $condname, $sort);
        
        $err = simpleQuery($this->dbaccess, $sql, $result);
        if ($err != "") return $err;
        return $result;
    }
    /**
     * update memberof fields with all group/role of user
     * @return array
     * @throws Exception
     */
    public function updateMemberOf()
    {
        
        $err = simpleQuery($this->dbaccess, sprintf("select idgroup from groups where iduser=%d", $this->id) , $gids, true, false);
        
        $g = new Group($this->dbaccess);
        $lg = $gids;
        foreach ($gids as $gid) {
            $lg = array_merge($g->getParentsGroupId($gid) , $lg);
        }
        //$lg[] = $this->id;
        $lg = array_values(array_unique($lg));
        $this->memberof = '{' . implode(',', $lg) . '}';
        $err = $this->modify(false, array(
            'memberof'
        ) , true);
        if ($err) throw new Exception($err);
        return $lg;
    }
    /**
     * return id of group/role id
     * @return array
     */
    public function getMemberOf()
    {
        $memberOf = array();
        if (strlen($this->memberof) > 2) {
            $memberOf = explode(',', substr($this->memberof, 1, -1));
        }
        return $memberOf;
    }
    /**
     * return list of account (group/role) member for a user
     * return null if user not exists
     * @static
     * @param int $uid user identificator
     * @return array|null
     */
    public static function getUserMemberOf($uid)
    {
        global $action;
        $memberOf = array();
        if ($action->user->id == $uid) {
            $memberOf = $action->user->getMemberOf();
        } else {
            $u = new User('', $uid);
            if ($u->isAffected()) {
                $memberOf = $u->getMemberOf();
            } else {
                return null;
            }
        }
        return $memberOf;
    }
    /**
     * verify if user is member of group (recursive)
     * @return boolean
     */
    public function isMember($uid)
    {
        $tr = array();
        
        $g = new Group($this->dbaccess);
        $lg = $g->getChildsGroupId($this->id);
        $lg[] = $this->id;
        $cond = getSqlCond($lg, "idgroup", true);
        if (!$cond) $cond = "true";
        
        $sql = sprintf("select users.id from users, groups where %s and (groups.iduser=users.id) and users.id=%d and isgroup != 'Y'", $cond, $uid);
        
        $err = simpleQuery($this->dbaccess, $sql, $result, true, true);
        
        return ($result != '');
    }
    // only use for group
    // get user member of group
    function getGroupUserList($qtype = "LIST", $withgroup = false)
    {
        $query = new QueryDb($this->dbaccess, "User");
        $query->order_by = "isgroup desc, lastname";
        $selgroup = "and (isgroup != 'Y' or isgroup is null)";
        if ($withgroup) $selgroup = "";
        return ($query->Query(0, 0, $qtype, "select users.* from users, groups where " . "groups.iduser=users.id and " . "idgroup={$this->id} {$selgroup};"));
    }
    /**
     * Get user token for open access
     * @param int $expire set expiration delay in seconds (false if nether expire)
     * @param bool $oneshot set to true to use one token is consumed/deleted when used
     */
    function getUserToken($expire = false, $oneshot = false, $context = array())
    {
        if ($expire === false) {
            $expire = 3600 * 24 * 365 * 20;
        }
        if ($context && (count($context) > 0)) {
            $scontext = serialize($context);
        } else $scontext = '';
        
        if (!$this->isAffected()) return false;
        include_once ('WHAT/Class.UserToken.php');
        include_once ('WHAT/Class.QueryDb.php');
        $create = false;
        $tu = array();
        if (!$oneshot) {
            $q = new QueryDb($this->dbaccess, "UserToken");
            $q->addQuery("userid=" . $this->id);
            if ($scontext) $q->addQuery("context='" . pg_escape_string($scontext) . "'");
            $tu = $q->Query(0, 0, "TABLE");
            $create = ($q->nb == 0);
        } else {
            $create = true;
        }
        
        if ($create) {
            // create one
            $uk = new UserToken("");
            $uk->deleteExpired();
            $uk->userid = $this->id;
            $uk->token = $uk->genToken();
            $uk->expire = $uk->setExpiration($expire);
            $uk->expendable = $oneshot;
            $uk->context = $scontext;
            $err = $uk->add();
            $token = $uk->token;
        } else {
            $token = $tu[0]["token"];
        }
        return $token;
    }
    /**
     * Set password for the admin account in the `admin' subdir
     * @param string $admin_passwd the password
     */
    function setAdminHtpasswd($admin_passwd)
    {
        include_once ('WHAT/Lib.Prefix.php');
        
        global $pubdir;
        
        if ($this->id != 1) {
            $err = sprintf("Method %s can only be used on the admin user.", __FUNCTION__);
            return $err;
        }
        
        $adminDir = $pubdir . DIRECTORY_SEPARATOR . 'admin';
        $tmpFile = @tempnam($adminDir, '.htpasswd');
        if ($tmpFile === false) {
            $err = sprintf("Error creating temporary file in '%s'.", $adminDir);
            return $err;
        }
        if (chmod($tmpFile, 0600) === false) {
            $err = sprintf("Error setting mode 0600 on temporary file '%s'.", $tmpFile);
            unlink($tmpFile);
            return $err;
        }
        $passwdLine = sprintf("%s:{SHA}%s", 'admin', base64_encode(sha1($admin_passwd, true)));
        if (file_put_contents($tmpFile, $passwdLine) === false) {
            $err = sprintf("Error writing to temporary file '%s'.", $tmpFile);
            unlink($tmpFile);
            return $err;
        }
        $htpasswdFile = $adminDir . DIRECTORY_SEPARATOR . '.htpasswd';
        if (rename($tmpFile, $htpasswdFile) === false) {
            $err = sprintf("Error renaming temporary file '%s' to '%s'.", $tmpFile, $htpasswdFile);
            unlink($tmpFile);
            return $err;
        }
        return '';
    }
}
?>
