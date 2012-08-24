<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Users Definition
 *
 * @author Anakeen
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

define("GALL_ID", 2);
define("ANONYMOUS_ID", 3);
define("GADMIN_ID", 4);
/**
 * Manage User, Group and Role account object
 * @class Account
 */
class Account extends DbObj
{
    var $fields = array(
        "id",
        "lastname",
        "firstname",
        "login",
        "password",
        "substitute",
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
     * @var int the substitute account identifier
     */
    public $substitute;
    /**
     * family identifier of user document default is IUSER/IGROUP
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
                        substitute      int,
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
     * affect account from login name
     * @param string $login login
     * @return bool true if ok
     */
    function setLoginName($login)
    {
        $login = trim(mb_strtolower($login));
        $query = new QueryDb($this->dbaccess, "Account");
        $query->AddQuery("login='" . pg_escape_string($login) . "'");
        
        $list = $query->Query(0, 0, "TABLE");
        if ($query->nb > 0) {
            $this->Affect($list[0]);
            return true;
        }
        return false;
    }
    /**
     * return substitute account
     * return null if no susbtitute
     * @return Account|null
     */
    public function getSubstitute()
    {
        if ($this->isAffected()) {
            if ($this->substitute) {
                return new Account($this->dbaccess, $this->substitute);
            }
        }
        return null;
    }
    /**
     * return incumbent ids account list (accounts which has this account as substitute)
     * @param bool $returnSystemIds set to true to return system account id, false to return document user id
     * @return int[]
     */
    public function getIncumbents($returnSystemIds = true)
    {
        $incumbents = array();
        if ($this->isAffected()) {
            $sql = sprintf("select %s from users where substitute=%d;", $returnSystemIds ? 'id' : 'fid', $this->id);
            simpleQuery($this->dbaccess, $sql, $incumbents, true, false);
        }
        return $incumbents;
    }
    /**
     * set substitute to this user
     * this user become the incumbent of $substitute
     * @param string $substitute login or user system id
     * @return string error message (empty if not)
     */
    public function setSubstitute($substitute)
    {
        $err = '';
        if (!$this->isAffected()) {
            $err = sprintf(_("cannot set substitute account object not affected"));
        }
        if ($err) return $err;
        if ($substitute) {
            if (!(is_numeric($substitute))) {
                $sql = sprintf("select id from users where login = '%s'", pg_escape_string($substitute));
                simpleQuery($this->dbaccess, $sql, $substituteId, true, true);
                if ($substituteId) $substitute = $substituteId;
                else $err = sprintf(_("cannot set substitute %s login not found") , $substitute);
            }
            if ($err) return $err;
            $sql = sprintf("select id from users where id = '%d'", $substitute);
            simpleQuery($this->dbaccess, $sql, $substituteId, true, true);
            if (!$substituteId) $err = sprintf(_("cannot set substitute %s id not found") , $substitute);
        }
        if ($err) return $err;
        if ($this->substitute == $this->id) {
            $err = sprintf(_("cannot substitute itself"));
        }
        if ($err) return $err;
        $oldSubstitute = $this->substitute;
        $this->substitute = $substitute;
        
        $err = $this->modify();
        if (!$err) {
            $u = new \Account($this->dbaccess, $this->substitute);
            $u->updateMemberOf();
            if ($oldSubstitute) {
                $u->select($oldSubstitute);
                $u->updateMemberOf();
            }
            
            global $action;
            if ($action->user->id == $u->id) $action->user->revert();
        }
        return $err;
    }
    /**
     * affect account from its login
     *
     * @param string $login login
     * @deprecated
     * @return bool true if ok
     */
    function setLogin($login, $unused = '0')
    {
        return $this->setLoginName($login);
    }
    /**
     * affect account from its document id
     *
     * @param int $fid
     * @return bool true if ok
     */
    function setFid($fid)
    {
        $query = new QueryDb($this->dbaccess, "Account");
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
        if ((!$this->login) && $this->accounttype == 'R') {
            // compute auto role reference
            $this->login = uniqid('role');
        }
        
        if ($this->setloginName($this->login)) return _("this login exists");
        if ($this->login == "") return _("login must not be empty");
        $this->login = mb_strtolower($this->login);
        if ($this->id == "") {
            $res = pg_query($this->dbid, "select nextval ('seq_id_users')");
            $arr = pg_fetch_array($res, 0);
            $this->id = $arr["nextval"];
        }
        
        if (($this->accounttype == 'G') || ($this->accounttype == 'R') || ($this->isgroup == "Y")) {
            if ((!$this->accounttype) && ($this->isgroup == "Y")) $this->accounttype = 'G';
            $this->password_new = uniqid($this->accounttype); // no passwd for group,role
            
        } else {
            $this->isgroup = "N";
        }
        if (!$this->accounttype) $this->accounttype = 'U';
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
        $err = $this->synchroAccountDocument();
        return $err;
    }
    
    function postUpdate()
    {
        return $this->synchroAccountDocument();
    }
    
    function preUpdate()
    {
        if (isset($this->password_new) && ($this->password_new != "")) {
            
            $this->computepass($this->password_new, $this->password);
            if ($this->id == 1) {
                $this->setAdminHtpasswd($this->password_new);
            }
        }
        
        $this->login = mb_strtolower($this->login);
        //expires and passdelay
        $this->GetExpires();
    }
    
    function postDelete()
    {
        include_once ("WHAT/Class.Session.php");
        include_once ("FDL/Lib.Usercard.php");
        $err = '';
        $group = new Group($this->dbaccess, $this->id);
        $ugroups = $group->groups;
        // delete reference in group table
        $sql = sprintf("delete from groups where iduser=%d or idgroup=%d", $this->id, $this->id);
        simpleQuery($this->dbaccess, $sql);
        
        refreshGroups($ugroups, true);
        
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
        $query = new QueryDb($this->dbaccess, "Account");
        
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
     * @param int $uid user identifier
     * @return string|null firstname and lastname or null if not found
     */
    static function getDisplayName($uid)
    {
        static $tdn = array();
        
        $uid = intval($uid);
        if ($uid > 0) {
            if (isset($tdn[$uid])) return $tdn[$uid];
            $dbid = getDbId(getDbAccess());
            $res = pg_query($dbid, "select firstname, lastname  from users where id=$uid");
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
     * return system user identifier from user document reference
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
     * @param array $roles
     * @param int $substitute system substitute id
     * @return string error message
     */
    function updateUser($fid, $lname, $fname, $expires, $passdelay, $login, $status, $pwd1, $pwd2, $extmail = '', array $roles = array(-1
    ) , $substitute = - 1)
    {
        $this->lastname = $lname;
        $this->firstname = $fname;
        $this->status = $status;
        if ($login != "") $this->login = $login;
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
        if ($roles != array(-1
        )) {
            $err.= $this->setRoles($roles);
        } else {
            $this->updateMemberOf();
        }
        
        if ((!$err) && ($substitute > - 1)) {
            $err = $this->setSubstitute($substitute);
        }
        return $err;
    }
    /**
     * update user from FREEDOM IGROUP document
     * @param int $fid document id
     * @param string $gname group name
     * @param string $login login
     * @param array $roles system role ids
     */
    function setGroups($fid, $gname, $login, array $roles = array(-1
    ))
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
        if ($roles != array(-1
        )) {
            $err.= $this->setRoles($roles);
        } else {
            $this->updateMemberOf();
        }
        return $err;
    }
    /**
     * revert values from database
     */
    public function revert()
    {
        if ($this->isAffected()) {
            $this->select($this->id);
        }
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
    
    function synchroAccountDocument()
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
            elseif ($this->accounttype == "G") $fam = "IGROUP";
            elseif ($this->accounttype == "R") $fam = "ROLE";
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
        $salt = '';
        $salt_space = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ./";
        srand((double)microtime() * 1000000);
        for ($i = 0; $i < 16; $i++) $salt.= $salt_space[rand(0, strlen($salt_space) - 1) ];
        $passk = crypt($pass, "\$5\${$salt}");
    }
    /**
     * @param string $pass clear password to test
     * @return bool
     */
    function checkpassword($pass)
    {
        if ($this->accounttype != 'U') return false; // don't log in group or role
        return ($this->checkpass($pass, $this->password));
    }
    // --------------------------------------------------------------------
    function checkpass($pass, $passk)
    {
        if (substr($passk, 0, 3) != '$5$') {
            /* Old DES crypted passwords => SSHA256 crypting*/
            $salt = substr($passk, 0, 2);
            $passres = crypt($pass, $salt);
            if ($passres == $passk) {
                $this->computepass($pass, $this->password);
                $err = $this->modify(true, array(
                    'password'
                ) , true);
                if ($err == '') {
                    if ($this->id == 1) $this->setAdminHtpasswd($pass);
                    $log = new Log("", "Session", "Authentication");
                    $facility = constant(getParam("AUTHENT_LOGFACILITY", "LOG_AUTH"));
                    $log->wlog("S", sprintf('User %s password crypted with salted SHA256 algorithm.', $this->login) , NULL, $facility);
                }
            }
        } else {
            $salt = substr($passk, 3, 19);
            $passres = crypt($pass, "\$5\${$salt}");
        }
        return ($passres == $passk);
    }
    /**
     * return mail adress
     * @param bool $rawmail set to false to have long mail with firstname and lastname
     * @return string mail address empty if no mail
     */
    function getMail($rawmail = true)
    {
        if ($this->accounttype == 'U') {
            if (!$this->mail) return '';
            if ($rawmail) {
                return $this->mail;
            } else {
                $dn = trim($this->firstname . ' ' . $this->lastname);
                $mail = sprintf('"%s" <%s>', str_replace('"', '-', $dn) , $this->mail);
                return $mail;
            }
        } else {
            $sql = sprintf("with recursive amembers(uid) as (
 select iduser, users.login, users.mail from groups,users where idgroup = %d and users.id=groups.iduser
union
 select iduser, users.login, users.mail from groups,users, amembers where groups.idgroup = amembers.uid and users.id=groups.iduser
) select users.firstname, users.lastname, users.mail from amembers, users where users.id=amembers.uid and users.accounttype='U' and users.mail is not null order by users.mail;", $this->id);
            simpleQuery($this->dbaccess, $sql, $umail);
            $tMail = array();
            if ($rawmail) {
                foreach ($umail as $aMail) {
                    
                    $tMail[] = $aMail["mail"];
                }
                $tMail = array_unique($tMail);
            } else {
                foreach ($umail as $aMail) {
                    $dn = trim($aMail["firstname"] . ' ' . $aMail["lastname"]);
                    $tMail[] = sprintf('"%s" <%s>', str_replace('"', '-', $dn) , $aMail["mail"]);
                }
            }
            return implode(', ', $tMail);
        }
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
        $this->accounttype = "U";
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
    /**
     * get the first incumbent which has $acl privilege
     * @param Action $action
     * @param Doc $doc document to verify
     * @param string $acl document acl name
     * @return string incumbent's name which has privilege
     */
    function getIncumbentPrivilege(Doc & $doc, $acl)
    {
        if ($this->id == 1) return '';
        if ($incumbents = $this->getIncumbents()) {
            if ($doc->control($acl, true) != '') {
                foreach ($incumbents as $aIncumbent) {
                    $eErr = $doc->controlUserId($doc->profid, $aIncumbent, $acl);
                    if (!$eErr) return Account::getDisplayName($aIncumbent);
                }
            }
        }
        return '';
    }
    /**
     * get All Users (not group not role)
     * @static
     * @param string $qtype return type LIST|TABLE|ITEM
     * @param int $start
     * @param int $slice
     * @param string $filteruser keyword to filter user on login or lastname
     * @return array
     */
    public static function getUserList($qtype = "LIST", $start = 0, $slice = 0, $filteruser = '')
    {
        $query = new QueryDb(getDbAccess() , "Account");
        $query->order_by = "lastname";
        $query->AddQuery("(accountType='U')");
        if ($filteruser) $query->AddQuery("(login ~* '" . pg_escape_string($filteruser) . "')" . " or " . "(lastname ~* '" . pg_escape_string($filteruser) . "')");
        return ($query->Query($start, $slice, $qtype));
    }
    /**
     * get All groups
     * @param string $qtype return type LIST|TABLE|ITEM
     * @return array
     */
    public static function getGroupList($qtype = "LIST")
    {
        $query = new QueryDb(getDbAccess() , "Account");
        $query->order_by = "lastname";
        $query->AddQuery("(accountType='G')");
        $l = $query->Query(0, 0, $qtype);
        return ($query->nb > 0) ? $l : array();
    }
    /**
     * get All Roles
     * @param string $qtype return type LIST|TABLE|ITEM
     * @return array
     */
    public static function getRoleList($qtype = "LIST")
    {
        $query = new QueryDb(getDbAccess() , "Account");
        $query->order_by = "lastname";
        $query->AddQuery("(accountType='R')");
        $l = $query->Query(0, 0, $qtype);
        return ($query->nb > 0) ? $l : array();
    }
    /**
     * get All users & groups (except role)
     * @param string $qtype return type LIST|TABLE|ITEM
     * @return array
     */
    public static function getUserAndGroupList($qtype = "LIST")
    {
        $query = new QueryDb(getDbAccess() , "Account");
        $query->AddQuery("(accountType='G' or accountType='U')");
        
        $query->order_by = "accounttype, lastname";
        return ($query->Query(0, 0, $qtype));
    }
    /**
     * get All ascendant group ids of the user object
     */
    function getGroupsId()
    {
        
        $sql = sprintf("select idgroup from groups, users where groups.idgroup=users.id and users.accounttype='G' and groups.iduser=%d", $this->id);
        simpleQuery($this->dbaccess, $sql, $groupsid, true, false);
        return $groupsid;
    }
    /**
     * for group :: get All user & groups ids in all descendant(recursive);
     * @param int $id group identifier
     * @return array of account array
     */
    function getRUsersList($id, $r = array())
    {
        $query = new QueryDb($this->dbaccess, "Account");
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
     * @param int $id group identifier
     * @param bool $onlygroup set to true if you want only child groups
     */
    function getUsersGroupList($gid, $onlygroup = false)
    {
        $query = new QueryDb($this->dbaccess, "Account");
        $optgroup = '';
        if ($onlygroup) $optgroup = " and users.accounttype='G' ";
        
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
    private function getUserMembers()
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
     * return all group (recursive) /role of user
     * @param string $accountFilter G|R to indicate if want only group or only role
     * @return array of users characteristics
     */
    public function getUserParents($accountFilter = '')
    {
        $acond = '';
        if ($accountFilter) {
            $acond = sprintf("and users.accounttype='%s'", pg_escape_string($accountFilter));
        }
        $sql = sprintf("with recursive agroups(gid) as (
 select idgroup from groups,users where iduser = %d and users.id=groups.idgroup
union
 select idgroup from groups,users, agroups where groups.iduser = agroups.gid and users.id=groups.idgroup
) select users.* from agroups, users where users.id=agroups.gid %s order by lastname", $this->id, $acond);
        simpleQuery($this->dbaccess, $sql, $parents);
        return $parents;
    }
    /**
     * get memberof for user without substitutes
     * @param int $uid if not set it is the current account object else use another account identifier
     * @return array
     * @throws Dcp\Exception
     */
    public function getStrictMemberOf($uid = - 1)
    {
        if ($uid == - 1) $uid = $this->id;
        if (!$uid) return array();
        // get all ascendants groupe,role of a user
        $sql = sprintf("with recursive agroups(gid, login, actype) as (
 select idgroup, users.login, users.accounttype from groups,users where iduser = %d and users.id=groups.idgroup
   union
 select idgroup, users.login, users.accounttype from groups,users, agroups where groups.iduser = agroups.gid and users.id=groups.idgroup
) select gid from agroups;", $uid);
        
        simpleQuery($this->dbaccess, $sql, $gids, true, false);
        return $gids;
    }
    /**
     * update memberof fields with all group/role of user
     * @param bool $updateSubstitute also update substitute by default
     * @return array of memberof identificators
     * @throws Dcp\Exception
     */
    public function updateMemberOf($updateSubstitute = true)
    {
        if (!$this->id) return array();
        
        $lg = $this->getStrictMemberOf();
        // search incumbents
        $sql = sprintf("select id from users where substitute=%d;", $this->id);
        simpleQuery($this->dbaccess, $sql, $incumbents, true, false);
        foreach ($incumbents as $aIncumbent) {
            $lg[] = $aIncumbent;
            // use strict no propagate substitutes
            $lg = array_merge($lg, $this->getStrictMemberOf($aIncumbent));
        }
        
        $lg = array_values(array_unique($lg));
        $this->memberof = '{' . implode(',', $lg) . '}';
        $err = $this->modify(true, array(
            'memberof'
        ) , true);
        if ($err) throw new Dcp\Exception($err);
        if ($updateSubstitute && $this->substitute) {
            $u = new Account($this->dbaccess, $this->substitute);
            $u->updateMemberOf(false);
        }
        
        return $lg;
    }
    /**
     * return id of group/role id
     * @param bool $useSystemId set to false to return document id instead of system id
     * @return array
     */
    public function getMemberOf($useSystemId = true)
    {
        $memberOf = array();
        if (strlen($this->memberof) > 2) {
            $memberOf = explode(',', substr($this->memberof, 1, -1));
        }
        if (!$useSystemId) {
            simpleQuery($this->dbaccess, sprintf("select fid from users where id in (%s)", implode(',', $memberOf)) , $dUids, true);
            return $dUids;
        }
        return $memberOf;
    }
    /**
     * return list of account (group/role) member for a user
     * return null if user not exists
     * @static
     * @param int $uid user identifier
     * @return array|null
     */
    public static function getUserMemberOf($uid, $strict = false)
    {
        global $action;
        $memberOf = array();
        if ($action->user->id == $uid) {
            if ($strict) $memberOf = $action->user->getStrictMemberOf();
            else $memberOf = $action->user->getMemberOf();
        } else {
            $u = new Account('', $uid);
            if ($u->isAffected()) {
                if ($strict) $memberOf = $u->getStrictMemberOf();
                else $memberOf = $u->getMemberOf();
            } else {
                return null;
            }
        }
        return $memberOf;
    }
    /**
     * verify if user is member of group (recursive)
     * @return bool
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
    /**
     * only use with group or role
     * if it is a group : get all direct user member of a group
     * if it is a role : het user which has role directly
     * @param string $qtype LIST|TABLE|ITEM
     * @param bool $withgroup set to true to return sub group also
     * @param int|string $limit max users returned
     * @return array of user properties
     */
    function getGroupUserList($qtype = "LIST", $withgroup = false, $limit = "all")
    {
        $query = new QueryDb($this->dbaccess, "Account");
        $query->order_by = "accounttype desc, lastname";
        $selgroup = "and (accounttype='U')";
        if ($withgroup) $selgroup = "";
        return ($query->Query(0, $limit, $qtype, "select users.* from users, groups where " . "groups.iduser=users.id and " . "idgroup={$this->id} {$selgroup};"));
    }
    /**
     * get all users of a group/role direct or indirect
     * @param int|string $limit max users returned
     * @param bool $onlyUsers set to true to have also sub groups
     * @return array of user properties
     */
    function getAllMembers($limit = "all", $onlyUsers = true)
    {
        if ($limit != 'all') $limit = intval($limit);
        if ($onlyUsers) {
            $sql = sprintf("select * from users where memberof && '{%d}' and accounttype='U' order by lastname limit %s", $this->id, $limit);
        } else {
            $sql = sprintf("select * from users where memberof && '{%d}' order by accounttype, lastname limit %s", $this->id, $limit);
        }
        simpleQuery($this->dbaccess, $sql, $users);
        return $users;
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
    /**
     * add a role to a user/group
     * @param string $idRole system identicator or reference role (login)
     * @return string error message
     */
    public function addRole($idRole)
    {
        if (!$this->isAffected()) return ErrorCode::getError("ACCT0002", $idRole);
        if ($this->accounttype != 'U') return ErrorCode::getError("ACCT0003", $idRole, $this->login);
        if (!is_numeric($idRole)) {
            simpleQuery($this->dbaccess, sprintf("select id from users where login = '%'", pg_escape_string($idRole)) , $idRoleW, true, true);
            if ($idRoleW) $idRole = $idRoleW;
        }
        if (!is_numeric($idRole)) {
            return ErrorCode::getError("ACCT0001", $idRole, $this->login);
        }
        $g = new group($this->dbaccess);
        $g->idgroup = $idRole;
        $g->iduser = $this->id;
        $err = $g->add();
        if ($err == 'OK') {
            $err = '';
            $this->updateMemberOf();
        }
        return $err;
    }
    /**
     * set role set to a user/group
     * @param array $roleIds system identicators or reference roles (login)
     * @return string error message
     */
    public function setRoles(array $roleIds)
    {
        if (!$this->isAffected()) return ErrorCode::getError("ACCT0006", implode(',', $roleIds));
        
        if ($this->accounttype == 'R') return ErrorCode::getError("ACCT0007", implode(',', $roleIds) , $this->login);
        $this->deleteRoles();
        $err = '';
        if ($this->accounttype == 'U' || $this->accounttype == 'G') {
            $g = new group($this->dbaccess);
            foreach ($roleIds as $rid) {
                if (!is_numeric($rid)) {
                    simpleQuery($this->dbaccess, sprintf("select id from users where login = '%'", pg_escape_string($rid)) , $idRoleW, true, true);
                    if ($idRoleW) $rid = $idRoleW;
                }
                if (!is_numeric($rid)) {
                    $err.= ErrorCode::getError("ACCT0008", $rid, $this->login);
                } else {
                    
                    $g->idgroup = $rid;
                    $g->iduser = $this->id;
                    $gerr = $g->add(true);
                    if ($gerr == 'OK') $gerr = '';
                    $err.= $gerr;
                }
            }
            
            $this->updateMemberOf();
        }
        if ($this->accounttype == 'G') {
            // must propagate to users
            $lu = $this->getUserMembers();
            $uw = new Account($this->dbaccess);
            foreach ($lu as $u) {
                $uw->id = $u["id"];
                $uw->updateMemberOf();
            }
        }
        return $err;
    }
    /**
     * return direct role ids (not role which can comes from parent groups)
     * @param bool $useSystemId if true return system id else return document ids
     * @return array
     */
    function getRoles($useSystemId = true)
    {
        $returnColumn = $useSystemId ? "id" : "fid";
        $sql = sprintf("SELECT users.%s from users, groups where groups.iduser=%d and users.id = groups.idgroup and users.accounttype='R'", $returnColumn, $this->id);
        simpleQuery($this->dbaccess, $sql, $rids, true, false);
        return $rids;
    }
    /**
     * return direct and indirect role which comes from groups
     * @param bool $useSystemId if true return system id else return document ids
     * @return array of users properties
     */
    function getAllRoles()
    {
        $mo = $this->getMemberOf();
        
        $sql = sprintf("SELECT * from users where id in (%s) and accounttype='R'", implode(',', $mo));
        simpleQuery($this->dbaccess, $sql, $rusers);
        return $rusers;
    }
    /**
     * delete all role of a user/group
     * @return string error message
     */
    public function deleteRoles()
    {
        if (!$this->isAffected()) return ErrorCode::getError("ACCT0004");
        if ($this->accounttype == 'R') return ErrorCode::getError("ACCT0005", $this->login);
        $err = '';
        $sql = sprintf("DELETE FROM groups USING users where groups.iduser=%d and users.id=groups.idgroup and users.accounttype='R'", $this->id);
        $err = simpleQuery($this->dbaccess, $sql);
        if (!$err) {
            
            $err = simpleQuery($this->dbaccess, "delete from permission where computed");
        }
        
        return $err;
    }
}
