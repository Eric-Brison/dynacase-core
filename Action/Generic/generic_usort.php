<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Apply sort by family
 *
 * @author Anakeen
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
function generic_usort(Action & $action)
{
    // -----------------------------------
    // get all parameters
    $aorder = $action->getArgument("aorder"); // id for controlled object
    $catg = $action->getArgument("catg"); // id for controlled object
    $onefamOrigin = $action->getArgument("onefam"); // onefam origin
    $sfamid = '';
    if ($catg) {
        $dir = new_doc($action->dbaccess, $catg);
        if ($dir->isAlive()) {
            $sfamid = $dir->getValue("se_famid");
        }
    }
    
    $action->parent->param->Set("GENERIC_USORT", setUsort($action, $aorder, $sfamid) , PARAM_USER . $action->user->id, $action->parent->id);
    
    $famid = getDefFam($action);
    
    redirect($action, $action->GetParam("APPNAME", "GENERIC") , "GENERIC_LIST&onefam=$onefamOrigin&dirid=$catg&tab=0&famid=$famid", $action->GetParam("CORE_STANDURL"));
}

function setUsort(Action & $action, $aorder, $famid = "")
{
    if (!$famid) $famid = getDefFam($action);
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
    if ($a === false) {
        $a = $fdoc->getProperty($sqlorder);
    }
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
