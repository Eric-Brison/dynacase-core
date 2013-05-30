<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Migrate date which are in arrays
 */

include_once ('FDL/Class.Doc.php');
include_once ("FDL/Lib.Dir.php");
global $action;

define("REDCOLOR", "\033" . '[01;31m');
define("GREENCOLOR", "\033" . '[01;32m');
define("STOPCOLOR", "\033" . '[0m');

$usage = new ApiUsage();

$usage->setDefinitionText("Update date from french to iso");
$force = ($usage->addOptionalParameter("force", "force conversion although the configuration is good", array(
    "yes",
    "no"
) , "no") == "yes");
$onlyAnalyze = ($usage->addOptionalParameter("analyze", "only analyze ", array(
    "yes",
    "no"
) , "no") == "yes");

$usage->verify();

$locale = getLcdate();
simpleQuery($action->dbaccess, "show datestyle", $dbDate, true, true);

if ((!$force) && $dbDate == "ISO, DMY" && $locale == "iso") {
    print "Config Date is clean.\n";
    return;
}

if (($force) || ($dbDate == "SQL, DMY" && $locale == "dmy")) {
    if (!$force) {
        print "Sql/French Config Date Detected.\n";
    }
    print "Convertion to iso begin...\n";
    convertDateToIso($action, $onlyAnalyze);
} else {
    
    $err = sprintf("Wrong Config Date detected : CORE_LCDATE= '%s', database datestyle='%s'\n", $locale, $dbDate);
    $action->exitError($err);
}
/**
 * Send date conversion dd/mm/yyyy to yyyy-mm-dd
 * @param Action $action
 * @param bool $onlyAnalyze
 */
function convertDateToIso(Action $action, $onlyAnalyze = false)
{
    $sql = "SELECT a1.id, a1.docid, a1.type from docattr a1, docattr a2 where (a1.type ~ 'date' or a1.type ~ 'timestamp') and a1.frameid=a2.id and a2.type ~ 'array';";
    
    simpleQuery($action->dbaccess, $sql, $res);
    
    $b = microtime(true);
    foreach ($res as $attr) {
        $famid = $attr["docid"];
        $famName = getNameFromId($action->dbaccess, $famid);
        $attrName = pg_escape_string($attr["id"]);
        
        $sql = sprintf("select count(%s) from doc%d where  %s ~ '/';", $attrName, $famid, $attrName);
        simpleQuery($action->dbaccess, $sql, $count, true, true);
        if ($count > 0) {
            printf("Family %s - Attribute %s : %d documents to %supdate%s\n", $famName, $attrName, $count, REDCOLOR, STOPCOLOR);
        } else {
            
            printf("Family %s - Attribute %s : %sOK%s\n", $famName, $attrName, GREENCOLOR, STOPCOLOR);
        }
        if ((!$onlyAnalyze) && $count > 0) {
            $b1 = microtime(true);
            $sql = sprintf("UPDATE doc%d set %s=regexp_replace(%s, E'(..)/(..)/(....)', E'\\\\3-\\\\2-\\\\1','g') where %s ~ '/';", $famid, $attrName, $attrName, $attrName);
            //print "$sql\n";
            simpleQuery($action->dbaccess, $sql);
            $partDelay = microtime(true) - $b1;
            printf("\t%s updated in %ds.\n", $attrName, $partDelay);
        }
    }
    if (!$onlyAnalyze) {
        simpleQuery($action->dbaccess, "SELECT current_database();", $databaseName, true, true);
        $sql = sprintf("ALTER DATABASE %s set datestyle = 'iso, dmy'", $databaseName);
        simpleQuery($action->dbaccess, $sql);
        print "Change database datestyle to 'iso, dmy'\n";
        $sql = "update paramv set val='iso' where name = 'CORE_LCDATE'";
        simpleQuery($action->dbaccess, $sql);
        print "Change CORE_LCDATE to 'iso'\n";
    }
    $e = microtime(true) - $b;
    printf("End of conversion in %dmin %ds.\n", $e / 60, $e % 60);
}
?>
