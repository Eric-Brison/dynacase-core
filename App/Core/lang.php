<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Locale name and language name localization
 *
 * @author Anakeen
 * @version $Id: lang.php,v 1.5 2009/01/16 13:33:00 jerome Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

$lang_dir = DEFAULT_PUBDIR . "/locale";
$lang = array();
$ret = load_lang($lang, $lang_dir);
if ($ret === false) {
    error_log(__FILE__ . sprintf(" Error loading lang from '%s'", $lang_dir));
    return false;
}

if (count($lang) <= 0) {
    error_log(sprintf(__FILE__ . " Language config is empty!"));
    return false;
}

foreach ($lang as $k => $v) {
    if (file_exists(DEFAULT_PUBDIR . "/locale/" . $v["locale"])) {
        if (!isset($v["flag"]) || $v["flag"] == "") $lang[$k]["flag"] = $k . ".png";
    }
}

function load_lang(&$lang, $lang_dir)
{
    $dir_fh = @opendir($lang_dir);
    if ($dir_fh === false) {
        error_log(sprintf(__FILE__ . " Could not open lang directory '%s'", $lang_dir));
        return false;
    }
    
    while ($subdir = readdir($dir_fh)) {
        $dir = $lang_dir . DIRECTORY_SEPARATOR . $subdir;
        
        if ($dir == '.' || $dir == '..' || !is_dir($dir)) {
            continue;
        }
        
        if (is_file("$dir/lang.php")) {
            $ret = load_lang_php($lang, "$dir/lang.php");
            if (!$ret) {
                error_log(sprintf(__FILE__ . " Error loading lang.php '%s/%s'", $dir, "lang.php"));
                continue;
            }
        }
    }
    
    return true;
}

function load_lang_php(&$lang, $file)
{
    $ret = include_once ($file);
    return $ret;
}
?>
