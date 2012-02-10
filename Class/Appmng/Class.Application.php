<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Application Class
 *
 * @author Anakeen 2000
 * @version $Id: Class.Application.php,v 1.64 2008/08/01 09:03:01 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
/**
 */

include_once ('Class.DbObj.php');
include_once ('Class.QueryDb.php');
include_once ('Class.Action.php');
include_once ('Class.Layout.php');
include_once ('Class.Param.php');
include_once ('Class.User.php');
include_once ('Class.Permission.php');
include_once ('Class.Style.php');
include_once ('Class.ParamDef.php');
include_once ('Lib.Http.php');
include_once ('Lib.Common.php');

function f_paramglog($var)
{ // filter to select only not global
    return (!((isset($var["global"]) && ($var["global"] == 'Y'))));
}

class Application extends DbObj
{
    public $fields = array(
        "id",
        "name",
        "short_name",
        "description",
        "access_free",
        "available",
        "icon",
        "displayable",
        "with_frame",
        "childof",
        "objectclass",
        "ssl",
        "machine",
        "iorder",
        "tag"
    );
    public $id;
    public $name;
    public $short_name;
    public $description;
    public $access_free;
    public $available;
    public $icon;
    public $displayable;
    public $with_frame;
    public $childof;
    public $objectclass;
    public $ssl;
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
     * @var User
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
    public $jsref = array();
    public $jscode = array();
    public $logmsg = array();
    
    public $cssref = array();
    public $csscode = array();
    
    function Set($name, &$parent, $session = "", $autoinit = false)
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
                    } elseif ($_SERVER['HTTP_HOST'] != "") {
                        Redirect($this, $this->name, "");
                    }
                } else {
                    global $_SERVER;
                    if ($_SERVER['HTTP_HOST'] != "") Header("Location: " . $_SERVER['HTTP_REFERER']);
                }
            } else {
                header('HTTP/1.0 503 Application unavalaible');
                printf("Fail to find application %s.\n", $name);
                exit;
                //throw new Exception(sprintf("Fail to find application %s",$name));
                
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
        $sessparam = false;
        $this->param = new Param($this->dbaccess);
        if ($this->session) $sessparam = $this->session->read("sessparam" . $this->id, false);
        if ($sessparam) {
            $this->param->appid = $this->id;
            $this->param->buffer = $sessparam;
            $this->InitStyle(false);
        } else {
            $this->InitStyle();
            $this->param->SetKey($this->id, isset($this->user->id) ? $this->user->id : false, $this->style->name);
            if ($this->session) $this->session->register("sessparam" . $this->id, $this->param->buffer);
        }
        if (!$this->rootdir) $this->rootdir = $this->Getparam("CORE_PUBDIR");
        if ($this->available == "N") {
            // error
            return sprintf(_("Application %s (%s) not available") , $this->name, _($this->short_name));
        }
        $this->permission = null;
        return '';
    }
    
    function Complete()
    {
    }
    
    function SetSession(&$session)
    {
        $this->session = $session;
        // Set the user if possible
        if (is_object($this->session)) {
            if ($this->session->userid != 0) {
                $this->log->debug("Get user on " . $this->GetParam("CORE_DB"));
                $this->user = new User($this->GetParam("CORE_DB") , $this->session->userid);
            } else {
                $this->log->debug("User not set ");
            }
        }
    }
    
    function PreInsert()
    {
        if ($this->Exists($this->name)) return "Ce nom d'application existe deja...";
        if ($this->name == "CORE") {
            $this->id = 1;
        } else {
            $res = $this->exec_query("select nextval ('seq_id_application')");
            $arr = $this->fetch_array(0);
            $this->id = $arr["nextval"];
        }
        return '';
    }
    
    function PreUpdate()
    {
        if ($this->dbid == - 1) return FALSE;
        if ($this->Exists($this->name, $this->id)) return "Ce nom d'application existe deja...";
        return '';
    }
    
    function Exists($app_name, $id_application = '')
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
        if (substr($pathname, 0, strlen($this->rootdir) - 1) == $this->rootdir) {
            $pathname = substr($pathname, strlen($this->rootdir) + 1);
        }
        
        return $pathname;
    }
    /**
     * Try to resolve a JS/CSS reference to a supported location
     * @param string $ref the JS/CSS reference
     * @return string the resolved location of the reference or an empty string on failure
     */
    private function resolveRessourceLocation($ref)
    {
        if ($this->rootdir == '') {
            $this->rootdir = $this->GetParam("CORE_PUBDIR");
        }
        
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
        
        return '';
    }
    /**
     * Add a ressource (JS/CSS) to the page
     * @param string $type 'js' or 'css'
     * @param string $ref the ressource reference
     * @param boolean $needparse should the ressource be parsed (default false)
     */
    function AddRessourceRef($type, $ref, $needparse)
    {
        /* Try to attach the ressource to the parent app */
        if ($this->hasParent()) {
            $ret = $this->parent->AddRessourceRef($type, $ref, $needparse);
            if ($ret !== '') {
                return $ret;
            }
        }
        /* Try to attach the ressource to the current app */
        $ressourceLocation = '';
        if ($needparse) {
            $ressourceLocation = $this->GetParam("CORE_STANDURL") . "&app=CORE&action=CORE_CSS&session=" . $this->session->id . "&layout=" . $ref . "&type=" . $type;
        } else {
            $location = $this->resolveRessourceLocation($ref);
            if ($location != '') {
                $ressourceLocation = $location;
            }
        }
        
        if ($ressourceLocation == '') {
            /* The ressource could not be resolved */
            return '';
        }
        
        if ($type == 'js') {
            $this->jsref[$ref] = $ressourceLocation;
        } elseif ($type == 'css') {
            $this->cssref[$ref] = $ressourceLocation;
        } else {
            return '';
        }
        
        return $ressourceLocation;
    }
    
    function AddCssRef($ref, $needparse = false)
    {
        return $this->AddRessourceRef('css', $ref, $needparse);
    }
    
    function AddJsRef($ref, $needparse = false)
    {
        return $this->AddRessourceRef('js', $ref, $needparse);
    }
    
    function AddJsCode($code)
    {
        // Js Code are stored in the top level application
        if ($this->hasParent()) {
            $this->parent->AddJsCode($code);
        } else {
            $this->jscode[] = $code;
        }
    }
    
    function AddLogMsg($code, $cut = 80)
    {
        if ($code == "") return;
        // Js Code are stored in the top level application
        if ($this->hasParent()) {
            $this->parent->AddLogMsg($code, $cut);
        } else {
            $logmsg = $this->session->read("logmsg", array());
            if (is_array($code)) {
                $code["stack"] = getDebugStack(4);
                $logmsg[] = json_encode($code);
            } else $logmsg[] = strftime("%H:%M - ") . str_replace("\n", "\\n", addslashes(substr($code, 0, $cut)));
            $this->session->register("logmsg", $logmsg);
            $suser = sprintf("%s %s [%d] - ", $this->user->firstname, $this->user->lastname, $this->user->id);
            if (is_array($code)) $code = print_r($code, true);
            $this->log->info($suser . $code);
        }
    }
    /**
     * send a message to the user interface
     * @param string $code message
     * @return mixed
     */
    function addWarningMsg($code)
    {
        if (($code == "") || ($code == "-")) return;
        // Js Code are stored in the top level application
        if ($this->hasParent()) {
            $this->parent->addWarningMsg($code);
        } else {
            if ($_SERVER['HTTP_HOST'] != "") {
                $logmsg = $this->session->read("warningmsg", array());
                $logmsg[] = str_replace("\n", "\\n", addslashes($code));
                $this->session->register("warningmsg", $logmsg);
            } else error_log("dcp warning: $code");
        }
    }
    function GetJsRef()
    {
        if ($this->hasParent()) {
            return ($this->parent->GetJsRef());
        } else {
            return ($this->jsref);
        }
    }
    
    function GetJsCode()
    {
        if ($this->hasParent()) {
            return ($this->parent->GetJsCode());
        } else {
            return ($this->jscode);
        }
    }
    
    function GetLogMsg()
    {
        return ($this->session->read("logmsg", array()));
    }
    
    function ClearLogMsg()
    {
        $this->session->unregister("logmsg");
    }
    function GetWarningMsg()
    {
        return ($this->session->read("warningmsg", array()));
    }
    
    function ClearWarningMsg()
    {
        $this->session->unregister("warningmsg");
    }
    
    function AddCssCode($code)
    {
        // Css Code are stored in the top level application
        if ($this->hasParent()) {
            $this->parent->AddCssCode($code);
        } else {
            $this->csscode[] = $code;
        }
    }
    function GetCssRef()
    {
        if ($this->hasParent()) {
            return ($this->parent->GetCssRef());
        } else {
            return ($this->cssref);
        }
    }
    
    function GetCssCode()
    {
        if ($this->hasParent()) {
            return ($this->parent->GetCssCode());
        } else {
            return ($this->csscode);
        }
    }
    /**
     * Test permission for currennt user in current application
     *
     * @param string $acl_name acl name to test
     * @param string $app_name application if test for other application
     * @return bool true if permission granted
     */
    function HasPermission($acl_name, $app_name = "")
    {
        if (!isset($this->user) || !is_object($this->user)) {
            $this->log->warning("Action {$this->parent->name}:{$this->name} requires authentification");
            return FALSE;
        }
        if ($this->user->id == 1) return true; // admin can do everything
        if ($app_name == "") {
            
            $acl = new Acl($this->dbaccess);
            if (!$acl->Set($acl_name, $this->id)) {
                $this->log->warning("Acl $acl_name not available for App $this->name");
                return FALSE;
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
            
            return ($this->permission->HasPrivilege($acl->id));
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
                    return ($wperm->HasPrivilege($acl->id));
                }
            }
        }
        return false;
    }
    
    function InitStyle($init = true)
    {
        if ($init == true) {
            if (isset($this->user)) $pstyle = new Param($this->dbaccess, array(
                "STYLE",
                PARAM_USER . $this->user->id,
                "1"
            ));
            else $pstyle = new Param($this->dbaccess, array(
                "STYLE",
                PARAM_USER . ANONYMOUS_ID,
                "1"
            ));
            if (!$pstyle->isAffected()) $pstyle = new Param($this->dbaccess, array(
                "STYLE",
                PARAM_APP,
                "1"
            ));
            
            $style = $pstyle->val;
            $this->style = new Style($this->dbaccess, $style);
            
            $this->style->Set($this);
        } else {
            $style = $this->getParam("STYLE");
            $this->style = new Style($this->dbaccess, $style);
            
            $this->style->Set($this);
        }
        if ("Y" == $this->style->parsable) {
            $this->AddCssRef("$style:gen.css", true);
        } else {
            $this->AddCssRef("STYLE/$style/Layout/gen.css");
        }
        $size = $this->getParam("FONTSIZE", "normal");
        $this->AddCssRef("WHAT/Layout/size-$size.css");
    }
    
    function SetLayoutVars($lay)
    {
        if ($this->hasParent()) {
            $this->parent->SetLayoutVars($lay);
        }
    }
    
    function GetRootApp()
    {
        if ($this->parent == "") {
            return ($this);
        } else {
            return ($this->parent->GetRootApp());
        }
    }
    
    function GetImageFile($img)
    {
        
        return $this->rootdir . "/" . $this->GetImageUrl($img);
    }
    
    var $noimage = "CORE/Images/noimage.png";
    function GetImageUrl($img, $detectstyle = true, $size = null)
    {
        
        if ($img != "") {
            // try style first
            if ($detectstyle) {
                $url = $this->style->GetImageUrl($img, "");
                if ($url != "") {
                    if ($size !== null) return 'resizeimg.php?img=' . $url . '&size=' . $size;
                    return $url;
                }
            }
            // try application
            if (file_exists($this->rootdir . "/" . $this->name . "/Images/" . $img)) {
                $url = $this->name . "/Images/" . $img;
                if ($size !== null) return 'resizeimg.php?img=' . $url . '&size=' . $size;
                return $url;
            } else { // perhaps generic application
                if (($this->childof != "") && (file_exists($this->rootdir . "/" . $this->childof . "/Images/" . $img))) {
                    $url = $this->childof . "/Images/" . $img;
                    if ($size !== null) return 'resizeimg.php?img=' . $url . '&size=' . $size;
                    return $url;
                } else if (file_exists($this->rootdir . "/Images/" . $img)) {
                    $url = "Images/" . $img;
                    if ($size !== null) return 'resizeimg.php?img=' . $url . '&size=' . $size;
                    return $url;
                }
            }
            // try in parent
            if ($this->parent != "") {
                $url = $this->parent->getImageUrl($img);
                if ($size !== null) return 'resizeimg.php?img=' . $url . '&size=' . $size;
                return $url;
            }
        }
        return $this->noimage;
    }
    
    function ImageFilterColor($image, $fcol, $newcol, $out = null)
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
    
    function GetFilteredImageUrl($imgf)
    {
        
        $ttf = explode(":", $imgf);
        $img = $ttf[0];
        $filter = $ttf[1];
        
        $url = $this->GetImageUrl($img);
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
        
        $cdir = 'img-cache/';
        $rcdir = $this->rootdir . '/' . $cdir;
        if (!is_dir($rcdir)) mkdir($rcdir);
        
        $uimg = $cdir . $this->name . '-' . $fcol[0] . '.' . $fcol[1] . '.' . $fcol[2] . '_' . $ncol[0] . '.' . $ncol[1] . '.' . $ncol[2] . '.' . $img;
        $cimg = $this->rootdir . '/' . $uimg;
        if (file_exists($cimg)) return $uimg;
        
        $this->ImageFilterColor($this->rootdir . '/' . $url, $fcol, $ncol, $cimg);
        return $uimg;
    }
    
    function GetLayoutFile($layname)
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
    function OldGetLayoutFile($layname)
    {
        
        $root = $this->Getparam("CORE_PUBDIR");
        $file = $root . "/" . $this->name . "/Layout/" . $layname;
        if (file_exists($file)) {
            $file = $this->style->GetLayoutFile($layname, $file);
            return ($file);
        }
        if ($this->parent != "") return ($this->parent->GetLayoutFile($layname));
        return ("");
    }
    
    function SetParam($key, $val)
    {
        if (is_array($val)) {
            if (isset($val["global"]) && $val["global"] == "Y") $type = PARAM_GLB;
            else $type = PARAM_APP;
            $this->param->Set($key, $val["val"], $type, $this->id);
        } else { // old method
            $this->param->Set($key, $val, PARAM_APP, $this->id);
        }
    }
    /**
     * set user parameter for current user
     *
     * @param string $key parameter identificator
     * @param string $val value
     * @return void
     */
    function SetParamU($key, $val)
    {
        $this->param->Set($key, $val, PARAM_USER . $this->user->id, $this->id);
    }
    function SetParamDef($key, $val)
    {
        // add new param definition
        $pdef = new ParamDef($this->dbaccess, $key);
        $oldValues = array();
        if (!$pdef->isAffected()) {
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
        
        if ($pdef->isAffected()) {
            $pdef->Modify();
            // migrate paramv values in case of type changes
            $newValues = $pdef->getValues();
            if ($oldValues['isglob'] != $newValues['isglob']) {
                $ptype = $oldValues['isglob'] == 'Y' ? PARAM_GLB : PARAM_APP;
                $ptypeNew = $newValues['isglob'] == 'Y' ? PARAM_GLB : PARAM_APP;
                $pv = new Param($this->dbaccess, array(
                    $pdef->name,
                    $ptype,
                    $pdef->appid
                ));
                if ($pv->isAffected()) {
                    $pv->set($pv->name, $pv->val, $ptypeNew, $pv->appid);
                }
            }
        } else $pdef->Add();
    }
    function SetVolatileParam($key, $val)
    {
        $this->param->SetVolatile($key, $val);
    }
    
    function GetParam($key, $default = "")
    {
        if (!isset($this->param)) return ($default);
        $z = $this->param->Get($key, "z");
        if ($z == "z") {
            if ($this->hasParent()) return $this->parent->GetParam($key, $default);
        } else {
            return ($z);
        }
        
        return ($default);
    }
    
    function InitAllParam($tparam, $update = false)
    {
        if (is_array($tparam)) {
            reset($tparam);
            while (list($k, $v) = each($tparam)) {
                $this->SetParamDef($k, $v); // update definition
                if ($update) {
                    // don't modify old parameters
                    if ($this->param->Get($k, null) === null) $this->SetParam($k, $v); // set only new parameters or static variable like VERSION
                    
                } else {
                    $this->SetParam($k, $v);
                }
            }
        }
    }
    
    function GetAllParam()
    {
        $list = $this->param->buffer;
        if ($this->hasParent()) {
            $list2 = $this->parent->GetAllParam();
            $list = array_merge($this->param->buffer, $list2);
        }
        
        return ($list);
    }
    
    function InitApp($name, $update = FALSE)
    {
        
        $this->log->info("Init : $name");
        if (file_exists($this->GetParam("CORE_PUBDIR", DEFAULT_PUBDIR) . "/{$name}/{$name}.app")) {
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
                    $this->Add();
                    $this->param = new Param();
                    $this->param->SetKey($this->id, isset($this->user->id) ? $this->user->id : ANONYMOUS_ID);
                }
            } else {
                $this->log->info("can't init $name");
                return false;
            }
            
            $action_desc = $action_desc_ini;
            // init acl
            $acl = new Acl($this->dbaccess);
            $acl->Init($this, $app_acl, $update);
            // init father if has
            if ($this->childof != "") {
                // init ACL & ACTION
                $app_acl = array();
                $action_desc = array();
                include ("{$this->childof}/{$this->childof}.app");
                // init acl
                $acl = new Acl($this->dbaccess);
                $acl->Init($this, $app_acl, $update);
                // init actions
                $action = new Action($this->dbaccess);
                $action->Init($this, $action_desc, false);
            }
            // init actions
            $action = new Action($this->dbaccess);
            
            $action->Init($this, array_merge($action_desc, $action_desc_ini) , $update);
            //----------------------------------
            // init application constant
            if (file_exists(GetParam("CORE_PUBDIR", DEFAULT_PUBDIR) . "/{$name}/{$name}_init.php")) {
                
                include ("{$name}/{$name}_init.php");
                if ($this->param) {
                    // delete paramters that cannot be change after initialisation to be change now
                    if ($update) $this->param->DelStatic($this->id);
                    global $app_const;
                    if (isset($app_const)) $this->InitAllParam($app_const, $update);
                }
            }
            //----------------------------------
            // add init father application constant
            if (file_exists(GetParam("CORE_PUBDIR", DEFAULT_PUBDIR) . "/{$this->childof}/{$this->childof}_init.php")) {
                include ("{$this->childof}/{$this->childof}_init.php");
                global $app_const;
                $this->InitAllParam(array_filter($app_const, "f_paramglog") , true);
            }
            
            if ($this->id > 1) {
                $this->SetParamDef("APPNAME", array(
                    "val" => $name,
                    "kind" => "static"
                )); // use by generic application
                $this->SetParam("APPNAME", array(
                    "val" => $name,
                    "kind" => "static"
                )); // use by generic application
                
            }
        } else {
            $this->log->info("No {$name}/{$name}.app available");
            return false;
        }
        return true;
    }
    
    function UpdateApp()
    {
        $name = $this->name;
        $this->InitApp($name, TRUE);
    }
    // Update All available application
    function UpdateAllApp()
    {
        
        $query = new QueryDb($this->dbaccess, $this->dbtable);
        $query->AddQuery("available = 'Y'");
        $allapp = $query->Query();
        
        while (list($k, $app) = each($allapp)) {
            $application = new Application($this->dbaccess, $app->id);
            
            $application->Set($app->name, $this->parent);
            $application->UpdateApp();
        }
    }
    function DeleteApp()
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
            reset($list);
            /**
             * @var Action $v
             */
            while (list($k, $v) = each($list)) {
                $this->log->debug(" Delete action {$v->name} ");
                $v->Delete();
            }
        }
        unset($query);
        
        unset($list);
        // delete params
        $param = new Param($this->dbaccess);
        $param->DelAll($this->id);
        // delete application
        $this->Delete();
    }
    
    function Text($code, $args = NULL)
    {
        if ($code == "") return "";
        return _("$code");
    }
    // Write default ACL when new user is created
    function UpdateUserAcl($iduser)
    {
        
        $query = new QueryDb($this->dbaccess, $this->dbtable);
        $query->AddQuery("available = 'Y'");
        $allapp = $query->Query();
        $acl = new Acl($this->dbaccess);
        
        while (list($k, $v) = each($allapp)) {
            $permission = new Permission($this->dbaccess);
            $permission->id_user = $iduser;
            $permission->id_application = $v->id;
            
            $privileges = $acl->getDefaultAcls($v->id);
            
            while (list($k2, $aclid) = each($privileges)) {
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
    function GetIdFromName($name)
    {
        $query = new QueryDb($this->dbaccess, $this->dbtable);
        $query->AddQuery("name = '" . pg_escape_string(trim($name)) . "'");
        $app = $query->Query(0, 0, "TABLE");
        if (is_array($app)) return $app[0]["id"];
        return 0;
    }
    
    function hasParent()
    {
        return (is_object($this->parent) && ($this->parent !== $this));
    }
}
?>