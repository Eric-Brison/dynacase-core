<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: freedom_convert.php,v 1.4 2005/08/08 16:00:54 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage
 */
/**
 */
// refreah for a classname
// use this only if you have changed title attributes
include_once ("FDL/Class.Doc.php");

$famId = GetHttpVars("tofamid", ""); // familly filter
$docid = GetHttpVars("docid", ""); // document

if (($docid == "") && ($famId == 0)) {
    print "arg class needed :usage --tofamid=<family id> --docid=<doc id to be converted>\n";
} else {
    
    $appl = new Application();
    $appl->Set("FDL", $core);
    
    $dbaccess = $appl->GetParam("FREEDOM_DB");
    if ($dbaccess == "") {
        print "Database not found : param FREEDOM_DB";
        exit;
    }
    
    $doc = new_Doc($dbaccess, $docid);
    if ($doc->isAffected()) {
        if ($doc->convert($famId)) print $doc->title . " converted";
        else print $doc->title . " NOT converted";
    } else {
        print "document  $docid not found";
    }
}
?>