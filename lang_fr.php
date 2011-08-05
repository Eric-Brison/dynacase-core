<?php
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 */
$lang["fr_FR"] = array(
	"label"  => "Français",
	"flag"   => "",
	"locale" => "fr",
	"dateFormat" => "%d/%m/%Y",
	"dateTimeFormat" => "%d/%m/%Y %H:%M",
	"timeFormat" => "%H:%M:%S",
);


/*
 ** Include local/override config
 ** -----------------------------
 */
$local_lang = dirname(__FILE__).DIRECTORY_SEPARATOR.'local-lang.php';
if( file_exists($local_lang) ) {
  include($local_lang);
}
?>