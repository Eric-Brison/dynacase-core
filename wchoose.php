<?php
/**
 * WHAT Choose database
 *
 * @author Anakeen 2004
 * @version $Id: wchoose.php,v 1.5 2005/07/01 08:36:47 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 */
/**
 */

include("WHAT/Lib.Common.php");
include("WHAT/wncurses.php");

global $_SERVER;
function writedbenv($dba) {
  if ($dba=="anakeen") $dbf=getenv("wpub")."/dbaccess.php";
  else $dbf=getenv("wpub")."/virtual/$dba/dbaccess.php";
  $dbcoord=file_get_contents($dbf);
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
}


function choosedb() {
  global $fullscreen,$lines, $columns;
  $pubdir=getenv("wpub");
  $dvir="$pubdir/virtual";

  $post=array();
  if (is_dir($dvir)) {
    if ($dh = opendir($dvir)) {
      while (($file = readdir($dh)) !== false) {
	$dbaccess="";
	if (@include("$dvir/$file/dbaccess.php")) {
	   
	  if ($dbaccess != "")  $post[]=getDBname($dbaccess);
	   
	}
	 

      }
      closedir($dh);
    }
  }


  ncurses_winit(sprintf(_("Choose database in %s (%s)"),trim(`hostname -f`),trim(`hostname -i`)));
  ncurses_getmaxyx(&$fullscreen, $lines, $columns); 

  $select=ncurses_select($post,"Select database");

  $dbank=$post[$select];

  $wact = ncurses_newwin($lines-9, $columns-4, 7, 2);
  ncurses_wborder($wact,0,0, 0,0, 0,0, 0,0);
  ncurses_wcolor_set($wact,3);
  ncurses_mvwaddstr($wact, 5, 4, sprintf(_("Database : [%s]"),$dbank));

  ncurses_wrefresh($wact);
  ncurses_end();
  return  $dbank;
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ //

if ($_SERVER['HTTP_HOST'] != "")     {
  print "<BR><H1>:~(</H1>";
  exit;
}
if ($argv[1]=="-b") $dbank="anakeen";
else $dbank=choosedb();
writedbenv($dbank);
exit(0);

?>