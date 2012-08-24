<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * update attribut for a document set
 *
 * @author Anakeen
 * @version $Id:  $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Format document list to be easily used in
 * @class UpdateAttribute
 * @code
 *      $s = new \SearchDoc('', "MY_FAMILY");
 $s->setObjectReturn();
 $dl=new DocumentList($s);
 $fc = new \UpdateAttribute();
 $fc->useCollection($dl);
 * @endcode
 */
class UpdateAttribute
{
    /**
     * @var DocumentList $dl
     */
    public $dl = null;
    private $dbaccess;
    private $useRevision = false;
    private $revisionComment = false;
    private $historyComment = '';
    private $statusFile = '';
    private $famid = '';
    private $transaction = false;
    private $useProfiling = false;
    private $error = '';
    /**
     * @var DbObj
     */
    private $transactionObject = null;
    /**
     * @var UpdateAttributeResults[]
     */
    private $results = array();
    
    public function __construct()
    {
        $this->dbaccess = getDbAccess();
    }
    
    private function getFamily()
    {
        $search = $this->dl->getSearchDocument();
        $this->famid = '';
        if ($search) {
            $famName = $search->fromid;
            if (!is_numeric($famName)) $this->famid = getFamIdFromName($this->dbaccess, $famName);
            else $this->famid = $famName;
        }
        return $this->famid;
    }
    /**
     * document list to process
     * @param DocumentList $l
     * @throws Dcp\Upat\Exception
     * @return UpdateAttribute
     */
    public function useCollection(DocumentList & $l)
    {
        $this->dl = $l;
        $s = $l->getSearchDocument();
        if (!$s->isObjectReturn()) throw new \Dcp\Upat\Exception(ErrorCode::getError("UPAT0002"));
        return $this;
    }
    /**
     * active revision before update documents
     * @param string $revisionComment
     * @return UpdateAttribute
     */
    public function addRevision($revisionComment = '')
    {
        $this->useRevision = true;
        $this->revisionComment = $revisionComment;
        return $this;
    }
    /**
     * active revision before update documents
     * @param string $historyComment
     * @return UpdateAttribute
     */
    public function addHistoryComment($historyComment = '')
    {
        $this->historyComment = $historyComment;
        return $this;
    }
    /**
     * all modification are done in a sql transaction
     * if only one request failed, none documents are modified
     * default no use transaction
     * @param bool $transaction
     * @return UpdateAttribute
     */
    public function useTransaction($transaction = true)
    {
        $this->transaction = $transaction;
        return $this;
    }
    /**
     * use status file when use it in background processing
     * @param string $statusFile file path where partial status are written
     * @return UpdateAttribute
     */
    public function setStatusFile($statusFile)
    {
        $this->statusFile = $statusFile;
        return $this;
    }
    /**
     * add post action to recompute profiling for each modified documents in case of dynamic attribute references
     * @param bool $profiling
     * @return UpdateAttribute
     */
    public function useProfileUpdating($profiling = true)
    {
        $this->useProfiling = $profiling;
        return $this;
    }
    
    public function getError()
    {
        return $this->error;
    }
    
    protected function executeProfiling(array $ids)
    {
        $dl = new DocumentList();
        $dl->addDocumentIdentifiers($ids);
        $c = 0;
        $nbR = count($ids);
        /**
         * @var Doc $doc
         */
        foreach ($dl as $doc) {
            $err = $doc->computeDProfil();
            /**
             * @var UpdateAttributeStatus $r
             */
            $r = & $this->results[$doc->initid];
            $r->profiled = ($err == '');
            $r->profilingError = $err;
            $this->error.= $err;
            $c++;
            if ($c % 10 == 0) $this->logStatus(sprintf(_("%d/%d profiling done") , $c, $nbR));
        }
        $this->logStatus(sprintf(_("%d/%d profiling done") , $c, $nbR));
    }
    /**
     * process a revision for each documents
     */
    protected function executeRevision(array $ids)
    {
        $this->logStatus(sprintf(_("process revision for %d documents") , $this->dl->length));
        /**
         * @var Doc $doc
         */
        $c = 0;
        $nbR = count($ids);
        foreach ($this->dl as $doc) {
            if (isset($ids[$doc->id])) {
                $err = $doc->addRevision($this->revisionComment);
                /**
                 * @var UpdateAttributeStatus $r
                 */
                $r = & $this->results[$doc->initid];
                $r->revised = ($err == '');
                $r->revisionError = $err;
                $this->error.= $err;
                $c++;
                if ($c % 10 == 0) $this->logStatus(sprintf(_("%d/%d revision done") , $c, $nbR));
            }
        }
        $this->logStatus(sprintf(_("%d/%d revision done") , $c, $nbR));
        $this->logStatus(sprintf(_("process revision done")));
    }
    /**
     * add history item for each document
     * @param int[] $ids document identicators to process
     * @return void
     */
    protected function executeHistory($ids)
    {
        
        $this->logStatus(sprintf(_("process history for %d documents") , count($ids)));
        $sql = "insert into dochisto (id,initid,uid,uname,date,level,code,comment ) values ";
        $vs = array();
        $u = getCurrentUser();
        $uid = $u->id;
        $uname = trim($u->firstname . ' ' . $u->lastname);
        $date = Doc::getTimeDate(0, true);
        $level = HISTO_MESSAGE;
        $code = "UPDATE";
        foreach ($ids as $id => $initid) {
            $vs[] = sprintf("(%d,%d,%d,'%s','%s',%d,'%s','%s')", $id, $initid, $uid, $uname, $date, $level, $code, pg_escape_string($this->historyComment));
        }
        $sql.= implode(',', $vs);
        simpleQuery($this->dbaccess, $sql);
        
        foreach ($ids as $id => $initid) {
            $this->results[$initid]->historyUpdated = true;
        }
        $this->logStatus(sprintf(_("Update history done")));
    }
    
    private function executeSetValue(array $ids, $attrid, $newValue)
    {
        $this->logStatus(sprintf(_("process setValue for %d documents") , count($ids)));
        if (is_array($newValue)) $newValue = Doc::_array2val($newValue);
        $this->logStatus(sprintf(_("argument %s=>%s") , $attrid, $newValue));
        $sql = sprintf("update doc%s set \"%s\"=E'%s' where locked != -1 and initid in (%s)", $this->famid, ($attrid) , pg_escape_string($newValue) , implode(',', $ids));
        simpleQuery($this->dbaccess, $sql);
        foreach ($ids as $id => $initid) {
            $this->results[$initid]->changed = true;
        }
        
        $this->logStatus(sprintf(_("%d documents are updated") , count($ids)));
    }
    
    private function executeRemoveValue(array $ids, $attrid, $valueToRemove)
    {
        $this->logStatus(sprintf(_("process removeValue for %d documents") , count($ids)));
        
        if (is_array($valueToRemove)) {
            foreach ($valueToRemove as $k => $v) {
                $valueToRemove[$k] = pg_escape_string($v);
            }
            $searchValue = implode('|', $valueToRemove);
        } else {
            $searchValue = pg_escape_string($valueToRemove);
        }
        $this->logStatus(sprintf(_("argument %s=>%s") , $attrid, $valueToRemove));
        $sql = sprintf("update doc%s set \"%s\"=regexp_replace(\"%s\",E'(\\\\A|\\n|<BR>)(%s)(\\\\Z|\n|<BR>)',E'\\\\1\\\\3','g')  where locked != -1 and initid in (%s)", $this->famid, $attrid, $attrid, $searchValue, implode(',', $ids));
        simpleQuery($this->dbaccess, $sql);
        
        $oa = $this->getFamilyAttribute($attrid);
        $singleMultiple = $doubleMultiple = false;
        if ($oa->inArray() && $oa->getOption("multiple") == "yes") {
            $doubleMultiple = true;
        } elseif ($oa->isMultiple()) {
            $singleMultiple = true;
        }
        if ($oa->getOption("multiple") == "yes") {
            if ($singleMultiple) {
                $sql = sprintf("update doc%s set \"%s\"=regexp_replace(\"%s\", E'(\\\\A\\n)|(\\n\\\\Z)', '','g')  where locked != -1 and initid in (%s)", $this->famid, $attrid, $attrid, implode(',', $ids));
                simpleQuery($this->dbaccess, $sql);
                $sql = sprintf("update doc%s set \"%s\"=regexp_replace(\"%s\", E'([\\n]+)', E'\n','g')  where locked != -1 and initid in (%s)", $this->famid, $attrid, $attrid, implode(',', $ids));
                simpleQuery($this->dbaccess, $sql);
            } elseif ($doubleMultiple) {
                $sql = sprintf("update doc%s set \"%s\"=regexp_replace(\"%s\", '(\\\\A<BR>)|(<BR>\\\\Z)', '','g')  where locked != -1 and initid in (%s)", $this->famid, $attrid, $attrid, implode(',', $ids));
                simpleQuery($this->dbaccess, $sql);
                $sql = sprintf("update doc%s set \"%s\"=regexp_replace(\"%s\", '(<BR>)+', '<BR>','g')  where locked != -1 and initid in (%s)", $this->famid, $attrid, $attrid, implode(',', $ids));
                simpleQuery($this->dbaccess, $sql);
            }
        }
        foreach ($ids as $id => $initid) {
            $this->results[$initid]->changed = true;
        }
        
        $this->logStatus(sprintf(_("%d documents are updated") , count($ids)));
    }
    private function executeAddValue(array $ids, $attrid, $valueToAdd)
    {
        $pattrid = pg_escape_string(strtolower($attrid));
        $this->logStatus(sprintf(_("process addValue for %d documents") , count($ids)));
        if (is_array($valueToAdd)) $valueToAdd = Doc::_array2val($valueToAdd);
        
        $this->logStatus(sprintf(_("argument %s=>%s") , $attrid, $valueToAdd));
        $oa = $this->getFamilyAttribute($attrid);
        if ($oa->inArray() && $oa->getOption("multiple") == "yes") {
            //double multiple
            $sql = sprintf("update doc%s set \"%s\"=regexp_replace(\"%s\",E'\$',E'<BR>%s','gn')  where locked != -1 and \"%s\" is not null and initid in (%s)", $this->famid, $pattrid, $pattrid, pg_escape_string($valueToAdd) , $pattrid, implode(',', $ids));
            
            simpleQuery($this->dbaccess, $sql);
            // trim when is first
            $sql = sprintf("update doc%s set \"%s\"=regexp_replace(\"%s\",E'^<BR>','','gn')  where locked != -1 and \"%s\" is not null and initid in (%s)", $this->famid, $pattrid, $pattrid, $pattrid, implode(',', $ids));
            
            simpleQuery($this->dbaccess, $sql);
        } else {
            // add if not null
            $sql = sprintf("update doc%s set \"%s\"=\"%s\" || E'\\n%s' where locked != -1 and \"%s\" is not null and initid in (%s)", $this->famid, $pattrid, $pattrid, pg_escape_string($valueToAdd) , $pattrid, implode(',', $ids));
            
            simpleQuery($this->dbaccess, $sql);
            // set when is null
            $sql = sprintf("update doc%s set \"%s\"= E'%s' where locked != -1 and \"%s\" is null and initid in (%s)", $this->famid, $pattrid, pg_escape_string($valueToAdd) , $pattrid, implode(',', $ids));
            
            simpleQuery($this->dbaccess, $sql);
        }
        foreach ($ids as $id => $initid) {
            $this->results[$initid]->changed = true;
        }
        
        $this->logStatus(sprintf(_("%d documents are updated") , count($ids)));
    }
    
    private function executeReplaceValue(array $ids, $attrid, $oldvalue, $newValue)
    {
        $this->logStatus(sprintf(_("process replaceValue for %d documents") , count($ids)));
        
        if (is_array($newValue)) $newValue = Doc::_array2val($newValue);
        
        $this->logStatus(sprintf(_("argument %s=>%s") , $attrid, $oldvalue . '/' . $newValue));
        $this->getFamily();
        //  replace for multiple
        $sql = sprintf("update doc%s set \"%s\"= regexp_replace(\"%s\",E'(\\\\A|\\n)%s(\\\\Z|\\n)',E'\\\\1%s\\\\2','g')  where locked != -1  and initid in (%s)", $this->famid, strtolower($attrid) , strtolower($attrid) , pg_escape_string($oldvalue) , pg_escape_string($newValue) , implode(',', $ids));
        simpleQuery($this->dbaccess, $sql);
        
        foreach ($ids as $id => $initid) {
            $this->results[$initid]->changed = true;
        }
        
        $this->logStatus(sprintf(_("%d documents are updated") , count($ids)));
    }
    /**
     * change value of an attribute for docment list
     * @param string $attrid attribute id
     * @param string|string[] $newValue new value to set for attribute
     * @throws Dcp\Upat\Exception
     */
    public function setValue($attrid, $newValue)
    {
        $oa = $this->getFamilyAttribute($attrid);
        $attrid = $oa->id;
        $this->getFamilyAttribute($attrid);
        $this->logStatus("BEGIN");
        if (!CheckAttr::checkAttrSyntax($attrid)) {
            $this->logStatus("END");
            throw new \Dcp\Upat\Exception(ErrorCode::getError("UPAT0001", $attrid));
        }
        
        $newValue = $this->name2id($oa, $newValue);
        $ids = array();
        $upToDateIds = array();
        /**
         * @var Doc $doc
         */
        foreach ($this->dl as $doc) {
            if ($doc->getValue($attrid) != $newValue) $ids[$doc->id] = intval($doc->initid);
            else $upToDateIds[$doc->id] = intval($doc->initid);
            $this->results[$doc->initid] = new UpdateAttributeResults();
        }
        foreach ($upToDateIds as $id => $initid) {
            $this->results[$initid]->changed = false;
        }
        
        $this->logStatus(sprintf(_("process %s") , json_encode(array_keys($ids))));
        if ($ids) {
            $this->beginTransaction();
            if ($this->historyComment) $this->executeHistory($ids + $upToDateIds);
            if ($this->useRevision) $this->executeRevision($ids);
            $this->executeSetValue($ids, $attrid, $newValue);
            
            if ($this->useProfiling) $this->executeProfiling($ids);
            $this->endTransaction();
        }
        $this->logStatusReport();
        $this->logStatus("END");
    }
    /**
     * replace value by another for an document list attribute
     * @param string $attrid attribute id
     * @param string $oldValue value to replace
     * @param string|string[] $newValue new value to set for attribute
     * @throws Dcp\Upat\Exception
     */
    public function replaceValue($attrid, $oldValue, $newValue)
    {
        
        $oa = $this->getFamilyAttribute($attrid);
        $attrid = $oa->id;
        
        $this->getFamilyAttribute($attrid);
        $this->logStatus("BEGIN");
        if (!CheckAttr::checkAttrSyntax($attrid)) {
            $this->logStatus("END");
            throw new \Dcp\Upat\Exception(ErrorCode::getError("UPAT0001", $attrid));
        }
        $ids = array();
        $upToDateIds = array();
        $oldValue = $this->name2id($oa, $oldValue);
        $newValue = $this->name2id($oa, $newValue);
        /**
         * @var Doc $doc
         */
        foreach ($this->dl as $doc) {
            if (preg_match(sprintf('/\b%s\b/', preg_quote($oldValue)) , $doc->getValue($attrid))) $ids[$doc->id] = intval($doc->initid);
            else $upToDateIds[$doc->id] = intval($doc->initid);
            $this->results[$doc->initid] = new UpdateAttributeResults();
        }
        foreach ($upToDateIds as $id => $initid) {
            $this->results[$initid]->changed = false;
        }
        
        $this->logStatus(sprintf(_("process %s") , json_encode(array_keys($ids))));
        if ($ids) {
            $this->beginTransaction();
            if ($this->historyComment) $this->executeHistory($ids + $upToDateIds);
            if ($this->useRevision) $this->executeRevision($ids);
            $this->executeReplaceValue($ids, $attrid, $oldValue, $newValue);
            if ($this->useProfiling) $this->executeProfiling($ids);
            $this->endTransaction();
        }
        $this->logStatusReport();
        $this->logStatus("END");
    }
    /**
     * @param $attrid
     * @throws Dcp\Upat\Exception
     * @return NormalAttribute
     */
    private function getFamilyAttribute($attrid)
    {
        $famid = $this->getFamily();
        if (!$famid) throw new \Dcp\Upat\Exception(ErrorCode::getError("UPAT0006"));
        $fam = new_doc($this->dbaccess, $famid);
        if (!$fam->isAlive()) throw new \Dcp\Upat\Exception(ErrorCode::getError("UPAT0005", $this->getFamily()));
        $oa = $fam->getAttribute($attrid);
        if (!$oa) throw new \Dcp\Upat\Exception("UPAT0004", $attrid, $fam->getTitle());
        return $oa;
    }
    /**
     * replace value by another for an document list attribute
     * @param string $attrid attribute id
     * @param string|\string[] $valueToAdd
     * @throws Dcp\Upat\Exception
     */
    public function addValue($attrid, $valueToAdd)
    {
        $oa = $this->getFamilyAttribute($attrid);
        $attrid = $oa->id;
        $singleMultiple = $doubleMultiple = false;
        if ($oa->inArray() && $oa->getOption("multiple") == "yes") {
            $doubleMultiple = true;
        } elseif ($oa->isMultiple()) {
            $singleMultiple = true;
        }
        
        if (!$singleMultiple && !$doubleMultiple) {
            throw new \Dcp\Upat\Exception("UPAT0007", $attrid, $oa->docname);
        }
        
        $valueToAdd = $this->name2id($oa, $valueToAdd);
        $ids = array();
        $upToDateIds = array();
        if ($oa->inArray() && $singleMultiple) {
            /**
             * @var Doc $doc
             */
            foreach ($this->dl as $doc) {
                $ids[$doc->id] = intval($doc->initid);
                
                $this->results[$doc->initid] = new UpdateAttributeResults();
            }
            foreach ($upToDateIds as $initid) {
                $this->results[$initid]->changed = false;
            }
        } elseif ((!$oa->inArray()) && $singleMultiple) {
            if (is_array($valueToAdd)) {
                throw new \Dcp\Upat\Exception("UPAT0008", $attrid, $oa->docname);
            }
            /**
             * @var Doc $doc
             */
            foreach ($this->dl as $doc) {
                if (!in_array($valueToAdd, $doc->getTValue($attrid))) $ids[$doc->id] = intval($doc->initid);
                else $upToDateIds[$doc->id] = intval($doc->initid);
                
                $this->results[$doc->initid] = new UpdateAttributeResults();
            }
            foreach ($upToDateIds as $initid) {
                $this->results[$initid]->changed = false;
            }
        } elseif ($doubleMultiple) {
            if (is_array($valueToAdd)) {
                throw new \Dcp\Upat\Exception("UPAT0008", $attrid, $oa->docname);
            }
            /**
             * @var Doc $doc
             */
            foreach ($this->dl as $doc) {
                $ids[$doc->id] = intval($doc->initid);
                
                $this->results[$doc->initid] = new UpdateAttributeResults();
            }
            foreach ($upToDateIds as $initid) {
                $this->results[$initid]->changed = false;
            }
        }
        $this->logStatus("BEGIN");
        
        $this->logStatus(sprintf(_("process %s") , json_encode(array_keys($ids))));
        if ($ids) {
            $this->beginTransaction();
            if ($this->historyComment) $this->executeHistory($ids + $upToDateIds);
            if ($this->useRevision) $this->executeRevision($ids);
            $this->executeAddValue($ids, $attrid, $valueToAdd);
            if ($this->useProfiling) $this->executeProfiling($ids);
            $this->endTransaction();
        }
        $this->logStatusReport();
        $this->logStatus("END");
    }
    
    protected function logStatusReport()
    {
        $this->logStatus("JSON:" . json_encode($this->getResults()));
        $this->logStatus("PHP:" . serialize($this->getResults()));
    }
    /**
     * replace value by another for an document list attribute
     * @param string $attrid attribute id
     * @param string|\string[] $valueToRemove
     * @throws Dcp\Upat\Exception
     */
    public function removeValue($attrid, $valueToRemove)
    {
        $oa = $this->getFamilyAttribute($attrid);
        $attrid = $oa->id;
        $singleMultiple = $doubleMultiple = false;
        if ($oa->inArray() && $oa->getOption("multiple") == "yes") {
            $doubleMultiple = true;
        } elseif ($oa->isMultiple()) {
            $singleMultiple = true;
        }
        
        if (!$singleMultiple && !$doubleMultiple) {
            throw new \Dcp\Upat\Exception("UPAT0009", $attrid, $oa->docname);
        }
        $valueToRemove = $this->name2id($oa, $valueToRemove);
        
        $ids = array();
        $upToDateIds = array();
        /**
         * @var Doc $doc
         */
        foreach ($this->dl as $doc) {
            if (is_array($valueToRemove)) {
                if (array_intersect($this->linearize($doc->getValue($attrid)) , $valueToRemove)) {
                    $ids[$doc->id] = intval($doc->initid);
                } else $upToDateIds[$doc->id] = intval($doc->initid);
            } elseif (in_array($valueToRemove, $this->linearize($doc->getValue($attrid)))) $ids[$doc->id] = intval($doc->initid);
            else $upToDateIds[$doc->id] = intval($doc->initid);
            
            $this->results[$doc->initid] = new UpdateAttributeResults();
        }
        foreach ($upToDateIds as $initid) {
            $this->results[$initid]->changed = false;
        }
        
        $this->logStatus("BEGIN");
        
        $this->logStatus(sprintf(_("process %s") , json_encode(array_keys($ids))));
        if ($ids) {
            $this->beginTransaction();
            if ($this->historyComment) $this->executeHistory($ids + $upToDateIds);
            if ($this->useRevision) $this->executeRevision($ids);
            $this->executeRemoveValue($ids, $attrid, $valueToRemove);
            if ($this->useProfiling) $this->executeProfiling($ids);
            $this->endTransaction();
        }
        $this->logStatusReport();
        $this->logStatus("END");
    }
    
    private function beginTransaction()
    {
        if ($this->transaction) {
            if (!$this->transactionObject) {
                $this->transactionObject = new DbObj($this->dbaccess);
            }
            $this->transactionObject->savePoint("UPDATEATTR");
        }
    }
    
    private function endTransaction()
    {
        if ($this->transaction) {
            if ($this->error) {
                $this->transactionObject->rollbackPoint("UPDATEATTR");
            } else {
                $this->transactionObject->commitPoint("UPDATEATTR");
            }
        }
    }
    
    private function name2id($oa, $values)
    {
        if ($oa->type == "docid" || $oa->type == "account") {
            if (is_array($values)) {
                foreach ($values as $ka => $aValue) {
                    $aValue = $this->getInitIdFromName($aValue);
                    $values[$ka] = $aValue;
                }
            } else {
                if (!is_numeric($values)) $values = $this->getInitIdFromName($values);
            }
        }
        
        return $values;
    }
    /**
     * fet initial document identifier from name
     * @param string $name
     * @return int
     */
    public static function getInitIdFromName($name)
    {
        simpleQuery(getDbAccess() , sprintf("select initid from docread where name = '%s' limit 1", pg_escape_string($name)) , $initid, true, true);
        return $initid;
    }
    private function linearize($v)
    {
        $t = explode("\n", str_replace('<BR>', "\n", $v));
        foreach ($t as $k => $vt) {
            if (!$vt) unset($t[$k]);
        }
        return $t;
    }
    public function logStatus($s)
    {
        if ($this->statusFile) {
            $st = getDebugStack(2);
            
            $f = $st[0]["function"];
            if ($f == "include") $f = '-';
            file_put_contents($this->statusFile, sprintf("%s %s %s\n", date("Y-m-d\\TH:i:s") , $f, $s) , FILE_APPEND);
        }
    }
    /**
     * call ::setValue() in a background process
     * the return filename can be use by UpdateAttributeStatus to see information trace from background process
     * @see UpdateAttributeStatus
     * @param string $attrid
     * @param string|string[] $newValue
     * @return string filename status
     */
    public function bgSetValue($attrid, $newValue)
    {
        $tmpThis = tempnam(getTmpDir() , 'uptSetValue');
        file_put_contents($tmpThis, serialize($this));
        $tmpArgs = tempnam(getTmpDir() , 'argSetValue');
        file_put_contents($tmpArgs, serialize(func_get_args()));
        $wsh = getWshCmd(true, getCurrentUser()->id);
        $tmpStatus = tempnam(getTmpDir() , 'statusSetValue');
        
        $cmd[] = sprintf("$wsh  --api=updateAttribute --objectFile=%s --argsFile=%s --statusFile=%s --method=setValue", escapeshellarg($tmpThis) , escapeshellarg($tmpArgs) , escapeshellarg($tmpStatus));
        
        bgexec($cmd, $result, $err);
        return $tmpStatus;
    }
    /**
     * call ::addValue() in a background process
     * the return filename can be use by UpdateAttributeStatus to see information trace from background process
     * @see UpdateAttributeStatus
     * @param string $attrid attribute id
     * @param string|\string[] $valueToAdd
     * @return string filename status
     */
    public function bgAddValue($attrid, $valueToAdd)
    {
        $tmpThis = tempnam(getTmpDir() , 'uptAddValue');
        file_put_contents($tmpThis, serialize($this));
        $tmpArgs = tempnam(getTmpDir() , 'argAddValue');
        file_put_contents($tmpArgs, serialize(func_get_args()));
        $wsh = getWshCmd(true, getCurrentUser()->id);
        $tmpStatus = tempnam(getTmpDir() , 'statusAddValue');
        
        $cmd[] = sprintf("$wsh  --api=updateAttribute --objectFile=%s --argsFile=%s --statusFile=%s --method=addValue", escapeshellarg($tmpThis) , escapeshellarg($tmpArgs) , escapeshellarg($tmpStatus));
        
        bgexec($cmd, $result, $err);
        return $tmpStatus;
    }
    /**
     * call ::removeValue() in a background process
     * the return filename can be use by UpdateAttributeStatus to see information trace from background process
     * @see UpdateAttributeStatus
     * @param string $attrid attribute id
     * @param string|\string[] $valueToRemove
     * @return string filename status
     */
    public function bgRemoveValue($attrid, $valueToRemove)
    {
        $tmpThis = tempnam(getTmpDir() , 'uptRemoveValue');
        file_put_contents($tmpThis, serialize($this));
        $tmpArgs = tempnam(getTmpDir() , 'argRemoveValue');
        file_put_contents($tmpArgs, serialize(func_get_args()));
        $wsh = getWshCmd(true, getCurrentUser()->id);
        $tmpStatus = tempnam(getTmpDir() , 'statusRemoveValue');
        
        $cmd[] = sprintf("$wsh  --api=updateAttribute --objectFile=%s --argsFile=%s --statusFile=%s --method=removeValue", escapeshellarg($tmpThis) , escapeshellarg($tmpArgs) , escapeshellarg($tmpStatus));
        
        bgexec($cmd, $result, $err);
        return $tmpStatus;
    }
    /**
     * call ::replaceValue() in a background process
     * the return filename can be use by UpdateAttributeStatus to see information trace from background process
     * @see UpdateAttributeStatus
     * @param string $attrid attribute id
     * @param string $oldValue value to replace
     * @param string|string[] $newValue new value to set for attribute
     * @return string filename status
     */
    public function bgReplaceValue($attrid, $oldValue, $newValue)
    {
        $tmpThis = tempnam(getTmpDir() , 'uptReplValue');
        file_put_contents($tmpThis, serialize($this));
        $tmpArgs = tempnam(getTmpDir() , 'argReplValue');
        file_put_contents($tmpArgs, serialize(func_get_args()));
        $wsh = getWshCmd(true, getCurrentUser()->id);
        $tmpStatus = tempnam(getTmpDir() , 'statusReplValue');
        
        $cmd[] = sprintf("$wsh  --api=updateAttribute --objectFile=%s --argsFile=%s --statusFile=%s --method=replaceValue", escapeshellarg($tmpThis) , escapeshellarg($tmpArgs) , escapeshellarg($tmpStatus));
        
        bgexec($cmd, $result, $err);
        return $tmpStatus;
    }
    /**
     * @return null|UpdateAttributeResults[]
     */
    public function getResults()
    {
        return $this->results;
    }
}

class UpdateAttributeResults
{
    /**
     * @var bool true if document has been modified
     */
    public $changed = null;
    public $revisionError = '';
    public $profilingError = '';
    /**
     * @var bool true if document has been profiled
     */
    public $profiled = false;
    /**
     * @var bool true if document has been revised
     */
    public $revised = false;
    /**
     * @var bool true if history has been updated
     */
    public $historyUpdated = false;
}

