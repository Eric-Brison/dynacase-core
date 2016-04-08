<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: deldirfile.php,v 1.14 2005/06/28 08:37:46 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Class.Dir.php");
include_once ("FDL/freedom_util.php");
// -----------------------------------
function deldirfile(Action & $action)
{
    // -----------------------------------
    // Get all the params
    $dirid = GetHttpVars("dirid");
    $docid = GetHttpVars("docid");
    $folio = GetHttpVars("folio", "N") == "Y"; // return in folio
    //  print "deldirfile :: dirid:$dirid , docid:$docid";
    $dbaccess = $action->dbaccess;
    /**
     * @var Dir $dir
     */
    $dir = new_Doc($dbaccess, $dirid); // use initial id for directories
    $err = $dir->DelFile($docid);
    if ($err != "") $action->exitError($err);
    
    if ($folio) {
        $doc = new_Doc($dbaccess, $docid);
        $refreshtab = (($doc->doctype == "F") ? "N" : "Y");
        redirect($action, "FREEDOM", "FOLIOLIST&refreshtab=$refreshtab&dirid=" . $dir->initid);
    } else redirect($action, "FREEDOM", "FREEDOM_VIEW&dirid=" . $dir->initid);
}
