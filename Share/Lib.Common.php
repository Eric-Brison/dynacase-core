<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Common util functions
 *
 * @author Anakeen 2002
 * @version $Id: Lib.Common.php,v 1.50 2008/09/11 14:50:04 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
/**
 */
include_once ("Lib.Prefix.php");

function N_($s)
{
    return ($s);
} // to tag gettext without change text immediatly
// library of utilies functions
function print_r2($z, $ret = false)
{
    print "<PRE>";
    print_r($z, $ret);
    print "</PRE>";
    flush();
}
/**
 * send a message to system log
 * @param string $msg message to log
 * @param int $cut size limit
 */
function AddLogMsg($msg, $cut = 80)
{
    global $action;
    if (isset($action->parent)) $action->parent->AddLogMsg($msg, $cut);
}
/**
 * send a message to system log
 * @param string $functioName
 */
function deprecatedFunction($msg = '')
{
    global $action;
    if (isset($action->parent)) $action->parent->log->deprecated("Deprecated : " . $msg);
}
/**
 * send a warning msg to the user interface
 * @param string $msg
 */
function addWarningMsg($msg)
{
    global $action;
    if (isset($action->parent)) $action->parent->addWarningMsg($msg);
}
/**
 * like ucfirst for utf-8
 * @param $s
 * @return string
 */
function mb_ucfirst($s)
{
    if ($s) {
        $s = mb_strtoupper(mb_substr($s, 0, 1, 'UTF-8') , 'UTF-8') . mb_substr($s, 1, mb_strlen($s) , 'UTF-8');
    }
    return $s;
}
/**
 * get mail addr of a user
 * @param int $userid system user identificator
 * @param bool $full if true email is like : "John Doe" <John.doe@blackhole.net> else only system email address : john.doe@blackhole.net
 * @return string mail address, false if user not exists
 */
function getMailAddr($userid, $full = false)
{
    $user = new Account("", $userid);
    
    if ($user->isAffected()) {
        $pren = $postn = "";
        if ($full) {
            $pren = '"' . trim(str_replace('"', '-', ucwords(strtolower($user->getDisplayName($user->id))))) . '" <';
            $postn = '>';
        }
        return $pren . $user->getMail() . $postn;
    }
    return false;
}

function getTmpDir($def = '/tmp')
{
    global $pubdir;
    static $tmp;
    if (isset($tmp) && !empty($tmp)) {
        return $tmp;
    }
    $tmp = getParam('CORE_TMPDIR', $def);
    if (empty($tmp)) {
        return $def;
    }
    if (substr($tmp, 0, 1) != '/') {
        $tmp = $pubdir . '/' . $tmp;
    }
    /* Try to create the directory if it does not exists */
    if (!is_dir($tmp)) {
        mkdir($tmp);
    }
    /* Add suffix, and try to create the sub-directory */
    $tmp = $tmp . '/dcp';
    if (!is_dir($tmp)) {
        mkdir($tmp);
    }
    /* We ignore any failure in the directory creation
     * and return the expected tmp dir.
     * The caller will have to handle subsequent
     * errors...
    */
    return $tmp;
}
/**
 * return value of parameters
 *
 * @brief must be in core or global type
 * @param string $name param name
 * @param string $def default value if value is empty
 *
 * @return string
 */
function getParam($name, $def = "")
{
    global $action;
    if ($action) return $action->getParam($name, $def);
    // if context not yet initialized
    return getCoreParam($name, $def);
}
/**
 * return value of a parameter
 *
 * @brief must be in core or global type
 * @param string $name param name
 * @param string $def default value if value is empty
 *
 * @return string
 */
function getCoreParam($name, $def = "")
{
    static $params = null;
    
    if (empty($params)) {
        $params = array();
        $tparams = array();
        $err = simpleQuery("", "select name, val from paramv where (type = 'G') or (type='A' and appid = (select id from application where name ='CORE'));", $tparams, false, false, false);
        if ($err == "") {
            foreach ($tparams as $p) {
                $params[$p['name']] = $p['val'];
            }
        }
    }
    if (array_key_exists($name, $params) == false) {
        error_log(sprintf("parameter %s not found use %s instead", $name, $def));
        return $def;
    }
    return ($params[$name] === null) ? $def : $params[$name];
}
/**
 *
 * @param string $name the variable
 * @param string $def default value if variable is not defined
 * @return mixed
 */
function getSessionValue($name, $def = "")
{
    global $action;
    if ($action) return $action->read($name, $def);
    return null;
}
/**
 * return current log in user
 * @return Account
 */
function getCurrentUser()
{
    global $action;
    return $action->user;
}
function getLayoutFile($app, $layfile)
{
    if (strstr($layfile, '..')) {
        return "";
    }
    if (!strstr($layfile, '.')) $layfile.= ".xml";
    $socStyle = Getparam("CORE_SOCSTYLE");
    $style = Getparam("STYLE");
    $root = Getparam("CORE_PUBDIR");
    if ($socStyle != "") {
        $file = $root . "/STYLE/$socStyle/Layout/$layfile";
        if (file_exists($file)) {
            return ($file);
        }
        
        $file = $root . "/STYLE/$socStyle/Layout/" . strtolower($layfile);
        if (file_exists($file)) {
            return ($file);
        }
    } elseif ($style != "") {
        $file = $root . "/STYLE/$style/Layout/$layfile";
        if (file_exists($file)) {
            return ($file);
        }
        
        $file = $root . "/STYLE/$style/Layout/" . strtolower($layfile);
        if (file_exists($file)) {
            return ($file);
        }
    }
    
    $file = $root . "/$app/Layout/$layfile";
    if (file_exists($file)) {
        return ($file);
    }
    
    $file = $root . "/$app/Layout/" . strtolower($layfile);
    if (file_exists($file)) {
        return ($file);
    }
    
    return "";
}

function microtime_diff($a, $b)
{
    list($a_micro, $a_int) = explode(' ', $a);
    list($b_micro, $b_int) = explode(' ', $b);
    if ($a_int > $b_int) {
        return ($a_int - $b_int) + ($a_micro - $b_micro);
    } elseif ($a_int == $b_int) {
        if ($a_micro > $b_micro) {
            return ($a_int - $b_int) + ($a_micro - $b_micro);
        } elseif ($a_micro < $b_micro) {
            return ($b_int - $a_int) + ($b_micro - $a_micro);
        } else {
            return 0;
        }
    } else { // $a_int<$b_int
        return ($b_int - $a_int) + ($b_micro - $a_micro);
    }
}
/**
 * return call stack
 * @param int $slice last call to not return
 * @return array
 */
function getDebugStack($slice = 1)
{
    $td = @debug_backtrace(false);
    if (!is_array($td)) return array();
    $t = array_slice($td, $slice);
    foreach ($t as $k => $s) {
        unset($t[$k]["args"]); // no set arg
        
    }
    return $t;
}
/**
 * @param int $slice
 * @return void
 */
function logDebugStack($slice = 1)
{
    $st = getDebugStack(2);
    foreach ($st as $k => $t) {
        error_log(sprintf('%d) %s:%s %s::%s()', $k, isset($t["file"]) ? $t["file"] : 'closure', isset($t["line"]) ? $t["line"] : 0, isset($t["class"]) ? $t["class"] : '', $t["function"]));
    }
}
function getDbid($dbaccess)
{
    global $CORE_DBID;
    if (!$dbaccess) $dbaccess = getDbAccess();
    if (!isset($CORE_DBID) || !($CORE_DBID[$dbaccess])) {
        $CORE_DBID[$dbaccess] = pg_connect($dbaccess);
        if (!$CORE_DBID[$dbaccess]) {
            // fatal error
            header('HTTP/1.0 503 DB connection unavalaible');
            throw new Exception(ErrorCode::getError('DB0101', $dbaccess));
        }
    }
    return $CORE_DBID[$dbaccess];
}

function getDbAccess()
{
    return getDbAccessCore();
}

function getDbAccessCore()
{
    return "service='" . getServiceCore() . "'";
}

function getDbAccessFreedom()
{
    return "service='" . getServiceFreedom() . "'";
}
/**
 * @deprecated
 * @return string
 */
function getDbEnv()
{
    error_log("Deprecated call to getDbEnv() : use getFreedomContext()");
    return getFreedomContext();
}
/**
 * @deprecated
 * @return string
 */
function getFreedomContext()
{
    $freedomctx = getenv("freedom_context");
    if ($freedomctx == false || $freedomctx == "") {
        return "default";
    }
    return $freedomctx;
}

function getServiceCore()
{
    static $pg_service = null;
    
    if ($pg_service) return $pg_service;
    $pgservice_core = getDbAccessvalue('pgservice_core');
    
    if ($pgservice_core == "") {
        error_log("Undefined pgservice_core in dbaccess.php");
        exit(1);
    }
    $pg_service = $pgservice_core;
    return $pg_service;
}

function getDbAccessValue($varName)
{
    $included = false;
    $filename = sprintf("%s/context/dbaccess.php", DEFAULT_PUBDIR);
    if (file_exists($filename)) {
        if (include ($filename)) {
            $included = true;
        }
    } else {
        $filename = sprintf("%s/context/default/dbaccess.php", DEFAULT_PUBDIR);
        if (file_exists($filename)) {
            if (include ($filename)) {
                $included = true;
            }
        } else {
            $filename = ('dbaccess.php');
            if (include ("dbaccess.php")) {
                $included = true;
            }
        }
    }
    if (!$included) {
        throw new Exception(ErrorCode::getError("FILE0005", $filename));
    }
    return $$varName;
}
function getServiceFreedom()
{
    static $pg_service = null;
    
    if ($pg_service) return $pg_service;
    $pgservice_freedom = getDbAccessValue('pgservice_freedom');
    if ($pgservice_freedom == "") {
        error_log("Undefined pgservice_freedom in dbaccess.php");
        exit(1);
    }
    $pg_service = $pgservice_freedom;
    return $pg_service;
}

function getDbName($dbaccess)
{
    error_log("Deprecated call to getDbName(dbaccess) : use getServiceName(dbaccess)");
    return getServiceName($dbaccess);
}

function getServiceName($dbaccess)
{
    if (preg_match("/service='?([a-zA-Z0-9_.-]+)/", $dbaccess, $reg)) {
        return $reg[1];
    }
    return '';
}
/**
 * send simple query to database
 * @param string $dbaccessaccess database coordonates
 * @param string $query sql query
 * @param string|array &$result  query result
 * @param bool $singlecolumn  set to true if only one field is return
 * @param bool $singleresult  set to true is only one row is expected (return the first row). If is combined with singlecolumn return the value not an array
 * @param bool $useStrict set to true to force exception or false to force no exception, if null use global parameter
 * @throw Exception when sql error in query
 * @return string error message. Empty message if no errors (when strict mode is not enable)
 */
function simpleQuery($dbaccess, $query, &$result = array() , $singlecolumn = false, $singleresult = false, $useStrict = null)
{
    global $SQLDEBUG;
    static $sqlStrict = null;
    
    $dbid = getDbid($dbaccess);
    $err = '';
    if ($dbid) {
        $result = array();
        $sqlt1 = 0;
        if ($SQLDEBUG) $sqlt1 = microtime();
        $r = @pg_query($dbid, $query);
        if ($r) {
            if (pg_numrows($r) > 0) {
                if ($singlecolumn) $result = pg_fetch_all_columns($r, 0);
                else $result = pg_fetch_all($r);
                if ($singleresult) $result = $result[0];
            } else {
                if ($singleresult) $result = false;
            }
            if ($SQLDEBUG) {
                global $TSQLDELAY, $SQLDELAY;
                $SQLDELAY+= microtime_diff(microtime() , $sqlt1); // to test delay of request
                $TSQLDELAY[] = array(
                    "t" => sprintf("%.04f", microtime_diff(microtime() , $sqlt1)) ,
                    "s" => str_replace(array(
                        "from",
                        'where'
                    ) , array(
                        "\nfrom",
                        "\nwhere"
                    ) , $query) ,
                    "st" => getDebugStack(1)
                );
            }
        } else {
            $err = ErrorCode::getError('DB0100', pg_last_error($dbid) , $query);
        }
    } else $err = ErrorCode::getError('DB0102', $dbaccess, $err, $query);
    if ($err) {
        logDebugStack();
        error_log($err);
        if ($useStrict !== false) {
            if ($sqlStrict === null) $sqlStrict = (getParam("CORE_SQLSTRICT") != "no");
            if ($useStrict === true || $sqlStrict) {
                
                throw new Exception($err);
            }
        }
    }
    
    return $err;
}
function getAuthType($freedomctx = "")
{
    global $pubdir;
    
    if (array_key_exists('authtype', $_GET)) {
        return $_GET['authtype'];
    }
    $freedom_authtype = getDbAccessValue('freedom_authtype');
    
    if ($freedom_authtype == "") {
        $freedom_authtype = "apache";
    }
    
    return trim($freedom_authtype);
}

function getAuthProvider($freedomctx = "")
{
    global $pubdir;
    $freedom_authprovider = getDbAccessValue('freedom_authprovider');
    
    if ($freedom_authprovider == "") {
        $freedom_authprovider = "apache";
    }
    
    return trim($freedom_authprovider);
}

function getAuthProviderList($freedomctx = "")
{
    global $pubdir;
    
    return preg_split("/\s*,\s*/", getAuthProvider($freedomctx));
}

function getAuthTypeParams($freedomctx = "")
{
    global $pubdir;
    $freedom_authtypeparams = getDbAccessValue('freedom_authtypeparams');
    if (!is_array($freedom_authtypeparams)) {
        throw new Exception(ErrorCode::getError('FILE0006'));
    }
    
    if (!array_key_exists(getAuthType() , $freedom_authtypeparams)) {
        error_log(__FUNCTION__ . ":" . __LINE__ . "> authtype " . getAuthType() . " does not exists in freedom_authtypeparams");
        return array();
    }
    
    return $freedom_authtypeparams[getAuthType() ];
}

function getAuthParam($freedomctx = "", $provider = "")
{
    global $pubdir;
    
    if ($provider == "") return array();
    $freedom_providers = getDbAccessValue('freedom_providers');
    if (!is_array($freedom_providers)) {
        return array();
    }
    
    if (!array_key_exists($provider, $freedom_providers)) {
        error_log(__FUNCTION__ . ":" . __LINE__ . "provider " . $provider . " does not exists in freedom_providers");
        return array();
    }
    
    return $freedom_providers[$provider];
}
/**
 * return shell commande for wsh
 * depending of database (in case of several instances)
 * @param bool $nice set to true if want nice mode
 * @param int $userid the user identificator to send command (if 0 send like admin without specific user parameter)
 * @param bool $sudo set to true if want to be send with sudo (need /etc/sudoers correctly configured)
 * @return string the command
 */
function getWshCmd($nice = false, $userid = 0, $sudo = false)
{
    $freedomctx = getFreedomContext(); // choose when several databases
    $wsh = "export freedom_context=\"$freedomctx\";";
    if ($nice) $wsh.= "nice -n +10 ";
    if ($sudo) $wsh.= "sudo ";
    $wsh.= escapeshellarg(GetParam("CORE_PUBDIR")) . "/wsh.php  ";
    $userid = intval($userid);
    if ($userid > 0) $wsh.= "--userid=$userid ";
    return $wsh;
}
/**
 * get the system user id
 * @return int
 */
function getUserId()
{
    global $action;
    if ($action) return $action->user->id;
    
    return 0;
}
/**
 * exec list of unix command in background
 * @param array $tcmd unix command strings
 */
function bgexec($tcmd, &$result, &$err)
{
    $foutname = uniqid(getTmpDir() . "/bgexec");
    $fout = fopen($foutname, "w+");
    fwrite($fout, "#!/bin/bash\n");
    foreach ($tcmd as $v) {
        fwrite($fout, "$v\n");
    }
    fclose($fout);
    chmod($foutname, 0700);
    //  if (session_id()) session_write_close(); // necessary to close if not background cmd
    exec("exec nohup $foutname > /dev/null 2>&1 &", $result, $err);
    //if (session_id()) @session_start();
    
}

function wbartext($text)
{
    wbar('-', '-', $text);
}

function wbar($reste, $total, $text = "", $fbar = false)
{
    static $preste, $ptotal;
    if (!$fbar) $fbar = GetHttpVars("bar"); // for progress bar
    if ($fbar) {
        if ($reste === '-') $reste = $preste;
        else $preste = $reste;
        if ($total === '-') $total = $ptotal;
        else $ptotal = $total;
        if (file_exists("$fbar.lck")) {
            $wmode = "w";
            unlink("$fbar.lck");
        } else {
            $wmode = "a";
        }
        $ffbar = fopen($fbar, $wmode);
        fputs($ffbar, "$reste/$total/$text\n");
        fclose($ffbar);
    }
}

function getJsVersion()
{
    include_once ("Class.QueryDb.php");
    $q = new QueryDb("", "param");
    $q->AddQuery("name='VERSION'");
    $l = $q->Query(0, 0, "TABLE");
    $nv = 0;
    foreach ($l as $k => $v) {
        $nv+= intval(str_replace('.', '', $v["val"]));
    }
    
    return $nv;
}
/**
 * produce an anchor mailto '<a ...>'
 * @param string $to a valid mail address or list separated by comma -supported by client-
 * @param string $anchor content <a...>anchor content</a>
 * @param string $subject
 * @param string $cc
 * @param string $bcc
 * @param array $treated as html anchor attribute : key is attribute name and value.. value
 * @param string $force link to be produced according the value
 * @return string like user admin dbname anakeen
 */
function setMailtoAnchor($to, $acontent = "", $subject = "", $cc = "", $bcc = "", $from = "", $anchorattr = array() , $forcelink = "")
{
    
    global $action;
    
    if ($to == "") return '';
    $classcode = '';
    if ($forcelink == "mailto" || $forcelink == "squirrel") {
        $target = $forcelink;
    } else {
        $target = strtolower(GetParam("CORE_MAIL_LINK", "optimal"));
        if ($target == "optimal") {
            $target = "mailto";
        }
    }
    $prot = ($_SERVER["HTTPS"] == "on" ? "https" : "http");
    $host = $_SERVER["SERVER_NAME"];
    $port = $_SERVER["SERVER_PORT"];
    
    $attrcode = "";
    if (is_array($anchorattr)) {
        foreach ($anchorattr as $k => $v) $attrcode.= ' ' . $k . '="' . $v . '"';
    }
    
    $subject = str_replace(" ", "%20", $subject);
    
    switch ($target) {
        case "squirrel":
            $link = ' <a ';
            $link.= 'href="' . $prot . "://" . $host . ":" . $port . "/" . GetParam("CORE_MAIL_SQUIRRELBASE", "squirrel") . "/src/compose.php?";
            $link.= "&send_to=" . $to;
            $link.= ($subject != "" ? '&subject=' . $subject : '');
            $link.= ($cc != "" ? '&cc=' . $cc : '');
            $link.= ($bcc != "" ? '&bcc=' . $bcc : '');
            $link.= '"';
            $link.= $attrcode;
            $link.= '>';
            $link.= $acontent;
            $link.= '</a>';
            break;

        case "mailto":
            $link = '<a ';
            $link.= 'href="mailto:' . $to . '"';
            $link.= ($subject != "" ? '&Subject=' . $subject : '');
            $link.= ($cc != "" ? '&cc=' . $cc : '');
            $link.= ($bcc != "" ? '&bcc=' . $bcc : '');
            $link.= '"';
            $link.= $attrcode;
            $link.= '>';
            $link.= $acontent;
            $link.= '</a>';
            break;

        default:
            $link = '<span ' . $classcode . '>' . $acontent . '</span>';
    }
    return $link;
}
/**
 * Returns <kbd>true</kbd> if the string or array of string is encoded in UTF8.
 *
 * Example of use. If you want to know if a file is saved in UTF8 format :
 * <code> $array = file('one file.txt');
 * $isUTF8 = isUTF8($array);
 * if (!$isUTF8) --> we need to apply utf8_encode() to be in UTF8
 * else --> we are in UTF8 :)
 * </code>
 * @param mixed $string, or an array from a file() function.
 * @return boolean
 */
function isUTF8($string)
{
    if (is_array($string)) return seems_utf8(implode('', $string));
    else return seems_utf8($string);
}
/**
 * Returns <kbd>true</kbd> if the string  is encoded in UTF8.
 *
 * @param mixed $Str string
 * @return boolean
 */
function seems_utf8($Str)
{
    for ($i = 0; $i < strlen($Str); $i++) {
        if (ord($Str[$i]) < 0x80) $n = 0; # 0bbbbbbb
        elseif ((ord($Str[$i]) & 0xE0) == 0xC0) $n = 1; # 110bbbbb
        elseif ((ord($Str[$i]) & 0xF0) == 0xE0) $n = 2; # 1110bbbb
        elseif ((ord($Str[$i]) & 0xF0) == 0xF0) $n = 3; # 1111bbbb
        else return false; # Does not match any model
        for ($j = 0; $j < $n; $j++) { # n octets that match 10bbbbbb follow ?
            if ((++$i == strlen($Str)) || ((ord($Str[$i]) & 0xC0) != 0x80)) return false;
        }
    }
    return true;
}
/**
 * Initialise WHAT : set global $action whithout an authorized user
 *
 */
function WhatInitialisation()
{
    global $action;
    include_once ('Class.User.php');
    include_once ('Class.Session.php');
    
    $CoreNull = "";
    $core = new Application();
    $core->Set("CORE", $CoreNull);
    $core->session = new Session();
    $action = new Action();
    $action->Set("", $core);
    // i18n
    $lang = $action->Getparam("CORE_LANG");
    setLanguage($lang);
}

function setSystemLogin($login)
{
    global $action;
    include_once ('Class.User.php');
    include_once ('Class.Session.php');
    
    if ($login != "") {
        $action->user = new Account(); //create user
        $action->user->setLoginName($login);
    }
}
/**
 * Returns a random password of specified length composed
 * with chars from the given charspace string or pattern
 */

function mkpasswd($length = 8, $charspace = "")
{
    if ($charspace == "") {
        $charspace = "[:alnum:]";
    }
    // Repeat a pattern e.g. [:a:3] -> [:a:][:a:][:a:]
    $charspace = preg_replace("/(\[:[a-z]+:)(\d+)(\])/e", "str_repeat('\\1\\3',\\2)", $charspace);
    // Expand [:patterns:]
    $charspace = preg_replace(array(
        "/\[:alnum:\]/",
        "/\[:extrastrong:\]/",
        "/\[:hex:\]/",
        "/\[:lower:\]/",
        "/\[:upper:\]/",
        "/\[:digit:\]/",
        "/\[:extra:\]/",
    ) , array(
        "[:lower:][:upper:][:digit:]",
        "[:extra:],;:=+*/(){}[]&@#!?\"'<>",
        "[:digit:]abcdef",
        "abcdefghijklmnopqrstuvwxyz",
        "ABCDEFGHIJKLMNOPQRSTUVWXYZ",
        "0123456789",
        "-_.",
    ) , $charspace);
    
    $passwd = "";
    for ($i = 0; $i < $length; $i++) {
        $passwd.= substr($charspace, rand(0, strlen($charspace) - 1) , 1);
    }
    
    return $passwd;
}
/**
 + * return lcdate use in database : iso or dmy
 + * @return string
 +
 */
function getLcdate()
{
    return substr(getParam("CORE_LCDATE") , 0, 3);
}
/**
 *
 * @param string $core_lang
 */
function getLocaleConfig($core_lang = '')
{
    
    if (empty($core_lang)) {
        $core_lang = getParam("CORE_LANG", "fr_FR");
    }
    $lng = substr($core_lang, 0, 2);
    if (preg_match('#^[a-z0-9_\.-]+$#i', $core_lang) && file_exists("locale/" . $lng . "/lang.php")) {
        include ("locale/" . $lng . "/lang.php");
    } else {
        include ("locale/fr/lang.php");
    }
    if (!isset($lang) || !isset($lang[$core_lang]) || !is_array($lang[$core_lang])) {
        return false;
    }
    return $lang[$core_lang];
}
/**
 *
 * @param string $lang
 */
function setLanguage($lang)
{
    global $pubdir;
    //  print "<h1>setLanguage:$lang</H1>";
    $lang.= ".UTF-8";
    setlocale(LC_MESSAGES, $lang);
    setlocale(LC_CTYPE, $lang);
    setlocale(LC_MONETARY, $lang);
    setlocale(LC_TIME, $lang);
    //print $action->Getparam("CORE_LANG");
    $number = 0;
    if (is_file("$pubdir/locale/.gettextnumber")) {
        $number = trim(@file_get_contents("$pubdir/locale/.gettextnumber"));
        if ($number == "") {
            $number = 0;
        }
    }
    
    $td = "freedom-catalog$number";
    
    putenv("LANG=" . $lang); // needed for old Linux kernel < 2.4
    bindtextdomain($td, "$pubdir/locale");
    bind_textdomain_codeset($td, 'utf-8');
    textdomain($td);
    mb_internal_encoding('UTF-8');
}
?>