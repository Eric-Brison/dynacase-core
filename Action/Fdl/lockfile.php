<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Lock a document
 *
 * @author Anakeen 2000
 * @version $Id: lockfile.php,v 1.6 2006/04/28 14:33:39 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
function lockfile(&$action)
{
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $docid = GetHttpVars("id", 0);
    
    $action->lay->Set("docid", $docid);
    
    $doc = new_Doc($dbaccess, $docid);
    
    $err = $doc->lock();
    if ($err != "") $action->ExitError($err);
    
    $action->AddActionDone("LOCKFILE", $doc->id);
    $action->AddLogMsg(sprintf(_("%s has been locked") , $doc->title));
    
    redirect($action, "FDL", "FDL_CARD&id=" . $doc->id, $action->GetParam("CORE_STANDURL"));
}
?>
