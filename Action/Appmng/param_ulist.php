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
 * @version $Id: param_ulist.php,v 1.2 2003/08/18 15:46:41 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage APPMNG
 */
/**
 */
// ---------------------------------------------------------------
// $Id: param_ulist.php,v 1.2 2003/08/18 15:46:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Appmng/param_ulist.php,v $
// ---------------------------------------------------------------
// -----------------------------------
function param_ulist(&$action)
{
    // -----------------------------------
    $userid = GetHttpVars("userid");
    
    $action->register("PARAM_ACT", "PARAM_ULIST&userid=$userid");
    $u = new Account();
    $list = $u->GetUserList("TABLE");
    // select the wanted user
    while (list($k, $v) = each($list)) {
        if ($v["id"] == $userid) $list[$k]["selected"] = "selected";
        else $list[$k]["selected"] = "";
    }
    $action->lay->SetBlockData("SELUSER", $list);
    return;
}
?>
