<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Suppress a link to a folder
 *
 * @author Anakeen
 * @version $Id: generic_del.php,v 1.13 2006/11/21 15:52:03 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocAttr.php");
include_once ("FDL/freedom_util.php");
/**
 * Put a doc in trash
 * @param Action &$action current action
 * @global id int Http var : document id to trash
 * @global recursive string Http var : if yes and it is a folder like family try to delete containt (primary relation) also
 */
function generic_del(Action & $action)
{
    // -----------------------------------
    // Get all the params
    $docid = GetHttpVars("id");
    $recursive = (GetHttpVars("recursive") == "yes");
    $dbaccess = $action->dbaccess;
    
    if ($docid > 0) {
        /**
         * @var Dir $doc
         */
        $doc = new_Doc($dbaccess, $docid);
        
        $err = $doc->PreDocDelete();
        if ($err != "") $action->ExitError($err);
        // ------------------------------
        // delete document
        if ($recursive) {
            if ($doc->doctype == 'D') $err = $doc->deleteRecursive();
            else $action->ExitError(sprintf(_("%s document it is not a folder and cannot support recursive deletion") , $doc->title));
        } else {
            $err = $doc->Delete();
        }
        if ($err != "") $action->ExitError($err);
        
        $action->AddActionDone("DELFILE", $doc->prelid);
        $action->AddActionDone("TRASHFILE", $doc->prelid);
        redirect($action, "FDL", "FDL_CARD&sole=Y&refreshfld=Y&id=$docid");
    }
    
    redirect($action, GetHttpVars("app") , "GENERIC_LOGO");
}
