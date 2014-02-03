<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generate Php Document Classes
 *
 * @subpackage
 */
/**
 */
// refreah for a classname
// use this only if you have changed title attributes
include_once ("FDL/Lib.Attr.php");
include_once ("FDL/Class.DocFam.php");
/**
 * @var Action $action
 */
$dbaccess = $action->dbaccess;
if ($dbaccess == "") {
    print "Database not found : param FREEDOM_DB";
    exit;
}
$usage = new ApiUsage();
$usage->setDefinitionText("Generate Php Document Classes");
$docid = $usage->addOptionalParameter("docid", "special docid", null, 0);
$usage->verify();

if (($docid !== 0) && (!is_numeric($docid))) {
    $odocid = $docid;
    $docid = getFamIdFromName($dbaccess, $docid);
    if (!$docid) {
        print sprintf(_("family %s not found") . "\n", $odocid);
        exit(1);
    }
}

$query = new QueryDb($dbaccess, "DocFam");
$query->AddField("familyLevel(id) as level");
if ($docid > 0) {
    $query->AddQuery(sprintf("id=%d", $docid));
}

$query->order_by = "level,id";
$result = $query->Query(0, 0, "TABLE");

ini_set("memory_limit", -1);

foreach ($result as $k => $v) {
    if (strstr($v["usefor"], 'W') === false) {
        updateDoc($dbaccess, $v);
    }
}
// workflow at the end
foreach ($result as $k => $v) {
    if (strstr($v["usefor"], 'W')) {
        updateDoc($dbaccess, $v);
        /**
         * @var WDOc $wdoc
         */
        $wdoc = createDoc($dbaccess, $v["id"]);
        $wdoc->CreateProfileAttribute(); // add special attribute for workflow
        activateTrigger($dbaccess, $v["id"]);
    }
}
$needCompatibleView = (getParam("CORE_DBDOCVIEWCOMPAT") == "yes");
$sql = sprintf("drop view if exists public.doc;");
$sql.= sprintf("drop view if exists public.docfam;");
if ($needCompatibleView) {
    $sql.= sprintf("create view public.doc as (select * from family.documents);");
    $sql.= sprintf("create view public.docfam as (select * from family.families);");
}
simpleQuery($dbaccess, $sql);

function updateDoc($dbaccess, $v)
{
    try {
        $phpfile = createDocFile($dbaccess, $v);
        print "$phpfile [" . $v["title"] . "(" . $v["name"] . ")]\n";
        $msg = PgUpdateFamilly($dbaccess, $v["id"], $v["name"]);
        print $msg;
        activateTrigger($dbaccess, $v["id"]);
        resetSystemEnum($v["id"]);
    }
    catch(\Dcp\Exception $e) {
        print $v["id"] . "[" . $v["title"] . "(" . $v["name"] . ")]\n";
        error_log($e->getMessage());
    }
}
?>