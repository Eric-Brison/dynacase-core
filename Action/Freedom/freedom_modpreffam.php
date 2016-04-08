<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: freedom_modpreffam.php,v 1.3 2003/08/18 15:47:03 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: freedom_modpreffam.php,v 1.3 2003/08/18 15:47:03 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_modpreffam.php,v $
include_once ("FDL/Class.Doc.php");

function freedom_modpreffam(Action & $action)
{
    $tidsfam = GetHttpVars("idsfam"); // preferenced families
    $idsfam = "";
    if (is_array($tidsfam)) $idsfam = implode(",", $tidsfam);
    
    $action->parent->param->Set("FREEDOM_PREFFAMIDS", $idsfam, PARAM_USER . $action->user->id, $action->parent->id);
    
    redirect($action, "CORE", "FOOTER");
}
