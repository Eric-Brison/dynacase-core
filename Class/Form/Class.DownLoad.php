<?
// ---------------------------------------------------------------------------
// PHP PROMAN Task Class
// ---------------------------------------------------------------------------
// anakeen 2000 - Marianne Le Briquer
// ---------------------------------------------------------------------------
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
// ---------------------------------------------------------------------------
//  $Id: 
//  $Log: 
// ---------------------------------------------------------------------------
//              DownLoad
//		Generer
// ---------------------------------------------------------------------------
//
$CLASS_DOWNLOAD_PHP = '$Id:';

class DownLoad 
{
var $dbid = -1; 

var $ContentType;
var $FileType;


var $array;             // THE Array (2 dimensionnal)
var $fields;            // array of the field names to show

var $NomModele="";
var $Application="";

var $white=0;
var $black=0;
var $im=0;
var $liste_extract=array();

// ---------------------------------------------------------------------------
// NOM	       : DownLoad
//
// DESCRIPTION : Constructeur, connexion à la base.
//		
// PARAMETRES  : entree: aucun.
//
// ---------------------------------------------------------------------------
function DownLoad( )
{
$this->liste_extract["all"]["content"]="application/vnd.ms-excel";
$this->liste_extract["all"]["extension"]="";
$this->liste_extract["cvs"]["content"]="application/vnd.ms-excel";
$this->liste_extract["cvs"]["extension"]="cvs";
$this->liste_extract["rtf"]["content"]="application/rtf";
$this->liste_extract["rtf"]["extension"]="rtf";

  return TRUE;
}
// ---------------------------------------------------------------------------
function Generer( $src, $p_type_file="cvs", $p_filename="extract",$add_ext="OUI")
{
  $this->FileType=$p_type_file;
  $this->ConstruireFichier($src, $p_filename,$add_ext);
exit;
  return (TRUE);  
}
function ConstruireFichier( $src, $p_filename,$add_ext)
{
  $this->InitFile( $p_filename,$add_ext);
  $this->ShowContent( $src);
  $this->EndFile();

  return;
}  
function InitFile ( $p_filename,$add_ext) 
{
  $name=$p_filename;
  ###$this->ContentType=$this->liste_extract["{$this->FileType}"]["content"];
  if ($add_ext=="OUI") {
    $ext=$this->liste_extract["{$this->FileType}"]["extension"];
    $name.".".$ext;
  }

  header("Content-Disposition: form-data;filename=$name"); 
  header("CONTENT-TYPE: ".$this->FileType); 
  switch ($this->FileType) {
    case "html" 	: $this->InitHtml();
                          break;
  } 
}
function InitHtml ( ) 
{
  echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 3.2//EN\">\n".
       " <HTML>\n".
       "<HEAD>".
       "<LINK REL=STYLESHEET TYPE=\"text/css\" HREF=\"../Style1/Css/style.css\">".
       "<TITLE>Extraction</TITLE></HEAD>";
  echo '<BODY BGCOLOR="#FFFFFF" link="#FFFFFF" vlink="#FFFFFF" alink="#FF0000">';
}
function ShowContent ( $src) 
{
echo $src;
}

function EndFile ( ) 
{
  switch ($this->FileType) {
    case "html" 	: $this->EndHtml();
                          break;
  } 
}

function EndHtml ( ) 
{
  echo "</BODY>\n";
  echo "</HTML>\n";
}
}
?>
