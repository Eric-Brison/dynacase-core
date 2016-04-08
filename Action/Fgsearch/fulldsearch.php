<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Full Text Search document
 *
 * @author Anakeen
 * @version $Id: fulldsearch.php,v 1.2 2007/12/06 10:51:35 eric Exp $
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
 * @global keyword string Http var : word to search in any values
 * @global famid int Http var : restrict to this family identioficator
 * @global start int Http var : page number
 * @global dirid int Http var : search identifier
 */
function fulldsearch(Action & $action)
{
    
    $famid = GetHttpVars("famid", 0);
    
    $dbaccess = $action->dbaccess;
    
    if ($famid > 0) {
        $fdoc = new_doc($dbaccess, $famid);
        if (!$fdoc->isAffected()) $action->exitError(sprintf(_("Family %s not exist") , $famid));
        $search = createTmpDoc($dbaccess, 16);
        $search->setValue("se_famid", $famid);
        $search->setValue("se_latest", "yes");
        
        setPostVars($search);
        
        $err = $search->Add();
        
        if ($err != "") $action->exitError($err);
        $search->preRefresh();
        
        setHttpVar("dirid", $search->id);
        fullsearchresult($action);
    }
}
