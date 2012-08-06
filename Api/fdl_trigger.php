<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: fdl_trigger.php,v 1.8 2007/05/22 16:06:29 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */
// refreah for a classname
// use this only if you have changed title attributes
include_once ("FDL/Lib.Attr.php");

$usage = new ApiUsage();

$usage->setText("Return sql code to inject trigget in base");
$docid = $usage->addOption("docid", "special docid", null, 0);
$trigger = $usage->addOption("trigger", "trigger", null, "-");
$trig = ($trigger != "-");
$drop = ($trigger == "N");

$usage->verify();

$appl = new Application();
$appl->Set("FDL", $core);

$dbaccess = $appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
    print "Database not found : param FREEDOM_DB";
    exit;
}

if ($docid != - 1) {
    $query = new QueryDb($dbaccess, "Doc");
    $query->AddQuery("doctype='C'");
    
    if ($docid > 0) $query->AddQuery("id=$docid");
    
    $table1 = $query->Query(0, 0, "TABLE");
    
    if ($query->nb > 0) {
        
        $pubdir = $appl->GetParam("CORE_PUBDIR");
        
        while (list($k, $v) = each($table1)) {
            $doc = createDoc($dbaccess, $v["id"]);
            
            if ($trig) print $doc->sqltrigger($drop) . "\n";
            else {
                $triggers = $doc->sqltrigger(false, true);
                
                if (is_array($triggers)) {
                    print implode(";\n", $triggers);
                } else {
                    print $triggers . "\n";
                }
            }
            print implode(' ', $doc->getSqlIndex()) . "\n";
        }
    }
}

if (($docid == - 1) || ($docid == 0)) {
    
    include_once ("FDL/Class.DocFam.php");
    $doc = new DocFam($dbaccess);
    
    $doc->doctype = 'C';
    $doc->fromid = 'fam';
    if ($trig) print $doc->sqltrigger($drop) . "\n";
    else {
        if (is_array($doc->sqltcreate)) {
            print implode(";\n", $doc->sqltcreate);
        } else {
            print $doc->sqltcreate . "\n";
        }
    }
}
?>