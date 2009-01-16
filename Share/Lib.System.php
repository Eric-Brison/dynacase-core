<?php

/**
 * LibSystem class
 *
 * This class provides methods for querying system informations
 *
 * @author Anakeen 2009
 * @version $Id: Lib.System.php,v 1.4 2009/01/16 13:33:01 jerome Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage
 */
 /**
 */

class LibSystem {

  function getCommandPath($cmdname) {
    $path_env = getenv("PATH");
    if( $path_env == false ) {
      return false;
    }
    foreach (split(":", $path_env) as $path) {
      if( file_exists("$path/$cmdname") ) {
	return "$path/$cmdname";
      }
    }
    return false;
  }
  
  function getHostName() {
    return php_uname('n');
  }
  
  function getHostIPAddress($hostname="") {
    if( $hostname == false ) {
      $hostname = LibSystem::getHostName();
    }
    $ip = gethostbyname($hostname);
    if( $ip == $hostname ) {
      return false;
    }
    return $ip;
  }

  function getServerName() {
    return getenv("SERVER_NAME");
  }

  function getServerAddr() {
    return getenv("SERVER_ADDR");
  }

  function runningInHttpd() {
    return LibSystem::getServerAddr();
  }

  function ssystem($args, $opt=null) {
    $pid = pcntl_fork();
    if( $pid == -1 ) {
      return -1;
    }
    if( $pid != 0 ) {
      $ret = pcntl_waitpid($pid, $status);
      if( $ret == -1 ) {
	return -1;
      }
      return pcntl_wexitstatus($status);
    }
    if( $opt && array_key_exists('closestdin') && $opt['closestdin'] ) {
      fclose(STDIN);
    }
    if( $opt && array_key_exists('closestdout') && $opt['closestdout'] ) {
      fclose(STDOUT);
    }
    if( $opt && array_ley_exists('closestderr') && $opt['closestderr'] ) {
      fclose(STDERR);
    }
    $cmd = array_shift($args);
    pcntl_exec($cmd, $args);
  }

}

?>