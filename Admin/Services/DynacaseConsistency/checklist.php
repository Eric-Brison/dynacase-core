<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Verify several point for the integrity of the system
 *
 * @author Anakeen
 * @version $Id: checklist.php,v 1.8 2008/12/31 14:37:26 jerome Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
/**
 */
?>
<html><head>
<style>
a.context {
 border:solid 1px black;
 margin:0px;
 width:100px;
 display:block;
 float:left;
 cursor:pointer;
   -moz-border-radius:0px 10px 0px 0px;
}
a.context:hover {
  background-color:yellow;
}
</style>
<title>Check List</title>
</head>
<body>
<?php
include ("../../../WHAT/Lib.Common.php");
require_once 'WHAT/autoload.php';
//---------------------------------------------------
//------------------- MAIN -------------------------
$dbaccess = getDbAccess();
print "<H1>Check Database <i>$dbaccess</i> </H1>";

include_once "Class.CheckDb.php";

$a = new checkDb($dbaccess);

$tout = $a->getFullAnalyse();

print '<table border=1 rules="all">';
foreach ($tout as $k => $v) {
    print sprintf("<tr><td><span style=\"background-color:%s;margin:3px;border:inset 2px %s\">&nbsp;&nbsp;&nbsp;</span></td><td>%s</td><td>%s</td></tr>", $v["status"], $v["status"], $k, $v["msg"]);
}
print "</table>";
?>
</body>
</html>
