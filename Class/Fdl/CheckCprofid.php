<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

class CheckCprofid extends CheckData
{
    protected $folderName;
    /**
     * @var Doc
     */
    protected $doc;
    
    protected $authorizedKeys = array(
        "attributes"
    );
    /**
     * @param array $data
     * @param Doc $doc
     * @return CheckCprofid
     */
    function check(array $data, &$doc = null)
    {
        
        $this->folderName = $data[1];
        $this->doc = $doc;
        $this->checkSearch();
        return $this;
    }
    /**
     * check id it is a search
     * @return void
     */
    protected function checkSearch()
    {
        if ($this->folderName) {
            $d = new_doc('', $this->folderName);
            if (!$d->isAlive()) {
                $this->addError(ErrorCode::getError('CPRF0001', $this->folderName, $this->doc->name));
            } elseif (!is_a($d, "Doc")) {
                $this->addError(ErrorCode::getError('CPRF0002', $this->folderName, $this->doc->name));
            }
        }
    }
}
