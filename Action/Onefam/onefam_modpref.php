<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * validate user or master choosen families
 *
 * @author Anakeen 2000
 * @version $Id: onefam_modpref.php,v 1.8 2006/10/05 09:22:38 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");

function onefam_modpref(&$action, $idsattr = "ONEFAM_IDS")
{
    $tidsfam = GetHttpVars("idsfam", array()); // preferenced families
    $openfam = GetHttpVars("preffirstfam"); //
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $idsfam = $action->GetParam($idsattr);
    $idsfam = implode(",", $tidsfam);
    
    if ($idsattr == "ONEFAM_IDS") {
        $action->parent->param->Set($idsattr, $idsfam, PARAM_USER . $action->user->id, $action->parent->id);
        $action->parent->param->Set("ONEFAM_FAMOPEN", $openfam, PARAM_USER . $action->user->id, $action->parent->id);
    } else {
        $action->parent->param->Set($idsattr, $idsfam, PARAM_APP, $action->parent->id);
        $action->parent->param->Set("ONEFAM_FAMOPEN", $openfam, PARAM_APP, $action->parent->id);
    }
    
    redirect($action, GetHttpVars("app") , "ONEFAM_ROOT");
}
function onefam_modmasterpref(&$action)
{
    onefam_modpref($action, "ONEFAM_MIDS");
}
?>
