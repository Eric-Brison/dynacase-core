<?php
/**
 * Close session
 *
 * @author Anakeen 1999
 * @version $Id: logout.php,v 1.3 2003/08/18 15:46:41 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 * @deprecated since HTTP Authentification
 */
/**
 */
include_once('Class.Session.php');
include_once('Class.User.php');
include_once('Lib.Http.php');
/**
 * Close session
 *
 * @deprecated since HTTP Authentification
 */
function logout(&$action) {

   $action->session->Close();
   
   redirect($action,"CORE","",$action->GetParam("CORE_ROOTURL"));

}
      
?>
