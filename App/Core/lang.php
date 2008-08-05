<?php

$lang = array(
	      
	      
	      "fr_FR" => array( "label"  => _("francais"),
				"flag"   => "", // if don't set to '$code.png'
				"locale" => "fr" ), 

// 	      "en_EN" => array( "label"  => _("UK english"),
// 				"locale" => "en" ),

	      "en_US" => array( "label"  => _("US english"),
				"locale" => "en" ),


	      );

foreach ($lang as $k => $v) {
  if (file_exists(DEFAULT_PUBDIR."/locale/".$v["locale"])) {
    if (!isset($v["flag"]) || $v["flag"]=="") $lang[$k]["flag"] = $k.".png";
  }
}

?>
