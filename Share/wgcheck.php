<?php
/**
 * Util function for update and initialize application
 *
 * @author Anakeen 2005
 * @version $Id: wgcheck.php,v 1.2 2005/10/18 15:36:24 eric Exp $
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
    TABLE TD { border-top:solid blue 1px;
  }
    TABLE {
    border: 1px solid;width:80%;
    }
TR {background-color:green;}
TR.U {background-color:yellow;}
</style>
</head>
<body style="background-image:url('CORE/Images/bg.gif')">


<div style="width:80%;border: groove 4px red">
<a href="#"><img align="right" width="100px" src="CORE/Images/freeeye.png"></a>
<H1>Applications state</H1>
<table cellspacing="0"><tr><th>Application</th><th>DB version</th><th>file version</th><th>State</th><th>Machine</th></tr>
<?php

include("WHAT/Lib.Common.php");
include("WHAT/Lib.WCheck.php");


$err=getCheckApp($pubdir,$applications);

foreach ($applications as $k=>$v) {
  print sprintf("<tr class=\"%s\"><td>%s</td><td>%s</td><td>%s</td><td>%s&nbsp;</td><td>%s&nbsp;</td></tr>",
		$v["chk"],
		$v["name"],
		$v["vdb"],
		$v["vfile"],
		$v["chk"],
		$v["machine"]);
}
?>
</table></div>
</body></html>