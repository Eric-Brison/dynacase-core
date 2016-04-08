<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * View folder containt in column mode
 *
 * @author Anakeen
 * @version $Id: freedom_column.php,v 1.6 2005/08/18 09:16:09 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ('FREEDOM/freedom_view.php');
// -----------------------------------
// -----------------------------------
function freedom_column(&$action)
{
    // -----------------------------------
    // Set the globals elements
    $memo = (getHttpVars("memo", "N") == "Y");
    
    if ($memo) $action->parent->param->Set("FREEDOM_VIEW", "column", PARAM_USER . $action->user->id, $action->parent->id);
    viewfolder($action, false, true, 1);
}
?>
