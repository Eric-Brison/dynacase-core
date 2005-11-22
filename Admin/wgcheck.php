<?php
/**
 * Util function for update and initialize application
 *
 * @author Anakeen 2005
 * @version $Id: wgcheck.php,v 1.5 2005/11/22 09:37:58 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
/**
 */
session_start();
global $_COOKIE;
$sid=$_COOKIE['adminsession'];
if ($sid) session_id($sid);
else $sid=session_id();
$uri=$_SERVER["REQUEST_URI"];
$buri=substr($uri,0,strrpos($uri,"/")+1);
setcookie("adminsession",$sid , time()+3600, "$buri");
?>
<html><head>
<title>FREEDOM check applications</title>

<LINK REL="stylesheet" type="text/css" HREF="Layout/wg.css" >
<script language="JavaScript" src="../WHAT/Layout/logmsg.js"></script>
<script>
var req;
var cmdcontinue=false;
var easy=true;
var ncmd=0;
var maxcmd=0;
var inprogress=false;
function sendCmds(n) {
  
}

function sendCmd(n) {
  if (inprogress) return; // one request only
    // branch for native XMLHttpRequest object
    if (window.XMLHttpRequest) {
        req = new XMLHttpRequest(); 
    } else if (window.ActiveXObject) {
      // branch for IE/Windows ActiveX version
      isIE = true;
      req = new ActiveXObject("Microsoft.XMLHTTP");
    }
    if (req) {
        req.onreadystatechange = processReqChange;
        req.open("POST", 'wgexecute.php', true);
	req.setRequestHeader("Content-type", "application/x-www-form-urlencoded"); 
        req.send("number="+n);
	var o=document.getElementById('err'+n);
	if (o) o.innerHTML="<img src=\"Images/progressbar.gif\"><blink>Executing...</blink>";
	o=document.getElementById('easycr');
	if (o) o.innerHTML="<img src=\"Images/progressbar.gif\"><br><blink>Executing "+n+"...</blink>";
	
	inprogress=true;

    }
    ncmd=n+1; // next cmd
    if (n>2) {
      n=n-2;
      var off=document.location.href.indexOf('#');
      if (cmdcontinue && (off > 0)) {
	document.location.href=document.location.href.substring(0,off)+'#trname'+n;
      } 
    }


}
function processReqChange() {
    // only if req shows "loaded"
  inprogress=false; 
  var o=document.getElementById('easycr');
  if (o) o.innerHTML='Done';
    if (req.readyState == 4) {
        // only if "OK"
        if (req.status == 200) {
            // ...processing statements go here...
	  //  alert(req.responseText);
	  if (req.responseXML) {
	    var elts = req.responseXML.getElementsByTagName("status");
	    if (elts.length == 1) {
	    var elt=elts[0];
	    var code=elt.getAttribute("code");
	    var number=elt.getAttribute("number");
	     o=document.getElementById('spi'+number);
	    if (o) {
	      if (code=="OK") {
		o.className="G";
	      } else {
		o.className="E";
	      }
	      o=document.getElementById('sp'+number);
	      if (o && (code != 'OK')) o.innerHTML=code;
	      elts = req.responseXML.getElementsByTagName("msg");
	      elt=elts[0];

	      o=document.getElementById('err'+number);
	      if (o) o.innerHTML=elt.firstChild.nodeValue;
	      if (((code=="OK")||(code=="SKIP")) && cmdcontinue) {
		if ((parseInt(number)+1) < maxcmd)	sendCmd(parseInt(number)+1);
		else if (confirm('Finish\nGo to FREEDOM now ?')) {
		  document.location.href="../";
		}
	      }
	      if (((code!="OK")&&(code!="SKIP")) && cmdcontinue) {
		alert(code+' : update aborted');
		displaynext();
	      }
	    }
	    } else {
	      alert('no status\n'+req.responseText);
	      displaynext();
	    }
	  } else {
	    alert('no xml\n'+req.responseText);
	    displaynext();
	  }
	  
	  
        } else {
            alert("There was a problem retrieving the XML data:\n" +
                req.statusText);
	    displaynext();
        }
    }
}
function displaynext() {
  var n,i,o,oi;
  document.getElementById('dsimple').style.display='none';
  document.getElementById('dcr').style.display='none';
  document.getElementById('dcmd').style.display='';
  easy=false;
  for (i=0;i<maxcmd;i++) {
      o=document.getElementById('tdi'+i);
      oi=document.getElementById('spi'+i);
      if (o && oi) o.appendChild(oi);
    }
  }

addEvent(window,"load",function al() {document.getElementById('dcmd').style.display='none';});
</script>
</head>
<body>
<?php

include("WHAT/Lib.Common.php");
include("WHAT/Lib.WCheck.php");


$err=checkPGConnection();
if ($err=="") {
  $err=getCheckApp($pubdir,$applications);
  if ($err) $msg=_("create databases ?");
  if ($err=="") {
    $err=getCheckActions($pubdir,$applications,$actions);
    if ($err == "") {
      $_SESSION["actions"] = $actions;
    }
 }
 }
?>

<div id="dsimple" class="frame">
<?php
  if ($err == "") {
    print '<table width="100%"><tr>';
    print '<td rowspan="2" width="85px"><div align="right" title="Click to launch easy install" class="bouton" onclick="cmdcontinue=true;sendCmd(ncmd)">Easy</div></td><td style="height:30px;">';
    foreach ($actions as $k=>$v) {
      print sprintf("<img class=\"button\" onclick=\"if (!easy) sendCmd(%s)\"  id=\"spi%s\" src=\"Images/option.png\">",  $k,$k);
    } 
    print '</td><td  width="85px" rowspan="2" align="right"><div title="Click for special install for expert" class="bouton" onclick="document.getElementById(\'dcr\').style.display=\'\';document.getElementById(\'dsimple\').style.display=\'none\'">Expert</div></td></tr>';
    print '<tr><td><span id="easycr"></span></td></tr>';
    print '</table>';
  }
?>
</div>
<div id="dcr" class="frame" style="display:none">

<table width="100%"><tr><td><H1>Applications state</H1></td><td align="right"><div class="bouton" onclick="displaynext()"
<?php if ($err) print "style=\"display:none\"";?>
>Next</div>

</td></tr></table>

<table cellspacing="0" align="center" class="app"><tr><th>Application</th><th>DB version</th><th>file version</th><th>State</th><th>Machine</th></tr>

<?php
if ($err == "") {
  foreach ($applications as $k=>$v) {

    print sprintf("<tr><td>%s</td><td>%s&nbsp;</td><td>%s&nbsp;</td><td ><span class=\"%s\"><img src=\"Images/option.png\"></span>%s&nbsp;</td><td>%s&nbsp;</td></tr>",
		  $v["name"],
		  $v["vdb"],
		  $v["vfile"],
		  ($v["chk"]=="")?"G":$v["chk"],
		  $v["chk"],
		  $v["machine"]);
  } 
 } else {
  print sprintf("<tr class=\"E\"><td colspan=\"5\">%s</td></tr>",$err);
  if ($msg) {    
    print sprintf("<tr class=\"E\"><td colspan=\"5\"><button onclick=\"document.location.href='wgdbcreate.php'\">%s</button></td></tr>",$msg);
    
  }
 
 }
?>
</table></div>

<div id="dcmd" class="frame"  style="display:">

<table width="100%"><tr><td><H1>Update part</H1></td><td align="right"><div id="bstart" class="bouton" onclick="cmdcontinue=true;sendCmd(ncmd);this.style.display='none';document.getElementById('bstop').style.display=''">Next</div><div id="bstop" class="bouton" style="display:none" onclick="cmdcontinue=false;this.style.display='none';document.getElementById('bstart').style.display=''">Stop</div></td></tr></table>

<table cellspacing="0" align="center" class="app"><tr><th>Commande</th><th>Status</th><th>Message</th></tr>
<?php
  if ($err=="") {
    foreach ($actions as $k=>$v) {
      print sprintf("<tr  id=\"cmd%s\" ><td><a name=\"trname%s\">%s</a></td><td id=\"tdi%s\">&nbsp;<span  id=\"sp%s\" ></span></td><td><div  class=\"msg\" id=\"err%s\"></div></td></tr>",
		    $k,$k,$v,$k,$k,$k,$k,$k);
    }
    print sprintf("<script> maxcmd=%d;</script>",count($actions));
  } else {
    print sprintf("<tr class=\"E\"><td colspan=\"5\">%s</td></tr>",$err);
  }


?>


</body></html>
