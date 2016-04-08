<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Change default folder for family
 *
 * @author Anakeen
 * @version $Id: moddfld.php,v 1.8 2005/06/28 08:37:46 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.Dir.php");
include_once ("FDL/Class.DocAttr.php");
include_once ("FDL/freedom_util.php");
// -----------------------------------
function moddfld(Action & $action)
{
    // -----------------------------------
    // Get all the params
    $docid = GetHttpVars("docid");
    $current = (GetHttpVars("current", "N") == "Y");
    $newfolder = (GetHttpVars("autofolder", "N") == "Y");
    $fldid = GetHttpVars("dfldid");
    
    $dbaccess = $action->dbaccess;
    // initialise object
    
    /**
     * @var DocFam $doc
     */
    $doc = new_Doc($dbaccess, $docid);
    if ($doc === null || !$doc->isAlive()) $action->exitError(sprintf(_("Document with id '%s' not found.") , $docid));
    // create folder if auto
    if ($newfolder) {
        $fldid = createAutoFolder($doc);
        if ($fldid === false) {
            $action->exitError(_("Error creating new default folder."));
        }
    } else {
        if ($fldid === "0") {
            $fldid = "";
        } else {
            $fld = new_Doc($dbaccess, $fldid);
            if ($fld === null || !$fld->isAlive()) {
                $action->exitError(sprintf(_("Document with id '%s' not found.") , $fldid));
            }
            if ($fld->defDoctype != 'D' && $fld->defDoctype != 'S') {
                $action->exitError(sprintf(_("Document with id '%s' is not a folder or search.") , $fld->id));
            }
            $fldid = $fld->id;
        }
    }
    
    if ($current) $doc->cfldid = $fldid;
    else $doc->dfldid = $fldid; // new default folder
    // test object permission before modify values (no access control on values yet)
    $doc->lock(true); // enabled autolock
    $err = $doc->canEdit();
    if ($err != "") $action->ExitError($err);
    
    $doc->Modify();
    
    $doc->unlock(true); // disabled autolock
    redirect($action, "FDL", "FDL_CARD&id=$docid", $action->GetParam("CORE_STANDURL"));
}
