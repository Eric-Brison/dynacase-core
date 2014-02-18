<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Document searches classes
 *
 * @author Anakeen
 * @version $Id: Class.DocSearch.php,v 1.56 2008/11/19 08:47:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */

include_once ("FDL/Class.PDocSearch.php");
include_once ("FDL/Lib.Dir.php");

class DocSearch extends PDocSearch
{
    
    public $defDoctype = 'S';
    public $defaultedit = "FREEDOM:EDITSEARCH";
    
    public $tol = array(
        "and" => "and", #N_("and")
        "or" => "or"
    ); #N_("or")
    
    
    /**
     * max recursive level
     * @public int
     */
    public $folderRecursiveLevel = 2;
    /**
     * @var SearchDoc
     */
    protected $search = null;
    function DocSearch($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        
        PDocSearch::__construct($dbaccess, $id, $res, $dbid);
        if (((!isset($this->fromid))) || ($this->fromid == "")) $this->fromid = FAM_SEARCH;
    }
    
    public function preCreated()
    {
        return $this->updateSearchAuthor();
    }
    /**
     * the author is the current user if not already set
     * @return string
     */
    public function updateSearchAuthor()
    {
        $err = '';
        if (!$this->getRawValue("se_author")) {
            $err = $this->setValue("se_author", $this->getUserId());
        }
        return $err;
    }
    /**
     * to affect a special query to a SEARCH document
     * must be call after the add method When use this method others filter parameters are ignored.
     * @param string $tquery the sql query
     * @return string error message (empty if no error)
     */
    function addStaticQuery($tquery)
    {
        $this->setValue("se_static", "1");
        $err = $this->addQuery($tquery);
        return $err;
    }
    
    function AddQuery($tquery)
    {
        // insert query in search document
        if (is_array($tquery)) $query = implode(";\n", $tquery);
        else $query = $tquery;
        
        if ($query == "") return "";
        if ($this->id == "") return "";
        
        if (substr($query, 0, 6) != "select") {
            AddWarningMsg(sprintf(_("query [%s] not valid for select document") , $query));
            return sprintf(_("query [%s] not valid for select document") , $query);
        }
        $oqd = new QueryDir($this->dbaccess);
        $oqd->dirid = $this->id;
        $oqd->qtype = "M"; // multiple
        $oqd->query = $query;
        
        if ($this->id > 0) $this->exec_query("delete from fld where dirid=" . intval($this->id) . " and qtype='M'");
        $err = $oqd->Add();
        if ($err == "") {
            $this->setValue("SE_SQLSELECT", $query);
            $err = $this->modify();
        }
        
        return $err;
    }
    /**
     * Test if current user can add or delete document in this folder
     * always false for a search
     * @return string error message, if no error empty string
     */
    function canModify()
    {
        return _("containt of searches cannot be modified");
    }
    /**
     * return true if the search has parameters
     */
    function isParameterizable()
    {
        return false;
    }
    
    function GetQueryOld()
    {
        $query = new QueryDb($this->dbaccess, "QueryDir");
        $query->AddQuery("dirid=" . $this->id);
        $query->AddQuery("qtype != 'S'");
        $tq = $query->Query(0, 0, "TABLE");
        
        if ($query->nb > 0) {
            return $tq[0]["query"];
        }
        return "";
    }
    /**
     * return SQL query(ies) needed to search documents
     * @return array string
     */
    function getQuery()
    {
        if (!$this->isStaticSql()) {
            $query = $this->ComputeQuery($this->getRawValue("se_key") , $this->getRawValue("se_famid") , $this->getRawValue("se_latest") , $this->getRawValue("se_case") == "yes", $this->getRawValue("se_idfld") , $this->getRawValue("se_sublevel") === "", $this->getRawValue("se_case") == "full");
            // print "<HR>getQuery1:[$query]";
            
        } else {
            $query[] = $this->getRawValue("SE_SQLSELECT");
            // print "<BR><HR>".$this->getRawValue("se_latest")."/".$this->getRawValue("se_case")."/".$this->getRawValue("se_key");
            //  print "getQuery2:[$query]";
            
        }
        
        return $query;
    }
    /**
     * @param bool $full set to true if wan't use full text indexing
     */
    function setSqlGeneralFilters($famid, $keyword, $cdirid = '', $latest)
    {
        
        $this->search = new SearchDoc($this->dbaccess, $famid);
        $this->search->only = ($this->getRawValue("se_famonly") == "yes");
        $this->search->latest = ($latest == "yes");
        $this->search->trash = $this->getRawValue("se_trash");
        if ($cdirid) $this->search->useCollection($cdirid);
        if ($keyword) $this->search->addGeneralFilter($keyword);
        $filters = array();
        
        $acls = $this->getMultipleRawValues("se_acl");
        if ((count($acls) > 0 && ($this->userid != 1))) {
            //      print_r2($acls);
            foreach ($acls as $acl) {
                $dacl = $this->dacls[$acl];
                if ($dacl) {
                    $posacl = $dacl["pos"];
                    $this->search->addFilter(sprintf("hasaprivilege('%s', profid, %d)", DocPerm::getMemberOfVector($this->userid) , (1 << intval($posacl))));
                }
            }
        }
        
        if ($latest == "fixed") {
            $this->search->latest = false;
            $this->search->addFilter("locked = -1");
            $this->search->addFilter("lmodify = 'L'");
        } else if ($latest == "allfixed") {
            $this->search->latest = false;
            $this->search->addFilter("locked = -1");
        }
        if ($latest == "lastfixed") {
            $this->search->latest = false;
            $this->search->addFilter("locked = -1");
        }
        
        if ($this->getRawValue("se_archive") > 0) {
            $this->search->addFilter("archiveid = %d", $this->getRawValue("se_archive"));
        }
        
        $this->setValue("se_orderby", " ");
        
        if ($this->getRawValue("se_sysfam") == 'no' && (!$this->getRawValue("se_famid"))) {
            $this->search->addFilter("usefor !~ '^S'");
            $this->search->addFilter("doctype != 'C'");
        }
        return $filters;
    }
    /**
     * return sqlfilters for a simple query in fulltext mode
     * @param string $keyword the word(s) searched
     * @param array &$sqlfilters return array of sql conditions
     * @param string &$sqlorder return sql order by
     * @param string &$fullkeys return tsearch2 keys for use it in headline sql function
     * @return void
     */
    static function getFullSqlFilters($keyword, &$sqlfilters, &$sqlorder, &$fullkeys)
    {
        $fullkeys = "";
        $sqlorder = "";
        $sqlfilters = array(
            "true"
        );
        if ($keyword == "") return;
        $pspell_link = false;
        if (function_exists('pspell_new')) {
            $pspell_link = pspell_new("fr", "", "", "utf-8", PSPELL_FAST);
        }
        $tkeys = array();
        $sqlfilters = array();
        
        $keyword = preg_replace('/\s+(OR)\s+/u', '|', $keyword);
        $keyword = preg_replace('/\s+(AND)\s+/u', ' ', $keyword);
        $tkeys = explode(" ", $keyword);
        $sqlfiltersbrut = array();
        $tsearchkeys = array();
        foreach ($tkeys as $k => $key) {
            $key = trim($key);
            if ($key) {
                $tsearchkeys[$k] = $key;
                if ($pspell_link !== false) {
                    if ((!is_numeric($key)) && (strstr($key, '|') === false) && (strstr($key, '&') === false) && (ord($key[0]) > 47) && (!pspell_check($pspell_link, $key))) {
                        $suggestions = pspell_suggest($pspell_link, $key);
                        $sug = $suggestions[0];
                        //foreach ($suggestions as $k=>$suggestion) {  echo "$k : $suggestion\n";  }
                        if ($sug && (unaccent($sug) != $key) && (!strstr($sug, ' '))) $tsearchkeys[$k] = "$key|$sug";
                    }
                }
                if (strstr($key, '"') !== false) {
                    // add more filter for search complete and exact expression
                    if (strstr($key, '|') === false) {
                        $sqlfiltersbrut[] = "svalues ~* E'\\\\y" . pg_escape_string(str_replace(array(
                            '"',
                            '&',
                            '(',
                            ')'
                        ) , array(
                            "",
                            ' ',
                            '',
                            ''
                        ) , $key)) . "\\\\y' ";
                    } else {
                        list($left, $right) = explode("|", $key);
                        if (strstr($left, '"') !== false) $q1 = "svalues ~* E'\\\\y" . pg_escape_string(str_replace(array(
                            '"',
                            '&',
                            '(',
                            ')'
                        ) , array(
                            "",
                            ' ',
                            '',
                            ''
                        ) , $left)) . "\\\\y' ";
                        else $q1 = "";
                        if (strstr($right, '"') !== false) $q2 = "svalues ~* E'\\\\y" . pg_escape_string(str_replace(array(
                            '"',
                            '&',
                            '(',
                            ')'
                        ) , array(
                            "",
                            ' ',
                            '',
                            ''
                        ) , $right)) . "\\\\y' ";
                        else $q2 = "";
                        $q3 = "fulltext @@ to_tsquery('search.french','" . pg_escape_string($left) . "') ";
                        $q4 = "fulltext @@ to_tsquery('search.french','" . pg_escape_string($right) . "') ";
                        
                        if ((!$q1) && $q2) $sqlfiltersbrut[] = "($q4 and $q2) or $q3";
                        elseif ((!$q2) && $q1) $sqlfiltersbrut[] = "($q3 and $q1) or $q4";
                        elseif ($q2 && $q1) $sqlfiltersbrut[] = "($q3 and $q1) or ($q4 and $q2)";
                    }
                }
            }
        }
        
        if (count($tsearchkeys) > 0) {
            $fullkeys = '(' . implode(")&(", $tsearchkeys) . ')';
            $fullkeys = unaccent($fullkeys);
            $fullkeys = pg_escape_string($fullkeys);
            $sqlfilters[] = "fulltext @@ to_tsquery('search.french','$fullkeys') ";
        }
        if (count($sqlfiltersbrut) > 0) $sqlfilters = array_merge($sqlfilters, $sqlfiltersbrut);
        $sqlorder = "ts_rank(fulltext,to_tsquery('search.french','$fullkeys')) desc";
    }
    
    function ComputeQuery($keyword = "", $famid = - 1, $latest = "yes", $sensitive = false, $dirid = - 1, $subfolder = true, $full = false)
    {
        
        $this->setSqlGeneralFilters($famid, $keyword, $dirid, $latest);
        
        $queries = $this->search->getQueries();
        
        return $queries;
    }
    /**
     * return true if the sqlselect is writted by hand
     * @return bool
     */
    function isStaticSql()
    {
        return ($this->getRawValue("se_static") != "") || (($this->getRawValue("se_latest") == "") && ($this->getRawValue("se_case") == "") && ($this->getRawValue("se_key") == ""));
    }
    /**
     * return error if query filters are not compatibles
     * @return string error message , empty if no errors
     */
    function getSqlParseError()
    {
        return "";
    }
    
    function preRefresh()
    {
        $err = "";
        
        if (!$this->isStaticSql()) {
            if (!$this->isParameterizable()) $query = $this->getQuery();
            else $query = 'select id from only doc where false';
            $err = $this->AddQuery($query);
        }
        if ($err == "") $err = $this->getSqlParseError();
        return $err;
    }
    /**
     * @templateController
     * @return string
     */
    function editsearch()
    {
        global $action;
        
        $rtarget = getHttpVars("rtarget");
        $this->lay->eset("rtarget", $rtarget);
        $this->lay->set("restrict", false);
        $this->lay->set("archive", false);
        
        $farch = new_doc($this->dbaccess, "ARCHIVING");
        if ($farch) $this->lay->set("archive", ($farch->control("view") == ""));
        $this->lay->eset("thekey", $this->getRawValue("se_key"));
        $dirid = GetHttpVars("dirid"); // to set restriction family
        $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/lib/jquery/jquery.js");
        $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FDL/Layout/edittable.js");
        $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FREEDOM/Layout/editdsearch.js");
        $famid = $this->getRawValue("se_famid");
        $classid = 0;
        if ($dirid > 0) {
            /**
             * @var Dir $dir
             */
            $dir = new_Doc($this->dbaccess, $dirid);
            if (method_exists($dir, "isAuthorized")) {
                if ($dir->isAuthorized($classid)) {
                    // verify if classid is possible
                    if ($dir->hasNoRestriction()) {
                        $tclassdoc = GetClassesDoc($this->dbaccess, $action->user->id, $classid, "TABLE");
                    } else {
                        $tclassdoc = $dir->getAuthorizedFamilies();
                        $this->lay->set("restrict", true);
                    }
                } else {
                    $tclassdoc = $dir->getAuthorizedFamilies();
                    $first = current($tclassdoc);
                    $famid = abs($first["id"]);
                    $this->lay->set("restrict", true);
                }
            } else {
                $tclassdoc = GetClassesDoc($this->dbaccess, $action->user->id, $classid, "TABLE");
            }
        } else {
            $tclassdoc = GetClassesDoc($this->dbaccess, $action->user->id, $classid, "TABLE");
        }
        
        $this->lay->eset("selfam", _("no family"));
        $selectclass = array();
        foreach ($tclassdoc as $k => $cdoc) {
            $selectclass[$k]["idcdoc"] = $cdoc["id"];
            $selectclass[$k]["classname"] = $cdoc["title"];
            $selectclass[$k]["system_fam"] = (substr($cdoc["usefor"], 0, 1) == 'S') ? true : false;
            if (abs($cdoc["initid"]) == abs($famid)) {
                $selectclass[$k]["selected"] = "selected";
                if ($famid < 0) $this->lay->eset("selfam", $cdoc["title"] . " " . !!_("(only)"));
                else $this->lay->eset("selfam", $cdoc["title"]);
            } else $selectclass[$k]["selected"] = "";
        }
        
        $this->lay->SetBlockData("SELECTCLASS", $selectclass);
        $this->lay->set("has_permission_fdl_system", $action->parent->hasPermission('FDL', 'SYSTEM'));
        $this->lay->set("se_sysfam", ($this->getRawValue('se_sysfam') == 'yes') ? true : false);
        
        $this->editattr();
    }
    /**
     * @templateController
     * @return string
     */
    function editspeedsearch()
    {
        return $this->editsearch();
    }
    /**
     * return document includes in search folder
     * @param bool $controlview if false all document are returned else only visible for current user  document are return
     * @param array $filter to add list sql filter for selected document
     * @param int $famid family identifier to restrict search
     * @return array array of document array
     */
    public function getContent($controlview = true, array $filter = array() , $famid = "", $qtype = "TABLE", $trash = "")
    {
        if ($controlview) $uid = $this->userid;
        else $uid = 1;
        $orderby = $this->getRawValue("se_orderby", "title");
        $tdoc = internalGetDocCollection($this->dbaccess, $this->initid, 0, "ALL", $filter, $uid, "TABLE", $famid, false, $orderby, true, $this->getRawValue("se_trash"));
        return $tdoc;
    }
}
?>
