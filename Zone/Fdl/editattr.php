<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: editattr.php,v 1.10 2005/06/28 08:37:46 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */
// ---------------------------------------------------------------
// $Id: editattr.php,v 1.10 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Fdl/editattr.php,v $
// ---------------------------------------------------------------
include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocAttr.php");

include_once ("FDL/freedom_util.php");
include_once ("FDL/editutil.php");
// Compute value to be inserted in a specific layout
// -----------------------------------
function editattr(Action & $action)
{
    // -----------------------------------
    // GetAllParameters
    $docid = GetHttpVars("id", 0);
    $classid = GetHttpVars("classid");
    // Set the globals elements
    $dbaccess = $action->dbaccess;
    
    if ($docid == 0) $doc = new_Doc($dbaccess, $classid);
    else $doc = new_Doc($dbaccess, $docid);
    
    $doc->lay = $action->lay;
    $doc->editattr();
    
    $action->lay = $doc->lay;
    
    return;
}
