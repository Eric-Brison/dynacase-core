<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * View folder containt in icon mode
 *
 * @author Anakeen
 * @version $Id: freedom_icons.php,v 1.5 2005/08/18 09:16:09 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ('FREEDOM/freedom_view.php');
// -----------------------------------
// -----------------------------------
function freedom_icons(&$action)
{
    // -----------------------------------
    // Set the globals elements
    $memo = (getHttpVars("memo", "N") == "Y");
    
    if ($memo) $action->parent->param->Set("FREEDOM_VIEW", "icon", PARAM_USER . $action->user->id, $action->parent->id);
    
    viewfolder($action, false);
}
?>
