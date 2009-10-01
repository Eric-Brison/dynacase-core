<?php
/**
 * Action Class
 *
 * @author Anakeen 2000 
 * @version $Id: Class.Action.php,v 1.40 2008/03/10 15:09:17 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

include_once('Class.DbObj.php');
include_once('Class.User.php');
include_once('Class.QueryDb.php');
include_once('Class.Application.php');

define ("THROW_EXITERROR",1968);

Class Action extends DbObj
{
  var $fields = array ( "id","id_application","name","short_name","long_name","script","function","layout","available","acl","grant_level","openaccess","root","icon","toc","father","toc_order");


  var $id_fields = array ( "id");

  var $idx = array ("id","id_application","name");

  var $dbtable = "action";

  var $sqlcreate = '
create table action (id int not null,
                   primary key (id),
                   id_application int not null,
                   name varchar(30) not null,
                   short_name text ,
                   long_name text, 
                   script text,
                   function text,
                   layout text ,
                   available varchar(3),
                   acl varchar(20),
                   grant_level int,
                   openaccess  char,
                   root char,
                   icon varchar(100),
                   toc  char,
                   father int ,
                   toc_order int);
create index action_idx1 on action(id);
create index action_idx2 on action(id_application);
create index action_idx3 on action(name);
create sequence SEQ_ID_ACTION;
                 ';

  var $parent;

  var $def = array ( "criteria" => "",
		     "order_by" => "name"
		     );

  var $criterias = array (
			  "name" => array ("libelle" => "Nom",
					   "type" => "TXT")
			  );

  var $grant_level=0;

  function Set($name,&$parent)
  {
  
    $query=new QueryDb($this->dbaccess,"Action","TABLE");
    if ($name!="") {
      $name=pg_escape_string($name);
      $query->basic_elem->sup_where = array ("name='$name'","id_application={$parent->id}");
    } else {
      $query->basic_elem->sup_where = array ("root='Y'","id_application={$parent->id}");
    }
    $query->Query(0,0,"TABLE");
    if ($query->nb > 0) {
      $this->Affect($query->list[0]);
      $this->log->debug("Set Action to {$this->name}");
    } else {    
      $err = sprintf(_("function '%s' not available for application %s (%d)"), $name, $parent->name, $parent->id);
      print $err;
      exit;
    }
  
    $this->CompleteSet($parent);
  }

  function CompleteSet(&$parent) {
    $this->parent=&$parent;
    if ($this->script=="") $this->script=strtolower($this->name).".php";
    if ($this->layout=="") $this->layout=strtolower($this->name).".xml";
    if ($this->function=="") $this->function = substr($this->script,0,strpos($this->script,'.php'));
   
    $this->session=&$parent->session;

    $this->user= &$parent->user;
    // Set the hereurl if possible
    $this->url = $this->GetParam("CORE_BASEURL")."app=".$this->parent->name."&action=".$this->name;

    // Init a log attribute
    $this->log->loghead=sprintf("%s %s [%d] - ",$this->user->firstname, $this->user->lastname, $this->user->id);
    $this->log->function=$this->name;
    $this->log->application=$this->parent->name;
    return "";
  }

  function Complete() 
  {
  }

  function Read($k, $d="") {
    if (is_object($this->session)) {
      return($this->session->Read($k, $d));
    }
    return($d."--");
  }

  function Register($k,$v) {
    if (isset($this->session) && is_object($this->session)) {
      return($this->session->Register($k,$v));
    }
  }
  
  function Unregister($k) {
    if (is_object($this->session)) {
      return($this->session->Unregister($k));
    }
  }

  function ActRead($k, $d="") {
    return($this->Read("{$this->id}_".$k, $d));
  }

  function ActRegister($k,$v) {
    return($this->Register("{$this->id}_".$k,$v));
  }
  
  function ActUnregister($k) {
    return($this->Unregister("{$this->id}_".$k));
  }

  function PreInsert( )
  {
    if ($this->Exists( $this->name, $this->id_application)) return "Action {$this->name} already exists...";  
    $msg_res = $this->exec_query("select nextval ('seq_id_action')");
    $arr = $this->fetch_array(0);
    $this->id = $arr["nextval"];
  
  }
  function PreUpdate()
  {
    if ($this->dbid == -1) return FALSE;
    if ($this->Exists( $this->name,$this->id_application,$this->id)) return "Action {$this->name} already exists...";    
  }

  function GetParam($name, $def="") {
    if (isset ($this->parent)) {
      return($this->parent->GetParam($name, $def));
    }
  }
  
 function setParamU($name, $val) {
    if (isset ($this->parent)) {
      return($this->parent->setParamU($name, $val));
    }
  }
  function GetImageUrl($name) {
    if (isset ($this->parent)) {
      return($this->parent->GetImageUrl($name));
    }
  }

  function GetFilteredImageUrl($name) {
    if (isset ($this->parent)) {
      return($this->parent->GetFilteredImageUrl($name));
    }
  }

  function GetImageFile($name) {
    if (isset ($this->parent)) {
      return($this->parent->GetImageFile($name));
    }
  }


  function AddLogMsg($msg,$cut=80) {
    if (isset ($this->parent)) {
      return($this->parent->AddLogMsg($msg,$cut));
    }
  }

  function AddWarningMsg($msg) {
    if (isset ($this->parent)) {
      return($this->parent->AddWarningMsg($msg));
    }
  }

  /**
   * store action done to be use in refreshing main window interface
   * @param string $actdone the code of action
   * @param string $args the argument of action
   */
  function AddActionDone($actdone,$arg="") {
    if ($actdone != "") {
	$sact=$this->session->read("actdone_name", array());
	$sarg=$this->session->read("actdone_arg", array());
	$sact[]=$actdone;
	$sarg[]=$arg;
	$sact=$this->session->register("actdone_name",$sact);
	$sarg=$this->session->register("actdone_arg", $sarg);
    }
  }
  /**
   * clear action done to be use in refreshing main window interface
   * @param string $actdone the code of action
   * @param string $args the argument of action
   */
  function ClearActionDone() {
    $this->session->unregister("actdone_name");
    $this->session->unregister("actdone_arg");    
  }
  /**
   * get action code and argument for action code done 
   * to be use in refreshing main window interface
   * @param string &$actdone the code of action
   * @param string &$args the argument of action
   */
  function GetActionDone(&$actdone,&$arg) {
    $actdone=$this->session->read("actdone_name", array());
    $arg=$this->session->read("actdone_arg", array());
  }
  function GetIcon($name,$text,$width="",$height="") {
  
    if ($width != "")
      $width = "width = \"".$width."\"";
    if ($height != "")
      $height = "height = \"".$height."\"";
  
    return("<img border=0 ".$width." ".$height." src=\"".
	   $this->GetImageUrl($name).
	   "\" title=\"".
	   $this->text($text).
	   "\" alt=\"".
	   $this->text($text).
	   "\">"); 
  }


  function GetLayoutFile($layname) {
    if (isset ($this->parent)) return($this->parent->GetLayoutFile($layname));
  }

  function Exists($name,$idapp,$id_func='')
  {
    if ($idapp=='') return false;
    $query=new QueryDb($this->dbaccess,"Action");

    if ($id_func!='') {
      $query->basic_elem->sup_where = array ("name='$name'","id!=$id_func",
					     "id_application=$idapp");

    } else {
      $query->basic_elem->sup_where = array ("name='$name'",
					     "id_application=$idapp");
    }

    $query->Query();

    return ($query->nb > 0);
  }

  function HasPermission($acl_name="",$app_name="")
  {
    if ($acl_name == "") return(true); // no control for this action
    return($this->parent->HasPermission($acl_name,$app_name));
  }
  /** 
   * return true if user can execute the specified action
   * @param string $actname action name
   * @param string $appid application name or id (default itself)
   * @return string error message (empty if no error)
   *
   */
  function canExecute($actname,$appid="") {
    

    if ($this->user->id==1) return;
    if ($appid=="") $appid=$this->parent->id;
    elseif (! is_numeric($appid)) $appid=$this->parent->GetIdFromName($appid);

    $aclname=$this->getAcl($actname,$appid);
    if (!$aclname) return; // no control
    $acl=new Acl($this->dbaccess);
    if ( ! $acl->Set($aclname,$appid)) {
      return sprintf(_("Acl [%s] not available for App %s"),$aclname,$appid);
    }
    $p = new Permission($this->dbaccess,array($this->user->id, $appid));
    if (! $p->HasPrivilege($acl->id)) return sprintf("no privilege %s for %s %s",$aclname,$appid,$actname);
  }

  /**
   * return id from name for an application
   * @param string $actname action name
   * @param string $appid application id (default itself)
   * @return string (false if not found)
   */
  function GetAcl($actname,$appid="") {
    if ($appid=="") $appid=$this->parent->id;
    $query = new QueryDb($this->dbaccess,$this->dbtable);
    $query -> AddQuery("name = '$actname'");
    $query -> AddQuery("id_application = $appid");
    $q = $query->Query(0,0,"TABLE");
    if (is_array($q)) return $q[0]["acl"];
    return false;
  }

  /**
   * execute the action
   * @return string the composed associated layout
   */
  function execute() {
 
    // If no parent set , it's a misconfiguration
    if (!isset($this->parent)) return;

    if ($this->auth && $this->auth->parms["type"]=="open") {
      if ($this->openaccess != 'Y') {
	$allow=false;
	if ($this->auth->token && $this->auth->token["context"]) {
	  print $this->auth->token->context;
	  //$this->exitForbidden('may be open');
	  $context=unserialize($this->auth->token["context"]);
	  if (is_array($context) && (count($context) > 0)) {
	    $allow=true;
	    foreach ($context as $k=>$v) {
	      if (getHttpVars($k)!=$v) {
		$allow=false;
	      }
	    }
	    if (! $allow) $this->exitForbidden(sprintf(_("action %s is not declared to be access in open mode and token context not match"),$this->name));
	  }
	} 
	if (! $allow) $this->exitForbidden(sprintf(_("action %s is not declared to be access in open mode"),$this->name));
      }
    }
    // check if this action is permitted
    if (!$this->HasPermission($this->acl)) { 
      $this->ExitError(sprintf(_("Access denied\nNeed ACL %s for action %s [%s]"),
			       $this->acl,$this->short_name,$this->name));   
    }
  
    if ($this->id>0) {
      global $QUERY_STRING;    
      $this->log->info("{$this->parent->name}:{$this->name} [".substr($QUERY_STRING,48)."]");

    }
    // Memo last application to return case of error
    $err = $this->Read("FT_ERROR","");
    if ($err == "") {
      if ($this->parent->name != "CORE") {
	$this->register("LAST_ACT",$this->parent->name);
      }
    }
    $this->log->push("{$this->parent->name}:{$this->name}");
    $pubdir = $this->parent->GetParam("CORE_PUBDIR");
    $nav=$this->Read("navigator");
    if ($this->layout != "") {
    
      $layout=$this->GetLayoutFile( $this->layout);
    
    } else {
      $layout = "";
    } 
    $this->lay = new Layout($layout,$this);
    if (isset($this->script) && $this->script!="") {
      $script = $pubdir."/".$this->parent->name."/".$this->script;
      if (!file_exists($script)) // try generic application
	$script = $pubdir."/".$this->parent->childof."/".$this->script;
      
    
      if (file_exists($script)) {
	include_once($script);
	$call = $this->function;
	$call($this);
      } else {
	$this->log->debug("$script does not exist");
      }
    } else {
      $this->log->debug("No script provided : No script called");
    }

    // Is there any error messages
    $err = $this->Read($this->parent->name."_ERROR","");
    if ($err != "") {
      $this->lay->Set("ERR_MSG",$err);
      $this->Unregister($this->parent->name."_ERROR");
    } else {
      $this->lay->Set("ERR_MSG","");
    }

   

    $out = $this->lay->gen();
    $this->log->pop();

    return($out);
  }

  /**
   * display error to user and stop execution
   * @param string $texterr the error message
   */
  function ExitError($texterr)  {
    if ($_SERVER['HTTP_HOST'] != "") {
      //      redirect($this,"CORE&sole=Y","ERROR");
      $this->lay=new Layout("CORE/Layout/error.xml",$this);
      $this->lay->set("error",$texterr);
      $this->lay->set("serror",str_replace("\n","\\n",addslashes($texterr)));
      $this->lay->set("appname",$this->parent->name);
      $this->lay->set("appact",$this->name);
      if ($this->parent && $this->parent->parent) { // reset js ans ccs
	$this->parent->parent->cssref=array();
	$this->parent->parent->jsref=array();
      }
      print $this->lay->gen();
      exit;
    } else {    
      throw new Exception($texterr,THROW_EXITERROR);   
    }
  }

  function exitForbidden($texterr) {    
    header("HTTP/1.0 401 Authorization Required ");
    header("HTTP/1.0 301 Access Forbidden ");
    print $texterr;
    exit;
  }
  /**
   * unregister FT error 
   */
  function ClearError() {
    $this->Unregister("FT_ERROR");
    $this->Unregister("FT_ERROR_ACT");
  }

  function Init($app,$action_desc,$update=FALSE)
  {
    if (sizeof($action_desc) == 0) {
      $this->log->info("No action available");
      return("");
    }
    $father[0]="";

    foreach ($action_desc as $k=>$node) {
      // set some default values
      $action=new Action($this->dbaccess);
      $action->root="N";
      $action->available="Y";
      $action->id_application=$app->id;
      $action->toc="N";

      // If the action already exists ,set it
      if ($action->Exists($node["name"],$app->id)) {
	$action->Set($node["name"],$app);
      }
      reset($node);
      while (list($k,$v)=each($node)) {
	$action->$k = $v;
      }
  
      // Get the acl grant level
      $acl = new Acl($this->dbaccess);
      if (isset($action->acl)) {
	$acl->Set($action->acl,$action->id_application);
	$action->grant_level=$acl->grant_level;
      } else {
	$action->grant_level=0;
      }

      // set non set values if possible
      if ($action->long_name=="") $action->long_name=$action->short_name;
      if ($action->script=="") $action->script=strtolower($action->name).".php";
      if ($action->layout=="") $action->layout=strtolower($action->name).".xml";
      if (!isset($action->level)) $action->level=0;
  
    
      $action->father=$father[$action->level];
      if ($action->Exists($node["name"],$app->id)) {
	$this->log->info("Update Action ".$node["name"]);
	$action->Modify();
      } else {
	$action->Add();
	$this->log->info("Create Action ".$node["name"]);
      }
      $father[$action->level+1]=$action->id;
    }

    // if update , remove unused actions
    if ($update) {
      $query=new QueryDb($this->dbaccess,"Action");
      $query->basic_elem->sup_where=array ("id_application = {$app->id}");
      $list=$query->Query();
      while(list($k,$act) =each($list)) {
	$find=FALSE;
	reset($action_desc);
	while ((list($k2,$v2) = each($action_desc)) && (!$find)) {
	  $find=( $v2["name"] == $act->name );
	}
	if (!$find) {
	  // remove the action
	  $this->log->info("Delete Action ".$act->name);
	  $act->Delete();
	}
      }
    }
  }


  function Text($code, $args=NULL) {  
    if ($code == "") return "";  
    return _("$code");
  }

  // Log functions
  function debug($msg) {
    $this->log->debug($msg);
  }
  function info($msg) {
    $this->log->info($msg);
  }
  function warning($msg) {
    $this->log->warning($msg);
  }
  function error($msg) {
    $this->log->error($msg);
  }
  function fatal($msg) {
    $this->log->fatal($msg);
  }

  /**
   * verify if the application is really installed in localhost
   * @return bool true if application is installed
   */
  function AppInstalled($appname) {
  
    $pubdir = $this->parent->GetParam("CORE_PUBDIR");
  
    return (@is_dir($pubdir."/".$appname));
  }


  /**
   * return available Applications for current user
   * @return array
   */
  function GetAvailableApplication() {
    
    $query=new QueryDb($this->dbaccess,"Application");
    $query->basic_elem->sup_where=array("available='Y'","displayable='Y'");
    $list = $query->Query(0,0,"TABLE");
    $tab = array();
    if ($query->nb > 0) {
      $i=0;
      foreach($list as $k=>$appli) {
	if ($appli["access_free"] == "N") {
       
	  if (isset($this->user)) {
	    if ($this->user->id != 1) { // no control for user Admin
	   
	      //if ($p->id_acl == "") continue;

	      // test if acl of root action is granted
	  
	  
	      // search  acl for root action
	      $queryact=new QueryDb($this->dbaccess,"Action");
	      $queryact->AddQuery("id_application=".$appli["id"]);
	      $queryact->AddQuery("root='Y'");
	      $listact = $queryact->Query(0,0,"TABLE");
	      $root_acl_name=$listact[0]["acl"];
	      if (! $this->HasPermission($root_acl_name,$appli["id"])) continue;
	    }
	  
	  } else { continue; }
	}
	$appli["description"]= $this->text($appli["description"]); // translate
	$appli["iconsrc"]=$this->GetImageUrl($appli["icon"]);
	if ($appli["iconsrc"]=="CORE/Images/noimage.png") $appli["iconsrc"]=$appli["name"]."/Images/".$appli["icon"];

	$tab[$i++]=$appli;
      }
    }
    return $tab;
  }
}
?>
