<?php
/**
 * WHAT Environnement
 *
 * @author Anakeen 2004
 * @version $Id: wenv.php,v 1.5 2005/11/18 16:01:06 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 */
/**
 */


global $_SERVER;
function writedbenv($dba,$dbcoord="",$dbfree="") {
  $wpub=getenv("wpub");
  if ($dba=="anakeen") $dbf="$wpub/dbaccess.php";
  else $dbf="$wpub/virtual/$dba/dbaccess.php";
  if (! file_exists($dbf)) return false;
  if ($dbcoord=="") $dbcoord=file_get_contents($dbf);
  else $dbcoord="\"$dbcoord\"";
  $dbhost="localhost";
  $dbport="5432";
  if (ereg('"([^"]*)"',$dbcoord,$reg)) {
    $dbcoord=$reg[1];
    if (ereg('dbname=[ ]*([a-z_0-9]*)',$dbcoord,$reg)) {  
      $dbname=$reg[1];
    }
    if (ereg('host=[ ]*([a-z_0-9]*)',$dbcoord,$reg)) {  
      $dbhost=$reg[1];
    }
    if (ereg('port=[ ]*([a-z_0-9]*)',$dbcoord,$reg)) {  
      $dbport=$reg[1];
    }
    $dbpsql="";
    if ($dbhost != "")  $dbpsql.= "--host $dbhost ";
    if ($dbport != "")  $dbpsql.= "--port $dbport ";
    $dbpsql.= "--username anakeen --dbname $dbname ";
  }



  $stderr = fopen('php://stderr', 'w');
  fwrite($stderr,"export dbanakeen=$dba\n");
  fwrite($stderr,"export dbfile=$dbf\n");
  fwrite($stderr,"export dbcoord='$dbcoord'\n");
  fwrite($stderr,"export dbhost=$dbhost\n");
  fwrite($stderr,"export dbport=$dbport\n");
  fwrite($stderr,"export dbname=$dbname\n");
  fwrite($stderr,"export dbpsql='$dbpsql'\n");

  if ($dbfree=="") {
    $dbfree="--username anakeen --dbname freedom";
    $dbf=trim(`$wpub/wsh.php --api=fdl_dbaccess 2>/dev/null`);
    //  print $dbf;

    if (strstr($dbf,"--dbname")) $dbfree=$dbf;
  }
  fwrite($stderr,"export dbfree='$dbfree'\n");


}



?>