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
 * @version $Id: appl_page.php,v 1.2 2003/08/18 15:46:41 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage ACCESS
 */
/**
 */
// ---------------------------------------------------------------
// $Id: appl_page.php,v 1.2 2003/08/18 15:46:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Access/appl_page.php,v $
// ---------------------------------------------------------------
// $Log: appl_page.php,v $
// Revision 1.2  2003/08/18 15:46:41  eric
// phpdoc
//
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.1  2000/10/24 17:15:22  yannick
// Import/Export
//
// Revision 1.2  2000/10/23 12:36:04  yannick
// Ajout de l'acces aux applications
//
// Revision 1.1  2000/10/23 09:10:27  marc
// Mise au point des utilisateurs
//
// Revision 1.1.1.1  2000/10/21 16:44:39  yannick
// Importation initiale
//
// Revision 1.2  2000/10/19 16:47:23  marc
// Evo TableLayout
//
// Revision 1.1.1.1  2000/10/19 10:35:49  yannick
// Import initial
//
//
//
// ---------------------------------------------------------------
include_once ("Class.QueryDb.php");
include_once ("Class.Application.php");
include_once ("Class.Acl.php");
include_once ("Class.Permission.php");
// -----------------------------------
function appl_page(&$action)
{
    // -----------------------------------
    // select the first user if not set
    // What user are we working on ? ask session.
    $start = GetHttpVars("start");
    $action->Register("appl_access_page", $start);
    
    redirect($action, "ACCESS", "APPL_ACCESS");
}
?>
