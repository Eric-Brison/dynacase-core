<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Update the SQL structure of a table of a DbObj Object
 *
 * @param string $appc the application directory (WHAT, FDL, ...)
 * @param string $class the class name of the DbObj Class
 * @param string $dbname the SQL database name (anakeen, freedom)
 * @author Anakeen 2002
 * @version $Id: updateclass.php.in,v 1.8 2008/12/31 14:39:35 jerome Exp $
 * @package FDL
 * @subpackage CORE
 */
/**
 */

global $action;

include_once ('Class.Application.php');

$usage = new ApiUsage();

$usage->setDefinitionText("Update the SQL structure of a table of a DbObj Object");
$appClass = $usage->addOptionalParameter('appc', "application class folder", function ($value, $name)
{
    if (!is_scalar($value)) {
        return sprintf("Multiple values for '%s' not allowed.", $name);
    }
    if (strpos($value, DIRECTORY_SEPARATOR) !== false) {
        return sprintf("Value for '%s' must not contain directory separator chars ('%s').", $name, DIRECTORY_SEPARATOR);
    }
    return '';
}
, 'WHAT');
$class = $usage->addRequiredParameter('class', 'Class name', function ($value, $name)
{
    if (!is_scalar($value)) {
        return sprintf("Multiple values for '%s' not allowed.", $name);
    }
    if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $value)) {
        return sprintf("Invalid class name '%s' for '%s'.", $value, $name, DIRECTORY_SEPARATOR);
    }
    return '';
});
$db = $usage->addOptionalParameter('dbcoord', "Database name", null, getDbAccess());

$usage->verify();

$phpClass = sprintf("%s/%s/Class.%s.php", DEFAULT_PUBDIR, $appClass, $class);

require_once ($phpClass);
/**
 * @var DbObj $o
 */
$o = new $class($db);

$sql = array();
$updateExistingTable = \Dcp\Core\PgInformationSchema::tableExists($o->dbaccess, 'public', $o->dbtable);
if ($updateExistingTable) {
    /* Compute columns that appears both in new and old table */
    $columns = \Dcp\Core\PgInformationSchema::getTableColumns($o->dbaccess, 'public', $o->dbtable);
    $commonColumns = array_intersect($o->fields, $columns);
    /* Add SQL rename of current table to table + '_old' */
    $oldTableName = sprintf("%s_old", $o->dbtable);
    $sql[] = sprintf("ALTER TABLE public.%s RENAME TO %s", pg_escape_identifier($o->dbtable) , pg_escape_identifier($oldTableName));
    /* Add SQL creation of new table */
    $sqlCommands = explode(";", str_replace("\n", " ", $o->sqlcreate));
    foreach ($sqlCommands as $k => $sqlQuery) {
        $tableIndexes = \Dcp\Core\PgInformationSchema::getTableIndexes($o->dbaccess, 'public', $o->dbtable);
        /* Drop index (if exists) before recreating it */
        if (preg_match('/CREATE\s+  (?:UNIQUE\s+)?  INDEX\s+  (?:CONCURRENTLY\s+)?  (?:IF\s+NOT\s+EXISTS\s+)?  (?P<indexName>[a-z0-9_]+)/xi', $sqlQuery, $m)) {
            /* If index exists, then drop it */
            if (in_array($m['indexName'], $tableIndexes)) {
                $sql[] = sprintf("DROP INDEX public.%s", pg_escape_identifier($m['indexName']));
            }
        }
        if (chop($sqlQuery) != "") $sql[] = $sqlQuery;
    }
    /* Add SQL to load common columns data from old table */
    $sql[] = sprintf("INSERT INTO public.%s (%s) SELECT %s FROM public.%s", pg_escape_identifier($o->dbtable) , implode(", ", array_map('pg_escape_identifier', $commonColumns)) , implode(", ", array_map('pg_escape_identifier', $commonColumns)) , pg_escape_identifier($oldTableName));
    /* Drop old table */
    $sql[] = sprintf("DROP TABLE public.%s", pg_escape_identifier($oldTableName));
}
/* Play SQL commands */
$point = uniqid(sprintf('%s/%s', $appClass, $class) , true);
if (($err = $o->savePoint($point)) !== '') {
    $action->exitError($err);
}
if ($updateExistingTable) {
    print sprintf("Updating existing table '%s'...\n", $o->dbtable);
    foreach ($sql as $k => $v) {
        if (preg_match('/CREATE\s+  SEQUENCE\s+  (?:IF\s+NOT\s+EXISTS\s+)?  (?P<seqName>[a-z0-9_]+)/xi', $v, $m)) {
            /* Do not recreate sequences (keep existing sequences untouched) */
            print sprintf("[+] (%d/%d) Skipping sequence '%s'.\n", $m['seqName'], $k + 1, count($sql));
            continue;
        }
        print sprintf("[+] (%d/%d) Executing SQL:\n", $k + 1, count($sql));
        print "\t--8<--\n";
        print "\t" . str_replace("\n", "\n\t", $v) . "\n";
        print "\t-->8--\n";
        simpleQuery($o->dbaccess, $v, $res, false, false, true);
        print "[+] Done.\n";
    }
} else {
    print sprintf("Creating table '%s'...\n", $o->dbtable);
    /* Table does not exists: create it */
    $o->Create();
}
if (($err = $o->commitPoint($point)) !== '') {
    $action->exitError($err);
}

print "\nDone.\n";
