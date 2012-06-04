<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Enable/disable forum for documents
 *
 * @author Anakeen 2000
 * @version $Id: setlogicalname.php,v 1.3 2008/09/26 12:34:57 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Class.Doc.php");

function setlogicalname(Action & $action)
{
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $docid = GetHttpVars("id");
    $name = GetHttpVars("name");
    if ($docid && $name) {
        $doc = new_Doc($dbaccess, $docid, true);
        if (!$doc->isAlive()) {
            $action->addWarningMsg(sprintf(_("Document %s is not alive") , $doc->getTitle()));
        } else {
            $oldName = $doc->name;
            $err = $doc->setLogicalIdentificator($name, true);
            if ($err != "") $action->addWarningMsg($err);
            else {
                if ($oldName) {
                    $doc->addComment(sprintf(_("update logical name from %s to %s") , $oldName, $doc->name));
                } else {
                    $doc->addComment(sprintf(_("set logical name to %s") , $doc->name));
                }
            }
        }
    }
    redirect($action, "FDL", "IMPCARD&zone=FDL:VIEWPROPERTIES:T&id=" . $docid);
}
?>