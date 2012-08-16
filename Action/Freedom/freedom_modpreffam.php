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
 * @version $Id: freedom_modpreffam.php,v 1.3 2003/08/18 15:47:03 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: freedom_modpreffam.php,v 1.3 2003/08/18 15:47:03 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_modpreffam.php,v $
include_once ("FDL/Class.Doc.php");

function freedom_modpreffam(&$action)
{
    $tidsfam = GetHttpVars("idsfam"); // preferenced families
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $idsfam = "";
    if (is_array($tidsfam)) $idsfam = implode(",", $tidsfam);
    
    $action->parent->param->Set("FREEDOM_PREFFAMIDS", $idsfam, PARAM_USER . $action->user->id, $action->parent->id);
    
    redirect($action, "CORE", "FOOTER");
}
?>
