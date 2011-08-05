<?php
/**
 * Verify several point for the integrity of the system
 *
 * @author Anakeen 2007
 * @version $Id: checklist.php,v 1.8 2008/12/31 14:37:26 jerome Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage CORE
 */
/**
 */
?>
<html><head>
<LINK REL="stylesheet" type="text/css" HREF="Layout/wg.css" >
<style>
a.context {
 border:solid 1px black;
 margin:0px;
 width:100px;
 display:block;
 float:left;
 cursor:pointer;
   -moz-border-radius:0px 10px 0px 0px;
}
a.context:hover {
  background-color:yellow;
}
</style>
<title>Check List</title>
</head>
<body>
<?php
define("OK","green");
define("KO","red");
define("BOF","orange");

include("../WHAT/Lib.Common.php");

function globalparam($conn) {
  $result = pg_query($conn, "SELECT * FROM paramv where  type='G'");
  if (!$result) {
    
  }
  $pout=array();
  while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
    $pout[$row["name"]]=$row["val"];
  }
  return $pout;
}

//---------------------------------------------------
//------------------- MAIN -------------------------

$wcontext=$_GET["context"];
if (!$wcontext) $wcontext="default";
print "<H1>Check List : <i>$wcontext</i> </H1>";
// detect Contexts
if ($handle = @opendir(DEFAULT_PUBDIR."/context")) {
   /* Ceci est la façon correcte de traverser un dossier. */
   while (false !== ($file = readdir($handle))) {
     if ($file[0]!=".") {
       if (file_exists(DEFAULT_PUBDIR."/context/".$file."/dbaccess.php")) {
	 $contexts[]=$file;
	 if ($file==$wcontext) include(DEFAULT_PUBDIR."/context/".$file."/dbaccess.php");
       }
     }
   }

   closedir($handle);
}

foreach ($contexts as $k=>$v) {
  if ($v==$wcontext) $sty='style="background-color:lightblue";';
  else $sty="";
  print "<a href=\"?context=$v\" $sty class=\"context\">$v</a>";
}
print "<hr style=\"clear:both\">";


$r=@pg_connect("service='$pgservice_core'");
if ($r) $dbr_anakeen=true;
 else $dbr_anakeen=false;

$tout["main connection db"]=array("status"=>$dbr_anakeen?OK:KO,
					"msg"=>$pgservice_core);

if ($dbr_anakeen) {
  $GP=globalparam($r);
  //  print_r2($GP);
  // TEST groups coherence

  $appNameList = array();
  $result = pg_query($r, "SELECT name FROM application;");
  while( $row = pg_fetch_array($result, NULL, PGSQL_ASSOC) ) {
  	$appNameList[] = $row['name'];
  }
  
  $result = pg_query($r, "SELECT * from groups where iduser not in (select id from users);");    
  $pout=array();
  while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
    $pout[$row["iduser"]][]=$row["idgroup"];
  }
  if (count($pout) > 0) $msg=sprintf("%d unreference users<pre>%s</pre>",count($pout),print_r($pout,true));
  else $msg="";
  $tout["unreference user in group"]=array("status"=>(count($pout)==0)?OK:BOF,
					   "msg"=>$msg);

  $result = pg_query($r, "SELECT distinct(idgroup) from groups where idgroup not in (select id from users where isgroup='Y');");    
  $pout=array();
  while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
    $pout[]=$row["idgroup"];
  }
  if (count($pout) > 0) $msg=sprintf("%d users detected as group<br><kbd>%s</kbd>",count($pout),implode(", ",$pout));
  else $msg="";
  $tout["user as group"]=array("status"=>(count($pout)==0)?OK:KO,
			       "msg"=>$msg);
  
  $result = pg_query($r, "SELECT * from action where id_application not in (select id from application);");    
  $pout=array();
  while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
    $pout[]=$row["name"];
  }
  if (count($pout) > 0) $msg=sprintf("%d unreference actions<br><kbd>%s</kbd>",count($pout),implode(", ",$pout));
  else $msg="";
  $tout["unreference actions"]=array("status"=>(count($pout)==0)?OK:BOF,
				     "msg"=>$msg);
  
  $result = pg_query($r, "SELECT * from paramdef where appid  not in (select id from application);");    
  $pout=array();
  while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
    $pout[]=$row["name"];
  }
  $result = pg_query($r, "SELECT * from paramv where appid  not in (select id from application);");    
  while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
    $pout[]=$row["name"];
  }
  if (count($pout) > 0) $msg=sprintf("%d unreference parameters<br><kbd>%s</kbd>",count($pout),implode(", ",$pout));
  else $msg="";
  $tout["unreference parameters"]=array("status"=>(count($pout)==0)?OK:BOF,
					"msg"=>$msg);
  
  $result = pg_query($r, "SELECT * from acl where id_application not in (select id from application);");    
  $pout=array();
  while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
    $pout[]=$row["name"];
  }
  if (count($pout) > 0) $msg=sprintf("%d unreference acl<br><kbd>%s</kbd>",count($pout),implode(", ",$pout));
  else $msg="";
  $tout["unreference acl"]=array("status"=>(count($pout)==0)?OK:BOF,
				 "msg"=>$msg);
  
  $result = pg_query($r, "SELECT * from permission where id_acl not in (select id from acl);");    
  $nb=pg_num_rows($result);
  $result = pg_query($r, "SELECT * from permission where id_user not in (select id from users);");    
  $nb+=pg_num_rows($result);
  $result = pg_query($r, "SELECT * from permission where id_application not in (select id from application);");    
  $nb+=pg_num_rows($result);
  
  if ($nb > 0) $msg=sprintf("%d unreference permission",($nb));
  $tout["unreference permission"]=array("status"=>($nb==0)?OK:BOF,
					"msg"=>$msg);
  
  // Test FREEDOM DB Connection
  $fdb=$GP["FREEDOM_DB"];
  $dbr_freedom=false;
  if ($fdb) {
    $rf=@pg_connect($fdb);
    if ($rf) $dbr_freedom=true;
  }
  
  $tout["connection db dynacase"]=array("status"=>$dbr_freedom?OK:KO,
				       "msg"=>$fdb);
  
  if ($rf) {
    // test double in docfrom    
    $result = pg_query($rf, "SELECT * from (SELECT id, count(id) as c  from doc group by id) as Z where Z.c > 1;");    
    $pout=array();
    while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
      $pout[$row["id"]]=$row["c"];
    }
    if (count($pout) > 0) $msg=sprintf("%d double id detected<pre>%s</pre>",count($pout),print_r($pout,true));
    else $msg="";
    $tout["double doc id"]=array("status"=>(count($pout)==0)?OK:KO,
				 "msg"=>$msg);
    
    // test double in docname 
    $result = pg_query($rf, "select * from (select name, count(name) as c from doc where name is not null and name != '' and locked != -1 group by name) as Z where Z.c >1");    
    $pout=array();
    while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
      $pout[$row["name"]]=$row["c"];
    }
    if (count($pout) > 0) $msg=sprintf("%d double detected<pre>%s</pre>",count($pout),print_r($pout,true));
    else $msg="";
    $tout["double doc name"]=array("status"=>(count($pout)==0)?OK:KO,
				   "msg"=>$msg);

    // test multiple alive
    $result = pg_query($rf, "select id, title from docread where id in (SELECT m AS id  FROM (SELECT min(id) AS m, initid, count(initid) AS c  FROM docread WHERE locked != -1 AND doctype != 'T' GROUP BY docread.initid) AS z where z.c > 1);");    
    $pout=array();
    while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
    	$pout[$row["id"]]=$row["title"];
    }
    if (count($pout) > 0) $msg=sprintf("%d multiple alive<pre>%s</pre>",count($pout),print_r($pout,true));
    else $msg="";
    $tout["multiple alive"]=array("status"=>(count($pout)==0)?OK:KO,
				   "msg"=>$msg);

    // test inheritance
    $result = pg_query($rf, "select * from docfam");    
    $pout=array();
    while ($row = pg_fetch_array($result, NULL, PGSQL_ASSOC)) {
      $fromid=intval($row["fromid"]);
      if ($fromid==0) $fromid="";
      $fid=intval($row["id"]);
      $test = pg_query($rf, 
		       sprintf("SELECT relname from pg_class where oid in (SELECT inhparent from pg_inherits where inhrelid =(SELECT oid FROM pg_class where relname='doc%d'));",
			       $fid));
      $dbfrom=pg_fetch_array($test, NULL, PGSQL_ASSOC);
      if ($dbfrom["relname"] != "doc$fromid") {
	$pout[]= sprintf("Family %s [%d]: fromid = %d, pg inherit=%s",
			 $row["name"],$row["id"],$row["fromid"],$dbfrom["relname"]);
      }
    }
    $tout["family inheritance"]=array("status"=>(count($pout)==0)?OK:KO,
				      "msg"=>implode("<br/>",$pout));
    
    // test groups
    $result = pg_query($rf, "select * from groups");    
    $result2 = pg_query($r, "select * from groups");    
    if (pg_num_rows($result) != pg_num_rows($result2)) $msg="tables group are differents between <b>$dbaccess</b> <br>and <b>$fdb</b>";
    else $msg="";       
    $tout["user group synchro"]=array("status"=>($msg=="")?OK:KO,
				      "msg"=>$msg);

  }

  // Test WEBDAV DB Connection
  $fdb=$GP["WEBDAV_DB"];
  if ($fdb) {
    $dbr_webdav=false;
    if ($fdb) {
      $rw=@pg_connect($fdb);
      if ($rw) $dbr_webdav=true;
    }
    
    $tout["connection db webdav"]=array("status"=>$dbr_webdav?OK:KO,
					"msg"=>$fdb);
  }

  // Test User LDAP (NetworkUser Module)
  $nuAppExists = (array_search('NU', $appNameList)===false)?false:true;
  $ldaphost=$GP["NU_LDAP_HOST"];
  $ldapport=$GP["NU_LDAP_PORT"];
  $ldapmode=$GP["NU_LDAP_MODE"];
  if ($nuAppExists && $ldaphost) {
  	include_once('../NU/Lib.NU.php');

   	$ldapBindDn = $GP['NU_LDAP_BINDDN'];
  	$ldapPassword = $GP['NU_LDAP_PASSWORD'];

  	$baseList = array();
  	array_push($baseList, array('dn' => $GP['NU_LDAP_USER_BASE_DN'], 'filter' => $GP['NU_LDAP_USER_FILTER']));
  	array_push($baseList, array('dn' => $GP['NU_LDAP_GROUP_BASE_DN'], 'filter' => $GP['NU_LDAP_GROUP_FILTER']));
  	
  	foreach( $baseList as $base ) {
  		$testName = sprintf("connection to '%s'", $base['dn']);
  		$tout[$testName] = array();

  		$uri = getLDAPUri($ldapmode, $ldaphost, $ldapport);
  		$conn = ldap_connect($uri);
  		if( $conn === false ) {
  			$tout[$testName]['status'] = KO;
  			$tout[$testName]['msg'] = sprintf("Could not connect to LDAP server '%s': %s", $uri, $php_errormsg);
  			continue;
  		}

  		ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
  		ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);

  		if( $ldapmode == 'tls' ) {
  			$ret = ldap_start_tls($conn);
  			if( $ret === false ) {
  				$tout[$testName]['status'] = KO;
  				$tout[$testName]['msg'] = sprintf("Could not negotiate TLS with server '%s': %s", $uri, ldap_error($conn));
  				continue;
  			}
  		}

  		$bind = ldap_bind($conn, $ldapBindDn, $ldapPassword);
  		if( $bind === false ) {
  			$tout[$testName]['status'] = KO;
  			$tout[$testName]['msg'] = sprintf("Could not bind with bind DN '%s' on server '%s': %s", $ldapBindDn, $uri, ldap_error($conn));
  			ldap_close($conn);
  			continue;
  		}
  		
		$res = ldap_search($conn, $base['dn'], sprintf("(&(objectClass=*)%s)", $base['filter']));
  		if( $res === false ) {
  			$tout[$testName]['status'] = KO;
  			$tout[$testName]['msg'] = sprintf("LDAP search on base '%s' with filter '%s' failed: %s", $base['dn'], $base['filter'], ldap_error($conn));
  			ldap_close($conn);
  			continue;
  		}
  		
  		$count = ldap_count_entries($conn, $res);
  		if( $count === false ) {
  			$tout[$testName]['status'] = KO;
  			$tout[$testName]['msg'] = sprintf("Error counting result entries: %s", ldap_error($conn));
  			ldap_close($conn);
  			continue;
  		}
  		if( $count <= 0 ) {
  			$tout[$testName]['status'] = BOF;
  			$tout[$testName]['msg'] = sprintf("Search returned 0 entries...");
  			ldap_close($conn);
  			continue;
  		}
  		
  		$tout[$testName]['status'] = OK;
  		$tout[$testName]['msg'] = sprintf("Search returned %s entries.", $count);
  		ldap_close($conn);
  	}
  }
  
}

print "<table border=1>";
foreach ($tout as $k=>$v) {
  print sprintf("<tr><td><span style=\"background-color:%s;margin:3px;border:inset 2px %s\">&nbsp;&nbsp;&nbsp;</span></td><td>%s</td><td>%s</td></tr>",
		$v["status"],
		$v["status"],
		$k,
		$v["msg"]);
}
print "</table>";

?>
</body>
</html>