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
 * @version $Id: param_edit.php,v 1.4 2003/08/18 15:46:41 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage APPMNG
 */
/**
 */
// ---------------------------------------------------------------
// $Id: param_edit.php,v 1.4 2003/08/18 15:46:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Appmng/param_edit.php,v $
// ---------------------------------------------------------------
// $Log: param_edit.php,v $
// Revision 1.4  2003/08/18 15:46:41  eric
// phpdoc
//
// Revision 1.3  2002/05/24 09:23:07  eric
// changement structure table paramv
//
// Revision 1.2  2002/05/23 16:14:40  eric
// paramètres utilisateur
//
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.1  2001/01/29 15:50:59  marianne
// prise en compte de la gestion des parametres
//
// ---------------------------------------------------------------
include_once ("Class.Param.php");
include_once ("Class.SubForm.php");
// -----------------------------------
function param_edit(&$action)
{
    // -----------------------------------
    // Get all the params
    $name = GetHttpVars("id");
    $appid = GetHttpVars("appid");
    $atype = GetHttpVars("atype", PARAM_APP);
    
    $action->lay->Set("appid", $appid);
    $action->lay->Set("atype", $atype);
    
    if ($name == "") {
        $input_name = new Layout($action->GetLayoutFile("input_name.xml") , $action);
        $action->lay->Set("NAME_EDIT", $input_name->gen());
        $param = new Param("");
        $action->lay->Set("name", "");
        $action->lay->Set("val", "");
        $action->lay->Set("TITRE", $action->text("titleparamcreate"));
        $action->lay->Set("BUTTONTYPE", $action->text("butcreate"));
    } else {
        $param = new Param($action->dbaccess, array(
            $name,
            $atype,
            $appid
        ));
        $input_name = new Layout($action->GetLayoutFile("aff_name.xml") , $action);
        $input_name->Set("NAME", $name);
        $action->lay->Set("NAME_EDIT", $input_name->gen());
        $action->lay->Set("name", $name);
        $action->lay->Set("val", $param->val);
        $action->lay->Set("TITRE", $action->text("titleparammodify"));
        $action->lay->Set("BUTTONTYPE", $action->text("butmodify"));
    }
}
?>
