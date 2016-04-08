<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: changetitle.php,v 1.5 2005/06/28 08:37:46 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: changetitle.php,v 1.5 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/changetitle.php,v $
// ---------------------------------------------------------------
include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocAttr.php");
include_once ("FREEDOM/freedom_mod.php");
include_once ("VAULT/Class.VaultFile.php");

function changetitle(Action & $action)
{
    
    $dbaccess = $action->dbaccess;
    $docid = GetHttpVars("id", 0);
    $ntitle = GetHttpVars("ititle", "");
    
    $doc = new_Doc($dbaccess, $docid);
    
    if ($ntitle != "") {
        $doc->title = $ntitle;
        $err = $doc->modify();
        if ($err != "") $action->ExitError($err);
        
        $action->AddLogMsg(sprintf(_("new title for %s") , $doc->title));
    }
    
    redirect($action, "FDL", "FDL_CARD&id=" . $doc->id);
}
