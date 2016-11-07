<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Default install directory
 *
 * @author Anakeen
 * @package FDL
 * @subpackage CORE
 */
/**
 */
global $pubdir;
$pubdir = dirname(__DIR__);

set_include_path($pubdir . PATH_SEPARATOR . "$pubdir/WHAT" . PATH_SEPARATOR . get_include_path());

ini_set("session.use_cookies", "0");
ini_set("session.name", "session");
@ini_set("session.use_trans_sid", "0");
ini_set("session.cache_limiter", "nocache");
ini_set("magic_quotes_gpc", "Off");
ini_set("default_charset", "utf-8");
ini_set("pcre.backtrack_limit", max(ini_get("pcre.backtrack_limit") , 10000000));
//ini_set("error_reporting", ini_get("error_reporting") & ~E_NOTICE);
define("DEFAULT_PUBDIR", $pubdir);
// Maximum length of a filename (should match your system NAME_MAX constant)
define("MAX_FILENAME_LEN", 255);
