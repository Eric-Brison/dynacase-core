<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * User manipulation
 *
 * @author Anakeen 2004
 * @version $Id: Method.DocIUser.php,v 1.49 2008/08/13 14:07:54 jerome Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage USERCARD
 */
/**
 */
/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
class _IUSER extends Doc
{
    public $wuser;
    public function setGroups()
    {
    }
    /**
     * @param bool $real
     * @return Account
     */
    public function getAccount($real = false)
    {
    }
    public function getSystemIds(array $accountIds)
    {
    }
    /**
     * @end-method-ignore
     */
    
    var $eviews = array(
        "USERCARD:CHOOSEGROUP"
    );
    var $defaultview = "FDL:VIEWBODYCARD";
    var $defaultedit = "FDL:EDITBODYCARD";
    function specRefresh()
    {
        $err = parent::SpecRefresh();
        
        if ($this->getValue("US_STATUS") == 'D') $err.= ($err == "" ? "" : "\n") . _("user is deactivated");
        // refresh MEID itself
        $this->SetValue("US_MEID", $this->id);
        $iduser = $this->getValue("US_WHATID");
        if ($iduser > 0) {
            $user = $this->getAccount();
            if (!$user->isAffected()) return sprintf(_("user #%d does not exist") , $iduser);
        } else {
            if ($this->getValue("us_login") != '-') $err = _("user has not identificator");
            /**
             * @var NormalAttribute $oa
             */
            $oa = $this->getAttribute("us_passwd1");
            if ($oa) $oa->needed = true;
            $oa = $this->getAttribute("us_passwd2");
            if ($oa) $oa->needed = true;
            $oa = $this->getAttribute("us_tab_system");
            $oa->setOption("firstopen", "yes");
        }
        $this->updateIncumbents();
        return $err;
    }
    public function updateIncumbents()
    {
        $u = $this->getAccount();
        if ($u) {
            $this->setValue("us_incumbents", $u->getIncumbents(false));
        }
    }
    /**
     * test if the document can be set in LDAP
     */
    function canUpdateLdapCard()
    {
        return ($this->getValue("US_STATUS") != 'D');
    }
    
    public function preRevive()
    {
        return _("user cannot be revived");
    }
    /**
     * get all direct group document identificators of the isuser
     * @return @array of group document id, the index of array is the system identificator
     */
    public function getUserGroups()
    {
        $err = simpleQuery($this->dbaccess, sprintf("SELECT id, fid from users, groups where groups.iduser=%d and users.id = groups.idgroup;", $this->getValue("us_whatid")) , $groupIds, false, false);
        if (!$err) {
            $gids = array();
            foreach ($groupIds as $gid) {
                $gids[$gid["id"]] = $gid["fid"];
            }
            return $gids;
        }
        return null;
    }
    /**
     * return all direct group and parent group document identificators of $gid
     * @param string $gid systeme identificator group or users
     */
    protected function getAscendantGroup($gid)
    {
        $groupIds = array();
        if ($gid > 0) {
            $err = simpleQuery($this->dbaccess, sprintf("SELECT id, fid from users, groups where groups.iduser=%d and users.id = groups.idgroup;", $gid) , $groupIds, false, false);
            $gids = array(); // current level
            $pgids = array(); // fathers
            foreach ($groupIds as $gid) {
                $gids[$gid["id"]] = $gid["fid"];
            }
            
            foreach ($gids as $systemGid => $docGid) {
                $pgids+= $this->getAscendantGroup($systemGid);
            }
            $groupIds = $gids + $pgids;
        }
        return $groupIds;
    }
    /**
     * get all direct group and parent group document identificators of the isuser
     * @return @array of group document id the index of array is the system identificator
     */
    public function getAllUserGroups()
    {
        return $this->getAscendantGroup($this->getValue("us_whatid"));
    }
    /**
     * Refresh folder parent containt
     */
    function refreshParentGroup()
    {
        $tgid = $this->getTValue("US_IDGROUP");
        foreach ($tgid as $gid) {
            /**
             * @var _IGROUP $gdoc
             */
            $gdoc = new_Doc($this->dbaccess, $gid);
            if ($gdoc->isAlive()) {
                $gdoc->insertGroups();
            }
        }
    }
    /**
     * recompute intranet values from USER database
     */
    function refreshDocUser()
    {
        
        $err = "";
        $wid = $this->getValue("us_whatid");
        if ($wid > 0) {
            $wuser = $this->getAccount(true);
            
            if ($wuser->isAffected()) {
                $this->SetValue("US_WHATID", $wuser->id);
                $this->SetValue("US_LNAME", $wuser->lastname);
                $this->SetValue("US_FNAME", $wuser->firstname);
                $this->SetValue("US_PASSWD1", " ");
                $this->SetValue("US_PASSWD2", " ");
                $this->SetValue("US_LOGIN", $wuser->login);
                $this->SetValue("US_STATUS", $wuser->status);
                $this->SetValue("US_PASSDELAY", $wuser->passdelay);
                $this->SetValue("US_EXPIRES", $wuser->expires);
                $this->SetValue("US_DAYDELAY", $wuser->passdelay / 3600 / 24);
                
                $rolesIds = $wuser->getRoles(false);
                $this->SetValue("us_roles", $rolesIds);
                
                $mail = $wuser->getMail();
                if (!$mail) $this->DeleteValue("US_MAIL");
                else $this->SetValue("US_MAIL", $mail);
                if ($wuser->passdelay <> 0) {
                    $this->SetValue("US_EXPIRESD", strftime("%Y-%m-%d", $wuser->expires));
                    $this->SetValue("US_EXPIREST", strftime("%H:%M", $wuser->expires));
                } else {
                    $this->SetValue("US_EXPIRESD", " ");
                    $this->SetValue("US_EXPIREST", " ");
                }
                
                $this->SetValue("US_MEID", $this->id);
                // search group of the user
                $g = new Group("", $wid);
                $tgid = array();
                if (count($g->groups) > 0) {
                    $gt = new Account($this->dbaccess);
                    foreach ($g->groups as $gid) {
                        $gt->select($gid);
                        $tgid[] = $gt->fid;
                    }
                    $this->deleteArray("us_groups");
                    $this->SetValue("us_idgroup", $tgid);
                } else {
                    $this->deleteArray("us_groups");
                }
                $err = $this->modify();
            } else {
                $err = sprintf(_("user %d does not exist") , $wid);
            }
        }
        
        return $err;
    }
    /**
     * affect to default group
     */
    function setToDefaultGroup()
    {
        $grpid = $this->getParamValue("us_defaultgroup");
        $err = '';
        if ($grpid) {
            /**
             * @var _IGROUP $grp
             */
            $grp = new_doc($this->dbaccess, $grpid);
            if ($grp->isAlive()) {
                $err = $grp->addFile($this->initid);
            }
        }
        return $err;
    }
    
    function postCreated()
    {
        $err = "";
        global $action;
        $ed = $action->getParam("AUTHENT_ACCOUNTEXPIREDELAY");
        if ($ed > 0) {
            $expdate = time() + ($ed * 24 * 3600);
            $err = $this->SetValue("us_accexpiredate", strftime("%Y-%m-%d 00:00:00", $expdate));
            if ($err == '') $err = $this->modify(true, array(
                "us_accexpiredate"
            ) , true);
        }
        return $err;
    }
    /**
     * update/synchro system user
     */
    public function postModify()
    {
        $err = $this->synchronizeSystemUser();
        if (!$err) $this->refreshRoles();
    }
    /**
     * Modify system account from document IUSER
     */
    function synchronizeSystemUser()
    {
        $err = '';
        $uid = $this->getValue("us_whatid");
        $lname = $this->getValue("us_lname");
        $fname = $this->getValue("us_fname");
        $pwd1 = $this->getValue("us_passwd1");
        $pwd2 = $this->getValue("us_passwd2");
        $expires = $this->getValue("us_expires");
        $daydelay = $this->getValue("us_daydelay");
        if ($daydelay == - 1) $passdelay = $daydelay;
        else $passdelay = intval($daydelay) * 3600 * 24;
        $status = $this->getValue("us_status");
        $login = $this->getValue("us_login");
        $substitute = $this->getValue("us_substitute");
        $allRoles = $this->getAValues("us_t_roles");
        $extmail = $this->getValue("us_extmail", $this->getValue("us_homemail", " "));
        
        if ($login != "-") {
            // compute expire for epoch
            $expiresd = $this->getValue("us_expiresd");
            $expirest = $this->getValue("us_expirest", "00:00");
            //convert date
            $expdate = $expiresd . " " . $expirest . ":00";
            $expires = 0;
            if ($expdate != "") {
                if (preg_match("|([0-9][0-9])/([0-9][0-9])/(2[0-9][0-9][0-9]) ([0-2][0-9]):([0-5][0-9]):([0-5][0-9])|", $expdate, $reg)) {
                    $expires = mktime($reg[4], $reg[5], $reg[6], $reg[2], $reg[1], $reg[3]);
                } else if (preg_match("|(2[0-9][0-9][0-9])-([0-9][0-9])-([0-9][0-9]) ([0-2][0-9]):([0-5][0-9]):([0-5][0-9])|", $expdate, $reg)) {
                    $expires = mktime($reg[4], $reg[5], $reg[6], $reg[2], $reg[3], $reg[1]);
                }
            }
            
            $fid = $this->id;
            $newuser = false;
            $user = $this->getAccount();
            if (!$user) {
                $user = new Account(""); // create new user
                $this->wuser = & $user;
                $newuser = true;
            }
            // get direct system role ids
            $roles = array();
            foreach ($allRoles as $arole) {
                if ($arole["us_rolesorigin"] != "group") $roles[] = $arole["us_roles"];
            }
            $roleIds = $this->getSystemIds($roles);
            // perform update system User table
            if ($substitute) $substitute = $this->getDocValue($substitute, "us_whatid");
            $err.= $user->updateUser($fid, $lname, $fname, $expires, $passdelay, $login, $status, $pwd1, $pwd2, $extmail, $roleIds, $substitute);
            if ($err == "") {
                if ($user) {
                    $this->setValue("US_WHATID", $user->id);
                    $this->modify(false, array(
                        "us_whatid"
                    ));
                    $err = $this->setGroups(); // set groups (add and suppress) may be long
                    if ($newuser) $err.= $this->setToDefaultGroup();
                }
            }
            
            if ($err == "") {
                $err = $this->RefreshDocUser(); // refresh from core database
                //      $this->refreshParentGroup();
                $errldap = $this->RefreshLdapCard();
                if ($errldap != "") AddWarningMsg($errldap);
            }
        } else {
            // tranfert extern mail if no login specified yet
            if ($this->getValue("us_login") == "-") {
                $email = $this->getValue("us_extmail", $this->getValue("us_homemail"));
                if (($email != "") && ($email[0] != "<")) $this->setValue("us_mail", $email);
                else $this->deleteValue("us_mail");
            }
        }
        
        $this->setValue("US_LDAPDN", $this->getLDAPValue("dn", 1));
        return $err;
    }
    
    function PostDelete()
    {
        parent::PostDelete();
        
        $user = $this->getAccount();
        if ($user) $user->Delete();
    }
    /**
     * Do not call ::setGroup if its import
     * called only in initialisation
     */
    function preImport()
    {
        if ($this->id > 0) {
            global $_POST;
            $_POST["gidnew"] = "N";
        }
    }
    
    public function preconsultation()
    {
        $this->refreshRoles();
    }
    public function preEdition()
    {
        $allRoles = $this->getAValues("us_t_roles");
        $this->deleteArray("us_t_roles");
        // get direct system role ids
        $roles = array();
        foreach ($allRoles as $arole) {
            if ($arole["us_rolesorigin"] != "group") $roles[] = $arole["us_roles"];
        }
        $this->setValue("us_roles", $roles);
    }
    /**
     * recompute role attributes from system role
     */
    public function refreshRoles()
    {
        $u = $this->getAccount();
        if (!$u) return;
        $directRoleIds = $u->getRoles();
        $allParents = $u->getUserParents();
        $allRoles = $allGroup = array();
        foreach ($allParents as $aParent) {
            if ($aParent["accounttype"] == 'R') $allRoles[] = $aParent;
            else $allGroup[] = $aParent;
        }
        
        $this->deleteArray("us_t_roles");
        foreach ($allRoles as $role) {
            if (in_array($role["id"], $directRoleIds)) {
                $group = '';
                $status = 'internal';
                $this->addArrayRow("us_t_roles", array(
                    "us_roles" => $role["fid"],
                    "us_rolesorigin" => $status,
                    "us_rolegorigin" => $group
                ));
            }
            $group = '';
            $rid = $role["id"];
            $tgroup = array();
            foreach ($allGroup as $aGroup) {
                simpleQuery($this->dbaccess, sprintf("select idgroup from groups where iduser=%d and idgroup=%d", $aGroup["id"], $rid) , $gr);
                if ($gr) {
                    $tgroup[] = $aGroup["fid"];
                }
            }
            if ($tgroup) {
                $status = 'group';
                $group = implode('<BR>', $tgroup);
                $this->addArrayRow("us_t_roles", array(
                    "us_roles" => $role["fid"],
                    "us_rolesorigin" => $status,
                    "us_rolegorigin" => $group
                ));
            }
        }
    }
    /**
     * return main mail address
     * @return string
     */
    public function getMail($rawmail = false)
    {
        $wu = $this->getAccount();
        if ($wu->isAffected()) {
            return $wu->getMail($rawmail);
        }
        return '';
    }
    /**
     * return crypted password
     * @return string
     */
    public function getCryptPassword()
    {
        $wu = $this->getAccount();
        if ($wu->isAffected()) {
            return $wu->password;
        }
        return '';
    }
    function constraintPassword($pwd1, $pwd2, $login)
    {
        if ($this->testForcePassword($pwd1)) return '';
        $sug = array();
        $err = "";
        
        if ($pwd1 <> $pwd2) {
            $err = _("the 2 passwords are not the same");
        } else if (($pwd1 == "") && ($this->getValue("us_whatid") == "")) {
            if ($login != "-") $err = _("passwords must not be empty");
        }
        
        return array(
            "err" => $err,
            "sug" => $sug
        );
    }
    public function testForcePassword($pwd)
    {
        $minLength = intval(getParam("AUTHENT_PWDMINLENGTH"));
        $minDigitLength = intval(getParam("AUTHENT_PWDMINDIGITLENGTH"));
        $minUpperLength = intval(getParam("AUTHENT_PWDMINUPPERALPHALENGTH"));
        $minLowerLength = intval(getParam("AUTHENT_PWDMINLOWERALPHALENGTH"));
        $minSymbolLength = intval(getParam("AUTHENT_PWDMINSYMBOLLENGTH"));
        
        if (preg_match('/[\p{C}]/u', $pwd)) {
            return _("Control characters are not allowed");
        }
        
        $msg = sprintf(_("Your password is not secure."));
        if ($minLength > 0) $msg.= "\n " . sprintf(_("It must contains at least %d characters (total length)") , $minLength);
        if ($minDigitLength + $minUpperLength + $minLowerLength + $minSymbolLength > 0) $msg.= " " . sprintf(_("with these conditions"));
        if ($minDigitLength) {
            if ($minDigitLength > 1) $msg.= "\n  - " . sprintf(_("at least %d digits") , $minDigitLength);
            else $msg.= "\n  - " . sprintf(_("at least one digit"));
        }
        if ($minUpperLength) {
            if ($minUpperLength > 1) $msg.= "\n  - " . sprintf(_("at least %d uppercase alpha characters") , $minUpperLength);
            else $msg.= "\n  - " . sprintf(_("at least one uppercase alpha character"));
        }
        if ($minLowerLength) {
            if ($minLowerLength > 1) $msg.= "\n  - " . sprintf(_("at least %d lowercase alpha characters") , $minLowerLength);
            else $msg.= "\n  - " . sprintf(_("at least one lowercase alpha character"));
        }
        if ($minSymbolLength) {
            if ($minSymbolLength > 1) $msg.= "\n  - " . sprintf(_("at least %d symbol characters") , $minSymbolLength);
            else $msg.= "\n  - " . sprintf(_("at least one symbol character"));
        }
        if (mb_strlen($pwd) < $minLength) {
            $err = _("Not enough characters.") . "\n";
            return nl2br($err . $msg);
        }
        $alphanum = 0;
        
        if ($minDigitLength) {
            preg_match_all('/[0-9]/', $pwd, $matches);
            $alphanum+= count($matches[0]);
            if (count($matches[0]) < $minDigitLength) {
                $err = _("Not enough digits.") . "\n";
                return nl2br($err . $msg);
            }
        }
        if ($minUpperLength) {
            preg_match_all('/[\p{Lu}]/u', $pwd, $matches);
            $alphanum+= count($matches[0]);
            if (count($matches[0]) < $minUpperLength) {
                $err = _("Not enough uppercase characters.") . "\n";
                return nl2br($err . $msg);
            }
        }
        if ($minLowerLength) {
            preg_match_all('/[\p{Ll}]/u', $pwd, $matches);
            $alphanum+= count($matches[0]);
            if (count($matches[0]) < $minLowerLength) {
                $err = _("Not enough lowercase characters.") . "\n";
                return nl2br($err . $msg);
            }
        }
        if ($minSymbolLength) {
            if ((mb_strlen($pwd) - $alphanum) < $minSymbolLength) {
                $err = _("Not enough special characters.") . "\n";
                return nl2br($err . $msg);
            }
        }
        return '';
    }
    function constraintExpires($expiresd, $expirest, $daydelay)
    {
        $err = '';
        $sug = array();
        if (($expiresd <> "") && ($daydelay == 0)) {
            $err = _("Expiration delay must not be 0 to keep expiration date");
        }
        
        return array(
            "err" => $err,
            "sug" => $sug
        );
    }
    /**
     * @templateController
     * @param string $target
     * @param bool $ulink
     * @param string $abstract
     */
    function editlikeperson($target = "finfo", $ulink = true, $abstract = "Y")
    {
        global $action;
        
        $this->lay = new Layout(getLayoutFile("FDL", "editbodycard.xml") , $action);
        
        $this->attributes->attr['us_tab_system']->visibility = 'R';
        $this->attributes->attr['us_fr_userchange']->visibility = 'R';
        $this->ApplyMask();
        
        $this->attributes->attr['us_extmail']->mvisibility = 'W';
        $this->attributes->attr['us_extmail']->fieldSet = $this->attributes->attr['us_fr_coord'];
        $this->attributes->attr['us_extmail']->ordered = $this->attributes->attr['us_pphone']->ordered - 1;
        uasort($this->attributes->attr, "tordered");
        
        $this->editbodycard($target, $ulink, $abstract);
    }
    /**
     * interface to only modify name and password
     * @templateController
     */
    function editchangepassword()
    {
        $this->viewprop();
        $this->editattr(false);
    }
    /**
     * Set/change user password
     */
    function setPassword($password)
    {
        $idwuser = $this->getValue("US_WHATID");
        
        $wuser = $this->getAccount();
        if (!$wuser->isAffected()) {
            return sprintf(_("user #%d does not exist") , $idwuser);
        }
        // Change what user password
        $wuser->password_new = $password;
        $err = $wuser->modify();
        if ($err != "") {
            return $err;
        }
        
        return "";
    }
    /**
     * Increase login failure count
     */
    function increaseLoginFailure()
    {
        if ($this->getValue("us_whatid") == 1) return ""; // it makes non sense for admin
        $lf = $this->getValue("us_loginfailure", 0) + 1;
        $err = $this->SetValue("us_loginfailure", $lf);
        if ($err == "") {
            $err = $this->modify(false, array(
                "us_loginfailure"
            ) , false);
        }
        return "";
    }
    /**
     * Reset login failure count
     * @apiExpose
     */
    function resetLoginFailure()
    {
        if ($this->getValue("us_whatid") == 1) return ""; // it makes non sense for admin
        $err = $this->canEdit();
        if ($err == '') {
            if (intval($this->getValue("us_loginfailure")) > 0) {
                $err = $this->setValue("us_loginfailure", 0);
                if ($err == "") {
                    $err = $this->modify(false, array(
                        "us_loginfailure"
                    ) , false);
                }
            }
        }
        return $err;
    }
    /**
     * the incumbent account documents cannot be modified by susbtitutes
     * @param string $aclname
     * @param bool $strict
     * @return string
     */
    public function control($aclname, $strict = false)
    {
        if ($this->getAccount()->substitute == $this->getSystemUserId()) {
            return parent::control($aclname, true);
        } else {
            return parent::control($aclname, $strict);
        }
    }
    /**
     * Security menus visibilities
     */
    function menuResetLoginFailure()
    {
        // Do not show the menu if the user has no FUSERS privileges
        global $action;
        if (!$action->parent->hasPermission('FUSERS', 'FUSERS')) {
            return MENU_INVISIBLE;
        }
        // Do not show the menu if the user has no edit rights on the document
        if ($this->canEdit() != '') {
            return MENU_INVISIBLE;
        }
        // Do not show the menu on the 'admin' user
        if ($this->getValue('us_whatid') == 1) {
            return MENU_INVISIBLE;
        }
        // Do not show the menu if the account had no failures
        if ($this->getValue("us_loginfailure") <= 0) {
            return MENU_INVISIBLE;
        }
        return MENU_ACTIVE;
    }
    function menuActivateAccount()
    {
        // Do not show the menu if the user has no FUSERS privileges
        global $action;
        if (!$action->parent->hasPermission('FUSERS', 'FUSERS')) {
            return MENU_INVISIBLE;
        }
        // Do not show the menu if the user has no edit rights on the document
        if ($this->canEdit() != '') {
            return MENU_INVISIBLE;
        }
        // Do not show the menu on the 'admin' user
        if ($this->getValue('us_whatid') == 1) {
            return MENU_INVISIBLE;
        }
        // Do not show the menu if the account is already active
        if ($this->getValue('us_status', 'A') == 'A') {
            return MENU_INVISIBLE;
        }
        return MENU_ACTIVE;
    }
    function menuDeactivateAccount()
    {
        // Do not show the menu if the user has no FUSERS privileges
        global $action;
        if (!$action->parent->hasPermission('FUSERS', 'FUSERS')) {
            return MENU_INVISIBLE;
        }
        // Do not show the menu if the user has no edit rights on the document
        if ($this->canEdit() != '') {
            return MENU_INVISIBLE;
        }
        // Do not show the menu on the 'admin' user
        if ($this->getValue('us_whatid') == 1) {
            return MENU_INVISIBLE;
        }
        // Do not show the menu if the account is already inactive
        if ($this->getValue('us_status', 'A') != 'A') {
            return MENU_INVISIBLE;
        }
        return MENU_ACTIVE;
    }
    /**
     * Manage account security
     */
    function isAccountActive()
    {
        if ($this->getValue("us_whatid") == 1) return false; // it makes non sense for admin
        $u = $this->getAccount();
        if ($u) {
            return $u->status != 'D';
        }
        return false;
    }
    /**
     * @apiExpose
     * @return string error message
     */
    function activateAccount()
    {
        // Check that the user has FUSERS privileges
        global $action;
        if ($this->canEdit() != '' || !$action->parent->hasPermission('FUSERS', 'FUSERS')) {
            return _("current user cannot deactivate account");
        }
        // The 'admin' account cannot be deactivated
        if ($this->getValue("us_whatid") == 1) {
            return '';
        }
        $err = $this->SetValue("us_status", 'A');
        if ($err == "") {
            $err = $this->modify(true, array(
                "us_status"
            ) , true);
            $this->synchronizeSystemUser();
        }
        return $err;
    }
    function isAccountInactive()
    {
        return (!$this->isAccountActive());
    }
    /**
     * @apiExpose
     * @return string error message
     */
    function deactivateAccount()
    {
        // Check that the user has FUSERS privileges
        global $action;
        if ($this->canEdit() != '' || !$action->parent->hasPermission('FUSERS', 'FUSERS')) {
            return _("current user cannot deactivate account");
        }
        // The 'admin' account cannot be deactivated
        if ($this->getValue("us_whatid") == 1) {
            return '';
        }
        $err = $this->SetValue("us_status", 'D');
        if ($err == "") {
            $err = $this->modify(true, array(
                "us_status"
            ) , true);
            $this->synchronizeSystemUser();
        }
        return $err;
    }
    function accountHasExpired()
    {
        if ($this->getValue("us_whatid") == 1) return false;
        $expd = $this->getValue("us_accexpiredate");
        //convert date
        $expires = 0;
        if ($expd != "") {
            if (preg_match("|([0-9][0-9])/([0-9][0-9])/(2[0-9][0-9][0-9])|", $expd, $reg)) {
                $expires = mktime(0, 0, 0, $reg[2], $reg[1], $reg[3]);
            } else if (preg_match("|(2[0-9][0-9][0-9])-([0-9][0-9])-([0-9][0-9]|", $expd, $reg)) {
                $expires = mktime(0, 0, 0, $reg[2], $reg[3], $reg[1]);
            }
            return ($expires <= time());
        }
        return false;
    }
    /**
     * @begin-method-ignore
     * this part will be deleted when construct document class until end-method-ignore
     */
}
/**
 * @end-method-ignore
 */
?>