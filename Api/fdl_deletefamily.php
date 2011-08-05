<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Delete family document and its documents
 *
 * @author Anakeen 2000
 * @version $Id: fdl_adoc.php,v 1.20 2008/10/30 17:34:31 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Lib.Attr.php");
include_once ("FDL/Class.DocFam.php");

$appl = new Application();
$appl->Set("FDL", $core);

$dbaccess = $appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
    print "Database not found : param FREEDOM_DB";
    exit;
}

$docid = GetHttpVars("famid", 0); // special docid
$force = (GetHttpVars("force") == "yes"); // force
if (!$docid) {
    print sprintf(_("usage %s --famid=<family id> [--force=yes]") . "\n", $argv[0]);
    exit(1);
}

if (($docid !== 0) && (!is_numeric($docid))) {
    $odocid = $docid;
    $docid = getFamIdFromName($dbaccess, $docid);
    if (!$docid) {
        print sprintf(_("family %s not found") . "\n", $odocid);
        exit(1);
    }
}

destroyFamily($dbaccess, $docid, $force);

function destroyFamily($dbaccess, $idfam, $force = false)
{
    $tdoc = getTDoc($dbaccess, $idfam);
    if ($tdoc) {
        $resid = $tdoc["id"];
        $resname = $tdoc["name"];
        print "Destroying [" . $tdoc["title"] . "(" . $tdoc["name"] . ")]\n";
        $dbid = getDbId($dbaccess);
        $tsql = array();
        if (!$force) $tsql[] = "begin;";
        $tsql+= array(
            "delete from fld where childid in (select id from doc$resid);",
            "delete from doc$resid;",
            "drop view family.\"" . strtolower($resname) . "\";",
            "delete from docname where name='$resname'",
            "delete from docfrom where fromid=$resid",
            "drop table doc$resid;",
            "delete from docattr where docid=$resid;",
            "delete from docfam where id=$resid;"
        );
        if (!$force) $tsql[] = "commit;";
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
        print "cannot destroy $idfam";
    }
}
?>