<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * @author Anakeen
 * @package FDL
 */
$lang["en_US"] = array(
    "label" => "English",
    "localeLabel" => _("English") ,
    "locale" => "en",
    "culture" => "en-US",
    "dateFormat" => "%m/%d/%Y",
    "dateTimeFormat" => "%m/%d/%Y %H:%M",
    "timeFormat" => "%H:%M:%S",
);
/*
 ** Include local/override config
 ** -----------------------------
*/
$local_lang = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'local-lang.php';
if (file_exists($local_lang)) {
    include ($local_lang);
}
?>