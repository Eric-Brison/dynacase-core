<?php
/*
 * Reinit vault files
 *
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

global $appl, $action;

include_once ('FDL/Class.Doc.php');
include_once ('FDL/Class.DocFam.php');
include_once ('FDL/Class.DocVaultIndex.php');
include_once ('VAULT/Class.VaultFile.php');
/**
 * Setup main db connection
 */
$dbaccess = GetParam("FREEDOM_DB");
if ($dbaccess == "") {
    $action->exitError("Database not found : param FREEDOM_DB");
}
$o = new DbObj($dbaccess);
if (!is_object($o)) {
    $action->exitError(sprintf("Could not get DbObj with dbaccess='%s'", $dbaccess));
}
/**
 * Parse arguments
 */
$parms = array();
$usage = new ApiUsage();
$usage->setDefinitionText("Re-initialize docvaultindex table");
/* --dryrun=no|yes (default 'no') */
$parms['dryrun'] = $usage->addEmptyParameter("dryrun", "Only output SQL queries that would be executed");
if ($parms['dryrun'] == 'yes' || $parms['dryrun'] === true) {
    $parms['dryrun'] = true;
} else {
    $parms['dryrun'] = false;
}
/* --famid=<name|id> (default 'all') */
$parms['famid'] = $usage->addOptionalParameter("famid", "Process all families (keyword 'all') or only a specific family (family name or family id)", null, "all");
if ($parms['famid'] != 'all') {
    if (!is_numeric($parms['famid'])) {
        $famid = getIdFromName($dbaccess, $parms['famid']);
        if (!is_numeric($famid)) {
            $action->exitError(sprintf("Could not find family '%s'", $parms['famid']));
        }
        $parms['famid'] = $famid;
    }
    $fam = new_Doc($dbaccess, $parms['famid'], true);
    if (!is_object($fam) || !$fam->isAlive()) {
        $action->exitError(sprintf("Family with id '%s' is not alive or not a valid family.", $parms['famid']));
    }
}
/* --transaction=no|yes (default 'no') */
$parms['transaction'] = $usage->addEmptyParameter("transaction", "Execute whole operation in a single transaction");
if ($parms['transaction'] == 'yes' || $parms['transaction'] === true) {
    $parms['transaction'] = true;
} else {
    $parms['transaction'] = false;
}
/* --realclean=yes|no (default 'yes') */
$parms['realclean'] = $usage->addHiddenParameter("realclean", "Delete everything in docvaultindex at the beginning of the operation - old yes/no");
$parms['softclean'] = $usage->addEmptyParameter("softclean", "Don't delete everything in docvaultindex before begin operation");

if ($parms['realclean'] == 'yes' && $parms['famid'] == 'all') {
    $parms['realclean'] = true;
} else {
    if ($parms['realclean'] == 'no') {
        $parms['realclean'] = false;
    } else {
        $parms['realclean'] = !$parms['softclean'];
    }
}
$usage->verify();
/**
 * Load family list
 */
$q = new QueryDb($dbaccess, "DocFam");
$q->order_by = 'id';
$q->AddQuery("icon ~ E'^[^\\\\|]*\\\\|\\\\d+(\\\\|[^\\\\|]*)?$'");
if (is_numeric($parms['famid'])) {
    $q->AddQuery(sprintf("id = %s", pg_escape_string($parms['famid'])));
}
$famIconList = $q->Query(0, 0, "TABLE");
if (!$famIconList) {
    $famIconList = array();
}
/**
 * Load all file attributes
 */
$q = new QueryDb($dbaccess, "DocAttr");
$q->order_by = 'docid, id';
$q->AddQuery("type = 'file' OR type = 'image'");
$q->AddQuery("usefor != 'Q'");
$q->AddQuery("id !~ '^:'"); /* Do not process modattr attributes */
if (is_numeric($parms['famid'])) {
    $q->AddQuery(sprintf("docid = %s", pg_escape_string($parms['famid'])));
}
$attrList = $q->Query(0, 0, "TABLE");
/**
 * Load all file parameters
 */
$q = new QueryDb($dbaccess, "DocAttr");
$q->order_by = 'docid, id';
$q->AddQuery("(type = 'file' OR type = 'image')");
$q->AddQuery("usefor = 'Q'");
$q->AddQuery("id !~ '^:'");
if (is_numeric($parms['famid'])) {
    $q->AddQuery(sprintf("docid = %s", pg_Escape_string($parms['famid'])));
}
$paramList = $q->Query(0, 0, "TABLE");
/**
 * Begin transaction if required
 */
if ($parms['transaction']) {
    sqlexec($o, $parms, "BEGIN;");
}
/**
 * Delete all docvaultindex if all families are reindexed
 * and --realclean=yes
 */
if ($parms['famid'] == 'all' && $parms['realclean']) {
    print sprintf("-- Deleting all indexes ...\n");
    sqlexec($o, $parms, "DELETE FROM docvaultindex");
    
    if (!$parms['transaction']) {
        sqlexec($o, $parms, "VACUUM ANALYSE docvaultindex");
    }
    sqlexec($o, $parms, "REINDEX TABLE docvaultindex");
}
/**
 * Re-index docvaultindex file attributes
 */
$deletedFam = array();
foreach ($attrList as $i => $attr) {
    $docid = $attr['docid'];
    $attrid = $attr['id'];
    $table = familyTableName($docid);
    
    if (!$parms['realclean'] && !isset($deletedFam[$docid])) {
        print sprintf("-- Deleting attributes vault indexes for family '%s'...\n", $docid);
        $sql = sprintf("DELETE FROM docvaultindex WHERE EXISTS (SELECT id FROM %s WHERE id = docid)", pg_escape_string($table));
        sqlexec($o, $parms, $sql);
        $deletedFam[$docid] = 1;
    }
    
    print sprintf("-- Indexing family '%s', attribute '%s'...\n", $docid, $attrid);
    $sql = sprintf("SELECT vaultreindex(id, %s) FROM %s WHERE %s IS NOT NULL", pg_escape_string($attrid) , pg_escape_string($table) , pg_escape_string($attrid));
    sqlexec($o, $parms, $sql);
}
/**
 * Re-index docvaultindex file parameters
 */
$deletedFam = array();
foreach ($paramList as $i => $param) {
    $docid = $param['docid'];
    $paramid = $param['id'];
    
    if (!isset($deletedFam[$docid]) && !$parms['realclean']) {
        print sprintf("-- Deleting icons and parameters vault indexes for family '%s'...\n", $docid);
        $sql = sprintf("DELETE FROM docvaultindex WHERE docid = %s", $docid);
        sqlexec($o, $parms, $sql);
        $deletedFam[$docid] = 1;
    }
    
    print sprintf("-- Indexing family '%s', parameter '%s'...\n", $docid, $paramid);
    $sql = sprintf("SELECT vaultreindexparam(id, param, '%s') FROM family.families WHERE id = %s", pg_escape_string($paramid) , pg_escape_string($docid) , pg_escape_string($paramid));
    sqlexec($o, $parms, $sql);
}
/**
 * Re-index family icons
 */
foreach ($famIconList as $i => $fam) {
    $famid = $fam['id'];
    
    print sprintf("-- Indexing icon for family '%s'...\n", $famid);
    $sql = sprintf("SELECT vaultreindex(id, icon) FROM family.families WHERE id = %s", $famid);
    sqlexec($o, $parms, $sql);
}
/**
 * Commit transaction if required
 */
if ($parms['transaction']) {
    sqlexec($o, $parms, "COMMIT;");
}
/**
 * @param DbObj $dbobj
 * @param array $parms
 * @param string $sql
 * @return string
 */
function sqlexec(&$dbobj, &$parms, $sql)
{
    if ($parms['dryrun']) {
        if (!preg_match('/;\s*$/', $sql)) {
            $sql = $sql . ';';
        }
        str_replace($sql, '\\', '\\\\');
        print "$sql\n";
        return '';
    }
    $err = $dbobj->exec_query($sql);
    if ($err != '') {
        error_log(sprintf("Error executing query [%s]: %s", $sql, $err));
    }
    return $err;
}
?>
