<?php
/*
 * @author Anakeen
 * @package FDL
*/

class CheckClass extends CheckData
{
    /**
     * @var string class name
     */
    protected $className;
    /**
     * @var string file where class is defined
     */
    protected $fileName;
    /**
     * @var Doc
     */
    protected $doc;
    /**
     * @param array $data
     * @param Doc $doc
     * @return CheckClass
     */
    function check(array $data, &$doc = null)
    {
        if (!empty($data[1])) {
            $this->className = $data[1];
            
            $this->doc = $doc;
            $this->checkClassSyntax();
            $this->checkClassFile();
            $this->checkInherit();
        }
        return $this;
    }
    
    protected function checkClassSyntax()
    {
        if (!preg_match('/^[A-Z][A-Z_0-9\\\\]*$/i', $this->className)) {
            $this->addError(ErrorCode::getError('CLASS0001', $this->className, $this->doc->name));
        }
        return false;
    }
    
    protected function getClassFile()
    {
        $classFile = \Dcp\DirectoriesAutoloader::instance(null, null)->getClassFile($this->className);
        
        if ($classFile === null) {
            \Dcp\DirectoriesAutoloader::instance(null, null)->forceRegenerate($this->className);
            
            $classFile = \Dcp\DirectoriesAutoloader::instance(null, null)->getClassFile($this->className);
        }
        
        return $classFile;
    }
    /**
     * check if it is a folder
     * @return void
     */
    protected function checkClassFile()
    {
        if ($this->className) {
            $classFile = $this->getClassFile();
            $fileName = realpath($classFile);
            if ($classFile && $fileName) {
                $this->fileName = $fileName;
                // Get the shell output from the syntax check command
                if (self::phpLintFile($fileName, $output) === false) {
                    $this->addError(ErrorCode::getError('CLASS0002', $classFile, $this->doc->name, implode("\n", $output)));
                }
            } else {
                $this->addError(ErrorCode::getError('CLASS0003', $this->className, $this->doc->name));
            }
        }
    }
    /**
     * Check PHP syntax of file (lint)
     *
     * @param string $fileName
     * @param array $output Error message
     * @return bool bool(true) if correct or bool(false) if error
     */
    public static function phpLintFile($fileName, &$output)
    {
        exec(sprintf('php -n -l %s 2>&1', escapeshellarg($fileName)) , $output, $status);
        return ($status === 0);
    }
    
    protected function checkInherit()
    {
        try {
            $o = new ReflectionClass('\\' . $this->className);
            if (!$o->isInstantiable()) {
                $this->addError(ErrorCode::getError('CLASS0005', $this->className, $this->fileName, $this->doc->name));
            }
            if ($this->doc) {
                if ($this->doc->fromid > 0) {
                    
                    $fromName = ucwords(strtolower(getNameFromId(getDbAccess() , $this->doc->fromid)));
                    if (!$fromName) {
                        $this->addError(ErrorCode::getError('CLASS0007', $this->className, $this->fileName, $this->doc->name));
                        return;
                    }
                } else {
                    $fromName = "Document";
                }
                
                $parentClass = '\\Dcp\Family\\' . $fromName;
                
                if (!$o->isSubclassOf($parentClass)) {
                    $this->addError(ErrorCode::getError('CLASS0006', $this->className, $this->fileName, $parentClass, $this->doc->name));
                }
            }
        }
        catch(\Exception $e) {
            $this->addError(ErrorCode::getError('CLASS0004', $this->className, $this->fileName, $this->doc->name, $e->getMessage()));
        }
    }
}
