<?php
/**
 * Reinit vault files
 *
 * @author Anakeen 2004
 * @version $Id: VaultIndexInit.php,v 1.4 2008/11/28 16:14:34 jerome Exp $
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
$o=new DbObj($dbaccess);
$q=new QueryDb($dbaccess,"DocAttr");
$q->AddQuery("type = 'file'");
$q->AddQuery("usefor != 'Q'");
$la=$q->Query(0,0,"TABLE");


foreach ($la as $k=>$v) {
  $docid=$v["docid"];
  $aid=$v["id"];
  
  $sql = "update doc$docid set {$aid}_vec=null;";
  print "$sql\n";
  $o->exec_query($sql);
  
  //print "$sql2\n";
}

$sql="update doc set fulltext=null;" ;
print "$sql\n";
$o->exec_query($sql);

?>
