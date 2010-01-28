<?php
/**
 * WHAT Environnement
 *
 * @author Anakeen 2004
 * @version $Id: wenv.php,v 1.11 2008/05/06 17:04:16 jerome Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 */
/**
 */

global $_SERVER;

function getBaseDirList() {
  include_once('WHAT/Lib.Prefix.php');
  # $bl[] = "default";
  if (!is_dir(DEFAULT_PUBDIR."/context/")) return $bl;
  
  if ($dh = opendir(DEFAULT_PUBDIR."/context/")) {
    while (($file = readdir($dh)) !== false) {
      if ($file!=".." && $file!="." && is_dir(DEFAULT_PUBDIR."/context/".$file)
	  && file_exists(DEFAULT_PUBDIR."/context/".$file."/dbaccess.php")) $bl[] = $file;
    }
    fclose($dh);
  }
  return $bl;
}

function setBaseDir($freedomctx="default") {
  include_once('WHAT/Lib.Prefix.php');
  if (is_dir(DEFAULT_PUBDIR."/context/".$freedomctx)) return DEFAULT_PUBDIR."/context/".$freedomctx;
  else return DEFAULT_PUBDIR;
}

function isRealDb($freedomctx="default") {
  include_once('WHAT/Lib.Prefix.php');
  if (file_exists(DEFAULT_PUBDIR."/context/".$freedomctx."/dbaccess.php")) return true;
  return false;
}

function getPhpEnv($freedomctx) {
  include_once('WHAT/Lib.Prefix.php');
  $vdir = setBaseDir($freedomctx);
  if (file_exists($vdir."/dbaccess.php")) $env = $vdir."/dbaccess.php";
  else $env = $vdir."/dbaccess.php";
  return $env;
}

function getShEnv($freedomctx) {
  error_log("Deprecated call to getShEnv()");
  return "";

  include_once('WHAT/Lib.Prefix.php');
  $vdir = setBaseDir($freedomctx);
  if (file_exists($vdir."/dbaccess.sh")) $env = $vdir."/dbaccess.sh";
  else $env = $vdir."/dbaccess.sh";
  return $env;
}

function setCurrentDb($freedomctx="default") {
  error_log("Deprecated call to setCurrentDb() in ".__FILE__." : use setCurrentContext(ctxName)");
  return setCurrentContext($freedomctx);
}

function setCurrentContext($freedomctx="default") {
  include_once('WHAT/Lib.Prefix.php');
  $fcur = fopen(DEFAULT_PUBDIR."/.freedom", 'w');
  fprintf($fcur, $freedomctx);
  fclose($fcur);
  $dpath = DEFAULT_PUBDIR."/context/".$freedomctx;
  system("ln -sf \"$dpath/dbaccess.sh\" \"".DEFAULT_PUBDIR."/.freedom.sh\"");
}

function getCurrentDb() {
  error_log("Deprecated call to getCurrentDb in ".__FILE___." : use getCurrentContext()");
  return getCurrentContext();
}

function getCurrentContext() {
  if (file_exists(DEFAULT_PUBDIR."/.freedom")) return file_get_contents(DEFAULT_PUBDIR."/.freedom");
  return "default";
}

function initDbContext( $freedomctx = "default",
		    $pgservice_core = "anakeen",
		    $pgservice_freedom = "freedom" ) {
  include_once('WHAT/Lib.Prefix.php');
  
  $inphpfile=DEFAULT_PUBDIR."/dbaccess.php.in";
  $inshfile=DEFAULT_PUBDIR."/dbaccess.sh.in";

  $vdir = DEFAULT_PUBDIR."/context/".$freedomctx;
  $dbfphp="$vdir/dbaccess.php";
  $dbfsh="$vdir/dbaccess.sh";

  if (!is_dir($vdir)) mkdir($vdir, 0755, true);

  if (file_exists($dbf)) return false;

  $httpu = getenv("httpuser");
  $httpconf = getenv("httpconf");

  $command = "sed -e 's,@PGSERVICE_CORE@,$pgservice_core,g'"
    .        "    -e 's,@PGSERVICE_FREEDOM@,$pgservice_freedom,g'"
    .        "    -e 's,@FREEDOM_CONTEXT@,$freedomctx,g'"
    ;  
  system("cat $inphpfile | $command > $dbfphp");
  system("cat $inshfile | $command > $dbfsh");

  setCurrentContext($freedomctx);
}

?>