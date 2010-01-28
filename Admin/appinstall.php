<?php 
/**
 * View interface to access admin pages
 *
 * @author Anakeen 2008
 * @version $Id: appinstall.php,v 1.2 2008/05/12 08:22:21 marc Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage CORE
 */
?>
<html>
<head><title>freedom application installer</title>

<style>
* {
  font-family:Tahoma,Arial,Verdana,Helvetica;
  font-size:9pt;
}
body {
  background-image:url('../Images/bg.gif');
  padding :20px;
}

.head {
  font-size : 180%;
  background-color : #fff;
  -moz-border-radius : 8px;
  border : 1px solid #555;
  padding : 10px;
  margin : 0 0 10px 0;
}
.idir {
}
.link {
  text-align:right;
}
.link a {
  text-decoration:none;
  font-size:85%;
  color : #f00;
}

.formu {
  background-color : #e4e4e4;
  -moz-border-radius : 8px;
  border : 1px solid #555;
  padding : 10px;
  margin : 10px 0px 10px 40px;
}
.step {
  margin : 5px 0px 10px 0px;
  color : #545454;
  font-weight : bold;
  text-decoration : underline;
}

.trace {
  padding : 0 0 0 10px;
  background-color : #e9e9e9;
}
.error {
  padding : 10px;
  background-color : #e9e9e9;
  font-weight : bold;
  border : 1px solid #f00;
}
.logE {
  background-color : #000;
  color : #0f0;
  margin : 10px;
  padding : 10px;
  border : 1px solid #0f0;
}

</style>
 

</head>
<body >


<?php
  include_once("FDL/import_tar.php");

function extractTar($tar,$untardir) {
  

  $mime=trim(`file -ib "$tar"`);
  $mime=trim(`file -b "$tar"`);
  $mime = substr($mime,0,strpos($mime, " "));
  
  $status = 0;
  switch ($mime) {
    case "gzip":
    case "application/x-compressed-tar":
    case "application/x-gzip":
      system("cd \"$untardir\" && tar xfz \"$tar\" >/dev/null",$status);
      break;
    case "bzip2":
      system("cd \"$untardir\" &&  tar xf \"$tar\" --use-compress-program bzip2 >/dev/null",$status);
      break;
    case "Zip":
    case "application/x-zip-compressed":
    case "application/x-zip":
      system("cd \"$untardir\" && unzip \"$tar\" >/dev/null",$status);
      WNGBDirRename($untardir);
      break;
    default:
      $status= -2;
    }
  return $status;
}

function rdir($dir, &$lf, $lvl=0) {
  if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
          if ($file != "." && $file != "..") {
           $lf[] = array("dir"=>$dir, "file"=>$file, "lvl"=>$lvl);
           if (is_dir($dir."/".$file)) rdir($dir."/".$file, $lf, $lvl+1);
          }
        }
        closedir($dh);
    }
  }
  return $lf;
}

function mlog($m) {
  //$d = true;
  $d = false;
  if ($d) echo "<div class=\"logE\">[freedom-installer] ".$m."</div>";
  else error_log("[freedom-installer] ".$m);
}

function trace($m, $err=false) {
  echo "<div class=\"".($err?"error":"trace")."\">[freedom-installer] ".$m."</div>";
  error_log("[freedom-installer] ".$m);
}

  $pubdir=dirname(getcwd());

 echo '<div class="head">freedom application installer
<div class="idir">Installation dir : '.$pubdir.'</div>
<div class="link"><a href="winit.php">admin page</a></div>
</div>';

 $eroot = "/tmp/fi-root";
 $edir = $eroot."/edir-app/";
 $efile = $eroot."/fi.tar.gz";
 $s = $_POST["s"];
 $istep = $_POST["istep"];
 //$pubdir = $_POST["appdir"];
 $appf = $_FILES["appzip"]["tmp_name"];
 $appl = "";

// first step
 if ($istep!=1 && $istep!=2) {
   if (file_exists($appf)) {
     system("rm -rf $eroot");
     @mkdir($edir, 0777, true);
     mlog("mv $appf $efile");
     system("mv $appf $efile");
     $status = extractTar($efile, $edir);
     mlog("extractTar($efile, $edir) status=$status");
     if ($status<0) {
       trace("Extraction error ($status), are you sure '".$_FILES["appzip"]["name"]."' is an archive ?",true);
       system("rm -rf $eroot");
     } else {
       if (file_exists($edir.$pubdir)) {
         $lf = array();
         rdir($edir.$pubdir, $lf);
         $appl = array();
         foreach ($lf as $k=>$v) {
             $b = basename($v["file"], ".app");
           if ($b!=$v["file"]) {
               if (is_dir($pubdir."/".$b)) $stat="U";
               else $stat="I";
               include_once($edir.$pubdir."/".$b."/".$b.".app");
               $appl[] = array("app" => $b, 
                               "name" => $app_desc["description"],
                               "stat" => $stat);
           }
         }
         if (count($appl)<1) {
           trace("No application found in archive.",true);
         } else {
           $istep=1;
         }
       } else {
         trace("Archive doesn't contain install dir [$pubdir]",true);
       }
     }
     mlog("applist = $appl");
   }
 } else if ($istep==1) {
   if (file_exists($efile)) {
     if ($pubdir=="") {
       trace("please set install dir",true);
     } else if (!is_dir($pubdir) || !is_writeable($pubdir)) {
       trace("install dir $pubdir not found ou not writeable",true);
     } else {
       trace("Installation dir [$pubdir]");
       trace("Installation step starts...");
       $status = extractTar($efile, "/");
       trace("Installation step done, status = $status.");
       if (is_dir($eroot)) {
         trace("Cleaning installation files...");
         system("rm -rf $eroot");
         mlog("Remove $edir");
       }
       $istep++;
     }
   } else {
       $istep=0;
   }
 } else if ($istep==2) {

 } else {
   echo "Unknown step<br>";
 } 
?>


<form enctype="multipart/form-data" id="uploadapp" name="uploadapp" method="POST" action="appinstall.php">
<input type="hidden" name="istep" value="<?php echo $istep; ?>">
<div class="formu">
<?php if ($istep==2) { ?>
<div class="step">Step [3] : Initialise application </div>
<div class="query">Send freedom init interface [<a href="wgcheck.php">click here</a>]!</div>
<?php } else if ($istep==1) { ?>
<div class="step">Step [2] : Install application</div>
<?php
echo "<ul>";
foreach ($appl as $k=>$v) echo "<li> [".($v["stat"]=="I"?"Install":"Update")."] ".$v["app"]." : ".$v["name"];
echo "</ul>";
?>
<div class="query">
<?php } else { ?>
<div class="step">Step [1] : send application archive file </div>
<div class="query">application archive file (max size <?php echo ini_get("upload_max_filesize") ?>) : <input type="file" name="appzip">
<?php } ?>
</div>
<?php if ($istep!=2) { ?> 
<input type="submit" id="submitButton" value="<?php echo ($istep==1?"Installer":"Envoyer"); ?>" /></div>
<?php } ?>
</form>

</body>
</html>

