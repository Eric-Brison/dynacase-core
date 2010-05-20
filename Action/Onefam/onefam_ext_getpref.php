<?php
/**
 * Retrieve family information for onefam
 *
 * @author Anakeen 2009
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package ECM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Dir.php");
include_once("FDL/Class.SearchDoc.php");


/**
 *  Retrieve families from onefam
 *
 * @param Action &$action current action
 * @global appid Http var : application name

 */

function onefam_ext_getpref(&$action,$idsattr="ONEFAM_IDS") {
  
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $ids=explode(",",$action->getParam($idsattr));

  //$umids=explode(",",$action->getParam("ONEFAM_IDS"));
 
  $out=array("ids"=>$ids);  
  
  $action->lay->noparse=true; // no need to parse after - increase performances
  $action->lay->template= json_encode($out);
  
}

function onefam_ext_getmasterpref(&$action) {
	onefam_ext_getpref($action,"ONEFAM_MIDS");	
}

?>