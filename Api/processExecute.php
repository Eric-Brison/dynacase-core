<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 *  Execute Freedom Processes when needed
 *
 * @author Anakeen
 * @version $Id: processExecute.php,v 1.4 2008/12/31 14:39:52 eric Exp $
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

$usage = new ApiUsage();
$usage->setText("Execute Dynacase Processes when needed");
$usage->verify();

$appl = new Application();
$appl->Set("FDL", $core);

$dbaccess = $appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
    print "Database not found : param FREEDOM_DB";
    exit;
}

function verifyExecDocuments($dbaccess)
{
    /**
     * @var Action $action
     */
    global $action;
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
            /**
             * @var _EXEC $de
             */
            /**
             * Logging in bgexecute
             */
            $status = $de->bgExecute(_("dynacase cron try execute"));
            $del = new_Doc($dbaccess, $de->latestId(false, true));
            /**
             * @var _EXEC $del
             */
            $del->deleteValue("exec_status");
            $del->deleteValue("exec_handnextdate");
            $err = $del->store();
            
            if ($status == 0) {
                print sprintf("Execute %s [%d] (%s) : %s\n", $del->title, $del->id, $del->getValue("exec_handnextdate") , $err);
            } else {
                print sprintf("Error executing %s [%d] (%s) : %s (%s)\n", $del->title, $del->id, $del->getValue("exec_handnextdate") , $err, $status);
            }
        }
    }
}

function verifyTimerDocuments($dbaccess)
{
    global $action;
    /**
     * @var Action $action
     */
    // Verify EXEC document
    $dt = new DocTimer($dbaccess);
    $ate = $dt->getActionsToExecute();
    
    foreach ($ate as $k => $v) {
        $dt->Affect($v);
        $time_start = microtime(true);
        $err = $dt->executeTimerNow();
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        if ($err) {
            $action->log->error(sprintf("Error while executing timer %s (%d): %s in %.03f seconds", $dt->title, $dt->id, $err, $time));
            print sprintf("Error while executing timer %s (%d): %s in %.03f seconds", $dt->title, $dt->id, $err, $time);
        } else {
            $action->log->info(sprintf("Timer %s (%d) executed in %.03f seconds", $dt->title, $dt->id, $time));
            print sprintf("Timer %s (%d) executed in %.03f seconds", $dt->title, $dt->id, $time);
        }
    }
}

verifyExecDocuments($dbaccess);
verifyTimerDocuments($dbaccess);
?>