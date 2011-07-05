<?php
/**
 * Display a message to advert that WHAT being to be upgraded
 * replace index.php when wstop is invocated (RPM update)
 *
 * @author Anakeen 2002
 * @version $Id: stop.php,v 1.4 2007/03/06 18:57:03 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage CORE
 */
header("HTTP/1.0 503 Service Unavailable");
?>
<HTML>
<HEAD>
<TITLE>Update</TITLE>
</HEAD>

<BODY  style="background-image:url('CORE/Images/bg.gif')">
<div align="center">
<div style="width:80%;border: groove 4px red">
<IMG align="right"  src="CORE/Images/freeeye.png">
<H2 align="left">Update is in progress.</H2>
<H2 align="left">Wait few minutes, please.</H2></div></div>
</BODY>
</HTML>