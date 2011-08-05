<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: access_user_chg.php,v 1.4 2007/02/14 15:13:16 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage ACCESS
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: access_user_chg.php,v 1.4 2007/02/14 15:13:16 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Access/access_user_chg.php,v $
// ---------------------------------------------------------------
// $Log: access_user_chg.php,v $
// Revision 1.4  2007/02/14 15:13:16  eric
// Fixes for session values in access interfaces
//
// Revision 1.3  2007/02/14 13:22:41  eric
// Add user filter on ACCES when too many users
//
// Revision 1.2  2003/08/18 15:46:41  eric
// phpdoc
//
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.3  2001/08/28 10:12:50  eric
// modification pour la prise en comptes des groupes d'utilisateurs
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
include_once("Class.QueryDb.php");
include_once("Class.Application.php");
include_once("Class.Acl.php");
include_once("Class.Permission.php");

// -----------------------------------
function access_user_chg(&$action) {
// -----------------------------------

  // select the first user if not set
  // What user are we working on ? ask session.
  $user_id=GetHttpVars("id");
  $group = (GetHttpVars("group") == "yes");
  $filteruser=getHttpVars("userfilter");
 
  $action->log->debug("user_id : ".$user_id);

  if ($group) {
    $action->Register("access_group_id",$user_id);
    redirect($action,"ACCESS","GROUP_ACCESS");
  } else {
    $action->Register("access_user_id",$user_id);
    redirect($action,"ACCESS","USER_ACCESS&userfilter=$filteruser");
  }

}
?>
