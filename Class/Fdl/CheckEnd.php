<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

class CheckEnd extends CheckData
{
    /**
     * @var Doc
     */
    protected $doc;
    /**
     * @param array $data
     * @param Doc $doc
     * @return CheckEnd
     */
    public function check(array $data, &$doc = null)
    {
        $this->doc = $doc;
        if ($doc->usefor == 'W') {
            $checkW = new CheckWorkflow($doc->classname, $doc->name);
            $checkCr = $checkW->verifyWorkflowComplete();
            if (count($checkCr) > 0) {
                $this->addError(implode("\n", $checkCr));
            }
        }
        
        $this->checkComputedAttributes();
        return $this;
    }
    
    protected function checkComputedAttributes()
    {
        $this->doc->getAttributes(); // force reattach attributes
        $la = $this->doc->GetNormalAttributes();
        foreach ($la as & $oa) {
            if (($oa->phpfile == '' || $oa->phpfile == '-') && (preg_match('/^[a-z0-9_]*::/', $oa->phpfunc))) {
                $this->checkMethod($oa);
            }
        }
    }
    
    private function checkMethod(NormalAttribute & $oa)
    {
        $oParse = new parseFamilyMethod();
        $strucFunc = $oParse->parse($oa->phpfunc);
        $error=$oParse->getError();
        if ($error) {
             $this->addError(ErrorCode::getError('ATTR1262', $oa->phpfunc, $oa->id, $error));

        } else {

        $phpMethName = $strucFunc->methodName;
        if ($strucFunc->className) {
            $phpClassName = $strucFunc->className;
        } else {
            $phpClassName = sprintf("Doc%d", $this->doc->id);
        }
        $phpLongName = $strucFunc->className . '::' . $phpMethName;
        try {
            $refMeth = new ReflectionMethod($phpClassName, $phpMethName);
            $numArgs = $refMeth->getNumberOfRequiredParameters();
            if ($numArgs > count($strucFunc->inputs)) {
                $this->addError(ErrorCode::getError('ATTR1261', $phpLongName, $numArgs, $oa->id));
            }
        }
        catch(Exception $e) {
            $this->addError(ErrorCode::getError('ATTR1260', $phpLongName, $oa->id));
        }
        }
    }
}
