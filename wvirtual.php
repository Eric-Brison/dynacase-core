<?php
/** WHAT Create another database
 *
 * @author Anakeen 2004
 * @version $Id: wvirtual.php,v 1.9 2008/05/06 08:43:33 jerome Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 */
/**
 */

include("WHAT/Lib.Common.php");
include("WHAT/wncurses.php");
include("WHAT/wenv.php");

global $_SERVER;

if ($_SERVER['HTTP_HOST'] != "")     {
  print "<BR><H1>:~(</H1>";
  exit;
}

$dbank=getenv("dbanakeen");
$dbaccess="user=anakeen dbname=$dbank";
$dbfree="free$dbank";
if ($argv[1]!="") $dbfree=$argv[1];

ncurses_winit(sprintf(_("Create other database for %s (%s)"),trim(`hostname -f`),trim(`hostname -i`)));
ncurses_getmaxyx($fullscreen, $lines, $columns); 

//---------------------------------------------------

$lhost=exec("dig `domainname` axfr | grep `hostname -f` | grep CNAME | awk '{print $1}'",$outlist,$ret);
if (count($outlist)==0) {
     ncurses_error(_("I can't find hostname alias"));
} 

foreach ($outlist as $k=>$v) {
  $post[] =  substr($v,0,-1);
}

$wact = ncurses_newwin($lines-9, $columns-4, 7, 2);
ncurses_wborder($wact,0,0, 0,0, 0,0, 0,0);
ncurses_wcolor_set($wact,3);

$select=ncurses_select($post,"Select context");

$context=$post[$select];

ncurses_mvaddstr($lines-2, 4, _("Enter name for database ? "));

$dbank=strtolower(ncurses_getln());
ncurses_wclear($wact);

ncurses_mvwaddstr($wact, 3, 4, sprintf(_("Context  : [%s]"),$context));
ncurses_mvwaddstr($wact, 5, 4, sprintf(_("Database : [%s]"),$dbank));

ncurses_wrefresh($wact);

initDbContext($dbank, $context, $dbank."core", $dbank."freedom", "localhost", "5432", "anakeen" );

$cpress=strtoupper(chr($pressed));

ncurses_mvaddstr($lines-2, 4, _("Continue (Y|N) ?").str_repeat(" ",40));
$pressed = ncurses_getch();// wait for a user keypress
$cpress=strtoupper(chr($pressed));
if ($cpress != "Y") exit(1);

ncurses_end();

exit(0);

?>
