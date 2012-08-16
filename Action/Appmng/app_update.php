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
 * @version $Id: app_update.php,v 1.2 2003/08/18 15:46:41 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage APPMNG
 */
/**
 */
// ---------------------------------------------------------------
// $Id: app_update.php,v 1.2 2003/08/18 15:46:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Appmng/app_update.php,v $
// ---------------------------------------------------------------
// $Log: app_update.php,v $
// Revision 1.2  2003/08/18 15:46:41  eric
// phpdoc
//
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.4  2001/07/25 12:51:12  eric
// ajout fonction updateall
//
// Revision 1.3  2000/11/02 18:39:08  marc
// OK
//
// Revision 1.2  2000/11/02 18:35:14  marc
// Creation (log info : application )
//
// Revision 1.1.1.1  2000/10/16 08:52:39  yannick
// Importation initiale
//
//
//
// ---------------------------------------------------------------
include_once ("Class.TableLayout.php");
include_once ("Class.QueryDb.php");
// -----------------------------------
function app_update(&$action)
{
    // -----------------------------------
    $appsel = GetHttpVars("appsel");
    $application = new Application("", $appsel);
    $action->log->info("Update " . $application->name);
    $application->Set($application->name, $action->parent);
    $application->UpdateApp();
    
    redirect($action, "APPMNG", "");
}
// -----------------------------------
function app_updateAll(&$action)
{
    // -----------------------------------
    $application = new Application();
    $application->UpdateAllApp();
    
    redirect($action, "APPMNG", "");
}
?>
