<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: refreshdir.php,v 1.3 2003/08/18 15:47:03 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: refreshdir.php,v 1.3 2003/08/18 15:47:03 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/refreshdir.php,v $
// ---------------------------------------------------------------
// $Log: refreshdir.php,v $
// Revision 1.3  2003/08/18 15:47:03  eric
// phpdoc
//
// Revision 1.2  2002/06/19 12:32:29  eric
// modif des permissions : intégration de rq sql hasviewpermission
//
// Revision 1.1  2002/02/05 16:34:07  eric
// decoupage pour FREEDOM-LIB
//
// Revision 1.2  2001/11/28 13:40:10  eric
// home directory
//
// Revision 1.1  2001/11/09 09:41:14  eric
// gestion documentaire
//
//
// ---------------------------------------------------------------
include_once("FDL/Class.Doc.php");
include_once("FDL/Class.QueryDir.php");



// -----------------------------------
// -----------------------------------
function refreshdir(&$action) {
// -----------------------------------

  $action->log->start();
  // Set the globals elements


  $baseurl=$action->GetParam("CORE_BASEURL");
  $standurl=$action->GetParam("CORE_STANDURL");
  $dbaccess = $action->GetParam("FREEDOM_DB");

  // Get all the params      
  $dirid=GetHttpVars("dirid"); // directory to refresh
  




  $oqd = new QueryDir($dbaccess);
  $oqd->RefreshDir($dirid);
  redirect($action,GetHttpVars("app"),"FREEDOM_VIEW&dirid=$dirid");
}
?>
