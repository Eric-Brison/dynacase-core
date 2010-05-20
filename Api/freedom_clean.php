<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_clean.php,v 1.8 2008/04/25 09:18:15 jerome Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// remove all tempory doc and orphelines values
include_once("FDL/Class.Doc.php");

$appl = new Application();
$appl->Set("FDL",	   $core);

$dbaccess=$appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Freedom Database not found : param FREEDOM_DB";
  exit;
}

global $_SERVER;
$dir=dirname($_SERVER["argv"][0]);
$real=(getHttpVars("real")=="yes");

$dbfreedom=getDbName($dbaccess);
if ($real) system("PGSERVICE=$dbfreedom psql -f \"$dir/API/freedom_realclean.sql\""); 
else system("PGSERVICE=$dbfreedom psql -f \"$dir/API/freedom_clean.sql\""); 

?>
