<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Display a message to advert that Dynacase Platform being to be upgraded
 *
 * @author Anakeen
 * @version $Id: stop.php,v 1.4 2007/03/06 18:57:03 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
header("HTTP/1.0 503 Service Unavailable");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<HEAD>
<TITLE>Dynacase Maintenance</TITLE>
<style>
body { font-family:Trebuchet MS, Helvetica, sans; background-color:#777; padding:0px; margin:0px; color:#444; }
#hello { padding: 60px 40px; }
.title { font-size: 400%; font-weight: bold; text-shadow: 0 1px 1px #fff;}
.content {    font-size: 200%; font-weight: bold; text-shadow: 0 1px 1px #fff; }
.message { padding-top:40px ; font-size:120%; color : #FF6600; text-shadow: 0 1px 1px #555;  }
td { vertical-align:top;padding:20px;}
</style>
</HEAD>

<body>
<div id="hello">
    <table>
    <tr><td><img src="Images/maintenance-symbol.png" /></td>
    <td>
      <div class="title">Dynacase Platform</div>
      <div class="content">The open source case management platform.</div> 
      <div class="message">
         <div>The system is currently unavailable due to maintenance works.</div> 
         <div>Please come back later.</div>
      </div>
      <div class="message">
         <div>Une op&eacute;ration de maintenance est en cours.</div> 
         <div>Merci de revenir plus tard.</div>
      </div>
    </td></tr>
    </table>
</div>
</BODY>
</HTML>