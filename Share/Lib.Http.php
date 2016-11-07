<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Set of usefull HTTP functions
 *
 * @author Anakeen
 * @version $Id: Lib.Http.php,v 1.38 2008/11/28 12:48:06 eric Exp $
 * @package FDL
 * @subpackage CORE
 */
/**
 * @param Action|Application $action
 * @param string $appname
 * @param string $actionname
 * @param string $otherurl
 * @param bool $httpparamredirect
 */
function Redirect($action, $appname, $actionname, $otherurl = "", $httpparamredirect = false)
{
    global $_SERVER, $_GET; // use only  with HTTP
    if (empty($_SERVER['HTTP_HOST'])) {
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
    $viewext = isset($_GET["viewext"]) ? $_GET["viewext"] : (isset($_POST["viewext"]) ? $_POST["viewext"] : "");
    if ($viewext === "yes") {
        if (\Dcp\Autoloader::classExists("Dcp\\ExtUi\\defaultMenu")) {
            /** @noinspection PhpUndefinedNamespaceInspection */
            /** @noinspection PhpUndefinedClassInspection */
            $location = \Dcp\ExtUi\defaultMenu::convertToExtUrl($location);
        }
    }
    header("Location: $location");
    exit;
}

function RedirectSender(Action & $action)
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
/**
 * if in useIndexAsGuest mode
 * redirect with authtication to current url
 * only if it is anonymous also
 * @param Action $action
 */
function redirectAsGuest(Action & $action)
{
    $guestMode = getDbAccessValue("useIndexAsGuest");
    if ($guestMode) {
        if ($action->user->id == Account::ANONYMOUS_ID) {
            /**
             * @var htmlAuthenticator $auth
             */
            $auth = AuthenticatorManager::$auth;
            if (is_a($auth, "htmlAuthenticator")) $auth->connectTo($_SERVER['REQUEST_URI']);
        }
    }
}
/**
 * return value of an http parameter
 * @param string $name parameter key
 * @param string $def default value if parameter is not set
 * @param string $scope The scope for the search of the value ('zone' for $ZONE_ARGS, 'get' for $_GET, 'post' for $_POST and 'all' for searching in all)
 * @return string
 */
function getHttpVars($name, $def = "", $scope = "all")
{
    global $_GET, $_POST, $ZONE_ARGS;
    
    if (($scope == "all" || $scope == "zone") && isset($ZONE_ARGS[$name])) {
        // try zone args first : it is set be Layout::execute for a zone
        return ($ZONE_ARGS[$name]);
    }
    if (($scope == "all" || $scope == "get") && isset($_GET[$name])) {
        return $_GET[$name];
    }
    if (($scope == "all" || $scope == "post") && isset($_POST[$name])) {
        return $_POST[$name];
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
    foreach ($mimes as $v) {
        if (substr($v, 0, 1) == "#") continue;
        $tab = preg_split('/\s+/', $v);
        if ((isset($tab[1])) && ($tab[1] == $ext)) return ($tab[0]);
    }
    return ("text/any");
}

function GetExt($mime_type)
{
    $mimes = file("/etc/mime.types");
    foreach ($mimes as $v) {
        if (substr($v, 0, 1) == "#") continue;
        $tab = preg_split('\s+/', $v);
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
/**
 * Send a response with the given data to be downloaded by the client.
 *
 * No output should be generated on stdout after calling this function.
 *
 * @param string $src the data to send to the client
 * @param string $ext the extension of the data (e.g. "pdf", "png", etc.)
 * @param string $name the filename that will be used by the client for saving to a file
 * @param bool $add_ext add the $ext extension to the $name filename (default = TRUE)
 * @param string $mime_type the Content-Type MIME type of the response. If empty, compute MIME type from $ext extension (this is the default behaviour)
 * @return void
 */
function Http_Download($src, $ext, $name, $add_ext = TRUE, $mime_type = "")
{
    if ($mime_type == '') $mime_type = GetMimeType($ext);
    if ($add_ext) $name = $name . "." . $ext;
    $name = str_replace('"', '\\"', $name);
    $uName = iconv("UTF-8", "ASCII//TRANSLIT", $name);
    $name = rawurlencode($name);
    header("Cache-control: private"); // for IE : don't know why !!
    header('Content-Length: ' . strlen($src));
    header("Pragma: "); // HTTP 1.0
    header("Content-Disposition: attachment;filename=\"$uName\";filename*=UTF-8''$name;");
    header("Content-type: " . $mime_type);
    echo $src;
}
/**
 * Send a response with the content of a local file to be downloaded by the client
 *
 * No output should be generated on stdout after calling this function.
 *
 * @param string $filename pathname of the file that will be sent to the client (e.g. "/tmp/foo.pdf")
 * @param string $name the basename of the file (e.g. "foo.pdf")
 * @param string $mime_type the Content-Type MIME type of the response (e.g. "application/pdf")
 * @param bool $inline Send the data with inline Content-Disposition (default = FALSE)
 * @param bool $cache Instruct clients and/or proxies to cache the response for 24h (default = TRUE)
 * @param bool $deleteafter Delete the $filename file when done (default = FALSE)
 * @return void
 */
function Http_DownloadFile($filename, $name, $mime_type = '', $inline = false, $cache = true, $deleteafter = false)
{
    if (!file_exists($filename)) {
        printf(_("file not found : %s") , $filename);
        return;
    }
    
    if (php_sapi_name() !== 'cli') {
        // Double quote not supported by all browsers - replace by minus
        $name = str_replace('"', '-', $name);
        $uName = iconv("UTF-8", "ASCII//TRANSLIT", $name);
        $name = rawurlencode($name);
        if (!$inline) {
            header("Content-Disposition: attachment;filename=\"$uName\";filename*=UTF-8''$name;");
        } else {
            header("Content-Disposition: inline;filename=\"$uName\";filename*=UTF-8''$name;");
        }
        
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
        $buflen = ob_get_length();
        if ($buflen !== false && $buflen > 0) {
            ob_clean();
        }
        flush();
    }
    readfile($filename);
    if ($deleteafter) unlink($filename);
    exit;
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
 * @param string $mime
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
