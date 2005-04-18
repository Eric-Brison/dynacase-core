<?php
/**
 * WHAT ncurses utilities functions
 *
 * @author Anakeen 2004
 * @version $Id: wncurses.php,v 1.3 2005/04/18 12:53:39 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 */
/**
 */

define ("NCURSES_KEY_CR",13);
global $fullscreen;

function ncurses_exit() {
  ncurses_clear();
  ncurses_attron(NCURSES_A_BOLD); 
  ncurses_color_set(1);
  ncurses_mvaddstr(0,1,_("terminal too small (must ne at least 80 columns by 24 lines)"));
  ncurses_attroff(NCURSES_A_BOLD); 
  ncurses_refresh();
  $pressed = ncurses_getch();// wait for a user keypress
  ncurses_end();// clean up our screen
  exit(1);
}

function ncurses_select($tr,$msg="Select",$iselect=0,$pairsel=0) {
  global $fullscreen;
  ncurses_getmaxyx($fullscreen, $lines, $columns);
  $wsel = ncurses_newwin($lines-9, $columns-4, 7, 2);
  ncurses_wborder($wsel,0,0, 0,0, 0,0, 0,0);
  ncurses_wcolor_set($wsel,3);
  foreach ($tr as $k=>$v) {
    if ($k==$iselect) {
      ncurses_wcolor_set($wsel,($pairsel==0)?1:$pairsel);
      ncurses_wattron($wsel,NCURSES_A_REVERSE); 
    } else {
      ncurses_wcolor_set($wsel,1);
    }
    ncurses_mvwaddstr($wsel, $k+1, 4, ($k+1).") $v");
    if ($k==$iselect) {
      ncurses_wattroff($wsel,NCURSES_A_REVERSE); 
    } else {
      ;
    }
    
  }
  ncurses_wrefresh($wsel);

  if ($pairsel == 0) {
  ncurses_mvaddstr($lines-2, 4, $msg);

  //ncurses_refresh();
  $pressed = ncurses_getch();// wait for a user keypress
  $maxsel=count($tr)-1;
  while ($pressed != NCURSES_KEY_CR) {
    switch ($pressed) {
    case NCURSES_KEY_UP:
      if ($iselect > 0) $iselect--;
      break;
    case NCURSES_KEY_DOWN:
      if ($iselect < $maxsel) $iselect++;
      break;
    }
    ncurses_getyx ($wsel, $y, $x);
    ncurses_select($tr,$msg,$iselect,1);
    $pressed = ncurses_getch();

  }
  ncurses_select($tr,$msg,$iselect,4);
  }
  return $iselect;
}

function ncurses_error($err) {
$small = ncurses_newwin(10, 50, 7, 25);
  if (!$small) {
    ncurses_exit();
  }
  ncurses_wborder($small,0,0, 0,0, 0,0, 0,0);
  ncurses_wcolor_set($small,1);
  ncurses_mvwaddstr($small, 4, 5, $ret);
  ncurses_wattron($small,NCURSES_A_BOLD); 
  ncurses_mvwaddstr($small, 5, 5, $err);
  ncurses_wattroff($small,NCURSES_A_BOLD);
// show our handiwork and refresh our small window
  ncurses_wrefresh($small);
  $pressed = ncurses_getch();// wait for a user keypress
  ncurses_end();// clean up our screen

  exit;
}

function ncurses_getln() {
  global $fullscreen;
  $s="";
  $pressed = ncurses_getch();
  while ($pressed != NCURSES_KEY_CR) {
    switch ($pressed) {
    case NCURSES_KEY_BACKSPACE:
      if ($s!="") {
	$s=substr($s,0,-1);
	ncurses_delch();
      }
      break;
    default:
      $s.=chr($pressed);
      break;
    }
    $pressed = ncurses_getch();
  }
  return $s;
}

function ncurses_winit($title) {
  global $fullscreen;
  // we begin by initializing ncurses
  $ncurse = ncurses_init();


  // let ncurses know we wish to use the whole screen
  $fullscreen = ncurses_newwin ( 0, 0, 0, 0); 
  if (!$fullscreen) {
    ncurses_exit();
  }
  ncurses_getmaxyx($fullscreen, $lines, $columns); 
  // draw a border around the whole thing.
  ncurses_border(0,0, 0,0, 0,0, 0,0);
  if(ncurses_has_colors()){
    ncurses_start_color();
    ncurses_init_pair(1,NCURSES_COLOR_RED,NCURSES_COLOR_BLACK);
    ncurses_init_pair(2,NCURSES_COLOR_BLUE,NCURSES_COLOR_BLACK);
    ncurses_init_pair(3,NCURSES_COLOR_YELLOW,NCURSES_COLOR_BLACK);
    ncurses_init_pair(4,NCURSES_COLOR_GREEN,NCURSES_COLOR_BLACK);
    ncurses_init_pair(5,NCURSES_COLOR_MAGENTA,NCURSES_COLOR_BLACK);
    ncurses_init_pair(6,NCURSES_COLOR_CYAN,NCURSES_COLOR_BLACK);
    ncurses_init_pair(7,NCURSES_COLOR_WHITE,NCURSES_COLOR_BLACK);
  }
  ncurses_attron(NCURSES_A_BOLD); 
  ncurses_mvaddstr(0,1,$title);
  ncurses_attroff(NCURSES_A_BOLD); 
  ncurses_color_set(6);

  ncurses_refresh();

  return $fullscreen;
}

function ncurses_getchw($chars) {
  $pressed = ncurses_getch();// wait for a user keypress
  $cpress=strtoupper(chr($pressed));
  if (strpos($chars,$cpress) === false) {
    ncurses_beep();
    $cpress=ncurses_getchw($chars);
  }
  return $cpress;
  
}
function ncurses_execute(&$actions) {
  global $fullscreen;
  ncurses_getmaxyx($fullscreen, $lines, $columns);
  

  $wact = ncurses_newwin($lines-9, $columns-4, 7, 2);
  ncurses_wborder($wact,0,0, 0,0, 0,0, 0,0);
  ncurses_wcolor_set($wact,3);
 
  ncurses_list($actions,0,$wact);
  ncurses_mvaddstr($lines-2, 4, "Execute (Y|N|I) ?               ");

  //ncurses_refresh();
  $cpress= ncurses_getchw("YNI");// wait for a user keypress

  if (($cpress != "Y") && ($cpress != "I")) exit;
  $slice=$lines-14;

  ncurses_list($actions,0,$wact,true);

  $format="%2d)%-".($columns-9)."s";
  foreach ($actions as $k=>$v) {
    $ki=$k+1;
    if ($k > $slice) {
      // need scroll
      $j=1;
      for ($i=$k-$slice;$i<$k;$i++) {


	ncurses_wcolor_set($wact,$colors[$i]);
	ncurses_wattron($wact,NCURSES_A_BOLD); 
	ncurses_mvwaddstr($wact, $j++, 1, sprintf($format,($i+1),substr($actions[$i],0,$columns-9))); 
	ncurses_wattroff($wact,NCURSES_A_BOLD); 

      }
      $ki=$slice+1;
    }
    ncurses_wattron($wact,NCURSES_A_BLINK); 
    ncurses_wcolor_set($wact,5); // OK
    ncurses_mvwaddstr($wact, $ki, 1, sprintf($format,($k+1),substr($v,0,$columns-9)));  
    ncurses_wattroff($wact,NCURSES_A_BLINK);  
    ncurses_wrefresh($wact);
    if (($cpress == "I") && ($v[0] != "#")) {
      ncurses_mvaddstr($lines-2, 4, "(S)kip, (E)xecute, (A)bord, (C)ontinue ?");
      $ccont = ncurses_getchw("SEAC");// wait for a user keypress
     
      if ($ccont == "C") $cpress="Y";
      elseif ($ccont == "A") {
	ncurses_end();
	exit;
      }
    }


    if (($cpress != "I") || ($ccont=="E")) {
      ncurses_mvaddstr($lines-2, 4, "Executing ".($k+1)."...".str_repeat(" ",40));
      ncurses_refresh($wact);
      if ($v[0]!='#') {
	$err=system ("echo `date` \"".addslashes($v)."\" >>/tmp/whatchk.log");
	$err=system ("(".$v.")"." >>/tmp/whatchk.log 2>&1",$ret);
      } 
      //    $err=system ("(".str_replace(array("(",")"),array("\(","\)"),$v).")"." >>/tmp/whatchk.log 2>&1");


      if ($ret != 0) { 
	if ($v[0]=='#') $colors[$k]=5;
	else $colors[$k]=1;
	ncurses_wcolor_set($wact,$colors[$k]); // KO
      } else {
	if ($v[0]=='#') $colors[$k]=5;
	else $colors[$k]=4;
	ncurses_wcolor_set($wact,$colors[$k]); // OK

      }
    } else {
      if ($v[0]=='#') $colors[$k]=5;
      else $colors[$k]=6;
      ncurses_wcolor_set($wact,$colors[$k]); // Skip
      
    }
    ncurses_wattron($wact,NCURSES_A_BOLD); 
    ncurses_mvwaddstr($wact, $ki, 1, sprintf($format,($k+1),substr($v,0,$columns-9))); 
    ncurses_wattroff($wact,NCURSES_A_BOLD); 
    ncurses_wrefresh($wact);
  }
}


function ncurses_list(&$actions,$start=0,$wlist="",$nogetch=false) {
  global $fullscreen;
  ncurses_getmaxyx($fullscreen, $lines, $columns);
  $slice=$lines-14;
  if ($wlist=="")  $wlist = ncurses_newwin($lines-9, $columns-4, 7, 2);
  ncurses_wborder($wlist,0,0, 0,0, 0,0, 0,0);
  ncurses_wcolor_set($wlist,3);

  $maxsel=count($actions);
  $fini=false;
  
  if ($maxsel > $slice) {
    $next=true; // need scroll
  }
  $format="%2d)%-".($columns-9)."s";
  while (!$fini) {
    $end = $start+$slice;
    if ($end > $maxsel) $end = $maxsel ;
    $fini=true;

    for ($k=$start; $k<$end; $k++) {     
      ncurses_mvwaddstr($wlist, $k+1-$start, 1, sprintf($format,($k+1),substr($actions[$k],0,$columns-9)));
    }
    if ($end < $maxsel) ncurses_mvwaddstr($wlist, $end-$start+1, 4, "  next...");  
    else ncurses_mvwaddstr($wlist, $end-$start+1, 4, "           ");  

    ncurses_wrefresh($wlist);
    ncurses_mvaddstr($lines-2, 4, "Press Return Key to continue".str_repeat(" ",40));
    if (!$nogetch) {
      $pressed = ncurses_getch();// wait for a user keypress
    if ($pressed != NCURSES_KEY_CR) {
      $fini=false;
      if ($next) {		
	$fini=false;
	switch ($pressed) {
	case NCURSES_KEY_UP:
	  if ($start > 0) $start--;
	  break;
	case NCURSES_KEY_DOWN:
	  if ($start < ($maxsel-$slice)) $start++;
	  break;
	case  NCURSES_KEY_NPAGE:
	  if ($start < $maxsel) $start+=$slice-1;
	  if ($start >= ($maxsel - $slice)) $start=$maxsel - $slice;
	  break;
	case  NCURSES_KEY_PPAGE:
	  if ($start < $maxsel) $start-=$slice-1;
	  if ($start <0) $start=0;
	  break;
	case  NCURSES_KEY_CR:
	  $fini=true;;

	  break;
	}
      } else {
	
	ncurses_mvaddstr($lines-2, 4, "Press Return Key".str_repeat(" ",40));
      }
    } 
    }
  }

  ncurses_wrefresh($wlist);

  
}

?>