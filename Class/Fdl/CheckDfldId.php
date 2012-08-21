<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

class CheckDfldid extends CheckData
{
    protected $folderName;
    /**
     * @var Doc
     */
    protected $doc;
    

    /**
     * @param array $data
     * @param Doc $doc
     * @return CheckDfldid
     */
    function check(array $data, &$doc = null)
    {
        
        $this->folderName = isset($data[1]) ? $data[1] : null;
        $this->doc = $doc;
        $this->checkFolder();
        return $this;
    }
    /**
     * check if it is a folder
     * @return void
     */
    protected function checkFolder()
    {
        if ($this->folderName && $this->folderName != 'auto') {
            $d = new_doc('', $this->folderName);
            if (!$d->isAlive()) {
                $this->addError(ErrorCode::getError('DFLD0001', $this->folderName, $this->doc->name));
            } elseif (!is_a($d, "Dir")) {
                $this->addError(ErrorCode::getError('DFLD0002', $this->folderName, $this->doc->name));
            }
        }
    }
}
