<?php
/**
 * Re-authenticate a user
 * Send a 401 Unauthorized HTTP header to force re-authentification or by redirect to index.php
 * which need also an authentification
 *
 * @author Anakeen 2003
 * @version $Id: authent.php,v 1.9 2004/03/17 17:47:47 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
/**
 */
include_once('Class.User.php');



/**
 * Send a 401 Unauthorized HTTP header
 */
function authenticate() {
  //   Header( "WWW-Authenticate: Basic realm=\"WHAT Connection\", stale=FALSE");
  //Header( "WWW-Authenticate: Basic realm=\"WHAT Connection\", stale=true");
  //Header( "HTTP/1.0 401 Unauthorized");
  header('WWW-Authenticate: Basic realm="WHAT Connection"');
  header('HTTP/1.0 401 Unauthorized');
  // Header("Location:guest.php");
  echo _("Vous devez entrer un nom d'utilisateur valide et un mot de passe correct pour accéder à cette ressource");
  exit;
}

//print "$PHP_AUTH_USER $SeenBefore $OldAuth";
if(!isset($PHP_AUTH_USER) || ($SeenBefore == 1 && !strcmp($OldAuth, $PHP_AUTH_USER)) ) {

  authenticate();
}
else {
  global $SERVER_NAME;
  global $SERVER_PORT;
  Header("Location: http://".$SERVER_NAME.":".$SERVER_PORT."/what/index.php?sole=R");
  exit;
}
?>
