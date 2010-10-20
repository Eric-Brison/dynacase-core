<?php
/**
 * Clean file name
 * delete \ characters
 *
 * @author Anakeen 2010
 * @version $Id:  $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */
ini_set("max_execution_time", "36000");


include_once('FDL/Class.Doc.php');
include_once('FDL/Class.DocFam.php');
include_once('FDL/Class.DocVaultIndex.php');
include_once('VAULT/Class.VaultFile.php');


$dbaccess=GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Freedom Database not found : param FREEDOM_DB";
  exit;
}

$limitFamily=$action->getArgument("family");

$o=new DbObj($dbaccess);

$qf=new QueryDb($dbaccess,"DocFam");
if ($limitFamily) $qf->AddQuery(sprintf("id ='%d' or name='%s'",$limitFamily,pg_escape_string($limitFamily)));
$rf=$qf->query(0,0,"TABLE");

if ($qf->nb == 0) {
    $action->exitError("no family found");
}

foreach ($rf as $k=>$vf) {
    printf("Family %s ",$vf["name"]);
    $q=new QueryDb($dbaccess,"DocAttr");
    $q->AddQuery("type = 'file' or type='image'");
    $q->AddQuery("usefor != 'Q'");
    $q->AddQuery("id !~ '^:'");
    $q->AddQuery(sprintf("docid = %d",$vf["id"]));
    //$q->AddQuery("frameid not in (select id from docattr where type~'array')");
    $la=$q->Query(0,0,"TABLE");
    if ($q->nb >0 ) {
        print "reset filenames\n";
        $s=sprintf("update doc%d set ",$vf["id"]);
        $w='';
        foreach ($la as $ka=>$va) {
            $w.=sprintf("%s= replace(%s, E'\\\\', ''),",$va["id"],$va["id"]);
        }
        $s.=substr($w,0,-1);
        $s.=" where values ~ E'\\\\\\\\';";
        $o->exec_query($s);
    } else {
        print "no filenames\n";
    }
    
    
}

    print "reset vault...";
    $o->exec_query("UPDATE vaultdiskstorage set name = replace(name, E'\\\\', '') where name ~ E'\\\\\\\\';");
    print "DONE\n";


?>
