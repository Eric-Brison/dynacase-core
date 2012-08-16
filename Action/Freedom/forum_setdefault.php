<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Enable/disable forum for documents
 *
 * @author Anakeen
 * @version $Id: forum_setdefault.php,v 1.1 2007/10/16 04:52:09 marc Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Lib.Dir.php");
include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocAttr.php");

function forum_setdefault(&$action)
{
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $docid = GetHttpVars("id", "");
    $state = GetHttpVars("st", "N");
    
    if ($docid == "") $action->exitError(_("no document reference"));
    $doc = new_Doc($dbaccess, $docid);
    if (!$doc->isAffected()) $action->exitError(sprintf(_("cannot see unknow reference %s") , $docid));
    if ($doc->doctype != 'C') $action->exitError(_("no document family") . " " . $docid);
    
    if ($state == "Y") $doc->forumid = 0;
    else $doc->forumid = "";
    
    $err = $doc->modify(true, array(
        "forumid"
    ));
    if ($err != "") $action->exitError($err);
    
    redirect($action, "FDL", "FDL_CARD&sole=Y&props=N&abstract=N&id=" . $docid);
}
?>
