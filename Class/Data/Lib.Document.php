<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Document Library
 *
 * @author Anakeen
 * @version $Id:  $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */

function getDavUrl($docid, $vid)
{
    if (include_once ("DAV/getsessionid.php")) {
        return dav_getdavurl($docid, $vid);
    }
}
/** 
 * return document list for relation attribute
 * @return array
 */
function searchByTitle($famid, $key)
{
    include_once ("FDL/Class.SearchDoc.php");
    $doc = new Fdl_Collection();
    $s = new SearchDoc($doc->dbaccess, $famid);
    $s->addFilter(sprintf("title ~* '%s'", pg_escape_string($key)));
    $s->slice = 100;
    $res = $s->search();
    $out = array();
    foreach ($res as $v) {
        $out[] = array(
            "display" => $v["title"],
            "id" => $v["id"],
            "title" => $v["title"]
        );
    }
    return $out;
}

function getSearchCriteria(stdClass &$out)
{
    $s = new DocSearch();
    $operators = $s->top;
    $out->error = '';
    $out->operators = array();
    $alltype = array(
        'integer',
        'text',
        'longtext',
        'htmltext',
        'docid',
        'double',
        "ifile",
        "array",
        "file",
        "image",
        "enum",
        "color",
        "date",
        "time",
        "timestamp",
        "money"
    );
    foreach ($operators as $k => $v) {
        if (empty($v["type"]) || (!is_array($v["type"]))) $v["type"] = $alltype;
        foreach ($v["type"] as $type) {
            $label = (!empty($v["label"])) ? _($v["label"]) : '';
            $dynlabel = (!empty($v["dynlabel"])) ? _($v["dynlabel"]) : '';
            if (!empty($v["slabel"])) {
                foreach ($v["slabel"] as $kl => $vl) {
                    if ($kl == $type) $label = _($vl);
                }
            }
            if (!empty($v["sdynlabel"])) {
                foreach ($v["sdynlabel"] as $kl => $vl) {
                    if ($kl == $type) $dynlabel = _($vl);
                }
            }
            $out->operators[$type][] = array(
                "operator" => $k,
                "operand" => $v["operand"],
                "label" => $label,
                "tplLabel" => $dynlabel
            );
        }
    }
}
