<?php

class Service {

  private static $path = "./service.d/";

  function serviceInfos($path) {
    $info = null;
    if (is_file($path."/info.php")) {
      include($path."/info.php");
      $info["error"] = "";
      $info["title"] = (isset($title) ? $title : $path);
      $info["description"] = (isset($description) ? $description : $path);
      if (!isset($start)) {
         $info["error"] = "Can't load service {$path}, start is not defined.";
      } else {
        if (is_file($path."/".$start)) {
           $info["start"] = $path."/".$start;
        } else {
           $info["error"] = "Can't load service {$path}, start file {$start} not found.";
        }
      }
    } else {
      $info["error"] = "Can't load service {$path}, info.php not found.";
    }
    return $info;
  }

  function getAll() {
    $services = array();
    if (is_dir(Service::$path)) {
      if ($dh = opendir(Service::$path)) {
        while (($file = readdir($dh)) !== false) {
	  if ($file!="." && $file!=".." && filetype(Service::$path . $file)=="dir") {
	    $ret = Service::serviceInfos(Service::$path.$file);
	    if (is_array($ret)) $services[] = $ret;
	  }
        }
        closedir($dh);
      }
      usort($services, 'Service::servicesort');
    } else {
      header("HTTP/1.0 404 Service directory not found");
    }
    return $services;
  }

  function servicesort($a, $b) { return strcmp($a["title"], $b["title"]); }
}

print_r(json_encode(Service::getAll()));

?>
