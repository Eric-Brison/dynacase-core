<?php
/**
 * Verify constraint on special attribute
 *
 * @author Anakeen 2003
 * @version $Id: vconstraint.php,v 1.6 2008/02/27 08:55:53 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
 /**
 */

include_once("FDL/Class.DocFam.php");
include_once("FDL/modcard.php");

function vconstraint(&$action) {

  
  $docid = GetHttpVars("id",0);
  $famid=GetHttpVars("famid",GetHttpVars("classid"));
  $attrid=GetHttpVars("attrid");
  $index = GetHttpVars("index",-1); // index of the attributes for arrays
  $domindex = GetHttpVars("domindex",""); // index in dom of the attributes for arrays

  if ($index === "") $index=-1;

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $dbaccess = $action->GetParam("FREEDOM_DB");


  if ($docid > 0) {
    $doc = new_Doc($dbaccess, $docid);


    

  } else {

    $doc = createDoc($dbaccess, $famid,false);
  }
  setPostVars($doc);
  


  $res=$doc->verifyConstraint($attrid,$index);

  if (is_array($res)) { // error with suggestion
    
    $action->lay->Set("error", $res["err"]);
    $action->lay->Set("iserror",($res["err"]=="")?"":"ko");
    $rargids=array($attrid);
    while (list($k, $v) = each($rargids)) {
      $rargids[$k].=$domindex;
    }
    $sattrid="[";
    $sattrid.= strtolower("'".implode("','", $rargids)."'");
    $sattrid.="]";
    $action->lay->Set("attrid", $sattrid);

    // list suggestion
    $tres=array();
    if (is_array($res["sug"])) {
      foreach ($res["sug"] as $sug) {
	$tres[]= array($sug, $sug);
      }
    }

    $action->lay->set("suggest", (count($tres)>0));
    // view possible correction
    while (list($k, $v) = each($tres)) {
      $tselect[$k]["choice"]= htmlentities($v[0]);
      $tselect[$k]["cindex"]= $k;
      $tval[$k]["index"]=$k;
      array_shift($v);

      foreach($v as $kv=>$vv) {
	$v[$kv]=addslashes($vv);
      }
      $tval[$k]["attrv"]="['".implode("','", $v)."']";
    

    
    }
    $action->lay->SetBlockData("SELECT", $tselect);
    $action->lay->SetBlockData("ATTRVAL", $tval);
  }
  
  
}