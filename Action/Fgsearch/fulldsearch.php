<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Full Text Search document
 *
 * @author Anakeen
 * @version $Id: fulldsearch.php,v 1.2 2007/12/06 10:51:35 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FGSEARCH/fullsearchresult.php");
include_once ("FDL/modcard.php");
/**
 * Fulltext Search document
 * @param Action &$action current action
 * @global keyword Http var : word to search in any values
 * @global famid Http var : restrict to this family identioficator
 * @global start Http var : page number
 * @global dirid Http var : search identificator
 */
function fulldsearch(&$action)
{
    
    $famid = GetHttpVars("famid", 0);
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    if ($famid > 0) {
        $fdoc = new_doc($dbaccess, $famid);
        if (!$fdoc->isAffected()) $action->exitError(sprintf(_("Family %s not exist") , $famid));
        $search = createTmpDoc($dbaccess, 16);
        $search->setValue("se_famid", $famid);
        $search->setValue("se_latest", "yes");
        
        setPostVars($search);
        
        $err = $search->Add();
        
        if ($err != "") $action->exitError($err);
        $search->SpecRefresh();
        
        setHttpVar("dirid", $search->id);
        fullsearchresult($action);
    }
}
?>