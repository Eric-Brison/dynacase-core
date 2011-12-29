<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Checking application accesses
 * @class CheckAccess
 * @brief Check application accesses when importing definition
 * @see ErrorCodeACCS
 */
class CheckAccess extends CheckData
{
    /**
     * application name
     * @var string
     */
    private $appName = '';
    /**
     * application identificator
     * @var int
     */
    private $appId = '';
    /**
     * user identificator
     * @var string
     */
    private $userId = '';
    /**
     * current action
     * @var Action
     */
    private $action = null;
    /**
     * acl list
     * @var array
     */
    private $acls = array();
    /**
     * @param array $data
     * @return CheckProfid
     */
    public function check(array $data, &$action = null)
    {
        $this->appName = $data[2];
        $this->userId = $data[1];
        
        for ($i = 3; $i < count($data); $i++) {
            if ($data[$i]) $this->acls[] = $data[$i];
        }
        
        $this->action = $action;
        $this->checkAppExists();
        if (!$this->hasErrors()) {
            $this->checkUserExists();
            $this->checkAclsExists();
        }
        
        return $this;
    }
    
    private function checkAppExists()
    {
        if (!$this->appName) {
            $this->addError(ErrorCode::getError('ACCS0006'));
        } else {
            if ($this->checkSyntax($this->appName)) {
                $this->appId = $this->action->parent->GetIdFromName($this->appName);
                if (!$this->appId) {
                    $this->addError(ErrorCode::getError('ACCS0001', $this->appName));
                }
            } else {
                $this->addError(ErrorCode::getError('ACCS0005', $this->appName));
            }
        }
    }
    
    private function checkUserExists()
    {
        if ($this->userId) {
            $findUser = false;
            if (ctype_digit($this->userId)) {
                $findUser = User::getDisplayName($this->userId);
            } else {
                // search document
                $tu = getTDoc(getDbAccess() , $this->userId);
                if ($tu) {
                    $findUser = ($tu["us_whatid"] != '');
                }
            }
            if ($findUser === false) {
                $this->addError(ErrorCode::getError('ACCS0003', $this->userId));
            }
        } else {
            $this->addError(ErrorCode::getError('ACCS0007'));
        }
    }
    private function checkAclsExists()
    {
        $oAcl = new Acl(getDbAccess());
        
        foreach ($this->acls as $acl) {
            if ($this->checkSyntax($acl)) {
                if (!$oAcl->Set($acl, $this->appId)) {
                    $this->addError(ErrorCode::getError('ACCS0002', $acl, $this->appName));
                }
            } else {
                $this->addError(ErrorCode::getError('ACCS0004', $acl));
            }
        }
    }
    /**
     * @param string $acl
     * @return bool
     */
    private function checkSyntax($acl)
    {
        if (preg_match("/^[A-Z_0-9]{1,63}$/i", $acl)) {
            return true;
        }
        return false;
    }
}
