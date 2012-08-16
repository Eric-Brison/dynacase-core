#!/usr/bin/env php
<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Concatenate '*-mo.js' JSON localization files
 *
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 */

array_shift($argv);

$js_out = array();
foreach ($argv as $js_file) {
    $js = file_get_contents($js_file);
    if ($js === false) {
        error_log(sprintf("Error reading content from '%s'.", $js_file));
        exit(1);
    }
    $js_dec = json_decode($js);
    if ($js_dec === null) {
        error_log(sprintf("Error decoding json from '%s': %s.", $js_file, json_last_errmsg()));
        exit(2);
    }
    foreach ($js_dec as $k => $v) {
        $js_out[$k] = $v;
    }
}

if (count($js_out) <= 0) {
    exit(0);
}

$js_enc = json_encode($js_out);
if ($js_enc == "") {
    error_log(sprintf("Error encoding json: %s.", json_last_errmsg()));
    exit(3);
}

print $js_enc;

exit(0);

function json_last_errmsg($errcode = null)
{
    if ($errcode === null) {
        $errcode = json_last_error();
    }
    switch ($errcode) {
        case JSON_ERROR_NONE:
            return 'JSON_ERROR_NONE';
            break;

        case JSON_ERROR_DEPTH:
            return 'JSON_ERROR_DEPTH';
            break;

        case JSON_ERROR_STATE_MISMATCH:
            return 'JSON_ERROR_STATE_MISMATCH';
            break;

        case JSON_ERROR_CTRL_CHAR:
            return 'JSON_ERROR_CTRL_CHAR';
            break;

        case JSON_ERROR_SYNTAX:
            return 'JSON_ERROR_SYNTAX';
            break;

        case JSON_ERROR_UTF8:
            return 'JSON_ERROR_UTF8';
            break;
    }
    return sprintf("error code '%s'", $errcode);
}
?>