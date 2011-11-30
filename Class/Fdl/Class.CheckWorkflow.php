<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Verify several point for the integrity of a workflow
 *
 * @author Anakeen 2007
 * @version $Id: checklist.php,v 1.8 2008/12/31 14:37:26 jerome Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
/**
 */

class checkWorkflow
{
    /**
     * @var array
     */
    private $terr = array();
    /**
     * @var Wdoc
     */
    private $wdoc;
    /**
     * @var string
     */
    private $className;
    public function __construct($className)
    {
        $this->className = $className;
    }
    
    private function addError($msg)
    {
        if ($msg) $this->terr[] = $msg;
    }
    public function getErrorMessage()
    {
        return implode("\n", $this->terr);
    }
    public function getError()
    {
        return $this->terr;
    }
    public function verifyWorflow()
    {
        $this->checkClassName();
        if (!$this->getErrorMessage()) {
            $this->checkIsAWorkflow();
            if (!$this->getErrorMessage()) {
                $this->checkPrefix();
                $this->checkAsk();
            }
        }
        return $this->getError();
    }
    
    public function checkAsk()
    {
    }
    
    public function checkIsAWorkflow()
    {
        /* set_error_handler(array(
            $this,
            "errorHandler"
        ));
        */
        // Sort out the formatting of the filename
        $fileName = realpath($this->getWorkflowClassFile());
        // Get the shell output from the syntax check command
        exec(sprintf('php -l %s 2>/dev/null', escapeshellarg($fileName)) , $output, $status);
        if ($status != 0) {
            $this->addError(implode("\n", $output));
        } else {
            include_once ($this->getWorkflowClassFile());
            if (!class_exists($this->className)) {
                $this->addError(sprintf("workflow class %s not found", $this->className));
            } else {
                $class = $this->className;
                $this->wdoc = new $class();
            }
        }
        //restore_error_handler();
        
    }
    
    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        
        $this->addError("handler:" . $errstr);
    }
    
    public function checkPrefix()
    {
        if (!$this->wdoc->attrPrefix) {
            $this->addError(sprintf("workflow : missing attrPrefix definition for %s class", $this->className));
        } elseif (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]{0,9}$/', $this->wdoc->attrPrefix)) {
            $this->addError(sprintf("workflow : syntax error attrPrefix for %s class (limit to 10 alpha characters)", $this->className));
        }
    }
    
    public function checkClassName()
    {
        if (empty($this->className)) {
            $this->addError(sprintf("workflow class name is empty"));
        } elseif (!self::checkPhpClass($this->className)) {
            $this->addError(sprintf("class name %s not valid", $this->className));
        } else {
            $this->checkFileName();
        }
    }
    
    public function checkFileName()
    {
        if (!file_exists($this->getWorkflowClassFile())) {
            $this->addError(sprintf("file name for %s not found", $this->className));
        }
    }
    public static function checkPhpClass($name)
    {
        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]+$/', $name)) {
            return true;
        } else {
            return false;
        }
    }
    
    public function getWorkflowClassFile()
    {
        return sprintf('FDL/Class.%s.php', $this->className);
    }
}
?>
