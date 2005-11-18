<?php
/**
 * WHAT Choose database
 *
 * @author Anakeen 2004
 * @version $Id: wchoose.php,v 1.11 2005/11/18 16:01:06 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 */
/**
 */

include("WHAT/Lib.Common.php");
include("WHAT/wncurses.php");
include("WHAT/wenv.php");

global $_SERVER;


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
  ncurses_getmaxyx($fullscreen, $lines, $columns); 

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

if (isset($_SERVER['HTTP_HOST']))     {
  print "<BR><H1>:~(</H1>";
  exit;
}
if (isset($argv[1])) {
  if ($argv[1]=="-b") $dbank="anakeen";
  else if ($argv[1]!="") $dbank=$argv[1];
 } else $dbank=choosedb();
if (! $dbank) exit(1);
 else writedbenv($dbank);
exit(0);

?>