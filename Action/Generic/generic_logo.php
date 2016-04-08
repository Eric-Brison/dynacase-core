<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Display logo
 *
 * @author Anakeen
 * @version $Id: generic_logo.php,v 1.8 2007/01/04 16:44:23 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("GENERIC/generic_util.php");

function generic_logo(Action & $action)
{
    $action->lay->Set("apptitle", "");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/geometry.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/resizeimg.js");
    $action->lay->Set("appicon", false);
    $action->lay->Set("apptitle", "");
    $famid = getDefFam($action);
    if ($famid > 0) {
        $dbaccess = $action->dbaccess;
        $doc = new_Doc($dbaccess, $famid);
        $action->lay->eSet("appicon", $doc->getIcon());
        $action->lay->eSet("apptitle", $doc->title);
    }
}
