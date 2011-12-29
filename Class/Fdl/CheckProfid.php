<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

class CheckProfid extends CheckData
{
    /**
     * profil name
     * @var string
     */
    private $prfName = '';
    /**
     * profil doccument
     * @var Doc
     */
    private $profil = '';
    /**
     * @param array $data
     * @return CheckProfid
     */
    function check(array $data, $extra = null)
    {
        $this->prfName = $data[1];
        $this->checkUnknow();
        if (!$this->hasErrors()) {
            $this->checkIsAFamilyProfil();
        }
        
        return $this;
    }
    
    private function checkUnknow()
    {
        if ($this->prfName) {
            try {
                $this->profil = new_doc(getDbAccess() , $this->prfName);
            }
            catch(Exception $e) {
                // due to no test validity of the family now
                $fam = getTDoc(getDbAccess() , $this->prfName);
                if (!$fam) throw $e;
                if ($fam["doctype"] == "C") {
                    $this->profil = new DocFam();
                    $this->profil->affect($fam);
                }
            }
            if (!$this->profil->isAlive()) {
                $this->addError(ErrorCode::getError('PRFD0001', $this->prfName));
            }
        }
    }
    
    private function checkIsAFamilyProfil()
    {
        if (!is_a($this->profil, "PFam")) {
            $this->addError(ErrorCode::getError('PRFD0002', $this->prfName));
        }
    }
}
