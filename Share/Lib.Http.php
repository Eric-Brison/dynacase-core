<?
// ---------------------------------------------------------------
// $Id: Lib.Http.php,v 1.1 2002/01/08 12:41:34 eric Exp $
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
// $Log: Lib.Http.php,v $
// Revision 1.1  2002/01/08 12:41:34  eric
// first
//
// Revision 1.11  2001/11/14 15:11:22  eric
// ajout fonction dowload file
//
// Revision 1.10  2001/06/14 14:59:17  eric
// multi frame : ajout param optionnel à redirect
//
// Revision 1.9  2000/11/08 11:04:56  marc
// Stop php execution after Redirect
//
// Revision 1.8  2000/10/27 15:55:58  yannick
// Valeur du cookie par défaut
//
// Revision 1.7  2000/10/27 07:49:43  marc
// Mise au point MAILADMIN
//
// Revision 1.6  2000/10/26 07:54:50  yannick
// Gestion du domaine sur les utilisateur
//
// Revision 1.5  2000/10/24 17:44:55  yannick
// Ajout du download
//
// Revision 1.4  2000/10/23 14:13:45  yannick
// Contrôle des accès
//
// Revision 1.3  2000/10/23 14:23:04  marc
// Default value
//
// Revision 1.2  2000/10/11 12:18:41  yannick
// Gestion des sessions
//
// Revision 1.1  2000/10/09 10:41:35  yannick
// Ajout de LibHttp
//
//
// ---------------------------------------------------------------

$LIB_HTTP_PHP = '$Id: Lib.Http.php,v 1.1 2002/01/08 12:41:34 eric Exp $';


function Redirect($action,$appname,$actionname,$otherurl="")
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

function GetHttpVars($name, $def="") {

  global $HTTP_GET_VARS,$HTTP_POST_VARS;
  if (isset($HTTP_GET_VARS[$name])) return $HTTP_GET_VARS[$name];
  if (isset($HTTP_POST_VARS[$name])) return $HTTP_POST_VARS[$name];
  return($def);
}

function GetHttpCookie($name, $def="") {

  global $HTTP_COOKIE_VARS;
  if (isset($HTTP_COOKIE_VARS[$name])) return $HTTP_COOKIE_VARS[$name];
  return($def);
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
   header("Content-Disposition: form-data;filename=$name");
   header("CONTENT-TYPE: ".$mime_type);
   echo $src;
}

function Http_DownloadFile($filename,$name,$mime_type='') {

   
   header("Content-Disposition: form-data;filename=$name");
   header("CONTENT-TYPE: ".$mime_type);
   $fd = fopen($filename, "r");
   while (! feof($fd)) {
     $contents = fread($fd, 4096);
     echo $contents;
   }
   fclose($fd);



   
}
