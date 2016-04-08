<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Creation of batch document from folder
 *
 * @author Anakeen
 * @version $Id: freedom_addbatch.php,v 1.4 2007/09/10 13:26:47 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Class.Doc.php");
/**
 * Create a batch document from folder
 * @param Action &$action current action
 * @global dirid int Http var : folder id document
 * @global bid int Http var : family identifier of the batch
 * @global linkdir string Http var : (Y|N) if Y copy reference in batch else copy containt of folder
 */
function freedom_addbatch(Action & $action)
{
    
    $bid = GetHttpVars("bid");
    $dirid = GetHttpVars("dirid");
    $linkdir = (GetHttpVars("linkdir", "N") == "Y");
    
    $dbaccess = $action->dbaccess;
    
    $bdoc = new_Doc($dbaccess, $bid);
    if (!$bdoc->isAlive()) $action->exitError(sprintf(_("unknown batch document %s") , $bid));
    /**
     * @var Dir $fld
     */
    $fld = new_Doc($dbaccess, $dirid);
    if (!$fld->isAlive()) $action->exitError(sprintf(_("unknown folder document %s") , $fld));
    /**
     * @var Dir $doc
     */
    $doc = createDoc($dbaccess, $bid);
    if (!$doc) $action->exitError(sprintf(_("no privilege to create this kind (%s) of document") , $bdoc->title));
    
    $doc->setTitle(sprintf(_("batch from %s folder") , $fld->title));
    $doc->setValue("ba_desc", sprintf(_("batch from %s folder") , $fld->title));
    $famid = $fld->getRawValue("se_famid");
    if ($famid) {
        $doc->setValue("pubm_idfam", $famid);
    }
    $err = $doc->Add();
    
    if ($err != "") $action->exitError($err);
    if ($linkdir) {
        $doc->Addfile($fld->initid);
    } else {
        $tdoc = $fld->getContent();
        foreach ($tdoc as $k => $v) {
            if (($v["doctype"] == "S") || ($v["doctype"] == "D")) unset($tdoc[$k]);
        }
        $doc->InsertMDoc($tdoc);
    }
    
    redirect($action, "FREEDOM", "OPENFOLIO&id=" . $doc->id, $action->GetParam("CORE_STANDURL"));
}
