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
        if (!preg_match("/^[A-Z][[0-9A-Z\-_]*$/i", $name)) {
            $action->addWarningMsg(sprintf(_("name must containt only alphanumeric characters: invalid  [%s]") , $name));
        } else {
            $doc = new_Doc($dbaccess, $docid, true);
            if (!$doc->isAffected()) $action->addWarningMsg(sprintf(_("cannot see unknow reference %s") , $docid));
            else {
                if (($doc->name != "") && ($doc->doctype != 'Z')) {
                    $action->addWarningMsg(sprintf(_("Logical name %s already set for %s") , $name, $doc->title));
                } else {
                    // verify not use yet
                    $q = $doc->exec_query("select id from doc where doctype != 'Z' and name='" . pg_escape_string($name) . "'");
                    if ($doc->numrows() == 0) {
                        $doc->name = $name;
                        $err = $doc->modify(true, array(
                            "name"
                        ) , true);
                        if ($err != "") $action->addWarningMsg($err);
                    } else {
                        $action->addWarningMsg(sprintf(_("Logical name %s already use other document") , $name, $doc->title));
                    }
                }
            }
        }
    }
    
    redirect($action, "FDL", "IMPCARD&zone=FDL:VIEWPROPERTIES:T&id=" . $docid);
}
?>