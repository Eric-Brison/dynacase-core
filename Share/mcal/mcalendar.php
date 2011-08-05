<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * For test Calendar Widget
 *
 * @author Anakeen 2005
 * @version $Id: mcalendar.php,v 1.13 2005/11/24 13:47:51 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage
 */
/**
 */
?>
<html>

<head>
<style>
html {background-color:#eeeeee}
  body, table, select {
    background-color:#FFF1E8;
    font-family:Tahoma,Arial,Helvetica,sans-serif;
    font-size:9px;
  margin : 0px;
  margin-top : 30px;
  padding : 0px;
  }
  .root {
    border-style: groove;
    border-color: orange; 
     /* border-width: 3px; */
    border-width: 0px;
  }


 .inputzone {
    background-color:white;
    border-style: groove;
    border-color: orange; 
    border-width: 3px;
    margin : 10px;
    padding : 10px;
 }

.event {
     color:blue;
     background-color:white;
     border : 1px solid blue;
     overflow : hidden;
}

.default {
    background-color: white;
    /*  border : 1px solid black; */
    border : 1px dotted black;
    overflow : hidden;
  }
</style>

<?php
$rwhat = '/what/WHAT/Layout/';
$rxml = '/what/jsXMLParser/Layout/';
$rmcal = '/what/mcal/Layout/';
echo '
<script type="text/javascript" src="' . $rwhat . 'geometry.js"></script>
<script type="text/javascript" src="' . $rwhat . 'DHTMLapi.js"></script>
<script type="text/javascript" src="' . $rwhat . 'AnchorPosition.js"></script>
<script language="JavaScript" src="' . $rwhat . 'logmsg.js"></script>
<script language="JavaScript" src="' . $rwhat . 'subwindow.js"></script>
<script type="text/javascript" src="' . $rxml . 'xmldom.js"></script>
<script type="text/javascript" src="' . $rmcal . 'mcallib.js"></script>
<script type="text/javascript" src="' . $rmcal . 'mcalCookie.js"></script>
<script type="text/javascript" src="' . $rmcal . 'mcalmenu.js"></script>
<script type="text/javascript" src="' . $rmcal . 'mcalendar.js"></script>
';
?>

</head>
<body>
<div id="calendarRoot" style="top:0px; left:0px; width:95%; height:90%; position:absolute"></div>
<!-- div id="calendarRoot2" style="top:10px; left:450px; width:400px; height:400px; position:absolute"></div -->

<script type="text/javascript">


    function mhandler(event, cal, evid) {
      var ts = '';
      for (var ia=0; ia<arguments.length; ia++) {
	ts += arguments[ia]+' ';
      }
      alert(ts);
    }

var menu = [
    { id:'newevent', label:'a cette heure', desc:'Nouveau rendez-vous, heure courante', status:2, type:1,
      icon:'Images/mcalendar-new.gif', onmouse:'', amode:3, aevent:1, 
      atarget:'editevent', ascript:'subwindow(400, 700, \'editEvent\', \'/freedom/?sole=Y&app=GENERIC&action=GENERIC_EDIT&classid=CALEVENT&id=0&nh=0&ts=%TS%\');' },
    { id:'newevent', label:'sans heure', desc:'Nouveau rendez-vous, sans heure', status:2, type:1,
      icon:'Images/mcalendar-new.gif', onmouse:'', amode:3, aevent:1, 
      atarget:'editevent', ascript:'subwindow(400, 700, \'editEvent\', \'/freedom/?&sole=Y&app=GENERIC&action=GENERIC_EDIT&classid=CALEVENT&id=0&nh=1\');' },
    ];

//   { id:'getevents', request:'mcalendar-rep.php?ts=%TS%&te=%TE%&' },
//   { id:'eventcard', request:'mcalendar_detail.php?id=%EVID%' },
var sm = [ 
  { id:'getevents', request:'/freedom/index.php?sole=Y&&app=FDL&action=VIEWSCARD&zone=FREEEVENT:XMLEVLIST:T&latest=Y&tmime=text/xml&id=1026&ts=%TS%&te=%TE%&lastrev=%LR%' },
  { id:'eventcard', request:'/freedom/index.php?sole=Y&&app=FDL&action=VIEWSCARD&id=%EVPID%' }  
  ];

 var cd = new Date;
 cd.setTime(cd.getTime()-(2*24*3600*1000));
 
 var cal = new MCalendar('calendarRoot', sm, menu, false, cd.getTime());
    cal.CalHoursPerDay = 10;
    //cal.refreshDelay = (60*1000); // seconds * 1000
    //cal.CalHourDivision = 2;
    cal.Display();
</script>

<div id="inputzone" style="position:absolute; border:1px solid orange; padding:2px; display:none; z-index:1000; ">
<input size="30" id="evtitle" type="text" value=""  onkeypress="return cal.createNewEvent(event);">
</div>


</body>
</html>
