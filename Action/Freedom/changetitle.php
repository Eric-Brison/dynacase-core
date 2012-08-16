<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: changetitle.php,v 1.5 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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

function changetitle(&$action)
{
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $docid = GetHttpVars("id", 0);
    $ntitle = GetHttpVars("ititle", "");
    
    $action->lay->Set("docid", $docid);
    
    $doc = new_Doc($dbaccess, $docid);
    
    if ($ntitle != "") {
        $doc->title = $ntitle;
        $err = $doc->modify();
        if ($err != "") $action->ExitError($err);
        
        $action->AddLogMsg(sprintf(_("new title for %s") , $doc->title));
    }
    
    redirect($action, "FDL", "FDL_CARD&id=" . $doc->id);
}
?>
