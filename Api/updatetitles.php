<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: updatetitles.php,v 1.4 2003/08/18 15:47:04 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */
// update title for a classname
// use this only if you have changed title attributes
include_once ("FDL/Class.Doc.php");

$usage = new ApiUsage();

$usage->setText("Update titles");
$className = $usage->addNeeded("class", "classname filter"); // classname filter
$famId = $usage->addNeeded("famid", "family filter"); // familly filter

$usage->verify();

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
        /**
         * @var Doc $v
         */
        print $v->title . "-";
        $v->refreshTitle();
        $v->Modify();
        print $v->title . "\n";
    }
}
?>