<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

ini_set("max_execution_time", "36000");

include_once ('FDL/freedom_util.php');

$usage = new ApiUsage();
$usage->setText("Fixed multiple alive revision problem");
$usage->verify();

$dbaccess = GetParam("FREEDOM_DB");
if ($dbaccess == "") {
    error_log("Freedom Database not found : param FREEDOM_DB");
    exit(1);
}

$multipleList = array();
$sql = "SELECT m AS id, z.initid AS initid FROM (SELECT min(id) AS m, initid, count(initid) AS c  FROM docread WHERE locked != -1 AND doctype != 'T' GROUP BY docread.initid) AS z where z.c > 1";
$err = simpleQuery($dbaccess, $sql, $multipleList);
if ($err != "") {
    error_log(sprintf("Error searching for multiple alive revision: %s", $err));
    exit(1);
}

foreach ($multipleList as $el) {
    print sprintf("Fixing mutiple alive revision for (initid='%s', id='%s')\n", $el['initid'], $el['id']);
    $res = array();
    $sql = sprintf("UPDATE doc SET locked = -1 WHERE initid = %s AND id = %s AND locked != -1 AND doctype != 'T'", pg_escape_string($el['initid']) , pg_escape_string($el['id']));
    $err = simpleQuery($dbaccess, $sql, $res);
    if ($err != "") {
        error_log(sprintf("Error fixing multiple alive revision for (initid='%s', id='%s'): %s", $el['initid'], $el['id'], $err));
        exit(1);
    }
}

exit(0);
?>