<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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

$dbaccess = $appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
    $action->exitError("Database not found : param FREEDOM_DB");
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
        if (!$force) $tsql[] = "begin;";
        $tableName = familyTableName($resid);
        $tsql+= array(
            "delete from fld where childid in (select id from $tableName);",
            "delete from $tableName;",
            "drop view if exists doc$resid;",
            "delete from docname where name='$resname'",
            "delete from docfrom where fromid=$resid",
            "drop table $tableName;",
            "delete from docattr where docid=$resid;",
            "delete from family.families where id=$resid;"
        );
        if (!$force) $tsql[] = "commit;";

        if (!unlink(getFamilyFileName($tdoc["id"]))) {
            if (!$force) {
            $action->exitError("cannot destroy $idfam family file");
            }
        } else {
            printf( "delete family class file : %s\n",getFamilyFileName($tdoc["id"]));
        }
        $attrFileName=sprintf("FDLGEN/Class.Attrid%s.php", ucfirst(strtolower($tdoc["name"])));
        if (!unlink($attrFileName)) {
            if (!$force) {
            $action->exitError("cannot destroy $idfam attribute file");
            }
        } else {
            printf( "delete attribute class file : %s\n",$attrFileName);
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
?>