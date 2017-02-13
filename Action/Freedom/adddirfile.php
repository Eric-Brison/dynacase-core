<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: adddirfile.php,v 1.15 2005/06/28 08:37:46 eric Exp $
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
function adddirfile(Action & $action)
{
    // -----------------------------------
    //    PrintAllHttpVars();
    // Get all the params
    $dirid = GetHttpVars("dirid");
    $docid = GetHttpVars("docid");
    $mode = GetHttpVars("mode");
    $return = GetHttpVars("return"); // return action may be folio
    $folio = (GetHttpVars("folio", "N") == "Y"); // return in folio
    $folio = ($folio || $return);
    
    $dbaccess = $action->dbaccess;
    
    $doc = new_Doc($dbaccess, $docid);
    /**
     * @var Dir $dir
     */
    $dir = new_Doc($dbaccess, $dirid);
    
    $err = $dir->AddFile($doc->initid, $mode);
    
    if ($err != "") $action->addWarningMsg($err);
    
    if ($folio) {
        $refreshtab = (($doc->doctype == "F") ? "N" : "Y");
        redirect($action, GetHttpVars("app") , "FOLIOLIST&refreshtab=$refreshtab&dirid=$dirid");
    } else redirect($action, GetHttpVars("app") , "FREEDOM_VIEW&dirid=$dirid");
}
