<?php
/**
 * Lock a document
 *
 * @author Anakeen 2000 
 * @version $Id: lockfile.php,v 1.6 2006/04/28 14:33:39 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
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
    
    $action->AddActionDone("LOCKDOC", $doc->id);
    $action->AddLogMsg(sprintf(_("%s has been locked"), $doc->title));
    // add events for  folders
    $fdlids = $doc->getParentFolderIds();
    foreach ( $fdlids as $fldid ) {
        $action->AddActionDone("MODFOLDERCONTAINT", $fldid);
    }
    
    redirect($action, "FDL", "FDL_CARD&id=" . $doc->id, $action->GetParam("CORE_STANDURL"));

}

?>
