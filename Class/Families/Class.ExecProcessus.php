<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
            setMaxExecutionTimeTo(3600);
            $cmd = getWshCmd(true);
            $cmd.= " --api=fdl_execute";
            $cmd.= " --docid=" . $this->id;
            
            $cmd.= " --userid=" . $this->userid;
            if ($comment != "") $cmd.= " --comment=" . base64_encode($comment); // prevent hack
            $time_start = microtime(true);
            system($cmd, $status);
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
}