<?php
/**
 * Get Values in XML form
 *
 * @author Anakeen 2006
 * @version $Id: getdocvalues.php,v 1.4 2008/11/05 10:10:41 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage FDC
 */
 /**
 */



include_once("FDL/Class.Doc.php");


/**
 * Get  doc attributes values
 * @param Action &$action current action
 * @global id Http var : document id to view
 */
function getdocvalues(&$action) {
  header('Content-type: text/xml; charset=utf-8'); 

  $mb=microtime();
  $docid = GetHttpVars("id");
  $attrid = strtolower(GetHttpVars("attrid"));
  $dbaccess = $action->GetParam("FREEDOM_DB");

  $action->lay->set("warning","");

  $doc=new_doc($dbaccess,$docid);
  $tvalues=array();
  
  if (! $doc->isAlive()) $err=sprintf(_("document [%s] not found"),$docid);
  if ($err == "") {
    $err=$doc->control("view");
    if ($err == "") {
      if ($attrid) $values[$attrid]=$doc->getValue($attrid);
      else $values=$doc->getValues();
      foreach ($values as $aid=>$v) {
	$a=$doc->getAttribute($aid);
	if ($a->visibility != "I") {
	  $tvalues[]=array("attrid"=>$aid,
			   "value"=>xml_entity_encode($v));
	}
      }
    }
  }
  if ($err) $action->lay->set("warning",$err);
  
  $action->lay->setBlockData("VALUES",$tvalues);
  $action->lay->set("CODE","OK");
  $action->lay->set("count",1);
  $action->lay->set("delay",microtime_diff(microtime(),$mb));					

}
?>