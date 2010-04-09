<?php
/**
 * Resize image (icons) by imagemagick converter
 *
 * @author Anakeen 2007
 * @version $Id: resizeimg.php,v 1.10 2007/11/30 17:14:09 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage CORE
 */
/**
 */
include_once("WHAT/Lib.Prefix.php");
include_once("WHAT/Lib.Http.php");
include_once("WHAT/Lib.Common.php");

function rezizelocalimage($img,$size,$basedest) {
    $source=$img;

    $dest=DEFAULT_PUBDIR.$basedest;

    $cmd=sprintf("convert  -thumbnail %d $source $dest",$size);
    //$cmd=sprintf("convert  -scale %dx%d $source $dest",$size,$size);
    system($cmd);
    if (file_exists($dest)) return $basedest;
    return false;
}

function copylocalimage($img,$size,$basedest) {
    $source=$img;

    $dest=DEFAULT_PUBDIR.$basedest;

    $cmd=sprintf("/bin/cp $source $dest",$size);
    system($cmd);
    if (file_exists($dest)) return $basedest;
    return false;
}
function getVaultPauth($vid) {

    $dbaccess=getDbAccess();
    $rcore = pg_connect($dbaccess);
    if ($rcore) {
        $result = pg_query($rcore, "SELECT val from paramv where name='FREEDOM_DB' and type='G'");
        if ($result) {
            $row = pg_fetch_row($result);
            $dbfree=current($row);

            if ($dbfree) {
                $rfree = pg_connect($dbfree);
                if ($rfree) {
                    $result = pg_query("select id_dir,name,public_access from vaultdiskstorage where id_file=$vid");
                    if ($result) {
                        $row = pg_fetch_row($result);
                        if ($row) {
                            $iddir=$row[0];
                            $name=$row[1];
			    $free=$row[2];

			    if (! $free) return false;

                            if (preg_match('/\.([^\.]*)$/',$name,$reg)) {
                                $ext=$reg[1];
                            }

                            $result = pg_query("SELECT l_path,id_fs from vaultdiskdirstorage where id_dir = $iddir");
                            $row = pg_fetch_row($result);
                            $lpath=$row[0];
                            $idfs=$row[1];
                            $result = pg_query("SELECT r_path from vaultdiskfsstorage where id_fs = $idfs");
                            $row = pg_fetch_row($result);
                            $rpath=$row[0];


                            $localimg="$rpath/$lpath/$vid.$ext";
                            if (file_exists($localimg)) return $localimg;

                        }
                    }
                }
            }
        }
    }
    return false;
}

function getVaultCacheImage($vid,$size) {
    $basedest="/img-cache/$size-vid$vid.png";
    return $basedest;
}

$size=$_GET["size"];
$img=$_GET["img"];
if (!$img) {
    $vid=$_GET["vid"];
    if ($vid>0) $img="vaultid=$vid";
}

$dir=dirname($_SERVER["SCRIPT_NAME"]);
$ldir=DEFAULT_PUBDIR;
if (preg_match("/vaultid=([0-9]+)/",$img,$vids)) {
    // vault file
    $vid=$vids[1];
    $basedest=getVaultCacheImage($vid,$size);
    $dest=DEFAULT_PUBDIR.$basedest;
    if (file_exists($dest)) {
        $location=$ldir."/".$basedest;
    } else {
        $localimage=getVaultPauth(intval($vid));
        if ($localimage) {
            $tsize=getimagesize($localimage);

            $width=intval($tsize[0]);
            if ($width > $size) {
                $newimg=rezizelocalimage($localimage,$size,$basedest);
            } else {
                $newimg=copylocalimage($localimage,$size,$basedest);
            }
            if ($newimg) $location="$ldir/$newimg";
        }
    }
} else {
    // local file
    $turl=(parse_url($img));
    $path=$turl["path"];

    if (strstr($path, $dir ) == $path) {
        $localimage=substr($path,strlen($dir));
    } else {
        $localimage=$img;
    }
    $basedest="/img-cache/$size-".basename($localimage).".png";
    $dest=DEFAULT_PUBDIR.$basedest;

    if (file_exists($dest)) $location= "$ldir/$basedest";
    else {
        $newimg=rezizelocalimage(DEFAULT_PUBDIR."/$localimage",$size,$basedest);
        if ($newimg) $location="$ldir/$newimg";
    }

}
//print("<hr>Location: $location");
if ($location) $location="/".ltrim($location,"/");
else $location=$img;



Http_DownloadFile($location,basename($location),"image/png");
//Header("Location: $location");

?>