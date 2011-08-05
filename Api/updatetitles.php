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
 * @version $Id: updatetitles.php,v 1.4 2003/08/18 15:47:04 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage
 */
/**
 */
// update title for a classname
// use this only if you have changed title attributes
include_once ("FDL/Class.Doc.php");

$className = GetHttpVars("class", "-"); // classname filter
$famId = GetHttpVars("famid", 0); // familly filter
if (($className == "-") && ($famId == 0)) {
    print "arg class needed :usage --class=<class name> --famid=<familly id>";
    return;
}

$famId = GetHttpVars("famid", 0); // output file
$appl = new Application();
$appl->Set("FDL", $core);

$dbaccess = $appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
    print "Database not found : param FREEDOM_DB";
    exit;
}

$query = new QueryDb($dbaccess, "Doc");
$query->AddQuery("locked != -1");

if ($className != "-") $query->AddQuery("classname ~* '$className'");
if ($famId > 0) $query->AddQuery("fromid = $famId");

$table1 = $query->Query();

if ($query->nb > 0) {
    while (list($k, $v) = each($table1)) {
        print $v->title . "-";
        $v->refreshTitle();
        $v->Modify();
        print $v->title . "\n";
    }
}
?>