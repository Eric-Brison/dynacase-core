<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Search Document
 *
 * @author Anakeen 2008
 * @version $Id: Class.SearchDoc.php,v 1.8 2008/08/14 14:20:25 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */

include_once ("FDL/Lib.Dir.php");

class SearchDoc
{
    /**
     * family identificator filter
     * @public string
     */
    public $fromid;
    /**
     * folder identificator filter
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
    public $orderby = "title";
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
     * user identificator : set to current user by default
     * @public int
     */
    public $userid = 0;
    /**
     * debug mode : to view query and delay
     * @public bool
     */
    private $debug = false;
    private $debuginfo = "";
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
    private $result;
    private $searchmode;
    /**
     * initialize with family
     *
     * @param string $dbaccess database coordinate
     * @param int|string $fromid family identificator to filter
     */
    public function __construct($dbaccess = '', $fromid = 0)
    {
        if ($dbaccess == "") $dbaccess = getDbAccess();
        $this->dbaccess = $dbaccess;
        $this->fromid = trim($fromid);
        $this->orderby = 'title';
        $this->userid = getUserId();
    }
    /**
     * count results without return data
     *
     * @return int the number of results
     */
    public function onlyCount()
    {
        if (!$this->result) {
            /**
             * @var Dir $fld
             */
            $fld = new_Doc($this->dbaccess, $this->dirid);
            $userid = $this->userid;
            if ($fld->fromid != getFamIdFromName($this->dbaccess, "SSEARCH")) {
                $this->mode = "ITEM";
                if ($this->debug) $debuginfo = array();
                else $debuginfo = null;
                $tqsql = getSqlSearchDoc($this->dbaccess, $this->dirid, $this->fromid, $this->getFilters() , $this->distinct, $this->latest, $this->trash, false, $this->folderRecursiveLevel, $this->join);
                $this->debuginfo["query"] = $tqsql[0];
                $count = 0;
                if (!is_array($tqsql)) {
                    $this->debuginfo["err"] = _("cannot produce sql request");
                    return 0;
                }
                foreach ($tqsql as $sql) {
                    if ($sql) {
                        if (preg_match('/from\s+([a-z0-9_\-]*)/', $sql, $reg)) $maintable = $reg[1];
                        else $maintable = '';
                        $maintabledot = ($maintable) ? $maintable . '.' : '';
                        
                        $mainid = ($maintable) ? "$maintable.id" : "id";
                        $sql = preg_replace('/^\s*select\s+(.*?)\s+from\s/i', "select count($mainid) from ", $sql, 1);
                        if ($userid != 1) {
                            $sql.= sprintf(" and (%sviews && '%s')", $maintabledot, $this->getUserViewVector($userid));
                        }
                        $dbid = getDbid($this->dbaccess);
                        $mb = microtime(true);
                        $q = @pg_query($dbid, $sql);
                        if (!$q) {
                            $this->debuginfo["query"] = $sql;
                            $this->debuginfo["error"] = pg_last_error($dbid);
                        } else {
                            $result = pg_fetch_array($q, 0, PGSQL_ASSOC);
                            $count+= $result["count"];
                            $this->debuginfo["query"] = $sql;
                            $this->debuginfo["delay"] = sprintf("%.03fs", microtime(true) - $mb);
                        }
                    }
                }
                $this->count = $count;
                return $count;
            } else {
                $this->count = count($fld->getContent());
            }
        } else $this->count();
        return $this->count;
    }
    /**
     * @static
     * @param $uid
     * @return string
     */
    public static function getUserViewVector($uid)
    {
        $memberOf = User::getUserMemberOf($uid);
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
     * @return string
     */
    public function getOriginalQuery()
    {
        $tqsql = getSqlSearchDoc($this->dbaccess, $this->dirid, $this->fromid, $this->getFilters() , $this->distinct, $this->latest, $this->trash, false, $this->folderRecursiveLevel, $this->join);
        
        return $tqsql[0];
    }
    public function join($jointure)
    {
        $this->join = $jointure;
    }
    /**
     * count results
     * ::search must be call before
     *
     * @return int
     *
     */
    public function count()
    {
        if ($this->count == - 1) {
            if ($this->searchmode == "ITEM") {
                $this->count = countDocs($this->result);
            } else {
                $this->count = count($this->result);
            }
        }
        return $this->count;
    }
    /** 
     *reset results to use another search
     * @return void
     */
    public function reset()
    {
        $this->result = false;
        $this->debuginfo = "";
    }
    /** 
     * Verify if query is already sended to database
     * @return boolean
     */
    public function isExecuted()
    {
        return ($this->result != false);
    }
    /**
     * Return sql filters used for request
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
     *
     * @return array of documents
     *
     */
    public function search()
    {
        if ($this->getError()) return array();
        if ($this->fromid) {
            if (!is_numeric($this->fromid)) {
                $fromid = getFamIdFromName($this->dbaccess, $this->fromid);
            } else {
                if ($this->fromid != - 1) {
                    // test if it is a family
                    if ($this->fromid < - 1) {
                        $this->only = true;
                    }
                    $err = simpleQuery($this->dbaccess, sprintf("select doctype from docfam where id=%d", abs($this->fromid)) , $doctype, true, true);
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
        if ($this->recursiveSearch && $this->dirid) {
            /**
             * @var DocSearch $tmps
             */
            $tmps = createTmpDoc($this->dbaccess, "SEARCH");
            $tmps->setValue("se_idfld", $this->dirid);
            $tmps->setValue("se_latest", "yes");
            $err = $tmps->add();
            if ($err == "") {
                $tmps->addQuery($tmps->getQuery()); // compute internal sql query
                $this->dirid = $tmps->id;
            }
        }
        $this->index = 0;
        $this->searchmode = $this->mode;
        if ($this->mode == "ITEM") {
            // change search mode because ITEM mode not supported for Specailized searches
            $fld = new_Doc($this->dbaccess, $this->dirid);
            if ($fld->fromid == getFamIdFromName($this->dbaccess, "SSEARCH")) $this->searchmode = "TABLE";
        }
        $debuginfo = array();
        
        $this->result = getChildDoc($this->dbaccess, $this->dirid, $this->start, $this->slice, $this->getFilters() , $this->userid, $this->searchmode, $this->fromid, $this->distinct, $this->orderby, $this->latest, $this->trash, $debuginfo, $this->folderRecursiveLevel, $this->join);
        if ($this->searchmode == "TABLE") $this->count = count($this->result); // memo cause array is unset by shift
        $this->debuginfo = $debuginfo;
        if (($this->searchmode == "TABLE") && ($this->mode == "ITEM")) $this->mode = "TABLEITEM";
        
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
     * @return DocumentList
     */
    public function getDocumentList()
    {
        include_once ("FDL/Class.DocumentList.php");
        return new DocumentList($this);
    }
    /**
     * return error message
     * @return string empty if no errors
     */
    public function searchError()
    {
        return ($this->debuginfo["error"]);
    }
    /**
     * Return error message
     * @return string
     */
    public function getError()
    {
        if ($this->debuginfo) return $this->debuginfo["error"];
        return "";
    }
    /**
     * do the search in debug mode, you can after the search get infrrmation with getDebugIndo()
     * @param boolean $debug set to true search in debug mode
     * @deprecated
     * @return void
     */
    public function setDebugMode($debug = true)
    {
        deprecatedFunction();
        $this->debug = $debug;
    }
    /**
     * set recursive mode for folder searches
     *
     * @param bool $recursiveMode set to true to use search in sub folders when collection is folder
     * @return void
     */
    public function setRecursiveSearch($recursiveMode = true)
    {
        $this->recursiveSearch = $recursiveMode;
    }
    /**
     * return debug info if debug mode enabled
     * @deprecated
     *
     * @return array of info
     */
    public function getDebugInfo()
    {
        deprecatedFunction();
        return $this->debuginfo;
    }
    /**
     * return information about query after search is call
     *
     * @return array of info
     */
    public function getSearchInfo()
    {
        return $this->debuginfo;
    }
    /**
     * set maximum number of document to return
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
     * @param string $order the new order, empty means no order
     *
     * @return Boolean
     */
    public function setOrder($order)
    {
        $this->orderby = $order;
        return true;
    }
    /**
     * use folder or search document to apply restrict the search
     * @param int $dirid identificator of the collection
     *
     * @return Boolean true if set
     */
    public function useCollection($dirid)
    {
        $dir = new_doc($this->dbaccess, $dirid);
        if ($dir->isAlive()) {
            $this->dirid = $dir->initid;
            return true;
        }
        $this->debuginfo["error"] = sprintf(_("collection %s not exists") , $dirid);
        
        return false;
    }
    /**
     * set offset where start the result window
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
     * can, be use in
     * ::search must be call before
     *
     * @return Doc or null if this is the end
     */
    public function nextDoc()
    {
        if ($this->mode == "ITEM") {
            $n = current($this->result);
            if ($n === false) return false;
            $tdoc = pg_fetch_array($n, NULL, PGSQL_ASSOC);
            if ($tdoc === false) {
                $n = next($this->result);
                if ($n === false) return false;
                $tdoc = pg_fetch_array($n, NULL, PGSQL_ASSOC);
                if ($tdoc === false) return false;
            }
            return $this->iDoc = $this->getNextDocument($tdoc);
        } elseif ($this->mode == "TABLEITEM") {
            $tdoc = array_shift($this->result);
            if (!is_array($tdoc)) return false;
            return $this->iDoc = $this->getNextDocument($tdoc);
        } else return array_shift($this->result);
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
            }
        }
        $this->cacheDocuments[$fromid]->Affect($v, true);
        $this->cacheDocuments[$fromid]->nocache = true;
        return $this->cacheDocuments[$fromid];
    }
    /**
     * add a condition in filters
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
            if (preg_match('/^([a-z0-9_\-]+)\./', $filter, $reg)) {
                // when use join filter like "zoo_espece.es_classe='Boo'"
                $famid = getFamIdFromName($this->dbaccess, $reg[1]);
                if ($famid > 0) $filter = preg_replace('/^([a-z0-9_\-]+)\./', "doc" . $famid . '.', $filter);
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
     * @param string $keywords
     * @param bool $useSpell use spell french checker
     */
    public function addGeneralFilter($keywords, $useSpell = false)
    {
        if ((strstr($keywords, '"') == false) && (strstr($keywords, '~') == false)) {
            $this->addFullKeywords($keywords, $useSpell);
        } else {
            
            $this->addComplexKeyword($keywords, $useSpell);
        }
    }
    
    protected function addComplexKeyword($keywords, $useSpell = false)
    {
        $workFilter = preg_replace('/\s+(OR)\s+/', '|||', $keywords);
        $workFilter = preg_replace('/\s+(AND)\s+/', '&&&', $workFilter);
        $workFilter = preg_replace('/\s+/', '&space;', $workFilter);
        // exacts keys
        preg_match_all('/(?m)"([^"]+)"/u', $workFilter, $matches);
        $exactsKeys = $matches[0];
        // delete matches from keywords
        $workFilter = preg_replace('/(?m)"([^"]+)"/u', "", $workFilter);
        // regexp keys
        preg_match_all('/(?m)~([\p{L}]+)/u', $workFilter, $matches);
        $regexpKeys = $matches[0];
        // delete matches from keywords
        $workFilter = preg_replace('/(?m)~([\p{L}]+)/u', "", $workFilter);
        // full word keys
        preg_match_all('/(?m)([\p{L}]+)/u', $workFilter, $matches);
        $fullsKeys = $matches[0];
        // delete matches from keywords
        $workFilter = preg_replace('/(?m)"([\p{L}]+)"/u', "", $workFilter);
        /* print_r2(array(
            "exact" => $exactsKeys,
            "regexp" => $regexpKeys,
            "full" => $fullsKeys,
            "final"=>$workFilter
        ));*/
        
        $filter = $keywords;
        foreach ($exactsKeys as $aKey) {
            $aKey = str_replace('&space;', ' ', $aKey);
            $repl = sprintf("svalues ~* E'\\\\y%s\\\\y'", pg_escape_string(substr($aKey, 1, -1)));
            $filter = str_replace($aKey, $repl, $filter);
        }
        foreach ($fullsKeys as $aKey) {
            $repl = sprintf("fulltext @@ to_tsquery('french','%s')", pg_escape_string(unaccent($aKey)));
            $filter = str_replace($aKey, $repl, $filter);
        }
        foreach ($regexpKeys as $aKey) {
            $repl = sprintf("svalues ~* E'%s'", pg_escape_string(substr($aKey, 1)));
            $filter = str_replace($aKey, $repl, $filter);
        }
        // add implicit AND
        $filter = preg_replace("/'\)\s+svalues/", "') and svalues", $filter);
        $filter = preg_replace("/'\)\s+fulltext/", "') and fulltext", $filter);
        $filter = preg_replace("/'\s+fulltext/", "' and fulltext", $filter);
        $filter = preg_replace("/'\s+svalues/", "' and svalues", $filter);
        
        $this->addFilter($filter);
    }
    
    protected function filterSValues($keyword)
    {
        return sprintf("svalues ~* E'\\\\y%s\\\\y'", pg_escape_string($keyword));
    }
    protected function filterFulltext($keyword)
    {
        return sprintf("fulltext @@ to_tsquery('french','%s')", pg_escape_string(unaccent($keyword)));
    }
    protected function addFullKeywords($keywords, $useSpell = false)
    {
        
        $filter = preg_replace('/\s+(OR)\s+/', '|', $keywords);
        $filter = preg_replace('/\s+(AND)\s+/', '&', $filter);
        $filter = preg_replace('/\s+/', '&', $filter);
        if ($useSpell) {
            $filter = preg_replace("/(?m)([\p{L}]+)/ue", "\$this->testSpell('\\1')", $filter);
        }
        
        $q3 = sprintf("fulltext @@ to_tsquery('french','%s')", pg_escape_string(unaccent($filter)));
        $this->addFilter($q3);
    }
    
    protected function testSpell($word, $language = "fr")
    {
        static $pspell_link = null;
        if (function_exists('pspell_new')) {
            if (!$pspell_link) $pspell_link = pspell_new($language, "", "", "utf-8", PSPELL_FAST);
            if ((!is_numeric($word)) && (!pspell_check($pspell_link, $word))) {
                $suggestions = pspell_suggest($pspell_link, $word);
                $sug = unaccent($suggestions[0]);
                if ($sug && ($sug != unaccent($word)) && (!strstr($sug, ' '))) {
                    $word = sprintf("(%s|%s)", $word, $sug);
                }
            }
        }
        return $word;
    }
    /**
     * return where condition like : foo in ('x','y','z')
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
     * add a condition in filters
     *
     * @return void
     */
    public function noViewControl()
    {
        $this->userid = 1;
    }
    /**
     * the return of ::search will be array of document's object
     *
     * @param bool $returnobject set to true to return object, false to return raw data
     * @return void
     */
    public function setObjectReturn($returnobject = true)
    {
        if ($returnobject) $this->mode = "ITEM";
        else $this->mode = "TABLE";
    }
    /**
     * the return of ::search will be array of values
     * @deprecated
     * @return void
     */
    public function setValueReturn()
    {
        deprecatedFunction();
        $this->mode = "TABLE";
    }
    /**
     * add a filter to not return confidential document if current user cannot see it
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
}
?>