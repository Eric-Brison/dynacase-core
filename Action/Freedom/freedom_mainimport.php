<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: freedom_editimport.php,v 1.8 2008/08/14 09:59:14 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// -----------------------------------
function freedom_mainimport(Action & $action)
{
    
    $parameters = '';
    foreach ($_GET as $k => $v) {
        if ($k != "app" && $k != "action" && $k != "sole") {
            $parameters.= sprintf("&%s=%s", $k, urlencode(($v)));
        }
    }
    
    $action->lay->set("params", $parameters);
    $windowId = uniqid("analysis");
    $action->lay->set("resultAnalysisId", $windowId);
}
?>
