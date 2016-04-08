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
include_once ("FDL/Lib.Attr.php");
include_once ("FDL/Class.DocFam.php");

$appl = new Application();
$appl->Set("FDL", $core);

$dbaccess = $appl->dbaccess;
if ($dbaccess == "") {
    print "Database not found : appl->dbaccess";
    exit;
}
$usage = new ApiUsage();
$usage->setDefinitionText("Delete parameter values which are not real parameters");
$verifyOnly = $usage->addEmptyParameter("verify-only", "only verify, do not changes");
$usage->verify();
/**
 * @var Action $action
 */
// First Part: Workflow
print "\t === Deleting parasite parameters ===\n";
if ($verifyOnly) print "\nJust Verify...\n";
$s = new SearchDoc($action->dbaccess, "-1");
$s->setObjectReturn(true);
$s->search();
$deleting = array();
/**
 * @var DocFam $fam
 */
while ($fam = $s->getNextDoc()) {
    print ("\n" . $fam->getTitle() . " : #" . $fam->id);
    $pa = $fam->getOwnParams();
    
    $before = $fam->param;
    foreach ($pa as $aid => $val) {
        $oa = $fam->getAttribute($aid);
        if (!$oa) {
            $deleting[] = $aid;
            $fam->setParam($aid, '', false);
        } else {
            if ($oa->usefor != 'Q') {
                $deleting[] = $aid;
                $fam->setParam($aid, '', false);
            } else {
                // it's a good param
                $sql = sprintf("select id from docattr where (id='%s' or id=':%s') and docid=%d", $oa->id, $oa->id, $oa->docid);
                simpleQuery($action->dbaccess, $sql, $dbAttrid, true, true);
                if (!$dbAttrid) {
                    $deleting[] = $aid;
                    $fam->setParam($aid, '', false);
                }
            }
        }
    }
    $after = $fam->param;
    if ($before != $after) {
        printf("Change from \n\t%s to \n\t%s", $before, $after);
        if (!$verifyOnly) {
            $err = $fam->modify();
            $err = '';
            if (!$err) print "changed";
            else print $err;
        }
    } else print ": clean - nothing to do";
}
//print_r2($deleting);
print "\n";
