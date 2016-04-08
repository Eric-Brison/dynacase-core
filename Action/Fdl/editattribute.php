<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Specific menu for family
 *
 * @author Anakeen
 * @version $Id: editattribute.php,v 1.4 2006/11/13 16:06:39 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
/**
 * Edit an attribute inline
 * @param Action &$action current action
 * @global string $docid Http var : document identifier to see
 * @global string $attrid Http var : the id of attribute to edit
 */
function editattribute(Action & $action)
{
    $docid = $action->getArgument("docid");
    $attrid = $action->getArgument("attrid");
    $modjsft = $action->getArgument("modjsft", "modattr");
    $dbaccess = $action->dbaccess;
    
    header('Content-type: text/xml; charset=utf-8');
    
    $mb = microtime();
    
    $action->lay->set("CODE", "OK");
    $action->lay->set("warning", "");
    if ($modjsft == "undefined") $modjsft = "modattr";
    $action->lay->eset("modjsft", $modjsft);
    $err = '';
    $doc = new_Doc($dbaccess, $docid);
    if (!$doc->isAffected()) $err = sprintf(_("cannot see unknow reference %s") , $docid);
    if ($err == "") {
        $action->lay->set("docid", $doc->id);
        $err = $doc->lock(true); // autolock
        if ($err == "") $action->AddActionDone("LOCKDOC", $doc->id);
        if ($err != "") {
            // test object permission before modify values (no access control on values yet)
            $err = $doc->canEdit();
        }
        
        if ($err == "") {
            $a = $doc->getAttribute($attrid);
            if (!$a) $err = sprintf(_("unknown attribute %s for document %s") , $attrid, $doc->title);
            $action->lay->set("attrid", $a->id);
            $action->lay->set("longtext", ($a->type == "longtext"));
            if ($err == "") {
            }
        }
        $action->lay->set("thetext", htmlspecialchars($doc->getRawValue($attrid)));
    }
    
    if ($err != "") $action->lay->set("CODE", "KO");
    $action->lay->set("warning", $err);
    $action->lay->set("delay", microtime_diff(microtime() , $mb));
    // notify actions done
    $action->getActionDone($actcode, $actarg);
    $tact = array();
    foreach ($actcode as $k => $v) {
        $tact[] = array(
            "acode" => $v,
            "aarg" => $actarg[$k]
        );
    }
    $action->lay->setBlockData("ACTIONS", $tact);
    $action->clearActionDone();
}
