<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generate Php Document Classes
 *
 * @author Anakeen
 * @version $Id: fdl_adoc.php,v 1.20 2008/10/30 17:34:31 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */
// refreah for a classname
// use this only if you have changed title attributes
include_once ("FDL/Class.DocEnum.php");

global $action;
$dbaccess = $action->dbaccess;
if ($dbaccess == "") {
    print "Database not found : param FREEDOM_DB";
    exit;
}
$usage = new ApiUsage();
$usage->setDefinitionText("Migrate enum defined in docattr(phpfunc) to docenum");
$usage->verify();
/**
 * @var Action $action
 */
// First Part: Workflow
print "\t === migrate Enum ===\n";
$sql = sprintf("select id, docid, phpfunc from docattr where type ~ '^enum' and (phpfile is null or phpfile != '-')");
simpleQuery($dbaccess, $sql, $result);

$oe = new DocEnum($dbaccess);
foreach ($result as $enumDef) {
    printf("Doing %s [%s]\n", $enumDef["id"], $enumDef["phpfunc"]);
    $err = importDocumentDescription::recordEnum($enumDef["docid"], $enumDef["id"], $enumDef["phpfunc"]);
    
    if ($err) $action->exitError($err);
    
    print "\n";
}
print "\n";
