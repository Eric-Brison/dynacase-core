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
 * @version $Id: action_appl_chg.php,v 1.2 2003/08/18 15:46:41 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage APPMNG
 */
/**
 */
// ---------------------------------------------------------------
// $Id: action_appl_chg.php,v 1.2 2003/08/18 15:46:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Appmng/action_appl_chg.php,v $
// ---------------------------------------------------------------
// $Log: action_appl_chg.php,v $
// Revision 1.2  2003/08/18 15:46:41  eric
// phpdoc
//
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.1  2001/02/06 11:40:52  marianne
// Prise en compte des styles, parametres et actions
//
//
//
// ---------------------------------------------------------------
include_once ("Class.QueryDb.php");
include_once ("Class.Application.php");
include_once ("Class.Acl.php");
include_once ("Class.Permission.php");
// -----------------------------------
function action_appl_chg(&$action)
{
    // -----------------------------------
    // select the first user if not set
    // What appli are we working on ? ask session.
    $appli_id = GetHttpVars("id");
    $action->log->debug("appl_id : " . $appli_id);
    $action->Register("action_appl_id", $appli_id);
    
    redirect($action, "APPMNG", "ACTIONLIST");
}
?>
