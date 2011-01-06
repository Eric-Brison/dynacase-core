<?php 
/**
 * View interface to access admin pages
 *
 * @author Anakeen 2008
 * @version $Id: winit.php,v 1.4 2008/10/02 09:06:01 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage CORE
 */
?>
<html>
<head><title>FREEDOM Initialisation</title>

<noscript>
<H1>Javascript must be enabled in your navigator</H1>
</noscript>
<LINK REL="icon" HREF="../[DYNACASE_FAVICO]" >
<LINK REL="stylesheet" type="text/css" HREF="Layout/wg.css" >
<script language="JavaScript" src="../WHAT/Layout/logmsg.js"></script>
<script language="JavaScript">
  <!--
    // this page must be on top frame
if (window != top) top.location.href = location.href;
if (! navigator.cookieEnabled) alert ('[TEXT:Your navigator does not accept cookies.\nCookies must be enabled to use correctly this application]');
//alert (navigator.userAgent+'\n'+navigator.appVersion);
  //Detect IE5.5+
var version=0
if (navigator.appName=="Netscape") {
  version=parseFloat(navigator.appVersion);
  if (version < 5)  alert('[TEXT:You use version an to old version of Netscape (Mozilla)]:'+version+'\n'+'[TEXT:You must upgrade to version Netscape 7 or Mozilla 1.7 (Firefox)]');
}
if (navigator.appVersion.indexOf("MSIE")!=-1){
  temp=navigator.appVersion.split("MSIE");
  version=parseFloat(temp[1]);
  if (version < 5.5) alert('You use version an to old version of Internet Explorer:'+version+'\n'+'You must upgrade to version 6 or more');
}

  //-->
   </script>

</head>
<body >


<table width="100%" height="100%" >
<tr><TD align="right" width="50%"><img style="border:none;" src="Images/freeeye.png"></td>
    <td align="left">  
       <a class="abut"  href="checklist.php"><span class="bigbutton">Check List</span></a><br>
       <a class="abut"  href="phpinfo.php"><span class="bigbutton">PHP info</span></a><br>
       <a class="abut"  href="../lib/examples/perfotest.html"><span class="bigbutton">Performance test</span></a><br>
<?php if (file_exists("dbmng.php")) { ?>
       <a class="abut"  href="dbmng.php"><span class="bigbutton">Check DB backups</span></a>
<?php } ?>
</td>
</tr></table>

</body>
</html>
