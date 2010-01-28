<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: access_appl_chg.php,v 1.2 2003/08/18 15:46:41 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage ACCESS
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: access_appl_chg.php,v 1.2 2003/08/18 15:46:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Access/access_appl_chg.php,v $
// ---------------------------------------------------------------
// $Log: access_appl_chg.php,v $
// Revision 1.2  2003/08/18 15:46:41  eric
// phpdoc
//
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.3  2001/11/09 15:39:52  eric
// correction nom registre
//
// Revision 1.2  2001/09/07 16:52:01  eric
// gestion des droits sur les objets
//
// Revision 1.1  2000/10/23 12:36:47  yannick
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
function access_appl_chg(&$action) {
// -----------------------------------

  // select the first user if not set
  // What user are we working on ? ask session.
  $user_id=GetHttpVars("id");
  $isclass = (GetHttpVars("isclass") == "yes");
  $action->log->debug("appl_id : ".$user_id);


  if ($isclass) {
    $action->Register("access_class_id",$user_id);
    redirect($action,"ACCESS","OBJECT_ACCESS");
  } else {
  $action->Register("access_appl_id",$user_id);
    redirect($action,"ACCESS","APPL_ACCESS");
  }
}
?>
