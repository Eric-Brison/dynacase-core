<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Apply sort by family
 *
 * @author Anakeen 2000
 * @version $Id: generic_usort.php,v 1.6 2006/04/28 14:34:02 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("GENERIC/generic_util.php");
// -----------------------------------
function generic_usort(&$action)
{
    // -----------------------------------
    // get all parameters
    $aorder = GetHttpVars("aorder"); // id for controlled object
    $catg = GetHttpVars("catg"); // id for controlled object
    $sfamid = '';
    if ($catg) {
        $dir = new_doc($dbaccess, $catg);
        if ($dir->isAlive()) {
            $sfamid = $dir->getValue("se_famid");
        }
    }
    if ($aorder == "-") {
        // invert order
        $uorder = getDefUSort($action, $sfamid);
        if ($uorder[0] == "-") $aorder = substr($uorder, 1);
        else $aorder = "-" . $uorder;
    }
    
    $action->parent->param->Set("GENERIC_USORT", setUsort($action, $aorder, $sfamid) , PARAM_USER . $action->user->id, $action->parent->id);
    
    $famid = getDefFam($action);
    
    redirect($action, $action->GetParam("APPNAME", "GENERIC") , "GENERIC_LIST&dirid=$catg&tab=0&famid=$famid", $action->GetParam("CORE_STANDURL"));
}

function setUsort(&$action, $aorder, $famid = "")
{
    
    if (!$famid) $famid = getDefFam(&$action);
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $fdoc = new_Doc($dbaccess, $famid);
    
    $pu = $action->GetParam("GENERIC_USORT");
    $tr = array();
    if ($pu) {
        // disambled parameter
        $tu = explode("|", $pu);
        
        while (list($k, $v) = each($tu)) {
            list($afamid, $uorder, $sqlorder) = explode(":", $v);
            $tr[$afamid] = $uorder . ":" . $sqlorder;
        }
    }
    
    $sqlorder = $aorder;
    if ($aorder[0] == "-") $sqlorder = substr($aorder, 1);
    $a = $fdoc->getAttribute($sqlorder);
    if ($a && $a->type == "text") $sqlorder = "lower($sqlorder)";
    if ($aorder[0] == "-") $sqlorder.= " desc";
    
    $tr[$famid] = $aorder . ":" . $sqlorder;
    // rebuild parameter
    $tu = array();
    reset($tr);
    while (list($k, $v) = each($tr)) {
        $tu[] = "$k:$v";
    }
    return implode("|", $tu);
}
?>
