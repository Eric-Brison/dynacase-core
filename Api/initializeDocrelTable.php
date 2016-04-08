<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 *  Reinit doc relations
 */

global $appl, $action;

include_once ('FDL/Class.Doc.php');
include_once ('FDL/Class.DocFam.php');
include_once ('FDL/Class.DocVaultIndex.php');
include_once ('VAULT/Class.VaultFile.php');
/**
 * Setup main db connection
 */
$dbaccess = $action->dbaccess;
if ($dbaccess == "") {
    $action->exitError("Database not found : action->dbaccess");
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
$usage->setDefinitionText("Re-initialize docrel table");
/* --dryrun=no|yes (default 'no') */
$parms['dryrun'] = $usage->addEmptyParameter("dryrun", "Only output SQL queries that would be executed");
if ($parms['dryrun'] == 'yes' || $parms['dryrun'] === true) {
    $parms['dryrun'] = true;
} else {
    $parms['dryrun'] = false;
}
/* --famid=<name|id> (default 'all') */
$parms['famid'] = $usage->addOptionalParameter("famid", "Process all families (keyword 'all') or only a specific family (family name or family id)", array() , "all");
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
$parms['realclean'] = $usage->addHiddenParameter("realclean", "Delete everything in docrel at the beginning of the operation - old yes/no");
$parms['softclean'] = $usage->addEmptyParameter("softclean", "Don't delete everything in decrel before begin operation");

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
 * Load all docid attributes
 */
$q = new QueryDb($dbaccess, "DocAttr");
$q->order_by = '(docid, id)';
$q->AddQuery("type ~ '^docid'");
$q->AddQuery("usefor != 'Q'");
$q->AddQuery("id !~ '^:'"); /* Do not process modattr attributes */
if (is_numeric($parms['famid'])) {
    $q->AddQuery(sprintf("docid = %s", pg_escape_string($parms['famid'])));
}
$attrList = $q->Query(0, 0, "TABLE");
/**
 * Begin transaction if required
 */
if ($parms['transaction']) {
    sqlexec($o, $parms, "BEGIN;");
}
/**
 * Delete all docrels if all families are reindexed
 * and --realclean=yes as been specified
 */
if ($parms['famid'] == 'all' && $parms['realclean']) {
    sqlexec($o, $parms, "DELETE FROM docrel");
    
    if (!$parms['transaction']) {
        sqlexec($o, $parms, "VACUUM ANALYSE docrel");
    }
    sqlexec($o, $parms, "REINDEX TABLE docrel");
}
/**
 * Re-index docid attributes
 */
foreach ($attrList as $i => $attr) {
    $docid = $attr["docid"];
    $attrid = $attr["id"];
    
    if (!$parms['realclean']) {
        /**
         * Delete only docrels that are going to be recomputed
         */
        print sprintf("-- Deleting relations for family '%s', attribute '%s'...\n", $docid, $attrid);
        $sql = sprintf("DELETE FROM docrel WHERE EXISTS (SELECT id FROM doc%s WHERE id = sinitid) AND type = '%s'", pg_escape_string($docid) , pg_escape_string($attrid));
        sqlexec($o, $parms, $sql);
    }
    
    print sprintf("-- Indexing family '%s', attribute '%s'...\n", $docid, $attrid);
    $sql = sprintf("SELECT docrelreindex(initid, %s, '%s') FROM doc%s WHERE %s IS NOT NULL AND locked != -1", pg_escape_string($attrid) , pg_escape_string($attrid) , pg_escape_string($docid) , pg_escape_string($attrid));
    sqlexec($o, $parms, $sql);
}
/**
 * Recompute titles and icons
 */
print sprintf("-- Deleting broken relations...\n");
sqlexec($o, $parms, "DELETE FROM docrel WHERE NOT EXISTS (SELECT id FROM docread WHERE id = cinitid)");
if (!$parms['transaction']) {
    sqlexec($o, $parms, "VACUUM ANALYSE docrel");
}

print sprintf("-- Dropping docrel indexes...\n");
sqlexec($o, $parms, "DROP INDEX docrel_u");
sqlexec($o, $parms, "DROP INDEX i_docrels");
sqlexec($o, $parms, "DROP INDEX i_docrelc");

print sprintf("-- Recomputing cinitid...\n");
sqlexec($o, $parms, "UPDATE docrel SET cinitid = docread.initid FROM docread WHERE cinitid IS NOT NULL AND cinitid > 0 AND cinitid = docread.id AND docread.id != docread.initid");

print sprintf("-- Recomputing {stitle, ctitle, sicon, cicon}...\n");
sqlexec($o, $parms, "
UPDATE docrel SET stitle = s.title, ctitle = c.title, sicon = s.icon, cicon = c.icon, cinitid = c.initid
FROM docread AS s, docread AS c
WHERE
  (
    sinitid = s.initid AND s.locked != -1
    AND
    cinitid = c.initid AND c.locked != -1
  )
  AND
  (
    docrel.stitle IS NULL
    OR
    docrel.ctitle IS NULL
    OR
    docrel.sicon IS NULL
    OR
    docrel.cicon IS NULL
  )
");

print sprintf("-- Re-creating docrel indexes...\n");
sqlexec($o, $parms, "CREATE INDEX docrel_u ON docrel (sinitid, cinitid, type)");
sqlexec($o, $parms, "CREATE INDEX i_docrels ON docrel (sinitid)");
sqlexec($o, $parms, "CREATE INDEX i_docrelc ON docrel (cinitid)");
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
