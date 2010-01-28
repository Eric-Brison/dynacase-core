<?php
/**
 * Re-authenticate a user
 * Send a 401 Unauthorized HTTP header to force re-authentification or by redirect to index.php
 * which need also an authentification
 *
 * @author Anakeen 2003
 * @version $Id: authent.php,v 1.17 2006/05/30 16:55:10 marc Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage CORE
 */
/**
 */
include_once('Class.User.php');



/**
 * Send a 401 Unauthorized HTTP header
 * @deprecated use logout action instead
 */
function authenticate() {
  //   Header( "WWW-Authenticate: Basic realm=\"WHAT Connection\", stale=FALSE");
  //Header( "WWW-Authenticate: Basic realm=\"WHAT Connection\", stale=true");
  //Header( "HTTP/1.0 401 Unauthorized");
  
  $CoreNull = "";
  $core = new Application();
  $core->Set("CORE",$CoreNull);
  $action = new Action();
  $action->Set("",$core);
  header('WWW-Authenticate: Basic realm="'.$action->getParam("CORE_REALM","FREEDOM Connection").'"');
  header('HTTP/1.0 401 Unauthorized');
  // Header("Location:guest.php");
  echo _("Vous devez entrer un nom d'utilisateur valide et un mot de passe correct pour acceder a cette ressource");
  exit;
}

global $_SERVER;
global $_POST;

$target = GetHttpVars("url", "/freedom/index.php?sole=R");

if(!isset($_SERVER['PHP_AUTH_USER']) || ($_POST["SeenBefore"] == 1 && !strcmp($_POST["OldAuth"],$_SERVER['PHP_AUTH_USER'] )) ) {

  authenticate();
}
else {
  Header("Location: $target");
  exit;
}
?>
