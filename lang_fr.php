<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * @author Anakeen
 * @package FDL
 */
$lang["fr_FR"] = array(
    "label" => "Français",
    "localeLabel" => _("French") ,
    "flag" => "",
    "locale" => "fr",
    "culture" => "fr-FR",
    "dateFormat" => "%d/%m/%Y",
    "dateTimeFormat" => "%d/%m/%Y %H:%M",
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