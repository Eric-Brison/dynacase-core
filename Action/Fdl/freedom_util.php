<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Function Utilities for freedom
 *
 * @author Anakeen 2000
 * @version $Id: freedom_util.php,v 1.119 2009/01/20 14:30:39 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Lib.Util.php");
//
// ------------------------------------------------------
// construction of a sql disjonction
// ------------------------------------------------------
function GetSqlCond2($Table, $column)
// ------------------------------------------------------

{
    $sql_cond = "";
    if (count($Table) > 0) {
        $sql_cond = "(($column = '$Table[0]') ";
        for ($i = 1; $i < count($Table); $i++) {
            $sql_cond = $sql_cond . "OR ($column = '$Table[$i]') ";
        }
        $sql_cond = $sql_cond . ")";
    }
    
    return $sql_cond;
}

function GetSqlCond($Table, $column, $integer = false)
// ------------------------------------------------------

{
    $sql_cond = "";
    if (count($Table) > 0) {
        if ($integer) { // for integer type
            $sql_cond = "$column in (";
            $sql_cond.= implode(",", $Table);
            $sql_cond.= ")";
        } else { // for text type
            foreach ($Table as & $v) $v = pg_escape_string($v);
            $sql_cond = "$column in ('";
            $sql_cond.= implode("','", $Table);
            $sql_cond.= "')";
        }
    }
    
    return $sql_cond;
}
/**
 * return first element of array
 * @param array $a
 * @return string the first, false is empty
 */
function first($a)
{
    if (count($a) == 0) return false;
    reset($a);
    return current($a);
}

function notEmpty($a)
{
    return (!empty($a));
}
/**
 * function use by Doc::getOOoValue()
 * use to convert html to xhtml
 * @param string $lt the < character
 * @param string $tag the tag name
 * @param string $attr all attributes of tag
 * @param string $gt the > tag
 * @return string the new tag
 */
function toxhtmltag($lt, $tag, $attr, $gt)
{
    //  print "\ntoxhtmltag($tag,$attr)\n ";
    if ($tag == "font") return '';
    elseif (strpos($tag, ':') > 0) {
        return strtolower($lt . 'xhtml:span' . $gt);
    } else {
        $attr = str_replace(':=', '=', $attr);
        return strtolower($lt . "xhtml:" . $tag . $attr . $gt);
    }
}
/**
 * function use by Doc::getOOoValue()
 * use to trap XML parsing error : raise exception
 * @param int $errno error number
 * @param string $errstr error message
 * @param string $errfile
 * @param string $errline error line
 * @return bool
 */
function HandleXmlError($errno, $errstr, $errfile, $errline)
{
    if ($errno == E_WARNING && (substr_count($errstr, "DOMDocument::loadXML()") > 0)) {
        throw new DOMException($errstr);
    } else return false;
}
/**
 * clear all cache used by new_doc function
 * @param int $id document identificator : limit to destroy cache of only this document
 * @return void
 */
function clearCacheDoc($id = 0)
{
    if ($id == 0) {
        global $gdocs; // optimize for speed
        $gdocs = array();
    } else {
        $gdocs[$id] = null;
    }
}
/** 
 * optimize for speed : memorize object for future use
 * @global array $_GLOBALS["gdocs"]
 * @name $gdocs
 */
/**
 * return document object in type concordance
 * @param string $dbaccess database specification
 * @param int|string $id identificator of the object
 * @param bool $latest if true set to latest revision of doc
 * @global array $gdocs optimize for speed
 *
 * @return Doc object
 */
function new_Doc($dbaccess, $id = '', $latest = false)
{
    
    global $gdocs; // optimize for speed
    if ($dbaccess == "") {
        // don't test if file exist or must be searched in include_path
        $dbaccess = getDbAccess();
    }
    //    print("doctype:".$res["doctype"]);
    $classname = "";
    if (($id == '')) {
        include_once ("FDL/Class.DocFile.php");
        $doc = new DocFile($dbaccess);
        
        return ($doc);
    }
    $fromid = "";
    $gen = ""; // path GEN or not
    if (!is_numeric($id)) $id = getIdFromName($dbaccess, $id);
    elseif ($latest) {
        $lid = getLatestDocId($dbaccess, $id);
        if ($lid > 0) {
            $id = $lid;
            $latest = false;
        }
    }
    $id = intval($id);
    if ($id > 0) {
        if (isset($gdocs[$id]) && ((!$latest) || ($gdocs[$id]->locked != - 1))) {
            $doc = $gdocs[$id];
            if (($doc->doctype != 'W') || (!isset($doc->doc))) {
                $doc = $gdocs[$id]; // optimize for speed
                //	if ($doc->id != $id) print_r2("<b>Error $id /".$doc->id."</b>");
                if ($doc->id == $id) {
                    $doc->cached = 1;
                    return $doc;
                } else unset($gdocs[$id]);
            }
        }
        
        $fromid = getFromId($dbaccess, $id);
        if ($fromid > 0) {
            $classname = "Doc$fromid";
            $gen = getGen($dbaccess);
        } else if ($fromid == - 1) $classname = "DocFam";
    }
    
    if ($classname != "") {
        if (!include_once ("FDL$gen/Class.$classname.php")) {
            AddWarningMsg(sprintf("cannot include %s class", $classname));
            return null;
        }
        
        $doc = new $classname($dbaccess, $id);
        
        if ($latest && $doc->locked == - 1) {
            $tl = getLatestTDoc($dbaccess, $doc->initid);
            $doc->Affect($tl);
            $id = $doc->id;
        }
        
        if (($id > 0) && (count($gdocs) < MAXGDOCS)) {
            if (($doc->doctype != 'C') || (count($doc->attributes->attr) > 0)) {
                $doc->iscached = 1;
                $gdocs[$id] = & $doc;
            }
            //print_r2("<b>use cache $id /".$doc->id."</b>");
            
        }
        return ($doc);
    } else {
        include_once ("FDL/Class.DocFile.php");
        $doc = new DocFile($dbaccess, $id);
        
        return ($doc);
    }
}
/**
 * create a new document object in type concordance
 *
 * the document is set with default values and default profil of the family
 * @param string $dbaccess database specification
 * @param string $fromid identificator of the family document (the number or internal name)
 * @param bool $control if false don't control the user hability to create this kind of document
 * @param bool $defaultvalues  if false not affect default values
 * @param bool $temporary  if true create document as temporary doc (use Doc::createTmpDoc instead)
 * @see createTmpDoc to create temporary/working document
 * @code
 * $myDoc=createDoc("", "SOCIETY");
 * if ($myDoc) {
 *     $myDoc->setValue("si_name", "my company");
 *     $err=$myDoc->store();
 * }
 * @endcode
 * @return Doc may be return false if no hability to create the document
 */
function createDoc($dbaccess, $fromid, $control = true, $defaultvalues = true, $temporary = false)
{
    
    if (!is_numeric($fromid)) $fromid = getFamIdFromName($dbaccess, $fromid);
    if ($fromid > 0) {
        include_once ("FDL/Class.DocFam.php");
        $cdoc = new DocFam($dbaccess, $fromid);
        
        if ($control) {
            $err = $cdoc->control('create');
            if ($err != "") return false;
        }
        
        $classname = "Doc" . $fromid;
        $GEN = getGen($dbaccess);
        include_once ("FDL$GEN/Class.$classname.php");
        /**
         * @var DocFam $doc
         */
        $doc = new $classname($dbaccess);
        
        $doc->revision = "0";
        $doc->doctype = $doc->defDoctype; // it is a new  document (not a familly)
        $doc->cprofid = "0"; // NO CREATION PROFILE ACCESS
        $doc->fromid = $fromid;
        if (!$temporary) {
            $doc->setProfil($cdoc->cprofid); // inherit from its familly
            $doc->setCvid($cdoc->ccvid); // inherit from its familly
            $doc->wid = $cdoc->wid;
        }
        $doc->icon = $cdoc->icon; // inherit from its familly
        $doc->usefor = $cdoc->usefor; // inherit from its familly
        $doc->forumid = $cdoc->forumid; // inherit from its familly
        $doc->atags = $cdoc->atags;
        if ($defaultvalues) $doc->setDefaultValues($cdoc->getDefValues());
        $doc->ApplyMask();
        return ($doc);
    }
    return new_Doc($dbaccess);
}
/**
 * create a temporary  document object in type concordance
 *
 * the document is set with default values and has no profil
 * the create privilege is not tested in this case
 * @param string $dbaccess database specification
 * @param string $fromid identificator of the family document (the number or internal name)
 * @param bool $defaultvalue set to false to not set default values
 * @return Doc may be return false if no hability to create the document
 */
function createTmpDoc($dbaccess, $fromid, $defaultvalue = true)
{
    $d = createDoc($dbaccess, $fromid, false, $defaultvalue, true);
    if ($d) {
        $d->doctype = 'T'; // tag has temporary document
        $d->profid = 0; // no privilege
        
    }
    return $d;
}
/**
 * return from id for document (not for family (use @see getFamFromId() instead)
 * @param string $dbaccess database specification
 * @param int $id identificator of the object
 *
 * @return int false if error occured (return -1 if family document )
 */
function getFromId($dbaccess, $id)
{
    if (!($id > 0)) return false;
    if (!is_numeric($id)) return false;
    $dbid = getDbid($dbaccess);
    $fromid = false;
    
    $result = pg_query($dbid, sprintf("select fromid from docfrom where id=%d", $id));
    if ($result) {
        if (pg_num_rows($result) > 0) {
            $arr = pg_fetch_array($result, 0, PGSQL_ASSOC);
            $fromid = $arr["fromid"];
        }
    }
    
    return $fromid;
}
/**
 * return from name for document (not for family (use @see getFamFromId() instead)
 * @param string $dbaccess database specification
 * @param int $id identificator of the object
 *
 * @return string false if error occured (return -1 if family document )
 */
function getFromName($dbaccess, $id)
{
    
    if (!($id > 0)) return false;
    if (!is_numeric($id)) return false;
    $dbid = getDbid($dbaccess);
    $fromname = false;
    $result = pg_query($dbid, sprintf("SELECT name from docfam where id=(select fromid from docfrom where id=%d)", $id));
    
    if (pg_num_rows($result) > 0) {
        $arr = pg_fetch_array($result, 0, PGSQL_ASSOC);
        $fromname = $arr["name"];
    }
    
    return $fromname;
}
/**
 * return from id for family document
 * @param string $dbaccess database specification
 * @param int $id identificator of the object
 *
 * @return int false if error occured
 */
function getFamFromId($dbaccess, $id)
{
    
    if (!($id > 0)) return false;
    if (!is_numeric($id)) return false;
    $dbid = getDbid($dbaccess);
    $fromid = false;
    $result = pg_query($dbid, "select  fromid from docfam where id=$id;");
    
    if (pg_num_rows($result) > 0) {
        $arr = pg_fetch_array($result, 0, PGSQL_ASSOC);
        $fromid = intval($arr["fromid"]);
    }
    
    return $fromid;
}
/**
 * get document title from document identificator
 * @param int|string $id document identificator
 * @param bool $latest set to false for a fixed id or true for latest
 * @return string
 */
function getDocTitle($id, $latest = true)
{
    $dbaccess = getDbAccess();
    if (!is_numeric($id)) $id = getIdFromName($dbaccess, $id);
    if ($id > 0) {
        
        if (!$latest) $sql = sprintf("select title, doctype, locked, initid from docread where id=%d", $id);
        else $sql = sprintf("select title, doctype, locked, initid from docread where initid=(select initid from docread where id=%d) and locked != -1", $id);
        simpleQuery($dbaccess, $sql, $t, false, true);
        
        if (!$t) return '';
        if ($t["doctype"] == 'C') return getFamTitle($t);
        // TODO confidential property
        return $t["title"];
    }
    return '';
}
/**
 * get some properties for a document
 * @param $id
 * @param bool $latest
 * @param array $prop properties list to retrieve
 * @return array|null of indexed properties's values - empty array if not found
 */
function getDocProperties($id, $latest = true, array $prop = array(
    "title"
))
{
    $dbaccess = getDbAccess();
    if (!is_numeric($id)) $id = getIdFromName($dbaccess, $id);
    if (($id > 0) && count($prop) > 0) {
        $sProps = implode(',', $prop);
        if (!$latest) $sql = sprintf("select %s, doctype, locked, initid from docread where id=%d", $sProps, $id);
        else $sql = sprintf("select %s, doctype, locked, initid from docread where initid=(select initid from docread where id=%d) and locked != -1", $sProps, $id);
        simpleQuery($dbaccess, $sql, $t, false, true);
        
        if (!$t) return null;
        return $t;
    }
    return null;
}
/**
 * return document table value
 * @param string $dbaccess database specification
 * @param int $id identificator of the object
 * @param array $sqlfilters add sql supply condition
 *
 * @return array false if error occured
 */
function getTDoc($dbaccess, $id, $sqlfilters = array() , $result = array())
{
    global $action;
    global $SQLDELAY, $SQLDEBUG;
    
    if (!is_numeric($id)) $id = getIdFromName($dbaccess, $id);
    if (!($id > 0)) return false;
    $dbid = getDbid($dbaccess);
    $table = "doc";
    $fromid = getFromId($dbaccess, $id);
    if ($fromid > 0) $table = "doc$fromid";
    else if ($fromid == - 1) $table = "docfam";
    
    $sqlcond = "";
    if (count($sqlfilters) > 0) $sqlcond = "and (" . implode(") and (", $sqlfilters) . ")";
    if (count($result) == 0) {
        $userMemberOf = DocPerm::getMemberOfVector();
        $sql = sprintf("select *,getaperm('%s',profid) as uperm from only %s where id=%d %s", $userMemberOf, $table, $id, $sqlcond);
    } else {
        $scol = implode($result, ",");
        $sql = "select $scol from only $table where id=$id $sqlcond;";
    }
    $sqlt1 = 0;
    if ($SQLDEBUG) $sqlt1 = microtime(); // to test delay of request
    $result = pg_query($dbid, $sql);
    if ($SQLDEBUG) {
        global $TSQLDELAY;
        $SQLDELAY+= microtime_diff(microtime() , $sqlt1); // to test delay of request
        $TSQLDELAY[] = array(
            "t" => sprintf("%.04f", microtime_diff(microtime() , $sqlt1)) ,
            "s" => $sql
        );
    }
    if (($result) && (pg_num_rows($result) > 0)) {
        $arr = pg_fetch_array($result, 0, PGSQL_ASSOC);
        
        return $arr;
    }
    return false;
}
/**
 * return the value of an doc array item
 *
 * @param array &$t the array where get value
 * @param string $k the index of the value
 * @param string $d default value if not found or if it is empty
 * @return string
 */
function getv(&$t, $k, $d = "")
{
    if (isset($t[$k]) && ($t[$k] != "")) return $t[$k];
    if (strpos($t["attrids"], "£$k") !== 0) {
        
        $tvalues = explode("£", $t["values"]);
        $tattrids = explode("£", $t["attrids"]);
        foreach ($tattrids as $ka => $va) {
            if ($va != "") {
                if (!isset($t[$va])) $t[$va] = $tvalues[$ka];
                if ($va == $k) {
                    if ($tvalues[$ka] != "") return $tvalues[$ka];
                    break;
                }
            }
        }
    }
    return $d;
}
/**
 * complete all values of an doc array item
 *
 * @param array &$t the array where get value
 * @return string
 */
function getvs(&$t)
{
    $tvalues = explode("£", $t["values"]);
    $tattrids = explode("£", $t["attrids"]);
    foreach ($tattrids as $ka => $va) {
        if ($va != "") {
            if (!isset($t[$va])) $t[$va] = $tvalues[$ka];
        }
    }
    return $t;
}
/** 
 * use to usort attributes
 * @param BasicAttribute $a
 * @param BasicAttribute $b
 */
function tordered($a, $b)
{
    if (isset($a->ordered) && isset($b->ordered)) {
        if ($a->ordered == $b->ordered) return 0;
        if ($a->ordered > $b->ordered) return 1;
        return -1;
    }
    if (isset($a->ordered)) return 1;
    if (isset($b->ordered)) return -1;
    return 0;
}

function cmp_cvorder3($a, $b)
{
    if ($a["cv_order"] == $b["cv_order"]) {
        return 0;
    }
    return ($a["cv_order"] < $b["cv_order"]) ? -1 : 1;
}
/** 
 * control privilege for a document in the array form
 * the array must provide from getTdoc
 * the function is equivalent of Doc::Control
 * @param array $tdoc document
 * @param string $aclname identificator of the privilege to test
 * @return bool true if current user has privilege
 */
function controlTdoc(&$tdoc, $aclname)
{
    global $action;
    static $_ODocCtrol = false;
    static $_memberOf = false; // current user
    if (!$_ODocCtrol) {
        $cd = new DocCtrl();
        $_ODocCtrol = $cd;
        $_memberOf = DocPerm::getMemberOfVector();
    }
    
    if (($tdoc["profid"] <= 0) || ($action->user->id == 1)) return true;
    if (!isset($tdoc["uperm"])) {
        $sql = sprintf("select getaperm('%s',%d) as uperm", $_memberOf, $tdoc['profid']);
        $err = simpleQuery($action->dbaccess, $sql, $uperm, true, true);
        if (!$err) $tdoc["uperm"] = $uperm;
    }
    $err = $_ODocCtrol->ControlUp($tdoc["uperm"], $aclname);
    
    return ($err == "");
}
/** 
 * get document object from array document values
 * @param string $dbaccess database specification
 * @param array $v values of document
 * @return Doc the document object
 */
function getDocObject($dbaccess, $v, $k = 0)
{
    static $_OgetDocObject;
    
    if ($v["doctype"] == "C") {
        if (!isset($_OgetDocObject[$k]["family"])) $_OgetDocObject[$k]["family"] = new DocFam($dbaccess);
        $_OgetDocObject[$k]["family"]->Affect($v, true);
        $v["fromid"] = "family";
    } else {
        if (!isset($_OgetDocObject[$k][$v["fromid"]])) $_OgetDocObject[$k][$v["fromid"]] = createDoc($dbaccess, $v["fromid"], false, false);
    }
    $_OgetDocObject[$k][$v["fromid"]]->Affect($v, true);
    $_OgetDocObject[$k][$v["fromid"]]->nocache = true;
    return $_OgetDocObject[$k][$v["fromid"]];
}
/**
 * return the next document in sql select ressources
 * use with "ITEM" type searches direct in QueryDb
 * return Doc the next doc (false if the end)
 */
function getNextDbObject($dbaccess, $res)
{
    $tdoc = pg_fetch_array($res, NULL, PGSQL_ASSOC);
    if ($tdoc === false) return false;
    return getDocObject($dbaccess, $tdoc, intval($res));
}
/**
 * return the next document in sql select ressources
 * use with "ITEM" type searches with getChildDoc
 * return Doc the next doc (false if the end)
 */
function getNextDoc($dbaccess, &$tres)
{
    $n = current($tres);
    if ($n === false) return false;
    $tdoc = pg_fetch_array($n, NULL, PGSQL_ASSOC);
    if ($tdoc === false) {
        $n = next($tres);
        if ($n === false) return false;
        $tdoc = pg_fetch_array($n, NULL, PGSQL_ASSOC);
        if ($tdoc === false) return false;
    }
    return getDocObject($dbaccess, $tdoc, intval(current($tres)));
}
/**
 * count returned document in sql select ressources
 * @param array $tres of ressources
 * return Doc the next doc (false if the end)
 */
function countDocs(&$tres)
{
    $n = 0;
    foreach ($tres as $res) $n+= pg_num_rows($res);
    reset($tres);
    return $n;
}
/**
 * return the identificator of a family from internal name
 *
 * @param string $dbaccess database specification
 * @param string $name internal family name
 * @return int 0 if not found
 */
function getFamIdFromName($dbaccess, $name)
{
    include_once ("FDL/Class.DocFam.php");
    global $tFamIdName;
    if (!isset($tFamIdName)) {
        $tFamIdName = array();
        $q = new QueryDb($dbaccess, "DocFam");
        $ql = $q->Query(0, 0, "TABLE");
        while (list($k, $v) = each($ql)) {
            if ($v["name"] != "") $tFamIdName[$v["name"]] = $v["id"];
        }
    }
    if (isset($tFamIdName[$name])) return $tFamIdName[$name];
    if (isset($tFamIdName[strtoupper($name) ])) return $tFamIdName[strtoupper($name) ];
    return 0;
}
/**
 * return the identificator of a document from a search with title
 *
 * @param string $dbaccess database specification
 * @param string $name logical name
 * @param string $famid must be set to increase speed search
 * @param boolean $only set to true to not search in subfamilies
 * @return int 0 if not found, return negative first id found if multiple (name must be unique)
 */
function getIdFromTitle($dbaccess, $title, $famid = "", $only = false)
{
    if ($famid && (!is_numeric($famid))) $famid = getFamIdFromName($dbaccess, $famid);
    if ($famid > 0) {
        $fromonly = ($only) ? "only" : "";
        $err = simpleQuery($dbaccess, sprintf("select id from $fromonly doc%d where title='%s' and locked != -1", $famid, pg_escape_string($title)) , $id, true, true);
    } else {
        $err = simpleQuery($dbaccess, sprintf("select id from docread where title='%s' and locked != -1", pg_escape_string($title)) , $id, true, true);
    }
    
    return $id;
}
/**
 * return the identificator of a document from its logical name
 *
 * @param string $dbaccess database specification
 * @param string $name logical name
 * @param string $famid must be set to increase speed search
 * @return int 0 if not found, return negative first id found if multiple (name must be unique)
 */
function getIdFromName($dbaccess, $name, $famid = "")
{
    static $first = true;
    $dbid = getDbid($dbaccess);
    $id = false;
    
    if ($first) {
        @pg_prepare($dbid, "getidfromname", 'select id from docname where name=$1');
        $first = false;
    }
    //  $result = pg_query($dbid,"select id from docname where name='$name';");
    $result = pg_execute($dbid, "getidfromname", array(
        trim($name)
    ));
    $n = pg_num_rows($result);
    if ($n > 0) {
        $arr = pg_fetch_array($result, ($n - 1) , PGSQL_ASSOC);
        $id = $arr["id"];
    }
    return $id;
}
/**
 * return the logical name of a document from its initial identificator
 *
 * @param string $dbaccess database specification
 * @param string $id initial identificator
 *
 * @return string empty if not found
 */
function getNameFromId($dbaccess, $id)
{
    static $first = true;
    $dbid = getDbid($dbaccess);
    $id = intval($id);
    $name = '';
    //  $result = pg_query($dbid,"select name from docname where id=$id;");
    if ($first) {
        @pg_prepare($dbid, "getNameFromId", 'select name from docread where id=$1');
        $first = false;
    }
    $result = pg_execute($dbid, "getNameFromId", array(
        $id
    ));
    $n = pg_num_rows($result);
    if ($n > 0) {
        $arr = pg_fetch_array($result, ($n - 1) , PGSQL_ASSOC);
        $name = $arr["name"];
    }
    return $name;
}
function setFamidInLayout(Action & $action)
{
    
    global $tFamIdName;
    
    if (!isset($tFamIdName)) getFamIdFromName($action->GetParam("FREEDOM_DB") , "-");
    
    reset($tFamIdName);
    while (list($k, $v) = each($tFamIdName)) {
        $action->lay->set("IDFAM_$k", $v);
    }
}
/**
 * return freedom user document in concordance with what user id
 * @param string $dbaccess database specification
 * @param int $userid what user identificator
 * @return Doc the user document
 */
function getDocFromUserId($dbaccess, $userid)
{
    if ($userid == "") return false;
    include_once ("FDL/Lib.Dir.php");
    $tdoc = array();
    $user = new Account("", $userid);
    if (!$user->isAffected()) return false;
    if ($user->isgroup == "Y") {
        $filter = array(
            "us_whatid = '$userid'"
        );
        $tdoc = getChildDoc($dbaccess, 0, 0, "ALL", $filter, 1, "LIST", getFamIdFromName($dbaccess, "IGROUP"));
    } else {
        $filter = array(
            "us_whatid = '$userid'"
        );
        $tdoc = getChildDoc($dbaccess, 0, 0, "ALL", $filter, 1, "LIST", getFamIdFromName($dbaccess, "IUSER"));
    }
    if (count($tdoc) == 0) return false;
    return $tdoc[0];
}

function getFamTitle(&$tdoc)
{
    $r = $tdoc["name"] . '#title';
    $i = _($r);
    if ($i != $r) return $i;
    return $tdoc['title'];
}
/**
 * verify in database if document is fixed
 * @return bool
 */
function isFixedDoc($dbaccess, $id)
{
    $tdoc = getTDoc($dbaccess, $id, array() , array(
        "locked"
    ));
    if (!$tdoc) return null;
    return ($tdoc["locked"] == - 1);
}

function ComputeVisibility($vis, $fvis, $ffvis = '')
{
    if ($vis == "I") return $vis;
    if ($fvis == "H") return $fvis;
    if (($fvis == "R") && ($vis == "W")) return $fvis;
    if (($fvis == "R") && ($vis == "O")) return "H";
    if (($fvis == "O") && ($vis == "W")) return $fvis;
    if (($fvis == "R") && ($vis == "U")) return $fvis;
    if (($fvis == "S") && (($vis == "W") || ($vis == "O"))) return $fvis;
    if ($fvis == "I") return $fvis;
    if ($fvis == 'U') {
        if ($ffvis && ($vis == 'W' || $vis == 'O')) {
            if ($ffvis == 'S') return 'S';
            if ($ffvis == 'R') return 'R';
        }
    }
    
    return $vis;
}
/**
 * return doc array of latest revision of initid
 *
 * @param string $dbaccess database specification
 * @param string $initid initial identificator of the  document
 * @param array $sqlfilters add sql supply condition
 * @return array values array if found. False if initid not avalaible
 */
function getLatestTDoc($dbaccess, $initid, $sqlfilters = array() , $fromid = false)
{
    global $action;
    
    if (!($initid > 0)) return false;
    $dbid = getDbid($dbaccess);
    $table = "doc";
    if (!$fromid) $fromid = getFromId($dbaccess, $initid);
    if (!$fromid) {
        $err = simpleQuery($dbaccess, sprintf("select fromid from docread where initid=%d and locked != -1", $initid) , $tf, true);
        if (count($tf) > 0) $fromid = $tf[0];
    }
    if ($fromid > 0) $table = "doc$fromid";
    else if ($fromid == - 1) $table = "docfam";
    
    $sqlcond = "";
    if (count($sqlfilters) > 0) $sqlcond = "and (" . implode(") and (", $sqlfilters) . ")";
    
    $userid = $action->user->id;
    if ($userid) {
        $userMember = DocPerm::getMemberOfVector();
        $sql = sprintf("select *,getaperm('%s',profid) as uperm  from only %s where initid=%d and doctype != 'T' and locked != -1 ", $userMember, $table, $initid, $sqlcond);
        $err = simpleQuery($dbaccess, $sql, $result);
        if ($result && (count($result) > 0)) {
            if (count($result) > 1) addWarningMsg(sprintf("document %d : multiple alive revision", $initid));
            
            $arr = $result[0];
            
            return $arr;
        }
    }
    return false;
}
/**
 * return identificators according to latest revision
 * the order is not the same as parameters. The key of result containt initial id
 *
 * @param string $dbaccess database specification
 * @param array $ids array of document identificators
 * @return array identificator relative to latest revision. if one or several documents document not exists the identificator not appear in result so the array count of result can be lesser than parameter
 */
function getLatestDocIds($dbaccess, $ids)
{
    if (!is_array($ids)) return null;
    
    $dbid = getDbid($dbaccess);
    foreach ($ids as $k => $v) $ids[$k] = intval($v);
    $sids = implode($ids, ",");
    $sql = sprintf("SELECT id,initid from docread where initid in (SELECT initid from docread where id in (%s)) and locked != -1;", $sids);
    $result = @pg_query($dbid, $sql);
    if ($result) {
        $arr = pg_fetch_all($result);
        $tlids = array();
        foreach ($arr as $v) $tlids[$v["initid"]] = $v["id"];
        return $tlids;
    }
    return null;
}
/**
 * return latest id of document from its initid
 *
 * @param string $dbaccess database specification
 * @param array $ids array of document identificators
 * @return array identificator relative to latest revision. if one or several documents document not exists the identificator not appear in result so the array count of result can be lesser than parameter
 */
function getLatestDocId($dbaccess, $initid)
{
    if (is_array($initid)) return null;
    // first more quick if alive
    $err = simpleQuery($dbaccess, sprintf("select id from docread where initid='%d' and locked != -1", $initid) , $id, true, true);
    if (($err == '') && ($id > 0)) return $id;
    // second for zombie document
    $err = simpleQuery($dbaccess, sprintf("select id from docread where initid='%d' order by id desc limit 1", $initid) , $id, true, true);
    if ($err == '') return $id;
    return null;
}
/**
 * return doc array of specific revision of document initid
 *
 * @param string $dbaccess database specification
 * @param string $initid initial identificator of the  document
 * @param int $rev revision number
 * @return array values array if found. False if initid not avalaible
 */
function getRevTDoc($dbaccess, $initid, $rev)
{
    global $action;
    
    if (!($initid > 0)) return false;
    $dbid = getDbid($dbaccess);
    $table = "doc";
    $fromid = getFromId($dbaccess, $initid);
    if ($fromid > 0) $table = "doc$fromid";
    else if ($fromid == - 1) $table = "docfam";
    
    $userMember = DocPerm::getMemberOfVector();
    $sql = sprintf("select *,getaperm('%s',profid) as uperm from only %s where initid=%d and revision=%d ", $userMember, $table, $initid, $rev);
    $err = simpleQuery($dbaccess, $sql, $result, false, true);
    if ($result) {
        return $result;
    }
    return false;
}
/**
 * return really latest revision number
 * use only for debug mode
 *
 * @param string $dbaccess database specification
 * @param int $initid initial identificator of the  document
 * @param int $fromid family identicator of document
 * @return int latest revision if found. False if initid not available
 */
function getLatestRevisionNumber($dbaccess, $initid, $fromid = 0)
{
    global $action;
    
    $initid = intval($initid);
    if (!($initid > 0)) return false;
    $dbid = getDbid($dbaccess);
    $table = "doc";
    if (!$fromid) $fromid = getFromId($dbaccess, $initid);
    if ($fromid > 0) $table = "doc$fromid";
    else if ($fromid == - 1) $table = "docfam";
    
    $result = @pg_query($dbid, "SELECT revision from only $table where initid=$initid order by revision desc limit 1;");
    if ($result && (pg_num_rows($result) > 0)) {
        $arr = pg_fetch_array($result, 0, PGSQL_ASSOC);
        return $arr['revision'];
    }
    return false;
}
/**
 * Create default folder for a family with default constraint
 *
 * @param Doc $Doc the family object document
 * @return int id of new folder (false if error)
 */
function createAutoFolder(&$doc)
{
    $dir = createDoc($doc->dbaccess, getFamIdFromName($doc->dbaccess, "DIR"));
    $err = $dir->Add();
    if ($err != "") return false;
    $dir->setValue("BA_TITLE", sprintf(_("root for %s") , $doc->title));
    $dir->setValue("BA_DESC", _("default folder"));
    $dir->setValue("FLD_ALLBUT", "1");
    $dir->setValue("FLD_FAM", $doc->title . "\n" . _("folder") . "\n" . _("search"));
    $dir->setValue("FLD_FAMIDS", $doc->id . "\n" . getFamIdFromName($doc->dbaccess, "DIR") . "\n" . getFamIdFromName($doc->dbaccess, "SEARCH"));
    $dir->setValue("FLD_SUBFAM", "yes\nyes\nyes");
    $dir->Modify();
    $fldid = $dir->id;
    return $fldid;
}
/**
 * get personal profil
 *
 * return the profil named "PERSONAL-PROFIL-<$uid>"
 * the document return is a folder profil that can be use also for "normal" documents
 * @return PDir may be return false if no hability to create the document
 */
function getMyProfil($dbaccess, $create = true)
{
    global $action;
    $uid = $action->user->id;
    $pname = sprintf("PERSONAL-PROFIL-%d", $uid);
    $p = new_doc($dbaccess, $pname);
    if (!$p->isAffected()) {
        if ($create) {
            $p = createDoc($dbaccess, "PDIR");
            $p->name = $pname;
            $p->setValue("ba_title", sprintf(_("Personal profile for %s %s") , $action->user->firstname, $action->user->lastname));
            $p->setValue("prf_desc", sprintf(_("Only %s %s can view and edit") , $action->user->firstname, $action->user->lastname));
            
            $err = $p->Add();
            if ($err == "") {
                $err = $p->setControl(); //activate the profile
                
            }
        } else {
            $p = false;
        }
    }
    return $p;
}
/**
 * @param DomElement $node
 * @return bool
 */
function xt_innerXML(&$node)
{
    if (!$node) return false;
    $document = $node->ownerDocument;
    $nodeAsString = $document->saveXML($node);
    preg_match('!\<.*?\>(.*)\</.*?\>!s', $nodeAsString, $match);
    return $match[1];
}
function cleanhtml($html)
{
    $html = preg_replace("/<\/?span[^>]*>/s", "", $html);
    $html = preg_replace("/<\/?font[^>]*>/s", "", $html);
    $html = preg_replace("/<\/?meta[^>]*>/s", "", $html);
    $html = preg_replace("/<style[^>]*>.*?<\/style>/s", "", $html);
    $html = preg_replace("/<([^>]*) style=\"[^\"]*\"/s", "<\\1", $html);
    $html = preg_replace("/<([^>]*) class=\"[^\"]*\"/s", "<\\1", $html);
    return $html;
}
?>
