<?php
/**
 * save an answer of a ask
 *
 * @author Anakeen 2000 
 * @version $Id: fdl_card.php,v 1.42 2008/12/02 15:20:52 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Dir.php");


/**
 * View a document
 * @param Action &$action current action
 * @global docid Http var : document identificator where use the ask
 * @global waskid Http var : document identificator of the ask
 * @global answer Http var : the answer for the question
 */
function setwask(&$action) {
  $docid = GetHttpVars("docid");
  $answers = GetHttpVars("answer");
  $dbaccess = $action->GetParam("FREEDOM_DB");
  if ($docid=="") $action->exitError(_("no document reference"));
  $doc = new_Doc($dbaccess, $docid);
  if (!$doc->isAlive()) $action->exitError(sprintf(_("unknow document reference '%s'"),GetHttpVars("docid")));
  $err=$doc->control("view");
  if ($err) $action->exitError($err);
  $task=array();
  foreach ($answers as $waskid=>$answer) {
    $wask=new_doc($dbaccess,$waskid);   
    $wask->set($doc);
    if ($wask->isAlive() && $wask->control('answer')=="") {
      $err=$doc->setWaskAnswer($waskid,$answer);
      if ($err) $action->addWarningMsg($err);
      else $task[]=array("ask"=>$wask->getValue("was_ask"),
			 "answer"=>implode(", ",$wask->getAskLabels($answer)));
    }
  }

  $action->lay->setBlockData("WASK",$task);
  $action->lay->set("docid",$doc->id);

}
?>