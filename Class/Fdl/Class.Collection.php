<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Document Object Definition
 *
 * @author Anakeen
 * @version $Id:  $
 * @package FDL
 */
/**
 */
include_once ("FDL/Class.Document.php");
include_once ("FDL/Lib.FullSearch.php");
/**
 * Document Class
 *
 */
class Fdl_Collection extends Fdl_Document
{
    /**
     * @var Doc|Dir
     */
    protected $doc;
    private $completeProperties = false;
    private $contentOnlyValue = true;
    private $contentOrderBy = '';
    private $contentSlice = 'ALL';
    private $contentStart = 0;
    private $contentKey = '';
    private $contentKeyMode = '';
    private $contentSearchProperty = '';
    private $contentFilter = '';
    private $contentVerifyHasChild = false;
    private $contentRecursiveLevel = 0;
    private $contentMap = null;
    private $onlyAttributes = null;
    
    private $debug = false;
    /**
     * Internal document list
     * @var DocumentList
     */
    private $documentList = null;
    /**
     * set true to add extra info about query
     * info are set in info field
     * @param bool $debug
     */
    public function setDebugMode($debug)
    {
        $this->debug = (!empty($debug));
    }
    
    public function setContentCompleteProperties($value)
    {
        $this->completeProperties = $value;
    }
    public function returnsOnlyAttributes(array $value)
    {
        $this->onlyAttributes = $value;
    }
    public function setContentOnlyValue($value)
    {
        $this->contentOnlyValue = $value;
    }
    public function setContentOrderBy($value)
    {
        $this->contentOrderBy = $value;
    }
    public function setContentSlice($value)
    {
        $this->contentSlice = $value;
    }
    public function setContentStart($value)
    {
        $this->contentStart = $value;
    }
    public function setContentKey($value)
    {
        $this->contentKey = $value;
    }
    public function setContentKeyMode($value)
    {
        $this->contentKeyMode = $value;
    }
    public function setContentSearchProperty($value)
    {
        $this->contentSearchProperty = $value;
    }
    public function setContentFilter($value)
    {
        $this->contentFilter = $value;
    }
    public function setContentVerifyHasChild($value)
    {
        $this->contentVerifyHasChild = $value;
    }
    public function setContentRecursiveLevel($value)
    {
        $this->contentRecursiveLevel = intval($value);
    }
    public function setContentMap($callback)
    {
        $this->contentMap = $callback;
    }
    /**
     * return documents list
     */
    public function getContent()
    {
        if ($this->documentList) return $this->getDocumentListContent();
        else return $this->getCollectionContent();
    }
    /**
     * return documents list
     * @param boolean $onlyvalues
     * @param boolean $completeprop
     * @param string $filter
     * @param int $start
     * @param int $slice
     * @param string $orderby
     * @param boolean $verifyhaschild
     * @return array of raw documents
     */
    public function getCollectionContent()
    {
        include_once ("FDL/Class.SearchDoc.php");
        $s = new SearchDoc($this->dbaccess);
        $s->useCollection($this->getProperty('initid'));
        if ($this->contentOrderBy) $s->orderby = $this->contentOrderBy;
        $s->setSlice($this->contentSlice);
        $s->setStart($this->contentStart);
        $s->excludeConfidential();
        $s->recursiveSearch = ($this->contentRecursiveLevel > 0);
        $s->folderRecursiveLevel = $this->contentRecursiveLevel;
        $err = '';
        $out = new stdClass();
        $content = array();
        if ($s->dirid > 0) {
            $s->setObjectReturn();
            $key = $this->contentKey;
            if ($key) {
                if ($this->contentKeyMode == "word") {
                    $sqlfilters = array();
                    $fullorderby = '';
                    $keyword = '';
                    DocSearch::getFullSqlFilters($key, $sqlfilters, $fullorderby, $keyword);
                    foreach ($sqlfilters as $vfilter) $s->addFilter($vfilter);
                    if (!$s->orderby) $s->orderby = $fullorderby;
                } else {
                    $s->addFilter("%s ~* '%s'", ($this->contentSearchProperty ? $this->contentSearchProperty : "svalues") , $key);
                }
            }
            if ($this->contentFilter) {
                if (is_string($this->contentFilter)) {
                    $lfilter = strtolower($this->contentFilter);
                    if ((!strstr($lfilter, '--')) && (!strstr($lfilter, ';')) && (!strstr($lfilter, 'insert')) && (!strstr($lfilter, 'alter')) && (!strstr($lfilter, 'delete')) && (!strstr($lfilter, 'update'))) {
                        // try to prevent sql injection
                        $s->addFilter($this->contentFilter);
                    }
                } elseif (is_object($this->contentFilter)) {
                    $ofamid = 0;
                    $sfilter = '';
                    $err = $this->doc->object2SqlFilter($this->contentFilter, $ofamid, $sfilter);
                    $this->setError($err);
                    if ($ofamid) $s->fromid = $ofamid;
                    $s->addFilter($sfilter);
                }
            }
            if ($this->onlyAttributes !== null) {
                $s->returnsOnly(array_merge(array_keys(Doc::$infofields) , $this->onlyAttributes));
            }
            if ($err == "") {
                $s->search();
                if ($this->debug) $out->info = $s->getSearchInfo();
                $err = $s->getError();
                if ($err) {
                    $this->setError($out->info["error"]);
                } else {
                    $dl = $s->getDocumentList();
                    $this->useDocumentList($dl);
                    return $this->getDocumentListContent();
                }
            }
        } else $this->error = sprintf(_("document not initialized"));
        $out->error = $this->error;
        
        foreach ($content as & $v) {
            $out->content[] = $v;
        }
        $out->slice = $s->slice;
        $out->start = $s->start;
        $out->date = date('Y-m-d H:i:s');
        
        return $out;
    }
    /**
     * use DocumentList instead a folder
     * @param DocumentList $dl
     * @return void
     */
    public function useDocumentList(DocumentList & $dl)
    {
        $this->documentList = $dl;
    }
    /**
     * return documents list
     * @param boolean $onlyvalues
     * @param boolean $completeprop
     * @return array of raw documents
     */
    private function getDocumentListContent()
    {
        
        $dl = $this->documentList;
        $content = null;
        $out = new stdClass();
        if (!$dl) {
            $this->setError("document list uninitialized");
        } else {
            $content = array();
            $s = $dl->getSearchDocument();
            if ($this->contentMap) $dl->listMap($this->contentMap);
            if ($this->debug) $out->info = $s->getSearchInfo();
            $out->slice = $s->slice;
            $out->start = $s->start;
            if (isset($out->info["error"])) $this->setError($out->info["error"]);
            $tmpdoc = new Fdl_Document();
            if ($this->onlyAttributes !== null) {
                $tmpdoc->usePartialDocument($this->onlyAttributes);
            }
            $kd = 0;
            $verifyhaschild = $this->contentVerifyHasChild;
            /**
             * @var Doc $doc
             */
            foreach ($dl as $doc) {
                $tmpdoc->affect($doc);
                if (!$doc->isConfidential()) {
                    if ($verifyhaschild) {
                        $tmpdoc->setVolatileProperty("haschildfolder", hasChildFld($this->dbaccess, $tmpdoc->getProperty('initid') , ($doc->doctype == 'S')));
                    }
                    $content[$kd] = $tmpdoc->getDocument($this->contentOnlyValue, $this->completeProperties);
                    $kd++;
                }
            }
            
            $out->totalCount = $s->count();
            if (($out->totalCount == $s->slice) || ($s->start > 0)) {
                $s->slice = 'ALL';
                $s->start = 0;
                $s->reset();
                $oc = $s->onlyCount();
                if ($this->debug) $out->info["totalCount"] = $s->getSearchInfo();
                if ($oc) $out->totalCount = $oc;
            }
        }
        $out->error = $this->error;
        $out->content = $content;
        $out->date = date('Y-m-d H:i:s');
        return $out;
    }
    /**
     * return document list from a keyword and optionaly family identifier
     * @param string $key
     * @param string $mode search method : regexp or word
     * @param int $famid filter on family
     * @param object $filter additionnal filter
     * @param int $start offset to start search (default is 0)
     * @param int $slice number of document returned
     * @param string $orderby order by property or attribute
     * @param boolean $onlyvalues set to true is want return attribute definition also
     * @param string $searchproperty property use where key is applied
     * @param boolean $whl with highlight return also text including keyword. the keyword is between HTML B tag.
     * @param boolean $verifyhaschild
     * @return array of raw document
     */
    public function simpleSearch($key, $mode = "word", $famid = 0, $filter = "", $start = 0, $slice = 100, $orderby = "", $onlyvalues = true, $searchproperty = "svalues", $whl = false, $verifyhaschild = false)
    {
        include_once ("FDL/Class.SearchDoc.php");
        static $sw = null;
        $out = new stdClass();
        $tfid = array();
        $err = '';
        $keyword = '';
        if (strstr($famid, '|')) {
            // multi family search
            $tfamids = explode('|', $famid);
            foreach ($tfamids as $fid) {
                if (!is_numeric($fid)) $fid = getFamidFromName($this->dbaccess, $fid);
                if ($fid > 0) $tfid[] = $fid;
            }
            
            $famid = 0;
        }
        if (preg_match('/([\w:]*)\s?strict/', trim($famid) , $reg)) {
            if (!is_numeric($reg[1])) $reg[1] = getFamIdFromName($this->dbaccess, $reg[1]);
            $famid = '-' . $reg[1];
        }
        $s = new SearchDoc($this->dbaccess, $famid);
        if ($key) {
            if ($mode == "word") {
                $sqlfilters = array();
                $fullorderby = '';
                $keyword = '';
                DocSearch::getFullSqlFilters($key, $sqlfilters, $fullorderby, $keyword);
                foreach ($sqlfilters as $vfilter) $s->addFilter($vfilter);
                if (!$orderby) $orderby = $fullorderby;
            } else {
                $s->addFilter(sprintf("%s ~* '%s'", $searchproperty, $key));
            }
        }
        if ($filter) {
            if (is_string($filter)) {
                $lfilter = strtolower($filter);
                if ((!strstr($lfilter, '--')) && (!strstr($lfilter, ';')) && (!stristr($lfilter, 'insert')) && (!stristr($lfilter, 'alter')) && (!stristr($lfilter, 'delete')) && (!stristr($lfilter, 'update'))) {
                    // try to prevent sql injection
                    $s->addFilter($filter);
                }
            } elseif (is_object($filter)) {
                /**
                 * @var \Dcp\Family\DSEARCH $sw
                 */
                if (!$sw) {
                    $sw = createTmpDoc($this->dbaccess, "DSEARCH");
                    $sw->setValue("se_famid", $famid);
                }
                $ofamid = 0;
                $sfilter = '';
                $err = $sw->object2SqlFilter($filter, $ofamid, $sfilter);
                $this->setError($err);
                if ($ofamid) {
                    $s->fromid = $ofamid;
                }
                $s->addFilter($sfilter);
            }
        }
        if ($err == "") {
            if (count($tfid) > 0) $s->addFilter(getSqlCond($tfid, 'fromid', true));
            $completeprop = false;
            $content = array();
            $s->slice = $slice;
            $s->start = $start;
            if ($orderby) $s->orderby = $orderby;
            $s->setObjectReturn();
            $s->excludeConfidential();
            $s->search();
            $info = $s->getSearchInfo();
            $out->error = $info["error"];
            if ($this->debug) $out->info = $info;
            
            if (!$out->error) {
                /**
                 * @var \Dcp\Family\DSEARCH $ws
                 */
                $ws = createTmpDoc($this->dbaccess, "DSEARCH");
                $ws->setValue("ba_title", sprintf(_("search %s") , $key));
                $ws->add();
                $ws->addStaticQuery($s->getOriginalQuery());
                $tmpdoc = new Fdl_Document($ws->id);
                $out->document = $tmpdoc->getDocument(true, false);
                $idx = 0;
                if (!$keyword) $keyword = str_replace(" ", "|", $key);
                while ($doc = $s->getNextDoc()) {
                    $tmpdoc->affect($doc);
                    if ($verifyhaschild) {
                        $tmpdoc->setVolatileProperty("haschildfolder", hasChildFld($this->dbaccess, $tmpdoc->getProperty('initid') , ($doc->doctype == 'S')));
                    }
                    $content[$idx] = $tmpdoc->getDocument($onlyvalues, $completeprop);
                    if ($whl) $content[$idx]['highlight'] = getHighlight($doc, $keyword);
                    $idx++;
                }
                
                $out->totalCount = $s->count();
                if (($out->totalCount == $slice) || ($start > 0)) {
                    $s->slice = 'ALL';
                    $s->start = 0;
                    $s->reset();
                    $out->totalCount = $s->onlyCount();
                    $info = $s->getSearchInfo();
                    if (!isset($out->delay)) {
                        $out->delay='';
                    }
                    $out->delay.= ' count:' . $info["delay"];
                }
                $out->content = $content;
            }
        } else {
            $out->error = $err;
        }
        $out->slice = $slice;
        $out->start = $start;
        $out->date = date('Y-m-d H:i:s');
        return $out;
    }
    /**
     * return child families
     * @param int $famid the family root
     * @return array of families where $famid is an ancestor
     */
    public function getSubFamilies($famid, $controlcreate = false)
    {
        $out = new stdClass();
        $fam = new_doc($this->dbaccess, $famid);
        if (!$fam->isAlive()) {
            $out->error = sprintf(_("data:family %s not alive") , $famid);
        } elseif ($fam->doctype != 'C') {
            $out->error = sprintf(_("data:document %s is not a family") , $famid);
        } else {
            
            $content = array();
            $fld = new Dir($this->dbaccess);
            if (!is_numeric($famid)) $famid = getFamIdFromName($this->dbaccess, $famid);
            $tfam = $fld->GetChildFam($famid, $controlcreate);
            if (count($tfam) > 0) {
                $tmpdoc = new Fdl_Document();
                $onlyvalues = true;
                $completeprop = false;
                foreach ($tfam as $id => $rawfam) {
                    $fam->affect($rawfam);
                    $tmpdoc->affect($fam);
                    if (!$tmpdoc->error) {
                        $content[] = $tmpdoc->getDocument($onlyvalues, $completeprop);
                    }
                }
            }
            $out->content = $content;
            $out->totalCount = count($content);
        }
        return $out;
    }
    /**
     * insert a document into folder
     * @param int $docid the document identifier to insert to
     * @return object with error or message field
     */
    public function insertDocument($docid)
    {
        $out = new stdClass();
        if ($this->docisset()) {
            $err = $this->doc->insertDocument($docid);
            if ($err != "") {
                $this->setError($err);
                $out->error = $err;
            } else {
                $out->message = sprintf(_("document %d inserted") , $docid);
            }
        } else $out->error = sprintf(_("document not set"));
        return $out;
    }
    /**
     * unlink a document from folder
     * @param int $docid the document identifier to unlink
     * @return object with error or message field
     */
    function unlinkDocument($docid)
    {
        $out = new stdClass();
        if ($this->docisset()) {
            $err = $this->doc->removeDocument($docid);
            if ($err != "") {
                $this->setError($err);
                $out->error = $err;
            } else {
                $out->message = sprintf(_("document %d deleted") , $docid);
            }
        } else $out->error = sprintf(_("document not set"));
        return $out;
    }
    /**
     * unlink several documents from folder
     * @param object $selection selection of document
     * @return object with error or message field
     */
    function unlinkDocuments($selection)
    {
        $out = new stdClass();
        include_once ("DATA/Class.DocumentSelection.php");
        $os = new Fdl_DocumentSelection($selection);
        $ids = $os->getIdentificators();
        
        if ($this->docisset()) {
            $out->notunlinked = array();
            $out->unlinked = array();
            $err = $this->doc->canModify();
            if ($err != "") {
                $out->error = $err;
            } else {
                foreach ($ids as $docid) {
                    $err = $this->doc->removeDocument($docid);
                    if ($err != "") {
                        $out->notunlinked[$docid] = $err;
                    } else {
                        $out->unlinked[$docid] = sprintf(_("document %d unlinked") , $docid) . "\n";
                    }
                }
                $out->unlinkedCount = count($out->unlinked);
                $out->notUnlinkedCount = count($out->notunlinked);
            }
        } else $out->error = sprintf(_("document not set"));
        return $out;
    }
    /**
     * unlink all documents from folder
     * @param object $selection selection of document
     * @return object with error or message field
     */
    function unlinkAllDocuments()
    {
        $out = new stdClass();
        if ($this->docisset()) {
            $out->error = "";
            $err = $this->doc->canModify();
            if ($err != "") {
                $out->error = $err;
            } else {
                $out->error = $this->doc->clear();
            }
        } else $out->error = sprintf(_("document not set"));
        return $out;
    }
    /**
     * move several documents from folder
     * @param object $selection selection of document
     * @return object with error or message field
     */
    function moveDocuments($selection, $targetId)
    {
        $out = new stdClass();
        include_once ("DATA/Class.DocumentSelection.php");
        $os = new Fdl_DocumentSelection($selection);
        $ids = $os->getIdentificators();
        
        if ($this->docisset()) {
            $out->notmoved = array();
            $out->moved = array();
            $err = $this->doc->canModify();
            if ($err == "") {
                /**
                 * @var Dir $targetDoc
                 */
                $targetDoc = new_doc($this->dbaccess, $targetId);
                if ($targetDoc->isAlive()) {
                    if ($targetDoc->defDoctype != 'D') $err = sprintf(_("target folder [%s] is not a folder") , $targetDoc->getTitle());
                    else {
                        $err = $targetDoc->canModify();
                    }
                } else {
                    $err = sprintf(_("target folder [%s] is not set") , $targetId);
                }
                if ($err != "") {
                    $out->error = $err;
                } else {
                    foreach ($ids as $docid) {
                        $err = $this->doc->moveDocument($docid, $targetDoc->initid);
                        if ($err != "") {
                            $out->notmoved[$docid] = $err;
                        } else {
                            $out->moved[$docid] = sprintf(_("document %d moved") , $docid) . "\n";
                        }
                    }
                    $out->movedCount = count($out->moved);
                    $out->notMovedCount = count($out->notmoved);
                }
            } else {
                $out->error = $err;
            }
        } else $out->error = sprintf(_("document not set"));
        return $out;
    }
    /**
     * insert several documents to folder
     * @param object $selection selection of document
     * @return object with error or message field
     */
    function insertDocuments($selection)
    {
        $out = new stdClass();
        include_once ("DATA/Class.DocumentSelection.php");
        $os = new Fdl_DocumentSelection($selection);
        if ($this->docisset()) {
            $tdocs = $os->getRawDocuments();
            $out->notinserted = array();
            $out->inserted = array();
            $err = $this->doc->insertMultipleDocuments($tdocs, "latest", false, $out->inserted, $out->notinserted);
            $out->insertedCount = count($out->inserted);
            $out->notInsertedCount = count($out->notinserted);
            $out->error = $err;
        } else $out->error = sprintf(_("document not set"));
        return $out;
    }
    
    function getAuthorizedFamilies()
    {
        if ($this->docisset()) {
            if (method_exists($this->doc, "getAuthorizedFamilies")) {
                
                return array(
                    "restriction" => $this->doc->hasNoRestriction() ? false : true,
                    "families" => $this->doc->getAuthorizedFamilies()
                );
            }
        }
        return null;
    }
}
?>