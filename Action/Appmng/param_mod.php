<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Parameters modification
 *
 * @author Anakeen
 * @version $Id: param_mod.php,v 1.10 2006/06/22 12:52:40 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage APPMNG
 */
/**
 */

include_once ("Class.SubForm.php");
include_once ("Class.Param.php");
// -----------------------------------
function param_mod(Action & $action)
{
    // -----------------------------------
    // Get all the params
    $appid = GetHttpVars("appid");
    $name = GetHttpVars("aname");
    $atype = GetHttpVars("atype", PARAM_APP);
    $val = GetHttpVars("val");
    $direct = ($action->getArgument("direct") == "yes");
    $err = '';
    $ParamCour = new Param($action->dbaccess, array(
        $name,
        $atype,
        $appid
    ));
    
    $pdef = new paramdef($action->dbaccess, $name);
    if (!$ParamCour->isAffected()) {
        $ParamCour->appid = $appid;
        $ParamCour->type = $atype;
        $ParamCour->name = $name;
        $ParamCour->val = $val;
        $err = $ParamCour->Add();
        if ($err != "") {
            $action->addLogMsg($action->text("err_add_param") . " : $err");
        } else {
            $action->lay->set("textModify", _("param Changed"));
        }
    } else {
        if (($pdef->kind == "password") && ($val == '*****')) {
            $action->lay->set("textModify", _("param not changed"));
        } else {
            if ($ParamCour->val == $val || $pdef == 'static' || $pdef == 'readonly') {
                $action->lay->set("textModify", _("param not changed"));
            } else {
                $ParamCour->val = $val;
                $err = $ParamCour->Modify();
                if ($err != "") {
                    $action->addLogMsg($action->text("err_mod_parameter") . " : $err");
                } else {
                    $action->lay->set("textModify", _("param Changed"));
                }
            }
        }
    }
    // reopen a new session to update parameters cache
    //unset($_SESSION["CacheObj"]);
    $prevact = $action->Read("PARAM_ACT", "PARAM_CULIST");
    
    if ($atype[0] == PARAM_USER) {
        $action->parent->session->close();
    } else {
        $action->parent->session->closeAll();
    }
    
    if (!$direct) redirect($action, "APPMNG", $prevact);
    else {
        $action->lay->set("error", json_encode($err));
        if ($pdef->kind == "password") {
            if ($ParamCour->val == '') $action->lay->set("value", json_encode($ParamCour->val));
            else $action->lay->set("value", json_encode("*****"));
        } else {
            $action->lay->set("value", json_encode($ParamCour->val));
        }
    }
}
// -----------------------------------
function param_umod(Action & $action)
{
    // -----------------------------------
    $atype = GetHttpVars("atype", PARAM_APP);
    $appid = GetHttpVars("appid");
    if ($atype[0] != PARAM_USER) $action->exitError(_("only user parameters can be modified with its action"));
    if (substr($atype, 1) != $action->user->id) $action->exitError(_("only current user parameters can be modified with its action"));
    
    param_mod($action);
}
?>
