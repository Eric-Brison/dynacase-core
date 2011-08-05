<?php
/**
 * WHAT Environnement
 *
 * @author Anakeen 2004
 * @version $Id: wenv.php,v 1.11 2008/05/06 17:04:16 jerome Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
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
  system(sprintf("ln -sf %s/dbaccess.sh %s/.freedom.sh", escapeshellarg($dpath), escapeshellarg(DEFAULT_PUBDIR)));
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

  $sed_1 = sprintf('s/@PGSERVICE_CORE@/%s/g', str_replace('/', '\/', $pgservice_core));
  $sed_2 = sprintf('s/@PGSERVICE_FREEDOM@/%s/g', str_replace('/', '\/', $pgservice_freedom));
  $sed_3 = sprintf('s/@FREEDOM_CONTEXT@/%s/g', str_replace('/', '\/', $freedomctx));

  $command = sprintf("sed -e %s -e %s -e %s",
    escapeshellarg($sed_1),
    escapeshellarg($sed_2),
    escapeshellarg($sed_3)
  );

  system(sprintf("cat %s | $command > %s", escapeshellarg($inphpfile), escapeshellarg($dbfphp)));
  system(sprintf("cat %s | $command > %s", escapeshellarg($inshfile), escapeshellarg($dbfsh)));

  setCurrentContext($freedomctx);
}

?>