<?php
/**
 * Set of usefull system file functions
 *
 * @author Anakeen 2000
 * @version $Id: Lib.FileDir.php,v 1.2 2003/08/18 15:46:42 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
/**
 */
function create_dir($dir,$access,$owner="",$group="") {
  clearstatcache();
  if (!file_exists($dir)) {
    if (!file_exists(dirname($dir))) create_dir(dirname($dir),$access,$owner,$group);
    mkdir($dir,$access);
    if ($owner != "") chown($dir,$owner);
    if ($group != "") chgrp($dir,$group);
  }
}

function create_file($file,$access,$owner="",$group="") {
  clearstatcache();
  if (!file_exists($file)) {
    if (!file_exists(dirname($file))) create_dir(dirname($file),$access,$owner,$group);
    touch($file);
    if ($owner != "") chown($file,$owner);
    if ($group != "") chgrp($file,$group);
  }
}

function install_file($from, $to, $access,$owner="",$group="") {
  clearstatcache();
  if (file_exists($from)) {
    create_dir(dirname($to),$access,$owner,$group);
    copy($from, $to);
    chmod($to, $access);
    if ($owner != "") chown($dir,$owner);
    if ($group != "") chgrp($dir,$group);
  }
}

function remove_dir($dir) {
  $cmd = "rm -rf $dir";
  system($cmd);
}

?>
