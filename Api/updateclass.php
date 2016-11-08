<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Update the SQL structure of a table of a DbObj Object
 *
 * @param string $appc the application directory (WHAT, FDL, ...)
 * @param string $class the class name of the DbObj Class
 * @param string $dbname the SQL database name (anakeen, freedom)
 * @author Anakeen 2002
 * @version $Id: updateclass.php.in,v 1.8 2008/12/31 14:39:35 jerome Exp $
 * @package FDL
 * @subpackage CORE
 */
/**
 */

include_once ('Class.Application.php');

$usage = new ApiUsage();

$usage->setDefinitionText("Update the SQL structure of a table of a DbObj Object");
$appclass = $usage->addOptionalParameter('appc', "application class folder", null, 'WHAT');
$class = $usage->addRequiredParameter('class', 'Class name');
$db = $usage->addOptionalParameter('dbcoord', "Database name", null, getDbAccess());

$usage->verify();

$phpClass=sprintf("%s/%s/Class.%s.php",DEFAULT_PUBDIR,$appclass,$class);

include_once ($phpClass);

/**
 * @var DbObj $o
 */
$o = new $class($db);

$dbid = pg_connect($db);
if (!$dbid) {
    print _("cannot access to  database $db\n");
    exit(1);
} else {
    print _("access granted to  database $db\n");
}

$sql = array();
$rq = @pg_query($dbid, "select * from " . $o->dbtable . " LIMIT 1;");
if (!$rq) {
    // table not exist : just create
    $o->Create();
} else {
    $row = 0;
    
    if (pg_result_error($rq) == "") {
        if (pg_num_rows($rq) > 0) {
            $row = pg_fetch_array($rq, 0, PGSQL_ASSOC);
            if ($row) {
                $fieds = array_intersect($o->fields, array_keys($row));
                $sql[] = "CREATE TABLE " . $o->dbtable . "_old AS SELECT * FROM " . $o->dbtable . ";";
            }
        }
        $sql[] = "DROP TABLE " . $o->dbtable . ";";
    }
    $sqlcmds = explode(";", $o->sqlcreate);
    while (list($k, $sqlquery) = each($sqlcmds)) {
        if (chop($sqlquery) != "") $sql[] = $sqlquery;
    }
    
    if ($row) {
        $sql[] = "INSERT INTO " . $o->dbtable . " (" . implode(",", $o->fields) . ") SELECT " . implode(",", $o->fields) . " FROM " . $o->dbtable . "_old";
        
        $sql[] = "DROP TABLE " . $o->dbtable . "_old;";
    }
}
while (list($k, $v) = each($sql)) {
    print "Sql:$v\n";
    $rq = @pg_exec($dbid, $v);
    if (!$rq) {
        if (preg_match("/create sequence/", $v, $reg)) {
            $pgmess = pg_errormessage($dbid);
            echo "[1;33;49m" . $pgmess . "[0m\n";
        } else {
            $pgmess = pg_errormessage($dbid);
            echo "[1;31;49m" . $pgmess . "[0m\n";
            echo "[1;31;40m" . "ABORTED" . "[0m\n";
            break;
        }
    }
}

pg_close($dbid);
