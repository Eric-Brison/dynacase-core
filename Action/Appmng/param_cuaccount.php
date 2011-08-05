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
 * @version $Id: param_cuaccount.php,v 1.2 2003/08/18 15:46:41 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage APPMNG
 */
/**
 */
// ---------------------------------------------------------------
// $Id: param_cuaccount.php,v 1.2 2003/08/18 15:46:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Appmng/param_cuaccount.php,v $
// ---------------------------------------------------------------
// -----------------------------------
function param_cuaccount(&$action)
{
    // -----------------------------------
    
    $action->lay->Set("userid", $action->user->id);
    return;
}
?>
