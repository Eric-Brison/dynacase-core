<?php
/*
 * @author Anakeen
 * @package FDL
*/


include_once ("FDL/Class.Dir.php");
/**
 * add a user tag to a document
 * @param Action &$action current action
 * @global string $docid Http var : document identifier to use
 * @global string $tag Http var : tag name
 * @global string $value Http var : tag value
 */
function fdl_setusertag(Action & $action)
{
    // -----------------------------------
    $usage = new ActionUsage($action);
    $usage->setDefinitionText("add user tag to a document");
    $docid = $usage->addRequiredParameter("id", "document identifier");
    $tagName = $usage->addRequiredParameter("tag", "tag name");
    $tagValue = $usage->addOptionalParameter("value", "tag value");
    $usage->verify();
    $dbaccess = $action->dbaccess;
    
    if (!is_numeric($docid)) $docid = getIdFromName($dbaccess, $docid);
    if (intval($docid) == 0) $action->exitError(sprintf(_("unknow logical reference '%s'") , $docid));
    $doc = new_Doc($dbaccess, $docid);
    if (!$doc->isAffected()) $action->exitError(sprintf(_("cannot see unknow reference %s") , $docid));
    $err = $doc->control("view");
    if ($err != "") $action->exitError($err);
    
    $err = $doc->addUtag($action->user->id, $tagName, $tagValue);
    $out = array(
        "error" => $err
    );
    header('Content-type: application/json');
    $action->lay->template = json_encode($out);
    $action->lay->noparse = true;
}