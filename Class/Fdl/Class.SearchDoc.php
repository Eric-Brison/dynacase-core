<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Search Document
 *
 * @author Anakeen
 * @version $Id: Class.SearchDoc.php,v 1.8 2008/08/14 14:20:25 eric Exp $
 * @package FDL
 */
/**
 */

include_once ("FDL/Lib.Dir.php");
/**
 * document searches
 * @code
 * $s=new SearchDoc($db,"IUSER");
 $s->setObjectReturn(); // document object returns
 $s->addFilter('us_extmail is not null'); // simple filter
 $s->search(); // send search query
 $c=$s->count();
 print "count $c\n";
 $k=0;
 while ($doc=$s->nextDoc()) {
 // iterate document by document
 print "$k)".$doc->getTitle()."(".$doc->getRawValue("US_MAIL","nomail").")\n";clam
 $k+
 * @endcode
 * @class SearchDoc.
 */
class SearchDoc
{
    /**
     * family identifier filter
     * @public string
     */
    public $fromid;
    /**
     * folder identifier filter
     * @public int
     */
    public $dirid = 0;
    /**
     * recursive search for folders
     * @public boolean
     */
    public $recursiveSearch = false;
    /**
     * max recursive level
     * @public int
     */
    public $folderRecursiveLevel = 2;
    /**
     * number of results : set "ALL" if no limit
     * @public int
     */
    public $slice = "ALL";
    /**
     * index of results begins
     * @public int
     */
    public $start = 0;
    /**
     * sql filters
     * @public array
     */
    public $filters = array();
    /**
     * search in sub-families set false if restriction to top family
     * @public bool
     */
    public $only = false;
    /**
     *
     * @public bool
     */
    public $distinct = false;
    /**
     * order of result : like sql order
     * @public string
     */
    public $orderby = 'title';
    /**
     * order result by this attribute label/title
     * @public string
     */
    public $orderbyLabel = '';
    /**
     * to search in trash : [no|also|only]
     * @public string
     */
    public $trash = "";
    /**
     * restriction to latest revision
     * @public bool
     */
    public $latest = true;
    /**
     * user identifier : set to current user by default
     * @public int
     */
    public $userid = 0;
    /**
     * debug mode : to view query and delay
     * @public bool
     */
    private $debug = false;
    private $debuginfo = [];
    private $join = "";
    /**
     * sql filter not return confidential document if current user cannot see it
     * @var string
     */
    private $excludeFilter = "";
    /**
     *
     * Iterator document
     * @var Doc
     */
    private $iDoc = null;
    /**
     *
     * Iterator document
     * @var Doc[]
     */
    private $cacheDocuments = array();
    /**
     * result type [ITEM|TABLE]
     * @private string
     */
    private $mode = "TABLE";
    private $count = - 1;
    private $index = 0;
    /**
     * @var bool|array
     */
    private $result = false;
    private $searchmode;
    /**
     * @var string pertinence order in case of full searches
     */
    private $pertinenceOrder = '';
    /**
     * @var string words used by SearchHighlight class
     */
    private $highlightWords = '';
    private $resultPos = 0;
    /**
     * @var int query number (in ITEM mode)
     */
    
    private $resultQPos = 0;
    protected $originalDirId = 0;
    
    protected $returnsFields = array();
    /**
     * initialize with family
     *
     * @param string $dbaccess database coordinate
     * @param int|string $fromid family identifier to filter
     */
    public function __construct($dbaccess = '', $fromid = 0)
    {
        if ($dbaccess == "") $dbaccess = getDbAccess();
        $this->dbaccess = $dbaccess;
        $this->fromid = trim($fromid);
        $this->setOrder('title');
        $this->userid = getUserId();
    }
    /**
     * Normalize supported forms of fromid
     *
     * @param int|string $id the fromid to normalize
     * @return bool|int normalized integer or bool(false) on normalization failure
     */
    private function normalizeFromId($id)
    {
        $id = trim($id);
        // "0" or "" (empty srting) = search on all documents (cross-family)
        if ($id === "0" || $id === "") {
            return 0;
        }
        // "-1" = search on docfam
        if ($id === "-1") {
            return -1;
        }
        if (is_numeric($id)) {
            // 123 or -123 = search on family with id 123
            $sign = 1;
            if ($id < 0) {
                // -123 = search on family with id 123 without sub-families
                $sign = - 1;
                $id = abs($id);
            }
            $fam = new_Doc($this->dbaccess, $id);
            if ($fam->isAlive() && $fam->defDoctype === 'C') {
                return $sign * (int)$fam->id;
            }
        } else {
            // "ABC" or "-ABC" = search on family with name ABC
            $sign = 1;
            if (substr($id, 0, 1) == '-') {
                // "-ABC" = search on family with name 123 without sub-families
                $sign = - 1;
                $id = substr($id, 1);
            }
            $fam = new_Doc($this->dbaccess, $id);
            if ($fam->isAlive() && $fam->defDoctype === 'C') {
                return $sign * (int)$fam->id;
            }
        }
        return false;
    }
    /**
     * count results without return data
     * @api send query search and only count results
     *
     * @return int the number of results
     * @throws Dcp\SearchDoc\Exception
     * @throws Dcp\Db\Exception
     */
    public function onlyCount()
    {
        /**  @var Dir $fld */
        $fld = new_Doc($this->dbaccess, $this->dirid);
        $userid = $this->userid;
        if ($fld->fromid != getFamIdFromName($this->dbaccess, "SSEARCH")) {
            $this->recursiveSearchInit();
            $tqsql = $this->getQueries();
            $this->debuginfo["query"] = $tqsql[0];
            $count = 0;
            if (!is_array($tqsql)) {
                if (!isset($this->debuginfo["error"]) || $this->debuginfo["error"] == "") {
                    $this->debuginfo["error"] = _("cannot produce sql request");
                }
                return -1;
            }
            foreach ($tqsql as $sql) {
                if ($sql) {
                    if (preg_match('/from\s+(?:only\s+)?([a-z0-9_\-]*)/', $sql, $reg)) $maintable = $reg[1];
                    else $maintable = '';
                    $maintabledot = ($maintable) ? $maintable . '.' : '';
                    
                    $mainid = ($maintable) ? "$maintable.id" : "id";
                    $distinct = "";
                    if (preg_match('/^\s*select\s+distinct(\s+|\(.*?\))/iu', $sql, $m)) {
                        $distinct = "distinct ";
                    }
                    $sql = preg_replace('/^\s*select\s+(.*?)\s+from\s/iu', "select count($distinct$mainid) from ", $sql, 1);
                    if ($userid != 1) {
                        $sql.= sprintf(" and (%sviews && '%s')", $maintabledot, $this->getUserViewVector($userid));
                    }
                    $dbid = getDbid($this->dbaccess);
                    $mb = microtime(true);
                    try {
                        simpleQuery($this->dbaccess, $sql, $result, false, true, true);
                    }
                    catch(\Dcp\Db\Exception $e) {
                        $this->debuginfo["query"] = $sql;
                        $this->debuginfo["error"] = pg_last_error($dbid);
                        $this->count = - 1;
                        throw $e;
                    }
                    $count+= $result["count"];
                    $this->debuginfo["query"] = $sql;
                    $this->debuginfo["delay"] = sprintf("%.03fs", microtime(true) - $mb);
                }
            }
            $this->count = $count;
            return $count;
        } else {
            $this->count = count($fld->getContent());
        }
        
        return $this->count;
    }
    /**
     * return memberof to be used in profile filters
     * @static
     * @param $uid
     * @return string
     */
    public static function getUserViewVector($uid)
    {
        $memberOf = Account::getUserMemberOf($uid);
        if ($memberOf === null) {
            return '';
        }
        $memberOf[] = 0;
        $memberOf[] = $uid;
        return '{' . implode(',', $memberOf) . '}';
    }
    /**
     * return original sql query before test permissions
     *
     *
     * @return string
     */
    public function getOriginalQuery()
    {
        return _internalGetDocCollection(true, $this->dbaccess, $this->dirid, $this->start, $this->slice, $this->getFilters() , $this->userid, $this->searchmode, $this->fromid, $this->distinct, $this->orderby, $this->latest, $this->trash, $debuginfo, $this->folderRecursiveLevel, $this->join, $this);
    }
    /**
     * add join condition
     *
     * @api Add join condition
     * @code
     $s=new searchDoc();
     $s->trash='only';
     $s->join("id = dochisto(id)");
     $s->addFilter("dochisto.uid = %d",$this->getSystemUserId());
     // search all document which has been deleted by search DELETE code in history
     $s->addFilter("dochisto.code = 'DELETE'");
     $s->distinct=true;
     $result= $s->search();
     * @endcode
     * @param string $jointure
     * @throws Dcp\Exception
     */
    public function join($jointure)
    {
        if (empty($jointure)) {
            $this->join = '';
        } elseif (preg_match('/([a-z0-9_\-:]+)\s*(=|<|>|<=|>=)\s*([a-z0-9_\-:]+)\(([^\)]*)\)/', $jointure, $reg)) {
            $this->join = $jointure;
        } else {
            throw new \Dcp\SearchDoc\Exception("SD0001", $jointure);
        }
    }
    /**
     * count results
     * ::search must be call before
     * @see SearchDoc::search()
     * @api count results after query search is sended
     *
     * @return int
     *
     */
    public function count()
    {
        if ($this->isExecuted()) {
            if ($this->count == - 1) {
                if ($this->searchmode == "ITEM") {
                    $this->count = $this->countDocs();
                } else {
                    $this->count = count($this->result);
                }
            }
        }
        return $this->count;
    }
    /**
     * count returned document in sql select ressources
     * @return int
     */
    protected function countDocs()
    {
        $n = 0;
        foreach ($this->result as $res) $n+= pg_num_rows($res);
        reset($this->result);
        return $n;
    }
    /** 
     * reset results to use another search
     *
     *
     * @return void
     */
    public function reset()
    {
        $this->result = false;
        $this->resultPos = 0;
        $this->resultQPos = 0;
        $this->debuginfo = [];
        $this->count = - 1;
    }
    /**
     * reset result offset
     * use it to redo a document's iteration
     *
     */
    public function rewind()
    {
        
        $this->resultPos = 0;
        $this->resultQPos = 0;
    }
    /** 
     * Verify if query is already sended to database
     *
     * @return boolean
     */
    public function isExecuted()
    {
        return ($this->result !== false);
    }
    /**
     * Return sql filters used for request
     *
     * @return array of string
     */
    public function getFilters()
    {
        if (!$this->excludeFilter) {
            return $this->filters;
        } else {
            return array_merge(array(
                $this->excludeFilter
            ) , $this->filters);
        }
    }
    /**
     * send search
     * the query is sent to database
     * @api send query
     * @return array|null|SearchDoc array of documents if no setObjectReturn else itself
     * @throws Dcp\SearchDoc\Exception
     * @throws Dcp\Db\Exception
     */
    public function search()
    {
        if (count($this->filters) > 0 && $this->dirid > 0) {
            $dir = new_Doc($this->dbaccess, $this->dirid);
            if (is_object($dir) && $dir->isAlive() && is_a($dir, '\Dcp\Family\Ssearch')) {
                // Searching on a "Specialized search" collection and specifying additional filters is not supported
                throw new \Dcp\SearchDoc\Exception("SD0008");
            }
        }
        if ($this->getError()) {
            if ($this->mode == "ITEM") {
                return null;
            } else {
                return array();
            }
        }
        if ($this->fromid) {
            if (!is_numeric($this->fromid)) {
                $fromid = getFamIdFromName($this->dbaccess, $this->fromid);
            } else {
                if ($this->fromid != - 1) {
                    // test if it is a family
                    if ($this->fromid < - 1) {
                        $this->only = true;
                    }
                    simpleQuery($this->dbaccess, sprintf("select doctype from docfam where id=%d", abs($this->fromid)) , $doctype, true, true);
                    if ($doctype != 'C') $fromid = 0;
                    else $fromid = $this->fromid;
                } else $fromid = $this->fromid;
            }
            if ($fromid == 0) {
                $error = sprintf(_("%s is not a family") , $this->fromid);
                $this->debuginfo["error"] = $error;
                error_log("ERROR SearchDoc: " . $error);
                if ($this->mode == "ITEM") return null;
                else return array();
            }
            if ($this->only) $this->fromid = - (abs($fromid));
            else $this->fromid = $fromid;
        }
        $this->recursiveSearchInit();
        $this->index = 0;
        $this->searchmode = $this->mode;
        if ($this->mode == "ITEM") {
            if ($this->dirid) {
                // change search mode because ITEM mode not supported for Specailized searches
                $fld = new_Doc($this->dbaccess, $this->dirid);
                if ($fld->fromid == getFamIdFromName($this->dbaccess, "SSEARCH")) {
                    $this->searchmode = "TABLE";
                }
            }
        }
        $debuginfo = array();
        $this->count = - 1;
        $this->result = internalGetDocCollection($this->dbaccess, $this->dirid, $this->start, $this->slice, $this->getFilters() , $this->userid, $this->searchmode, $this->fromid, $this->distinct, $this->orderby, $this->latest, $this->trash, $debuginfo, $this->folderRecursiveLevel, $this->join, $this);
        if ($this->searchmode == "TABLE") $this->count = count($this->result); // memo cause array is unset by shift
        $this->debuginfo = $debuginfo;
        if (($this->searchmode == "TABLE") && ($this->mode == "ITEM")) $this->mode = "TABLEITEM";
        $this->resultPos = 0;
        $this->resultQPos = 0;
        if ($this->mode == "ITEM") return $this;
        
        return $this->result;
    }
    /**
     * return document iterator to be used in loop
     * @code
     *  $s=new \SearchDoc($dbaccess, $famName);
     $s->setObjectReturn();
     $s->search();
     $dl=$s->getDocumentList();
     foreach ($dl as $docId=>$doc) {
     print $doc->getTitle();
     }
     * @endcode
     * @api get document iterator
     * @return DocumentList
     */
    public function getDocumentList()
    {
        include_once ("FDL/Class.DocumentList.php");
        return new DocumentList($this);
    }
    /**
     * limit query to a subset of somes attributes
     * @param array $returns
     */
    public function returnsOnly(array $returns)
    {
        if ($this->fromid) {
            $fdoc = createTmpDoc($this->dbaccess, $this->fromid, false);
            $fields = array_merge($fdoc->fields, $fdoc->sup_fields);
        } else {
            $fdoc = new Doc();
            $fields = array_merge($fdoc->fields, $fdoc->sup_fields);
        }
        foreach ($returns as $k => $r) {
            if (empty($r)) unset($returns[$k]);
            $returns[$k] = strtolower($r);
            // delete unknow fields
            if (!in_array($r, $fields)) {
                unset($returns[$k]);
            }
        }
        $this->returnsFields = array_unique(array_merge(array(
            "id",
            "title",
            "fromid",
            "doctype"
        ) , $returns));
    }
    public function getReturnsFields()
    {
        if ($this->returnsFields) return $this->returnsFields;
        if ($this->fromid) {
            $fdoc = createTmpDoc($this->dbaccess, $this->fromid, false);
            if ($fdoc->isAlive()) return array_merge($fdoc->fields, $fdoc->sup_fields);
        }
        return null;
    }
    /**
     * return error message
     * @return string empty if no errors
     */
    public function searchError()
    {
        return $this->getError();
    }
    /**
     * Return error message
     * @api get error message
     * @return string
     */
    public function getError()
    {
        if ($this->debuginfo && isset($this->debuginfo["error"])) return $this->debuginfo["error"];
        return "";
    }
    /**
     * do the search in debug mode, you can after the search get infrrmation with getDebugIndo()
     * @param boolean $debug set to true search in debug mode
     * @deprecated no debug mode setting are necessary
     * @return void
     */
    public function setDebugMode($debug = true)
    {
        deprecatedFunction();
        $this->debug = $debug;
    }
    /**
     * set recursive mode for folder searches
     * can be use only if collection set if a static folder
     * @param bool $recursiveMode set to true to use search in sub folders when collection is folder
     * @param int $level Indicate depth to inspect subfolders
     * @throws Dcp\SearchDoc\Exception
     * @api set recursive mode for folder searches
     * @see SearchDoc::useCollection
     * @return void
     */
    public function setRecursiveSearch($recursiveMode = true, $level = 2)
    {
        $this->recursiveSearch = $recursiveMode;
        if (!is_int($level) || $level < 0) {
            throw new \Dcp\SearchDoc\Exception("SD0006", $level);
        }
        $this->folderRecursiveLevel = $level;
    }
    /**
     * return debug info if debug mode enabled
     * @deprecated use getSearchInfo instead
     *
     * @return array of info
     */
    public function getDebugInfo()
    {
        deprecatedFunction();
        return $this->debuginfo;
    }
    /**
     * return informations about query after search has been sent
     * array indexes are : query, err, count, delay
     * @api get informations about query results
     * @return array of info
     */
    public function getSearchInfo()
    {
        return $this->debuginfo;
    }
    /**
     * set maximum number of document to return
     * @api set maximum number of document to return
     * @param int $slice the limit ('ALL' means no limit)
     *
     * @return Boolean
     */
    public function setSlice($slice)
    {
        if ((!is_numeric($slice)) && ($slice != 'ALL')) return false;
        $this->slice = $slice;
        return true;
    }
    /**
     * use different order , default is title
     * @api set order to sort results
     * @param string $order the new order, empty means no order
     * @param string $orderbyLabel string of comma separated columns names on which the order should be performed on their label instead of their value (e.g. order enum by their label instead of their key)
     * @return void
     */
    public function setOrder($order, $orderbyLabel = '')
    {
        $this->orderby = $order;
        $this->orderbyLabel = $orderbyLabel;
        /* Rewrite "-<column_name>" to "<column_name> desc" */
        $this->orderby = preg_replace('/(^\s*|,\s*)-([A-Z_0-9]{1,63})\b/i', '$1$2 desc', $this->orderby);
    }
    /**
     * use folder or search document to search within it
     * @api use folder or search document
     * @param int $dirid identifier of the collection
     *
     * @return Boolean true if set
     */
    public function useCollection($dirid)
    {
        $dir = new_doc($this->dbaccess, $dirid);
        if ($dir->isAlive()) {
            $this->dirid = $dir->initid;
            $this->originalDirId = $this->dirid;
            return true;
        }
        $this->debuginfo["error"] = sprintf(_("collection %s not exists") , $dirid);
        
        return false;
    }
    /**
     * set offset where start the result window
     * @api set offset where start the result window
     * @param int $start the offset (0 is the begin)
     *
     * @return Boolean true if set
     */
    public function setStart($start)
    {
        if (!(is_numeric($start))) return false;
        $this->start = intval($start);
        return true;
    }
    /**
     * can, be use in loop
     * ::search must be call before
     *
     * @see Application::getNextDoc
     *
     * @deprecated use { @link Application::getNextDoc } instead
     *
     * @see SearchDoc::search
     *
     * @return Doc|array or null if this is the end
     */
    public function nextDoc()
    {
        deprecatedFunction();
        return $this->getNextDoc();
    }
    /**
     * can, be use in loop
     * ::search must be call before
     *
     * @see SearchDoc::search
     *
     * @api get next document results
     *
     * @return Doc|array|bool  false if this is the end
     */
    public function getNextDoc()
    {
        if ($this->mode == "ITEM") {
            $n = empty($this->result[$this->resultQPos]) ? null : $this->result[$this->resultQPos];
            if (!$n) return false;
            $tdoc = @pg_fetch_array($n, $this->resultPos, PGSQL_ASSOC);
            if ($tdoc === false) {
                $this->resultQPos++;
                $n = empty($this->result[$this->resultQPos]) ? null : $this->result[$this->resultQPos];
                if (!$n) return false;
                $this->resultPos = 0;
                $tdoc = @pg_fetch_array($n, $this->resultPos, PGSQL_ASSOC);
                if ($tdoc === false) return false;
            }
            $this->resultPos++;
            return $this->iDoc = $this->getNextDocument($tdoc);
        } elseif ($this->mode == "TABLEITEM") {
            $tdoc = current(array_slice($this->result, $this->resultPos, 1));
            if (!is_array($tdoc)) return false;
            $this->resultPos++;
            return $this->iDoc = $this->getNextDocument($tdoc);
        } else {
            return current(array_slice($this->result, $this->resultPos++, 1));
        }
    }
    /**
     * after search return only document identifiers instead of complete document
     * @api get only document identifiers
     * @return int[] document identifiers
     */
    public function getIds()
    {
        $ids = array();
        if ($this->mode == "ITEM") {
            foreach ($this->result as $n) {
                $c = pg_num_rows($n);
                for ($i = 0; $i < $c; $i++) {
                    $ids[] = pg_fetch_result($n, $i, "id");
                }
            }
        } else {
            
            foreach ($this->result as $raw) {
                $ids[] = $raw["id"];
            }
        }
        return $ids;
    }
    /**
     * Return an object document from array of values
     *
     * @param array $v the values of documents
     * @return Doc the document object
     */
    protected function getNextDocument(Array $v)
    {
        $fromid = $v["fromid"];
        if ($v["doctype"] == "C") {
            if (!isset($this->cacheDocuments["family"])) $this->cacheDocuments["family"] = new DocFam($this->dbaccess);
            $this->cacheDocuments["family"]->Affect($v, true);
            $fromid = "family";
        } else {
            if (!isset($this->cacheDocuments[$fromid])) {
                $this->cacheDocuments[$fromid] = createDoc($this->dbaccess, $fromid, false, false);
                if (empty($this->cacheDocuments[$fromid])) {
                    throw new Exception(sprintf('Document "%s" has an unknow family "%s"', $v["id"], $fromid));
                }
            }
        }
        
        $this->cacheDocuments[$fromid]->Affect($v, true);
        $this->cacheDocuments[$fromid]->nocache = true;
        if ((!empty($this->returnsFields))) $this->cacheDocuments[$fromid]->doctype = "I"; // incomplete document
        return $this->cacheDocuments[$fromid];
    }
    /**
     * add a condition in filters
     * @api add a new condition in filters
     * @param string $filter the filter string
     * @param string $args arguments of the filter string (arguments are escaped to avoid sql injection)
     * @return void
     */
    public function addFilter($filter, $args = '')
    {
        
        if ($filter != "") {
            $args = func_get_args();
            if (count($args) > 1) {
                $fs[0] = $args[0];
                for ($i = 1; $i < count($args); $i++) {
                    $fs[] = pg_escape_string($args[$i]);
                }
                $filter = call_user_func_array("sprintf", $fs);
            }
            if (preg_match('/(\s|^|\()(?P<relname>[a-z0-9_\-]+)\./', $filter, $reg)) {
                // when use join filter like "zoo_espece.es_classe='Boo'"
                $famid = getFamIdFromName($this->dbaccess, $reg['relname']);
                if ($famid > 0) $filter = preg_replace('/(\s|^|\()(?P<relname>[a-z0-9_\-]+)\./', '${1}doc' . $famid . '.', $filter);
            }
            $this->filters[] = $filter;
        }
    }
    /**
     * add global filter based on keyword to match any attribute value
     * available example :
     *   foo : filter all values with has the word foo
     *   foo bar : the word foo and the word bar are set in document attributes
     *   foo OR bar : the word foo or the word bar are set in a document attributes
     *   foo OR (bar AND zou) : more complex logical expression
     * @api add global filter based on keyword
     * @param string $keywords
     * @param bool $useSpell use spell french checker
     * @param bool $usePartial if true each words are defined as partial characters
     * @throws \Dcp\SearchDoc\Exception SD0004 SD0003 SD0002
     */
    public function addGeneralFilter($keywords, $useSpell = false, $usePartial = false)
    {
        if (!$this->checkGeneralFilter($keywords)) {
            throw new \Dcp\SearchDoc\Exception("SD0004", $keywords);
        } else {
            $filter = $this->getGeneralFilter(trim($keywords) , $useSpell, $this->pertinenceOrder, $this->highlightWords, $usePartial);
            $this->addFilter($filter);
        }
    }
    /**
     * Verify if $keywords syntax is comptatible with a part of query
     * for the moment verify only parenthesis balancing
     * @param string $keyword
     * @return bool
     */
    public static function checkGeneralFilter($keyword)
    {
        // no symbol allowed
        if (preg_match('/\(\s*\)/u', $keyword)) return false;
        // test parenthensis count
        $keyword = str_replace('\(', '-', $keyword);
        $keyword = str_replace('\)', '-', $keyword);
        if (substr_count($keyword, '(') != substr_count($keyword, ')')) return false;
        $si = strlen($keyword); // be carrefyl no use mb_strlen here : it is wanted
        $pb = 0;
        for ($i = 0; $i < $si; $i++) {
            if ($keyword[$i] == '(') $pb++;
            if ($keyword[$i] == ')') $pb--;
            if ($pb < 0) return false;
        }
        return true;
    }
    /**
     * add a order based on keyword
     * consider how often the keyword terms appear in the document
     * @api add a order based on keyword
     * @param string $keyword
     */
    public function setPertinenceOrder($keyword = '')
    {
        if ($keyword != '') {
            $rank = preg_replace('/\s+(OR)\s+/u', '|', $keyword);
            $rank = preg_replace('/\s+(AND)\s+/u', '&', $rank);
            $rank = preg_replace('/\s+/u', '&', $rank);
            $this->pertinenceOrder = sprintf("ts_rank(fulltext,to_tsquery('french', E'%s')) desc, id desc", pg_escape_string(unaccent($rank)));
        }
        if ($this->pertinenceOrder) $this->setOrder($this->pertinenceOrder);
    }
    /**
     * get global filter
     * @see SearchDoc::addGeneralFilter
     * @static
     * @param string $keywords
     * @param bool $useSpell
     * @param string $pertinenceOrder return pertinence order
     * @param string $highlightWords return words to be use by SearchHighlight class
     * @param bool $usePartial if true each words are defined as partial characters
     * @return string
     * @throws \Dcp\Lex\LexException
     * @throws \Dcp\SearchDoc\Exception
     */
    public static function getGeneralFilter($keywords, $useSpell = false, &$pertinenceOrder = '', &$highlightWords = '', $usePartial = false)
    {
        $filter = "";
        $rank = "";
        $words = array();
        $currentOperator = "and";
        $parenthesisBalanced = 0;
        $filterElement = "";
        $parenthesis = "";
        $rankElement = "";
        $stringWords = array();
        
        $convertOperatorToTs = function ($operator)
        {
            if ($operator === "") {
                return "";
            }
            if ($operator === "and") {
                return "&";
            } else if ($operator === "or") {
                return "|";
            } else {
                throw new \Dcp\SearchDoc\Exception("SD0002", $operator);
            }
        };
        
        $filterElements = \Dcp\Lex\GeneralFilter::analyze($keywords);
        if ($usePartial) {
            $isOnlyWord = false;
        } else {
            $isOnlyWord = true;
            foreach ($filterElements as $currentFilter) {
                if ($usePartial && $currentFilter["mode"] === \Dcp\Lex\GeneralFilter::MODE_WORD) {
                    $isOnlyWord = false;
                    $currentFilter["mode"] = \Dcp\Lex\GeneralFilter::MODE_PARTIAL_BOTH;
                }
                if (!in_array($currentFilter["mode"], array(
                    \Dcp\Lex\GeneralFilter::MODE_OR,
                    \Dcp\Lex\GeneralFilter::MODE_AND,
                    \Dcp\Lex\GeneralFilter::MODE_OPEN_PARENTHESIS,
                    \Dcp\Lex\GeneralFilter::MODE_CLOSE_PARENTHESIS,
                    \Dcp\Lex\GeneralFilter::MODE_WORD,
                ))) {
                    $isOnlyWord = false;
                    break;
                }
            }
        }
        foreach ($filterElements as $currentElement) {
            if ($usePartial && ($currentElement["mode"] === \Dcp\Lex\GeneralFilter::MODE_WORD || $currentElement["mode"] === \Dcp\Lex\GeneralFilter::MODE_STRING)) {
                $currentElement["mode"] = \Dcp\Lex\GeneralFilter::MODE_PARTIAL_BOTH;
            }
            switch ($currentElement["mode"]) {
                case \Dcp\Lex\GeneralFilter::MODE_OR:
                    $currentOperator = "or";
                    break;

                case \Dcp\Lex\GeneralFilter::MODE_AND:
                    $currentOperator = "and";
                    break;

                case \Dcp\Lex\GeneralFilter::MODE_OPEN_PARENTHESIS:
                    $parenthesis = "(";
                    $parenthesisBalanced+= 1;
                    break;

                case \Dcp\Lex\GeneralFilter::MODE_CLOSE_PARENTHESIS:
                    $parenthesis = ")";
                    $parenthesisBalanced-= 1;
                    break;

                case \Dcp\Lex\GeneralFilter::MODE_WORD:
                    $filterElement = $currentElement["word"];
                    if ($useSpell) {
                        $filterElement = self::testSpell($currentElement["word"]);
                    }
                    $rankElement = unaccent($filterElement);
                    if (is_numeric($filterElement)) {
                        $filterElement = sprintf("(%s|-%s)", $filterElement, $filterElement);
                    }
                    
                    $words[] = $filterElement;
                    if ($isOnlyWord) {
                        $filterElement = pg_escape_string(unaccent($filterElement));
                    } else {
                        $to_tsquery = sprintf("to_tsquery('french', E'%s')", pg_escape_string(unaccent($filterElement)));
                        $dbObj = new DbObj('');
                        $point = sprintf('dcp:%s', uniqid(__METHOD__));
                        $dbObj->savePoint($point);
                        try {
                            simpleQuery('', sprintf("select %s", $to_tsquery) , $indexedWord, true, true);
                            $dbObj->rollbackPoint($point);
                        }
                        catch(Dcp\Db\Exception $e) {
                            $dbObj->rollbackPoint($point);
                            throw new \Dcp\SearchDoc\Exception("SD0007", unaccent($filterElement));
                        }
                        if ($indexedWord) {
                            $filterElement = sprintf("(fulltext @@ E'%s')", pg_escape_string($indexedWord));
                        } else {
                            //ignore stop words
                            $filterElement = "";
                        }
                    }
                    break;

                case \Dcp\Lex\GeneralFilter::MODE_STRING:
                    $rankElement = unaccent($currentElement["word"]);
                    if (!preg_match('/\p{L}|\p{N}/u', mb_substr($rankElement, 0, 1))) {
                        $begin = '[£|\\\\s]';
                    } else {
                        $begin = '\\\\y';
                    }
                    if (!preg_match('/\p{L}|\p{N}/u', mb_substr($rankElement, -1))) {
                        $end = '[£|\\\\s]';
                    } else {
                        $end = '\\\\y';
                    }
                    /* Strip non-word chars to prevent errors with to_tsquery() */
                    $rankElement = trim(preg_replace('/[^\w]+/', ' ', $rankElement));
                    $stringWords[] = $rankElement;
                    
                    $filterElement = sprintf("svalues ~* E'%s%s%s'", $begin, pg_escape_string(preg_quote($currentElement["word"])) , $end);
                    break;

                case \Dcp\Lex\GeneralFilter::MODE_PARTIAL_END:
                    $rankElement = unaccent($currentElement["word"]);
                    
                    if (!preg_match('/\p{L}|\p{N}/u', mb_substr($rankElement, 0, 1))) {
                        $begin = '[£|\\\\s]';
                    } else {
                        $begin = '\\\\y';
                    }
                    $filterElement = sprintf("svalues ~* E'%s%s'", $begin, pg_escape_string(preg_quote($currentElement["word"])));
                    break;

                case \Dcp\Lex\GeneralFilter::MODE_PARTIAL_BEGIN:
                    $rankElement = unaccent($currentElement["word"]);
                    
                    if (!preg_match('/\p{L}|\p{N}/u', mb_substr($rankElement, -1))) {
                        $end = '[£|\\\\s]';
                    } else {
                        $end = '\\\\y';
                    }
                    $filterElement = sprintf("svalues ~* E'%s%s'", pg_escape_string(preg_quote($currentElement["word"])) , $end);
                    break;

                case \Dcp\Lex\GeneralFilter::MODE_PARTIAL_BOTH:
                    $rankElement = unaccent($currentElement["word"]);
                    if ($usePartial) {
                        $stringWords[] = $currentElement["word"];
                    }
                    $filterElement = sprintf("svalues ~* E'%s'", pg_escape_string(preg_quote($currentElement["word"])));
                    break;
            }
            if ($filterElement) {
                if ($isOnlyWord) {
                    $filter.= $filter ? $convertOperatorToTs($currentOperator) . $filterElement : $filterElement;
                } else {
                    $filter.= $filter ? " " . $currentOperator . " " . $filterElement : $filterElement;
                }
                $rank.= $rank ? $convertOperatorToTs($currentOperator) . $rankElement : $rankElement;
                $filterElement = "";
                $currentOperator = "and";
            } else if ($parenthesis) {
                if ($isOnlyWord) {
                    
                    $filter.= $filter && $parenthesis === "(" ? $convertOperatorToTs($currentOperator) . $parenthesis : $parenthesis;
                } else {
                    $filter.= $filter && $parenthesis === "(" ? " " . $currentOperator . " " . $parenthesis : $parenthesis;
                }
                $rank.= $rank && $parenthesis === "(" ? $convertOperatorToTs($currentOperator) . $parenthesis : $parenthesis;
                $currentOperator = $parenthesis === "(" ? "" : "and";
                $parenthesis = "";
            }
        }
        if ($parenthesisBalanced !== 0) {
            throw new \Dcp\SearchDoc\Exception("SD0003", $keywords);
        }
        if ($isOnlyWord) {
            $filter = str_replace(')(', ')&(', $filter);
            $filter = sprintf("fulltext @@ to_tsquery('french', E'%s')", pg_escape_string($filter));
        }
        
        $pertinenceOrder = sprintf("ts_rank(fulltext,to_tsquery('french', E'%s')) desc, id desc", pg_escape_string(preg_replace('/\s+/u', '&', $rank)));
        
        $highlightWords = implode("|", array_merge($words, $stringWords));
        
        return $filter;
    }
    /**
     * return a document part where general filter term is found
     *
     * @see SearchDoc::addGeneralFilter
     * @param Doc $doc document to analyze
     * @param string $beginTag delimiter begin tag
     * @param string $endTag delimiter end tag
     * @param int $limit file size limit to analyze
     * @return mixed
     */
    public function getHighLightText(Doc & $doc, $beginTag = '<b>', $endTag = '</b>', $limit = 200, $wordMode = true)
    {
        static $oh = null;
        if (!$oh) {
            $oh = new SearchHighlight();
        }
        if ($beginTag) $oh->beginTag = $beginTag;
        if ($endTag) $oh->endTag = $endTag;
        if ($limit > 0) $oh->setLimit($limit);
        simpleQuery($this->dbaccess, sprintf("select svalues from docread where id=%d", $doc->id) , $text, true, true);
        
        if ($wordMode) {
            $h = $oh->highlight($text, $this->highlightWords);
        } else {
            $h = $oh->rawHighlight($text, $this->highlightWords);
        }
        
        return $h;
    }
    /**
     * detect if word is a word of language
     * if not the near word is set to do an OR condition
     * @static
     * @param string $word word to analyze
     * @param string $language
     * @return string word with its correction if it is not correct
     */
    protected static function testSpell($word, $language = "fr")
    {
        static $pspell_link = null;
        if (function_exists('pspell_new')) {
            if (!$pspell_link) $pspell_link = pspell_new($language, "", "", "utf-8", PSPELL_FAST);
            if ((!is_numeric($word)) && (!pspell_check($pspell_link, $word))) {
                $suggestions = pspell_suggest($pspell_link, $word);
                $sug = false;
                if (isset($suggestions[0])) {
                    $sug = unaccent($suggestions[0]);
                }
                if ($sug && ($sug != unaccent($word)) && (!strstr($sug, ' '))) {
                    $word = sprintf("(%s|%s)", $word, $sug);
                }
            }
        }
        return $word;
    }
    /**
     * return where condition like : foo in ('x','y','z')
     *
     * @static
     * @param array $values set of values
     * @param string $column database column name
     * @param bool $integer set to true if database column is numeric type
     * @return string
     */
    public static function sqlcond(array $values, $column, $integer = false)
    {
        $sql_cond = "true";
        if (count($values) > 0) {
            if ($integer) { // for integer type
                $sql_cond = "$column in (";
                $sql_cond.= implode(",", $values);
                $sql_cond.= ")";
            } else { // for text type
                foreach ($values as & $v) $v = pg_escape_string($v);
                $sql_cond = "$column in ('";
                $sql_cond.= implode("','", $values);
                $sql_cond.= "')";
            }
        }
        
        return $sql_cond;
    }
    /**
     * no use access view control in filters
     *  @see SearchDoc::overrideViewControl
     *
     * @deprecated use { @link SearchDoc::overrideViewControl } instead
     * @return void
     */
    public function noViewControl()
    {
        deprecatedFunction();
        $this->overrideViewControl();
    }
    /**
     * no use access view control in filters
     * @api no add view access criteria in final query
     * @return void
     */
    public function overrideViewControl()
    {
        $this->userid = 1;
    }
    /**
     * the return of ::search will be array of document's object
     *
     * @api set return type : document object or document array
     * @param bool $returnobject set to true to return object, false to return raw data
     * @return void
     */
    public function setObjectReturn($returnobject = true)
    {
        if ($returnobject) $this->mode = "ITEM";
        else $this->mode = "TABLE";
    }
    
    public function isObjectReturn()
    {
        return ($this->mode == "ITEM");
    }
    /**
     * the return of ::search will be array of values
     * @deprecated use setObjectReturn(false) instead
     * @return void
     */
    public function setValueReturn()
    {
        deprecatedFunction();
        $this->mode = "TABLE";
    }
    /**
     * add a filter to not return confidential document if current user cannot see it
     * @api add a filter to not return confidential
     * @param boolean $exclude set to true to exclude confidential
     * @return void
     */
    public function excludeConfidential($exclude = true)
    {
        if ($exclude) {
            if ($this->userid != 1) {
                $this->excludeFilter = sprintf("confidential is null or hasaprivilege('%s', profid,%d)", DocPerm::getMemberOfVector($this->userid) , 1 << POS_CONF);
            }
        } else {
            $this->excludeFilter = '';
        }
    }
    
    protected function recursiveSearchInit()
    {
        if ($this->recursiveSearch && $this->dirid) {
            if (!$this->originalDirId) {
                $this->originalDirId = $this->dirid;
            }
            /**
             * @var DocSearch $tmps
             */
            $tmps = createTmpDoc($this->dbaccess, "SEARCH");
            $tmps->setValue(\Dcp\AttributeIdentifiers\Search::se_famid, $this->fromid);
            $tmps->setValue(\Dcp\AttributeIdentifiers\Search::se_idfld, $this->originalDirId);
            $tmps->setValue(\Dcp\AttributeIdentifiers\Search::se_latest, "yes");
            $err = $tmps->add();
            if ($err == "") {
                $tmps->addQuery($tmps->getQuery()); // compute internal sql query
                $this->dirid = $tmps->id;
            } else {
                throw new \Dcp\SearchDoc\Exception("SD0005", $err);
            }
        }
    }
    /**
     * Get the SQL queries that will be executed by the search() method
     * @return array|bool boolean false on error, or array() of queries on success.
     */
    public function getQueries()
    {
        $dbaccess = $this->dbaccess;
        $dirid = $this->dirid;
        $fromid = $this->fromid;
        $sqlfilters = $this->getFilters();
        $distinct = $this->distinct;
        $latest = $this->latest;
        $trash = $this->trash;
        $folderRecursiveLevel = $this->folderRecursiveLevel;
        $join = $this->join;
        
        $normFromId = $this->normalizeFromId($fromid);
        if ($normFromId === false) {
            $this->debuginfo["error"] = sprintf(_("%s is not a family") , $fromid);
            return false;
        }
        $fromid = $normFromId;
        if (($fromid != "") && (!is_numeric($fromid))) {
            preg_match('/^(?P<sign>-?)(?P<fromid>.+)$/', trim($fromid) , $m);
            $fromid = $m['sign'] . getFamIdFromName($dbaccess, $m['fromid']);
        }
        if ($this->only && strpos($fromid, '-') !== 0) {
            $fromid = '-' . $fromid;
        }
        $table = "doc";
        $only = "";
        
        if ($fromid == - 1) {
            $table = "docfam";
        } elseif ($fromid < 0) {
            $only = "only";
            $fromid = - $fromid;
            $table = "doc$fromid";
        } else {
            if ($fromid != 0) {
                if (isSimpleFilter($sqlfilters) && (familyNeedDocread($dbaccess, $fromid))) {
                    $table = "docread";
                    $fdoc = new_doc($dbaccess, $fromid);
                    $sqlfilters[-4] = GetSqlCond(array_merge(array(
                        $fromid
                    ) , array_keys($fdoc->GetChildFam())) , "fromid", true);
                } else {
                    $table = "doc$fromid";
                }
            } elseif ($fromid == 0) {
                if (isSimpleFilter($sqlfilters)) $table = "docread";
            }
        }
        $maintable = $table; // can use join only on search
        if ($join) {
            if (preg_match('/(?P<attr>[a-z0-9_\-:]+)\s*(?P<operator>=|<|>|<=|>=)\s*(?P<family>[a-z0-9_\-:]+)\((?P<family_attr>[^\)]*)\)/', $join, $reg)) {
                $joinid = getFamIdFromName($dbaccess, $reg['family']);
                $jointable = ($joinid) ? "doc" . $joinid : $reg['family'];
                
                $sqlfilters[] = sprintf("%s.%s %s %s.%s", $table, $reg['attr'], $reg['operator'], $jointable, $reg['family_attr']); // "id = dochisto(id)";
                $maintable = $table;
                $table.= ", " . $jointable;
            } else {
                addWarningMsg(sprintf(_("search join syntax error : %s") , $join));
                return false;
            }
        }
        $maintabledot = ($maintable && $dirid == 0) ? $maintable . '.' : '';
        
        if ($distinct) {
            $selectfields = "distinct on ($maintable.initid) $maintable.*";
        } else {
            $selectfields = "$maintable.*";
            $sqlfilters[-2] = $maintabledot . "doctype != 'T'";
            ksort($sqlfilters);
        }
        $sqlcond = "true";
        ksort($sqlfilters);
        if (count($sqlfilters) > 0) $sqlcond = " (" . implode(") and (", $sqlfilters) . ")";
        
        $qsql = '';
        if ($dirid == 0) {
            //-------------------------------------------
            // search in all Db
            //-------------------------------------------
            if (strpos(implode(",", $sqlfilters) , "archiveid") === false) $sqlfilters[-4] = $maintabledot . "archiveid is null";
            
            if ($trash === "only") {
                $sqlfilters[-3] = $maintabledot . "doctype = 'Z'";
            } elseif ($trash !== "also") {
                $sqlfilters[-3] = $maintabledot . "doctype != 'Z'";
            }
            
            if (($latest) && (($trash == "no") || (!$trash))) {
                $sqlfilters[-1] = $maintabledot . "locked != -1";
            }
            ksort($sqlfilters);
            if (count($sqlfilters) > 0) {
                $sqlcond = " (" . implode(") and (", $sqlfilters) . ")";
            }
            $qsql = "select $selectfields " . "from $only $table  " . "where  " . $sqlcond;
            $qsql = $this->injectFromClauseForOrderByLabel($fromid, $this->orderbyLabel, $qsql);
        } else {
            //-------------------------------------------
            // in a specific folder
            //-------------------------------------------
            $fld = new_Doc($dbaccess, $dirid);
            if ($fld->defDoctype != 'S') {
                /**
                 * @var Dir $fld
                 */
                $hasFilters = false;
                if ($fld && method_exists($fld, "getSpecificFilters")) {
                    $specFilters = $fld->getSpecificFilters();
                    if (is_array($specFilters) && (count($specFilters) > 0)) {
                        $sqlfilters = array_merge($sqlfilters, $specFilters);
                        $hasFilters = true;
                    }
                }
                if (strpos(implode(",", $sqlfilters) , "archiveid") === false) $sqlfilters[-4] = $maintabledot . "archiveid is null";
                //if ($fld->getRawValue("se_trash")!="yes") $sqlfilters[-3] = "doctype != 'Z'";
                if ($trash == "only") $sqlfilters[-1] = "locked = -1";
                elseif ($latest) $sqlfilters[-1] = "locked != -1";
                ksort($sqlfilters);
                if (count($sqlfilters) > 0) $sqlcond = " (" . implode(") and (", $sqlfilters) . ")";
                
                $sqlfld = "dirid=$dirid and qtype='S'";
                if ($fromid == 2) $sqlfld.= " and doctype='D'";
                if ($fromid == 5) $sqlfld.= " and doctype='S'";
                if ($hasFilters) {
                    $sqlcond = " (" . implode(") and (", $sqlfilters) . ")";
                    $qsql = "select $selectfields from $only $table where $sqlcond ";
                } else {
                    $q = new QueryDb($dbaccess, "QueryDir");
                    $q->AddQuery($sqlfld);
                    $tfld = $q->Query(0, 0, "TABLE");
                    if ($q->nb > 0) {
                        $tfldid = array();
                        foreach ($tfld as $onefld) {
                            $tfldid[] = $onefld["childid"];
                        }
                        if (count($tfldid) > 1000) {
                            $qsql = "select $selectfields " . "from $table where initid in (select childid from fld where $sqlfld)  " . "and  $sqlcond ";
                        } else {
                            $sfldids = implode(",", $tfldid);
                            if ($table == "docread") {
                                /*$qsql= "select $selectfields ".
                                                 "from $table where initid in (select childid from fld where $sqlfld)  ".
                                                 "and  $sqlcond ";	*/
                                $qsql = "select $selectfields " . "from $table where initid in ($sfldids)  " . "and  $sqlcond ";
                            } else {
                                /*$qsql= "select $selectfields ".
                                    "from (select childid from fld where $sqlfld) as fld2 inner join $table on (initid=childid)  ".
                                    "where  $sqlcond ";*/
                                $qsql = "select $selectfields " . "from $only $table where initid in ($sfldids)  " . "and  $sqlcond ";
                            }
                        }
                    }
                }
            } else {
                //-------------------------------------------
                // search familly
                //-------------------------------------------
                $docsearch = new QueryDb($dbaccess, "QueryDir");
                $docsearch->AddQuery("dirid=$dirid");
                $docsearch->AddQuery("qtype = 'M'");
                $ldocsearch = $docsearch->Query(0, 0, "TABLE");
                // for the moment only one query search
                if (($docsearch->nb) > 0) {
                    switch ($ldocsearch[0]["qtype"]) {
                        case "M": // complex query
                            // $sqlM=$ldocsearch[0]["query"];
                            $fld = new_Doc($dbaccess, $dirid);
                            /**
                             * @var DocSearch $fld
                             */
                            if ($trash) {
                                $fld->setValue("se_trash", $trash);
                            } else {
                                $trash = $fld->getRawValue("se_trash");
                            }
                            $fld->folderRecursiveLevel = $folderRecursiveLevel;
                            $tsqlM = $fld->getQuery();
                            $qsql=[];
                            foreach ($tsqlM as $sqlM) {
                                if ($sqlM != false) {
                                    if (!preg_match("/doctype[ ]*=[ ]*'Z'/", $sqlM, $reg)) {
                                        if (($trash != "also") && ($trash != "only")) {
                                            $sqlfilters[-3] = "doctype != 'Z'"; // no zombie if no trash
                                            
                                        }
                                        ksort($sqlfilters);
                                        foreach ($sqlfilters as $kf => $sf) { // suppress doubles
                                            if (strstr($sqlM, $sf)) {
                                                unset($sqlfilters[$kf]);
                                            }
                                        }
                                        if (count($sqlfilters) > 0) {
                                            $sqlcond = " (" . implode(") and (", $sqlfilters) . ")";
                                        } else {
                                            $sqlcond = "";
                                        }
                                    }
                                    if ($fromid > 0) {
                                        $sqlM = str_replace("from doc ", "from $only $table ", $sqlM);
                                    }
                                    $fldFromId = ($fromid == 0) ? $fld->getRawValue('se_famid', 0) : $fromid;
                                    $sqlM = $this->injectFromClauseForOrderByLabel($fldFromId, $this->orderbyLabel, $sqlM);
                                    if ($sqlcond) {
                                        $qsql[] = $sqlM . " and " . $sqlcond;
                                    } else {
                                        $qsql[] = $sqlM;
                                    }
                                }
                            }
                            break;
                    }
                } else {
                    return false; // no query avalaible
                    
                }
            }
        }
        if (is_array($qsql)) return $qsql;
        return array(
            $qsql
        );
    }
    /**
     * Insert an additional relation in the FROM clause of the given query
     * to perform a sort on a label/title instead of a key/id.
     *
     * After rewriting the query, the new column name which will serve for
     * the ordering is stored into the private _orderbyLabelMaps struct
     * which will be used later when the "ORDER BY" directive will be
     * constructed.
     *
     * @param int $fromid The identifier of the family which the query is based on
     * @param string $column The name of the column on which the result is supposed to be be ordered
     * @param string $sqlM The SQL query in which an additional FROM relation should be injected
     * @return string The modified query
     */
    private function injectFromClauseForOrderByLabel($fromid, $column, $sqlM)
    {
        if ($column == '') {
            return $sqlM;
        }
        $attr = $this->_getAttributeFromColumn($fromid, $column);
        if ($attr === false || $attr->isMultiple()) {
            return $sqlM;
        }
        switch ($attr->type) {
            case 'enum':
                $enumKeyLabelList = $attr->getEnum();
                $mapValues = array(
                    "('', NULL)"
                );
                foreach ($enumKeyLabelList as $key => $label) {
                    $mapValues[] = sprintf("('%s', '%s')", pg_escape_string($key) , pg_escape_string($label));
                }
                $map = sprintf('(VALUES %s) AS map_%s(key, label)', join(', ', $mapValues) , $attr->id);
                $where = sprintf("map_%s.key = coalesce(doc%s.%s, '')", $attr->id, $fromid, $attr->id);
                
                $sqlM = preg_replace('/ where /i', ", $map where ($where) and ", $sqlM);
                $this->orderby = preg_replace(sprintf('/\b%s\b/', preg_quote($column, "/")) , sprintf("map_%s.label", $attr->id) , $this->orderby);
                break;

            case 'docid':
                /*
                 * No need to inject anything, just remap the docid attribute
                 * to the one holding the title.
                */
                $opt_doctitle = $attr->getOption('doctitle');
                if ($opt_doctitle != '') {
                    if ($opt_doctitle == 'auto') {
                        $opt_doctitle = sprintf('%s_title', $attr->id);
                    }
                    $this->orderby = preg_replace(sprintf('/\b%s\b/', preg_quote($column, "/")) , $opt_doctitle, $this->orderby);
                }
        }
        return $sqlM;
    }
    /**
     * Get the NormalAttribute object corresponding to the column of the given family
     *
     * @param $fromid
     * @param $column
     * @return NormalAttribute|bool
     */
    private function _getAttributeFromColumn($fromid, $column)
    {
        $fam = new_Doc($this->dbaccess, $fromid);
        if (!$fam->isAlive()) {
            return false;
        }
        $attrList = $fam->getNormalAttributes();
        foreach ($attrList as $attr) {
            if ($attr->id == $column) {
                return $attr;
            }
        }
        return false;
    }
}
