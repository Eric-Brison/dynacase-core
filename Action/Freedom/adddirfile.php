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
 * @version $Id: adddirfile.php,v 1.15 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: adddirfile.php,v 1.15 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/adddirfile.php,v $
// ---------------------------------------------------------------
include_once ("FDL/Lib.Dir.php");
include_once ("FDL/freedom_util.php");
// -----------------------------------
function adddirfile(&$action)
{
    // -----------------------------------
    //    PrintAllHttpVars();
    // Get all the params
    $dirid = GetHttpVars("dirid");
    $docid = GetHttpVars("docid");
    $mode = GetHttpVars("mode");
    $return = GetHttpVars("return"); // return action may be folio
    $folio = (GetHttpVars("folio", "N") == "Y"); // return in folio
    $folio = ($folio | $return);
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $doc = new_Doc($dbaccess, $docid);
    $dir = new_Doc($dbaccess, $dirid);
    
    $err = $dir->AddFile($doc->initid, $mode);
    
    if ($err != "") $action->addWarningMsg($err);
    
    if ($folio) {
        $refreshtab = (($doc->doctype == "F") ? "N" : "Y");
        redirect($action, GetHttpVars("app") , "FOLIOLIST&refreshtab=$refreshtab&dirid=$dirid");
    } else redirect($action, GetHttpVars("app") , "FREEDOM_VIEW&dirid=$dirid");
}
?>
