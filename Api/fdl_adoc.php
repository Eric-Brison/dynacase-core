<?php
/**
 * Generate Php Document Classes
 *
 * @author Anakeen 2000 
 * @version $Id: fdl_adoc.php,v 1.20 2008/10/30 17:34:31 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



// refreah for a classname
// use this only if you have changed title attributes

include_once("FDL/Lib.Attr.php");
include_once("FDL/Class.DocFam.php");




$appl = new Application();
$appl->Set("FDL",	   $core);


$dbaccess=$appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Freedom Database not found : param FREEDOM_DB";
  exit;
}



$docid = GetHttpVars("docid",0); // special docid

if (($docid !== 0) && (! is_numeric($docid)))  {
  $odocid=$docid;
  $docid   =  getFamIdFromName($dbaccess,$docid);
  if (! $docid) {
    print sprintf(_("family %s not found")."\n",$odocid);
    exit(1);
  }
}


	
$query = new QueryDb($dbaccess,"DocFam");
$query ->AddQuery("doctype='C'");
$query->order_by="id";

  
if ($docid > 0) {
  $query->AddQuery("id=$docid");
  $tid = $query->Query(0,0,"TABLE");
} else {
  // sort id by dependance
  $table1 = $query->Query(0,0,"TABLE");
  $tid=array();
  pushfam(0, $tid, $table1); 
  
}      

if ($query->nb > 0)	{
  $pubdir = $appl->GetParam("CORE_PUBDIR");
  if ($query->nb > 1) { 
    $tii=array(1,2,3,4,5,6,20,21);
    foreach ($tii as $ii) {     
      updateDoc($dbaccess,$tid[$ii]);
      unset($tid[$ii]);
    }
  }

    // workflow at the end
    foreach ($tid as $k=>$v)   {	     
      if ($v["usefor"] == "W") { 
	updateDoc($dbaccess,$v);

	$wdoc= createDoc($dbaccess,$v["id"]);
	$wdoc->CreateProfileAttribute();// add special attribute for workflow
	activateTrigger($dbaccess, $v["id"]);
      }    
    }
  foreach ($tid as $k=>$v)   {	     
    if ($v["usefor"] != "W") { 
      updateDoc($dbaccess,$v);
    }    
  }
}      
    
  function updateDoc($dbaccess,$v) {
    $phpfile=createDocFile($dbaccess,$v);
    print "$phpfile [".$v["title"]."(".$v["name"].")]\n";
    $msg=PgUpdateFamilly($dbaccess, $v["id"],$v["name"]);
    print $msg;    
    activateTrigger($dbaccess, $v["id"]);    
  }

// recursive sort by fromid
function pushfam($fromid, &$tid, $tfam) {
  
  foreach($tfam as $k=>$v) {
   
    if ($v["fromid"]==$fromid) {
      $tid[$v["id"]]=$v;
     
      pushfam($v["id"],$tid,$tfam);
    }
  }
}

?>