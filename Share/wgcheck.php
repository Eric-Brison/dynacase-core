<?php
/**
 * Util function for update and initialize application
 *
 * @author Anakeen 2005
 * @version $Id: wgcheck.php,v 1.3 2005/10/27 14:24:39 eric Exp $
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
TABLE.app TR.U {background-color:yellow;}
TABLE.app TR.E {background-color:red;}
BUTTON {
 border:outset;
}
BUTTON.over {
 border:inset;
  background-color:lightgreen;
}
</style>
</head>
<body style="background-image:url('CORE/Images/bg.gif')">


<div style="width:80%;border: groove 4px red">

<table width="100%"><tr><td><H1>Applications state</H1></td><td align="right"><a  href="#"><button onmouseover="this.className='over'" onmouseout="this.className=''"><img width="50px" style="border:none;" src="CORE/Images/freeeye.png"></button></a></td></tr></table>

<table cellspacing="0" align="center" class="app"><tr><th>Application</th><th>DB version</th><th>file version</th><th>State</th><th>Machine</th></tr>
<?php

include("WHAT/Lib.Common.php");
include("WHAT/Lib.WCheck.php");


$err=getCheckApp($pubdir,$applications);

if ($err == "") {
  foreach ($applications as $k=>$v) {
    print sprintf("<tr class=\"%s\"><td>%s</td><td>%s</td><td>%s</td><td>%s&nbsp;</td><td>%s&nbsp;</td></tr>",
		  $v["chk"],
		  $v["name"],
		  $v["vdb"],
		  $v["vfile"],
		  $v["chk"],
		  $v["machine"]);
  } 
 }else {
  print sprintf("<tr class=\"E\"><td colspan=\"5\">%s</td></tr>",$err);
 
 }
?>
</table></div>
</body></html>