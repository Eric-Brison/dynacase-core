<?php
/**
 * Set of usefull HTTP functions
 *
 * @author Anakeen 2000
 * @version $Id: Lib.Http.php,v 1.13 2003/08/18 15:46:42 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
/**
 */
// ---------------------------------------------------------------
// $Id: Lib.Http.php,v 1.13 2003/08/18 15:46:42 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Share/Lib.Http.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2000
// O*O  Anakeen Development Team
//  O   dev@anakeen.com
// ---------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or (at
//  your option) any later version.
//
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
// or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
// for more details.
//
// You should have received a copy of the GNU General Public License along
// with this program; if not, write to the Free Software Foundation, Inc.,
// 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
// ---------------------------------------------------------------


$LIB_HTTP_PHP = '$Id: Lib.Http.php,v 1.13 2003/08/18 15:46:42 eric Exp $';


function Redirect(&$action,$appname,$actionname,$otherurl="")
{
  if ($otherurl == "")
    $baseurl=$action->GetParam("CORE_BASEURL");
  else
    $baseurl=$otherurl;
  $location = $baseurl."app=".$appname."&action=".$actionname;
  $action->log->debug("Redirect : $location");
  Header("Location: $location");
  exit;
}

function RedirectSender(&$action)
{
  global $HTTP_SERVER_VARS;

  if ($HTTP_SERVER_VARS["HTTP_REFERER"] != "") {
    Header("Location: ".$HTTP_SERVER_VARS["HTTP_REFERER"]); // return to sender
    exit;
  }
  $referer=GetHttpVars("http_referer");
  if ($referer!="") {
    Header("Location: ".$referer); // return to sender
    exit;
  }
  
  $action->exitError(_("no referer url found"));
  exit;
}
function GetHttpVars($name, $def="") {

  global $HTTP_GET_VARS,$HTTP_POST_VARS,$ZONE_ARGS;

  // it's necessary to strip slashes because HTTP add slashes automatically
  if (isset($ZONE_ARGS[$name])) return stripslashes($ZONE_ARGS[$name]); // try zone args first : it is set be Layout::execute for a zone
  if (isset($HTTP_GET_VARS[$name])) return stripslashes($HTTP_GET_VARS[$name]);
  if (isset($HTTP_POST_VARS[$name])) {
    if (is_array($HTTP_POST_VARS[$name])) return $HTTP_POST_VARS[$name];
    else return stripslashes($HTTP_POST_VARS[$name]);
  }
  return($def);
}

function GetHttpCookie($name, $def="") {

  global $HTTP_COOKIE_VARS;
  if (isset($HTTP_COOKIE_VARS[$name])) return $HTTP_COOKIE_VARS[$name];
  return($def);
}

function SetHttpVar($name, $def) {

  global $ZONE_ARGS;
  if ($def == "") unset($ZONE_ARGS[$name]);
  else $ZONE_ARGS[$name]=$def;
}

function GetMimeType($ext) {
   $mimes = file("/etc/mime.types");
   while(list($k,$v)=each($mimes)) {
     if (substr($v,0,1)=="#") continue;
     $tab = preg_split("/\s+/",$v);
     if ((isset($tab[1])) && ($tab[1]==$ext)) return($tab[0]);
   }
   return("text/any");
}


function GetExt($mime_type) {
   $mimes = file("/etc/mime.types");
   while(list($k,$v)=each($mimes)) {
     if (substr($v,0,1)=="#") continue;
     $tab = preg_split("/\s+/",$v);
     if ((isset($tab[0])) && ($tab[0]==$mime_type)) {
       if (isset($tab[1])) {
         return($tab[1]);
       } else {
         return("");
       }
     }
   }
   return("");
}

function Http_Download($src,$ext,$name,$add_ext=TRUE) {

   $mime_type = GetMimeType($ext);
   if ($add_ext) $name=$name.".".$ext;
  header("Cache-control: private"); // for IE : don't know why !!
   header("Content-Disposition: form-data;filename=$name");
   header("Content-type: ".$mime_type);
   echo $src;
}

function Http_DownloadFile($filename,$name,$mime_type='') {

  
   header("Content-Disposition: form-data;filename=$name");   
   header("Cache-Control: private, max-age=3600"); // use cache client (one hour) for speed optimsation

   header("Expires: ".gmdate ("D, d M Y H:i:s T\n",time()+3600));  // for mozilla
   header("Pragma: "); // HTTP 1.0
   header("Content-type: ".$mime_type);
   $fd = fopen($filename, "r");
   while (! feof($fd)) {
     $contents = fread($fd, 4096);
     echo $contents;
   }
   fclose($fd);
   
}

function PrintAllHttpVars() { // just to debug

  global $HTTP_GET_VARS,$HTTP_POST_VARS,$ZONE_ARGS;
  print "<PRE>";
  if (isset($ZONE_ARGS)) print_r($ZONE_ARGS);
  if (isset($HTTP_GET_VARS)) print_r($HTTP_GET_VARS);
  if (isset($HTTP_POST_VARS)) print_r($HTTP_POST_VARS);
  print "</PRE>";
}


?>