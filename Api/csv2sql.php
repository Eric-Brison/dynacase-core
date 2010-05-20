<?php
/**
 * to big importation
 *
 * @author Anakeen 2007
 * @version $Id: csv2sql.php,v 1.2 2007/12/13 16:54:21 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage WSH
 */
 /**
 */


include_once("FDL/import_file.php");

$fimport=GetHttpVars("file");

if (!$fimport) {
   print "file needed :usage  --file=<csv file>\n";
   exit(1);
 }
if (seemsODS($fimport)) {
  $cvsfile=ods2csv($fimport);
  $fdoc = fopen($cvsfile,"r");
 } else {
  $fdoc = fopen($fimport,"r");
 }
if (! $fdoc) exit(1);

$dbaccess = getParam('FREEDOM_DB');
$idoc=new doc($dbaccess);
while (!feof($fdoc)) { 


    $buffer = rtrim(fgets($fdoc, 16384));
    $data=explode(";",$buffer);
    $nline++;

    if ($data[0]=='ORDER') {
      if (is_numeric($data[1]))   $orfromid = $data[1];
      else $orfromid = getFamIdFromName($dbaccess,$data[1]);
      $tcolorder[$orfromid]=getOrder($data);
      $cdoc=createDoc($dbaccess,$orfromid);
      $ta=$cdoc->GetTitleAttributes();
      $titles[$orfromid]=array();
      foreach ($ta as $k=>$v) {
	$titles[$orfromid][]=$v->id;
      }

      foreach ($idoc->fields as $k=>$v) {
	if ($cdoc->$v!="")	$tval[$orfromid][$v]="'".$cdoc->$v."'";
      }
      $tval[$orfromid]["id"]="(select nextval ('seq_id_doc'))";
      $tval[$orfromid]["initid"]="(select currval ('seq_id_doc'))";
      $tval[$orfromid]["owner"]=$action->user->id;

    } else if ($data[0]=='DOC') {
      if (is_numeric($data[1]))   $fromid = $data[1];
      else $fromid = getFamIdFromName($dbaccess,$data[1]);
      
      if (! isset($tval[$fromid])) {
	print "-- order not defined for $fromid;\n";
	continue;
      }

      $ini=$tval[$fromid];
      
      $idx=4;
      foreach ($tcolorder[$fromid] as $k=>$v) {
	$ini[$v]="'".pg_escape_string($data[$idx])."'";
	$idx++;
      }
      $title="";
      foreach ($titles[$orfromid] as $k=>$v) {
	$title.= substr($ini[$v],1,-1)." ";
      }
      $ini["title"]="'".pg_escape_string(trim($title))."'";



      $sval=implode($ini,",");
      $skey=implode(array_keys($ini),",");
      $sql=sprintf("INSERT INTO doc%d (%s) values (%s);\n",$fromid,$skey,$sval);
      print $sql;
    }
 }

// update family index when finished
foreach ($titles as $fromid=>$v) {
  $sql=sprintf("select setval ('seq_doc%d',(select max(id) from doc%d));\n",$fromid,$fromid);
  print $sql;
}

?>