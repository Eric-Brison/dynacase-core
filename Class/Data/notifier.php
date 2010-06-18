<?php

/**
 * docoments event pool notifier
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 */


include_once("WHAT/Lib.Common.php");

function getNotifyHisto($date,$nextdelay=5,$limit=1000) {
  $c=@pg_pconnect(sprintf("service='%s'",getServiceFreedom()));
  if ($c) {
    $r["date"]=date("Y-m-d H:i:s");
    $r["delay"]=$nextdelay;
    if ($date && $date!="null") {
      $sql=sprintf("select * from doclog where level=4 and  date >= '%s' and date < '%s' limit %d",
		   pg_escape_string($date),pg_escape_string($r["date"]),$limit);
      
    $r["sql"]=$sql;
      $result = @pg_query($c,$sql);
      if ($result) {
	$nbrows=pg_numrows ($result);
	if ($nbrows>0) {
	  $r["notifications"] = pg_fetch_all($result);  
	  foreach ($r["notifications"] as $k=>$v) if ($v["arg"]) $r["notifications"][$k]["arg"]=unserialize($v["arg"]);       
	}		
      } else {
	$r["error"]=pg_last_error($c);
      }
    }   
  }
  return json_encode($r);
}


//$_POST["date"]='2009-11-19';
$a=getNotifyHisto($_POST["date"],10);
print($a);
?>