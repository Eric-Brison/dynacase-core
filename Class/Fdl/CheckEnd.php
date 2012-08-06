<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

class CheckEnd extends CheckData
{
    /**
     * max column for a table in postgresql
     */
    const maxSqlColumn = 1600;
    /**
     * @var DocFam
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
        if (strstr($doc->usefor, 'W')) {
            $checkW = new CheckWorkflow($doc->classname, $doc->name);
            $checkCr = $checkW->verifyWorkflowComplete();
            if (count($checkCr) > 0) {
                $this->addError(implode("\n", $checkCr));
            }
        }
        
        $this->checkSetAttributes();
        $this->checkComputedConstraintAttributes();
        $this->checkDefault();
        $this->checkLinks();
        return $this;
    }
    private function getColumnCount()
    {
        
        $c = count($this->doc->fields) + count($this->doc->sup_fields);
        $ancestor = $this->doc->getFathersDoc();
        $ancestor[] = $this->doc->id;
        
        $sql = sprintf("select count(*) from docattr where type != 'frame' and type != 'tab' and type != 'array' and %s", GetSqlCond($ancestor, "docid", true));
        simpleQuery('', $sql, $r, true, true);
        $c+= $r;
        return $c;
    }
    /**
     * Verify if max sql column is reached
     * @param Doc $doc
     */
    public function checkMaxAttributes(Doc & $doc)
    {
        $this->doc = $doc;
        $c = $this->getColumnCount();
        if ($c > self::maxSqlColumn) {
            $this->addError(ErrorCode::getError('ATTR1701', $c, self::maxSqlColumn));
        }
    }
    protected function checkSetAttributes()
    {
        $this->doc->getAttributes(); // force reattach attributes
        $la = $this->doc->GetNormalAttributes();
        foreach ($la as & $oa) {
            $foa = $oa->fieldSet;
            if (!$foa) {
                $this->addError(ErrorCode::getError('ATTR0203', $oa->id));
            } elseif ((!is_a($foa, "FieldSetAttribute")) && ($foa->type != 'array')) {
                $this->addError(ErrorCode::getError('ATTR0204', $foa->id, $oa->id));
            } else {
                $type = $oa->type;
                $ftype = $oa->fieldSet->type;
                if (($ftype != 'frame') && ($ftype != 'array')) {
                    $this->addError(ErrorCode::getError('ATTR0205', $foa->id, $oa->id));
                } elseif ($ftype == 'array') {
                    if ($oa->needed) {
                        $this->addError(ErrorCode::getError('ATTR0902', $oa->id));
                    }
                }
            }
        }
        
        $la = $this->doc->GetFieldAttributes();
        foreach ($la as & $oa) {
            $foa = $oa->fieldSet;
            if ($foa) {
                $type = $oa->type;
                $ftype = $oa->fieldSet->type;
                if (($type == 'frame') && ($ftype != 'tab') && ($oa->fieldSet->id != BasicAttribute::hiddenFieldId)) {
                    $this->addError(ErrorCode::getError('ATTR0207', $foa->id, $oa->id));
                }
            }
        }
    }
    
    protected function checkComputedConstraintAttributes()
    {
        $this->doc->getAttributes(); // force reattach attributes
        $la = $this->doc->GetNormalAttributes();
        foreach ($la as & $oa) {
            if (($oa->phpfile == '' || $oa->phpfile == '-') && (preg_match('/^[a-z0-9_]*::/i', $oa->phpfunc))) {
                $this->checkMethod($oa);
            }
            if (preg_match('/^[a-z0-9_]*::/i', $oa->phpconstraint)) {
                $this->checkConstraint($oa);
            }
        }
    }
    /**
     * check method validity for phpfunc property
     * @param NormalAttribute $oa
     */
    private function checkMethod(NormalAttribute & $oa)
    {
        $oParse = new parseFamilyMethod();
        $strucFunc = $oParse->parse($oa->phpfunc);
        $error = $oParse->getError();
        if ($error) {
            $this->addError(ErrorCode::getError('ATTR1262', $oa->phpfunc, $oa->id, $error));
        } else {
            
            $err = $this->verifyMethod($strucFunc, $oa);
            if ($err) {
                $this->addError($err);
                $this->addError(ErrorCode::getError('ATTR1265', $this->doc->name, $err));
            }
        }
    }
    /**
     * Verify all links which references document's method
     */
    protected function checkLinks()
    {
        $la = $this->doc->getAttributes();
        foreach ($la as & $oa) {
            if ($oa) $this->checkLinkMethod($oa);
        }
    }
    /**
     * check method validity for phpfunc property
     * @param NormalAttribute|MenuAttribute $oa
     */
    private function checkLinkMethod(BasicAttribute & $oa)
    {
        if (empty($oa->link)) return;
        $link = '';
        if (preg_match('/action=FDL_METHOD&.*method=([^&]*)/', $oa->link, $reg)) {
            $link = urldecode($reg[1]);
            if (preg_match('/^[a-z0-9_]+$/i', $link)) $link = '::' . $link . '()';
        } elseif (preg_match('/^[a-z0-9_]*::/i', $oa->link, $reg)) {
            $link = $oa->link;
        }
        if (!$link) return;
        //og($oa->id. '=>'.$oa->link);
        $oParse = new parseFamilyMethod();
        $strucLink = $oParse->parse($link);
        $error = $oParse->getError();
        if ($error) {
            $this->addError(ErrorCode::getError('ATTR1000', $link, $oa->id, $error));
        } else {
            /**
             * @var ReflectionMethod $refMeth
             */
            $err = $this->verifyMethod($strucLink, $oa, $refMeth);
            if ($err) {
                $this->addError($err);
                $this->addError(ErrorCode::getError('ATTR1001', $this->doc->name, $err));
            } else {
                $methodComment = $refMeth->getDocComment();
                if (!preg_match('/@apiExpose\b/', $methodComment)) {
                    $completeMethod = $refMeth->getDeclaringClass()->getName() . '::' . $refMeth->getName() . '()';
                    $this->addError(ErrorCode::getError('ATTR1002', $completeMethod, $oa->id));
                }
            }
        }
    }
    private function checkDefault()
    {
        $defaults = $this->doc->getDefValues();
        foreach ($defaults as $attrid => $def) {
            /**
             * @var $oa NormalAttribute
             */
            $oa = $this->doc->getAttribute($attrid);
            if (!$oa) {
                $this->addError(ErrorCode::getError('DFLT0005', $attrid, $this->doc->name));
            } else {
                $oParse = new parseFamilyMethod();
                $strucFunc = $oParse->parse($def);
                $error = $oParse->getError();
                if (!$error) {
                    
                    $err = $this->verifyMethod($strucFunc, $oa);
                    if ($err) {
                        $this->addError(ErrorCode::getError('DFLT0004', $this->doc->name, $err));
                    }
                }
            }
        }
    }
    
    private function verifyMethod($strucFunc, $oa, &$refMeth = null)
    {
        $err = '';
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
                $err = (ErrorCode::getError('ATTR1261', $phpLongName, $numArgs, $oa->id));
            } else {
                if ($strucFunc->className && (!$refMeth->isStatic())) {
                    $err = (ErrorCode::getError('ATTR1263', $phpLongName, $oa->id));
                }
            }
        }
        catch(Exception $e) {
            $err = (ErrorCode::getError('ATTR1260', $phpLongName, $oa->id));
        }
        return $err;
    }
    /**
     * check method validity for constraint property
     * @param NormalAttribute $oa
     */
    private function checkConstraint(NormalAttribute & $oa)
    {
        $oParse = new parseFamilyMethod();
        $strucFunc = $oParse->parse($oa->phpconstraint, true);
        $error = $oParse->getError();
        if ($error) {
            $this->addError(ErrorCode::getError('ATTR1404', $oa->phpconstraint, $oa->id, $error));
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
                    $this->addError(ErrorCode::getError('ATTR1401', $phpLongName, $numArgs, $oa->id));
                } else {
                    if ($strucFunc->className && (!$refMeth->isStatic())) {
                        $this->addError(ErrorCode::getError('ATTR1403', $phpLongName, $oa->id));
                    }
                }
            }
            catch(Exception $e) {
                $this->addError(ErrorCode::getError('ATTR1402', $phpLongName, $oa->id));
            }
        }
    }
}
