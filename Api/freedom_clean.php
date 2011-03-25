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


$dbaccess=$action->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Database not found : param FREEDOM_DB";
  exit;
}

$duration=intval($action->GetParam("CORE_LOGDURATION",60)); // default 60 days
$logdelete=sprintf("DELETE FROM doclog where date < '%s'",Doc::getDate(-($duration)));
print "$logdelete\n";

simpleQuery($dbaccess, $logdelete);

global $_SERVER;
$dir=dirname($_SERVER["argv"][0]);
$real=(getHttpVars("real")=="yes");

$dbfreedom=getServiceName($dbaccess);
if ($real) system("PGSERVICE=$dbfreedom psql -f \"$dir/API/freedom_realclean.sql\""); 
else system("PGSERVICE=$dbfreedom psql -f \"$dir/API/freedom_clean.sql\""); 

?>
