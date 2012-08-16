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
?>