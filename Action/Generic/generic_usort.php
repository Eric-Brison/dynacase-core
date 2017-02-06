<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Apply sort by family
 *
 * @author Anakeen
 * @version $Id: generic_usort.php,v 1.6 2006/04/28 14:34:02 eric Exp $
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
    $tab = $action->getArgument("tab", 0); // tab index
    $dirid = $action->getArgument("dirid", 0); // collection id
    $sfamid = '';
    if ($catg) {
        $dir = new_doc($action->dbaccess, $catg);
        if ($dir->isAlive()) {
            $sfamid = $dir->getRawValue("se_famid");
        }
    }
    
    $action->parent->param->Set("GENERIC_USORT", setUsort($action, $aorder, $sfamid) , Param::PARAM_USER . $action->user->id, $action->parent->id);
    
    $famid = getDefFam($action);
    if ($tab) {
        redirect($action, $action->GetParam("APPNAME", "GENERIC") , "GENERIC_TAB&onefam=$onefamOrigin&catg=$catg&famid=$famid&tab=$tab", $action->GetParam("CORE_STANDURL"));
    } else {
        redirect($action, $action->GetParam("APPNAME", "GENERIC") , "GENERIC_LIST&onefam=$onefamOrigin&dirid=$dirid&tab=0&famid=$famid&tab=$tab", $action->GetParam("CORE_STANDURL"));
    }
}

function setUsort(Action & $action, $aorder, $famid = "")
{
    if (!$famid) $famid = getDefFam($action);
    $dbaccess = $action->dbaccess;
    
    $fdoc = new_Doc($dbaccess, $famid);
    
    $pu = $action->GetParam("GENERIC_USORT");
    $tr = array();
    if ($pu) {
        // disambled parameter
        $tu = explode("|", $pu);
        
        foreach ($tu as $v) {
            list($afamid, $uorder, $sqlorder) = explode(":", $v);
            $tr[$afamid] = $uorder . ":" . $sqlorder;
        }
    }
    
    if (!isset($tr[$famid])) {
        /*
         * Handle the case when no order is memorized and "revdate"
         * is clicked: invert revdate sort order
        */
        /* This should be in sync with the default value $def from getDefUSort() */
        if ($aorder == 'revdate') {
            $aorder = '-revdate';
        } elseif ($aorder == '-revdate') {
            $aorder = 'revdate';
        }
    }
    
    $sqlorder = $aorder;
    if (isset($aorder[0]) && $aorder[0] == "-") $sqlorder = substr($aorder, 1);
    $a = $fdoc->getAttribute($sqlorder);
    if ($a === false) {
        $a = $fdoc->getPropertyValue($sqlorder);
    }
    if ($a && isset($a->type) && $a->type == "text") $sqlorder = "lower($sqlorder)";
    if (isset($aorder[0]) && $aorder[0] == "-") $sqlorder.= " desc";
    
    $tr[$famid] = $aorder . ":" . $sqlorder;
    // rebuild parameter
    $tu = array();
    reset($tr);
    foreach ($tr as $k => $v) {
        $tu[] = "$k:$v";
    }
    return implode("|", $tu);
}
