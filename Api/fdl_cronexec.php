<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 *  Execute Freedom Processes when needed
 *
 * @author Anakeen 2005
 * @version $Id: fdl_cronexec.php,v 1.4 2008/12/31 14:39:52 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */
// refreah for a classname
// use this only if you have changed title attributes
include_once ("FDL/Class.DocFam.php");
include_once ("FDL/Class.DocTimer.php");
include_once ("FDL/Class.SearchDoc.php");

$appl = new Application();
$appl->Set("FDL", $core);

$dbaccess = $appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
    print "Database not found : param FREEDOM_DB";
    exit;
}

function verifyExecDocuments($dbaccess)
{
    // Verify EXEC document
    $now = Doc::getTimeDate();
    
    $s = new SearchDoc($dbaccess, "EXEC");
    $s->setObjectReturn();
    $s->addFilter("exec_nextdate < '" . $now . "'");
    $s->addFilter("exec_status is null or exec_status='none'");
    //  $s->setDebugMode();
    $s->search();
    if ($s->count() > 0) {
        while ($de = $s->nextDoc()) {
            $de->setValue("exec_status", "waiting");
            $de->modify(true, array(
                "exec_status"
            ) , true);
        }
        $s = new SearchDoc($dbaccess, "EXEC");
        $s->setObjectReturn();
        $s->addFilter("exec_nextdate < '" . $now . "'");
        $s->addFilter("exec_status != 'progressong'");
        //$s->setDebugMode();
        $s->search();
        //print_r2($s->getDebugInfo());
        while ($de = $s->nextDoc()) {
            $status = $de->bgExecute(_("freedom cron try execute"));
            $del = new_Doc($dbaccess, $de->latestId(false, true));
            
            $del->deleteValue("exec_status");
            $del->deleteValue("exec_handnextdate");
            $del->refresh();
            $del->postModify();
            $err = $del->modify();
            print sprintf("Execute %s [%d] (%s) : %s\n", $del->title, $del->id, $del->exec_handnextdate, $err);
        }
    }
}
function verifyTimerDocuments($dbaccess)
{
    // Verify EXEC document
    $dt = new DocTimer($dbaccess);
    $ate = $dt->getActionsToExecute();
    
    foreach ($ate as $k => $v) {
        $dt->Affect($v);
        $dt->executeTimerNow();
    }
}
verifyExecDocuments($dbaccess);
verifyTimerDocuments($dbaccess);
?>