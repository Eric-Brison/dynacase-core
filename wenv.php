<?php
/**
 * WHAT Environnement
 *
 * @author Anakeen 2004
 * @version $Id: wenv.php,v 1.9 2008/04/25 09:19:10 jerome Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 */
/**
 */

global $_SERVER;

function getBaseDirList() {
  include_once('WHAT/Lib.Prefix.php');
  # $bl[] = "default";
  if (!is_dir(DEFAULT_PUBDIR."/virtual/")) return $bl;
  
  if ($dh = opendir(DEFAULT_PUBDIR."/virtual/")) {
    while (($file = readdir($dh)) !== false) {
      if ($file!=".." && $file!="." && is_dir(DEFAULT_PUBDIR."/virtual/".$file)
	  && file_exists(DEFAULT_PUBDIR."/virtual/".$file."/dbaccess.php")) $bl[] = $file;
    }
    fclose($dh);
  }
  return $bl;
}

function setBaseDir($freedomenv="default") {
  include_once('WHAT/Lib.Prefix.php');
  if (is_dir(DEFAULT_PUBDIR."/virtual/".$freedomenv)) return DEFAULT_PUBDIR."/virtual/".$freedomenv;
  else return DEFAULT_PUBDIR;
}

function isRealDb($freedomenv="default") {
  include_once('WHAT/Lib.Prefix.php');
  if (file_exists(DEFAULT_PUBDIR."/virtual/".$freedomenv."/dbaccess.php")) return true;
  return false;
}

function getPhpEnv($freedomenv) {
  include_once('WHAT/Lib.Prefix.php');
  $vdir = setBaseDir($freedomenv);
  if (file_exists($vdir."/dbaccess.php")) $env = $vdir."/dbaccess.php";
  else $env = $vdir."/dbaccess.php";
  return $env;
}

function getShEnv($freedomenv) {
  include_once('WHAT/Lib.Prefix.php');
  $vdir = setBaseDir($freedomenv);
  if (file_exists($vdir."/dbaccess.sh")) $env = $vdir."/dbaccess.sh";
  else $env = $vdir."/dbaccess.sh";
  return $env;
}

function setCurrentDb($freedomenv="default") {
  error_log("Deprecated call to setCurrentDb() in ".__FILE__." : use setCurrentEnv(envName)");
  return setCurrentEnv($freedomenv);
}

function setCurrentEnv($freedomenv="default") {
  include_once('WHAT/Lib.Prefix.php');
  $fcur = fopen(DEFAULT_PUBDIR."/.freedom", 'w');
  fprintf($fcur, $freedomenv);
  fclose($fcur);
  $dpath = DEFAULT_PUBDIR."/virtual/".$freedomenv;
  system("ln -sf \"$dpath/dbaccess.sh\" \"".DEFAULT_PUBDIR."/.freedom.sh\"");
}

function getCurrentDb() {
  error_log("Deprecated call to getCurrentDb in ".__FILE___." : use getCurrentEnv()");
  return getCurrentEnv();
}

function getCurrentEnv() {
  if (file_exists(DEFAULT_PUBDIR."/.freedom")) return file_get_contents(DEFAULT_PUBDIR."/.freedom");
  return "default";
}

function initDbEnv( $freedomenv = "default",
		    $pgservice_core = "anakeen",
		    $pgservice_freedom = "freedom" ) {
  include_once('WHAT/Lib.Prefix.php');
  
  $inphpfile=DEFAULT_PUBDIR."/dbaccess.php.in";
  $inshfile=DEFAULT_PUBDIR."/dbaccess.sh.in";

  $vdir = DEFAULT_PUBDIR."/virtual/".$freedomenv;
  $dbfphp="$vdir/dbaccess.php";
  $dbfsh="$vdir/dbaccess.sh";

  if (!is_dir($vdir)) mkdir($vdir, 0755, true);

  if (file_exists($dbf)) return false;

  $httpu = getenv("httpuser");
  $httpconf = getenv("httpconf");

  $command = "sed -e 's,@PGSERVICE_CORE@,$pgservice_core,g'"
    .        "    -e 's,@PGSERVICE_FREEDOM@,$pgservice_freedom,g'"
    .        "    -e 's,@FREEDOMENV@,$freedomenv,g'"
    ;  
  system("cat $inphpfile | $command > $dbfphp");
  system("cat $inshfile | $command > $dbfsh");

  setCurrentEnv($freedomenv);
}

?>