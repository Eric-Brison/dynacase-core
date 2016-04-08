<?php
/*
 * @author Anakeen
 * @package FDL
 */

include_once ("FDL/Lib.Attr.php");
include_once ("FDL/Class.DocFam.php");

global $action;

$parent = null;

$dbaccess = $action->dbaccess;
if ($dbaccess == "") {
    $action->exitError("Empty dbaccess: action->dbaccess.");
}

$usage = new ApiUsage();
$usage->setDefinitionText("Migrate IUSERS from dynacase 3.1 to 3.2");
$dryrun = $usage->addEmptyParameter('dry-run', 'Dry-run mode');
$usage->verify();

$sqlList = array(
    'BEGIN;'
);
/* Get families that inherits from IUSER */
$childList = array();
$q = sprintf("WITH RECURSIVE q AS ( SELECT id, fromid FROM docfam WHERE fromid = 128 UNION ALL SELECT docfam.id, docfam.fromid FROM docfam, q WHERE q.id = docfam.fromid ) SELECT id FROM q ORDER BY id");
simpleQuery($dbaccess, $q, $childList, false, false, true);
$childIdList = array();
foreach ($childList as $child) {
    $childIdList[] = $child['id'];
}
/*
 * 1. Suppress attributes that do not belongs to IUSER
*/
$resList = array();
$q = sprintf("SELECT id, docid FROM docattr WHERE docid IN (120, 128) AND (type IS NULL OR type NOT IN ('frame', 'tab', 'array', 'menu')) AND id !~ '^:' AND id NOT IN ('us_defaultgroup', 'us_lname', 'us_fname', 'us_mail', 'us_extmail', 'us_meid', 'us_login', 'us_whatid', 'us_roles', 'us_rolesorigin', 'us_rolegorigin', 'us_group', 'us_idgroup', 'us_expires', 'us_daydelay', 'us_expiresd', 'us_expirest', 'us_passdelay', 'us_ldapdn', 'us_substitute', 'us_incumbents', 'us_passwd1', 'us_passwd2', 'us_status', 'us_loginfailure', 'us_accexpiredate')");
simpleQuery($dbaccess, $q, $resList, false, false, true);
$warnNonEmpty = false;
$warnColumns = array();
foreach ($resList as $attr) {
    $sqlList[] = sprintf("DELETE FROM docattr WHERE docid = 128 AND id = '%s';", pg_escape_string($attr['id']));
    /* Check if the column is empty */
    $count = array();
    $q = sprintf("SELECT count(%s) AS count FROM doc128 WHERE %s IS NOT NULL", pg_escape_string($attr['id']) , pg_escape_string($attr['id']));
    simpleQuery($dbaccess, $q, $count, false, false, true);
    
    if (!isset($count[0]['count'])) {
        printf("Error: missing 'count' in result from query '%s'.", $q);
        exit(1);
    }
    
    if ($count[0]['count'] > 0) {
        /* Keep non-empty columns and warn user */
        $warnColumns[] = $attr['id'];
        $warnNonEmpty = true;
        continue;
    }
    /* Column is empty, so we can safely drop it */
    $sqlList[] = sprintf('ALTER TABLE doc128 DROP COLUMN "%s" CASCADE;', pg_escape_string($attr['id']));
}
/*
 * 2. Suppress MODATTR of deprecated attributes of USER and IUSER
*/
if (count($childIdList) > 0) {
    $resList = array();
    $q = sprintf("SELECT id, docid FROM docattr WHERE docid IN (120, 128) AND id !~ '^:' AND id NOT IN ('us_defaultgroup', 'us_lname', 'us_fname', 'us_mail', 'us_extmail', 'us_meid', 'us_login', 'us_whatid', 'us_roles', 'us_rolesorigin', 'us_rolegorigin', 'us_group', 'us_idgroup', 'us_expires', 'us_daydelay', 'us_expiresd', 'us_expirest', 'us_passdelay', 'us_ldapdn', 'us_substitute', 'us_incumbents', 'us_passwd1', 'us_passwd2', 'us_status', 'us_loginfailure', 'us_accexpiredate')");
    simpleQuery($dbaccess, $q, $resList, false, false, true);
    foreach ($resList as $attr) {
        $sqlList[] = sprintf("DELETE FROM docattr WHERE docid IN (%s) AND id = ':%s';", join(', ', $childIdList) , pg_escape_string($attr['id']));
    }
}
/*
 * 3. Suppress arrays/tabs that have no sub-attributes
*/
$sqlList[] = "DELETE FROM docattr WHERE docid = 128 AND type IN ('frame', 'array', 'tab') AND id NOT IN (SELECT DISTINCT dright.id FROM docattr AS dleft, docattr AS dright WHERE dleft.docid = 128 AND dleft.docid = dright.docid AND dleft.frameid = dright.id);";
/*
 * 4. Remove deprecated methods files
*/
$sqlList[] = <<<'EOT'
UPDATE docfam SET methods = array_to_string(
    array(
        SELECT * FROM (
            SELECT regexp_split_to_table(methods, E'\n') AS method FROM docfam AS docfam2 WHERE docfam2.id = docfam.id
        ) AS methodlist WHERE method NOT IN (
            'Method.DocUser.php',
            'Method.FAddBook.php',
            'Method.DocSociety.php',
            'Method.FAddBookSociety.php',
            'Method.DocSite.php'
        )
    ),
    E'\n'
);
EOT;

$sqlList[] = 'COMMIT;';

$sql = join("\n", $sqlList);

try {
    if ($dryrun) {
        printf("%s\n", $sql);
    } else {
        simpleQuery($dbaccess, $sql, $resList, false, false, true);
    }
}
catch(Exception $e) {
    printf("Exception: %s\n", $e->getMessage());
    printf("Error executing SQL transaction:\n%s\n", $sql);
    exit(1);
}

if ($warnNonEmpty) {
    $columns = join(', ', $warnColumns);
    $err = <<<"EOT"

===========================================================
We have detected that you have families that inherits from
IUSER, or have modified the IUSER family, and you are using
deprecated attributes that are not present anymore on the
IUSER family.

The SQL columns and the data have been kept, and you MUST
update your families and migrate the data to new
attributes.

Deprecated columns with data:

$columns

Press [Continue] to proceed with migration and fix it later
===========================================================

EOT;
    
    
}
exit(0);
