<?php
/**
 * Speed Search
 *
 * @author Anakeen 2000 
 * @version $Id: speedsearch.php,v 1.5 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */




include_once("FDL/Lib.Dir.php");



// -----------------------------------
function speedsearch(&$action) {
  // -----------------------------------

  $dbaccess = $action->GetParam("FREEDOM_DB");

  // Get all the params      
  $dir=GetHttpVars("dirid"); // insert search in this folder
  
  $action->lay->Set("dirid", $dir);

  $idsfam = $action->GetParam("FREEDOM_PREFFAMIDS");


  if ($idsfam != "") {
    $tidsfam = explode(",",$idsfam);

    $selectclass=array();
    while (list($k,$cid)= each ($tidsfam)) {
      $cdoc= new_Doc($dbaccess, $cid);
     
	$selectclass[$k]["idcdoc"]=$cdoc->initid;
	$selectclass[$k]["classname"]=$cdoc->title;
      
      
    }
    $action->lay->SetBlockData("SELECTPREFCLASS", $selectclass);
  }

  $tclassdoc=GetClassesDoc($dbaccess, $action->user->id,array(1,2),"TABLE");

  while (list($k,$cdoc)= each ($tclassdoc)) {
    $selectclass[$k]["idcdoc"]=$cdoc["initid"];
    $selectclass[$k]["classname"]=$cdoc["title"];
  }
  
  $action->lay->SetBlockData("SELECTCLASS", $selectclass);
  
}


?>