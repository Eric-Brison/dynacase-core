<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Resize image (icons) by imagemagick converter
 *
 * @author Anakeen
 * @version $Id: resizeimg.php,v 1.10 2007/11/30 17:14:09 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
/**
 */
include_once ("WHAT/Lib.Prefix.php");
include_once ("WHAT/Lib.Http.php");
include_once ("WHAT/Lib.Common.php");

define("MAX_RESIZE_IMG_SIZE", 512); // maximum size to prevent attack
function rezizelocalimage($img, $size, $basedest)
{
    $source = $img;
    
    $dest = DEFAULT_PUBDIR . $basedest;
    if ($size[0] == 'H') {
        $size = substr($size, 1);
        $h = "x";
    } else {
        $h = '';
    }
    
    $size = min(MAX_RESIZE_IMG_SIZE, $size);
    
    $cmd = sprintf("convert  -thumbnail $h%d %s %s", $size, escapeshellarg($source) , escapeshellarg($dest));
    system($cmd);
    if (file_exists($dest)) return $basedest;
    return false;
}

function getVaultPauth($vid)
{
    
    $dbaccess = getDbAccess();
    $rcore = pg_connect($dbaccess);
    if ($rcore) {
        $result = pg_query($rcore, "SELECT val from paramv where name='FREEDOM_DB' and type='G'");
        if ($result) {
            $row = pg_fetch_row($result);
            $dbfree = current($row);
            
            if ($dbfree) {
                $rfree = pg_connect($dbfree);
                if ($rfree) {
                    $result = pg_query("select id_dir,name,public_access from vaultdiskstorage where id_file=$vid");
                    if ($result) {
                        $row = pg_fetch_row($result);
                        if ($row) {
                            $iddir = $row[0];
                            $name = $row[1];
                            $free = $row[2];
                            
                            if (!$free) {
                                return false;
                            }
                            $ext = '';
                            if (preg_match('/\.([^\.]*)$/', $name, $reg)) {
                                $ext = $reg[1];
                            }
                            
                            $result = pg_query("SELECT l_path,id_fs from vaultdiskdirstorage where id_dir = $iddir");
                            $row = pg_fetch_row($result);
                            $lpath = $row[0];
                            $idfs = $row[1];
                            $result = pg_query("SELECT r_path from vaultdiskfsstorage where id_fs = $idfs");
                            $row = pg_fetch_row($result);
                            $rpath = $row[0];
                            
                            $localimg = "$rpath/$lpath/$vid.$ext";
                            if (file_exists($localimg)) return $localimg;
                        }
                    }
                }
            }
        }
    }
    return false;
}

function getVaultCacheImage($vid, $size)
{
    $basedest = sprintf("/var/cache/image/%s-vid%d.png", $size, $vid);
    return $basedest;
}

$size = isset($_GET["size"]) ? $_GET["size"] : null;
if (!$size) {
    if (isset($_GET["width"])) {
        $size = $_GET["width"];
    }
}
if (!$size) {
    $heigth = isset($_GET["height"]) ? $_GET["height"] : null;
    if ($heigth) {
        $size = "H" . $heigth;
    }
}

if (!preg_match('/^H?[0-9]+(px)?$/', $size)) {
    header('HTTP/1.0 400 Bad request');
    print "Wrong image size";
    exit;
} else {
    if ($size[0] == 'H') {
        $isize = intval(substr($size, 1));
        $isize = min(MAX_RESIZE_IMG_SIZE, $isize);
        $size = "H" . $isize;
    } else {
        $isize = intval($size);
        $isize = min(MAX_RESIZE_IMG_SIZE, $isize);
        $size = $isize;
    }
}
$img = isset($_GET["img"]) ? $_GET["img"] : null;
if (!$img) {
    $vid = isset($_GET["vid"]) ? $_GET["vid"] : null;
    if ($vid > 0) $img = "vaultid=$vid";
}
$location = '';
$dir = dirname($_SERVER["SCRIPT_NAME"]);
$ldir = DEFAULT_PUBDIR;
if (preg_match("/vaultid=([0-9]+)/", $img, $vids)) {
    // vault file
    $vid = $vids[1];
    $basedest = getVaultCacheImage($vid, $size);
    $dest = DEFAULT_PUBDIR . $basedest;
    if (file_exists($dest)) {
        $location = $ldir . "/" . $basedest;
    } else {
        $localimage = getVaultPauth(intval($vid));
        if ($localimage) {
            $newimg = rezizelocalimage($localimage, $size, $basedest);
            if ($newimg) $location = "$ldir/$newimg";
        } else {
            header('HTTP/1.0 404 Not found');
            exit;
        }
    }
} else {
    // local file
    $turl = (parse_url($img));
    $path = $turl["path"];
    
    $realfile = realpath($path);
    if (!$realfile) {
        header('HTTP/1.0 404 Not found');
        exit;
    }
    $itselfName = $_SERVER["SCRIPT_FILENAME"];
    $itselfdir = dirname($itselfName);
    //printf("\n[%s] [%s]\n", $itselfdir, substr(dirname($realfile), 0,strlen($itselfdir)));
    if (substr(dirname($realfile) , 0, strlen($itselfdir)) != $itselfdir) {
        if (!is_link($path)) {
            header('HTTP/1.0 403 Forbidden');
            exit;
        }
    }
    
    if (strtok(substr($realfile, strlen($itselfdir)) , '/') == "var") {
        header('HTTP/1.0 403 Forbidden');
        exit;
    }
    
    $cmd = sprintf('file -ib %s', escapeshellarg($realfile));
    
    $tsize = getimagesize($realfile);
    if (!$tsize) {
        header('HTTP/1.0 403 Forbidden');
        exit;
    }
    
    if (preg_match('%[0-9]+/[0-9]+\.[a-z]+$%', $realfile)) {
        header('HTTP/1.0 403 Forbidden');
        exit;
    }
    
    if (strstr($path, $dir) == $path) {
        $localimage = substr($path, strlen($dir));
    } else {
        $localimage = $img;
    }
    
    $basedest = sprintf("/var/cache/image/%s-%s.png", $size, basename(str_replace("/", "_", $localimage)));
    $dest = DEFAULT_PUBDIR . $basedest;
    
    if (file_exists($dest) && filemtime($dest) >= filemtime(DEFAULT_PUBDIR . "/$localimage")) {
        $location = "$ldir/$basedest";
    } else {
        $newimg = rezizelocalimage(DEFAULT_PUBDIR . "/$localimage", $size, $basedest);
        if ($newimg) $location = "$ldir/$newimg";
    }
}
//print("<hr>Location: $location");
if ($location) $location = "/" . ltrim($location, "/");
else $location = $img;
Http_DownloadFile($location, basename($location) , "image/png", true, true);
// if here file has not be sent
header('HTTP/1.0 404 Not found');
