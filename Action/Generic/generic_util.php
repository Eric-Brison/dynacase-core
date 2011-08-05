<?php
/**
 * Collection of utilities functions for GENERIC application
 *
 * @author Anakeen 2000 
 * @version $Id: generic_util.php,v 1.33 2008/11/14 12:43:12 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


include_once("FDL/Lib.Dir.php");  


function getDefFam(&$action) {
  
  // special for onefam application
  $famid=GetHttpVars("famid");
  if (! is_numeric($famid)) $famid=getIdFromName( $action->GetParam("FREEDOM_DB"),$famid);
  if ($famid != "") return $famid;

  $famid = $action->GetParam("DEFAULT_FAMILY", 1); 
  if ($famid==1) {
    $famid=$action->Read("DEFAULT_FAMILY", 0);
    $action->parent->SetVolatileParam("DEFAULT_FAMILY",$famid);
  }
  
  return $famid;
}

function getDefFld(&$action) {
  $famid=getDefFam($action);
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $fdoc = new_Doc($dbaccess,$famid);
  if ($fdoc->dfldid > 0) return $fdoc->dfldid;
  

  return 0;
}
// return attribute sort default
function getDefUSort(&$action,$def="title",$famid="") {
  if (!$famid) $famid=getDefFam($action);
  $pu = $action->GetParam("GENERIC_USORT");
  if ($pu) {
    $tu = explode("|",$pu);
    
    while (list($k,$v) = each($tu)) {
      list($afamid,$aorder,$sqlorder) = explode(":",$v);
      if( ! is_numeric($afamid) ) {
	$afamid = getFamIdFromName($action->getParam('FREEDOM_DB'), $afamid);
      }
      if( $afamid == $famid ) {
	return $aorder;
      }
    }
  }
  return $def;
}


// return parameters key search
function getDefUKey(&$action) {
  $famid=getDefFam($action);
  $pu = $action->GetParam("GENE_LATESTTXTSEARCH");
  if ($pu) {
    $tu = explode("|",$pu);
    while (list($k,$v) = each($tu)) {
      list($afamid,$aorder) = explode(":",$v);
      if ($afamid == $famid) return $aorder;
    }
  }
  return "";
}

/**
 * memorize search key for generic applications
 * @param Action $action current action
 * @param int fmily identificator
 * @param string $key key to memorize
 */
function setUkey(&$action, $famid, $key) {
  
  $famid=getDefFam($action);
  $dbaccess = $action->GetParam("FREEDOM_DB");


  $fdoc= new_Doc( $dbaccess, $famid);

  $pu = $action->GetParam("GENE_LATESTTXTSEARCH");
  $tr=array();
  if ($pu) {
    // disambled parameter
    $tu = explode("|",$pu);
    
    while (list($k,$v) = each($tu)) {
      list($afamid,$uk) = explode(":",$v);
      $tr[$afamid]=$uk;
    }
  }
  if (trim($key)=="") unset($tr[$famid]);
  else $tr[$famid]=$key;

  // rebuild parameter
  $tu=array();
  foreach($tr as $k=>$v) {
    $tu[]="$k:$v";
  }

  return implode("|", $tu);
}
/**
 * return parameters key search
 * @param action $action current action
 * @param string $key parameter name
 * return string the value of the parameter according to default family
*/
function getDefU(&$action,$key) {
  $famid=getDefFam($action);
  return getFamilyParameter($action,$famid,$key);
}

/**
 * return attribute split mode 
 * @return string [V|H] vertical or horizontal split according to family
 */
function getSplitMode(&$action,$famid="") {
  if ($famid=="") $famid=getDefFam($action);
  return getFamilyParameter($action,$famid,"GENE_SPLITMODE","V");
}

/**
 * return attribute view mode 
 * @return string [abstract|column]  according to family
 */
function getViewMode(&$action,$famid="") {
  if ($famid=="") $famid=getDefFam($action);
  return getFamilyParameter($action,$famid,"GENE_VIEWMODE","abstract");
}
/**
 * return attribute view tab letters
 * @return string [Y|N] Yes/No  according to family
 */
function getTabLetter(&$action,$famid="") {
  if ($famid=="") $famid=getDefFam($action);
  return getFamilyParameter($action,$famid,"GENE_TABLETTER","Y");
}
/**
 * return  if search is also in inherit famileis 
 * @return string [Y|N] Yes/No  according to family
 */
function getInherit(&$action,$famid="") {
  if ($famid=="") $famid=getDefFam($action);
  return getFamilyParameter($action,$famid,"GENE_INHERIT","Y");
}
/**
 * return  if search is also in inherit famileis 
 * @return string [Y|N] Yes/No  according to family
 */
function getSearchMode(&$action,$famid="") {
  if ($famid=="") $famid=getDefFam($action);
  return getFamilyParameter($action,$famid,"GENE_MODESEARCH","REGEXP");
}
/**
 * set attribute split mode
 * @param string $split [V|H]
 */
function setSplitMode(&$action,$famid,$split) {
  return setFamilyParameter($action,$famid,'GENE_SPLITMODE',$split);
}
/**
 * set attribute view mode
 * @param string $view [abstract|column]
 */
function setViewMode(&$action,$famid,$view) {
  return setFamilyParameter($action,$famid,'GENE_VIEWMODE',$view);
}
/**
 * set attribute view tab letters
 * @param string $letter [Y|N] Yes/No
 */
function setTabLetter(&$action,$famid,$letter) {
  return setFamilyParameter($action,$famid,'GENE_TABLETTER',$letter);
}
/**
 * set attribute view tab letters
 * @param string $inherit [Y|N] Yes/No
 */
function setInherit(&$action,$famid,$inherit) {
  return setFamilyParameter($action,$famid,'GENE_INHERIT',$inherit);
}
/**
 * set attribute search mode
 * @param string $split [REGEXP|FULL]
 */
function setSearchMode(&$action,$famid,$mode) {
  return setFamilyParameter($action,$famid,'GENE_MODESEARCH',$mode);
}
/**
 * return parameters key search for all familly
 * @param action $action current action
 * @param string $key parameter name
 * return array all values indexed by family id
*/
function getFamilyParameters(&$action,$key) { 
  $pu = $action->GetParam($key);
  $t=array();
  if ($pu) {
    $tu = explode(",",$pu);
    while (list($k,$v) = each($tu)) {
      list($afamid,$aorder) = explode("|",$v);
      $t[$afamid]=$aorder;
    }
  }
  return $t;
}
/**
 * return parameters key search
 * @param action $action current action
 * @param int $famid family identificator
 * @param string $key parameter name
 * return string the value of the parameter according to family
*/
function getFamilyParameter(&$action,$famid,$key,$def="") { 
  $pu = $action->GetParam($key);
  if ($pu) {
    $tu = explode(",",$pu);
    while (list($k,$v) = each($tu)) {
      list($afamid,$aorder) = explode("|",$v);
      if( ! is_numeric($afamid) ) {
	$afamid = getFamIdFromName($action->getParam('FREEDOM_DB'), $afamid);
      }
      if( $afamid == $famid ) {
	return $aorder;
      }
    }
  }
  return $def;
}
/**
 * set family attribute for generic application
 */
function setFamilyParameter(&$action,$famid,$attrid,$value) {
  $tmode= explode(",",$action->getParam($attrid));

  // explode parameters
  foreach($tmode as $k=>$v) {
    list($fid,$val)=explode("|",$v);
    $tview[$fid]=$val;
  }
  if ($tview[$famid]!=$value) {
    $tview[$famid]=$value;
    // implode parameters to change user preferences
    $tmode=array();
    foreach($tview as $k=>$v) {
      if ($k>0) $tmode[]="$k|$v";
    }
    $pmode=implode(",",$tmode);
    $action->setParamU($attrid,$pmode);
  }
}/**
 * delete family attribute for generic application
 */
function deleteFamilyParameter(&$action,$famid,$attrid) {
  $tmode= explode(",",$action->getParam($attrid));

  // explode parameters
  foreach($tmode as $k=>$v) {
    list($fid,$val)=explode("|",$v);
    $tview[$fid]=$val;
  }
  if ($tview[$famid]) {
    unset($tview[$famid]);
    // implode parameters to change user preferences
    $tmode=array();
    foreach($tview as $k=>$v) {
      if ($k>0) $tmode[]="$k|$v";
    }
    $pmode=implode(",",$tmode);
    $action->setParamU($attrid,$pmode);
  }
}

// -----------------------------------
function getChildCatg($docid, $level,$notfldsearch=false,$maxlevel=2) {
  // -----------------------------------
  global $dbaccess;
  global $action;

  $ltree=array();


  if ($level <= $maxlevel) {
    $ldir = getChildDir($dbaccess,$action->user->id,$docid, $notfldsearch,"TABLE");
  

    if (count($ldir) > 0 ) {
     
      while (list($k,$v) = each($ldir)) {
	$ltree[$v["id"]] = array("level"=>$level*20,
				 "id"=>$v["id"],
				 "doctype"=>$v["doctype"],
				 "fromid"=>$v["fromid"],
				 "title"=>$v["title"]);

	if ($v["doctype"] == "D") $ltree = $ltree +  getChildCatg($v["id"], $level+1, $notfldsearch,$maxlevel );
      }
    } 
  }
  return $ltree;
}

// -----------------------------------
function getSqlFrom($dbaccess, $docid) {
  // -----------------------------------
  $fdoc= new_Doc( $dbaccess, $docid);
  $child= $fdoc->GetChildFam();
  return GetSqlCond(array_merge(array($docid),array_keys($fdoc->GetChildFam())),"fromid");
  
}

?>