<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Class.Upload.php,v 1.2 2003/08/18 15:46:42 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

//--------------------------------#
// LICENSE
//--------------------------------#
//
///// fileupload.class /////
//Copyright (c) 1999 David Fox, Angryrobot Productions
//(http://www.angryrobot.com) All rights reserved.
//
//Redistribution and use in source and binary forms, with or without
//modification, are permitted provided that the following conditions are met:
//1. Redistributions of source code must retain the above copyright notice,
//this list of conditions and the following disclaimer.
//2. Redistributions in binary form must reproduce the above copyright notice,
//this list of conditions and the following disclaimer in the documentation
//and/or other materials provided with the distribution.
//3. Neither the name of author nor the names of its contributors may be used
//to endorse or promote products derived from this software without specific
//prior written permission.
//
//DISCLAIMER:
//THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS "AS IS" AND ANY
//EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
//WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
//DISCLAIMED.  IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
//DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
//(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
//LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
//ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
//(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
//THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
// ---------------------------------------------------------------------------
//  $Id: Class.Upload.php,v 1.2 2003/08/18 15:46:42 eric Exp $
//  $Log: Class.Upload.php,v $
//  Revision 1.2  2003/08/18 15:46:42  eric
//  phpdoc
//
//  Revision 1.1  2002/01/08 12:41:34  eric
//  first
//
//  Revision 1.2  2001/02/08 12:07:18  marianne
//  Creation de la directory 'path' si elle n'existe pas
//
//  Revision 1.1  2001/01/22 11:53:04  marianne
//  *** empty log message ***
//
// ---------------------------------------------------------------------------
//
$CLASS_UPLOAD_PHP = '$Id:';   
/*
Error codes:
  0 - "No file was uploaded"
  1 - "Maximum file size exceeded"
  2 - "Maximum image size exceeded"
  3 - "Only specified file type may be uploaded"
  4 - "File already exists" (save only)
*/

class uploader {

  var $file;
  var $errors;
  var $accepted="";
  var $new_file="";
  var $max_filesize=100000;
  var $max_image_width=1000;
  var $max_image_height=1000;

  function max_filesize($size){
    $this->max_filesize=$size;
  }

  function max_image_size($width, $height){
    $this->max_image_width=$width;
    $this->max_image_height=$height;
  }

  function upload($filename, $accept_type, $extension) {
    // get all the properties of the file
    $index=array("file", "name", "size", "type");
    for($i=0; $i < 4; $i++) {
      $file_var='$' . $filename . (($index[$i] != "file") ? "_" . $index[$i] : "");
      eval('global ' . $file_var . ';');
      eval('$this->file[$index[$i]]=' . $file_var . ';');
    }
  
    if($this->file["file"] && $this->file["file"] != "none") {
      //test max size
      if($this->max_filesize && $this->file["size"] > $this->max_filesize) {
        $this->errors[]="Taille maximale dépassée:. Le fichier ne doit pas excéder " . $this->max_filesize/1000 . "KB.";
        return False;
      }
       if(ereg("image", $this->file["type"])) {

         $image=getimagesize($this->file["file"]);
         $this->file["width"]=$image[0];
         $this->file["height"]=$image[1];
      
        // test max image size
        if(($this->max_image_width || $this->max_image_height) && 
           (($this->file["width"] > $this->max_image_width) || 
           ($this->file["height"] > $this->max_image_height))) {
          $this->errors[]="Les dimensions de l'image sont trop importantes. ".
                          "L'image ne doit pas faire plus de : " . 
                           $this->max_image_width . " x " . 
                           $this->max_image_height . " pixels";
          return False;
        }
         switch($image[2]) {
           case 1:
             $this->file["extension"]=".gif";
             break;
           case 2:
             $this->file["extension"]=".jpg";
             break;
           case 3:
             $this->file["extension"]=".png";
             break;
           default:
            $this->file["extension"]=$extension;
             break;
         }
      }
       else if(!ereg("(\.)([a-z0-9]{3,5})$",$this->file["name"])&&!$extension) {
        // add new mime types here
        switch($this->file["type"]) {
          case "text/plain":
            $this->file["extension"]=".txt";
            break;
          default:
            break;
        }      
       }
      else {
        $this->file["extension"]=$extension;
      }
    
      // check to see if the file is of type specified
      if($accept_type) {
        if(ereg($accept_type, $this->file["type"])) { $this->accepted=True; }
        else { $this->errors[]="Seuls les fichiers de type " . 
               ereg_replace("\|", " or ", $accept_type) . 
               " sont acceptés"; }
      }
      else { $this->accepted=True; }
    }
    else { $this->errors[]="Fichier introuvable..."; }
    return $this->accepted;
  }

  function save_file($path, $mode){
    global $NEW_NAME;
    
    if($this->accepted) {
      if(!file_exists($path )) { mkdir( $path, 0775); }
      // very strict naming of file.. only lowercase letters, 
      // numbers and underscores
      $new_name=ereg_replace("[^a-z0-9._]", "", 
		ereg_replace(" ", "_", 
		ereg_replace("%20", "_", strtolower($this->file["name"]))));

      // check for extension and remove
      if(ereg("(\.)([a-z0-9]{3,5})$", $new_name)) {
        $pos=strrpos($new_name, ".");
        if(!isset($this->file["extension"])) { 
	  $this->file["extension"]=substr($new_name,$pos,strlen($new_name));
        }
        $new_name=substr($new_name, 0, $pos);
        
      }
      $new_name= uniqid("")."_".$new_name; 
      if (!isset($this->file["extension"])) $this->file["extension"]="";
      $this->new_file=$path . $new_name . $this->file["extension"];
      $NEW_NAME=$new_name . $this->file["extension"];
      
      switch($mode) {
        case 1: // overwrite mode
          $aok=copy($this->file["file"], $this->new_file);
          break;
        case 2: // create new with incremental extension
          while(file_exists($path . $new_name . $copy . 
                            $this->file["extension"])) 
          {
            $copy="_copy" . $n;
            $n++;
          }
          $this->new_file=$path.$new_name.$copy.$this->file["extension"];
          $aok=copy($this->file["file"], $this->new_file);
          break;
        case 3: // do nothing if exists, highest protection
          if(file_exists($this->new_file)){
            $this->errors[]="Le fichier &quot".
                             $this->new_file."&quot existe déjà";
          }
          else {
            $aok=rename($this->file["file"], $this->new_file);
          }
          break;
        default:
          break;
      }
      if(!$aok) { unset($this->new_file); }
      return $aok;
    }
  }
}
?>
