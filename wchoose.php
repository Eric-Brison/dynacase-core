<?php
/**
 * WHAT Choose database
 *
 * @author Anakeen 2004
 * @version $Id: wchoose.php,v 1.15 2008/05/06 08:43:33 jerome Exp $
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
  $dvir="$pubdir/context";

//   $post=array();
//   if (is_dir($dvir)) {
//     if ($dh = opendir($dvir)) {
//       while (($file = readdir($dh)) !== false) {
// 	$dbaccess="";
// 	if (@include("$dvir/$file/dbaccess.php")) {
	   
// 	  if ($dbaccess != "")  $post[]=getDBname($dbaccess);
	   
// 	}
	 

//       }
//       closedir($dh);
//     }
//   }

  $post = getBaseDirList();
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

if (!isset($argv[1]) || $argv[1]=="-b") $freedomctx="default";
if ($argv[1]=="-i") $freedomctx=choosedb();
 else {
   if (isRealDb($argv[1])) $freedomctx=$argv[1];
 }

setCurrentContext($freedomctx);
exit(0);
?>