<?php

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

  }

?>