<?php
/** WHAT Create another database
 *
 * @author Anakeen 2004
 * @version $Id: wvirtual.php,v 1.4 2004/10/05 10:29:49 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 */
/**
 */
$pubdir="/home/httpd/what";

include("WHAT/Lib.Common.php");
include("WHAT/wncurses.php");

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


ncurses_getmaxyx(&$fullscreen, $lines, $columns); 

//---------------------------------------------------





$lhost= exec("dig `domainname` axfr | grep `hostname -f` | grep CNAME | awk '{print $1}'",$outlist,$ret);

if ($ret != 0) {

     ncurses_error(_("Command failed"));

} 
  

foreach ($outlist as $k=>$v) {
  $post[] =  substr($v,0,-1);
}
 

  

  $wact = ncurses_newwin($lines-9, $columns-4, 7, 2);
  ncurses_wborder($wact,0,0, 0,0, 0,0, 0,0);
  ncurses_wcolor_set($wact,3);



$select=ncurses_select($post,"Select virtual host");

$virtual=$post[$select];

ncurses_mvaddstr($lines-2, 4, _("Name of anakeen database ?"));

$dbank=strtolower(ncurses_getln());
ncurses_wclear(&$wact);

ncurses_mvwaddstr($wact, 3, 4, sprintf(_("Virtual host : [%s]"),$virtual));
ncurses_mvwaddstr($wact, 5, 4, sprintf(_("Database : [%s]"),$dbank));

ncurses_wrefresh($wact);
$stderr = fopen('php://stderr', 'w');
fwrite($stderr,"export dbanakeen=$dbank\n");
fwrite($stderr,"export snwhat=$virtual\n");


$cpress=strtoupper(chr($pressed));



ncurses_mvaddstr($lines-2, 4, _("Continue (Y|N) ?").str_repeat(" ",40));
$pressed = ncurses_getch();// wait for a user keypress
$cpress=strtoupper(chr($pressed));
if ($cpress != "Y") exit(1);

ncurses_end();

exit(0);

?>