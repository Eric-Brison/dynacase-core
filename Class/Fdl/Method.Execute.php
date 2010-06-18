<?php
/**
 * Method for processes family
 *
 * @author Anakeen 2005
 * @version $Id: Method.Execute.php,v 1.9 2008/12/02 13:21:27 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */
/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
Class _EXEC extends Doc {
        /*
         * @end-method-ignore
         */
private $execuserid;
  /**
   * execute the action describe in the object
   * @return int shell status (0 means OK).
   */
function bgExecute($comment="") {

  if (! $this->canExecuteAction()) {
    AddWarningMsg(sprintf(_("Error : need edit privilege to execute")));
  } else {
    if (ini_get("max_execution_time") < 3600) ini_set("max_execution_time",3600); 
    $cmd= getWshCmd(true);
    $cmd.= " --api=fdl_execute";
    $cmd.= " --docid=".$this->id;
  
    $cmd.= " --userid=".$this->userid;
    if ($comment != "") $cmd.= " --comment=".base64_encode($comment); // prevent hack
 
    system($cmd,$status);
    if ($status==0) AddWarningMsg(sprintf(_("Process %s [%d] executed"),$this->title,$this->id));
    else AddWarningMsg(sprintf(_("Error : Process %s [%d]: status %d"),$this->title,$this->id,$status));
    return $status;
  }
  
}


function resetExecute() {  
  $this->deleteValue("exec_status");
  $this->deleteValue("exec_statusdate");
  $err=$this->modify();
  return $err;
}
function isInprogress() {  
  if ($this->canEdit()=="") {
    if ($this->getvalue("exec_status")=="progressing") return MENU_ACTIVE;
  }
  return MENU_INVISIBLE;
}
function postModify() {
  $this->setValue("exec_nextdate", $this->getNextExecDate());
}
  /**
   * return the wsh command which be send
   */
function bgCommand($masteruserid=false) {
  $bgapp=$this->getValue("exec_application");
  $bgact=$this->getValue("exec_action");
  $bgapi=$this->getValue("exec_api");

  $tp= $this->getAValues("exec_t_parameters");
  
  $cmd =  getWshCmd(true);
  if ($masteruserid) {
    $fuid=$this->getValue("exec_iduser");
    $fu=getTDoc($this->dbaccess,$fuid);
    $wuid=$fu["us_whatid"];
    $this->execuserid=$fuid;
  } else {
    $wuid=$this->userid;
    $this->execuserid=$this->getUserId();
  }
  $cmd.= " --userid=$wuid";
  if (!$bgapi) $cmd.= " --app=$bgapp --action=$bgact";
  else $cmd.= " --api=$bgapi";
  
  foreach ($tp as $k=>$v) {
    $b=sprintf(" --%s=\"%s\"",$v["exec_idvar"],str_replace("\"","'",$v["exec_valuevar"]));
    $cmd.=$b;
  }
  return $cmd;
}

/**
 * return the document user id for the next execution
 * @return string
 */
function getExecUserID() {
  return $this->execuserid;
}
/**
 * return the next date to execute process
 * @return date
 */
function getNextExecDate() {
  $ndh=$this->getValue("exec_handnextdate");
  if ($ndh=="") {
    $nday=$this->getValue("exec_periodday",0);
    $nhour=$this->getValue("exec_periodhour",0);
    $nmin=$this->getValue("exec_periodmin",0);
    if (($nday+$nhour+$nmin) > 0) {
      $ndh=$this->getDate($nday,$nhour,$nmin);
    } else {
      $ndh=" ";
    }
    
  }

  return $ndh;
}
function getPrevExecDate() {
  if ($this->revision > 0) {
    $pid=$this->latestId(true);
    $td=getTDoc($this->dbaccess,$pid);
    $ndh=getv($td,"exec_date");

    return $ndh;
  }  
}

function isLatestExec() {
  if ($this->locked == -1) return MENU_INVISIBLE;  
  if (! $this->canExecuteAction())  return MENU_INACTIVE;
  return  MENU_ACTIVE;
}


function canExecuteAction() {
  $err=$this->control('edit');
  return ($err=="");
}
/**
        * @begin-method-ignore
        * this part will be deleted when construct document class until end-method-ignore
        */
}

/*
 * @end-method-ignore
 */
?>