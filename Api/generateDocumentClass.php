<?php
/*
 * @author Anakeen
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

$appl = new Application();
$appl->Set("FDL", $core);

$dbaccess = $appl->dbaccess;
if ($dbaccess == "") {
    print "Database not found : appl->dbaccess";
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
$query->AddQuery("doctype='C'");
$query->order_by = "id";

ini_set("memory_limit", -1);

if ($docid > 0) {
    $query->AddQuery("id=$docid");
    $tid = $query->Query(0, 0, "TABLE");
} else {
    // sort id by dependance
    $table1 = $query->Query(0, 0, "TABLE");
    $tid = array();
    pushfam(0, $tid, $table1);
}
if ($query->nb > 0) {
    $pubdir = DEFAULT_PUBDIR;
    if ($query->nb > 1) {
        $tii = array(
            1,
            2,
            3,
            4,
            5,
            6,
            20,
            21
        );
        foreach ($tii as $ii) {
            updateDoc($dbaccess, $tid[$ii]);
            unset($tid[$ii]);
        }
    }
    // workflow at the end
    foreach ($tid as $k => $v) {
        if (strstr($v["usefor"], 'W')) {
            updateDoc($dbaccess, $v);
            /**
             * @var WDOc $wdoc
             */
            $wdoc = createDoc($dbaccess, $v["id"]);
            $wdoc->CreateProfileAttribute(); // add special attribute for workflow
            \Dcp\FamilyImport::activateTrigger($dbaccess, $v["id"]);
        }
    }
    foreach ($tid as $k => $v) {
        if (strstr($v["usefor"], 'W') === false) {
            updateDoc($dbaccess, $v);
        }
    }
}
function updateDoc($dbaccess, $v)
{
    require_once 'FDL/Lib.Attr.php';
    try {
        $err = \Dcp\FamilyImport::buildFamilyFilesAndTables($dbaccess, $v, true);
        if ($err) {
            error_log($err);
        }
    }
    catch(\Dcp\Exception $e) {
        print $v["id"] . "[" . $v["title"] . "(" . $v["name"] . ")]\n";
        error_log($e->getMessage());
    }
}
// recursive sort by fromid
function pushfam($fromid, &$tid, $tfam)
{
    
    foreach ($tfam as $k => $v) {
        
        if ($v["fromid"] == $fromid) {
            $tid[$v["id"]] = $v;
            
            pushfam($v["id"], $tid, $tfam);
        }
    }
}
