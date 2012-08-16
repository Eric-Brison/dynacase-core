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
 * @version $Id: app_delete.php,v 1.2 2003/08/18 15:46:41 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage APPMNG
 */
/**
 */
// ---------------------------------------------------------------
// $Id: app_delete.php,v 1.2 2003/08/18 15:46:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Appmng/app_delete.php,v $
// ---------------------------------------------------------------
// $Log: app_delete.php,v $
// Revision 1.2  2003/08/18 15:46:41  eric
// phpdoc
//
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.3  2001/01/29 15:50:59  marianne
// prise en compte de la gestion des parametres
//
// Revision 1.2  2000/11/02 18:39:08  marc
// OK
//
// Revision 1.1  2000/11/02 18:35:14  marc
// Creation (log info : application )
//
//
// ---------------------------------------------------------------
include_once ("Class.TableLayout.php");
include_once ("Class.QueryDb.php");
// -----------------------------------
function app_delete(&$action)
{
    // -----------------------------------
    $appsel = GetHttpVars("appsel");
    
    $application = new Application("", $appsel);
    $action->log->info("Remove " . $application->name);
    $application->DeleteApp();
    redirect($action, "APPMNG", "");
}
?>
