<?php
/*
 * pack js or css files
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 */

function setHeaderCache($type)
{
    $mime = "text/plain";
    if ($type == 'css') {
        $mime = 'text/css';
    } elseif ($type == 'js') {
        $mime = 'text/javascript';
    }
    ini_set('session.cache_limiter', 'none');
    $duration = 24 * 3600;
    header("Cache-Control: private, max-age=$duration"); // use cache client (one day) for speed optimsation
    header("Expires: " . gmdate("D, d M Y H:i:s T\n", time() + $duration)); // for mozilla
    header("Pragma: none"); // HTTP 1.0
    header("Content-Disposition: inline;");
    header("Content-type: $mime");
}

function exitError($text)
{
    header('HTTP/1.0 503 ' . $text);
    exit;
}

$cookName = 'freedom_param';

if (!isset($_COOKIE[$cookName])) exitError('Not connected');
$sessid = $_COOKIE[$cookName];

if (!isset($_GET["pack"])) exitError('No pack set');
if (!isset($_GET["type"])) exitError('No type set');
if ($_GET["type"] != "js" && $_GET["type"] != "css") exitError('No compatible type');
$packName = $_GET["pack"];
$type = $_GET["type"];
session_id($sessid);
session_start();

if (!isset($_SESSION['RSPACK_' . $packName])) exitError('No pack reference found');
$packDef = $_SESSION['RSPACK_' . $packName];

setHeaderCache($type);
foreach ($packDef as $fileRef) {
    $path = $fileRef["ref"];
    printf("\n/*include : %s */\n", $path);
    if (!file_exists($path)) exitError(sprintf('File %s not found', $path));
    print file_get_contents($path);
}
