<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * add new logical name
 *
 * @author Anakeen
 * @version $Id: setlogicalname.php,v 1.3 2008/09/26 12:34:57 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Class.Doc.php");

function setlogicalname(Action & $action)
{
    $dbaccess = $action->dbaccess;
    $docid = GetHttpVars("id");
    $name = GetHttpVars("name");
    if ($docid) {
        $doc = new_Doc($dbaccess, $docid, true);
        if (!$doc->isAlive()) {
            $action->addWarningMsg(sprintf(_("Document %s is not alive") , $doc->getTitle()));
        } else {
            $oldName = $doc->name;
            $err = $doc->setLogicalName($name, true);
            if ($err != "") {
                $action->addWarningMsg($err);
            } elseif (!empty($name)) {
                if ($oldName) {
                    $doc->addHistoryEntry(sprintf(_("update logical name from %s to %s") , $oldName, $doc->name));
                } else {
                    $doc->addHistoryEntry(sprintf(_("set logical name to %s") , $doc->name));
                }
            }
        }
    }
    redirect($action, "FDL", "IMPCARD&zone=FDL:VIEWPROPERTIES:T&id=" . $docid);
}
