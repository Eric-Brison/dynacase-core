<?php
/**
 * Export Vault Files
 *
 * @author Anakeen 2000 
 * @version $Id: exportfile.php,v 1.21 2008/05/20 15:26:48 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocAttr.php");
include_once("FDL/Lib.Vault.php");
include_once("VAULT/Class.VaultFile.php");

define("RESIZEDIR",DEFAULT_PUBDIR."/.img-resize/");
// --------------------------------------------------------------------
function exportfile(&$action) {
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("docid", GetHttpVars("id",0)); // same docid id
  $attrid = GetHttpVars("attrid",0);
  $vaultid = GetHttpVars("vaultid",0); // only for public file
  $index = GetHttpVars("index");
  //  $imgheight = GetHttpVars("height");
  $imgwidth = GetHttpVars("width");
  $inline = (GetHttpVars("inline")=="yes");
  $cache = (GetHttpVars("cache","yes")=="yes");
  $latest = GetHttpVars("latest");
  $state = GetHttpVars("state"); // search doc in this state
  $type = GetHttpVars("type"); // [pdf|png] 
  $pngwidth = GetHttpVars("width"); // [pdf|png] 
  $pngpage = GetHttpVars("page"); // [pdf|png] 

  $isControled=false;

  if ($vaultid == 0) {

    $doc= new_Doc($dbaccess,$docid);
    if ($state != "") {
      $docid=$doc->getRevisionState($state,true);
      if ($docid==0) {
	$action->exitError(sprintf(_("Document %s in %s state not found"),
				   $doc->title,_($state)));
      } 
      $doc= new_Doc($dbaccess,$docid);
    } else {
      if (($latest == "Y") && ($doc->locked == -1)) {
	// get latest revision
	$docid=$doc->latestId();
	$doc= new_Doc($dbaccess,$docid);
      } 
    }

    // ADD CONTROL ACCESS HERE
    $err = $doc->control("view");
    if ($err != "") $action->exiterror($err);
    $isControled=true;;
    if ($doc->doctype=="C") $ovalue=$doc->getDefValue($attrid);
    else $ovalue = $doc->getValue($attrid);
    if (($index !== "") && ($index >= 0)) {
      $tvalue = explode("\n",$ovalue);
      $ovalue= $tvalue[$index];
    }
    $oa=$doc->getAttribute($attrid);
    if ($oa->getOption("preventfilechange")=="yes") {
      if (preg_match(PREGEXPFILE, $ovalue, $reg)) {
	$vaultid= $reg[2];
	$mimetype=$reg[1];
	$info=vault_properties($vaultid);
	$othername=vault_uniqname($vaultid);
      }
    }

    if ($ovalue == "") {
      print(sprintf(_("no file referenced for %s document"),$doc->title));
      exit;
    }
    if ($ovalue == "") $action->exiterror(sprintf(_("no file referenced for %s document"),$doc->title));    
    preg_match(PREGEXPFILE, $ovalue, $reg);
    $vaultid= $reg[2];
    $mimetype=$reg[1];
  } else {
    $mimetype = "";
  }

  DownloadVault($action, $vaultid, $isControled, $mimetype,$imgwidth,$inline,$cache,$type,$pngpage,$othername);    
  exit;
    
  
    
}



/**
 * Idem like exportfile instead that download first file attribute found
 */
function exportfirstfile(&$action) {
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("docid", GetHttpVars("id",0));

  $doc= new_Doc($dbaccess,$docid);
  $attr = $doc->GetFirstFileAttributes();
  if (! $attr) $action->exiterror(_("no attribute file found"));

  setHttpVar("attrid",$attr->id);

  exportfile($action);                  
}


// --------------------------------------------------------------------
function DownloadVault(&$action, $vaultid, $isControled, $mimetype="",$width="",$inline=false,$cache=true,$type="",$pngpage=0,$othername='') {
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $vf = newFreeVaultFile($dbaccess);
  if ($type=="pdf") {
      $teng_name='pdf';
      $err=$vf->Show($vaultid, $info,$teng_name);
      if ($err != "") $err=sprintf(_("PDF conversion not found"))."\n$err";
  } else {
       $err=$vf->Show($vaultid, $info);
          //print_r2(substr($info->mime_s,0,5));
          //print_r2("width=".$width);
          if (substr($info->mime_s,0,5) == "image") $type="original";
      
      if ($type=="png") {
          $teng_name='pdf';
          $err=$vf->Show($vaultid, $info,$teng_name);
          if ($err == "") {
              $filecache=sprintf("%s/.img-resize/vid-%s-%s.png",DEFAULT_PUBDIR,$info->id_file,$pngpage);
              if (file_exists($filecache)) {
                  //  print_r2($filecache);
                  Http_DownloadFile($filecache,$info->name.".png","image/png",$inline,$cache);
                  exit;
              }

              $cible=uniqid(getTmpDir()."/thumb").".png";
              if (! $width) $width=150;
              $quality=200;
              $resample=false;
              // option 1
              //$cmd=sprintf("gs -q -dSAFER -dBATCH -dNOPAUSE -sDEVICE=png16m -r%d -sOutputFile=- -dFirstPage=%d -dLastPage=%d %s | convert -  -thumbnail %s %s",   min(intval($width/8.06),$quality),$pngpage+1,$pngpage+1,$info->path,$width,$cible);
              // option 2
              $cmd=sprintf("convert -thumbnail %s  -density %d %s[%d] %s",$width,$quality,$info->path,$pngpage,$cible);
              // option 3
              //$cmd=sprintf("gs -dSAFER -dBATCH -dNOPAUSE -sDEVICE=png16m -r%d -sOutputFile=%s -dFirstPage=%d -dLastPage=%d %s",		   min(intval($width/8.06),$quality),$cible,$pngpage+1,$pngpage+1,$info->path);
              // option 4
              //$cmd=sprintf("gs -dSAFER -dBATCH -dNOPAUSE -sDEVICE=png16m -r%d -sOutputFile=%s -dFirstPage=%d -dLastPage=%d %s",		   min(intval($width/8.06),$quality),$cible,$pngpage+1,$pngpage+1,$info->path); $resample=true;

              // option 5
              //$cmd=sprintf("gs -dSAFER -dBATCH -dNOPAUSE -sDEVICE=png16m -r%d -sOutputFile=%s -dFirstPage=%d -dLastPage=%d %s", intval($width/8.06),$cible,$pngpage+1,$pngpage+1,$info->path);

              exec($cmd, $out, $ret);

              if ($ret == 1) $err=implode("\n",$out);
              if (file_exists($cible)) {

                  if($resample) {
                      $filename=$cible;
                      list($owidth, $oheight) = getimagesize($filename);
                      $newwidth = $width;
                      $newheight = $oheight * ($width/$owidth);
                      // chargement
                      $thumb = imagecreatetruecolor($newwidth, $newheight);
                      $source = imagecreatefrompng($filename);
                      // Redimensionnement
                      imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $owidth, $oheight);
                      // Affichage
                      header('Content-type: image/png');
                      imagepng($thumb);
                      exit;
                  } else {
                      Http_DownloadFile($cible,$info->name.".png","image/png",$inline,$cache);
                  }
                  unlink($cible);
                  exit;
              } else $err = sprintf(_("cannot get image transformation for %s"),$info->name)."\n$err";
          } else {
              $vf = newFreeVaultFile($dbaccess);
              $vf->Show($vaultid, $info);
              if ($info) $err=sprintf(_("conversion png not found for %s"),$info->name)."\n$err";
          }
      } else {
          $err=$vf->Show($vaultid, $info);
          //print_r2(substr($info->mime_s,0,5));
          //print_r2("width=".$width);
          if ((substr($info->mime_s,0,5) == "image") && ($width > 0)) {
              //print_r2($info);
              $dest=rezizelocalimage($info->path,$width,$width."-".$info->id_file.".png");
              if ($dest) Http_DownloadFile($dest, $info->name.".png", "image/png",$inline);
          }
      }
  }

  if ($err != "") {    
    sendimgerror($err);
    //  Http_DownloadFile("FREEDOM/Images/doc.gif", "unknow", "image/gif");
  } else  {
      if ($info->mime_s) $mimetype=$info->mime_s;
      //Header("Location: $url");
      if ($isControled || ( $info->public_access)) {
	if (($mimetype != "image/jpeg") || ($width == 0)) {
	  if ($othername) $info->name=$othername;
	  Http_DownloadFile($info->path, $info->name, $mimetype,$inline,$cache);
	} else {
	  $filename=$info->path; 
	  $name=$info->name;
	  if (!$inline) header("Content-Disposition: form-data;filename=$name");   
	  if ($inline) {
	    global $_SERVER;
	    $nav=$_SERVER['HTTP_USER_AGENT'];
	    $pos=strpos($nav,"MSIE");
	    if ($pos) {
	      // add special header for extension
	      header("Content-Disposition: form-data;filename=\"$name\"");
	    }
	  } 
	  //	  header("Cache-Control: private, max-age=3600"); // use cache client (one hour) for speed optimsation
	  // header("Expires: ".gmdate ("D, d M Y H:i:s T\n",time()+3600));  // for mozilla
	  // header("Pragma: "); // HTTP 1.0
	  header('Content-type: image/jpeg');

	  $mb=microtime();
	  // Calcul des nouvelles dimensions
	  list($owidth, $oheight) = getimagesize($filename);
	  $newwidth = $width;
	  $newheight = $oheight * ($width/$owidth);

	  // chargement
	  $thumb = imagecreatetruecolor($newwidth, $newheight);
	  $source = imagecreatefromjpeg($filename);

	  // Redimensionnement
	  imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $owidth, $oheight);

	  // Affichage
	  imagejpeg($thumb);
	  exit;
	  
	}
	if (! $info->public_access)   AddlogMsg(sprintf(_("%s has be sended"),$info->name));
      } else {
	$action->exiterror(_("file must be controlled : read permission needed"));
      }
    }

  exit;
}

/**
 * send a text in a image file
 * @param string $text text to display in the image
 */
function sendimgerror($text) {
  // Set font size
  $font_size = 4;

  if (seems_utf8($text)) $text=utf8_decode($text); // support only iso8859
  $ts=explode("\n",$text);
  $width=0;
  foreach ($ts as $k=>$string) {
    $width=max($width,strlen($string));
  }

  // Create image width dependant on width of the string
  $width  = imagefontwidth($font_size)*$width;
  // Set height to that of the font
  $height = imagefontheight($font_size)*count($ts);
  $el=imagefontheight($font_size);
  $em=imagefontwidth($font_size);
  // Create the image pallette
  $img = imagecreatetruecolor($width,$height);
  // Dark red background
  $bg = imagecolorallocate($img, 0xAA, 0x00, 0x00);
  // White font color
  imagefilledrectangle($img, 0, 0,$width ,$height , $bg);
  $color = imagecolorallocate($img, 255, 255, 255);

  foreach ($ts as $k=>$string) {
    // Length of the string
    $len = strlen($string);
    // Y-coordinate of character, X changes, Y is static
    $ypos = 0;
    // Loop through the string
    for($i=0;$i<$len;$i++){
      // Position of the character horizontally
      $xpos = $i * $em;
      $ypos = $k * $el;
      // Draw character
      imagechar($img, $font_size, $xpos, $ypos, $string, $color);
      // Remove character from string
      $string = substr($string, 1);      
    }
  }
  // Return the image
  header("Content-Type: image/png");
  imagepng($img);
  // Remove image
  imagedestroy($img);
  exit;
}

function rezizelocalimage($img,$size,$basedest) {
    $source=$img;

    $dest=RESIZEDIR.$basedest;

    if (! is_dir(RESIZEDIR)) {
        mkdir(RESIZEDIR);
    }
    if (! file_exists($dest)) {
        $cmd=sprintf("convert  -thumbnail %d $source $dest",$size);
        //print_r2($cmd);
        //$cmd=sprintf("convert  -scale %dx%d $source $dest",$size,$size);
        system($cmd);
    if (file_exists($dest)) return $dest;
    } else {
        return $dest;
    }
    return false;
}

?>