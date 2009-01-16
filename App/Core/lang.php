<?php

/**
 * Locale name and language name localization
 *
 * @author Anakeen 2009
 * @version $Id: lang.php,v 1.5 2009/01/16 13:33:00 jerome Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage
 */
 /**
 */

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
