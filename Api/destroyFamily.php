<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Delete family document and its documents
 *
 * @subpackage
 */
/**
 */
global $action;

include_once ("FDL/Lib.Attr.php");
include_once ("FDL/Class.DocFam.php");

$usage = new ApiUsage();
$usage->setDefinitionText("Delete family document and its documents");
$docid = $usage->addRequiredParameter("famid", "special docid");
$force = $usage->addHiddenParameter("force", "force without transaction");

$transaction = $usage->addEmptyParameter("transaction", "abort deletion if one of query failed");
if (!$force) {
    $force = !$transaction;
} else {
    $force = ($force == "yes");
}
$usage->verify();

$appl = new Application();
$appl->Set("FDL", $core);

$dbaccess = $appl->dbaccess;
if ($dbaccess == "") {
    $action->exitError("Database not found : appl->dbaccess");
}

if (($docid !== 0) && (!is_numeric($docid))) {
    $odocid = $docid;
    $docid = getFamIdFromName($dbaccess, $docid);
    if (!$docid) {
        $action->exitError(sprintf(_("family %s not found") . "\n", $odocid));
    }
}

destroyFamily($dbaccess, $docid, $force);

function destroyFamily($dbaccess, $idfam, $force = false)
{
    global $action;
    
    $tdoc = getTDoc($dbaccess, $idfam);
    if ($tdoc) {
        $resid = $tdoc["id"];
        $resname = $tdoc["name"];
        print "Destroying [" . $tdoc["title"] . "(" . $tdoc["name"] . ")]\n";
        $dbid = getDbId($dbaccess);
        $tsql = array();
        if (!$force) $tsql[] = "BEGIN;";
        $tsql+= array(
            sprintf("DELETE FROM fld WHERE childid IN (SELECT id FROM doc%d);", $resid) ,
            sprintf("DELETE FROM doc%d;", $resid) ,
            sprintf("DELETE FROM docname WHERE name = %s;", pg_escape_literal($resname)) ,
            sprintf("DELETE FROM docfrom WHERE fromid = %d;", $resid) ,
            sprintf("DELETE FROM docattr WHERE docid = %d;", $resid) ,
            sprintf("DELETE FROM docfam WHERE id = %d;", $resid) ,
            sprintf("DROP VIEW IF EXISTS family.%s;", pg_escape_identifier(strtolower($resname))) ,
            sprintf("DROP TABLE IF EXISTS doc%d;", $resid) ,
            sprintf("DROP SEQUENCE IF EXISTS seq_doc%d;", $resid)
        );
        if (!$force) $tsql[] = "COMMIT;";
        $fdlgen = sprintf("FDLGEN/Class.Doc%d.php", $tdoc["id"]);
        if (file_exists($fdlgen) && is_file($fdlgen)) {
            if (!unlink($fdlgen)) {
                $action->exitError("Could not delete file '%s'.", $fdlgen);
            } else {
                printf("Deleted file '%s'.\n", $fdlgen);
            }
        }
        $res = "";
        foreach ($tsql as $sql) {
            print "$sql\n";
            $res = @pg_query($dbid, $sql);
            if (!$res) {
                print pg_last_error() . "\n";
                if (!$force) break;
            }
        }
        if ($res) printf("Family %s (id : %d) is destroyed.\n", $tdoc["name"], $tdoc["id"]);
    } else {
        $action->exitError("cannot destroy $idfam\n");
    }
}
