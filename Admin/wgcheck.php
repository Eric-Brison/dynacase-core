<?php
/**
 * Util function for update and initialize application
 *
 * @author Anakeen 2005
 * @version $Id: wgcheck.php,v 1.1 2005/11/08 17:16:34 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
/**
 */
?>
<html><head>
<title>FREEDOM check applications</title>

<style type="text/css">
    TABLE.app TD { border-top:solid blue 1px;
  }
    TABLE.app {
    border: 1px solid;width:80%;
    }
TABLE.app TR {background-color:green;}
TABLE.app TR.U {background-color:orange;}
TABLE.app TR.E {background-color:magenta;}
TABLE.app TR.D {background-color:red;}
TABLE.app TR.I {background-color:yellow;}
.msg {
 height:40px;
 overflow:hidden;
 font-family:courier;
 font-size:7pt;
 width:200px;
}
BUTTON {
 border:outset;
}
BUTTON.over {
 border:inset;
  background-color:lightgreen;
}
</style>
<script>
var req;
var cmdcontinue=true;
function sendCmds(n) {
  
}

function sendCmd(n) {
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
	var o=document.getElementById('sp'+n);
	if (o) o.innerHTML="<blink>Executing...</blink>";

    }
}
function processReqChange() {
    // only if req shows "loaded"
    if (req.readyState == 4) {
        // only if "OK"
        if (req.status == 200) {
            // ...processing statements go here...
	  //  alert(req.responseText);
	  var statuss = req.responseXML.getElementsByTagName("status");
	  var status=statuss[0];
	  var code=status.getAttribute("code");
	  var number=status.getAttribute("number");
	  var o=document.getElementById('cmd'+number);
	  if (o) {
	    if (code=="OK") {
	      o.className="G";
	    } else {
	      o.className="E";
	    }
	    o=document.getElementById('sp'+number);
	    if (o) o.innerHTML=code;
	    statuss = req.responseXML.getElementsByTagName("msg");
	    status=statuss[0];

	    o=document.getElementById('err'+number);
	    if (o) o.innerHTML=status.firstChild.nodeValue;
	    if (code=="OK") sendCmd(parseInt(number)+1);
	  }
	  
        } else {
            alert("There was a problem retrieving the XML data:\n" +
                req.statusText);
        }
    }
}
</script>
</head>
<body style="background-image:url('../CORE/Images/bg.gif')">


<div style="width:80%;border: groove 4px red">

<table width="100%"><tr><td><H1>Applications state</H1></td><td align="right"><a  href="#"><button onmouseover="this.className='over'" onmouseout="this.className=''"><img width="50px" style="border:none;" src="../CORE/Images/freeeye.png"></button></a></td></tr></table>

<table cellspacing="0" align="center" class="app"><tr><th>Application</th><th>DB version</th><th>file version</th><th>State</th><th>Machine</th></tr>
<?php

include("WHAT/Lib.Common.php");
include("WHAT/Lib.WCheck.php");

session_start();
global $_COOKIE;
$sid=$_COOKIE['adminsession'];
print "sid:$sid";
if ($sid) session_id($sid);
else $sid=session_id();
$uri=$_SERVER["REQUEST_URI"];
$buri=substr($uri,0,strrpos($uri,"/")+1);
setcookie("adminsession",$sid , time()+3600, "$buri");

$err=checkPGConnection();
if ($err=="") {
  $err=getCheckApp($pubdir,$applications);
  if ($err) $msg=_("create databases ?");
 }

if ($err == "") {
  foreach ($applications as $k=>$v) {
    print sprintf("<tr class=\"%s\"><td>%s</td><td>%s&nbsp;</td><td>%s</td><td>%s&nbsp;</td><td>%s&nbsp;</td></tr>",
		  $v["chk"],
		  $v["name"],
		  $v["vdb"],
		  $v["vfile"],
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

<div style="width:80%;border: groove 4px red">

<table width="100%"><tr><td><H1>Applications state</H1></td><td align="right"><a  href="#"><button onmouseover="this.className='over'" onmouseout="this.className=''"><img width="50px" style="border:none;" src="../CORE/Images/freeeye.png"></button></a></td></tr></table>

<table cellspacing="0" align="center" class="app"><tr><th>Commande</th><th>Status</th><th>Message</th></tr>
<?php
$err=getCheckActions($pubdir,$applications,$actions);
$_SESSION["actions"] = $actions;
$_SESSION["coucou"] = "coucou";
foreach ($actions as $k=>$v) {
  print sprintf("<tr id=\"cmd%s\" class=\"I\"><td>%s</td><td>&nbsp;<span id=\"sp%s\" onclick=\"sendCmd(%s)\">Go</span></td><td><div  class=\"msg\" id=\"err%s\"></div></td></tr>",
		  $k,$v,$k,$k,$k);
}

?>


</body></html>
