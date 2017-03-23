<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Application Class
 *
 * @author Anakeen
 * @version $Id: Class.Application.php,v 1.64 2008/08/01 09:03:01 eric Exp $
 * @package FDL
 * @subpackage CORE
 */
/**
 */

require_once ('WHAT/autoload.php');
include_once ('WHAT/Lib.Http.php');
include_once ('WHAT/Lib.Common.php');

function f_paramglog($var)
{ // filter to select only not global
    return (!((isset($var["global"]) && ($var["global"] == 'Y'))));
}
/**
 * Application managing
 * @class Application
 *
 */
class Application extends DbObj
{
    public $fields = array(
        "id",
        "name",
        "short_name",
        "description",
        "access_free", //@deprecated
        "available",
        "icon",
        "displayable",
        "with_frame",
        "childof",
        "objectclass", //@deprecated
        "ssl", //@deprecated
        "machine", //@deprecated
        "iorder",
        "tag"
    );
    /**
     * @var int application identifier
     */
    public $id;
    public $name;
    public $short_name;
    public $description;
    /**
     * @deprecated
     * @var $access_free
     */
    public $access_free;
    public $available;
    public $icon;
    public $displayable;
    public $with_frame;
    public $childof;
    /**
     * @deprecated
     * @var $objectclass
     */
    public $objectclass;
    /**
     * @deprecated
     * @var $ssl
     */
    public $ssl;
    /**
     * @deprecated
     * @var $machine
     */
    public $machine;
    public $iorder;
    public $tag;
    public $id_fields = array(
        "id"
    );
    public $rootdir = '';
    public $fulltextfields = array(
        "name",
        "short_name",
        "description"
    );
    public $sqlcreate = '
create table application ( 	id 	int not null,
     		primary key (id),
			name 	    text not null,
			short_name text,
			description text ,
			access_free  char,
			available  char,
                        icon text,
                        displayable char,
                        with_frame char,
                        childof text,
                        objectclass char,
                        ssl char,
                        machine text,
                        iorder int,
                        tag text);
create index application_idx1 on application(id);
create index application_idx2 on application(name);
create sequence SEQ_ID_APPLICATION start 10;
';
    
    public $dbtable = "application";
    
    public $def = array(
        "criteria" => "",
        "order_by" => "name"
    );
    
    public $criterias = array(
        "name" => array(
            "libelle" => "Nom",
            "type" => "TXT"
        )
    );
    /**
     * @var Application
     */
    public $parent = null;
    /**
     * @var Session
     */
    public $session = null;
    /**
     * @var Account
     */
    public $user = null;
    /**
     * @var Style
     */
    public $style;
    /**
     * @var Param
     */
    public $param;
    /**
     * @var Permission
     */
    public $permission = null; // permission object
    
    /**
     * @var Log
     */
    public $log = null;
    public $jsref = array();
    public $jscode = array();
    public $logmsg = array();
    /**
     * true if application is launched from admin context
     * @var bool
     */
    protected $adminMode = false;
    
    public $cssref = array();
    public $csscode = array();
    /**
     * Application constructor.
     * @param string $dbaccess
     * @param string|string[] $id
     * @param string|array $res
     * @param int $dbid
     */
    function __construct($dbaccess = '', $id = '', $res = '', $dbid = 0)
    {
        parent::__construct($dbaccess, $id, $res, $dbid);
        $this->rootdir = DEFAULT_PUBDIR;
    }
    /**
     * initialize  Application object
     * @param string $name application name to set
     * @param Application|string $parent the parent object (generally CORE app) : empty string if no parent
     * @param string $session parent session
     * @param bool $autoinit set to true to auto create app if not exists yet
     *
     * @param bool $verifyAvailable set to true to not exit when unavailable action
     * @return string error message
     * @throws \Dcp\Core\Exception if application not exists
     * @throws \Dcp\Db\Exception
     * @code
     $CoreNull = "";
     * $core = new Application();
     * $core->Set("CORE", $CoreNull); // init core application from nothing
     * $core->session = new Session();
     * $core->session->set();
     * $one = new Application();
     * $one->set("ONEFAM", $core, $core->session);// init ONEFAM application from CORE
     *
     * @endcode
     *
     */
    public function set($name, &$parent, $session = "", $autoinit = false, $verifyAvailable = true)
    {
        $this->log->debug("Entering : Set application to $name");
        
        $query = new QueryDb($this->dbaccess, "Application");
        $query->order_by = "";
        $query->criteria = "name";
        $query->operator = "=";
        $query->string = "'" . pg_escape_string($name) . "'";
        $list = $query->Query(0, 0, "TABLE");
        if ($query->nb != 0) {
            $this->affect($list[0]);
            $this->log->debug("Set application to $name");
            if (!isset($parent)) {
                $this->log->debug("Parent not set");
            }
        } else {
            if ($autoinit) {
                // Init the database with the app file if it exists
                $this->InitApp($name);
                if ($parent != "") {
                    $this->parent = & $parent;
                    if ($this->name == "") {
                        printf("Application name %s not found", $name);
                        exit;
                    } elseif (!empty($_SERVER['HTTP_HOST'])) {
                        Redirect($this, $this->name, "");
                    }
                } else {
                    global $_SERVER;
                    if (!empty($_SERVER['HTTP_HOST'])) Header("Location: " . $_SERVER['HTTP_REFERER']);
                }
            } else {
                $e = new Dcp\Core\Exception("CORE0004", $name);
                $e->addHttpHeader('HTTP/1.0 404 Application not found');
                throw $e;
            }
        }
        
        if ($this !== $parent) $this->parent = & $parent;
        if (is_object($this->parent) && isset($this->parent->session)) {
            $this->session = $this->parent->session;
            if (isset($this->parent->user) && is_object($this->parent->user)) {
                $this->user = $this->parent->user;
            }
        }
        
        if ($session != "") $this->SetSession($session);
        $this->param = new Param($this->dbaccess);
        $style = false;
        if ($this->session) $style = $this->session->read("userCoreStyle", false);
        
        if ($style) {
            $this->InitStyle(false, $style);
        } else {
            $this->InitStyle();
        }
        if ($this->session) {
            $pStyle = $this->getParam("STYLE");
            if ($pStyle) {
                $this->session->register("userCoreStyle", $pStyle);
            }
        }
        
        $this->param->SetKey($this->id, isset($this->user->id) ? $this->user->id : false, $this->style->name);
        if ($verifyAvailable && $this->available === "N") {
            // error
            $e = new Dcp\Core\Exception("CORE0007", $name);
            $e->addHttpHeader('HTTP/1.0 503 Application unavailable');
            throw $e;
        }
        $this->permission = null;
        return '';
    }
    
    public function complete()
    {
    }
    
    public function setSession(&$session)
    {
        $this->session = $session;
        // Set the user if possible
        if (is_object($this->session)) {
            if ($this->session->userid != 0) {
                $this->log->debug("Get user on " . $this->dbaccess);
                $this->user = new Account($this->dbaccess, $this->session->userid);
            } else {
                $this->log->debug("User not set ");
            }
        }
    }
    
    public function preInsert()
    {
        if ($this->Exists($this->name)) return "Ce nom d'application existe deja...";
        if ($this->name == "CORE") {
            $this->id = 1;
        } else {
            $this->exec_query("select nextval ('seq_id_application')");
            $arr = $this->fetch_array(0);
            $this->id = $arr["nextval"];
        }
        return '';
    }
    
    public function preUpdate()
    {
        if ($this->dbid == - 1) return false;
        if ($this->Exists($this->name, $this->id)) return "Ce nom d'application existe deja...";
        return '';
    }
    /**
     * Verify an application name exists
     * @param string $app_name application reference name
     * @param int $id_application optional numeric id to verify if not itself
     * @return bool
     */
    public function exists($app_name, $id_application = 0)
    {
        $this->log->debug("Exists $app_name ?");
        $query = new QueryDb($this->dbaccess, "application");
        $query->order_by = "";
        $query->criteria = "";
        
        if ($id_application != '') {
            $query->basic_elem->sup_where = array(
                "name='$app_name'",
                "id!=$id_application"
            );
        } else {
            $query->criteria = "name";
            $query->operator = "=";
            $query->string = "'" . $app_name . "'";
        }
        
        $r = $query->Query(0, 0, "TABLE");
        
        return ($query->nb > 0) ? $r[0]["id"] : false;
    }
    /**
     * Strip the pubdir/wpub directory from a file pathname
     * @param string $pathname the file pathname
     * @return string file pathname without the root dir
     */
    private function stripRootDir($pathname)
    {
        if (substr($pathname, 0, strlen($this->rootdir)) === $this->rootdir) {
            $pathname = substr($pathname, strlen($this->rootdir) + 1);
        }
        
        return $pathname;
    }
    /**
     * Try to resolve a JS/CSS reference to a supported location
     * @param string $ref the JS/CSS reference
     * @return string the resolved location of the reference or an empty string on failure
     */
    private function resolveResourceLocation($ref)
    {
        if (strstr($ref, '../') !== false) {
            return '';
        }
        /* Resolve through getLayoutFile */
        $location = $this->GetLayoutFile($ref);
        if ($location != '') {
            return $this->stripRootDir($location);
        }
        /* Try "APP:file.extension" notation */
        if (preg_match('/^(?P<appname>[a-z][a-z0-9_-]*):(?P<filename>.*)$/i', $ref, $m)) {
            $location = sprintf('%s/%s/Layout/%s', $this->rootdir, $m['appname'], $m['filename']);
            if (is_file($location)) {
                return sprintf('%s/Layout/%s', $m['appname'], $m['filename']);
            }
        }
        /* Try hardcoded locations */
        foreach (array(
            $ref,
            sprintf("%s/Layout/%s", $this->name, $ref)
        ) as $filename) {
            if (is_file(sprintf("%s/%s", $this->rootdir, $filename))) {
                return $filename;
            }
        }
        /* Detect URLs */
        $pUrl = parse_url($ref);
        if (isset($pUrl['scheme']) || isset($pUrl['query'])) {
            return $ref;
        }
        
        if (is_file($ref)) return $ref;
        /* TODO : update with application log class */
        $this->log->error(__METHOD__ . " Unable to identify the ref $ref");
        
        return '';
    }
    /**
     * Add a resource (JS/CSS) to the page
     *
     * @param string $type 'js' or 'css'
     * @param string $ref the resource reference
     * @param boolean $needparse should the resource be parsed (default false)
     * @param string $packName
     *
     * @return string resource location
     */
    public function addRessourceRef($type, $ref, $needparse, $packName)
    {
        /* Try to attach the resource to the parent app */
        if ($this->hasParent()) {
            $ret = $this->parent->AddRessourceRef($type, $ref, $needparse, $packName);
            if ($ret !== '') {
                return $ret;
            }
        }
        
        $resourceLocation = $this->getResourceLocation($type, $ref, $needparse, $packName, true);
        if (!$resourceLocation) {
            $wng = sprintf(_("Cannot find %s resource file") , $ref);
            $this->addLogMsg($wng);
            $this->log->warning($wng);
        }
        if ($type == 'js') {
            $this->jsref[$resourceLocation] = $resourceLocation;
        } elseif ($type == 'css') {
            $this->cssref[$resourceLocation] = $resourceLocation;
        } else {
            return '';
        }
        
        return $resourceLocation;
    }
    /**
     * Get resourceLocation with cache handling
     *
     * @param string $type (js|css)
     * @param string $ref path or URI of the resource
     * @param bool $needparse need to parse
     * @param string $packName use it to pack all the ref with the same packName into a single file
     * @param bool $fromAdd (do not use this param) true only if you call it from addRessourceRef function
     *
     * @return string new location
     */
    private function getResourceLocation($type, $ref, $needparse, $packName, $fromAdd = false)
    {
        static $firstPack = array();
        $resourceLocation = '';
        
        $key = isset($this->session) ? $this->session->getUKey(getParam("WVERSION")) : uniqid(getParam("WVERSION"));
        if ($packName) {
            
            $resourcePackParseLocation = sprintf("?app=CORE&amp;action=CORE_CSS&amp;type=%s&amp;ukey=%s&amp;pack=%s", $type, $key, $packName);
            $resourcePackNoParseLocation = sprintf("pack.php?type=%s&amp;pack=%s&amp;wv=%s", $type, $packName, getParam("WVERSION"));
            
            if (!isset($firstPack[$packName])) {
                $packSession = array();
                $firstPack[$packName] = true;
            } else {
                $packSession = ($this->session ? $this->session->Read("RSPACK_" . $packName) : array());
                if (!$packSession) {
                    $packSession = array();
                }
            }
            $packSession[$ref] = array(
                "ref" => $ref,
                "needparse" => $needparse
            );
            if ($this->session) {
                $this->session->Register("RSPACK_" . $packName, $packSession);
            }
            
            if ($needparse) {
                if ($fromAdd) {
                    if ($type == "js") {
                        unset($this->jsref[$resourcePackNoParseLocation]);
                    } elseif ($type == "css") {
                        unset($this->cssref[$resourcePackNoParseLocation]);
                    }
                }
                $resourceLocation = $resourcePackParseLocation;
            } else {
                $hasParseBefore = (($type === "js") && isset($this->jsref[$resourcePackParseLocation]));
                if (!$hasParseBefore) {
                    $hasParseBefore = (($type === "css") && isset($this->cssref[$resourcePackParseLocation]));
                }
                if (!$hasParseBefore) {
                    $resourceLocation = $resourcePackNoParseLocation;
                }
            }
        } elseif ($needparse) {
            $resourceLocation = "?app=CORE&amp;action=CORE_CSS&amp;ukey=" . $key . "&amp;layout=" . $ref . "&amp;type=" . $type;
        } else {
            $location = $this->resolveResourceLocation($ref);
            if ($location != '') {
                $resourceLocation = (strpos($location, '?') !== false) ? $location : $location . '?wv=' . getParam("WVERSION");
            }
        }
        
        return $resourceLocation;
    }
    /**
     * Get dynacase CSS link
     *
     * @api Get the src of a CSS with dynacase cache
     *
     * @param string $ref path, or URL, or filename (if in the current application), or APP:filename
     * @param bool $needparse if true will be parsed by the template engine (false by default)
     * @param string $packName use it to pack all the ref with the same packName into a single file
     *
     * @return string the src of the CSS or "" if non existent ref
     */
    public function getCssLink($ref, $needparse = null, $packName = '')
    {
        if (substr($ref, 0, 2) == './') {
            $ref = substr($ref, 2);
        }
        $styleParseRule = $this->detectCssParse($ref, $needparse);
        $rl = $this->getResourceLocation('css', $ref, $styleParseRule, $packName);
        if (!$rl) {
            $msg = sprintf(_("Cannot find %s resource file") , $ref);
            $this->addLogMsg($msg);
            $this->log->warning($msg);
        }
        return $rl;
    }
    /**
     * Get dynacase JS link
     *
     * @api Get the src of a JS with dynacase cache
     *
     * @param string $ref path, or URL, or filename (if in the current application), or APP:filename
     * @param bool $needparse if true will be parsed by the template engine (false by default)
     * @param string $packName use it to pack all the ref with the same packName into a single file
     *
     * @return string the src of the JS or "" if ref not exists
     */
    public function getJsLink($ref, $needparse = false, $packName = '')
    {
        if (substr($ref, 0, 2) == './') {
            $ref = substr($ref, 2);
        }
        $rl = $this->getResourceLocation('js', $ref, $needparse, $packName);
        if (!$rl) {
            $msg = sprintf(_("Cannot find %s resource file") , $ref);
            $this->addLogMsg($msg);
            $this->log->warning($msg);
        }
        return $rl;
    }
    /**
     * Add a CSS in an action
     *
     * Use this method to add a CSS in an action that use the zone [CSS:REF] and the template engine
     *
     * @api Add a CSS in an action
     *
     * @param string $ref path, or URL, or filename (if in the current application), or APP:filename
     * @param bool $needparse if true will be parsed by the template engine (false by default)
     * @param string $packName use it to pack all the ref with the same packName into a single file
     *
     * @throws Dcp\Style\Exception
     * @return string the path of the added ref or "" if the ref is not valid
     */
    public function addCssRef($ref, $needparse = null, $packName = '')
    {
        $styleParseRule = $this->detectCssParse($ref, $needparse);
        
        if (substr($ref, 0, 2) == './') $ref = substr($ref, 2);
        return $this->AddRessourceRef('css', $ref, $styleParseRule, $packName);
    }
    
    private function detectCssParse($ref, $askParse)
    {
        $needparse = $askParse;
        $currentFileRule = $this->style->getRule('css', $ref);
        if (is_array($currentFileRule)) {
            if (isset($currentFileRule['flags']) && ($currentFileRule['flags'] & Style::RULE_FLAG_PARSE_ON_RUNTIME)) {
                if (isset($currentFileRule['runtime_parser']) && is_array($currentFileRule['runtime_parser']) && isset($currentFileRule['runtime_parser']['className']) && null !== $currentFileRule['parse_on_runtime']['className']) {
                    throw new \Dcp\Style\Exception("STY0007", 'custom parse_on_runtime class is not supported yet');
                }
                $parseOnLoad = true;
                if ((null !== $needparse) && ($parseOnLoad !== $needparse)) {
                    $this->log->warning(sprintf("%s was added with needParse to %s but style has a rule saying %s", $ref, var_export($needparse, true) , var_export($parseOnLoad, true)));
                }
                $needparse = $parseOnLoad;
            }
        }
        $needparse = $needparse ? true : false;
        
        return $needparse;
    }
    /**
     * Get the current CSS ref of the current action
     *
     * @return string[]
     */
    public function getCssRef()
    {
        if ($this->hasParent()) {
            return ($this->parent->GetCssRef());
        } else {
            return ($this->cssref);
        }
    }
    /**
     * Add a JS in an action
     *
     * Use this method to add a JS in an action that use the zone [JS:REF] and the template engine
     *
     * @api Add a JS in an action
     *
     * @param string $ref path to a js, or URL to a js, or js file name (if in the current application), or APP:jsfilename
     * @param bool $needparse if true will be parsed by the template engine (false by default)
     * @param string $packName use it to pack all the ref with the same packName into a single file
     *
     * @return string the path of the added ref or "" if the ref is not valid
     */
    public function addJsRef($ref, $needparse = false, $packName = '')
    {
        if (substr($ref, 0, 2) == './') $ref = substr($ref, 2);
        return $this->AddRessourceRef('js', $ref, $needparse, $packName);
    }
    /**
     * Get the js ref array of the current action
     *
     * @return string[] array of location
     */
    public function getJsRef()
    {
        if ($this->hasParent()) {
            return ($this->parent->GetJsRef());
        } else {
            return ($this->jsref);
        }
    }
    /**
     * Add a JS code in an action
     * Use this method to add a JS in an action that use the zone [JS:REF] and the template engine
     * (beware use protective ; because all the addJsCode are concatened)
     *
     * @api Add a JS code in an action
     *
     * @param string $code code to add
     *
     * @return void
     */
    public function addJsCode($code)
    {
        // Js Code are stored in the top level application
        if ($this->hasParent()) {
            $this->parent->AddJsCode($code);
        } else {
            $this->jscode[] = $code;
        }
    }
    /**
     * Get the js code of the current action
     *
     * @return string[]
     */
    public function getJsCode()
    {
        if ($this->hasParent()) {
            return ($this->parent->GetJsCode());
        } else {
            return ($this->jscode);
        }
    }
    /**
     * Add a CSS code in an action
     * Use this method to add a CSS in an action that use the zone [CSS:REF] and the template engine
     *
     * @api Add a CSS code in an action
     *
     * @param string $code code to add
     *
     * @return void
     */
    public function addCssCode($code)
    {
        // Css Code are stored in the top level application
        if ($this->hasParent()) {
            $this->parent->AddCssCode($code);
        } else {
            $this->csscode[] = $code;
        }
    }
    /**
     * Get the current CSS code of the current action
     *
     * @return string[]
     */
    public function getCssCode()
    {
        if ($this->hasParent()) {
            return ($this->parent->GetCssCode());
        } else {
            return ($this->csscode);
        }
    }
    /**
     * Add message to log (syslog)
     * The message is also displayed in the console of the web interface
     *
     * @param string $code message to add to log
     * @param int $cut truncate message longer than this length (set to <= 0 to not truncate the message)(default is 0).
     */
    public function addLogMsg($code, $cut = 0)
    {
        if ($code == "") return;
        // Js Code are stored in the top level application
        if ($this->hasParent()) {
            $this->parent->AddLogMsg($code, $cut);
        } else {
            if ($this->session) {
                $logmsg = $this->session->read("logmsg", array());
                if (is_array($code)) {
                    $code["stack"] = getDebugStack(4);
                    $logmsg[] = json_encode($code);
                } else {
                    $logmsg[] = strftime("%H:%M - ") . str_replace("\n", "\\n", (($cut > 0) ? mb_substr($code, 0, $cut) : $code));
                }
                $this->session->register("logmsg", $logmsg);
                $suser = sprintf("%s %s [%d] - ", $this->user->firstname, $this->user->lastname, $this->user->id);
                if (is_array($code)) $code = print_r($code, true);
                $this->log->info($suser . $code);
            } else {
                error_log($code);
            }
        }
    }
    /**
     * send a message to the user interface
     *
     * @param string $code message
     * @return void
     */
    public function addWarningMsg($code)
    {
        if (($code == "") || ($code == "-")) return;
        // Js Code are stored in the top level application
        if ($this->hasParent()) {
            $this->parent->addWarningMsg($code);
        } else {
            if (!empty($_SERVER['HTTP_HOST']) && $this->session) {
                $logmsg = $this->session->read("warningmsg", array());
                $logmsg[] = $code;
                $this->session->register("warningmsg", $logmsg);
            } else {
                error_log("dcp warning: $code");
            }
        }
    }
    /**
     * Get log text messages
     * @return array
     */
    public function getLogMsg()
    {
        return ($this->session ? ($this->session->read("logmsg", array())) : array());
    }
    
    public function clearLogMsg()
    {
        if ($this->session) {
            $this->session->unregister("logmsg");
        }
    }
    /**
     * Get warning texts
     * @return array
     */
    public function getWarningMsg()
    {
        return ($this->session ? ($this->session->read("warningmsg", array())) : array());
    }
    
    public function clearWarningMsg()
    {
        if ($this->session) {
            $this->session->unregister("warningmsg");
        }
    }
    /**
     * mark the application as launched from admin context
     *
     * @param bool $enable true to enable admin mode, false to disable it
     */
    public function setAdminMode($enable = true)
    {
        if ($this->hasParent()) {
            $this->parent->setAdminMode($enable);
        } else {
            $this->adminMode = ($enable ? true : false);
        }
    }
    /**
     * @return bool true if application is launched from admin context
     */
    public function isInAdminMode()
    {
        if ($this->hasParent()) {
            return $this->parent->isInAdminMode();
        }
        return $this->adminMode === true;
    }
    /**
     * Test permission for current user in current application
     *
     * @param string $acl_name acl name to test
     * @param string $app_name application if test for other application
     * @param bool $strict to not use substitute account information
     * @return bool true if permission granted
     */
    public function hasPermission($acl_name, $app_name = "", $strict = false)
    {
        if (Action::ACCESS_FREE == $acl_name) {
            return true;
        }
        if (!isset($this->user) || !is_object($this->user)) {
            $this->log->warning("Action {$this->parent->name}:{$this->name} requires authentification");
            return false;
        }
        if ($this->user->id == 1) return true; // admin can do everything
        if ($app_name == "") {
            
            $acl = new Acl($this->dbaccess);
            if (!$acl->Set($acl_name, $this->id)) {
                $this->log->warning("Acl $acl_name not available for App $this->name");
                return false;
            }
            if (!$this->permission) {
                $permission = new Permission($this->dbaccess, array(
                    $this->user->id,
                    $this->id
                ));
                if (!$permission->IsAffected()) { // case of no permission available
                    $permission->Affect(array(
                        "id_user" => $this->user->id,
                        "id_application" => $this->id
                    ));
                }
                $this->permission = & $permission;
            }
            
            return ($this->permission->HasPrivilege($acl->id, $strict));
        } else {
            // test permission for other application
            if (!is_numeric($app_name)) $appid = $this->GetIdFromName($app_name);
            else $appid = $app_name;
            
            $wperm = new Permission($this->dbaccess, array(
                $this->user->id,
                $appid
            ));
            if ($wperm->isAffected()) {
                $acl = new Acl($this->dbaccess);
                if (!$acl->Set($acl_name, $appid)) {
                    $this->log->warning("Acl $acl_name not available for App $this->name");
                    return false;
                } else {
                    return ($wperm->HasPrivilege($acl->id, $strict));
                }
            }
        }
        return false;
    }
    /**
     * create style parameters
     * @param bool $init
     * @param string $useStyle
     */
    public function initStyle($init = true, $useStyle = '')
    {
        if ($init == true) {
            if (isset($this->user)) $pstyle = new Param($this->dbaccess, array(
                "STYLE",
                Param::PARAM_USER . $this->user->id,
                "1"
            ));
            else $pstyle = new Param($this->dbaccess, array(
                "STYLE",
                Param::PARAM_USER . Account::ANONYMOUS_ID,
                "1"
            ));
            if (!$pstyle->isAffected()) $pstyle = new Param($this->dbaccess, array(
                "STYLE",
                Param::PARAM_APP,
                "1"
            ));
            
            $style = $pstyle->val;
            $this->style = new Style($this->dbaccess, $style);
            
            $this->style->Set($this);
        } else {
            $style = ($useStyle) ? $useStyle : $this->getParam("STYLE");
            $this->style = new Style($this->dbaccess, $style);
            
            $this->style->Set($this);
        }
        if ($style) {
            //  $this->AddCssRef("css/dcp/system.css");
            
            
        }
    }
    
    public function setLayoutVars($lay)
    {
        if ($this->hasParent()) {
            $this->parent->SetLayoutVars($lay);
        }
    }
    
    public function getRootApp()
    {
        if ($this->parent == "") {
            return ($this);
        } else {
            return ($this->parent->GetRootApp());
        }
    }
    
    public function getImageFile($img)
    {
        
        return $this->rootdir . "/" . $this->getImageLink($img);
    }
    
    var $noimage = "CORE/Images/core-noimage.png";
    /**
     * get image url of an application
     * can also get another image by search in Images general directory
     * @api get image url of an application
     * @param string $img image filename
     * @param bool $detectstyle to use theme image instead of original
     * @param int $size to use image with another width (in pixel) - null is original size
     * @return string url to download image
     */
    public function getImageLink($img, $detectstyle = true, $size = null)
    {
        static $cacheImgUrl = array();
        
        $cacheIndex = $img . $size;
        if (isset($cacheImgUrl[$cacheIndex])) return $cacheImgUrl[$cacheIndex];
        if ($img != "") {
            // try style first
            if ($detectstyle) {
                $url = $this->style->GetImageUrl($img, "");
                if ($url != "") {
                    if ($size !== null) $url = 'resizeimg.php?img=' . urlencode($url) . '&size=' . $size;
                    $cacheImgUrl[$cacheIndex] = $url;
                    return $url;
                }
            }
            // try application
            if (file_exists($this->rootdir . "/" . $this->name . "/Images/" . $img)) {
                $url = $this->name . "/Images/" . $img;
                if ($size !== null) $url = 'resizeimg.php?img=' . urlencode($url) . '&size=' . $size;
                $cacheImgUrl[$cacheIndex] = $url;
                return $url;
            } else { // perhaps generic application
                if (($this->childof != "") && (file_exists($this->rootdir . "/" . $this->childof . "/Images/" . $img))) {
                    $url = $this->childof . "/Images/" . $img;
                    if ($size !== null) $url = 'resizeimg.php?img=' . urlencode($url) . '&size=' . $size;
                    $cacheImgUrl[$cacheIndex] = $url;
                    return $url;
                } else if (file_exists($this->rootdir . "/Images/" . $img)) {
                    $url = "Images/" . $img;
                    if ($size !== null) $url = 'resizeimg.php?img=' . urlencode($url) . '&size=' . $size;
                    $cacheImgUrl[$cacheIndex] = $url;
                    return $url;
                }
            }
            // try in parent
            if ($this->parent != "") {
                $url = $this->parent->getImageLink($img);
                if ($size !== null) $url = 'resizeimg.php?img=' . urlencode($url) . '&size=' . $size;
                $cacheImgUrl[$cacheIndex] = $url;
                return $url;
            }
        }
        if ($size !== null) return 'resizeimg.php?img=' . urlencode($this->noimage) . '&size=' . $size;
        return $this->noimage;
    }
    /**
     * get image url of an application
     * can also get another image by search in Images general directory
     *
     * @see Application::getImageLink
     *
     * @deprecated use { @link Application::getImageLink } instead
     *
     * @param string $img image filename
     * @param bool $detectstyle to use theme image instead of original
     * @param int $size to use image with another width (in pixel) - null is original size
     * @return string url to download image
     */
    public function getImageUrl($img, $detectstyle = true, $size = null)
    {
        deprecatedFunction();
        return $this->getImageLink($img, $detectstyle, $size);
    }
    
    public function imageFilterColor($image, $fcol, $newcol, $out = null)
    {
        if ($out === null) {
            $out = getTmpDir() . "/i.gif";
        }
        $im = imagecreatefromgif($image);
        $idx = imagecolorexact($im, $fcol[0], $fcol[1], $fcol[2]);
        imagecolorset($im, $idx, $newcol[0], $newcol[1], $newcol[2]);
        imagegif($im, $out);
        imagedestroy($im);
    }
    
    public function getFilteredImageUrl($imgf)
    {
        
        $ttf = explode(":", $imgf);
        $img = $ttf[0];
        $filter = $ttf[1];
        
        $url = $this->getImageLink($img);
        if ($url == $this->noimage) return $url;
        
        $tf = explode("|", $filter);
        if (count($tf) != 2) return $url;
        
        $fcol = explode(",", $tf[0]);
        if (count($fcol) != 3) return $url;
        
        if (substr($tf[1], 0, 1) == '#') $col = $tf[1];
        else $col = $this->getParam($tf[1]);
        $ncol[0] = hexdec(substr($col, 1, 2));
        $ncol[1] = hexdec(substr($col, 3, 2));
        $ncol[2] = hexdec(substr($col, 5, 2));
        
        $cdir = 'var/cache/image/';
        $rcdir = $this->rootdir . '/' . $cdir;
        if (!is_dir($rcdir)) mkdir($rcdir);
        
        $uimg = $cdir . $this->name . '-' . $fcol[0] . '.' . $fcol[1] . '.' . $fcol[2] . '_' . $ncol[0] . '.' . $ncol[1] . '.' . $ncol[2] . '.' . $img;
        $cimg = $this->rootdir . '/' . $uimg;
        if (file_exists($cimg)) return $uimg;
        
        $this->ImageFilterColor($this->rootdir . '/' . $url, $fcol, $ncol, $cimg);
        return $uimg;
    }
    /**
     * get file path layout from layout name
     * @param string $layname
     * @return string file path
     */
    public function getLayoutFile($layname)
    {
        if (strstr($layname, '..')) {
            return ""; // not authorized
            
        }
        $file = $this->style->GetLayoutFile($layname, "");
        if ($file != "") return $file;
        
        $laydir = $this->rootdir . "/" . $this->name . "/Layout/";
        $file = $laydir . $layname; // default file
        if (file_exists($file)) {
            return ($file);
        } else {
            // perhaps generic application
            $file = $this->rootdir . "/" . $this->childof . "/Layout/$layname";
            if (file_exists($file)) return ($file);
        }
        if ($this->parent != "") return ($this->parent->GetLayoutFile($layname));
        return ("");
    }
    public function OldGetLayoutFile($layname)
    {
        $file = $this->rootdir . "/" . $this->name . "/Layout/" . $layname;
        if (file_exists($file)) {
            $file = $this->style->GetLayoutFile($layname, $file);
            return ($file);
        }
        if ($this->parent != "") return ($this->parent->GetLayoutFile($layname));
        return ("");
    }
    /**
     * affect new value to an application parameter
     * @see ParameterManager to easily manage application parameters
     * @param string $key parameter id
     * @param string $val parameter value
     */
    public function setParam($key, $val)
    {
        if (is_array($val)) {
            if (isset($val["global"]) && $val["global"] == "Y") $type = Param::PARAM_GLB;
            else $type = Param::PARAM_APP;
            $this->param->Set($key, $val["val"], $type, $this->id);
        } else { // old method
            $this->param->Set($key, $val, Param::PARAM_APP, $this->id);
        }
    }
    /**
     * set user parameter for current user
     *
     * @see ParameterManager to easily manage application parameters
     * @param string $key parameter identifier
     * @param string $val value
     * @return string error message
     */
    public function setParamU($key, $val)
    {
        return $this->param->Set($key, $val, Param::PARAM_USER . $this->user->id, $this->id);
    }
    /**
     * declare new application parameter
     * @param string $key
     * @param array $val
     */
    public function setParamDef($key, $val)
    {
        // add new param definition
        $pdef = ParamDef::getParamDef($key, $this->id);
        
        $oldValues = array();
        if (!$pdef) {
            $pdef = new ParamDef($this->dbaccess);
            $pdef->name = $key;
            $pdef->isuser = "N";
            $pdef->isstyle = "N";
            $pdef->isglob = "N";
            $pdef->appid = $this->id;
            $pdef->descr = "";
            $pdef->kind = "text";
        } else {
            $oldValues = $pdef->getValues();
        }
        
        if (is_array($val)) {
            if (isset($val["kind"])) $pdef->kind = $val["kind"];
            if (isset($val["user"]) && $val["user"] == "Y") $pdef->isuser = "Y";
            else $pdef->isuser = "N";
            if (isset($val["style"]) && $val["style"] == "Y") $pdef->isstyle = "Y";
            else $pdef->isstyle = "N";
            if (isset($val["descr"])) $pdef->descr = $val["descr"];
            if (isset($val["global"]) && $val["global"] == "Y") $pdef->isglob = "Y";
            else $pdef->isglob = "N";
        }
        
        if ($pdef->appid == $this->id) {
            if ($pdef->isAffected()) {
                $pdef->Modify();
                // migrate paramv values in case of type changes
                $newValues = $pdef->getValues();
                if ($oldValues['isglob'] != $newValues['isglob']) {
                    $ptype = $oldValues['isglob'] == 'Y' ? Param::PARAM_GLB : Param::PARAM_APP;
                    $ptypeNew = $newValues['isglob'] == 'Y' ? Param::PARAM_GLB : Param::PARAM_APP;
                    $pv = new Param($this->dbaccess, array(
                        $pdef->name,
                        $ptype,
                        $pdef->appid
                    ));
                    if ($pv->isAffected()) {
                        $pv->set($pv->name, $pv->val, $ptypeNew, $pv->appid);
                    }
                }
            } else {
                $pdef->Add();
            }
        }
    }
    /**
     * Add temporary parameter to ths application
     * Can be use to transmit global variable or to affect Layout
     *
     * @param string $key
     * @param string $val
     */
    public function setVolatileParam($key, $val)
    {
        if ($this->hasParent()) $this->parent->setVolatileParam($key, $val);
        else $this->param->SetVolatile($key, $val);
    }
    /**
     * get parameter value
     * @param string $key
     * @param string $default value if not set
     * @return string
     */
    public function getParam($key, $default = "")
    {
        if (!isset($this->param)) return ($default);
        $z = $this->param->Get($key, "z");
        
        if ($z === "z") {
            if ($this->hasParent()) return $this->parent->GetParam($key, $default);
        } else {
            return ($z);
        }
        return ($default);
    }
    /**
     * create/update application parameter definition
     * @param array $tparam all parameter definition
     * @param bool $update
     */
    public function initAllParam($tparam, $update = false)
    {
        if (is_array($tparam)) {
            reset($tparam);
            foreach ($tparam as $k => $v) {
                $this->SetParamDef($k, $v); // update definition
                if ($update) {
                    // don't modify old parameters
                    if ($this->param && $this->param->Get($k, null) === null) {
                        // set only new parameters or static variable like VERSION
                        $this->SetParam($k, $v);
                    }
                } else {
                    $this->SetParam($k, $v);
                }
            }
        }
    }
    /**
     * get all parameters values indexed by name
     * @return array all paramters values
     */
    public function getAllParam()
    {
        $list = $this->param->buffer;
        if ($this->hasParent()) {
            $list2 = $this->parent->GetAllParam();
            $list = array_merge($this->param->buffer, $list2);
        }
        
        return ($list);
    }
    /**
     * initialize application description
     * from .app and _init.php configuration files
     * @param string $name application name reference
     * @param bool $update set to true when update application
     * @return bool true if init is done, false if error
     */
    public function initApp($name, $update = false)
    {
        
        $this->log->info("Init : $name");
        if (file_exists($this->rootdir . "/{$name}/{$name}.app")) {
            global $app_desc, $app_acl, $action_desc;
            // init global array
            $app_acl = array();
            $app_desc = array();
            $action_desc = array();
            include ("{$name}/{$name}.app");
            $action_desc_ini = $action_desc;
            if (sizeof($app_desc) > 0) {
                if (!$update) {
                    $this->log->debug("InitApp :  new application ");
                }
                if ($update) {
                    foreach ($app_desc as $k => $v) {
                        switch ($k) {
                            case 'displayable':
                            case 'available':
                                break;

                            default:
                                $this->$k = $v;
                        }
                    }
                    $this->Modify();
                } else {
                    $this->available = "Y";
                    foreach ($app_desc as $k => $v) {
                        $this->$k = $v;
                    }
                    if ($this->isAffected()) {
                        $this->modify();
                    } else {
                        $this->Add();
                    }
                    $this->param = new Param();
                    $this->param->SetKey($this->id, isset($this->user->id) ? $this->user->id : Account::ANONYMOUS_ID);
                }
            } else {
                $this->log->info("can't init $name");
                return false;
            }
            
            $action_desc = $action_desc_ini;
            // init acl
            $acl = new Acl($this->dbaccess);
            $acl->Init($this, $app_acl, $update);
            // init actions
            $action = new Action($this->dbaccess);
            $action->Init($this, $action_desc, $update);
            // init father if has
            if ($this->childof != "") {
                // init ACL & ACTION
                // init acl
                simpleQuery($this->dbaccess, sprintf("INSERT INTO acl (id,id_application,name,grant_level,description, group_default) SELECT nextval('seq_id_acl') as id, %d as id_application, acl.name, acl.grant_level, acl.description, acl.group_default from acl as acl,application as app where acl.id_application=app.id and app.name='%s' and acl.name NOT IN (SELECT acl.name from acl as acl, application as app  where id_application=app.id and app.name='%s')", $this->id, pg_escape_string($this->childof) , pg_escape_string($this->name)));
                // init actions
                simpleQuery($this->dbaccess, sprintf("INSERT INTO action (id, id_application, name, short_name, long_name,script,function,layout,available,acl,grant_level,openaccess,root,icon,toc,father,toc_order) SELECT nextval('seq_id_action') as id, %d as id_application, action.name, action.short_name, action.long_name, action.script, action.function, action.layout, action.available, action.acl, action.grant_level, action.openaccess, action.root, action.icon, action.toc, action.father, action.toc_order from action as action,application as app where action.id_application=app.id and app.name='%s' and action.name NOT IN (SELECT action.name from action as action, application as app  where action.id_application=app.id and app.name='%s')", $this->id, pg_escape_string($this->childof) , pg_escape_string($this->name)));
                $this->log->info(sprintf("Update Actions from %s parent", $this->childof));
                $err = $this->_initACLWithGroupDefault();
                if ($err != '') {
                    return false;
                }
            }
            //----------------------------------
            // init application constant
            if (file_exists($this->rootdir . "/{$name}/{$name}_init.php")) {
                include ("{$name}/{$name}_init.php");
                if ($update) {
                    /* Store previous version for post migration scripts */
                    global $app_const;
                    $nextVersion = isset($app_const['VERSION']) ? $app_const['VERSION'] : '';
                    if ($nextVersion != '') {
                        $currentVersion = $this->getParam('VERSION', '');
                        if ($currentVersion != '' && $nextVersion != $currentVersion) {
                            $this->setParam('PREVIOUS_VERSION', array(
                                'val' => $currentVersion,
                                'kind' => 'static'
                            ));
                        }
                    }
                }
                if ($this->param) {
                    // delete paramters that cannot be change after initialisation to be change now
                    if ($update) $this->param->DelStatic($this->id);
                    global $app_const;
                    if (isset($app_const)) $this->InitAllParam($app_const, $update);
                }
            }
            //----------------------------------
            // add init father application constant
            if (file_exists($this->rootdir . "/{$this->childof}/{$this->childof}_init.php")) {
                include ("{$this->childof}/{$this->childof}_init.php");
                global $app_const;
                $this->InitAllParam(array_filter($app_const, "f_paramglog") , true);
            }
            
            if ($this->id > 1) {
                $this->SetParamDef("APPNAME", array(
                    "descr" => "$name application",
                    "val" => $name,
                    "kind" => "static"
                )); // use by generic application
                $this->SetParam("APPNAME", array(
                    "val" => $name,
                    "kind" => "static"
                )); // use by generic application
                
            }
            $this->updateChildApplications();
        } else {
            $this->log->info("No {$name}/{$name}.app available");
            return false;
        }
        return true;
    }
    /**
     * update action/acl/param for application's childs
     * @throws Dcp\Exception|Exception
     */
    private function updateChildApplications()
    {
        $sql = sprintf("select id, name from application where childof ='%s'", pg_escape_string($this->name));
        
        simpleQuery($this->dbaccess, $sql, $childIds);
        foreach ($childIds as $childApp) {
            $childId = $childApp["id"];
            $childName = $childApp["name"];
            $a = new Application($this->dbaccess, $childId);
            
            if ($a->isAffected()) {
                try {
                    $a->set($childName, $noParent);
                    $a->initApp($childName, true);
                }
                catch(\Dcp\Exception $e) {
                    if ($e->getDcpCode() != "CORE0007") {
                        throw $e;
                    }
                }
            }
        }
    }
    /**
     * update application description
     * from .app and _init.php configuration files
     */
    public function updateApp()
    {
        $name = $this->name;
        $this->InitApp($name, true);
    }
    /**
     * Update All available application
     * @see updateApp
     */
    public function updateAllApp()
    {
        
        $query = new QueryDb($this->dbaccess, $this->dbtable);
        $query->AddQuery("available = 'Y'");
        $allapp = $query->Query();
        
        foreach ($allapp as $app) {
            $application = new Application($this->dbaccess, $app->id);
            
            $application->Set($app->name, $this->parent);
            $application->UpdateApp();
        }
    }
    /**
     * delete application
     * database application reference are destroyed but application files are not removed from server
     * @return string
     */
    public function deleteApp()
    {
        // delete acl
        $acl = new Acl($this->dbaccess);
        $acl->DelAppAcl($this->id);
        // delete actions
        $this->log->debug("Delete {$this->name}");
        $query = new QueryDb("", "Action");
        $query->basic_elem->sup_where = array(
            "id_application = {$this->id}"
        );
        $list = $query->Query();
        
        if ($query->nb > 0) {
            /**
             * @var Action $v
             */
            foreach ($list as $v) {
                $this->log->debug(" Delete action {$v->name} ");
                $err = $v->Delete();
                if ($err != '') {
                    return $err;
                }
            }
        }
        unset($query);
        
        unset($list);
        // delete params
        $param = new Param($this->dbaccess);
        $param->DelAll($this->id);
        // delete application
        $err = $this->Delete();
        return $err;
    }
    /**
     * translate text
     * use gettext catalog
     *
     * @param string $code text to translate
     * @return string
     */
    public static function text($code)
    {
        if ($code == "") return "";
        return _($code);
    }
    /**
     * Write default ACL when new user is created
     * @TODO not used - to remove
     * @param int $iduser
     * @throws \Dcp\Db\Exception
     */
    public function updateUserAcl($iduser)
    {
        
        $query = new QueryDb($this->dbaccess, $this->dbtable);
        $query->AddQuery("available = 'Y'");
        $allapp = $query->Query();
        $acl = new Acl($this->dbaccess);
        
        foreach ($allapp as $v) {
            $permission = new Permission($this->dbaccess);
            $permission->id_user = $iduser;
            $permission->id_application = $v->id;
            
            $privileges = $acl->getDefaultAcls($v->id);
            
            foreach ($privileges as $aclid) {
                $permission->id_acl = $aclid;
                if (($permission->id_acl > 0) && (!$permission->Exists($permission->id_user, $v->id))) {
                    $permission->Add();
                }
            }
        }
    }
    /**
     * return id from name for an application
     * @param string $name
     * @return int (0 if not found)
     */
    public function getIdFromName($name)
    {
        $query = new QueryDb($this->dbaccess, $this->dbtable);
        $query->AddQuery("name = '" . pg_escape_string(trim($name)) . "'");
        $app = $query->Query(0, 0, "TABLE");
        if (is_array($app) && isset($app[0]) && isset($app[0]["id"])) return $app[0]["id"];
        return 0;
    }
    /**
     * verify if application object has parent application
     * @return bool
     */
    public function hasParent()
    {
        return (is_object($this->parent) && ($this->parent !== $this));
    }
    /**
     * Initialize ACLs with group_default='Y'
     */
    private function _initACLWithGroupDefault()
    {
        $res = array();
        try {
            simpleQuery($this->dbaccess, sprintf("SELECT * FROM acl WHERE id_application = %s AND group_default = 'Y'", $this->id) , $res, false, false, true);
        }
        catch(Exception $e) {
            return $e->getMessage();
        }
        foreach ($res as $acl) {
            $permission = new Permission($this->dbaccess);
            if ($permission->Exists(Account::GALL_ID, $this->id, $acl['id'])) {
                continue;
            }
            $permission->Affect(array(
                'id_user' => Account::GALL_ID,
                'id_application' => $this->id,
                'id_acl' => $acl['id']
            ));
            $err = $permission->Add();
            if ($err != '') {
                return $err;
            }
        }
        return '';
    }
}
