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
 * @version $Id: admin_view.php,v 1.3 2004/03/22 15:21:40 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage USERS
 */
/**
 */
// ---------------------------------------------------------------
// $Id: admin_view.php,v 1.3 2004/03/22 15:21:40 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Users/admin_view.php,v $
// ---------------------------------------------------------------
// $Log: admin_view.php,v $
// Revision 1.3  2004/03/22 15:21:40  eric
// change HTTP variable name to put register_globals = Off
//
// Revision 1.2  2003/08/18 15:46:41  eric
// phpdoc
//
// Revision 1.1  2002/07/29 11:15:18  marc
// Release 0.1.1, see ChangeLog
//
// Revision 1.15  2001/09/12 10:31:41  eric
// seul domain_master peut changer les groupes
//
// ---------------------------------------------------------------
include_once ("Class.Domain.php");
include_once ("Class.MailAccount.php");
include_once ("Class.User.php");
// -----------------------------------
function admin_view(&$action)
{
    // -----------------------------------
    global $_POST;
    $id = GetHttpVars("id");
    $group = GetHttpVars("group", "no");
    $papp = GetHttpVars("papp", "APPMNG");
    $paction = GetHttpVars("paction", "PARAM_CUACCOUNT");
    $pargs = GetHttpVars("pargs", "");
    
    $action->lay->Set("id", $id);
    if ($id == - 1) {
        if ($group == "yes") $action->lay->Set("TITRE", $action->text("titlecreateg"));
        else $action->lay->Set("TITRE", $action->text("titlecreateu"));
    } else {
        if ($group == "yes") $action->lay->Set("TITRE", $action->text("titlemodifyg"));
        else $action->lay->Set("TITRE", $action->text("titlemodifyu"));
    }
    $action->lay->Set("group", $group);
    $action->lay->Set("userid", $id);
    $action->lay->Set("papp", $papp);
    $action->lay->Set("paction", $paction);
}
?>
