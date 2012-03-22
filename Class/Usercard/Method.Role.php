<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Specials methods for Role family
 *
 */
/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
class _ROLE extends Doc
{
    /*
     * @end-method-ignore
    */
    /**
     * @var User system role
     */
    protected $sysRole = null;
    
    public function PreCreated()
    {
        $this->lowerLogin();
        $err = $this->userSynchronize();
        return $err;
    }
    
    public function PreUpdate()
    {
        parent::PreUpdate();
        if ($this->isChanged()) {
            $this->lowerLogin();
        }
    }
    
    public function preRevive()
    {
        return _("role cannot be revived");
    }
    /**
     * return concatenation of mail addresses
     * @param bool $rawmail if true get mail address only else get mail address with name
     * @return string
     */
    public function getMail($rawmail = false)
    {
        $wu = $this->getSystemRole();
        if ($wu->isAffected()) {
            return $wu->getMail($rawmail);
        }
        return '';
    }
    private function lowerLogin()
    {
        $login = $this->getValue("role_login");
        if (mb_strtolower($login) != $login) {
            
            $this->setValue("role_login", mb_strtolower($login));
        }
    }
    /**
     * synchro with User table
     *
     * @return string error message, if no error empty string
     * @see Doc::PostModify()
     */
    public function postModify()
    {
        
        $err = $this->userSynchronize();
        return $err;
    }
    /**
     * update/create system role from document role
     * @return string error message
     */
    public function userSynchronize()
    {
        $err = '';
        if ($this->id) {
            $sR = $this->getSystemRole();
            
            if (!$sR) {
                // try create it
                $sR = new User();
                $sR->login = $this->getValue('role_login');
                $sR->lastname = $this->getValue('role_name');
                $sR->fid = $this->initid;
                $sR->accounttype = 'R';
                $sR->password_new = uniqid("role");
                $sR->isgroup = 'N';
                $err = $sR->add();
                if ($err == "") {
                    $this->setValue("us_whatid", $sR->id);
                    $this->modify(true, array(
                        "us_whatid"
                    ) , true);
                }
            } else {
                // update it
                $sR->login = $this->getValue('role_login');
                $sR->lastname = $this->getValue('role_name');
                $sR->fid = $this->initid;
                $err = $sR->modify();
            }
        }
        
        return $err;
    }
    /**
     * recompute sytstem values from USER database
     */
    function refreshDocUser()
    {
        $wid = $this->getValue("us_whatid");
        if ($wid > 0) {
            $wuser = $this->getSystemRole(true);
            
            if ($wuser && $wuser->isAffected()) {
                $this->SetValue("us_whatid", $wuser->id);
                $this->SetValue("role_login", $wuser->login);
                $this->SetValue("role_name", $wuser->lastname);
            }
        }
    }
    /**
     * return system user object conform to whatid
     * @param bool $nocache
     * @return User|null return null if not found
     */
    function getSystemRole($nocache = false)
    {
        if ($nocache) {
            unset($this->sysRole); // needed for reaffect new values
            
        }
        if (!$this->sysRole) {
            $wid = $this->getValue("us_whatid");
            if ($wid > 0) {
                $this->sysRole = new User("", $wid);
            }
        }
        if (!$this->sysRole) return null;
        return $this->sysRole;
    }
    /**
     * constraint to detect unique login
     * @param $login
     * @return string
     */
    public function isUniqueLogin($login)
    {
        $err = "";
        $sql = sprintf("select id from users where login = '%s' and id != %d", mb_strtolower(pg_escape_string($login)) , $this->getValue("us_whatid"));
        simpleQuery('', $sql, $id, true, true);
        
        if ($id) $err = sprintf(_("role %s id is already used") , $login);
        
        return $err;
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