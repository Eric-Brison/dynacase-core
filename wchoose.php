<?php

$pubdir="/home/httpd/what";

include("WHAT/Lib.Common.php");
include("WHAT/wncurses.php");

global $_SERVER;

if ($_SERVER['HTTP_HOST'] != "")     {
  print "<BR><H1>:~(</H1>";
  exit;
}

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


ncurses_winit(sprintf(_("Choose database in %s"),gethostbyname(`hostname -f`)));


ncurses_getmaxyx(&$fullscreen, $lines, $columns); 

//---------------------------------------------------


$select=ncurses_select($post);

$dbank=$post[$select];

  $wact = ncurses_newwin($lines-9, $columns-4, 7, 2);
  ncurses_wborder($wact,0,0, 0,0, 0,0, 0,0);
  ncurses_wcolor_set($wact,3);
ncurses_mvwaddstr($wact, 5, 4, sprintf(_("Database : [%s]"),$dbank));

ncurses_wrefresh($wact);
$stderr = fopen('php://stderr', 'w');
fwrite($stderr,"export dbanakeen=$dbank\n");


$cpress=strtoupper(chr($pressed));



ncurses_mvaddstr($lines-2, 4, _("Continue (Y|N) ?").str_repeat(" ",40));
$pressed = ncurses_getch();// wait for a user keypress
$cpress=strtoupper(chr($pressed));
if ($cpress != "Y") exit(1);

ncurses_end();

exit(0);

?>