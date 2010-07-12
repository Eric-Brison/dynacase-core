<?php
/**
 * Database creation
 *
 * @author Anakeen 2005
 * @version $Id: wgdbcreate.php,v 1.5 2005/11/24 09:11:18 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage CORE
 */
?>
<html><head>
<title>FREEDOM Create Databases</title>
<LINK REL="stylesheet" type="text/css" HREF="Layout/wg.css" >

</head>
<body>
<div class="frame">
<?php

include("WHAT/Lib.Common.php");
include("WHAT/Lib.WCheck.php");


$err=checkPGConnection();
if ($err=="") {
  $err=getCheckApp($pubdir,$applications);
  if ($err) {
    exec ( "$pubdir/CORE/CORE_post I" , $out ,$err );
    //$out = shell_exec("$pubdir/CORE/CORE_post I 2>".getTmpDir()."/w");
    $out2=array();
    if ($err == 0) {
    
      exec ( "$pubdir/CORE/CORE_post U" , $out2 ,$err );
      if ($err == 0) {   
      print("<table width=\"100%\"><tr><td><H1 style=\"float:left\">Databases created</H1></td><td align=\"right\">");
      print '<div  class="bouton"  onclick="document.location.href=\'wgcheck.php\'">Next</div></td></tr></table>';
      $class="logG";// Good
      }
    } else {
      print("<H1 class=\"E\">Error in creation see log</H1>");   
      $class="logE";// Error
    }
    print sprintf("<div class=\"%s\"><fieldset><legend>Log</legend>%s</fieldset></div>",$class,implode("<br/>",array_merge($out,$out2)));
  } else {
    print("<H1>Databases already created</H1>");
  }
 } else {
  print("<H1 class=\"E\">Cannot Access PostgreSQL Server</H1>");
 }


?>
</div></body></html>