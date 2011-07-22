<?php
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
$lang["en_US"] = array(
        "label"  => "English",
        "locale" => "en",
        "dateFormat" => "%m/%d/%Y",
        "dateTimeFormat" => "%m/%d/%Y %H:%M",
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