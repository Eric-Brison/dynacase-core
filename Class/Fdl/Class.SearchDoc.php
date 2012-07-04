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
     *
     * @var string pertinence order in case of full search
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
        $this->setOrder('title');
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
                $tqsql = $this->getQueries();
                $this->debuginfo["query"] = $tqsql[0];
                $count = 0;
                if (!is_array($tqsql)) {
                    $this->debuginfo["err"] = _("cannot produce sql request");
                    return 0;
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
     * return memberof to be used in profile filters
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
        $tqsql = $this->getQueries();
        
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
                $this->count = $this->countDocs();
            } else {
                $this->count = count($this->result);
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
     *reset results to use another search
     * @return void
     */
    public function reset()
    {
        $this->result = false;
        $this->resultPos = 0;
        $this->resultQPos = 0;
        $this->debuginfo = "";
    }
    /**
     * reset result offset
     * use it to redo a document iteration
     */
    public function rewind()
    {
        
        $this->resultPos = 0;
        $this->resultQPos = 0;
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
     * @return array|SearchDoc array of documents if no setObjectReturn el itself
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
        
        $this->result = getChildDoc($this->dbaccess, $this->dirid, $this->start, $this->slice, $this->getFilters() , $this->userid, $this->searchmode, $this->fromid, $this->distinct, $this->orderby, $this->latest, $this->trash, $debuginfo, $this->folderRecursiveLevel, $this->join, $this);
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
     * can, be use in loop
     * ::search must be call before
     *
     * @return Doc|array or null if this is the end
     */
    public function nextDoc()
    {
        if ($this->mode == "ITEM") {
            $n = $this->result[$this->resultQPos];
            if (!$n) return false;
            $tdoc = @pg_fetch_array($n, $this->resultPos, PGSQL_ASSOC);
            if ($tdoc === false) {
                $this->resultQPos++;
                $n = $this->result[$this->resultQPos];
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
            if (preg_match('/^([a-z0-9_\-]+\()?([a-z0-9_\-]+)\./', $filter, $reg)) {
                // when use join filter like "zoo_espece.es_classe='Boo'"
                $famid = getFamIdFromName($this->dbaccess, $reg[2]);
                if ($famid > 0) $filter = preg_replace('/^([a-z0-9_\-]+\()?([a-z0-9_\-]+)\./', '${1}doc' . $famid . '.', $filter);
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
        if (!$this->checkGeneralFilter($keywords)) {
            $this->debuginfo["error"] = sprintf(_("incorrect global filter %s") , $keywords);
        } else {
            $filter = $this->getGeneralFilter(trim($keywords) , $useSpell, $this->pertinenceOrder, $this->highlightWords);
            $this->addFilter($filter);
        }
    }
    /**
     * Verify if $keywords syntax is comptatible with a part of query
     * for the moment verify only parenthesis balancing
     * @param string $keyword
     * @return bool
     */
    private function checkGeneralFilter($keyword)
    {
        // test parentensis count
        if (preg_match('/\(\s*\)/u', $keyword)) return false;
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
    public function setPertinenceOrder($keyword = '')
    {
        if ($keyword != '') {
            $rank = preg_replace('/\s+(OR)\s+/u', '|', $keyword);
            $rank = preg_replace('/\s+(AND)\s+/u', '&', $rank);
            $rank = preg_replace('/\s+/u', '&', $rank);
            $this->pertinenceOrder = sprintf("ts_rank(fulltext,to_tsquery('french','%s')) desc, id desc", pg_escape_string(unaccent($rank)));
        }
        if ($this->pertinenceOrder) $this->setOrder($this->pertinenceOrder);
    }
    /**
     * get global filter
     * @see addGeneralFilter
     * @static
     * @param string $keywords
     * @param bool $useSpell
     * @param string $pertinenceOrder return pertinence order
     * @param string $highlightWords return words to be use by SearchHighlight class
     * @return string sql filter
     */
    public static function getGeneralFilter($keywords, $useSpell = false, &$pertinenceOrder = '', &$highlightWords = '')
    {
        if ((strstr($keywords, '"') == false) && (strstr($keywords, '~') == false)) {
            $filter = self::getFullFilter($keywords, $useSpell, $pertinenceOrder, $highlightWords);
        } else {
            
            $filter = self::getMiscFilter($keywords, $useSpell, $pertinenceOrder);
        }
        return $filter;
    }
    /**
     * return sql filter from some words or expressions
     * @static
     * @param string $keywords
     * @param bool $useSpell
     * @param string $pertinenceOrder return pertinence order
     * @return string sql filter
     */
    protected static function getMiscFilter($keywords, $useSpell = false, &$pertinenceOrder = '')
    {
        $workFilter = preg_replace('/\s+(OR)\s+/u', '|||', $keywords);
        $workFilter = preg_replace('/\s+(AND)\s+/u', '&&&', $workFilter);
        $workFilter = preg_replace('/\s+/u', '&space;', $workFilter);
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
        $rank = $keywords;
        foreach ($exactsKeys as $aKey) {
            $aKey = str_replace('&space;', ' ', $aKey);
            $repl = sprintf("svalues ~* E'\\\\y%s\\\\y'", pg_escape_string(substr($aKey, 1, -1)));
            $filter = str_replace($aKey, $repl, $filter);
            $repl = unaccent(substr($aKey, 1, -1));
            $rank = str_replace($aKey, $repl, $rank);
        }
        foreach ($fullsKeys as $aKey) {
            if ($useSpell) {
                $rKey = self::testSpell($aKey);
            } else {
                $rKey = $aKey;
            }
            $repl = sprintf("fulltext @@ to_tsquery('french','%s')", pg_escape_string(unaccent($rKey)));
            
            $filter = str_replace($aKey, $repl, $filter);
            $rank = str_replace($aKey, $rKey, $rank);
        }
        foreach ($regexpKeys as $aKey) {
            $repl = sprintf("svalues ~* E'%s'", pg_escape_string(substr($aKey, 1)));
            $filter = str_replace($aKey, $repl, $filter);
        }
        // add implicit AND
        $filter = preg_replace("/'\\)\\s+svalues/", "') and svalues", $filter);
        $filter = preg_replace("/'\\)\\s+fulltext/", "') and fulltext", $filter);
        $filter = preg_replace("/'\\s+fulltext/", "' and fulltext", $filter);
        $filter = preg_replace("/'\\s+svalues/", "' and svalues", $filter);
        
        $rank = preg_replace('/\s+(OR)\s+/u', '|', $rank);
        $rank = preg_replace('/\s+(AND)\s+/u', '&', $rank);
        $rank = preg_replace('/\s+/u', '&', $rank);
        $rank = str_replace('~', '', $rank);
        $pertinenceOrder = sprintf("ts_rank(fulltext,to_tsquery('french','%s')) desc", pg_escape_string(unaccent($rank)));
        return ($filter);
    }
    /**
     * @param Doc $doc document to analyze
     * @param string $beginTag delimiter begin tag
     * @param string $endTag delimiter end tag
     * @param int $limit file size limit to analyze
     * @return mixed
     */
    public function getHighLightText(Doc & $doc, $beginTag = '<b>', $endTag = '</b>', $limit = 200)
    {
        static $oh = null;
        if (!$oh) {
            $oh = new SearchHighlight();
        }
        if ($beginTag) $oh->beginTag = $beginTag;
        if ($endTag) $oh->endTag = $endTag;
        if ($limit > 0) $oh->setLimit($limit);
        simpleQuery($this->dbaccess, sprintf("select svalues from docread where id=%d", $doc->id) , $text, true, true);
        $h = $oh->highlight($text, $this->highlightWords);
        
        return $h;
    }
    /**
     * get full filter from some words
     * @static
     * @param string $words
     * @param bool $useSpell
     * @param string $pertinenceOrder return pertinence order
     * @return string sql filter
     */
    protected static function getFullFilter($words, $useSpell = false, &$pertinenceOrder = '', &$highlightWords = '')
    {
        $filter = trim($words, "'");
        
        $filter = preg_replace('/\p{S}/u', ' ', $filter);
        $filter = preg_replace('/\p{Po}/u', ' ', $filter);
        $filter = preg_replace('/\s+(OR)\s+/u', '|', trim($filter));
        $filter = preg_replace('/\s+(AND)\s+/u', '&', $filter);
        $filter = preg_replace('/\s*\)\s*/u', ')', $filter);
        $filter = preg_replace('/\s*\(\s*/u', '(', $filter);
        $filter = preg_replace('/\s+/u', '&', $filter);
        $filter = preg_replace('/([\p{L}\)])\(/u', '\\1&(', $filter);
        if ($useSpell) {
            $filter = preg_replace("/(?m)([\p{L}]+)/ue", "self::testSpell('\\1')", $filter);
        }
        
        $fullKey = pg_escape_string(unaccent($filter));
        $pertinenceOrder = sprintf("ts_rank(fulltext,to_tsquery('french','%s')) desc, id desc", $fullKey);
        $highlightWords = $fullKey;
        //print_r2($pertinenceOrder);
        $q = sprintf("fulltext @@ to_tsquery('french','%s')", $fullKey);
        return $q;
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
    
    public function isObjectReturn()
    {
        return ($this->mode == "ITEM");
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
    /**
     * Get the SQL queries that will be executed by the search() method
     * @return array|bool boolean false on error, or array() of queries on success.
     */
    function getQueries()
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
        
        if (($fromid != "") && (!is_numeric($fromid))) {
            preg_match('/^(?P<sign>-?)(?P<fromid>.+)$/', trim($fromid) , $m);
            $fromid = $m['sign'] . getFamIdFromName($dbaccess, $m['fromid']);
        }
        if ($this->only && strpos($fromid, '-') !== 0) {
            $fromid = '-' . $fromid;
        }
        $table = "doc";
        $only = "";
        if ($trash == "only") $distinct = true;
        if ($fromid == - 1) $table = "docfam";
        elseif ($fromid < 0) {
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
            if (preg_match("/([a-z0-9_\-:]+)\s*(=|<|>|<=|>=)\s*([a-z0-9_\-:]+)\(([^\)]*)\)/", $join, $reg)) {
                $joinid = getFamIdFromName($dbaccess, $reg[3]);
                $jointable = ($joinid) ? "doc" . $joinid : $reg[3];
                
                $sqlfilters[] = sprintf("%s.%s %s %s.%s", $table, $reg[1], $reg[2], $jointable, $reg[4]); // "id = dochisto(id)";
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
            
            if ($trash == "only") {
                $sqlfilters[-3] = $maintabledot . "doctype = 'Z'";
            } elseif ($trash == "also");
            else if (!$fromid) $sqlfilters[-3] = $maintabledot . "doctype != 'Z'";
            
            if (($latest) && (($trash == "no") || (!$trash))) $sqlfilters[-1] = $maintabledot . "locked != -1";
            ksort($sqlfilters);
            if (count($sqlfilters) > 0) $sqlcond = " (" . implode(") and (", $sqlfilters) . ")";
            $qsql = "select $selectfields " . "from $only $table  " . "where  " . $sqlcond;
            $qsql = $this->injectFromClauseForOrderByLabel($fromid, $this->orderbyLabel, $qsql);
        } else {
            //-------------------------------------------
            // in a specific folder
            //-------------------------------------------
            $fld = new_Doc($dbaccess, $dirid);
            if ($fld->defDoctype != 'S') {
                $hasFilters = false;
                if ($fld && method_exists($fld, "getSpecificFilters")) {
                    $specFilters = $fld->getSpecificFilters();
                    if (is_array($specFilters) && (count($specFilters) > 0)) {
                        $sqlfilters = array_merge($sqlfilters, $specFilters);
                        $hasFilters = true;
                    }
                }
                if (strpos(implode(",", $sqlfilters) , "archiveid") === false) $sqlfilters[-4] = $maintabledot . "archiveid is null";
                //if ($fld->getValue("se_trash")!="yes") $sqlfilters[-3] = "doctype != 'Z'";
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
                            
                            /**
                             * @var DocSearch $fld
                             */
                            $fld = new_Doc($dbaccess, $dirid);
                            if ($trash) {
                                $fld->setValue("se_trash", $trash);
                            } else {
                                $trash = $fld->getValue("se_trash");
                            }
                            $fld->folderRecursiveLevel = $folderRecursiveLevel;
                            $tsqlM = $fld->getQuery();
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
                                    $fldFromId = ($fromid == 0) ? $fld->getValue('se_famid', 0) : $fromid;
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
        if ($attr === false) {
            return $sqlM;
        }
        switch ($attr->type) {
            case 'enum':
                $enumKeyLabelList = $attr->getEnum();
                $mapValues = array();
                foreach ($enumKeyLabelList as $key => $label) {
                    $mapValues[] = sprintf("('%s', '%s')", pg_escape_string($key) , pg_escape_string($label));
                }
                $map = sprintf('(VALUES %s) AS map_%s(key, label)', join(', ', $mapValues) , $attr->id);
                $where = sprintf('map_%s.key = doc%s.%s', $attr->id, $fromid, $attr->id);
                
                $sqlM = preg_replace('/ where /i', ", $map where ($where) and ", $sqlM);
                $this->orderby = preg_replace(sprintf('/\b%s\b/', preg_quote($column)) , sprintf("map_%s.label", $attr->id) , $this->orderby);
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
                    $this->orderby = preg_replace(sprintf('/\b%s\b/', preg_quote($column)) , $opt_doctitle, $this->orderby);
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
