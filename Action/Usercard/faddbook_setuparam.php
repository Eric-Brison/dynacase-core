<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Save user parameters
 *
 * @author Anakeen 2005
 * @version $Id: faddbook_setuparam.php,v 1.3 2005/11/24 13:48:17 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage USERCARD
 */
/**
 */
function faddbook_setuparam(&$action)
{
    $param = GetHttpVars("pname", "");
    $value = GetHttpVars("pvalue", "");
    $rapp = GetHttpVars("rapp", "");
    $raction = GetHttpVars("raction", "");
    $action->parent->param->set($param, $value, PARAM_USER . $action->user->id, $action->parent->id);
    if ($rapp == "" || $raction == "") return;
    
    redirect($action, $rapp, $raction);
}
?>