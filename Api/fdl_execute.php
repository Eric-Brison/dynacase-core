<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Execute Freedom Processes
 *
 * @author Anakeen
 * @version $Id: fdl_execute.php,v 1.6 2008/10/02 15:42:43 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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

$dbaccess = $appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
    print "Database not found : param FREEDOM_DB";
    exit;
}

$usage = new ApiUsage();
$usage->setText("Execute Freedom Processes");
$docid = $usage->addOption("docid", "special docid", null, 0);
$comment = base64_decode($usage->addOption("comment", "additionnal comment", null, ""));
$usage->verify();

if (($docid == 0) && (!is_numeric($docid))) $docid = getFamIdFromName($dbaccess, $docid);

if ($docid > 0) {
    $doc = new_Doc($dbaccess, $docid);
    if ($doc->locked == - 1) { // it is revised document
        $doc = new_Doc($dbaccess, $doc->latestId());
    }
    
    $doc->setValue("exec_status", "progressing");
    $doc->setValue("exec_statusdate", $doc->getTimeDate());
    $doc->modify(true, array(
        "exec_status",
        "exec_statusdate"
    ) , true);
    $cmd = $doc->bgCommand($action->user->id == 1);
    $f = uniqid(getTmpDir() . "/fexe");
    $fout = "$f.out";
    $ferr = "$f.err";
    $cmd.= ">$fout 2>$ferr";
    $m1 = microtime();
    system($cmd, $statut);
    $m2 = microtime_diff(microtime() , $m1);
    $ms = gmstrftime("%H:%M:%S", $m2);
    
    if (file_exists($fout)) {
        $doc->setValue("exec_detail", file_get_contents($fout));
        unlink($fout);
    }
    if (file_exists($ferr)) {
        $doc->setValue("exec_detaillog", file_get_contents($ferr));
        unlink($ferr);
    }
    
    $doc->deleteValue("exec_nextdate");
    $doc->setValue("exec_elapsed", $ms);
    $doc->setValue("exec_date", date("d/m/Y H:i "));
    $doc->deleteValue("exec_status");
    $doc->deleteValue("exec_statusdate");
    $doc->setValue("exec_state", (($statut == 0) ? "OK" : $statut));
    $puserid = $doc->getValue("exec_iduser"); // default exec user
    $doc->setValue("exec_iduser", $doc->getExecUserID());
    $doc->refresh();
    $err = $doc->modify();
    if ($err == "") {
        if ($comment != "") $doc->AddComment($comment);
        $err = $doc->AddRevision(sprintf(_("execution by %s done %s") , $doc->getTitle($doc->getExecUserID()) , $statut));
        if ($err == "") {
            $doc->deleteValue("exec_elapsed");
            $doc->deleteValue("exec_detail");
            $doc->deleteValue("exec_detaillog");
            $doc->deleteValue("exec_date");
            $doc->deleteValue("exec_state");
            $doc->setValue("exec_iduser", $puserid);
            $doc->refresh();
            $err = $doc->modify();
        }
    } else {
        $doc->AddComment($err, HISTO_ERROR);
    }
    
    if ($err != "") exit(1);
}
?>