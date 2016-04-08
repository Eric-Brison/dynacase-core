<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Execute an action in a batch documents
 *
 * @author Anakeen
 * @version $Id: batchexec.php,v 1.1 2005/09/09 16:25:46 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Dir.php");
/**
 * Batch execute
 * @param Action &$action current action
 * @global id int Http var : document identifier folder
 * @global saction string Http var : action name to execute
 * @global sapp string Http var : app name to execute
 */
function batchexec(Action & $action)
{
    // -----------------------------------
    $docid = GetHttpVars("id");
    $latest = GetHttpVars("latest");
    $saction = GetHttpVars("saction");
    $sapp = GetHttpVars("sapp");
    $dbaccess = $action->dbaccess;
    /**
     * @var Dir $doc
     */
    $doc = new_Doc($dbaccess, $docid);
    if (!$doc->isAlive()) $action->exitError(sprintf(_("batcexec aborted\ndocument [%s] not found") , $docid));
    
    $l = $doc->getContent();
    
    $de = createDoc($dbaccess, "EXEC");
    if (!$de) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document") , "EXEC"));
    
    $de->setValue("exec_idref", $doc->id);
    $de->setValue("exec_ref", $doc->title);
    $de->setValue("exec_application", $sapp);
    $de->setValue("exec_action", $saction);
    $de->setValue("exec_idvar", array(
        "wshfldid"
    ));
    $de->setValue("exec_valuevar", array(
        $doc->initid
    ));
    
    $err = $de->Add();
    if ($err != "") $action->exitError($err);
    
    redirect($action, "FDL", "FDL_METHOD&method=bgExecute&id=" . $de->id, $action->GetParam("CORE_STANDURL"));
}
