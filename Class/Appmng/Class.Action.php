<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Action class
 *
 * @author Anakeen
 * @version $Id: Class.Action.php,v 1.40 2008/03/10 15:09:17 eric Exp $
 * @package FDL
 * @subpackage CORE
 */

require_once ('WHAT/autoload.php');
include_once ("FDL/Lib.Util.php");

define("THROW_EXITERROR", 1968);
/**
 * manage Action
 * Action is part of Application
 * @see Application
 */
class Action extends DbObj
{
    /**
     * fake ACL to allow an action to be access free without its application being access_free
     */
    const ACCESS_FREE = "";
    var $fields = array(
        "id",
        "id_application",
        "name",
        "short_name",
        "long_name",
        "script",
        "function",
        "layout",
        "available",
        "acl",
        "grant_level",
        "openaccess",
        "root",
        "icon",
        "toc",
        "father",
        "toc_order"
    );
    public $id;
    public $id_application;
    public $name;
    public $short_name;
    public $long_name;
    public $script;
    /**
     * @var string
     */
    public $function;
    public $layout;
    public $available;
    public $acl;
    public $grant_level = 0;
    public $openaccess;
    public $root;
    public $icon;
    public $toc;
    public $father;
    public $toc_order;
    
    var $id_fields = array(
        "id"
    );
    
    var $idx = array(
        "id",
        "id_application",
        "name"
    );
    
    var $dbtable = "action";
    
    var $sqlcreate = '
create table action (id int not null,
                   primary key (id),
                   id_application int not null,
                   name text not null,
                   short_name text ,
                   long_name text, 
                   script text,
                   function text,
                   layout text ,
                   available char,
                   acl text,
                   grant_level int,
                   openaccess  char,
                   root char,
                   icon text,
                   toc  char,
                   father int ,
                   toc_order int);
create index action_idx1 on action(id);
create index action_idx2 on action(id_application);
create index action_idx3 on action(name);
create sequence SEQ_ID_ACTION;
                 ';
    /**
     * @var Application
     */
    public $parent;
    
    var $def = array(
        "criteria" => "",
        "order_by" => "name"
    );
    
    var $criterias = array(
        "name" => array(
            "libelle" => "Nom",
            "type" => "TXT"
        )
    );
    /**
     * current user
     * @var Account
     */
    public $user;
    /**
     * current session
     * @var Session
     */
    public $session;
    /**
     * @var string url to access action
     */
    public $url;
    /**
     * @var Authenticator|openAuthenticator
     */
    public $auth;
    /**
     * @var int inheritance level
     */
    public $level;
    /**
     * @var Layout
     */
    public $lay;
    /**
     * initialize Action object
     * need set action to execute it
     *
     * @code
     $core = new Application();
     $core->Set("CORE", $CoreNull); // init core application from nothing
     $core->session = new Session();
     $core->session->set();
     $one = new Application();
     $one->set("ONEFAM", $core, $core->session);// init ONEFAM application from core
     $myAct=new Action();
     $myAct->set("ONEFAM_LIST", $one);
     print $myAct->execute();
     *
     * @endcode
     * @param string $name action name reference
     * @param Application $parent application object where action depends
     * @throws Dcp\Core\Exception if action not exists
     */
    public function Set($name, &$parent)
    {
        $this->script = "";
        $this->layout = "";
        $this->function = "";
        $query = new QueryDb($this->dbaccess, "Action", "TABLE");
        if ($name != "") {
            $name = pg_escape_string($name);
            $query->basic_elem->sup_where = array(
                "name='$name'",
                "id_application={$parent->id}"
            );
        } else {
            $query->basic_elem->sup_where = array(
                "root='Y'",
                "id_application={$parent->id}"
            );
        }
        $query->Query(0, 0, "TABLE");
        if ($query->nb > 0) {
            $this->Affect($query->list[0]);
            $this->log->debug("Set Action to {$this->name}");
        } else {
            $e = new Dcp\Core\Exception("CORE0005", $name, $parent->name, $parent->id);
            $e->addHttpHeader('HTTP/1.0 404 Action not found');
            throw $e;
        }
        
        $this->CompleteSet($parent);
    }
    /**
     * add Application parent
     * @param Application $parent
     * @return string
     */
    public function completeSet(&$parent)
    {
        $this->parent = & $parent;
        if ($this->script == "") $this->script = strtolower($this->name) . ".php";
        if ($this->layout == "") $this->layout = strtolower($this->name) . ".xml";
        if ($this->function == "") $this->function = substr($this->script, 0, strpos($this->script, '.php'));
        
        $this->session = & $parent->session;
        
        $this->user = & $parent->user;
        // Set the hereurl if possible
        $this->url = $this->GetParam("CORE_BASEURL") . "app=" . $this->parent->name . "&action=" . $this->name;
        // Init a log attribute
        if ($this->user) $this->log->loghead = sprintf("%s %s [%d] - ", $this->user->firstname, $this->user->lastname, $this->user->id);
        else $this->log->loghead = "user not defined - ";
        
        $this->log->function = $this->name;
        $this->log->application = $this->parent->name;
        return "";
    }
    
    public function complete()
    {
    }
    /**
     * read a session variable
     *
     * @param string $k key variable
     * @param string $d default value
     * @return string
     */
    public function Read($k, $d = "")
    {
        if (is_object($this->session)) {
            return ($this->session->Read($k, $d));
        }
        return ($d . "--");
    }
    /**
     * record a session variable
     *
     * @param  string $k key variable
     * @param string $v value to set
     * @return bool return true if ok
     */
    public function Register($k, $v)
    {
        if (isset($this->session) && is_object($this->session)) {
            return ($this->session->Register($k, $v));
        }
        return false;
    }
    /**
     * remove variable from current session
     *
     * @param string $k key variable
     * @return bool return true if ok
     */
    public function Unregister($k)
    {
        if (is_object($this->session)) {
            return ($this->session->Unregister($k));
        }
        return false;
    }
    
    public function actRead($k, $d = "")
    {
        return ($this->Read("{$this->id}_" . $k, $d));
    }
    
    public function actRegister($k, $v)
    {
        return ($this->Register("{$this->id}_" . $k, $v));
    }
    
    public function actUnregister($k)
    {
        return ($this->Unregister("{$this->id}_" . $k));
    }
    
    public function PreInsert()
    {
        if ($this->Exists($this->name, $this->id_application)) return "Action {$this->name} already exists...";
        $this->exec_query("select nextval ('seq_id_action')");
        $arr = $this->fetch_array(0);
        $this->id = $arr["nextval"];
        return '';
    }
    public function PreUpdate()
    {
        if ($this->dbid == - 1) return false;
        if ($this->Exists($this->name, $this->id_application, $this->id)) return "Action {$this->name} already exists...";
        return '';
    }
    /**
     * get parameter value of action'sapplication
     * shorcut to Application::getParam
     *
     * @param string $name
     * @param string $def default value if not set
     * @return string
     */
    public function getParam($name, $def = "")
    {
        if (isset($this->parent)) {
            return ($this->parent->GetParam($name, $def));
        }
        return $def;
    }
    /**
     * set a new value for a user parameter
     * @see ParameterManager::setUserApplicationParameter
     * @param string $name parameter key
     * @param string $val new value for the parameter
     * @return string error message if not succeed else empty string
     */
    public function setParamU($name, $val)
    {
        if (isset($this->parent)) {
            return ($this->parent->setParamU($name, $val));
        }
        return '';
    }
    /**
     * get image url of an application
     * shorcut to Application::getImageUrl
     *
     * @see Application::getImageLink
     *
     * @deprecated use { @link Application::getImageLink } instead
     *
     * @param string $name image filename
     * @param bool $detectstyle to use theme image instead of original
     * @param int $size to use image with another width (in pixel) - null is original size
     * @return string url to download image
     */
    public function getImageUrl($name, $detectstyle = true, $size = null)
    {
        deprecatedFunction();
        if (isset($this->parent)) {
            return ($this->parent->getImageLink($name, $detectstyle, $size));
        }
        return '';
    }
    
    public function getFilteredImageUrl($name)
    {
        if (isset($this->parent)) {
            return ($this->parent->GetFilteredImageUrl($name));
        }
        return '';
    }
    
    public function getImageFile($name)
    {
        if (isset($this->parent)) {
            return ($this->parent->GetImageFile($name));
        }
        return '';
    }
    
    public function addLogMsg($msg, $cut = 0)
    {
        if (isset($this->parent)) {
            $this->parent->AddLogMsg($msg, $cut);
        }
    }
    
    public function addWarningMsg($msg)
    {
        if (isset($this->parent)) {
            $this->parent->AddWarningMsg($msg);
        }
        return '';
    }
    /**
     * store action done to be use in refreshing main window interface
     *
     * @param string $actdone the code of action
     * @param string $arg the argument of action
     */
    public function addActionDone($actdone, $arg = "")
    {
        if ($actdone != "") {
            $sact = $this->session->read("actdone_name", array());
            $sarg = $this->session->read("actdone_arg", array());
            $sact[] = $actdone;
            $sarg[] = $arg;
            $this->session->register("actdone_name", $sact);
            $this->session->register("actdone_arg", $sarg);
        }
    }
    /**
     * clear action done to be use in refreshing main window interface
     */
    public function clearActionDone()
    {
        $this->session->unregister("actdone_name");
        $this->session->unregister("actdone_arg");
    }
    /**
     * get action code and argument for action code done
     * to be use in refreshing main window interface
     * @param string &$actdone the code of action
     * @param string &$arg the argument of action
     */
    public function getActionDone(&$actdone, &$arg)
    {
        $actdone = $this->session->read("actdone_name", array());
        $arg = $this->session->read("actdone_arg", array());
    }
    /**
     * get image HTML fragment
     * @param string  $name icon filename
     * @param string $text alternative text
     * @param string $width icon width
     * @param string $height icon Height
     * @return string HTML fragment image tag
     */
    public function getIcon($name, $text, $width = "", $height = "")
    {
        
        if ($width != "") $width = "width = \"" . $width . "\"";
        if ($height != "") $height = "height = \"" . $height . "\"";
        
        return ("<img border=0 " . $width . " " . $height . " src=\"" . $this->parent->getImageLink($name) . "\" title=\"" . $this->text($text) . "\" alt=\"" . $this->text($text) . "\">");
    }
    /**
     * get file path layout from layout name
     * @see Application::getLayoutFile
     * @param $layname
     * @return string
     */
    public function getLayoutFile($layname)
    {
        if (isset($this->parent)) return ($this->parent->GetLayoutFile($layname));
        return '';
    }
    /**
     * Verify if action exists
     * @param string $name action name
     * @param int $idapp application numeric identifier
     * @param int $id_func action identifier - when test itself @ internal purpose
     * @return bool true if exists
     */
    public function exists($name, $idapp, $id_func = 0)
    {
        if ($idapp == '') return false;
        $query = new QueryDb($this->dbaccess, "Action");
        
        if ($id_func != '') {
            
            $query->AddQuery(sprintf("name='%s' and id != %d and id_application=%d", pg_escape_string($name) , $id_func, $idapp));
        } else {
            $query->AddQuery(sprintf("name='%s' and id_application=%d", pg_escape_string($name) , $idapp));
        }
        
        $query->Query();
        return ($query->nb > 0);
    }
    /**
     * Verify acl grant for current user
     *
     * @param string $acl_name acl name
     * @param string $app_name app name to specify another appname (else current app name)
     * @param bool $strict to not use substitute account information
     * @return bool true if current user has acl privilege
     */
    public function hasPermission($acl_name = "", $app_name = "", $strict = false)
    {
        if (self::ACCESS_FREE == $acl_name) return (true); // no control for this action
        return ($this->parent->HasPermission($acl_name, $app_name, $strict));
    }
    /** 
     * Check if the current user can execute the specified action.
     * @api verify if an action can be executed
     * @param string $actname action name
     * @param string $appid application name or application id (default is the current application)
     * @return string with error message if the user cannot execute the given action, or an empty string if the user can execute the action
     *
     */
    public function canExecute($actname, $appid = "")
    {
        
        if ($this->user->id == 1) return "";
        if ($appid == "") $appid = $this->parent->id;
        elseif (!is_numeric($appid)) $appid = $this->parent->GetIdFromName($appid);
        
        $aclname = $this->getAcl($actname, $appid);
        if (!$aclname) return ""; // no control
        $acl = new Acl($this->dbaccess);
        if (!$acl->Set($aclname, $appid)) {
            return sprintf(_("Acl [%s] not available for App %s") , $aclname, $appid);
        }
        $p = new Permission($this->dbaccess, array(
            $this->user->id,
            $appid
        ));
        if (!$p->HasPrivilege($acl->id)) return sprintf("no privilege %s for %s %s", $aclname, $appid, $actname);
        return "";
    }
    /**
     * return acl name for an action
     * @param string $actname action name
     * @param string $appid application id (default itself)
     * @return string (false if not found)
     */
    public function getAcl($actname, $appid = "")
    {
        if ($appid == "") $appid = $this->parent->id;
        $query = new QueryDb($this->dbaccess, $this->dbtable);
        $query->AddQuery("name = '$actname'");
        $query->AddQuery("id_application = $appid");
        $q = $query->Query(0, 0, "TABLE");
        if (is_array($q)) return $q[0]["acl"];
        return false;
    }
    /**
     * execute the action
     * test if current user can execute it
     *
     *
     * @throws Dcp\Core\Exception
     * @throws Dcp\Exception
     *
     * @return string the composed associated layout
     */
    public function execute()
    {
        // If no parent set , it's a misconfiguration
        if (!isset($this->parent)) return '';
        
        if ($this->auth && $this->auth->parms["type"] === "open") {
            if ($this->openaccess !== 'Y') {
                $this->exitForbidden(sprintf(_("action %s is not declared to be access in open mode") , $this->name));
            }
        }
        
        if ($this->available == "N") {
            $e = new Dcp\Core\Exception("CORE0008", $this->name, $this->parent->name);
            $e->addHttpHeader('HTTP/1.0 503 Action unavalaible');
            throw $e;
        }
        // check if we are in an admin application and user can execute it
        $appTag = $this->parent->tag;
        if (preg_match('/(\W|\A)ADMIN(\W|\Z)/i', $appTag)) {
            if (!$this->parent->isInAdminMode()) {
                $e = new Dcp\Exception("CORE0009", $this->short_name, $this->name, $this->parent->name, $this->parent->short_name);
                $e->addHttpHeader('HTTP/1.0 503 Action forbidden');
                throw $e;
            }
        }
        // check if this action is permitted
        if (!$this->HasPermission($this->acl)) {
            $e = new Dcp\Exception("CORE0006", $this->short_name, $this->name, $this->acl, $this->user->login);
            $e->addHttpHeader('HTTP/1.0 503 Action forbidden');
            throw $e;
        }
        
        if ($this->id > 0) {
            global $QUERY_STRING;
            $this->log->info("{$this->parent->name}:{$this->name} [" . substr($QUERY_STRING, 48) . "]");
        }
        
        $this->log->push("{$this->parent->name}:{$this->name}");
        $pubdir = DEFAULT_PUBDIR;
        if ($this->layout != "") {
            $layout = $this->GetLayoutFile($this->layout);
        } else {
            $layout = "";
        }
        $this->lay = new Layout($layout, $this);
        if (isset($this->script) && $this->script != "") {
            $script = $pubdir . "/" . $this->parent->name . "/" . $this->script;
            if (!file_exists($script)) // try generic application
            $script = $pubdir . "/" . $this->parent->childof . "/" . $this->script;
            
            if (file_exists($script)) {
                include_once ($script);
                $call = $this->function;
                $call($this);
            } else {
                $this->log->debug("$script does not exist");
            }
        } else {
            $this->log->debug("No script provided : No script called");
        }
        // Is there any error messages
        $err = $this->Read($this->parent->name . "_ERROR", "");
        if ($err != "") {
            $this->lay->Set("ERR_MSG", $err);
            $this->Unregister($this->parent->name . "_ERROR");
        } else {
            $this->lay->Set("ERR_MSG", "");
        }
        
        $out = $this->lay->gen();
        $this->log->pop();
        
        return ($out);
    }
    /**
     * display error to user and stop execution
     * @param string $texterr the error message
     * @param bool $exit if false , no exit are pêrformed
     * @throws Dcp\Core\Exception
     * @api abort action execution
     * @return void
     */
    public function exitError($texterr, $exit = true)
    {
        if (!empty($_SERVER['HTTP_HOST'])) {
            //      redirect($this,"CORE&sole=Y","ERROR");
            $this->lay = new Layout("CORE/Layout/error.xml", $this);
            $this->lay->set("TITLE", _("Error"));
            $this->lay->set("error", cleanhtmljs(nl2br($texterr)));
            $this->lay->set("serror", json_encode(cleanhtmljs($texterr) , JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP));
            $this->lay->set("appname", (empty($this->parent)) ? '' : $this->parent->name);
            $this->lay->set("appact", $this->name);
            if ($this->parent && $this->parent->parent) { // reset js ans ccs
                $this->parent->parent->cssref = array();
                $this->parent->parent->jsref = array();
            }
            header('Warning: ' . strtok($texterr, "\n"));
            print $this->lay->gen();
            if ($exit) {
                exit;
            }
        } else {
            throw new Dcp\Core\Exception("CORE0001", $texterr);
        }
    }
    
    public function exitForbidden($texterr)
    {
        if (php_sapi_name() !== 'cli') {
            header("HTTP/1.0 403 Forbidden");
            print ErrorCode::getError("CORE0012", $texterr);
            exit;
        } else {
            error_log(sprintf("Forbidden: %s\n", $texterr));
            throw new Dcp\Core\Exception("CORE0012", $texterr);
        }
    }
    /**
     * unregister FT error
     */
    public function clearError()
    {
        $this->Unregister("FT_ERROR");
        $this->Unregister("FT_ERROR_ACT");
    }
    /**
     * record/update action
     * @param Application $app application
     * @param array $action_desc action description
     * @param bool $update set to true if update only
     * @return string none
     */
    public function Init($app, $action_desc, $update = false)
    {
        if (sizeof($action_desc) == 0) {
            $this->log->info("No action available");
            return ("");
        }
        $father[0] = "";
        
        foreach ($action_desc as $k => $node) {
            // set some default values
            $action = new Action($this->dbaccess);
            $action->root = "N";
            $action->available = "Y";
            $action->id_application = $app->id;
            $action->toc = "N";
            // If the action already exists ,set it
            if ($action->Exists($node["name"], $app->id)) {
                $action->Set($node["name"], $app);
                foreach ($node as $k => $v) {
                    if ($k == 'available' && $update) {
                        continue;
                    }
                    $action->$k = $v;
                }
                reset($node);
            } else {
                foreach ($node as $k => $v) {
                    $action->$k = $v;
                }
                reset($node);
            }
            // Get the acl grant level
            $acl = new Acl($this->dbaccess);
            if (isset($action->acl)) {
                $acl->Set($action->acl, $action->id_application);
                $action->grant_level = $acl->grant_level;
            } else {
                $action->grant_level = 0;
            }
            // set non set values if possible
            if ($action->long_name == "") $action->long_name = $action->short_name;
            if ($action->script == "") $action->script = strtolower($action->name) . ".php";
            if ($action->layout == "") $action->layout = strtolower($action->name) . ".xml";
            if (!isset($action->level)) $action->level = 0;
            
            $action->father = $father[$action->level];
            if ($action->Exists($node["name"], $app->id)) {
                $this->log->info("Update Action " . $node["name"]);
                $action->Modify();
            } else {
                $action->Add();
                $this->log->info("Create Action " . $node["name"]);
            }
            $father[$action->level + 1] = $action->id;
        }
        // if update , remove unused actions
        if ($update) {
            $query = new QueryDb($this->dbaccess, "Action");
            $query->basic_elem->sup_where = array(
                "id_application = {$app->id}"
            );
            $list = $query->Query();
            foreach ($list as $k => $act) {
                /**
                 * @var Action $act
                 */
                $find = false;
                reset($action_desc);
                /** @noinspection PhpUnusedLocalVariableInspection */
                while ((list($k2, $v2) = each($action_desc)) && (!$find)) {
                    $find = ($v2["name"] == $act->name);
                }
                if (!$find) {
                    // remove the action
                    $this->log->info("Delete Action " . $act->name);
                    $act->Delete();
                }
            }
        }
        return '';
    }
    /**
     * retrieve the value of an argument fot the action
     * in web mode the value comes from http variable and in shell mode comes from args variable
     *
     * @param string $k the argument name
     * @param mixed $def default value if no argument is not set
     * @return mixed|string
     */
    public static function getArgument($k, $def = '')
    {
        $v = getHttpVars($k, null);
        if ($v === null) return $def;
        else return $v;
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
     * log with debug level
     *
     * @see Log
     * @param string $msg message text
     */
    public function debug($msg)
    {
        $this->log->debug($msg);
    }
    /**
     * log with info level
     *
     * @see Log
     * @param string $msg message text
     */
    public function info($msg)
    {
        $this->log->info($msg);
    }
    /**
     * log with warning level
     *
     * @see Log
     * @param string $msg message text
     */
    public function warning($msg)
    {
        $this->log->warning($msg);
    }
    /**
     * log with error level
     *
     * @see Log
     * @param string $msg message text
     */
    public function error($msg)
    {
        $this->log->error($msg);
    }
    /**
     * log with fatal level
     *
     * @see Log
     * @param string $msg message text
     */
    public function fatal($msg)
    {
        $this->log->fatal($msg);
    }
    /**
     * verify if an application is really installed in localhost
     * @param string $appname application reference name
     * @return bool true if application is installed
     */
    public function appInstalled($appname)
    {
        
        $pubdir = DEFAULT_PUBDIR;
        
        return (@is_dir($pubdir . "/" . $appname));
    }
    /**
     * return available Applications for current user
     * @return array
     */
    public function getAvailableApplication()
    {
        
        $query = new QueryDb($this->dbaccess, "Application");
        $query->basic_elem->sup_where = array(
            "available='Y'",
            "displayable='Y'"
        );
        $list = $query->Query(0, 0, "TABLE");
        $tab = array();
        if ($query->nb > 0) {
            $i = 0;
            foreach ($list as $k => $appli) {
                if ($appli["access_free"] == "N") {
                    
                    if (isset($this->user)) {
                        if ($this->user->id != 1) { // no control for user Admin
                            //if ($p->id_acl == "") continue;
                            // test if acl of root action is granted
                            // search  acl for root action
                            $queryact = new QueryDb($this->dbaccess, "Action");
                            $queryact->AddQuery("id_application=" . $appli["id"]);
                            $queryact->AddQuery("root='Y'");
                            $listact = $queryact->Query(0, 0, "TABLE");
                            $root_acl_name = $listact[0]["acl"];
                            if (!$this->HasPermission($root_acl_name, $appli["id"])) continue;
                        }
                    } else {
                        continue;
                    }
                }
                $appli["description"] = $this->text($appli["description"]); // translate
                $appli["iconsrc"] = $this->parent->getImageLink($appli["icon"]);
                if ($appli["iconsrc"] == "CORE/Images/core-noimage.png") $appli["iconsrc"] = $appli["name"] . "/Images/" . $appli["icon"];
                
                $tab[$i++] = $appli;
            }
        }
        return $tab;
    }
}
