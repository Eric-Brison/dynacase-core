<?php
// ---------------------------------------------------------------
// $Id: updateclass.php,v 1.1 2003/04/25 12:44:21 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Api/Attic/updateclass.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2001
// O*O  Anakeen development team
//  O   dev@anakeen.com
// ---------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or (at
//  your option) any later version.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
// or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
// for more details.
//
// You should have received a copy of the GNU General Public License along
// with this program; if not, write to the Free Software Foundation, Inc.,
// 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
// ---------------------------------------------------------------

$pubdir="/home/httpd/what";
ini_set("include_path", ".:/home/httpd/what:/home/httpd/what/WHAT");

function GetArg($name, $def="") {

  global $ARGS, $argv;;

  static $first=true;;

  if ($first) {

    while (list($k, $v) = each($argv)) {
  
      if (ereg("--(.+)=(.+)", $v , $reg)) {
	$ARGS[$reg[1]]=$reg[2];
      }  else if (ereg("--(.+)", $v , $reg)) {
    
	$ARGS[$reg[1]]=true;
      } 
    }
    $first=false;
  }
  
  
  if (isset($ARGS[$name])) return ($ARGS[$name]); // 
    return($def);
}



$appclass = Getarg("appc","WHAT");
$class = Getarg("class");
$dbname = Getarg("dbname");


include("$pubdir/dbaccess.php");
if ($dbname != "")   $db = ereg_replace("dbname=([^ ]+)","dbname=$dbname", $dbaccess);
else $db = $dbaccess;

include_once("$pubdir/$appclass/Class.$class.php");


$o= new $class();
$dbid=pg_connect($db);
if (! $dbid) {
  print _("cannot access to  database $db\n");
  exit(1);
} else print _("access granted to  database $db\n");


$rq=pg_exec ($dbid, "select * from ".$o->dbtable." LIMIT 1;");
if (!$rq) {
  // table not exist : just create
  $sqlcmds = explode(";",$o->sqlcreate);
  while (list($k,$sqlquery)=each($sqlcmds)) {
    if (chop($sqlquery) != "")
      $sql[]=$sqlquery;
  }
} else {

  $row= pg_fetch_array($rq,0,PGSQL_ASSOC);



  if ($row) {
    $fieds = array_intersect($o->fields,array_keys($row));
    $sql[]= "CREATE TABLE ".$o->dbtable."_old AS SELECT * FROM ".$o->dbtable.";";
  }
  $sql[]= "DROP TABLE ".$o->dbtable.";";
  
  $sqlcmds = explode(";",$o->sqlcreate);
  while (list($k,$sqlquery)=each($sqlcmds)) {
    if (chop($sqlquery) != "")
      $sql[]=$sqlquery;
  }

  if ($row) {
    $sql[]= "INSERT INTO ".$o->dbtable." (".implode(",", $fieds).") SELECT ".implode(",", $fieds). " FROM ".$o->dbtable."_old";
    
    $sql[]= "DROP TABLE ".$o->dbtable."_old;";
  }
}
while (list($k,$v) = each ($sql)) {
  print "Sql:$v\n";
  $rq=@pg_exec ($dbid, $v);
  if (! $rq) {
    if (ereg("create sequence",$v, $reg)) {
      $pgmess = pg_errormessage($dbid);
      echo "[1;33;49m".$pgmess."[0m\n";

    } else {
      $pgmess = pg_errormessage($dbid);
      echo "[1;31;49m".$pgmess."[0m\n";
      echo "[1;31;40m"."ABORTED"."[0m\n";
      break;
    }
    
  }
}

pg_close($dbid);
?>