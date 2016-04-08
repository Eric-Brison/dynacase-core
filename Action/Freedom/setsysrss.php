<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Set RSS usable for all users
 *
 * @author Anakeen
 * @version $Id: setsysrss.php,v 1.1 2006/11/27 11:43:04 marc Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */
include_once ("FDL/Class.Doc.php");

function setsysrss(Action & $action)
{
    // Set the globals elements
    $dbaccess = $action->dbaccess;
    $docid = GetHttpVars("id", 0); // document to edit
    $rss = new_Doc($dbaccess, $docid);
    if (is_object($rss) && $rss->isAffected()) {
        if ($rss->getValue("gui_sysrss") == "yes") {
            $rss->setValue("gui_sysrss", "no");
            $msg = _("rss unavaible for users");
        } else {
            $rss->setValue("gui_isrss", "yes");
            $rss->setValue("gui_sysrss", "yes");
            $msg = _("rss avaible for users");
        }
        AddWarningMsg($msg);
        $rss->modify(true, array(
            "gui_isrss",
            "gui_sysrss"
        ) , true);
    }
    redirect($action, "FDL", "FDL_CARD&id=$docid", $action->GetParam("CORE_STANDURL"));
}
