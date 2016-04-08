<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * save an answer of a ask
 *
 * @author Anakeen
 * @version $Id: fdl_card.php,v 1.42 2008/12/02 15:20:52 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Dir.php");
/**
 * View a document
 * @param Action &$action current action
 * @global docid int Http var : document identifier where use the ask
 * @global waskid int Http var : document identifier of the ask
 * @global answer string Http var : the answer for the question
 */
function setwask(Action & $action)
{
    $docid = GetHttpVars("docid");
    $answers = GetHttpVars("answer");
    $dbaccess = $action->dbaccess;
    if ($docid == "") $action->exitError(_("no document reference"));
    $doc = new_Doc($dbaccess, $docid);
    if (!$doc->isAlive()) $action->exitError(sprintf(_("unknow document reference '%s'") , GetHttpVars("docid")));
    $err = $doc->control("view");
    if ($err) $action->exitError($err);
    $task = array();
    foreach ($answers as $waskid => $answer) {
        $wask = new_doc($dbaccess, $waskid);
        $wask->set($doc);
        if ($wask->isAlive() && $wask->control('answer') == "") {
            $err = $doc->setWaskAnswer($waskid, $answer);
            if ($err) $action->addWarningMsg($err);
            else $task[] = array(
                "ask" => $wask->getRawValue("was_ask") ,
                "answer" => implode(", ", $wask->getAskLabels($answer))
            );
        }
    }
    
    $action->lay->setBlockData("WASK", $task);
    $action->lay->set("docid", $doc->id);
}
