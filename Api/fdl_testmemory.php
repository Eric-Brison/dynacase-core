<?php

/**
 * List Animal
 *
 * @author Anakeen 2008
 * @version $Id: zoo_animallist.php,v 1.1 2008/02/26 13:34:14 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage ZOO
 *
 * @global docid Http var : document identificator
 * @global newname Http var : new animal name
 /**
 */


include_once("FDL/Class.Doc.php");
include_once("FDL/Class.SearchDoc.php");

$usage="usage  --mode=>[TABLE|OBJECT|ITEM]> --famid=<family idenificator> --slice=<number od document to retrieve>";

$dbaccess=$appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Freedom Database not found : param FREEDOM_DB";
  exit;
}

$mode=(GetHttpVars("mode"));
if (($mode!='TABLE') && ($mode!='OBJECT') &&($mode!='ITEM'))    $action->exitError("mode needed :\n $usage");  

$FD=array();

$family=GetHttpVars("famid");
$slice=GetHttpVars("slice",10);
$s=new SearchDoc($dbaccess,$family);
$s->slice=$slice;
$s->orderby='id desc';
//$s->setDebugMode();
$t=$s->search();
//print_r2($s->getDebugInfo());
foreach ($t as $v) $ids[]=$v["id"];


$time_start = microtime(true); 
$memory_start=memory_get_usage();
foreach ($ids as $id) {
  if ($mode=="OBJECT")  $d=new_doc($dbaccess,$id);
  if ($mode=="ITEM") {
    $d=getDocObject($dbaccess,getTdoc($dbaccess,$id));
  } else $d=getTdoc($dbaccess,$id);
  $stat[$id]=array("memory"=>memory_get_usage(),		   
		   "time"=>microtime(true));
  if (is_array($d)) $tf[$d["fromid"]]=$d["fromid"];
  else $tf[$d->fromid]=$d->fromid;
}

$time_end = microtime(true);
$memory_end=memory_get_usage();


$lmem=$memory_start;
$ltime=$time_start;
foreach ($stat as $id=>$v) {
  //  printf("%3d | %3dKo | %3dms |\n",$id,	 ($v["memory"] - $lmem)/1024,	 ($v["time"] - $ltime)*1000);
  $lmem=$v["memory"];
  $ltime=$v["time"];
}

printf("TOT (%6s) | %3dKo | %3dms | DFAM=%s,CARD=%d,MOY=%3dms\n",$mode,
       ($memory_end - $memory_start )/1024,
       ($time_end - $time_start)*1000,count($tf),count($ids),
       ($time_end - $time_start)*1000/count($ids) );
return;

?>