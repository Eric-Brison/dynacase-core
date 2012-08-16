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
 * @version $Id: style_mod.php,v 1.3 2005/07/08 15:29:51 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage APPMNG
 */
/**
 */
// ---------------------------------------------------------------
// $Id: style_mod.php,v 1.3 2005/07/08 15:29:51 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Appmng/style_mod.php,v $
// ---------------------------------------------------------------
// $Log: style_mod.php,v $
// Revision 1.3  2005/07/08 15:29:51  eric
// suppress CORE_USERDB
//
// Revision 1.2  2003/08/18 15:46:41  eric
// phpdoc
//
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.1  2001/01/29 15:50:59  marianne
// prise en compte de la gestion des parametres
//
//
// ---------------------------------------------------------------
include_once ("Class.SubForm.php");
include_once ("Class.Param.php");
// -----------------------------------
function style_mod(&$action)
{
    // -----------------------------------
    // Get all the params
    $style_id = GetHttpVars("id");
    $creation = GetHttpVars("creation");
    $name = GetHttpVars("name");
    if ($creation == "Y") {
        $ParamCour = new Param($action->GetParam("CORE_DB"));
    } else {
        $ParamCour = new Param($action->GetParam("CORE_DB") , array(
            $style_id,
            $name
        ));
    }
    $ParamCour->key = $style_id;
    $ParamCour->name = GetHttpVars("name");
    $ParamCour->val = GetHttpVars("val");
    if ($creation == "Y") {
        $res = $ParamCour->Add();
        if ($res != "") {
            $txt = $action->text("err_add_param") . " : $res";
            $action->Register("err_add_parameter", AddSlashes($txt));
        }
    } else {
        $res = $ParamCour->Modify();
        if ($res != "") {
            $txt = $action->text("err_mod_param") . " : $res";
            $action->Register("err_add_parameter", AddSlashes($txt));
        }
    }
    redirect($action, "APPMNG", "STYLELIST");
}
?>
