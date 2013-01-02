<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: freedom_editimport.php,v 1.8 2008/08/14 09:59:14 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// -----------------------------------
function freedom_mainimport(Action & $action)
{
    
    $paramters = '';
    foreach ($_GET as $k => $v) {
        if ($k != "app" && $k != "action" && $k != "sole") {
            $paramters.= sprintf("&%s=%s", $k, urlencode(($v)));
        }
    }
    
    $action->lay->set("params", $paramters);
    $windowId = uniqid("analysis");
    $action->lay->set("resultAnalysisId", $windowId);
}
?>
