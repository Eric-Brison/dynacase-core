<?php
/**
 * Application Class
 *
 * @author Anakeen 2000 
 * @version $Id: Class.Application.php,v 1.45 2005/10/28 15:10:54 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */



include_once('Class.DbObj.php');
include_once('Class.QueryDb.php');
include_once('Class.Action.php');
include_once('Class.Layout.php');
include_once('Class.Param.php');
include_once('Class.User.php');
include_once('Class.Permission.php');
include_once('Class.Style.php');
include_once('Class.ParamDef.php');
include_once('Lib.Http.php');
include_once('Lib.Common.php');

function N_($s) {return ($s);} // to tag gettext without change text immediatly
function f_paramglog($var) { // filter to select only not global
    return (! ((isset($var["global"]) && ($var["global"] == 'Y'))));
}


Class Application extends DbObj
{
  public $fields = array ( "id",  "name","short_name",  "description",  "access_free",  "available", "icon", "displayable", "with_frame", "childof","objectclass","ssl","machine");

  public $id_fields = array ( "id");

  public $fulltextfields = array ("name","short_name","description");
  public $sqlcreate = '
create table application ( 	id 	int not null,
     		primary key (id),
			name 	    varchar(20) not null,
			short_name text,
			description text ,
			access_free  varchar(20),
			available  char,
                        icon text,
                        displayable char,
                        with_frame char,
                        childof varchar(20),
                        objectclass char,
                        ssl char,
                        machine text);
create index application_idx1 on application(id);
create index application_idx2 on application(name);
create sequence SEQ_ID_APPLICATION start 10;
';

  public $dbtable = "application";

  public $def = array ( "criteria" => "",
		     "order_by" => "name"
		     );

  public $criterias = array (
			  "name" => array ("libelle" => "Nom",
					   "type" => "TXT")
			  );


  public $parent="";

  public $param;
  public $permission = ""; // permission object

  public $jsref=array();
  public $jscode=array();
  public $logmsg=array();

  public $cssref=array();
  public $csscode=array();

  function Set($name,&$parent, $session="")
    {


  
      $this->log->debug("Entering : Set application to $name");

 

      $query=new QueryDb($this->dbaccess,"Application");
      $query->order_by = "";
      $query->criteria = "name";
      $query->operator = "=";
      $query->string = "'".pg_escape_string($name)."'";
      $list = $query->Query(0,0,"TABLE");
      if ($query->nb != 0) {
	$this->affect($list[0]);
	$this->log->debug("Set application to $name");
	if (!isset ($parent)) {
	  $this->log->debug("Parent not set");
	}
      } else {
	// Init the database with the app file if it exists
	$this->InitApp($name);
	if ($parent != "") {
	  $this->parent=&$parent;
	  Redirect($this,$this->name,"");
	} else {
	  global $_SERVER;
	  Header("Location: ".$_SERVER['HTTP_REFERER']);
	}
      }

      if ($this !== $parent)  $this->parent=&$parent;
      if (is_object($this->parent) && isset($this->parent->session)) {
	$this->session=$this->parent->session;
	if (isset($this->parent->user) && is_object($this->parent->user)) {
	  $this->user=$this->parent->user;
	  $permission = new Permission($this->dbaccess, array($this->user->id,$this->id));
	  if (! $permission-> IsAffected()) { // case of no permission available
	    $permission->Affect(array("id_user" => $this->user->id,
				      "id_application" => $this->id ));
	  } 
	  $this->permission=&$permission;
	}
      }

      if ($this->available == "N") 
	Redirect($this,"CORE","",$action->GetParam("CORE_ROOTURL"));

      if ($session != "") $this->SetSession($session);

      $this->InitStyle( );

      $this->param=new Param($this->dbaccess);
      $this->param->SetKey($this->id,isset($this->user->id)?$this->user->id:ANONYMOUS_ID,$this->style->name);


    }

  function Complete() {
  }

  function SetSession(&$session) {
    $this->session=$session;
    // Set the user if possible
    if (is_object($this->session)) {
      if ($this->session->userid != 0) {
	$this->log->debug("Get user on ".$this->GetParam("CORE_DB"));
	$this->user = new User($this->GetParam("CORE_DB"),$this->session->userid);
      } else {
	$this->log->debug("User not set ");
      }
    }
  

  }


  function PreInsert( )
    {
      if ($this->Exists( $this->name)) return "Ce nom d'application existe deja...";  
      if ($this->name == "CORE") {
	$this->id=1;
      } else {
	$res = $this->exec_query("select nextval ('seq_id_application')");
	$arr = $this->fetch_array (0);
	$this->id = $arr["nextval"];
      }
  
    }

  function PreUpdate()
    {
      if ($this->dbid == -1) return FALSE;
      if ($this->Exists( $this->name,$this->id)) return "Ce nom d'application existe deja...";    

    }

  function Exists($app_name,$id_application='')
    {
      $this->log->debug("Exists $app_name ?");
      $query=new QueryDb($this->dbaccess,"application");
      $query->order_by="";
      $query->criteria="";

      if ($id_application!='') {
	$query->basic_elem->sup_where = array ("name='$app_name'","id!=$id_application");

      } else {
	$query->criteria="name";
	$query->operator="=";
	$query->string="'".$app_name."'";
      }

      $r=$query->Query(0,0,"TABLE");

      return ($query->nb > 0)?$r[0]["id"]:false;
    }

  function AddJsRef($ref,$needparse=false) 
    {
      // Js Ref are stored in the top level application
      $root = $this->Getparam("CORE_PUBDIR");
      if (file_exists($root."/".$this->name."/Layout/".$ref)) {
	$ref=$this->Getparam("CORE_PUBURL")."/".$this->name."/Layout/".$ref;
      }
      if ($this->hasParent()) {
	$this->parent->AddJsRef($ref,$needparse);
      } else {
	(!isset($this->jscount) ? $this->jscount = 0 : $this->jscount++);
	if ($needparse) {
	  $this->jsref[$ref]=$this->Getparam("CORE_STANDURL")."&app=CORE&action=CORE_CSS&session=".$this->session->id."&layout=".$ref;
	} else {
	  $this->jsref[$ref]=$ref;
	}
	$this->log->debug("AddJsRef [{$this->jscount}] = <{$this->jsref[$this->jscount]}>");
      }
    }

  function AddJsCode($code) 
    {
      // Js Code are stored in the top level application
      if ($this->hasParent()) {
	$this->parent->AddJsCode($code);
      } else {
	$this->jscode[]=$code;
      }
    }

  function AddLogMsg($code) 
    {
      if ($code =="") return;
      // Js Code are stored in the top level application
      if ($this->hasParent()) {
	$this->parent->AddLogMsg($code);
      } else {    
	$logmsg=$this->session->read("logmsg", array());
	$logmsg[]=strftime("%H:%M - ").str_replace("\n","\\n",addslashes(substr($code,0,80)));
	$this->session->register("logmsg",$logmsg);
	$suser = sprintf("%s %s [%d] - ",$this->user->firstname, $this->user->lastname, $this->user->id);
	$this->log->info($suser.$code);
      }
    }
  function AddWarningMsg($code) 
    {
      if (($code =="") || ($code =="-")) return;
      // Js Code are stored in the top level application
      if ($this->hasParent()) {
	$this->parent->AddWarningMsg($code);
      } else {    
	$logmsg=$this->session->read("warningmsg", array());
	$logmsg[]=str_replace("\n","\\n",addslashes($code));
	$this->session->register("warningmsg",$logmsg);
   
      }
    }
  function GetJsRef() 
    {
      if ($this->hasParent()) {
	return($this->parent->GetJsRef());
      } else {
	return($this->jsref);
      }
    }
   
  function GetJsCode() 
    {
      if ($this->hasParent()) {
	return($this->parent->GetJsCode());
      } else {
	return($this->jscode);
      }
    }
   
  function GetLogMsg() 
    {  
      return($this->session->read("logmsg", array()));
    }

  function ClearLogMsg() 
    {
      $this->session->unregister("logmsg");
    }   
  function GetWarningMsg() 
    {  
      return($this->session->read("warningmsg", array()));
    }

  function ClearWarningMsg() 
    {
      $this->session->unregister("warningmsg");
    }
  function AddCssRef($ref,$needparse=false) 
    {
      // Css Ref are stored in the top level application
      $root = $this->Getparam("CORE_PUBDIR");
      if (file_exists($root."/".$this->name."/Layout/".$ref)) 
	$ref=$this->Getparam("CORE_PUBURL")."/".$this->name."/Layout/".$ref;
      if ($this->hasParent()) {
	$this->parent->AddCssRef($ref,$needparse);
      } else {
	(!isset($this->csscount) ? $this->csscount = 0 : $this->csscount++);
	if ($needparse) {
	  $this->cssref[$ref]=$this->Getparam("CORE_STANDURL")."&app=CORE&action=CORE_CSS&session=".$this->session->id."&layout=".$ref;
	} else {
	  $this->cssref[$this->csscount]=$ref;
	}
	$this->log->debug("AddCssRef [{$this->csscount}] = <{$this->cssref[$this->csscount]}>");
      }
    }

  function AddCssCode($code) 
    {
      // Css Code are stored in the top level application
      if ($this->hasParent()) {
	$this->parent->AddCssCode($code);
      } else {
	$this->csscode[]=$code;
      }
    }
  function GetCssRef() 
    {
      if ($this->hasParent()) {
	return($this->parent->GetCssRef());
      } else {
	return($this->cssref);
      }
    }
   
  function GetCssCode() 
    {
      if ($this->hasParent()) {
	return($this->parent->GetCssCode());
      } else {
	return($this->csscode);
      }
    }

  /**
   * Test permission for currennt user in current application
   * 
   * @param string $acl_name acl name to test
   * @param string $app_name application if test for other application
   * @return bool true if permission granted
   */
  function HasPermission($acl_name,$app_name="") {
    if (!isset($this->user) || !is_object($this->user)) {
      $this->log->warning("Action {$this->parent->name}:{$this->name} requires authentification");
      return FALSE;
    }
    if ($this->user->id==1) return true; // admin can do everything
    if ($app_name=="") {
  
      $acl=new Acl($this->dbaccess);
      if ( ! $acl->Set($acl_name,$this->id)) {
	$this->log->warning("Acl $acl_name not available for App $this->name");    
	return FALSE;
      }

      return($this->permission->HasPrivilege($acl->id));
    } else {
      // test permission for other application
      if (! is_numeric($app_name)) $appid=$this->GetIdFromName($app_name);
      else $appid=$app_name;

      $wperm=new Permission($this->dbaccess, array($this->user->id,$appid));
      if ($wperm->isAffected()) {
	$acl=new Acl($this->dbaccess);
	if ( ! $acl->Set($acl_name,$appid)) {
	  $this->log->warning("Acl $acl_name not available for App $this->name");    
	  return false;
	} else {
	  return($wperm->HasPrivilege($acl->id));
	
	}
      }
			     
    }
    return false;
  }

  function InitStyle()
    {
      if (isset($this->user))
	$pstyle = new Param($this->dbaccess,array("STYLE",PARAM_USER.$this->user->id,"1"));
      else 
	$pstyle = new Param($this->dbaccess,array("STYLE",PARAM_USER.ANONYMOUS_ID,"1"));
      if (! $pstyle->isAffected()) $pstyle = new Param($this->dbaccess,array("STYLE",PARAM_APP,"1"));

      $style = $pstyle->val;
      $this->style=new Style($this->dbaccess,$style);

      $this->style->Set($this);
    }




  function SetLayoutVars($lay) {
    if ($this->hasParent()) {
      $this->parent->SetLayoutVars($lay);
    }
  }
  
  function GetRootApp() {
    if ($this->parent == "") {
      return($this);
    } else {
      return($this->parent->GetRootApp()) ;
    }
  }

  function GetImageFile($img) {
    $root = $this->Getparam("CORE_PUBDIR");
  
    return $root."/".$this->GetImageUrl($img);
  }

  function GetImageUrl($img) {
    if ($img != "") {
    // try style first 
      $url = $this->style->GetImageUrl($img,"");
      if ($url != "") return $url;

      // try application 
      $root = $this->Getparam("CORE_PUBDIR");

      if (file_exists($root."/".$this->name."/Images/".$img)) {
	return ($this->name."/Images/".$img);
      } else { // perhaps generic application
	if (($this->childof != "") && (file_exists($root."/".$this->childof."/Images/".$img))) {
	  return ($this->childof."/Images/".$img);
	} else  if (file_exists($root."/Images/".$img)) {
	  return ("Images/".$img);
	} 
      }
  
      // try in parent 
      if ($this->parent != "") return($this->parent->getImageUrl($img));
    }
    return ("CORE/Images/noimage.png");
  }

  function GetLayoutFile($layname) {

    $file= $this->style->GetLayoutFile($layname,"");
    if ($file != "") return $file;

    $nav=$this->session->Read("navigator");
    $ver=doubleval($this->session->Read("navversion"));

  
    $laydir = $this->Getparam("CORE_PUBDIR")."/".$this->name."/Layout/";
    $file = $laydir.$layname; // default file
 
  
    if (file_exists($file)) {
      return($file);
    } else { 
      // perhaps generic application
      $file = $this->Getparam("CORE_PUBDIR")."/".$this->childof."/Layout/$layname";
      if (file_exists($file))  return ($file);    
    }
    if ($this->parent != "") return($this->parent->GetLayoutFile($layname));
    return ("");
  }
  function OldGetLayoutFile($layname) {

    $root = $this->Getparam("CORE_PUBDIR");
    $file = $root."/".$this->name."/Layout/".$layname;
    if (file_exists($file)) {
      $file= $this->style->GetLayoutFile($layname,$file);
      return($file);
    }
    if ($this->parent != "") return($this->parent->GetLayoutFile($layname));
    return ("");
  }

  function SetParam($key,$val)
    {       
      if (is_array($val)) {
	if (isset($val["global"]) && $val["global"]=="Y") $type=PARAM_GLB; else $type=PARAM_APP;
	$this->param->Set($key,$val["val"],$type,$this->id);

      } else { // old method

	$this->param->Set($key,$val,PARAM_APP,$this->id);

      }
    }

  function SetParamDef($key,$val)
    {
      // add new param definition
      $pdef = new ParamDef($this->dbaccess,$key);
      if (! $pdef->isAffected()) {
	$pdef->name=$key;
	$pdef->isuser="N";
	$pdef->isstyle="N";
	$pdef->isglob="N";  
	$pdef->appid=$this->id;
	$pdef->descr="";
	$pdef->kind="text";
      }
    
      if (is_array($val)) {
	if (isset($val["kind"]))  $pdef->kind=$val["kind"];
	if (isset($val["user"]) && $val["user"]=="Y") $pdef->isuser="Y";
	if (isset($val["style"]) && $val["style"]=="Y") $pdef->isstyle="Y";
	if (isset($val["descr"])) $pdef->descr=$val["descr"];  
	if (isset($val["global"]) && $val["global"]=="Y") $pdef->isglob="Y";       
      }

      if ($pdef->isAffected()) $pdef->Modify();
      else $pdef->Add();
    }
  function SetVolatileParam($key,$val)
    {
      $this->param->SetVolatile($key,$val);
    }

  function GetParam($key,$default="")
    { 
      if (!isset($this->param)) return ($default);
      $z=$this->param->Get($key,"z");
      if ($z=="z") {
	if ($this->hasParent())return $this->parent->GetParam($key,$default);
      } else {
	return ($z);
      }

      return ($default);
    }

  function InitAllParam($tparam,$update=false) {
    if (is_array($tparam)) {
      reset($tparam);
      while (list($k,$v) = each ($tparam)) {
	$this->SetParamDef($k,$v); // update definition
	if ($update) { 

	  // don't modify old parameters
	    
	  if ($this->param->Get($k) == "") 
	    $this->SetParam($k,$v);// set only new parameters or static variable like VERSION
	} else {
	  $this->SetParam($k,$v);
	}
      }
    }
  } 

  function GetAllParam()
    {
      $list=$this->param->buffer;
      if ($this->hasParent()) {
	$list2 = $this->parent->GetAllParam();
	$list = array_merge($this->param->buffer,$list2);
      }


      return($list);
    }
  
  function InitApp($name,$update=FALSE) {

    $this->log->info("Init : $name");
    if (file_exists($this->GetParam("CORE_PUBDIR",DEFAULT_PUBDIR)."/{$name}/{$name}.app")) {
      global $app_desc, $app_acl, $action_desc;

      // init global array
      $app_acl=array();
      $app_desc=array();
      $action_desc=array();
      include("{$name}/{$name}.app");
      $action_desc_ini=$action_desc;
      if (sizeof($app_desc)>0) {
	if (! $update) {
	  $this->log->debug("InitApp :  new application ");
	}
	foreach ($app_desc as $k=>$v) {
	  $this->$k = $v;
	}
	$this->available = "Y";
	if ($update) {
	  $this->Modify();
	} else {
	  $this->Add();
	  $this->param=new Param();
	  $this->param->SetKey($this->id,isset($this->user->id)?$this->user->id:ANONYMOUS_ID);
	}
      } else {
	$this->log->info ("can't init $name");
	return false;
      }

      $action_desc=$action_desc_ini;
      // init acl
      $acl = new Acl($this->dbaccess);
      $acl->Init($this,$app_acl,$update);



      // init father if has
      if ($this->childof != "") {
       
	// init ACL & ACTION
	$app_acl=array();
	$action_desc=array();
	include("{$this->childof}/{$this->childof}.app");
       
	// init acl
	$acl = new Acl($this->dbaccess);
	$acl->Init($this,$app_acl,$update);

	// init actions
	$action = new Action($this->dbaccess);
	$action->Init($this,$action_desc,false);
       
      }

      // init actions
      $action = new Action($this->dbaccess);
     
      $action->Init($this,array_merge($action_desc,$action_desc_ini),$update);

      //----------------------------------
      // init application constant
      if (file_exists(GetParam("CORE_PUBDIR",DEFAULT_PUBDIR)."/{$name}/{$name}_init.php")) {

        include("{$name}/{$name}_init.php");

	// delete paramters that cannot be change after initialisation to be change now
	if ($update)   $this->param->DelStatic($this->id);
        global $app_const;
        if (isset($app_const)) $this->InitAllParam($app_const,$update);
         
      }
     
      //----------------------------------
      // add init father application constant
      if (file_exists(GetParam("CORE_PUBDIR",DEFAULT_PUBDIR)."/{$this->childof}/{$this->childof}_init.php")) {
        include("{$this->childof}/{$this->childof}_init.php");
        global $app_const;
        $this->InitAllParam(array_filter($app_const,"f_paramglog"),true);
      }

      if ($this->id > 1) {
	$this->SetParamDef("APPNAME",array("val"=>$name,
					   "kind"=>"static")); // use by generic application
	$this->SetParam("APPNAME",array("val"=>$name,
					"kind"=>"static")); // use by generic application

      }

     
     
    } else {
      $this->log->info ("No {$name}/{$name}.app available");
      return false;
    }
  }
     

  function UpdateApp() {
    $name=$this->name;
    $this->InitApp($name,TRUE);
  }

  // Update All available application
  function UpdateAllApp()
    {
  
      $query = new QueryDb($this->dbaccess,$this->dbtable);
      $query -> AddQuery("available = 'Y'");
      $allapp = $query->Query();
  
      while (list($k,$app)=each($allapp)) {
	$application = new Application($this->dbaccess, $app->id);
    
	$application->Set($app->name, $this->parent);
	$application->UpdateApp();
      }
  
    }
  function DeleteApp() {
  
    // delete acl
    $acl = new Acl($this->dbaccess);
    $acl->DelAppAcl($this->id);
  
  
    // delete actions
    $this->log->debug("Delete {$this->name}");
    $query = new QueryDb("","Action");
    $query->basic_elem->sup_where=array ("id_application = {$this->id}");
    $list = $query->Query();
  
    if ( $query->nb>0) {
      reset ($list);
      while (list($k,$v) = each($list)) {
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


  function Text($code, $args=NULL) {     
    if ($code == "") return "";  
    return _("$code");
  }

  // Write default ACL when new user is created
  function UpdateUserAcl($iduser)
    {
  
      $query = new QueryDb($this->dbaccess,$this->dbtable);
      $query -> AddQuery("available = 'Y'");
      $allapp = $query->Query();
      $acl = new Acl($this->dbaccess);
  
      while (list($k,$v)=each($allapp)) {
	$permission = new Permission($this->dbaccess);
	$permission->id_user=$iduser;
	$permission->id_application=$v->id;
    
	$privileges = $acl-> getDefaultAcls($v->id);
    
	while (list($k2,$aclid)=each($privileges)) {
	  $permission->id_acl=$aclid;
	  if (($permission->id_acl > 0) &&
	      (! $permission->Exists($permission->id_user,$v->id))) {
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
  function GetIdFromName($name) {
      $query = new QueryDb($this->dbaccess,$this->dbtable);
      $query -> AddQuery("name = '$name'");
      $app = $query->Query(0,0,"TABLE");
      if (is_array($app)) return $app[0]["id"];
      return 0;
    }

  function hasParent() {
    return (($this->parent != "") && ($this->parent !== $this) );
  }
}
?>
