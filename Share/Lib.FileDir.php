<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Set of usefull system file functions
 *
 * @author Anakeen
 * @version $Id: Lib.FileDir.php,v 1.3 2004/07/29 09:28:34 yannick Exp $
 * @package FDL
 * @subpackage CORE
 */
/**
 */
function create_dir($dir, $access, $owner = "", $group = "")
{
    clearstatcache();
    if (!file_exists($dir)) {
        if (!file_exists(dirname($dir))) create_dir(dirname($dir) , "$access", $owner, $group);
        $cmd = "mkdir(\"" . $dir . "\"," . $access . ");";
        eval($cmd);
        if ($owner != "") chown($dir, $owner);
        if ($group != "") chgrp($dir, $group);
    }
}

function create_file($file, $access, $owner = "", $group = "")
{
    clearstatcache();
    if (!file_exists($file)) {
        if (!file_exists(dirname($file))) create_dir(dirname($file) , "$access", $owner, $group);
        touch($file);
        if ($owner != "") chown($file, $owner);
        if ($group != "") chgrp($file, $group);
    }
}

function install_file($from, $to, $access, $owner = "", $group = "")
{
    clearstatcache();
    if (file_exists($from)) {
        create_dir(dirname($to) , "$access", $owner, $group);
        copy($from, $to);
        $cmd = "chmod(\"" . $to . "\"," . $access . ");";
        eval($cmd);
        chmod($to, $access);
        if ($owner != "") chown($dir, $owner);
        if ($group != "") chgrp($dir, $group);
    }
}

function remove_dir($dir)
{
    $cmd = sprintf("rm -rf %s", escapeshellarg($dir));
    system($cmd);
}
?>
