<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * display interface to insert document in portfolio
 *
 * @author Anakeen
 * @version $Id: foliosearch.php,v 1.1 2005/04/06 16:38:58 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */
// -----------------------------------
function foliosearch(Action &$action)
{
    // -----------------------------------
    // Get all the params
    $docid = GetHttpVars("id", 0); // portfolio id
    $action->lay->set("docid", urlencode($docid));
}
?>