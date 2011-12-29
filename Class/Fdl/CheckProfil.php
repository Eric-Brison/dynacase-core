<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
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
    private $acls = '';
    /**
     * modifier
     * @var string
     */
    private $modifier = '';
    /**
     * @var array
     */
    private $availablesModifier = array(
        'reset',
        'add',
        'delete'
    );
    /**
     * @param array $data
     * @return CheckProfil
     */
    function check(array $data, &$extra = null)
    {
        
        if ($data[2]) {
            $this->prfName = $data[2];
            $this->docName = $data[1];
        } else {
            $this->prfName = $data[1];
            for ($i = 4; $i < count($data); $i++) {
                $this->acls[] = $data[$i];
            }
        }
        $this->modifier = strtolower($data[3]);
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
                if ($this->profil->getValue("dpdoc_famid")) {
                    if (!$this->checkUser($uid)) {
                        $this->checkAttribute($uid);
                    }
                } else {
                    if (!$this->checkUser($uid)) {
                        $this->addError(ErrorCode::getError('PRFL0103', $uid, $this->prfName));
                    }
                }
            } else {
                $this->addError(ErrorCode::getError('PRFL0102', $this->prfName));
            }
        }
    }
    
    private function checkUser($uid)
    {
        
        $findUser = false;
        if (ctype_digit($uid)) {
            $findUser = User::getDisplayName($uid);
        } else {
            // search document
            $tu = getTDoc(getDbAccess() , $uid);
            if ($tu) {
                $findUser = ($tu["us_whatid"] != '');
            }
        }
        return $findUser;
    }
    
    private function checkAttribute($aid)
    {
        $dynName = $this->profil->getValue("dpdoc_famid");
        if (!$this->dynDoc) {
            $this->dynDoc = new_doc(getDbAccess() , $dynName);
        }
        if (!$this->dynDoc->isAlive()) {
            $this->addError(ErrorCode::getError('PRFL0203', $dynName, $this->prfName));
        } else {
            $aids = array_keys($this->dynDoc->getNormalAttributes());
            $adocids = array();
            foreach ($aids as $naid) {
                if ($this->dynDoc->getAttribute($naid)->type == "docid") {
                    $adocids[] = $naid;
                }
            }
            if (!in_array($aid, $aids)) {
                $this->addError(ErrorCode::getError('PRFL0200', $aid, $this->prfName, implode(', ', $adocids)));
            } else {
                if (!in_array($aid, $adocids)) {
                    $this->addError(ErrorCode::getError('PRFL0201', $aid, $this->prfName, implode(', ', $adocids)));
                }
            }
        }
    }
}
