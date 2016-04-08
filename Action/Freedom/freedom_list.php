<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * view folder containt in list mode
 *
 * @author Anakeen
 * @version $Id: freedom_list.php,v 1.6 2005/08/18 09:16:09 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FREEDOM/freedom_view.php");
// -----------------------------------
// -----------------------------------
function freedom_list(&$action)
{
    // -----------------------------------
    // Set the globals elements
    $memo = (getHttpVars("memo", "N") == "Y");
    
    if ($memo) $action->parent->param->Set("FREEDOM_VIEW", "list", PARAM_USER . $action->user->id, $action->parent->id);
    
    viewfolder($action, false);
}
?>
