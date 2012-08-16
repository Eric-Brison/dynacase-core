<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * FDL Forum open action
 *
 * @author Anakeen
 * @version $Id: fdl_forumopen.php,v 1.1 2007/10/15 10:05:35 marc Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */
include_once ("FDL/Class.Doc.php");
include_once ("FDL/freedom_util.php");

function fdl_forumopen(&$action)
{
    
    $docid = GetHttpVars("docid", "");
    
    $dbaccess = GetParam("FREEDOM_DB");
    
    if ($docid == "") $action->exitError(_("no document reference"));
    $doc = new_Doc($dbaccess, $docid);
    if (!$doc->isAffected()) $action->exitError(sprintf(_("cannot see unknow reference %s") , $docid));
    
    if ($doc->Control("edit") != "") $action->exitError(sprintf(_("you don't have privilege to edit forum for document %s") , $doc->title));
    
    $doc->forumid = abs($doc->forumid);
    
    $doc->modify(true, array(
        "forumid"
    ));
    
    redirect($action, "FDL", "FDL_CARD&sole=Y&id=" . $doc->id);
}
?>