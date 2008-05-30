<?php
/**
 * Close session
 *
 * @author Anakeen 1999
 * @version $Id: logout.php,v 1.6 2008/05/30 12:06:48 eric Exp $
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
 */
function logout(&$action) {

   $action->session->Close();
   
global $_SERVER;
global $_POST;

 $rapp = GetHttpVars("rapp");
 $raction = GetHttpVars("raction");
 $rurl = GetHttpVars("rurl", $action->GetParam("CORE_ROOTURL"));

 if(!isset($_SERVER['PHP_AUTH_USER']) || ($_POST["SeenBefore"] == 1 && !strcmp($_POST["OldAuth"],$_SERVER['PHP_AUTH_USER'] )) ) {
   authenticate($action);
 } else {
   redirect($action,$rapp,$raction,$rurl);
 }
}

 /**
 * Send a 401 Unauthorized HTTP header
 */
function authenticate(&$action) {
  //   Header( "WWW-Authenticate: Basic realm=\"WHAT Connection\", stale=FALSE");
  //Header( "WWW-Authenticate: Basic realm=\"WHAT Connection\", stale=true");
  //Header( "HTTP/1.0 401 Unauthorized");
  
 
  header('WWW-Authenticate: Basic realm="'.$action->getParam("CORE_REALM","FREEDOM Connection").'"');
  header('HTTP/1.0 401 Unauthorized');
  // Header("Location:guest.php");
  echo _("Vous devez entrer un nom d'utilisateur valide et un mot de passe correct pour acceder a cette ressource");
  exit;
}     
?>
