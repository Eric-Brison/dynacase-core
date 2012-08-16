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
 * @version $Id: param_delete.php,v 1.7 2006/06/22 16:19:07 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage APPMNG
 */
/**
 */
// ---------------------------------------------------------------
// $Id: param_delete.php,v 1.7 2006/06/22 16:19:07 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Appmng/param_delete.php,v $
// ---------------------------------------------------------------
include_once ("Class.Param.php");
// -----------------------------------
function param_delete(&$action)
{
    // -----------------------------------
    $name = GetHttpVars("id");
    $appid = GetHttpVars("appid");
    $atype = GetHttpVars("atype", PARAM_APP);
    
    $parametre = new Param($action->dbaccess, array(
        $name,
        $atype,
        $appid
    ));
    if ($parametre->isAffected()) {
        $action->log->info(_("Remove parameter") . $parametre->name);
        $parametre->Delete();
    } else $action->addLogMsg(sprintf(_("the '%s' parameter cannot be removed") , $name));
    // reopen a new session to update parameters cache
    if ($atype[0] == PARAM_USER) {
        $action->parent->session->close();
    } else {
        $action->parent->session->closeAll();
    }
    
    redirect($action, "APPMNG", $action->Read("PARAM_ACT", "PARAM_CULIST"));
}
// -----------------------------------
function param_udelete(&$action)
{
    // -----------------------------------
    $atype = GetHttpVars("atype", PARAM_APP);
    $appid = GetHttpVars("appid");
    if ($atype[0] != PARAM_USER) $action->exitError(_("only user parameters can be deleted with its action"));
    if (substr($atype, 1) != $action->user->id) $action->exitError(_("only current user parameters can be deleted with its action"));
    
    param_delete(&$action);
}
?>
