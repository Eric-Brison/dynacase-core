<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Detailled search
 */
namespace Dcp\Core;
class DetailSearch extends \Dcp\Family\Search
{
    /**
     * Last suggestion for constraints
     * @var string
     */
    private $last_sug;
    
    var $defaultedit = "FREEDOM:EDITDSEARCH"; #N_("include") N_("equal") N_("equal") _("not equal") N_("is empty") N_("is not empty") N_("one value equal")
    var $defaultview = "FREEDOM:VIEWDSEARCH"; #N_("not include") N_("begin by") N_("not equal") N_("&gt; or equal") N_("&lt; or equal")  N_("content file word") N_("content file expression")
    
    /**
     * @var \DocFam|null
     */
    protected $searchfam = null;
    /**
     * return sql query to search wanted document
     */
    function ComputeQuery($keyword = "", $famid = - 1, $latest = "yes", $sensitive = false, $dirid = - 1, $subfolder = true, $full = false)
    {
        
        if ($dirid > 0) {
            
            if ($subfolder) $cdirid = getRChildDirId($this->dbaccess, $dirid);
            else $cdirid = $dirid;
        } else $cdirid = 0;;
        
        $filters = $this->getSqlGeneralFilters($keyword, $latest, $sensitive);
        $cond = $this->getSqlDetailFilter();
        if ($cond === false) return array(
            false
        );
        $distinct = false;
        $only = '';
        if ($latest == "lastfixed") $distinct = true;
        if ($cond != "") $filters[] = $cond;
        if ($this->getRawValue("se_famonly") == "yes") {
            if (!is_numeric($famid)) $famid = getFamIdFromName($this->dbaccess, $famid);
            $only = "only";
        }
        $query = getSqlSearchDoc($this->dbaccess, $cdirid, $famid, $filters, $distinct, $latest == "yes", $this->getRawValue("se_trash") , false, $level = 2, $join = '', $only);
        
        return $query;
    }
    /**
     * Change queries when use filters objects instead of declarative criteria
     * @see DocSearch#getQuery()
     */
    function getQuery()
    {
        $filtersType = $this->getMultipleRawValues("se_typefilter");
        if ((count($this->getMultipleRawValues("se_filter")) > 0) && (empty($filtersType[0]) || $filtersType[0] != "generated")) {
            $queries = array();
            $filters = $this->getMultipleRawValues("se_filter");
            foreach ($filters as $filter) {
                $q = $this->getSqlXmlFilter($filter);
                if ($q) $queries[] = $q;
            }
            return $queries;
        } else {
            return parent::getQuery();
        }
    }
    
    function postStore()
    {
        $err = parent::postStore();
        try {
            $this->getSqlDetailFilter(true);
        }
        catch(\Exception $e) {
            $err.= $e->getMessage();
        }
        $err.= $this->updateFromXmlFilter();
        $err.= $this->updateXmlFilter();
        if ((!$err) && ($this->isChanged())) $err = $this->modify();
        return $err;
    }
    /**
     * @deprecated use postStore() instead
     * @return string
     */
    public function postModify()
    {
        deprecatedFunction();
        return self::postStore();
    }
    /**
     * update somes attributes from Xml filter
     * @return string error message
     */
    public function updateFromXmlFilter()
    {
        // update only if one filter
        $err = '';
        if (count($this->getMultipleRawValues("se_filter")) == 1) {
            // try to update se_famid
            $filters = $this->getMultipleRawValues("se_filter");
            $filtersType = $this->getMultipleRawValues("se_typefilter");
            $filter = $filters[0];
            $filterType = $filtersType[0];
            if ($filterType != "generated") {
                $famid = '';
                $root = simplexml_load_string($filter);
                $std = $this->simpleXml2StdClass($root);
                if ($std->family) {
                    if (!is_numeric($std->family)) {
                        if (preg_match("/([\w:]*)\s?(strict)?/", trim($std->family) , $reg)) {
                            if (!is_numeric($reg[1])) $reg[1] = getFamIdFromName($this->dbaccess, $reg[1]);
                            if ($reg[2] == "strict") $famid = '-' . $reg[1];
                            else $famid = $reg[1];
                        }
                    } else {
                        $famid = ($std->family);
                    }
                    if ($famid) {
                        $err = $this->setValue("se_famid", abs($famid));
                        $err.= $this->setValue("se_famonly", ($famid > 0) ? "no" : "yes");
                    }
                }
            }
        }
        return $err;
    }
    /**
     * update somes attributes from Xml filter
     * @return string error message
     */
    public function updateXmlFilter()
    {
        // update only if one filter
        $err = '';
        if (count($this->getMultipleRawValues("se_filter")) < 2) {
            // try to update se_famid
            $filters = $this->getMultipleRawValues("se_filter");
            $typeFilters = $this->getMultipleRawValues("se_typefilter");
            if (count($this->getMultipleRawValues("se_filter")) == 1) {
                if ($typeFilters[0] != "generated") return ''; // don't update specified filter created by data API
                
            }
            if ($this->getRawValue("se_famid")) {
                $filterXml = sprintf("<filter><family>%s%s</family>", $this->getRawValue("se_famid") , ($this->getRawValue("se_famonly") == "yes" ? " strict" : ""));
                
                $filterXml.= "</filter>";
                $this->setValue("se_typefilter", "generated"); // only one
                $this->setValue("se_filter", $filterXml);
            }
        }
        return $err;
    }
    /**
     * return a query from on filter object
     * @param string $xml xml filter object
     * @return string the query
     */
    function getSqlXmlFilter($xml)
    {
        $root = simplexml_load_string($xml);
        // trasnform XmlObject to StdClass object
        $std = $this->simpleXml2StdClass($root);
        $famid = $sql = "";
        $this->object2SqlFilter($std, $famid, $sql);
        
        $filters[] = $sql;
        $cdirid = 0;
        $q = getSqlSearchDoc($this->dbaccess, $cdirid, $famid, $filters);
        if (count($q) == 1) {
            $q0 = $q[0]; // need a tempo variable : don't know why
            return ($q0);
        }
        
        return false;
    }
    /**
     * cast SimpleXMLElment to stdClass
     * @param \SimpleXMLElement $xml
     * @return \stdClass return  object or value if it is a leaf
     */
    public function simpleXml2StdClass(\SimpleXMLElement $xml)
    {
        $std = null;
        if ($xml->count() == 0) {
            return current($xml);
        } else {
            foreach ($xml as $k => $se) {
                if (isset($std->$k)) {
                    if (!is_array($std->$k)) $std->$k = array(
                        $std->$k
                    );
                    array_push($std->$k, $this->simpleXml2StdClass($se));
                } else {
                    if ($std === null) $std = new \stdClass();
                    $std->$k = $this->simpleXml2StdClass($se);
                }
            }
        }
        return $std;
    }
    
    function preConsultation()
    {
        $err = parent::preConsultation();
        if ($err !== '') {
            return $err;
        }
        if (count($this->getMultipleRawValues("se_filter")) > 0) {
            if ($this->defaultview == "FREEDOM:VIEWDSEARCH") {
                $type = $this->getMultipleRawValues("se_typefilter");
                if ($type[0] != "generated") {
                    $this->defaultview = "FDL:VIEWBODYCARD";
                }
            }
        }
        return '';
    }
    
    function preEdition()
    {
        if (count($this->getMultipleRawValues("se_filter")) > 0) {
            $type = $this->getMultipleRawValues("se_typefilter");
            if ($type[0] != "generated") {
                $this->defaultedit = "FDL:EDITBODYCARD";
                /**
                 * @var \NormalAttribute $oa
                 */
                $this->getAttribute('se_t_detail', $oa);
                $oa->setVisibility('R');
                $this->getAttribute('se_t_filters', $oa);
                $oa->setVisibility('W');
                
                $this->getAttribute('se_filter', $oa);
                $oa->setVisibility('W');
            }
        }
    }
    /**
     * return error if query filters are not compatibles
     * verify parenthesis
     * @return string error message , empty if no errors
     */
    function getSqlParseError()
    {
        $err = "";
        $tlp = $this->getMultipleRawValues("SE_LEFTP");
        $tlr = $this->getMultipleRawValues("SE_RIGHTP");
        $clp = 0;
        $clr = 0;
        //if (count($tlp) > count($tlr)) $err=sprintf(_("left parenthesis is not closed"));
        if ($err == "") {
            foreach ($tlp as $lp) if ($lp == "yes") $clp++;
            foreach ($tlr as $lr) if ($lr == "yes") $clr++;
            if ($clp != $clr) $err = sprintf(_("parenthesis number mismatch : %d left, %d right") , $clp, $clr);
        }
        return $err;
    }
    /**
     * Check the given string is a valid timestamp (or date)
     *
     * @param $str
     * @return string empty string if valid or error message
     */
    private function isValidTimestamp($str)
    {
        $this->last_sug = '';
        /* Check french format */
        if (preg_match('|^\d\d/\d\d/\d\d\d\d(\s+\d\d:\d\d(:\d\d)?)?$|', $str)) {
            return '';
        }
        /* Check ISO format */
        if (preg_match('@^\d\d\d\d-\d\d-\d\d([\s+|T]\d\d:\d\d(:\d\d)?)?$@', $str)) {
            return '';
        }
        $this->last_sug = $this->getDate(0, '', '', true);
        return _("DetailSearch:malformed timestamp").": $str";
    }
    /**
     * Check the given string is a valid Postgresql's RE
     *
     * @param string $str
     * @return string empty string if valid or error message
     */
    private function isValidPgRegex($str)
    {
        $err = '';
        $this->last_sug = '';
        $point = "dcp:isValidPgRegex";
        $this->savePoint($point);
        $q = sprintf("SELECT regexp_matches('', E'%s')", pg_escape_string($str));
        try {
            simpleQuery($this->dbaccess, $q, $res);
        }
        catch(\Exception $e) {
            $err = $e->getMessage();
        }
        $this->rollbackPoint($point);
        if ($err != '') {
            $err = _("invalid regular expression");
            $this->last_sug = preg_quote($str, '');
        }
        return $err;
    }
    /**
     * Check validity of a condition tuple (attr, op, value)
     *
     * @param string $attr The attribute for the condition
     * @param string $op The operator for the condition
     * @param string $value The value for the condition
     * @return string empty string if valid or error message
     */
    public function isValidCondition($attr, $op, $value)
    {
        /* Accept method name */
        if ($value !== '' && $this->getMethodName($value) !== '') {
            return array(
                'err' => '',
                'sug' => ''
            );
        }
        /* Accept parameter */
        if (substr($value, 0, 1) == '?') {
            return array(
                'err' => '',
                'sug' => ''
            );
        }
        /* Call getSqlCond() in validation mode (validateCond = true) */
        $err = '';
        $this->getSqlCond($attr, $op, $value, '', $err, true);
        if ($err != '') {
            $err = sprintf(_("Invalid condition for attribute '%s' with value '%s': %s") , $attr, $value, $err);
        }
        return array(
            'err' => $err,
            'sug' => isset($this->last_sug) ? $this->last_sug : ''
        );
    }
    /**
     * Check for properly balanced conditions' parenthesis
     */
    private function checkConditionsParens()
    {
        $err = '';
        $lp = $this->getMultipleRawValues('se_leftp');
        $rp = $this->getMultipleRawValues('se_rightp');
        $pc = 0;
        foreach ($lp as $p) {
            if ($p == 'yes') {
                $pc++;
            }
        }
        foreach ($rp as $p) {
            if ($p == 'yes') {
                $pc--;
            }
        }
        if ($pc != 0) {
            $err = _("DetailSearch:unbalanced parenthesis");
        }
        return $err;
    }
    /**
     * Check global coherence of conditions
     */
    public function checkConditions()
    {
        $err = '';
        $err.= $this->checkConditionsParens();
        return array(
            'err' => $err,
            'sug' => ''
        );
    }
    /**
     * return sql part from operator
     * @param string $col a column : property or attribute name
     * @param string $op one of this ::top keys : =, !=, >, ....
     * @param string $val value use for test
     * @param string $val2 second value use for test with >< operator
     * @return string the sql query part
     */
    function getSqlCond($col, $op, $val = "", $val2 = "", &$err = "", $validateCond = false)
    {
        
        if ((!$this->searchfam) || ($this->searchfam->id != $this->getRawValue("se_famid"))) {
            $this->searchfam = \new_Doc($this->dbaccess, $this->getRawValue("se_famid"));
        }
        $col = trim(strtok($col, ' ')); // a col is one word only (prevent injection)
        // because for historic reason revdate is not a date type
        if (($col == "revdate") && ($val != '') && (!is_numeric($val))) {
            $val = stringdatetounixts($val);
        }
        $stateCol = '';
        if ($col == "activity" || $col == "fixstate") {
            $stateCol = $col;
            $col = "state";
        }
        $atype = '';
        $oa = $this->searchfam->getAttribute($col);
        /**
         * @var \NormalAttribute $oa
         */
        if ($oa) {
            $atype = $oa->type;
        } elseif (!empty(\Doc::$infofields[$col])) {
            $atype = \Doc::$infofields[$col]["type"];
        }
        if (($atype == "date" || $atype == "timestamp")) {
            if ($col == 'revdate') {
                if ($op == "=") {
                    $val2 = $val + 85399; // tonight
                    $op = "><";
                }
            } else {
                $hms = '';
                if (($atype == "timestamp")) {
                    $pos = strpos($val, ' ');
                    if ($pos != false) {
                        $hms = substr($val, $pos + 1);
                    }
                }
                
                $cfgdate = getLocaleConfig();
                if ($val) {
                    $val = stringDateToIso($val, $cfgdate['dateFormat']);
                }
                if ($val2) {
                    $val2 = stringDateToIso($val2, $cfgdate['dateFormat']);
                }
                
                if (($atype == "timestamp") && ($op == "=")) {
                    
                    $val = trim($val);
                    if (strlen($val) == 10) {
                        if ($hms == '') {
                            $val2 = $val . " 23:59:59";
                            $val.= " 00:00:00";
                            $op = "><";
                        } elseif (strlen($hms) == 2) {
                            $val2 = $val . ' ' . $hms . ":59:59";
                            $val.= ' ' . $hms . ":00:00";
                            $op = "><";
                        } elseif (strlen($hms) == 5) {
                            $val2 = $val . ' ' . $hms . ":59";
                            $val.= ' ' . $hms . ":00";
                            $op = "><";
                        } else {
                            $val.= ' ' . $hms;
                        }
                    }
                }
                
                if ($validateCond && in_array($op, array(
                    "=",
                    "!=",
                    ">",
                    "<",
                    ">=",
                    "<=",
                    "~y"
                ))) {
                    if (($err = $this->isValidTimestamp($val)) != '') {
                        return '';
                    }
                }
            }
        }
        $cond = '';
        switch ($op) {
            case "is null":
                
                switch ($atype) {
                    case "int":
                    case "uid":
                    case "double":
                    case "money":
                        $cond = sprintf(" (%s is null or %s = 0) ", $col, $col);
                        break;

                    case "date":
                    case "time":
                        $cond = sprintf(" (%s is null) ", $col);
                        break;

                    default:
                        $cond = sprintf(" (%s is null or %s = '') ", $col, $col);
                }
                
                break;

            case "is not null":
                $cond = " " . $col . " " . trim($op) . " ";
                break;

            case "~*":
                if ($validateCond) {
                    if (($err = $this->isValidPgRegex($val)) != '') {
                        return '';
                    }
                }
                if (trim($val) != "") {
                    $cond = " " . $col . " " . trim($op) . " " . $this->_pg_val($val) . " ";
                }
                break;

            case "~^":
                if ($validateCond) {
                    if (($err = $this->isValidPgRegex($val)) != '') {
                        return '';
                    }
                }
                if (trim($val) != "") {
                    $cond = " " . $col . "~* '^" . pg_escape_string(trim($val)) . "' ";
                }
                break;

            case "~y":
                if (!is_array($val)) {
                    $val = $this->rawValueToArray($val);
                }
                foreach ($val as & $v) {
                    $v = self::pgRegexpQuote($v);
                }
                unset($v);
                if (count($val) > 0) {
                    $cond = " " . $col . " ~ E'\\\\y(" . pg_escape_string(implode('|', $val)) . ")\\\\y' ";
                }
                break;

            case "><":
                if ((trim($val) != "") && (trim($val2) != "")) {
                    $cond = sprintf("%s >= %s and %s <= %s", $col, $this->_pg_val($val) , $col, $this->_pg_val($val2));
                }
                break;

            case "=~*":
                switch ($atype) {
                    case "uid":
                        if ($validateCond) {
                            if (($err = $this->isValidPgRegex($val)) != '') {
                                return '';
                            }
                        }
                        $err = simpleQuery(getDbAccessCore() , sprintf("select id from users where firstname ~* '%s' or lastname ~* '%s'", pg_escape_string($val) , pg_escape_string($val)) , $ids, true);
                        if ($err == "") {
                            if (count($ids) == 0) {
                                $cond = "false";
                            } elseif (count($ids) == 1) {
                                $cond = " " . $col . " = " . intval($ids[0]) . " ";
                            } else {
                                $cond = " " . $col . " in (" . implode(',', $ids) . ") ";
                            }
                        }
                        break;

                    case "account":
                    case "docid":
                        if ($validateCond) {
                            if (($err = $this->isValidPgRegex($val)) != '') {
                                return '';
                            }
                        }
                        if ($oa) {
                            $otitle = $oa->getOption("doctitle");
                            if (!$otitle) {
                                $fid = $oa->format;
                                if (!$fid && $oa->type == "account") {
                                    $fid = "IUSER";
                                }
                                if (!$fid) {
                                    $err = sprintf(_("no compatible type with operator %s") , $op);
                                } else {
                                    if (!is_numeric($fid)) {
                                        $fid = getFamidFromName($this->dbaccess, $fid);
                                    }
                                    $err = simpleQuery($this->dbaccess, sprintf("select id from doc%d where title ~* '%s'", $fid, pg_escape_string($val)) , $ids, true);
                                    if ($err == "") {
                                        if (count($ids) == 0) {
                                            $cond = "false";
                                        } elseif (count($ids) == 1) {
                                            $cond = " " . $col . " = '" . intval($ids[0]) . "' ";
                                        } else {
                                            $cond = " " . $col . " in ('" . implode("','", $ids) . "') ";
                                        }
                                    }
                                }
                            } else {
                                if ($otitle == "auto") {
                                    $otitle = $oa->id . "_title";
                                }
                                $oat = $this->searchfam->getAttribute($otitle);
                                if ($oat) {
                                    $cond = " " . $oat->id . " ~* '" . pg_escape_string(trim($val)) . "' ";
                                } else {
                                    $err = sprintf(_("attribute %s : cannot detect title attribute") , $col);
                                }
                            }
                        } elseif ($col == "fromid") {
                            $err = simpleQuery($this->dbaccess, sprintf("select id from docfam where title ~* '%s'", pg_escape_string($val)) , $ids, true);
                            if ($err == "") {
                                if (count($ids) == 0) {
                                    $cond = "false";
                                } elseif (count($ids) == 1) {
                                    $cond = " " . $col . " = " . intval($ids[0]) . " ";
                                } else {
                                    $cond = " " . $col . " in (" . implode(",", $ids) . ") ";
                                }
                            }
                        }
                        break;

                    default:
                        if ($atype) {
                            $err = sprintf(_("attribute %s : %s type is not allowed with %s operator") , $col, $atype, $op);
                        } else {
                            $err = sprintf(_("attribute %s not found [%s]") , $col, $atype);
                        }
                }
                break;

            case "~@":
                if ($validateCond) {
                    if (($err = $this->isValidPgRegex($val)) != '') {
                        return '';
                    }
                }
                if (trim($val) != "") {
                    $cond = " " . $col . '_txt' . " ~ '" . strtolower($val) . "' ";
                }
                break;

            case "=@":
            case "@@":
                if (trim($val) != "") {
                    $tstatickeys = explode(' ', $val);
                    if (count($tstatickeys) > 1) {
                        $keyword = str_replace(" ", "&", trim($val));
                    } else {
                        $keyword = trim($val);
                    }
                    if ($op == "@@") {
                        $cond = " " . $col . '_vec' . " @@ to_tsquery('french','." . pg_escape_string(unaccent(strtolower($keyword))) . "') ";
                    } elseif ($op == "=@") {
                        $cond = sprintf("fulltext @@ to_tsquery('french','%s') ", pg_escape_string(unaccent(strtolower($keyword))));
                    }
                }
                break;

            default:
                
                switch ($atype) {
                    case "enum":
                        $enum = $oa->getEnum();
                        if (strrpos($val, '.') !== false) {
                            $val = substr($val, strrpos($val, '.') + 1);
                        }
                        $tkids = array();;
                        foreach ($enum as $k => $v) {
                            if (in_array($val, explode(".", $k))) {
                                $tkids[] = substr($k, strrpos("." . $k, '.'));
                            }
                        }
                        
                        if ($op == '=') {
                            if ($oa->repeat) {
                                $cond = " " . $col . " ~ E'\\\\y(" . pg_escape_string(implode('|', $tkids)) . ")\\\\y' ";
                            } else {
                                $cond = " $col='" . implode("' or $col='", $tkids) . "'";
                            }
                        } elseif ($op == '!=') {
                            if ($oa->repeat) {
                                $cond1 = " " . $col . " !~ E'\\\\y(" . pg_escape_string(implode('|', $tkids)) . ")\\\\y' ";
                            } else {
                                $cond1 = " $col !='" . implode("' and $col != '", $tkids) . "'";
                            }
                            $cond = " (($cond1) or ($col is null))";
                        } elseif ($op == '!~*') {
                            if ($validateCond) {
                                if (($err = $this->isValidPgRegex($val)) != '') {
                                    return '';
                                }
                            }
                            $cond = sprintf("( (%s is null) or (%s %s %s) )", $col, $col, trim($op) , $this->_pg_val($val));
                        }
                        
                        break;

                    default:
                        if ($atype == "docid") {
                            if (!is_numeric($val)) $val = getIdFromName($this->dbaccess, $val);
                        }
                        $cond1 = " " . $col . " " . trim($op) . $this->_pg_val($val) . " ";
                        if (($op == '!=') || ($op == '!~*')) {
                            if ($validateCond && $op == '!~*') {
                                if (($err = $this->isValidPgRegex($val)) != '') {
                                    return '';
                                }
                            }
                            $cond = "(($cond1) or ($col is null))";
                        } else {
                            $cond = $cond1;
                        }
                    }
            }
            if (!$cond) {
                $cond = "true";
            } elseif ($stateCol == "activity") {
                $cond = sprintf("(%s and locked != -1)", $cond);
            } elseif ($stateCol == "fixstate") {
                $cond = sprintf("(%s and locked = -1)", $cond);
            }
            return $cond;
    }
    
    private static function _pg_val($s)
    {
        if (substr($s, 0, 2) == ':@') {
            return " " . trim(strtok(substr($s, 2) , " \t")) . " ";
        } else return " '" . pg_escape_string(trim($s)) . "' ";
    }
    /**
     * return array of sql filter needed to search wanted document
     */
    function getSqlDetailFilter($validateCond = false)
    {
        $ol = $this->getRawValue("SE_OL");
        $tkey = $this->getMultipleRawValues("SE_KEYS");
        $taid = $this->getMultipleRawValues("SE_ATTRIDS");
        $tf = $this->getMultipleRawValues("SE_FUNCS");
        $tlp = $this->getMultipleRawValues("SE_LEFTP");
        $tlr = $this->getMultipleRawValues("SE_RIGHTP");
        $tols = $this->getMultipleRawValues("SE_OLS");
        
        if ($ol == "") {
            // try in old version
            $ols = $this->getMultipleRawValues("SE_OLS");
            $ol = isset($ols[1]) ? $ols[1] : '';
            if ($ol) {
                $this->setValue("SE_OL", $ol);
                $this->modify();
            }
        }
        if ($ol == "") $ol = "and";
        $cond = "";
        if (!$this->searchfam) {
            $this->searchfam = \new_Doc($this->dbaccess, $this->getRawValue("se_famid"));
        }
        if ((count($taid) > 1) || (count($taid) > 0 && $taid[0] != "")) {
            // special loop for revdate
            foreach ($tkey as $k => $v) {
                // Does it looks like a method name?
                $methodName = $this->getMethodName($v);
                if ($methodName != '') {
                    // it's method call
                    $workdoc = $this->getSearchFamilyDocument();
                    if (!$workdoc) {
                        $workdoc = $this;
                    }
                    if (!$workdoc->isValidSearchMethod($workdoc, $methodName)) {
                        return 'false';
                    }
                    $rv = $workdoc->ApplyMethod($v);
                    $tkey[$k] = $rv;
                }
                if (substr($v, 0, 1) == "?") {
                    // it's a parameter
                    $rv = getHttpVars(substr($v, 1) , "-");
                    if ($rv == "-") return (false);
                    if ($rv === "" || $rv === " ") unset($taid[$k]);
                    else $tkey[$k] = $rv;
                }
                if ($taid[$k] == "revdate") {
                    if (substr_count($tkey[$k], '/') === 2) {
                        list($dd, $mm, $yyyy) = explode("/", $tkey[$k]);
                        if ($yyyy > 0) $tkey[$k] = mktime(0, 0, 0, $mm, $dd, $yyyy);
                    }
                }
            }
            foreach ($taid as $k => $v) {
                $cond1 = $this->getSqlCond($taid[$k], trim($tf[$k]) , $tkey[$k], "", $err, $validateCond);
                if ($validateCond && $err != '') {
                    throw new \Exception($err);
                }
                if ($cond == "") {
                    if (isset($tlp[$k]) && $tlp[$k] == "yes") $cond = '(' . $cond1 . " ";
                    else $cond = $cond1 . " ";
                    if (isset($tlr[$k]) && $tlr[$k] == "yes") $cond.= ')';
                } elseif ($cond1 != "") {
                    if (isset($tols[$k]) && $tols[$k] != "" && $ol === "perso") $ol1 = $tols[$k];
                    else $ol1 = $ol;
                    
                    if ($ol1 === "perso") {
                        // workaround if user set global as condition
                        $ol1 = "and";
                    }
                    if (isset($tlp[$k]) && $tlp[$k] == "yes") $cond.= $ol1 . ' (' . $cond1 . " ";
                    else $cond.= $ol1 . " " . $cond1 . " ";
                    if (isset($tlr[$k]) && $tlr[$k] == "yes") $cond.= ') ';
                }
            }
        }
        if (trim($cond) == "") $cond = "true";
        return $cond;
    }
    /**
     * return true if the search has parameters
     */
    function isParameterizable()
    {
        $tkey = $this->getMultipleRawValues("SE_KEYS");
        if (empty($tkey)) return false;
        if ((count($tkey) > 1) || ($tkey[0] != "")) {
            foreach ($tkey as $k => $v) {
                
                if ($v && $v[0] == '?') {
                    return true;
                    //if (getHttpVars(substr($v,1),"-") == "-") return true;
                    
                }
            }
        }
        return false;
    }
    /**
     * return true if the search need parameters
     */
    function needParameters()
    {
        $tkey = $this->getMultipleRawValues("SE_KEYS");
        if ((count($tkey) > 1) || (!empty($tkey[0]))) {
            
            foreach ($tkey as $k => $v) {
                
                if ($v && $v[0] == '?') {
                    if (getHttpVars(substr($v, 1) , "-") == "-") return true;
                }
            }
        }
        return false;
    }
    /**
     * Add parameters
     */
    function urlWhatEncodeSpec($l)
    {
        $tkey = $this->getMultipleRawValues("SE_KEYS");
        
        if ((count($tkey) > 1) || (isset($tkey[0]) && $tkey[0] != "")) {
            
            foreach ($tkey as $k => $v) {
                
                if ($v && $v[0] == '?') {
                    if (getHttpVars(substr($v, 1) , "-") != "-") {
                        $l.= '&' . substr($v, 1) . "=" . getHttpVars(substr($v, 1));
                    }
                }
            }
        }
        
        return $l;
    }
    /**
     * add parameters in title
     */
    function getCustomTitle()
    {
        $tkey = $this->getMultipleRawValues("SE_KEYS");
        $taid = $this->getMultipleRawValues("SE_ATTRIDS");
        $l = "";
        if ((count($tkey) > 1) || (isset($tkey[0]) && $tkey[0] != "")) {
            $tl = array();
            foreach ($tkey as $k => $v) {
                
                if ($v && $v[0] == '?') {
                    $vh = getHttpVars(substr($v, 1) , "-");
                    if (($vh != "-") && ($vh != "")) {
                        
                        if (is_numeric($vh)) {
                            $fam = $this->getSearchFamilyDocument();
                            if ($fam) {
                                $oa = $fam->getAttribute($taid[$k]);
                                if ($oa && $oa->type == "docid") {
                                    $vh = $this->getTitle($vh);
                                }
                            }
                        }
                        $tl[] = $vh;
                    }
                }
            }
            if (count($tl) > 0) {
                $l = " (" . implode(", ", $tl) . ")";
            }
        }
        return $this->getRawValue("ba_title") . $l;
    }
    /**
     * @templateController default detailed search view
     * @param string $target
     * @param bool $ulink
     * @param bool $abstract
     */
    function viewdsearch($target = "_self", $ulink = true, $abstract = false)
    {
        // Compute value to be inserted in a  layout
        $this->viewattr();
        //-----------------------------------------------
        // display already condition written
        $tkey = $this->getMultipleRawValues("SE_KEYS");
        $taid = $this->getMultipleRawValues("SE_ATTRIDS");
        $tf = $this->getMultipleRawValues("SE_FUNCS");
        $se_ol = $this->getRawValue(\Dcp\AttributeIdentifiers\Dsearch::se_ol);
        $se_ols = $this->getMultipleRawValues(\Dcp\AttributeIdentifiers\Dsearch::se_ols);
        $se_leftp = $this->getMultipleRawValues(\Dcp\AttributeIdentifiers\Dsearch::se_leftp);
        $se_rightp = $this->getMultipleRawValues(\Dcp\AttributeIdentifiers\Dsearch::se_rightp);
        if ((count($taid) > 1) || (!empty($taid[0]))) {
            
            $fdoc = \new_Doc($this->dbaccess, $this->getRawValue("SE_FAMID", 1));
            $zpi = $fdoc->GetNormalAttributes();
            $zpi["state"] = new \BasicAttribute("state", $this->fromid, _("step"));
            $zpi["fixstate"] = new \BasicAttribute("fixstate", $this->fromid, _("state"));
            $zpi["activity"] = new \BasicAttribute("activity", $this->fromid, _("activity"));
            $zpi["title"] = new \BasicAttribute("title", $this->fromid, _("doctitle"));
            $zpi["revdate"] = new \BasicAttribute("revdate", $this->fromid, _("revdate"));
            $zpi["cdate"] = new \BasicAttribute("cdate", $this->fromid, _("cdate") , 'W', '', '', 'date');
            $zpi["revision"] = new \BasicAttribute("cdate", $this->fromid, _("revision"));
            $zpi["owner"] = new \BasicAttribute("owner", $this->fromid, _("owner"));
            $zpi["locked"] = new \BasicAttribute("owner", $this->fromid, _("locked"));
            $zpi["allocated"] = new \BasicAttribute("owner", $this->fromid, _("allocated"));
            $zpi["svalues"] = new \BasicAttribute("svalues", $this->fromid, _("any values"));
            $tcond = array();
            foreach ($taid as $k => $v) {
                if (isset($zpi[$v])) {
                    $label = $zpi[$v]->getLabel();
                    if ($label == "") $label = $v;
                    if ($v == "activity") {
                        $fdoc->state = $tkey[$k];
                        $displayValue = $fdoc->getStatelabel();
                    } else {
                        $displayValue = ($tkey[$k] != "") ? _($tkey[$k]) : $tkey[$k];
                    }
                    $type = $zpi[$taid[$k]]->type;
                    if ($zpi[$taid[$k]]->isMultiple() || $zpi[$taid[$k]]->inArray()) {
                        if ($type === "docid") $type = "docid[]";
                        else if ($type === "account") $type = "account[]";
                    }
                    $elmts = array();
                    if ($se_ol == 'perso') {
                        if (count($tcond) > 0) {
                            /* Do not display operator on first line */
                            if (isset($se_ols[$k]) && $se_ols[$k] != '') {
                                $elmts[] = _($se_ols[$k]);
                            }
                        }
                        if (isset($se_leftp[$k]) && $se_leftp[$k] == 'yes') {
                            $elmts[] = '(';
                        }
                        $elmts[] = sprintf("%s %s %s", mb_ucfirst($label) , $this->getOperatorLabel($tf[$k], $type) , $displayValue);
                        if (isset($se_rightp[$k]) && $se_rightp[$k] == 'yes') {
                            $elmts[] = ')';
                        }
                    } else {
                        if (count($tcond) > 0) {
                            /* Do not display operator on first line */
                            $elmts[] = _($se_ol);
                        }
                        $elmts[] = sprintf("%s %s %s", mb_ucfirst($label) , $this->getOperatorLabel($tf[$k], $type) , $displayValue);
                    }
                    $tcond[]["condition"] = join(' ', $elmts);
                    if (isset($tkey[$k][0]) && $tkey[$k][0] == '?') {
                        $tparm[substr($tkey[$k], 1) ] = $taid[$k];
                    }
                } else {
                    addWarningMsg(sprintf("property %s not know", $v));
                }
            }
            $this->lay->SetBlockData("COND", $tcond);
        }
        $this->lay->Set("ddetail", "");
    }
    /**
     * return true if the sqlselect is writted by hand
     * @return bool
     */
    function isStaticSql()
    {
        return ($this->getRawValue("se_static") != "");
    }
    /**
     * return family use for search
     * @return \Doc
     */
    private function getSearchFamilyDocument()
    {
        static $fam = null;
        if (!$fam) $fam = createTmpDoc($this->dbaccess, $this->getRawValue("SE_FAMID", 1));
        return $fam;
    }
    /**
     * @templateController
     * @param string $target
     * @param bool $ulink
     * @param bool $abstract
     */
    function paramdsearch($target = "_self", $ulink = true, $abstract = false)
    {
        // Compute value to be inserted in a  layout
        $this->viewattr();
        $tparm = $tcond = array();
        //-----------------------------------------------
        // display already condition written
        $tkey = $this->getMultipleRawValues("SE_KEYS");
        $taid = $this->getMultipleRawValues("SE_ATTRIDS");
        $tf = $this->getMultipleRawValues("SE_FUNCS");
        $zpi = $toperator = array();
        if ((count($taid) > 1) || ($taid[0] != "")) {
            
            $fdoc = \new_Doc($this->dbaccess, $this->getRawValue("SE_FAMID", 1));
            $zpi = $fdoc->GetNormalAttributes();
            $zpi["state"] = new \BasicAttribute("state", $this->fromid, _("step"));
            $zpi["fixstate"] = new \BasicAttribute("state", $this->fromid, _("fixstate"));
            $zpi["activity"] = new \BasicAttribute("state", $this->fromid, _("activity"));
            $zpi["title"] = new \BasicAttribute("title", $this->fromid, _("doctitle"));
            $zpi["revdate"] = new \BasicAttribute("revdate", $this->fromid, _("revdate"));
            $zpi["cdate"] = new \BasicAttribute("cdate", $this->fromid, _("cdate") , 'W', '', '', 'date');
            $zpi["revision"] = new \BasicAttribute("cdate", $this->fromid, _("revision"));
            $zpi["owner"] = new \BasicAttribute("owner", $this->fromid, _("owner"));
            $zpi["locked"] = new \BasicAttribute("owner", $this->fromid, _("locked"));
            $zpi["allocated"] = new \BasicAttribute("owner", $this->fromid, _("allocated"));
            $zpi["svalues"] = new \BasicAttribute("svalues", $this->fromid, _("any values"));
            
            foreach ($taid as $k => $v) {
                if ($tkey[$k][0] == '?') {
                    $tparm[substr($tkey[$k], 1) ] = $taid[$k];
                    $toperator[substr($tkey[$k], 1) ] = $tf[$k];
                }
            }
            $this->lay->SetBlockData("COND", $tcond);
        }
        
        $this->lay->Set("ddetail", "");
        if (count($tparm) > 0) {
            include_once ("FDL/editutil.php");
            global $action;
            editmode($action);
            
            $doc = $this->getSearchFamilyDocument();
            $inputset = array();
            $ki = 0; // index numeric
            $tinputs = $ttransfert = array();
            foreach ($tparm as $k => $v) {
                if (isset($inputset[$v])) {
                    // need clone when use several times the same attribute
                    $vz = $v . "Z" . $ki;
                    $zpi[$vz] = $zpi[$v];
                    $zpi[$vz]->id = $vz;
                    $v = $vz;
                }
                if ($zpi[$v]->fieldSet->type == 'array') $zpi[$v]->fieldSet->type = 'frame'; // no use array configuration for help input
                $ki++;
                $inputset[$v] = true;
                
                $ttransfert[] = array(
                    "idi" => $v,
                    "idp" => $k,
                    "value" => getHttpVars($k)
                );
                $tinputs[$k]["label"] = $zpi[$v]->getLabel();
                $type = $zpi[$v]->type;
                if ($zpi[$v]->isMultiple() || $zpi[$v]->inArray()) {
                    if ($type === "docid") $type = "docid[]";
                    else if ($type === "account") $type = "account[]";
                }
                $tinputs[$k]["operator"] = $this->getOperatorLabel($toperator[$k], $type);
                if (($toperator[$k] == "=~*" || $toperator[$k] == "~*") && $zpi[$v]->type == "docid") $zpi[$v]->type = "text"; // present like a search when operator is text search
                if ($zpi[$v]->visibility == 'R') $zpi[$v]->mvisibility = 'W';
                if ($zpi[$v]->visibility == 'S') $zpi[$v]->mvisibility = 'W';
                if (isset($zpi[$v]->id)) {
                    $zpi[$v]->isAlone = true;
                    $tinputs[$k]["inputs"] = getHtmlInput($doc, $zpi[$v], getHttpVars($k));
                } else {
                    $aotxt = new \BasicAttribute($v, $doc->id, "eou");
                    if ($v == "revdate") $aotxt->type = "date";
                    /** @noinspection PhpParamsInspection */
                    $tinputs[$k]["inputs"] = getHtmlInput($doc, $aotxt, getHttpVars($k));
                }
            }
            $this->lay->setBlockData("PARAM", $tinputs);
            $this->lay->setBlockData("TRANSFERT", $ttransfert);
            $this->lay->setBlockData("PINPUTS", $ttransfert);
            $this->lay->eSet("ddetail", "none");
            $this->lay->eset("stext", _("send search"));
            $this->lay->eset("saction", getHttpVars("saction", "FREEDOM_VIEW"));
            $this->lay->eset("sapp", getHttpVars("sapp", "FREEDOM"));
            $this->lay->eset("sid", getHttpVars("sid", "dirid"));
            $this->lay->eset("starget", getHttpVars("starget", ""));
            $this->lay->set("icon", $this->getIcon());
        }
    }
    // -----------------------------------
    
    /**
     *
     * @templateController default detailed search edit view
     */
    function editdsearch()
    {
        /**
         * @var \Action $action
         */
        global $action;
        $classid = GetHttpVars("sfamid", 0);
        $famid = $this->getRawValue("SE_FAMID", 0);
        $onlysubfam = GetHttpVars("onlysubfam"); // restricy to sub fam of
        $dirid = GetHttpVars("dirid");
        $alsosub = getHttpVars("alsosub") == "Y";
        $this->lay->set("ACTION", urlencode($action->name));
        $tclassdoc = array();
        $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/lib/jquery/jquery.js");
        $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FDL/Layout/edittable.js");
        $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FREEDOM/Layout/editdsearch.js");
        
        if ($dirid > 0) {
            /**
             * @var \Dir $dir
             */
            $dir = \new_Doc($this->dbaccess, $dirid);
            if (method_exists($dir, "isAuthorized")) {
                if ($dir->isAuthorized($classid)) {
                    // verify if classid is possible
                    if ($dir->hasNoRestriction()) {
                        $tclassdoc = GetClassesDoc($this->dbaccess, $action->user->id, $classid, "TABLE");
                        $tclassdoc[] = array(
                            "id" => 0,
                            "title" => _("any families") ,
                            "usefor" => ''
                        );
                    } else {
                        $tclassdoc = $dir->getAuthorizedFamilies();
                        $this->lay->set("restrict", true);
                    }
                } else {
                    $tclassdoc = $dir->getAuthorizedFamilies();
                    $first = current($tclassdoc);
                    $famid1 = ($first["id"]);
                    $this->lay->set("restrict", true);
                    $tfamids = array_keys($tclassdoc);
                    if (!in_array($famid, $tfamids)) $famid = $famid1;
                }
            } else {
                $tclassdoc = GetClassesDoc($this->dbaccess, $action->user->id, $classid, "TABLE");
            }
        } else {
            if ($onlysubfam) {
                if (!is_numeric($onlysubfam)) $onlysubfam = getFamIdFromName($this->dbaccess, $onlysubfam);
                $cdoc = \new_Doc($this->dbaccess, $onlysubfam);
                $tsub = $cdoc->GetChildFam($cdoc->id, false);
                $tclassdoc[$classid] = array(
                    "id" => $cdoc->id,
                    "title" => $cdoc->title,
                    "usefor" => ''
                );
                if ($alsosub) {
                    $tclassdoc = array_merge($tclassdoc, $tsub);
                }
                if (!$this->id) $this->setValue("se_famonly", $alsosub ? "no" : "yes");
                $first = current($tclassdoc);
                if ($classid == "") $classid = $first["id"];
            } else {
                $tclassdoc = GetClassesDoc($this->dbaccess, $action->user->id, $classid, "TABLE");
                $tclassdoc[] = array(
                    "id" => 0,
                    "title" => _("any families") ,
                    "usefor" => ''
                );
            }
        }
        $sLabelArray = array();
        foreach ($this->top as $k => $v) {
            $sLabel = array();
            if (isset($v["slabel"]) && is_array($v["slabel"])) {
                foreach ($v["slabel"] as $key => $value) {
                    $sLabel[$key] = _($value);
                }
            }
            $sLabelArray[$k] = array(
                "label" => _($v["label"]) ,
                "slabel" => $sLabel
            );
        }
        $this->lay->set("topInformation", json_encode($sLabelArray));
        $this->lay->set("onlysubfam", urlencode($onlysubfam));
        $selfam = false;
        $selectclass = array();
        foreach ($tclassdoc as $k => $tdoc) {
            $selectclass[$k]["idcdoc"] = $tdoc["id"];
            $selectclass[$k]["classname"] = $tdoc["title"];
            $selectclass[$k]["system_fam"] = (substr($tdoc["usefor"], 0, 1) == 'S') ? true : false;
            if (abs($tdoc["id"]) == abs($famid)) {
                $selfam = true;
                $selectclass[$k]["selected"] = 'selected="selected"';
                if ($famid < 0) $this->lay->set("selfam", $tdoc["title"] . " " . _("(only)"));
                else $this->lay->set("selfam", $tdoc["title"]);
            } else $selectclass[$k]["selected"] = "";
        }
        if (!$selfam) {
            $famid = abs($this->getRawValue("se_famid"));
            if ($this->id && $famid) {
                $selectclass[] = array(
                    "idcdoc" => $famid,
                    "classname" => $this->getTitle($famid) ,
                    "selected" => "selected"
                );
            } else {
                reset($tclassdoc);
                $first = current($tclassdoc);
                $famid = $first["id"];
            }
        }
        $this->lay->Set("dirid", urlencode($dirid));
        $this->lay->Set("classid", $this->fromid);
        $this->lay->SetBlockData("SELECTCLASS", $selectclass);
        $this->lay->set("has_permission_fdl_system", $action->parent->hasPermission('FDL', 'SYSTEM'));
        $this->lay->set("se_sysfam", ($this->getRawValue('se_sysfam') == 'yes') ? true : false);
        $this->setFamidInLayout();
        // display attributes
        $tattr = array();
        $internals = array(
            "title" => _("doctitle") ,
            "revdate" => _("revdate") ,
            "cdate" => _("cdate") ,
            "revision" => _("revision") ,
            "owner" => _("id owner") ,
            "locked" => _("id locked") ,
            "allocated" => _("id allocated") ,
            "svalues" => _("any values")
        );
        
        $tattr["_prop"] = array(
            "attrid" => "_prop",
            "attrtype" => "set",
            "attrdisabled" => "disabled",
            "attrname" => _("DocProperties") ,
            "ismultiple" => 'no'
        );
        
        foreach ($internals as $k => $v) {
            if ($k == "revdate") $type = "date";
            else if ($k == "owner") $type = "uid";
            else if ($k == "locked") $type = "uid";
            else if ($k == "allocated") $type = "uid";
            else if ($k == "cdate") $type = "date";
            else if ($k == "revision") $type = "int";
            else if ($k == "state") $type = "docid";
            else $type = "text";
            
            $tattr[$k] = array(
                "attrid" => $k,
                "ismultiple" => 'no',
                "attrtype" => $type,
                "attrdisabled" => "",
                "attrname" => $v
            );
        }
        
        $fdoc = \new_Doc($this->dbaccess, abs($famid));
        $tmpDoc = \createTmpDoc($this->dbaccess, abs($famid));
        $zpi = $fdoc->GetNormalAttributes();
        
        foreach ($zpi as $k => $v) {
            if ($v->type == "array" || $v->type == "password") {
                continue;
            }
            $opt_searchcriteria = $v->getOption("searchcriteria", "");
            if ($opt_searchcriteria == "hidden" || $opt_searchcriteria == "restricted") {
                continue;
            }
            
            $type = $v->type;
            if ($v->getOption("doctitle") && $v->isMultiple()) $type = "docidtitle[]";
            $tset = $this->editGetSetAttribute($v->fieldSet);
            if (count($tset) > 0) $tattr = array_merge($tattr, array_reverse($tset));
            
            $tattr[$v->id] = array(
                "attrid" => $v->id,
                "ismultiple" => ($v->isMultiple()) ? 'yes' : 'no',
                "attrtype" => $type,
                "attrdisabled" => "",
                "attrname" => $v->getLabel()
            );
        }
        if ($action->getParam("ISIE6")) {
            // cannot disable select option with IE6
            foreach ($tattr as $ka => $va) {
                if (!empty($va["attrdisabled"])) unset($tattr[$ka]);
            }
        }
        $this->lay->SetBlockData("ATTR", $tattr);
        $tfunc = array();
        foreach ($this->top as $k => $v) {
            $display = '';
            if (isset($v["type"])) {
                $ctype = implode(",", $v["type"]);
                if (!in_array('text', $v["type"])) $display = 'none'; // first is title
                
            } else $ctype = "";
            
            $tfunc[] = array(
                "funcid" => $k,
                "functype" => $ctype,
                "funcdisplay" => $display,
                "funcname" => _($v["label"])
            );
        }
        $this->lay->SetBlockData("FUNC", $tfunc);
        foreach ($tfunc as $k => $v) {
            if (($v["functype"] != "") && (strpos($v["functype"], "enum") === false)) unset($tfunc[$k]);
        }
        $this->lay->SetBlockData("FUNCSTATE", $tfunc);
        $this->lay->Set("icon", $fdoc->getIcon());
        
        if ($this->getRawValue("SE_LATEST") == "no") $this->lay->Set("select_all", "selected");
        else $this->lay->Set("select_all", "");
        $states = array();
        //-----------------------------------------------
        // display state
        $wdoc = null;
        if ($fdoc->wid > 0) {
            $wdoc = \new_Doc($this->dbaccess, $fdoc->wid);
            /**
             * @var \Wdoc $wdoc
             */
            $states = $wdoc->getStates();
            
            $tstates = array();
            foreach ($states as $k => $v) {
                $tstates[] = array(
                    "step" => "state",
                    "stateid" => $v,
                    "statename" => _($v)
                );
                $activity = $wdoc->getActivity($v);
                
                $tstates[] = array(
                    "step" => "activity",
                    "stateid" => $v,
                    "statename" => ($activity) ? _($activity) : _($v)
                );
            }
            $this->lay->SetBlockData("STATE", $tstates);
            $this->lay->Set("dstate", "inline");
        } else {
            $this->lay->Set("dstate", "none");
        }
        //-----------------------------------------------
        // display already condition written
        $tol = $this->getMultipleRawValues("SE_OLS");
        $tkey = $this->getMultipleRawValues("SE_KEYS");
        $taid = $this->getMultipleRawValues("SE_ATTRIDS");
        $tf = $this->getMultipleRawValues("SE_FUNCS");
        $tlp = $this->getMultipleRawValues("SE_LEFTP");
        $trp = $this->getMultipleRawValues("SE_RIGHTP");
        
        $cond = "";
        $tcond = array();
        if ((count($taid) > 1) || ($taid && $taid[0] != "")) {
            foreach ($taid as $k => $keyId) {
                $docid_aid = 0;
                $v = $tkey[$k];
                $oa = $fdoc->getAttribute($keyId);
                $tcond[$k] = array(
                    "OLCOND" => "olcond$k",
                    "ATTRCOND" => "attrcond$k",
                    "FUNCCOND" => "funccond$k",
                    "ISENUM" => (($keyId == "state") || ($keyId == "fixstate") || ($keyId == "activity") || ($oa && $oa->type == "enum")) ,
                    "SSTATE" => "sstate$k",
                    "ols_and_selected" => ($tol[$k] == "and") ? "selected" : "",
                    "ols_or_selected" => ($tol[$k] == "or") ? "selected" : "",
                    "leftp_none_selected" => ($tlp[$k] != "yes") ? "selected" : "",
                    "leftp_open_selected" => ($tlp[$k] == "yes") ? "selected" : "",
                    "rightp_none_selected" => ($trp[$k] != "yes") ? "selected" : "",
                    "rightp_open_selected" => ($trp[$k] == "yes") ? "selected" : "",
                    "key" => $v,
                    "rowidx" => $k
                );
                $tattrSelect = array();
                if ($keyId == "state" || ($keyId == "fixstate") || ($keyId == "activity")) {
                    $tstates = array();
                    $stateselected = false;
                    foreach ($states as $ks => $vs) {
                        if ($keyId != "activity") {
                            $tstates[] = array(
                                "sstateid" => $vs,
                                "sstep" => "state",
                                "sstate_selected" => ($vs == $v) ? "selected" : "",
                                "sstatename" => _($vs)
                            );
                        } else {
                            $activity = $wdoc->getActivity($vs);
                            $tstates[] = array(
                                "sstateid" => $vs,
                                "sstep" => "activity",
                                "sstate_selected" => ($vs == $v) ? "selected" : "",
                                "sstatename" => ($activity) ? _($activity) : _($vs)
                            );
                        }
                        if ($vs == $v) $stateselected = true;
                    }
                    if (!$stateselected) $tcond[$k]["ISENUM"] = false;
                    $this->lay->SetBlockData("sstate$k", $tstates);
                    
                    $tattrSelect[] = array(
                        "attrid" => $keyId,
                        "ismultiple" => 'no',
                        "attrtype" => "enum",
                        "attrdisabled" => '',
                        "attrselected" => "selected",
                        "attrname" => _($keyId)
                    );
                } else {
                    if ($oa && $oa->type == "enum") {
                        /**
                         * @var \NormalAttribute $oa
                         */
                        $te = $oa->getEnum();
                        $tstates = array();
                        $enumselected = false;
                        foreach ($te as $ks => $vs) {
                            $tstates[] = array(
                                "sstateid" => $ks,
                                "sstate_selected" => ($ks == $v) ? "selected" : "",
                                "sstatename" => $vs
                            );
                            if ($ks == $v) $enumselected = true;
                        }
                        $this->lay->SetBlockData("sstate$k", $tstates);
                        if (!$enumselected) $tcond[$k]["ISENUM"] = false;
                    }
                    
                    $tattrSelect = $tattr;
                    foreach ($tattrSelect as $ki => $vi) {
                        $tattrSelect[$ki]["attrselected"] = "";
                    }
                    
                    foreach ($internals as $ki => $vi) {
                        if (isset($tattrSelect[$ki])) {
                            $tattrSelect[$ki]["attrselected"] = ($keyId == $ki) ? "selected" : "";
                        }
                    }
                    
                    $this->editGetSetAttribute(null, true);
                    foreach ($zpi as $ki => $vi) {
                        if (isset($tattrSelect[$ki])) {
                            $tattrSelect[$vi->id]["attrselected"] = ($keyId == $vi->id) ? "selected" : "";
                        }
                    }
                }
                $this->lay->SetBlockData("attrcond$k", $tattrSelect);
                
                $tfunc = array();
                foreach ($this->top as $ki => $vi) {
                    $oa = $fdoc->getAttribute($keyId);
                    if ($oa) $type = $oa->type;
                    else $type = '';
                    if ($type == "") {
                        if ($keyId == "title") $type = "text";
                        elseif ($keyId == "cdate") $type = "date";
                        elseif ($keyId == "fixstate") $type = "enum";
                        elseif ($keyId == "activity") $type = "enum";
                        elseif ($keyId == "revision") $type = "int";
                        elseif ($keyId == "allocated") $type = "uid";
                        elseif ($keyId == "locked") $type = "uid";
                        elseif ($keyId == "revdate") $type = "date";
                        elseif ($keyId == "owner") $type = "uid";
                        elseif ($keyId == "svalues") $type = "text";
                        elseif ($keyId == "state") $type = "enum";
                    } else {
                        if (($oa->isMultiple() || $oa->inArray()) && $type === "docid") $type = "docid[]";
                        else if (($oa->isMultiple() || $oa->inArray()) && $type === "account") $type = "account[]";
                        else if ($oa->inArray() && ($oa->type != 'file')) $type = "array";
                    }
                    $display = '';
                    $ctype = '';
                    if (isset($vi["type"])) {
                        if (!in_array($type, $vi["type"])) $display = 'none';
                        $ctype = implode(",", $vi["type"]);
                    }
                    if ($tf[$k] == $ki && $display == '' && ((($type == 'docid' || $type == 'account') && ($ki == '=' || $ki == '!=')) || (($type == 'docid[]' || $type == 'account[]') && $ki == '~y'))) {
                        $docid_aid = $keyId;
                    }
                    $tfunc[] = array(
                        "func_id" => $ki,
                        "func_selected" => ($tf[$k] == $ki) ? "selected" : "",
                        "func_display" => $display,
                        "func_type" => $ctype,
                        "func_name" => $this->getOperatorLabel($ki, $type)
                    );
                }
                
                $this->lay->SetBlockData("funccond$k", $tfunc);
                
                $tols = array();
                foreach ($this->tol as $ki => $vi) {
                    $tols[] = array(
                        "ol_id" => $ki,
                        "ol_selected" => ($tol[$k] == $ki) ? "selected" : "",
                        "ol_name" => _($vi)
                    );
                }
                $this->lay->SetBlockData("olcond$k", $tols);
                
                if ((is_numeric($v) || empty($v)) && isset($docid_aid) && !empty($docid_aid)) {
                    $tcond[$k]["ISENUM"] = false;
                    $tcond[$k]["ISDOCID"] = true;
                    $tcond[$k]["ISDOCIDMULTIPLE"] = $oa->isMultiple();
                    $tcond[$k]["DOCID_AID"] = $docid_aid;
                    $tcond[$k]["DOCID_AIDINDEX"] = $docid_aid . $k;
                    $tcond[$k]["DOCID_TITLE"] = $this->getTitle($v);
                    $tcond[$k]["FAMID"] = abs($famid);
                    $tcond[$k]["ISSEARCHMETHOD"] = false;
                } else {
                    $tcond[$k]["ISDOCID"] = false;
                    $tcond[$k]["ISDOCIDMULTIPLE"] = false;
                    $tcond[$k]["DOCID_AID"] = 0;
                    $tcond[$k]["DOCID_AIDINDEX"] = 0;
                    $tcond[$k]["DOCID_TITLE"] = '';
                    $tcond[$k]["FAMID"] = abs($famid);
                    $isSearchMethod = false;
                    if ($oa) {
                        $attrType = $oa->type;
                        if ($oa->format != '') {
                            // Recompose full attr spec: <attrType>("<format>")
                            $attrType = sprintf('%s("%s")', $attrType, $oa->format);
                        }
                        $methods = $tmpDoc->getSearchMethods($oa->id, $attrType);
                        
                        foreach ($methods as $method) {
                            if ($method['method'] == $v) {
                                $isSearchMethod = true;
                                break;
                            }
                        }
                    }
                    $tcond[$k]["ISSEARCHMETHOD"] = $isSearchMethod;
                }
            }
        }
        if (count($tcond) > 0) $this->lay->SetBlockData("CONDITIONS", $tcond);
        // Add select for enum attributes
        $tenums = array();
        foreach ($zpi as $k => $v) {
            if (($v->type == "enum") || ($v->type == "enumlist")) {
                $tenums[] = array(
                    "SELENUM" => "ENUM$k",
                    "attrid" => $v->id
                );
                $tenum = $v->getEnum();
                $te = array();
                foreach ($tenum as $ke => $ve) {
                    if ($ke === ' ' || $ke === '') {
                        continue;
                    }
                    $te[] = array(
                        "enumkey" => $ke,
                        "enumlabel" => $ve
                    );
                }
                $this->lay->setBlockData("ENUM$k", $te);
            }
        }
        
        $this->lay->setBlockData("ENUMS", $tenums);
        
        $this->lay->Set("id", $this->id);
        $this->editattr();
    }
    /**
     * @param \BasicAttribute $fs
     * @param bool $reset
     * @return array
     */
    private function editGetSetAttribute($fs, $reset = false)
    {
        static $setAttribute = array();
        $level = 0;
        $tset = array();
        if ($reset) $setAttribute = array();
        while ($fs && $fs->id != \Adoc::HIDDENFIELD) {
            if (!in_array($fs->id, $setAttribute)) {
                $tset[$fs->id] = array(
                    "attrid" => $fs->id,
                    "attrtype" => "set",
                    "attrdisabled" => "disabled",
                    "attrselected" => "",
                    "attrname" => $fs->getLabel()
                );
                $setAttribute[] = $fs->id;
                $level++;
                $fs = $fs->fieldSet;
            } else {
                break;
            }
        }
        return $tset;
    }
    
    private function getMethodName($methodStr)
    {
        $parseMethod = new \parseFamilyMethod();
        $parseMethod->parse($methodStr);
        $err = $parseMethod->getError();
        if ($err) {
            return '';
        }
        return $parseMethod->methodName;
    }
    
    public static function pgRegexpQuote($str)
    {
        /*
         * Escape Postgresql's regexp special chars into theirs UTF16 form "\u00xx"
        */
        return preg_replace_callback('/[.|*+?{}\[\]()\\\\^$]/u', function ($m)
        {
            return sprintf('\\u00%x', ord($m[0]));
        }
        , $str);
    }
}
