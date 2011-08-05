<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: Method.Action_impl.php,v 1.3 2003/08/18 15:47:04 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: Method.Action_impl.php,v 1.3 2003/08/18 15:47:04 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Freedom/Method.Action_impl.php,v $
// ---------------------------------------------------------------
var $defaultedit = "FREEDOM:EDIT_IMPL";

function edit_impl($target = "finfo", $ulink = true, $abstract = "Y")
{
    global $action;
    include_once ("FDL/editutil.php");
    //$action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FREEDOM/Layout/workflow.js");
    $this->lay->Set("famid", 552);
    
    $this->lay->Set("docid", $this->id);
    $this->lay->Set("TITLE", $this->title);
    
    $this->editattr($target, $ulink, $abstract);
}
?>