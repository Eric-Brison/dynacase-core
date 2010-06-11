<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_refresh.php,v 1.22 2008/12/12 17:48:25 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



// refreah for a classname
// use this only if you have changed title attributes

include_once("FDL/Class.Doc.php");
include_once("FDL/Class.SearchDoc.php");

$famId = GetHttpVars("famid",""); // familly filter
$docid = GetHttpVars("docid",""); // doc filter
$method = GetHttpVars("method"); // method to use
$allrev = (GetHttpVars("revision","N")=="Y"); // method to use
$arg = GetHttpVars("arg"); // arg for method
$fldid = GetHttpVars("fldid"); // arg for method


if  ($famId == "") {
  print "arg class needed :usage  --famid=<familly id> [--docid=<doc id>] [--fldid=<folder id>] [--method=<method name>] [--revision=[N|Y]]";
  return;
}


$appl = new Application();
$appl->Set("FDL",	   $core);


$dbaccess=$appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Freedom Database not found : param FREEDOM_DB";
  exit;
}

if ($famId) {
    $f=new_doc($dbaccess,$famId);
    if (! $f->isAlive()) {
        $action->exitError(sprintf( "family %s not exists",$famId));   
    }
   if ($f->doctype != 'C') {
        $action->exitError(sprintf( "document %s not a family",$famId));   
    }
}

$s=new SearchDoc($dbaccess,$famId);
$s->setObjectReturn();
if ($docid > 0) $s->addFilter("id = $docid");
if ($fldid > 0) $s->dirid=$fldid;
if ($allrev) $s->latest=false;
$s->search();

if ($s->searchError()) {
     $action->exitError(sprintf("search error : %s",$s->getError()));
}
$targ = array();
if ($arg != "") $targ[]=$arg;
$card=$s->count();
printf("\n%d documents to refresh\n", $card);
;
while ($doc=$s->nextDoc()) {
  $usemethod= ($method && (method_exists ($doc,$method)));
    if ($usemethod) {
      $ret = call_user_func_array(array($doc, $method), $targ);
    } else $ret='';	
    print $card-$k.")".$doc->title." ".(($usemethod)?"(use $method($arg))":"").get_class($doc).":$ret\n";
    //print $card-$k.")".$doc->title ." - ".$doc->fromid." - ".get_class($doc)." - " .round(memory_get_usage()/1024)."\n";
    
    $doc->refresh();
    $doc->refreshTitle();
    $doc->Modify();
    $k++;
}

     


?>
