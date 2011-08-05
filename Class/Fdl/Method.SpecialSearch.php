<?php
/**
 * Detailled search
 *
 * @author Anakeen 2000 
 * @version $Id: Method.SpecialSearch.php,v 1.3 2007/08/01 14:07:12 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */



/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
Class _SSEARCH extends DocSearch {
        /*
         * @end-method-ignore
         */

var $defaultedit= "FDL:EDITBODYCARD";
var $defaultview= "FDL:VIEWBODYCARD"; 




/**
  * return sql query to search wanted document
  */
function ComputeQuery($keyword="",$famid=-1,$latest="yes",$sensitive=false,$dirid=-1, $subfolder=true) {
    
  

  return true;
}


function getDocList($start=0, $slice="ALL", $qtype="TABLE",$userid="") {
  $phpfile=$this->getValue("se_phpfile");
  $phpfunc=$this->getValue("se_phpfunc");
  $phparg=$this->getValue("se_phparg");
  if (! include_once("EXTERNALS/$phpfile")) {
    global $action;
    $action->AddWarningMsg(sprintf(_("php file %s needed for request not found"),"EXTERNALS/$phpfunc"));
    return false;
  }
  if (! $userid) $userid=$this->getSystemUserId();
  $arg=array($start,$slice,$userid);
  if ($phparg!="") {
    $moreargs=explode(",",$phparg);
    foreach ($moreargs as $k=>$v) {
      $v=trim($v);
      if ($v) {
	if (($v[0]=="%") && (substr($v,-1)=="%")) {
	  $aid=substr($v,1,-1);
	  if ($aid=="THIS") $moreargs[$k]=&$this;
	  else {
	    $val=$this->getValue($aid);
	    if (!$val) $val=$this->getProperty($aid);
	    $moreargs[$k]=$val;
	  }
	}
      }
    }
    $arg=array_merge($arg,$moreargs);
  }
  $res = call_user_func_array($phpfunc, $arg);

  return($res);

}

/**
 * return true if the search has parameters
 */
function isParameterizable() {
  return false;
}
/**
 * return true if the search need parameters
 */
function needParameters() {
  return false;
}

function isStaticSql() {
  return true;
}

  /**
   * return document includes in search folder
   * @param bool $controlview if false all document are returned else only visible for current user  document are return
   * @param array $filter to add list sql filter for selected document
   * @param int $famid family identificator to restrict search 
   * @return array array of document array
   */
function getContent($controlview=true,$filter=array(),$famid="") {
  if ($controlview) $uid=1;
  return $this->getDocList(0,"ALL",$uid);
}
/**
   * return number of item in this searches
   * @return int -1 if errors
   */
function count() {
  $t=$this->getContent();
  if (is_array($t)) return count($t);
  return -1;
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