<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: confirmmail.php,v 1.3 2008/02/28 17:50:36 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */
// ---------------------------------------------------------------
// $Id: confirmmail.php,v 1.3 2008/02/28 17:50:36 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Fdl/confirmmail.php,v $
// ---------------------------------------------------------------
include_once ("FDL/editmail.php");
// -----------------------------------
// -----------------------------------
function confirmmail(&$action)
{
    
    $nextstate = GetHttpVars("state");
    $ulink = GetHttpVars("ulink");
    editmail($action);
    
    $action->lay->eSet("ulink", ($ulink ? $ulink : "Y"));
    $action->lay->eSet("state", $nextstate);
    $action->lay->eSet("tstate", _($nextstate));
}
