<?php
/**
 * For TEST widget calendar
 *
 * @author Anakeen 2005
 * @version $Id: mcalendar-rep.php,v 1.7 2005/11/24 13:47:51 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */
global $_GET;
$startp = $_GET["ts"];
$endp = $_GET["te"];
$lastreq = $_GET["LR"];

//  sleep(5);

$events = array(  100 =>array( "time" => $startp + (8*3600), "dura" => 3600, "mode"=> 1 ),
		  1001 =>array( "time" => $startp + (34*3600) - 1800, "dura" => 3400, "mode"=> 1 ),
		  1002 =>array( "time" => $startp + (34*3600) - 1800, "dura" => 5*3600, "mode"=> 1 ),
		  1003 =>array( "time" => $startp + (36*3600) - 1800, "dura" => 3600, "mode"=> 1 ),
		  101 =>array( "time" => $startp + (34*3600), "dura" => 1800 , "mode"=> 1),
		  1011 =>array( "time" => $startp + (34*3600) + 1800, "dura" => 1800 , "mode"=> 1),
		  1013 =>array( "time" => $startp + (34*3600) + 1800 + 2000, "dura" => 6600 , "mode"=> 1),
		  1012 =>array( "time" => $startp + (48*3600), "dura" => (24*3600)-1, "mode"=> 1 ),
		  102 =>array( "time" => $startp + (110*3600), "dura" => 5400, "mode"=> 1),
		  1022 =>array( "time" => $startp + (110*3600), "dura" => 3600, "mode"=> 1),
		  1021 =>array( "time" => $startp + (110*3600), "dura" => 0, "mode"=> 1),
		  1023 =>array( "time" => $startp + (96*3600), "dura" => (3600*20), "mode"=> 0),
		  103 =>array( "time" => $startp + (130*3600), "dura" => 26*3600, "mode"=> 1),
		 );

// $events = array(  1001 =>array( "time" => $startp + (34*3600) - 1800, "dura" => 3400 ),
//                  1002 =>array( "time" => $startp + (34*3600) - 1800, "dura" => 5*3600 ),
//                  1003 =>array( "time" => $startp + (36*3600) - 1800, "dura" => 3600 ),
//                  101 =>array( "time" => $startp + (34*3600), "dura" => 1800 ),
//                  1011 =>array( "time" => $startp + (34*3600) + 1800, "dura" => 1800 ),
// 		 );

// 		 200 =>array( "time" => $startp + (8*3600), "dura" => 3600 ),
//                  201 =>array( "time" => $startp + (34*3600), "dura" => 1800 ),
//                  2011 =>array( "time" => $startp + (34*3600) + 1800, "dura" => 1800 ),
//                  2012 =>array( "time" => $startp + (48*3600), "dura" => (24*3600)-1 ),
//                  202 =>array( "time" => $startp + (110*3600), "dura" => 5400),
//                  2021 =>array( "time" => $startp + (110*3600), "dura" => 0),
// 		 203 =>array( "time" => $startp + (130*3600), "dura" => 26*3600),
// 		 300 =>array( "time" => $startp + (8*3600), "dura" => 3600 ),
//                  301 =>array( "time" => $startp + (34*3600), "dura" => 1800 ),
//                  3011 =>array( "time" => $startp + (34*3600) + 1800, "dura" => 1800 ),
//                  3012 =>array( "time" => $startp + (48*3600), "dura" => (24*3600)-1 ),
//                  302 =>array( "time" => $startp + (110*3600), "dura" => 5400),
//                  3021 =>array( "time" => $startp + (110*3600), "dura" => 0),
// 		 303 =>array( "time" => $startp + (130*3600), "dura" => 26*3600),
// 		 400 =>array( "time" => $startp + (8*3600), "dura" => 3600 ),
//                  401 =>array( "time" => $startp + (34*3600), "dura" => 1800 ),
//                  4011 =>array( "time" => $startp + (34*3600) + 1800, "dura" => 1800 ),
//                  4012 =>array( "time" => $startp + (48*3600), "dura" => (24*3600)-1 ),
//                  402 =>array( "time" => $startp + (110*3600), "dura" => 5400),
//                  4021 =>array( "time" => $startp + (110*3600), "dura" => 0),
// 		 403 =>array( "time" => $startp + (130*3600), "dura" => 26*3600)
header("Content-Type: text/xml");
echo '<?xml version="1.0" encoding="UTF-8"?>';

echo '<eventdesc>';

echo '<menu id="evt_menu">';
// echo '<style font="Arial,Helvetica,sans-serif" size="9" fgcolor="#000081" bgcolor="#E9E3FF" afgcolor="" abgcolor="#C2C5F9" tfgcolor="white" tbgcolor="#000081" />';
echo '  <item id="evt_menu_title" status="2" type="0">';
echo '    <label>Menu evenement</label>';
echo '    <description>Menu evenement</description>';
echo '  </item>';
echo '  <item id="evt_menu_read" status="2" type="1" icon="/what/mcal/Images/defico.png">';
echo '    <label>Afficher</label>';
echo '    <description>Afficher la description complete</description>';
echo '    <action aid="evt_menu_read" amode="0" aevent="0" atarget="event_show" ascript="mcalendar_detail.php?id=%EID%" />';
echo '   </item>';
echo '  <item id="evt_menu_test" status="2" type="1">';
echo '    <label>Test parser</label>';
echo '    <description>Menu permettant de tester le parser</description>';
echo '    <action aid="evt_menu_read" amode="3" aevent="0" atarget="" ascript="alert(\'test parser %EID%\');" />';
echo '   </item>';
echo '  <item id="evt_menu_test" status="2" type="1">';
echo '    <label>Test JS handler</label>';
echo '    <description>Menu permettant de tester le handler JS</description>';
echo '    <action aid="evt_menu_read" amode="2" aevent="0" atarget="" ascript="mhandler" />';
echo '   </item>';
echo '  <item id="evt_menu_delete" status="2" type="1">';
echo '    <label>Supprimer</label>';
echo '    <description>Suppression de cet evenement</description>';
echo '    <action aid="evt_menu_delete" aevent="3" amode="2" atarget="" ascript="mhandler" />';
echo '  </item>';
echo '  <item id="evt_menu_test2" status="2" type="1">';
echo '    <label>Free</label>';
echo '    <description>Visiter le site de Free</description>';
echo '    <action aid="evt_menu_read" amode="1" aevent="0" atarget="free" ascript="http://www.free.fr" />';
echo '   </item>';
echo '</menu>';

foreach ($events as $k => $v) {
  echo '<event id="'.$k.'" rid="evc'.$k.'" cid="evc'.$k.'" dmode="'.(isset($v["mode"])?$v["mode"]:1).'" time="'.$v["time"].'" duration="'.$v["dura"].'">';
  echo '<menuref id="evt_menu" use="'.(isset($v["mode"]) && $v["mode"]==0?0:1).',1,1,1,2,1" />';
  echo '<title>'.getTitle($k).'</title>';
  echo '<content>'.getContent($k,$v).'</content>';
  echo '</event>';
}
echo '</eventdesc>';
return;

function getTitle($x) {
  return "Event number $x";
}
function getContent($x,$v) {
  $color = array( 0 => "#eef1ed", 1 => "#DCE5FF" );
  $r = '<styleinfo>';
  $r .= '<style id="background-color" val="'.(isset($v["mode"])?$color[$v["mode"]]:$color[1]).'"/>';
  $r .= '<style id="color" val="marron"/>';
  $r .= '<style id="border" val="1px solid #8B96AA"/>';
  $r .= '</styleinfo>';
  $r .= '<chtml>';
  $r .= '<img style="vertical-align:middle; width:14" src="/what/mcal/Images/defico.png"/>';
  $r .= '<img style="vertical-align:middle; width:14" src="/what/mcal/Images/defico.png"/>';
  $r .= '<img style="vertical-align:middle; width:14" src="/what/mcal/Images/defico.png"/>';
  $r .= '<span style="vertical-align:middle">'.getTitle($x).' ('.(isset($v["mode"])?$v["mode"]:1).')</span>';
  $r .= '<div>'.strftime("%H:%M",$v["time"]).' - '.strftime("%H:%M",($v["time"] + $v["dura"])).'</div>';
  $r .= '</chtml>';
  return $r;
}
?>
