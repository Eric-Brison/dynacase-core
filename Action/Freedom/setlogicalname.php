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
        if (!preg_match('/^[A-Z][[0-9A-Z\-_]*$/i', $name)) {
            $action->addWarningMsg(sprintf(_("name must containt only alphanumeric characters: invalid  [%s]") , $name));
        } else {
            $doc = new_Doc($dbaccess, $docid, true);
            if (!$doc->isAffected()) $action->addWarningMsg(sprintf(_("cannot see unknow reference %s") , $docid));
            else {
                $oldName = $doc->name;
                if (!$doc->isAlive()) {
                    $action->addWarningMsg(sprintf(_("Document %s is not dead") , $doc->getTitle()));
                } else {
                    // verify not use yet
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
        }
    }
    redirect($action, "FDL", "IMPCARD&zone=FDL:VIEWPROPERTIES:T&id=" . $docid);
}
?>