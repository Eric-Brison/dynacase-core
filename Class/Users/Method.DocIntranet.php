<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Intranet User & Group  manipulation
 *
 * @author Anakeen
 * @version $Id: Method.DocIntranet.php,v 1.23 2008/04/15 07:11:04 eric Exp $
 * @package FDL
 * @subpackage USERCARD
 */
/**
 */
/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
class _IGROUPUSER extends Doc
{
    /*
     * @end-method-ignore
    */
    /**
     * @var Account
     */
    public $wuser;
    /**
     * verify if the login syntax is correct and if the login not already exist
     * @param string $login login to test
     * @return array 2 items $err & $sug for view result of the constraint
     */
    function ConstraintLogin($login)
    {
        $sug = array(
            "-"
        );
        $err = '';
        if ($login == "") {
            $err = _("the login must not be empty");
        } else if ($login == "-") {
        } else if ($login == "-") {
        } else {
            if ($err == "") {
                return $this->ExistsLogin($login);
            }
        }
        return array(
            "err" => $err,
            "sug" => $sug
        );
    }
    /**
     * verify if the login not already exist
     * @param string $login login to test
     * @return array 2 items $err & $sug for view result of the constraint
     */
    function ExistsLogin($login, $unused = 0)
    {
        $sug = array();
        
        $id = $this->getRawValue("US_WHATID");
        
        $q = new QueryDb("", "Account");
        $q->AddQuery(sprintf("login='%s'", pg_escape_string(mb_strtolower($login))));
        if ($id) $q->AddQuery(sprintf("id != %d", $id));
        $q->Query(0, 0, "TABLE");
        $err = $q->basic_elem->msg_err;
        if (($err == "") && ($q->nb > 0)) $err = _("login yet use");
        
        return array(
            "err" => $err,
            "sug" => $sug
        );
    }
    
    function preCreated()
    {
        if ($this->getRawValue("US_WHATID") != "") {
            include_once ('FDL/Lib.Dir.php');
            
            $filter = array(
                "us_whatid = '" . intval($this->getRawValue("US_WHATID")) . "'"
            );
            $tdoc = internalGetDocCollection($this->dbaccess, 0, 0, "ALL", $filter, 1, "TABLE", $this->fromid);
            if (count($tdoc) > 0) return _("system id already set in database\nThis kind of document can not be duplicated");
        }
        return '';
    }
    /**
     * avoid deletion of system document
     */
    function preDocDelete()
    {
        $err = parent::preDocDelete();
        if ($err == "") {
            $uid = $this->getRawValue("us_whatid");
            if (($uid > 0) && ($uid < 10)) $err = _("this system user cannot be deleted");
        }
        return $err;
    }
    /**
     * get system id account from document id account
     * @param array $accountIds
     * @return array
     */
    public function getSystemIds(array $accountIds)
    {
        $accountIds = array_unique($accountIds);
        $kr = array_search('', $accountIds);
        if ($kr !== false) unset($accountIds[$kr]);
        $sysIds = array();
        if (count($accountIds) > 0) {
            $sql = sprintf("select id from users where fid in (%s)", implode(',', $accountIds));
            simpleQuery($this->dbaccess, $sql, $sysIds, true, false);
            $sysIds = array_unique($sysIds);
        }
        return $sysIds;
    }
    /**
     * interface to affect group for an user
     * @templateController interface to view group tree and select group
     * @param string $target window target name for hyperlink destination
     * @param bool $ulink if false hyperlink are not generated
     * @param bool $abstract if true only abstract attribute are generated
     */
    function ChooseGroup($target = "_self", $ulink = true, $abstract = false)
    {
        global $action;
        
        $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FDL/Layout/mktree.js");
        $action->parent->addCssRef($action->GetParam("CORE_PUBURL") . "/USERCARD/Layout/choosegroup.css");
        
        $err = '';
        $iduser = $this->getRawValue("US_WHATID");
        if ($iduser > 0) {
            $user = $this->getAccount();
            if (!$user->isAffected()) {
                return sprintf(_("user #%d does not exist") , $iduser);
            }
            $ugroup = $user->GetGroupsId();
        } else {
            $ugroup = array(
                "2"
            ); // default what group
            
        }
        
        $tgroup = array();
        
        $this->lay->set("wid", ($iduser == "") ? "0" : $iduser);
        
        $q2 = new queryDb("", "Account");
        $groups = $q2->Query(0, 0, "TABLE", "select users.*, groups.idgroup from users, groups where users.id = groups.iduser and users.accounttype='G'");
        
        $q2 = new queryDb("", "Account");
        $mgroups = $q2->Query(0, 0, "TABLE", "select users.* from users where accounttype='G' and id not in (select iduser from groups, users u where groups.idgroup = u.id and u.accounttype='G')");
        
        if ($groups) {
            foreach ($groups as $k => $v) {
                $v["login"] = htmlspecialchars($v["login"]);
                $v["firstname"] = htmlspecialchars($v["firstname"]);
                $v["lastname"] = htmlspecialchars($v["lastname"]);
                $groupuniq[$v["id"]] = $v;
                
                if (in_array($v["id"], $ugroup)) {
                    $groupuniq[$v["id"]]["checkbox"] = "checked";
                } else {
                    $groupuniq[$v["id"]]["checkbox"] = "";
                }
            }
        } else {
            $groups = array();
        }
        
        $iconGroup = $this->getIcon('', 14);
        
        if ($mgroups) {
            foreach ($mgroups as $k => $v) {
                $v["login"] = htmlspecialchars($v["login"]);
                $v["firstname"] = htmlspecialchars($v["firstname"]);
                $v["lastname"] = htmlspecialchars($v["lastname"]);
                $cgroup = $this->_getChildsGroup($v["id"], $groups);
                $tgroup[$k] = $v;
                $tgroup[$k]["SUBUL"] = $cgroup;
                $fid = $v["fid"];
                if ($fid) {
                    $tdoc = getTDoc($this->dbaccess, $fid);
                    $icon = $this->getIcon($tdoc["icon"], 14);
                    $tgroup[$k]["icon"] = $icon;
                } else {
                    $tgroup[$k]["icon"] = $iconGroup;
                }
                
                $groupuniq[$v["id"]] = $v;
                if (in_array($v["id"], $ugroup)) {
                    $groupuniq[$v["id"]]["checkbox"] = "checked";
                } else {
                    $groupuniq[$v["id"]]["checkbox"] = "";
                }
            }
        }
        $this->lay->setBlockData("LI", $tgroup);
        uasort($groupuniq, array(
            get_class($this) ,
            "_cmpgroup"
        ));
        $this->lay->setBlockData("SELECTGROUP", $groupuniq);
        return $err;
    }
    /**
     * internal function use for choosegroup
     * use to compute displayed group tree
     */
    function _getChildsGroup($id, $groups)
    {
        
        $tlay = array();
        foreach ($groups as $k => $v) {
            if ($v["idgroup"] == $id) {
                $tlay[$k] = $v;
                $tlay[$k]["SUBUL"] = $this->_getChildsGroup($v["id"], $groups);
                $fid = $v["fid"];
                if ($fid) {
                    $tdoc = getTDoc($this->dbaccess, $fid);
                    $icon = $this->getIcon($tdoc["icon"]);
                    $tlay[$k]["icon"] = $icon;
                } else {
                    $tlay[$k]["icon"] = "Images/igroup.gif";
                }
            }
        }
        
        if (count($tlay) == 0) return "";
        global $action;
        $lay = new Layout("USERCARD/Layout/ligroup.xml", $action);
        uasort($tlay, array(
            get_class($this) ,
            "_cmpgroup"
        ));
        $lay->setBlockData("LI", $tlay);
        return $lay->gen();
    }
    /**
     * to sort group by name
     */
    static function _cmpgroup($a, $b)
    {
        return strcasecmp($a['lastname'], $b['lastname']);
    }
    /**
     * affect new groups to the user
     * @global gidnew  string Http var : egual Y to say effectif change (to not suppress group if gid not set)
     * @global gid string Http var : array of new groups id
     */
    function setGroups()
    {
        include_once ("FDL/Lib.Usercard.php");
        
        global $_POST;
        $err = '';
        $gidnew = isset($_POST["gidnew"]) ? $_POST["gidnew"] : '';
        $tgid = array(); // group ids will be modified
        if ($gidnew == "Y") {
            /**
             * @var int[] $gids
             */
            $gids = $_POST["gid"];
            if ($gids == "") $gids = array();
            
            $gAccount = $this->getAccount();
            $rgid = $gAccount->GetGroupsId();
            if ((count($rgid) != count($gids)) || (count(array_diff($rgid, $gids)) != 0)) {
                $gdel = array_diff($rgid, $gids);
                $gadd = array_diff($gids, $rgid);
                // add group
                $g = new Group("", $gAccount->id);
                foreach ($gadd as $gid) {
                    $g->iduser = $gAccount->id;
                    $g->idgroup = $gid;
                    // insert in folder group
                    $gdoc = $this->getDocUser($gid);
                    //  $gdoc->insertMember($this->id);
                    $err.= $gdoc->insertDocument($this->id); // add in group is set here by postInsert
                    $tgid[$gid] = $gid;
                }
                foreach ($gdel as $gid) {
                    $g->iduser = $gid;
                    //$aerr.=$g->SuppressUser($user->id,true);
                    // delete in folder group
                    $gdoc = $this->getDocUser($gid);
                    if (!method_exists($gdoc, "deleteMember")) AddWarningMsg("no group $gid/" . $gdoc->id);
                    else {
                        // $gdoc->deleteMember($this->id);
                        $err = $gdoc->removeDocument($this->id);
                        $tgid[$gid] = $gid;
                    }
                }
                // $g->FreedomCopyGroup();
                //if ($user->isgroup=='Y')  $tgid[$user->id]=$user->id;
                
            }
        }
        // it is now set in bacground
        //  refreshGroups($tgid,true);
        return $err;
    }
    /**
     * return document objet from what id (user or group)
     * @param int $wid what identifier
     * @return \Dcp\Family\Iuser|\Dcp\Family\IGROUP the object document (false if not found)
     */
    function getDocUser($wid)
    {
        $u = new Account("", $wid);
        if ($u->isAffected()) {
            if ($u->fid > 0) {
                $du = new_Doc($this->dbaccess, $u->fid);
                if ($du->isAlive()) return $du;
            }
        }
        return false;
    }
    /**
     * return system account object conform to whatid
     * @param bool $nocache set to true if need to reload user object from database
     * @return Account return false if not found
     */
    function getAccount($nocache = false)
    {
        if ($nocache) {
            $this->wuser=null; // needed for reaffect new values
            
        } elseif ($this->wuser) {
            if ($this->wuser->fid != $this->getRawValue("us_whatid")) {
                $this->wuser=null; // clear cache when reaffect
                
            }
        }
        
        if (!isset($this->wuser)) {
            $wid = $this->getRawValue("us_whatid");
            if ($wid > 0) {
                $this->wuser = new Account("", $wid);
            }
        }
        if (!isset($this->wuser)) return false;
        return $this->wuser;
    }
    /**
     * return what user object conform to whatid
     * @deprecated use getAccount instead
     * @return Account return false if not found
     */
    function getWuser($nocache = false)
    {
        return $this->getAccount($nocache);
    }
    /**
     * reset wuser
     */
    protected function postAffect(array $data, $more, $reset)
    {
        if (isset($this->wuser)) {
            $this->wuser=null;
        }
    }
    /**
     * @begin-method-ignore
     * this part will be deleted when construct document class until end-method-ignore
     */
}
/*
 * @end-method-ignore
*/
?>
