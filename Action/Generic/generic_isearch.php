<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Searches of referenced documents
 *
 * @author Anakeen
 * @version $Id: generic_isearch.php,v 1.13 2007/09/07 07:23:57 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.DocSearch.php");
include_once ("FDL/freedom_util.php");
include_once ("GENERIC/generic_util.php");

include_once ("FDL/Class.DocRel.php");
// -----------------------------------
function generic_isearch(Action & $action)
{
    // -----------------------------------
    // Get all the params
    $docid = GetHttpVars("id"); // id doc to search
    $famid = GetHttpVars("famid", 0); // restriction of search
    $viewone = GetHttpVars("viewone"); //
    $generic = (GetHttpVars("generic") == "Y"); //
    $dbaccess = $action->dbaccess;
    
    if (($famid !== 0) && (!is_numeric($famid))) {
        $nfamid = getFamIdFromName($dbaccess, $famid);
        if (!$nfamid) $action->addWarningMsg(sprintf("family %s not found", $famid));
        else $famid = $nfamid;
    }
    
    if ($docid == "") $action->exitError(_("related search aborted : no parameter found"));
    
    $doc = new_Doc($dbaccess, $docid);
    
    $sdoc = createTmpDoc($dbaccess, 'SSEARCH'); //new Special Seraches
    $sdoc->setValue("ba_title", sprintf(_("related documents of %s") , $doc->title));
    $sdoc->setValue("se_phpfile", "fdlsearches.php");
    $sdoc->setValue("se_phpfunc", "relateddoc");
    $sdoc->setValue("se_phparg", "$docid,$famid");
    
    try {
        if (($err = $sdoc->Add()) != '') {
            $action->exitError($err);
        }
        if (($err = $sdoc->setControl(false)) != '') {
            $sdoc->delete(true);
            $action->exitError($err);
        }
        if ($action->user->id != 1) {
            if (($err = $sdoc->addControl($action->user->id, 'execute')) != '') {
                $sdoc->delete(true);
                $action->exitError($err);
            }
        }
    }
    catch(\Exception $e) {
        if ($sdoc->isAffected()) {
            $sdoc->delete(true);
        }
        $action->exitError($e->getMessage());
    }
    
    setHttpVar("dirid", $sdoc->id);
    if ($generic) {
        include_once ("GENERIC/generic_list.php");
        generic_list($action);
        //    redirect($action,"GENERIC","GENERIC_LIST&dirid=".$sdoc->id."&famid=$famid&catg=0");
        
    } else {
        include_once ("FREEDOM/freedom_view.php");
        $action->parent->name = "FREEDOM";
        $freedomApp = new Application($action->dbaccess);
        $freedomApp->Set('FREEDOM', $action->parent);
        $viewMode = $freedomApp->getParam('FREEDOM_VIEW');
        setHttpVar("view", $viewMode);
        setHttpVar("target", "gisearch");
        freedom_view($action);
        //    redirect($action,"FREEDOM","FREEDOM_VIEW&viewone=$viewone&dirid=".$sdoc->id);
        
    }
    //
    
    
}
