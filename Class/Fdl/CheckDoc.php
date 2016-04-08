<?php
/*
 * @author Anakeen
 * @package FDL
*/

class CheckDoc extends CheckData
{
    const LOGICALNAME_RE = '/^[a-z][a-z0-9_-]*$/i';
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
        
        $this->famName = isset($data[1]) ? trim($data[1]) : '';
        $this->specName = isset($data[2]) ? trim($data[2]) : '';
        $this->folderId = isset($data[3]) ? trim($data[3]) : '';
        $this->CheckDocName();
        if (!$this->hasErrors()) $this->CheckDocFrom();
        
        return $this;
    }
    /**
     * Get the parsed family name or bool(false) if family could not be
     * parsed.
     *
     * @return bool|string
     */
    public function getParsedFamName()
    {
        return (isset($this->famName) ? $this->famName : false);
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
                try {
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
                        $this->famName = $f->name;
                    }
                }
                catch(Exception $e) {
                    $this->addError(ErrorCode::getError('DOC0010', $this->famName, $this->specName, $e->getMessage()));
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
            if (!self::isWellformedLogicalName($name)) {
                return false;
            }
        }
        return true;
    }
    /**
     * Check the given logical name is well-formed.
     * @param $name string
     * @return bool true when well-formed or false when mal-formed
     */
    public static function isWellformedLogicalName($name)
    {
        return (preg_match(self::LOGICALNAME_RE, $name) === 1);
    }
}
