<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Set of usefull HTTP functions
 *
 * @author Anakeen 2000
 * @version $Id: Lib.Http.php,v 1.38 2008/11/28 12:48:06 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
/**
 */

function Redirect(&$action, $appname, $actionname, $otherurl = "", $httpparamredirect = false)
{
    global $_SERVER, $_GET; // use only  with HTTP
    if ($_SERVER['HTTP_HOST'] == "") {
        print "\n--Redirect $appname $actionname--\n";
        return;
    }
    
    if ($appname == "") {
        $location = ".";
    } else {
        if ($otherurl == "") {
            if (in_array($appname, array(
                "CORE",
                "APPMNG",
                "ACCESS",
                "AUTHENT"
            ))) $baseurl = $action->GetParam("CORE_BASEURL");
            else $baseurl = '?';
        } else $baseurl = $otherurl;
        $location = $baseurl . "app=" . $appname . "&action=" . $actionname;
        //    $location .= "&session=".$action->session->id;
        
    }
    
    $action->log->debug("Redirect : $location");
    
    if ($httpparamredirect) {
        //add ZONE_ARGS
        global $ZONE_ARGS;
        if (is_array($ZONE_ARGS)) foreach ($ZONE_ARGS as $k => $v) $location.= "&$k=$v";
    }
    global $SQLDEBUG;
    if ($SQLDEBUG) {
        global $ticainit, $tic1, $trace;
        global $TSQLDELAY, $SQLDELAY;
        $trace["__url"] = $trace["url"];
        $trace["__init"] = $trace["init"];
        unset($trace["url"]);
        unset($trace["init"]);
        $deb = gettimeofday();
        $tic4 = $deb["sec"] + $deb["usec"] / 1000000;
        $trace["__app"] = sprintf("%.03fs", $tic4 - $ticainit);
        $trace["__memory"] = sprintf("%dkb", round(memory_get_usage() / 1024));
        $trace["__queries"] = sprintf("%.03fs #%d", $SQLDELAY, count($TSQLDELAY));
        $trace["__server all"] = sprintf("%.03fs", $tic4 - $tic1);
        $action->register("trace", $trace);
    }
    if (($_GET["viewext"] == "yes") || ($_POST["viewext"] == "yes")) {
        $location = str_replace("FDL_CARD", "VIEWEXTDOC", $location);
        if (preg_match("/action=GENERIC_EDIT/", $location)) {
            $location = str_replace("GENERIC_EDIT", "EDITEXTDOC", $location);
            $location = str_replace("app=GENERIC", "app=FDL", $location);
        }
    }
    Header("Location: $location");
    exit;
}

function RedirectSender(&$action)
{
    global $_SERVER;
    
    if ($_SERVER["HTTP_REFERER"] != "") {
        Header("Location: " . $_SERVER["HTTP_REFERER"]); // return to sender
        exit;
    }
    $referer = GetHttpVars("http_referer");
    if ($referer != "") {
        Header("Location: " . $referer); // return to sender
        exit;
    }
    
    $action->exitError(_("no referer url found"));
    exit;
}
function GetHttpVars($name, $def = "")
{
    
    global $_GET, $_POST, $ZONE_ARGS;
    // it's necessary to strip slashes because HTTP add slashes automatically
    if (isset($ZONE_ARGS[$name])) return ($ZONE_ARGS[$name]); // try zone args first : it is set be Layout::execute for a zone
    if (isset($_GET[$name])) return stripslashes($_GET[$name]);
    if (isset($_POST[$name])) {
        if (is_array($_POST[$name])) return array_map(create_function('$v', 'return is_scalar($v)?stripslashes($v):$v;'), $_POST[$name]);
        else return stripslashes($_POST[$name]);
    }
    return ($def);
}

function GetHttpCookie($name, $def = "")
{
    
    global $_COOKIE;
    if (isset($_COOKIE[$name])) return $_COOKIE[$name];
    return ($def);
}

function SetHttpVar($name, $def)
{
    
    global $ZONE_ARGS;
    if ($def == "") unset($ZONE_ARGS[$name]);
    else $ZONE_ARGS[$name] = $def;
}

function GetMimeType($ext)
{
    $mimes = file("/etc/mime.types");
    while (list($k, $v) = each($mimes)) {
        if (substr($v, 0, 1) == "#") continue;
        $tab = preg_split("/\s+/", $v);
        if ((isset($tab[1])) && ($tab[1] == $ext)) return ($tab[0]);
    }
    return ("text/any");
}

function GetExt($mime_type)
{
    $mimes = file("/etc/mime.types");
    while (list($k, $v) = each($mimes)) {
        if (substr($v, 0, 1) == "#") continue;
        $tab = preg_split("/\s+/", $v);
        if ((isset($tab[0])) && ($tab[0] == $mime_type)) {
            if (isset($tab[1])) {
                return ($tab[1]);
            } else {
                return ("");
            }
        }
    }
    return ("");
}

function Http_Download($src, $ext, $name, $add_ext = TRUE, $mime_type = "")
{
    if ($mime_type == '') $mime_type = GetMimeType($ext);
    if ($add_ext) $name = $name . "." . $ext;
    header("Cache-control: private"); // for IE : don't know why !!
    header('Content-Length: ' . strlen($src));
    header("Pragma: "); // HTTP 1.0
    header("Content-Disposition: form-data;filename=\"$name\"");
    header("Content-type: " . $mime_type);
    echo $src;
}

function Http_DownloadFile($filename, $name, $mime_type = '', $inline = false, $cache = true, $deleteafter = false)
{
    if (!file_exists($filename)) {
        printf(_("file not found : %s") , $filename);
        return;
    }
    //  $name=urlencode($name);
    //  $name=htmlentities( $name , ENT_QUOTES , "UTF-8" );
    if (seems_utf8($name)) $name = utf8_decode($name);
    $name = str_replace('"', '\\"', $name);
    if (!$inline) header("Content-Disposition: attachment;filename=\"$name\"");
    else header("Content-Disposition: inline;filename=\"$name\"");
    
    if ($cache) {
        $duration = 24 * 3600;
        header("Cache-Control: private, max-age=$duration"); // use cache client (one hour) for speed optimsation
        header("Expires: " . gmdate("D, d M Y H:i:s T\n", time() + $duration)); // for mozilla
        
    } else {
        header("Cache-Control: private");
    }
    header("Pragma: "); // HTTP 1.0
    if ($inline && substr($mime_type, 0, 4) == "text" && substr($mime_type, 0, 9) != "text/html" && substr($mime_type, 0, 8) != "text/xml") $mime_type = preg_replace("_text/([^;]*)_", "text/plain", $mime_type);
    
    header("Content-type: " . $mime_type);
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: " . filesize($filename));
    ob_clean();
    flush();
    if (file_exists($filename)) {
        readfile($filename);
        if ($deleteafter) unlink($filename);
        exit;
    } else {
        printf(_("file not found : %s") , $filename);
    }
}

function PrintAllHttpVars()
{ // just to debug
    global $_GET, $_POST, $ZONE_ARGS;
    print "<PRE>";
    if (isset($ZONE_ARGS)) print_r($ZONE_ARGS);
    if (isset($_GET)) print_r($_GET);
    if (isset($_POST)) print_r($_POST);
    print "</PRE>";
}

function glue_url($parsed)
{
    if (!is_array($parsed)) return false;
    $uri = $parsed['scheme'] ? $parsed['scheme'] . ':' . ((strtolower($parsed['scheme']) == 'mailto') ? '' : '//') : '';
    $uri.= $parsed['user'] ? $parsed['user'] . ($parsed['pass'] ? ':' . $parsed['pass'] : '') . '@' : '';
    $uri.= $parsed['host'] ? $parsed['host'] : '';
    $uri.= $parsed['port'] ? ':' . $parsed['port'] : '';
    $uri.= $parsed['path'] ? $parsed['path'] : '';
    $uri.= $parsed['query'] ? '?' . $parsed['query'] : '';
    $uri.= $parsed['fragment'] ? '#' . $parsed['fragment'] : '';
    return $uri;
}
/**
 * set in cache one hour
 */
function setHeaderCache($mime = "text/css")
{
    ini_set('session.cache_limiter', 'none');
    $duration = 24 * 3600;
    header("Cache-Control: private, max-age=$duration"); // use cache client (one hour) for speed optimsation
    header("Expires: " . gmdate("D, d M Y H:i:s T\n", time() + $duration)); // for mozilla
    header("Pragma: none"); // HTTP 1.0
    header("Content-type: $mime");
}
?>
