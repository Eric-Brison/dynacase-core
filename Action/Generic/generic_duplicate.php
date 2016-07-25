<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: generic_duplicate.php,v 1.9 2007/11/27 16:36:19 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */
// ---------------------------------------------------------------
// $Id: generic_duplicate.php,v 1.9 2007/11/27 16:36:19 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Generic/generic_duplicate.php,v $
// ---------------------------------------------------------------
include_once ("FDL/duplicate.php");

include_once ("FDL/Class.Dir.php");
include_once ("GENERIC/generic_util.php");
// -----------------------------------
function generic_duplicate(Action & $action)
{
    // -----------------------------------
    // Get all the params
    $dirid = GetHttpVars("dirid"); // where to duplicate
    $docid = GetHttpVars("id", 0); // doc to duplicate
    if ($dirid == "") {
        $dbaccess = $action->dbaccess;
        /**
         * @var Doc $fdoc
         */
        $fdoc = new_Doc($dbaccess, $docid);
        
        $dirid = $fdoc->prelid;
    }
    $copy = duplicate($action, $dirid, $docid);
    
    redirect($action, "FDL", "FDL_CARD&refreshfld=Y&id=" . $copy->id, $action->GetParam("CORE_STANDURL"));
}
