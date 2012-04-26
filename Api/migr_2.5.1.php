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
 * @version $Id: migr_2.5.1.php,v 1.2 2007/02/14 16:13:57 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */
// refreah for a classname
// use this only if you have changed title attributes
include_once ("FDL/Class.Dir.php");
include_once ("FDL/Class.DocFam.php");

$usage = new ApiUsage();
$usage->setText("Migration 2.5.1");
$usage->verify();

function addFamIndexes($dbaccess)
{
    
    $query = new QueryDb($dbaccess, "Docfam");
    $lfam = $query->Query(0, 0, "TABLE");
    foreach ($lfam as $k => $v) {
        print sprintf("create index doc_initid%d on doc%d(initid);\n", $v["id"], $v["id"]);
        #    print sprintf("create index doc_fldrels%d on doc%d(fldrels);\n",$v["id"],$v["id"]);
        
    }
    
    $table1 = $query->Query();
}
function addProfIndexes($dbaccess)
{
    
    $query = new QueryDb($dbaccess, "Docfam");
    $lfam = $query->Query(0, 0, "TABLE");
    foreach ($lfam as $k => $v) {
        print sprintf("create index doc_profidid%d on doc%d(profid);\n", $v["id"], $v["id"]);
    }
    
    $table1 = $query->Query();
}
function updateFldrel($dbaccess)
{
    
    $query = new QueryDb($dbaccess, "QueryDir");
    $query->AddQuery("qtype='S'");
    $lfam = $query->Query(0, 0, "TABLE");
    foreach ($lfam as $k => $v) {
        print sprintf("update fld set qtype=qtype where dirid=%s and childid=%s;\n", $v["dirid"], $v["childid"]);
    }
    
    $table1 = $query->Query();
}
$dbaccess = GetParam("FREEDOM_DB");

addProfIndexes($dbaccess);
//updateFldrel($dbaccess);

?>