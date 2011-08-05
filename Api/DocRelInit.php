<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Reinit vault files
 *
 * @author Anakeen 2004
 * @version $Id: DocRelInit.php,v 1.4 2007/07/04 13:23:47 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */
ini_set("max_execution_time", "36000");

include_once ('FDL/Class.Doc.php');
include_once ('FDL/Class.DocFam.php');
include_once ('FDL/Class.DocVaultIndex.php');
include_once ('VAULT/Class.VaultFile.php');

$dbaccess = GetParam("FREEDOM_DB");
if ($dbaccess == "") {
    print "Database not found : param FREEDOM_DB";
    exit;
}
$o = new DbObj($dbaccess);
$q = new QueryDb($dbaccess, "DocAttr");
$q->AddQuery("type = 'docid'");
$q->AddQuery("usefor != 'Q'");
//$q->AddQuery("frameid not in (select id from docattr where type~'array')");
$la = $q->Query(0, 0, "TABLE");
if ($q->nb > 0) {
    $o->exec_query("delete from docrel");
}

foreach ($la as $k => $v) {
    $docid = $v["docid"];
    $aid = $v["id"];
    
    $sql = "insert into docrel (sinitid, cinitid, type,doctype ) (SELECT initid, {$aid}::int, '$aid', doctype from doc$docid where $aid ~ '^[0-9]+$' and locked != -1);";
    $o->exec_query($sql);
    // print "$sql\n";
    $sql2 = "SELECT docrelreindex(initid, $aid,'$aid') from doc$docid where $aid is not null and $aid ~ '^[^\n]*[0-9]\n.*[0-9]$' and locked != -1;";
    $o->exec_query($sql2);
    //  print "$sql2\n";
    
    
}
// Folders
$sql2 = "insert into docrel (sinitid, cinitid, type, doctype ) ( SELECT dirid, childid ,'folder', doctype from fld where qtype='S')";
$o->exec_query($sql2);

print "stitle\n";
$sql = "UPDATE docrel set stitle = (select title from docread where initid=sinitid and locked != -1) where stitle is  null;";
$o->exec_query($sql);
print "ctitle\n";

$sql = "DELETE FROM docrel where cinitid not in (select id from docread);";
$o->exec_query($sql);
$sql = "UPDATE docrel set ctitle = (select title from docread where initid=cinitid and locked != -1) where ctitle is  null;";
$o->exec_query($sql);
$sql = "UPDATE docrel set cinitid = (select initid from docread where id=cinitid) where cinitid is not null and cinitid > 0";
$o->exec_query($sql);
print "cicon\n";
$sql = "UPDATE docrel set cicon = (select icon from docread where id=cinitid) where cicon is  null;";
$o->exec_query($sql);
print "sicon\n";
$sql = "UPDATE docrel set sicon = (select icon from docread where id=sinitid)  where sicon is  null;";
$o->exec_query($sql);
?>
