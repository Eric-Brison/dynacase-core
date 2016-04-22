<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Checking document's profil
 * @class CheckProfil
 * @brief Check profil when importing definition
 * @see ErrorCodePRFL
 */
class CheckProfil extends CheckData
{
    /**
     * profil name
     * @var string
     */
    private $prfName = '';
    /**
     * doc name
     * @var string
     */
    private $docName = '';
    /**
     * profil doccument
     * @var Doc
     */
    private $profil = '';
    /**
     * dynamic reference
     * @var Doc
     */
    private $dynDoc = null;
    /**
     * access control list
     * @var array
     */
    private $acls = array();
    /**
     * modifier
     * @var string
     */
    private $modifier = '';
    
    private $defaultAccountType = '';
    /**
     * @var array
     */
    private $availablesModifier = array(
        'reset',
        'add',
        'delete',
        'set'
    );
    
    private $availablesDefaultType = array(
        ':useAccount',
        ':useDocument',
        ':useAttribute'
    );
    private $userIds = [];
    /**
     * @param array $data
     * @return CheckProfil
     */
    function check(array $data, &$extra = null)
    {
        
        if (!empty($data[2]) && !in_array($data[2], $this->availablesDefaultType)) {
            $this->prfName = $data[2];
            $this->docName = $data[1];
        } else {
            $this->defaultAccountType = isset($data[2]) ? trim($data[2]) : null;
            $this->prfName = isset($data[1]) ? $data[1] : null;
            for ($i = 4; $i < count($data); $i++) {
                $this->acls[] = $data[$i];
            }
        }
        if (isset($data[3])) $this->modifier = strtolower($data[3]);
        $this->checkUnknow();
        if (!$this->hasErrors()) {
            $this->checkModifier();
            $this->checkIsACompatibleProfil();
            $this->checkAcls();
        }
        
        return $this;
    }
    
    private function checkUnknow()
    {
        if ($this->prfName) {
            clearCacheDoc();
            $this->profil = new_doc(getDbAccess() , $this->prfName);
            if (!$this->profil->isAlive()) {
                $this->addError(ErrorCode::getError('PRFL0002', $this->prfName));
            }
        } else {
            $this->addError(ErrorCode::getError('PRFL0001'));
        }
    }
    
    private function checkIsACompatibleProfil()
    {
        if ($this->docName) {
            $doc = new_doc(getDbAccess() , $this->docName);
            if (!$doc->isAlive()) {
                $this->addError(ErrorCode::getError('PRFL0003', $this->docName));
            } else {
                if ($doc->acls != $this->profil->acls) {
                    $this->addError(ErrorCode::getError('PRFL0004', $this->prfName, $this->docName));
                }
            }
        }
    }
    
    private function checkModifier()
    {
        if ($this->modifier) {
            if (!in_array($this->modifier, $this->availablesModifier)) {
                $this->addError(ErrorCode::getError('PRFL0005', $this->modifier, implode(', ', $this->availablesModifier)));
            }
        }
    }
    
    private function checkAcls()
    {
        if (!$this->docName) {
            $profAcls = $this->profil->acls;
            $profAcls["viewacl"] = "viewacl"; // common special acl
            $profAcls["modifyacl"] = "modifyacl";
            foreach ($this->acls as $acl) {
                if ($acl) {
                    if (preg_match("/([^=]+)=(.+)/", $acl, $reg)) {
                        $aclId = $reg[1];
                        $userId = $reg[2];
                        if (!in_array($aclId, $profAcls)) {
                            
                            $this->addError(ErrorCode::getError('PRFL0101', $aclId, $this->prfName, implode(',', $profAcls)));
                        }
                        $this->checkUsers(explode(',', $userId));
                    } else {
                        $this->addError(ErrorCode::getError('PRFL0100', $acl, $this->prfName));
                    }
                }
            }
        }
    }
    
    private function checkUsers(array $uids)
    {
        foreach ($uids as $uid) {
            $uid = trim($uid);
            if ($uid) {
                if ($this->profil->getRawValue("dpdoc_famid")) {
                    if (!$this->checkAccount($uid)) {
                        $this->checkAttribute($uid);
                    }
                } else {
                    if (!$this->checkAccount($uid)) {
                        $this->addError(ErrorCode::getError('PRFL0103', $uid, $this->prfName));
                    }
                }
            } else {
                $this->addError(ErrorCode::getError('PRFL0102', $this->prfName));
            }
        }
    }
    
    private function checkAccount($reference)
    {
        $findUser = false;
        $this->extractAccount($reference, $type, $value);
        switch ($type) {
            case ':useAccount':
                $findUser = $this->getUserIdFromLogin($value);
                if (!$findUser) {
                    $this->addError(ErrorCode::getError('PRFL0104', $value, $this->prfName));
                }
                break;

            case ':useDocument':
                $tu = getTDoc(getDbAccess() , $value);
                if ($tu) {
                    $findUser = ($tu["us_whatid"] != '');
                }
                break;

            case ':useAttribute':
                $this->checkAttribute($value);
                $findUser = true;
                break;

            default:
                if (ctype_digit($reference)) {
                    $findUser = Account::getDisplayName($reference);
                } else {
                    // search document
                    $tu = getTDoc(getDbAccess() , $reference);
                    if ($tu) {
                        $findUser = ($tu["us_whatid"] != '');
                    }
                }
        }
        
        return $findUser;
    }
    
    private function extractAccount($reference, &$type, &$value)
    {
        if (preg_match('/^attribute\((.*)\)$/', $reference, $reg)) {
            $type = ":useAttribute";
            $value = strtolower(trim($reg[1]));
        } elseif (preg_match('/^account\((.*)\)$/', $reference, $reg)) {
            $type = ":useAccount";
            $value = mb_strtolower(trim($reg[1]));
        } elseif (preg_match('/^document\((.*)\)$/', $reference, $reg)) {
            $type = ":useDocument";
            $value = trim($reg[1]);
        } else {
            $value = $reference;
            $type = $this->defaultAccountType;
        }
    }
    
    private function getUserIdFromLogin($login)
    {
        $login = mb_strtolower($login);
        if (!isset($this->userIds[$login])) {
            simpleQuery("", sprintf("select login from users where login='%s'", pg_escape_string($login)) , $uid, true, true);
            $this->userIds[$uid] = $uid;
        }
        return $this->userIds[$login];
    }
    private function checkAttribute($aid)
    {
        $dynName = $this->profil->getRawValue("dpdoc_famid");
        if (!$this->dynDoc) {
            $this->dynDoc = new_doc(getDbAccess() , $dynName);
        }
        if (!$this->dynDoc->isAlive()) {
            $this->addError(ErrorCode::getError('PRFL0203', $dynName, $this->prfName));
        } else {
            $aids = array_keys($this->dynDoc->getNormalAttributes());
            $adocids = array();
            foreach ($aids as $naid) {
                $aType = $this->dynDoc->getAttribute($naid)->type;
                $isuserOption = $this->dynDoc->getAttribute($naid)->getOption("isuser");
                if (($aType == "docid" && $isuserOption == "yes") || ($aType == "account")) {
                    $adocids[] = $naid;
                }
            }
            if (!in_array(strtolower($aid) , $aids)) {
                $this->addError(ErrorCode::getError('PRFL0200', $aid, $this->prfName, implode(', ', $adocids)));
            } else {
                if (!in_array(strtolower($aid) , $adocids)) {
                    $this->addError(ErrorCode::getError('PRFL0201', $aid, $this->prfName, implode(', ', $adocids)));
                }
            }
        }
    }
}
