<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

class CheckDoc extends CheckData
{
    /**
     * @var string family reference
     */
    protected $famName;
    /**
     * @var string special logical name
     */
    protected $specName;
    /**
     * @var string folder reference where insert document
     */
    protected $folderId;
    /**
     * @var Doc
     */
    protected $doc;
    /**
     * @param array $data
     * @param Doc $doc
     * @return CheckDoc
     */
    function check(array $data, &$extra = null)
    {
        
        $this->famName = trim($data[1]);
        $this->specName = trim($data[2]);
        $this->folderId = trim($data[3]);
        $this->CheckDocName();
        if (!$this->hasErrors()) $this->CheckDocFrom();
        
        return $this;
    }
    /**
     * check
     * check
     * @return void
     */
    protected function CheckDocFrom()
    {
        if ($this->famName) {
            if (!$this->checkName($this->famName)) {
                $this->addError(ErrorCode::getError('DOC0003', $this->famName, $this->specName));
            } else {
                $f = new_doc(getDbAccess() , $this->famName);
                if (!$f->isAlive()) {
                    $this->addError(ErrorCode::getError('DOC0005', $this->famName, $this->specName));
                } else {
                    if ($f->doctype != 'C') {
                        $this->addError(ErrorCode::getError('DOC0006', $this->famName, $this->specName));
                    } else {
                        $canCreateError = $f->control('create');
                        if ($canCreateError) $this->addError(ErrorCode::getError('DOC0007', $this->famName, $this->specName));
                    }
                }
            }
        } else {
            
            $this->addError(ErrorCode::getError('DOC0002', $this->specName));
        }
    }
    /**
     * check logical name
     * @return void
     */
    protected function CheckDocName()
    {
        if (!$this->checkName($this->specName)) {
            $this->addError(ErrorCode::getError('DOC0004', $this->specName));
        }
    }
    
    private function checkName($name)
    {
        if ($name && (!is_numeric($name))) {
            if (!preg_match('/^[a-z][a-z0-9_]*$/i', $name)) {
                return false;
            }
        }
        return true;
    }
}
