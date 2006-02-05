<?php
/**
 * WHAT Environnement
 *
 * @author Anakeen 2004
 * @version $Id: wenv.php,v 1.6 2006/02/05 09:48:26 marc Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 */
/**
 */


global $_SERVER;

function getBaseDirList() {
  include_once('WHAT/Lib.Prefix.php');
  $bl[] = "anakeen";
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
	
  
function setBaseDir($dba="anakeen") {
  include_once('WHAT/Lib.Prefix.php');
  if (is_dir(DEFAULT_PUBDIR."/virtual/".$dba)) return DEFAULT_PUBDIR."/virtual/".$dba;
  else return DEFAULT_PUBDIR;
}

function isRealDb($dba="anakeen") {
  include_once('WHAT/Lib.Prefix.php');
  if ($dba=="anakeen") return true;
  if (file_exists(DEFAULT_PUBDIR."/virtual/".$dba."/dbaccess.php")) return true;
  return false;
}

function getPhpEnv($dba) {
  include_once('WHAT/Lib.Prefix.php');
  $vdir = setBaseDir($dba);
  if (file_exists($vdir."/dbaccess.php")) $env = $vdir."/dbaccess.php";
  else $env = $vdir."/dbaccess.php";
  return $env;
}

function getShEnv($dba) {
  include_once('WHAT/Lib.Prefix.php');
  $vdir = setBaseDir($dba);
  if (file_exists($vdir."/dbaccess.sh")) $env = $vdir."/dbaccess.sh";
  else $env = $vdir."/dbaccess.sh";
  return $env;
}

function setCurrentDb($dba="anakeen") {
  include_once('WHAT/Lib.Prefix.php');
  $fcur = fopen(DEFAULT_PUBDIR."/.freedom", 'w');
  fprintf($fcur, $dba);
  fclose($fcur);
  if ($dba=="anakeen") $dpath = DEFAULT_PUBDIR;
  else $dpath = DEFAULT_PUBDIR."/virtual/".$dba;
  system("ln -sf $dpath/dbaccess.sh ".DEFAULT_PUBDIR."/.freedom.sh");
}

function getCurrentDb() {
  if (file_exists(DEFAULT_PUBDIR."/.freedom")) return file_get_contents(DEFAULT_PUBDIR."/.freedom");
  return "anakeen";
}

function initDbEnv( $dbenv="anakeen", 
		    $dbserv="",
		    $dbcore="anakeen", 
		    $dbfree="freedom", 
		    $dbhost="localhost", $dbport="5432", $dbuser="anakeen")  {
  include_once('WHAT/Lib.Prefix.php');

  $inphpfile=DEFAULT_PUBDIR."/dbaccess.php.in";
  $inshfile=DEFAULT_PUBDIR."/dbaccess.sh.in";

  if ($dba=="anakeen") return;

  $vdir = DEFAULT_PUBDIR."/virtual/".$dbenv;
  $dbfphp="$vdir/dbaccess.php";
  $dbfsh="$vdir/dbaccess.sh";

  if (!is_dir($vdir)) mkdir($vdir, 0755, true);

  if (file_exists($dbf)) return false;

  $httpu = getenv("httpuser");
  $httpconf = getenv("httpdir");

  $command = "sed -e 's,@prefix@,".DEFAULT_PUBDIR.",g' "
    .        "    -e 's/@HTTPU@/$httpu/g'"
    .        "    -e 's,@HTTPC@,$httpconf,g'"
    .        "    -e 's/@DBENV@/$dbenv/g'"
    .        "    -e 's/@DBSERV@/$dbserv/g'"
    .        "    -e 's/@DBNAME@/$dbcore/g'"
    .        "    -e 's/@DBHOST@/$dbhost/g'"
    .        "    -e 's/@DBPORT@/$dbport/g'"
    .        "    -e 's/@DBUSER@/$dbuser/g'"
    .        "    -e 's/@DBFREEDOM@/$dbfree/g'";
  system("cat $inphpfile | $command > $dbfphp");
  system("cat $inshfile | $command > $dbfsh");

  setCurrentDb($dbenv);
}



?>