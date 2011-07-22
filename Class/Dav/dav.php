<?php

/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */

$d1=microtime();
include_once("../WHAT/Lib.Common.php");
include_once("DAV/Class.FdlDav.php");

//error_log("dav:   path_info=(".$_SERVER["PATH_INFO"].")");
$_SERVER['PATH_INFO'] = "/".$_GET['filename'];
$type = isset($_GET['type'])?$_GET['type']:'freedav';

if( $type != 'webdav' && $type != 'freedav' ) {
    error_log(sprintf("Error: Invalid DAV type '%s'", $type));
    header('HTTP/1.1 500 Invalid DAV type');
    exit;
}

$_SERVER['SCRIPT_NAME'] = preg_replace('/index\.php$/', '', $_SERVER['SCRIPT_NAME']);

global $action;
global $_SERVER;

if( $type == 'webdav' ) {
  webdav_auth();
}

error_log("======[ ".$_SERVER['REQUEST_METHOD']." ]=[ ".$_SERVER['PATH_INFO']." ]=======");
whatInit();
$s=new HTTP_WebDAV_Server_Freedom($action->getParam("WEBDAV_DB"));
$s->setFolderMaxItem($action->getParam('WEBDAV_FOLDERMAXITEM'));
$path=$_SERVER['PATH_INFO'];
if ($type=="freedav") {
  if (preg_match("|/vid-([0-9]+)-([0-9]+)-([^/]+)|",$path,$reg)) {
    $docid=$reg[1];
    $vid=$reg[2];
    $sid=$reg[3];
    //error_log("dav: -> $docid  $vid $sid");
    $login=$s->getLogin($docid,$vid,$sid);
    //error_log("dav LOGIN: -> $login");
  }
} else {
  $login=$_SERVER['PHP_AUTH_USER'];
}
if (! $login) {	
  if (((($path == "/")||(strtolower($path) == "/freedav")) && ($_SERVER['REQUEST_METHOD']=="OPTIONS")) ||
      ((($path == "/")||(strtolower($path) == "/freedav")) && ($_SERVER['REQUEST_METHOD']=="PROPFIND"))) {
    // keep without authenticate
  } else {
    // header('HTTP/1.0 401 Unauthorized');
    header('HTTP/1.0 403 Forbidden');
    exit;
  }
} else {
  whatLogin($login);
}

$d2=microtime();

$dt=sprintf("%.02f",microtime_diff($d1,$d2));

$s->http_auth_realm = "Dynacase Platform connection";
$s->db_freedom=$action->getParam("FREEDOM_DB");
$s->type = $type;
$s->racine=$action->getParam("WEBDAV_ROOTID",9);
$s->ServeRequest();
$d2=microtime();
$d=sprintf("%.02f",microtime_diff($d1,$d2));

error_log("================ $d $dt=====".$login."===================");

function whatInit() {
  global $action;
  include_once('Class.User.php');
  include_once('Class.Session.php');

  $CoreNull="";
  $core = new Application();
  $core->Set("CORE",$CoreNull);
  $core->session=new Session();
  $action=new Action();
  $action->Set("",$core);

  // i18n
  $lang=$action->Getparam("CORE_LANG");  
  setLanguage($action->Getparam("CORE_LANG"));
}

function whatLogin($login) {
  global $action;
  include_once('Class.User.php');
  include_once('Class.Session.php');

  if ($login!="") {
    $action->user=new User(); //create user 
    $action->user->setLoginName($login);
  }
}

function webdav_auth() {
  include_once('WHAT/Lib.Main.php');
  include_once('WHAT/Class.AuthenticatorManager.php');

  global $_SERVER;

  $status = AuthenticatorManager::checkAccess('basic');

  switch ($status) {
 
  case 0: // it'good, user is authentified
    break;

  default:
    sleep(1); // for robots
    // Redirect to authentication
    AuthenticatorManager::$auth->askAuthentication();
    exit(0);
  }

  $_SERVER['PHP_AUTH_USER'] = AuthenticatorManager::$auth->getAuthUser();

}

?>
