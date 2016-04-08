<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Reinit vault files
 *
 * @author Anakeen
 * @version $Id: VaultIndexInit.php,v 1.4 2008/11/28 16:14:34 jerome Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ('FDL/Class.Doc.php');
include_once ('FDL/Class.DocFam.php');
include_once ('FDL/Class.DocVaultIndex.php');
include_once ('VAULT/Class.VaultFile.php');

$usage = new ApiUsage();

$usage->setDefinitionText("Reinit vault files");

$usage->verify();

global $action;
$dbaccess = $action->dbaccess;
if ($dbaccess == "") {
    print "Database not found : action->dbaccess";
    exit;
}
$o = new DbObj($dbaccess);
$q = new QueryDb($dbaccess, "DocAttr");
$q->AddQuery("type = 'file'");
$q->AddQuery("usefor != 'Q'");
$la = $q->Query(0, 0, "TABLE");

foreach ($la as $k => $v) {
    $docid = $v["docid"];
    $aid = $v["id"];
    
    $sql = "update doc$docid set {$aid}_vec=null;";
    print "$sql\n";
    $o->exec_query($sql);
    //print "$sql2\n";
    
}

$sql = "update doc set fulltext=null;";
print "$sql\n";
$o->exec_query($sql);
