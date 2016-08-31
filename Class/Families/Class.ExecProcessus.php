<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Method for processes family
 *
 */
namespace Dcp\Core;
class ExecProcessus extends \Dcp\Family\Document
{
    private $execuserid;
    /**
     * execute the action describe in the object
     * @apiExpose
     * @param string $comment
     * @return int shell status (0 means OK).
     */
    function bgExecute($comment = "")
    {
        /**
         * @var \Action $action
         */
        global $action;
        
        if (!$this->canExecuteAction()) {
            AddWarningMsg(sprintf(_("Error : need edit privilege to execute")));
        } else {
            return $this->_execute($action, $comment);
        }
        return -2;
    }
    /**
     * cancel next execution
     * @apiExpose
     * @return string
     */
    function resetExecute()
    {
        $this->clearValue("exec_status");
        $this->clearValue("exec_statusdate");
        $err = $this->modify();
        return $err;
    }
    
    function isInprogress()
    {
        if ($this->canEdit() == "") {
            if ($this->getRawValue("exec_status") == "progressing") return MENU_ACTIVE;
        }
        return MENU_INVISIBLE;
    }
    
    function postStore()
    {
        $this->setValue("exec_nextdate", $this->getNextExecDate());
    }
    /**
     * return the wsh command which be send
     */
    function bgCommand($masteruserid = false)
    {
        $bgapp = $this->getRawValue("exec_application");
        $bgact = $this->getRawValue("exec_action");
        $bgapi = $this->getRawValue("exec_api");
        
        $tp = $this->getArrayRawValues("exec_t_parameters");
        
        $cmd = getWshCmd(true);
        if ($masteruserid) {
            $fuid = $this->getRawValue("exec_iduser");
            $fu = getTDoc($this->dbaccess, $fuid);
            $wuid = $fu["us_whatid"];
            $this->execuserid = $fuid;
        } else {
            $wuid = $this->userid;
            $this->execuserid = $this->getUserId();
        }
        $cmd.= " --userid=$wuid";
        if (!$bgapi) $cmd.= sprintf(" --app=%s --action=%s", escapeshellarg($bgapp) , escapeshellarg($bgact));
        else $cmd.= sprintf(" --api=%s", escapeshellarg($bgapi));
        
        foreach ($tp as $k => $v) {
            $b = sprintf(" --%s=%s", escapeshellarg($v["exec_idvar"]) , escapeshellarg($v["exec_valuevar"]));
            $cmd.= $b;
        }
        return $cmd;
    }
    /**
     * return the document user id for the next execution
     * @return string
     */
    function getExecUserID()
    {
        return $this->execuserid;
    }
    /**
     * return the next date to execute process
     * @return string date
     */
    function getNextExecDate()
    {
        $ndh = $this->getRawValue("exec_handnextdate");
        if ($ndh == "") {
            $nday = intval($this->getRawValue("exec_periodday", 0));
            $nhour = intval($this->getRawValue("exec_periodhour", 0));
            $nmin = intval($this->getRawValue("exec_periodmin", 0));
            if (($nday + $nhour + $nmin) > 0) {
                $ndh = $this->getDate($nday, $nhour, $nmin);
            } else {
                $ndh = " ";
            }
        }
        
        return $ndh;
    }
    
    function getPrevExecDate()
    {
        if ($this->revision > 0) {
            $pid = $this->getLatestId(true);
            $td = getTDoc($this->dbaccess, $pid);
            $ndh = getv($td, "exec_date");
            
            return $ndh;
        }
        return '';
    }
    
    function isLatestExec()
    {
        if ($this->locked == - 1) return MENU_INVISIBLE;
        if (!$this->canExecuteAction()) return MENU_INACTIVE;
        return MENU_ACTIVE;
    }
    
    function canExecuteAction()
    {
        $err = $this->control('edit');
        return ($err == "");
    }
    
    public function executeNow()
    {
        /**
         * Logging in bgexecute
         */
        $status = $this->bgExecute(_("dynacase cron try execute"));
        $del = new_Doc($this->dbaccess, $this->getLatestId(false, true));
        /**
         * @var \Dcp\Family\EXEC $del
         */
        $del->clearValue("exec_status");
        $del->clearValue("exec_handnextdate");
        $err = $del->store();
        
        if ($status == 0) {
            print sprintf("Execute %s [%d] (%s) : %s\n", $del->title, $del->id, $del->getRawValue("exec_handnextdate") , $err);
        } else {
            print sprintf("Error executing %s [%d] (%s) : %s (%s)\n", $del->title, $del->id, $del->getRawValue("exec_handnextdate") , $err, $status);
        }
    }
    
    public function _execute(\Action & $action, $comment = '')
    {
        setMaxExecutionTimeTo(3600);
        /*
        $cmd = getWshCmd(true);
        $cmd.= " --api=fdl_execute";
        $cmd.= " --docid=" . $this->id;
        
        $cmd.= " --userid=" . $this->userid;
        if ($comment != "") $cmd.= " --comment=" . base64_encode($comment); // prevent hack
        */
        $time_start = microtime(true);
        // system($cmd, $status);
        $status = $this->__execute($action, $this->id, $comment);
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        if ($status == 0) {
            AddWarningMsg(sprintf(_("Process %s [%d] executed") , $this->title, $this->id));
            $action->log->info(sprintf(_("Process %s [%d] executed in %.03f seconds") , $this->title, $this->id, $time));
        } else {
            AddWarningMsg(sprintf(_("Error : Process %s [%d]: status %d") , $this->title, $this->id, $status));
            $action->log->error(sprintf(_("Error : Process %s [%d]: status %d in %.03f seconds") , $this->title, $this->id, $status, $time));
        }
        return $status;
    }
    
    public function __execute(\Action & $action, $docid, $comment = '')
    {
        $doc = new_Doc($action->dbaccess, $docid);
        /**
         * @var \Dcp\Family\EXEC $doc
         */
        if ($doc->locked == - 1) { // it is revised document
            $doc = new_Doc($action->dbaccess, $doc->getLatestId());
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
        
        $doc->clearValue("exec_nextdate");
        $doc->setValue("exec_elapsed", $ms);
        $doc->setValue("exec_date", date("d/m/Y H:i "));
        $doc->clearValue("exec_status");
        $doc->clearValue("exec_statusdate");
        $doc->setValue("exec_state", (($statut == 0) ? "OK" : $statut));
        $puserid = $doc->getRawValue("exec_iduser"); // default exec user
        $doc->setValue("exec_iduser", $doc->getExecUserID());
        $doc->refresh();
        $err = $doc->modify();
        if ($err == "") {
            if ($comment != "") $doc->addHistoryEntry($comment);
            $err = $doc->revise(sprintf(_("execution by %s done %s") , $doc->getTitle($doc->getExecUserID()) , $statut));
            if ($err == "") {
                $doc->clearValue("exec_elapsed");
                $doc->clearValue("exec_detail");
                $doc->clearValue("exec_detaillog");
                $doc->clearValue("exec_date");
                $doc->clearValue("exec_state");
                $doc->setValue("exec_iduser", $puserid);
                $doc->refresh();
                $err = $doc->modify();
            }
        } else {
            $doc->addHistoryEntry($err, HISTO_ERROR);
        }
        
        if ($err != "") return 1;
        return 0;
    }
}
