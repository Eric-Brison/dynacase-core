<?php
/*
 * @author Anakeen
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

$locale = substr(getParam("CORE_LCDATE") , 0, 3);
simpleQuery($action->dbaccess, "show datestyle", $dbDate, true, true);
simpleQuery($action->dbaccess, "SELECT current_database();", $databaseName, true, true);

if ((!$force) && $dbDate == "ISO, DMY" && $locale == "iso") {
    print "Database '$databaseName' datestyle is 'ISO, DMY'.\n\tDatabase is clean.\n";
    print "Inspect family parameters and default values only.\n";
    convertFamilyDateToIso($action, $onlyAnalyze);
    return;
}

if (($force) || ($dbDate == "SQL, DMY" && $locale == "dmy")) {
    if (!$force) {
        print "Database datestyle is 'SQL, DMY'.\nDatabase '$databaseName' needs to be migrated\n";
    } else {
        print "Force mode enable.\n";
    }
    print "Conversion dates to iso starts ...\n";
    
    convertDateToIso($action, $onlyAnalyze);
    
    convertFamilyDateToIso($action, $onlyAnalyze);
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
    $sql = "SELECT a1.id, a1.docid, a1.type from docattr a1, docattr a2 where a1.usefor != 'Q' and (a1.type ~ 'date' or a1.type ~ 'timestamp') and a1.frameid=a2.id and a2.type ~ 'array' and a1.id !~ ':';";
    
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
        $sql = sprintf("ALTER DATABASE %s set datestyle = 'ISO, DMY'", pg_escape_identifier($databaseName));
        simpleQuery($action->dbaccess, $sql);
        print "Change database datestyle to 'ISO, DMY'\n";
        $sql = "update paramv set val='iso' where name = 'CORE_LCDATE'";
        simpleQuery($action->dbaccess, $sql);
        print "Change CORE_LCDATE to 'iso'\n";
    }
    $e = microtime(true) - $b;
    printf("End of conversion in %dmin %ds.\n", $e / 60, $e % 60);
}
/**
 * Send date conversion for parameters and default values dd/mm/yyyy to yyyy-mm-dd
 * @param Action $action
 * @param bool $onlyAnalyze
 */
function convertFamilyDateToIso(Action $action, $onlyAnalyze = false)
{
    
    $b = microtime(true);
    $s = new SearchDoc($action->dbaccess, -1);
    $s->setObjectReturn(true);
    $s->addFilter("param ~ E'([0-9]{2})/([0-9]{2})/([0-9]{4})' or defval ~ E'([0-9]{2})/([0-9]{2})/([0-9]{4})'");
    $s->search();
    
    if ($s->count() == 0) {
        print "\tFamily parameters and default date are clean.\n";
        return;
    }
    /**
     * @var DocFam $docfam
     */
    while ($docfam = $s->getNextDoc()) {
        $defVal = $docfam->getOwnDefValues();
        $params = $docfam->getOwnParams();
        $la = $docfam->getAttributes();
        foreach ($la as $attrid => $oAttr) {
            
            if ($oAttr->type == "date" || $oAttr->type == "timestamp") {
                
                if (!empty($defVal[$attrid])) {
                    $date = $defVal[$attrid];
                    
                    $isoDate = preg_replace('/(..)\/(..)\/(....)/', '$3-$2-$1', $date);
                    if ($isoDate != $date) {
                        $docfam->setDefValue($attrid, $isoDate);
                        
                        print "\tChange default value for $attrid : $date to $isoDate\n";
                        if (!$onlyAnalyze) {
                            $err = $docfam->modify();
                            if ($err) {
                                printf("\t%s$err%s", REDCOLOR, STOPCOLOR);
                            } else {
                                printf("\t%s updated.\n", $attrid);
                            }
                        }
                    }
                }
                if ($oAttr->usefor == "Q") {
                    if (!empty($params[$attrid])) {
                        $date = $params[$attrid];
                        
                        $isoDate = preg_replace('/(..)\/(..)\/(....)/', '$3-$2-$1', $date);
                        if ($isoDate != $date) {
                            $docfam->setParam($attrid, $isoDate);
                            print "\tChange parameter value for $attrid : $date to $isoDate\n";
                            if (!$onlyAnalyze) {
                                $err = $docfam->modify();
                                if ($err) {
                                    printf("\t%s$err%s", REDCOLOR, STOPCOLOR);
                                } else {
                                    printf("\t%s updated.\n", $attrid);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    
    $e = microtime(true) - $b;
    printf("Family parameters and default date conversion in %dmin %ds.\n", $e / 60, $e % 60);
}
?>
