<?
// ---------------------------------------------------------------------------
// PHP PROMAN Task Class
// ---------------------------------------------------------------------------
// anakeen 2000 - Marianne Le Briquer
// ---------------------------------------------------------------------------
// This program is free software; you can redistribute it and/or modify 
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or (at
//  your option) any later version.
//
// This program is distributed in the hope that it will be useful, but 
// WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
// or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
// for more details.
// 
// You should have received a copy of the GNU General Public License along 
// with this program; if not, write to the Free Software Foundation, Inc.,
// 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
// ---------------------------------------------------------------------------
//  $Id: Class.Application.php,v 1.8 2002/04/16 12:07:27 eric Exp $
//

$CLASS_APPLICATION_PHP = '$Id: Class.Application.php,v 1.8 2002/04/16 12:07:27 eric Exp $';
include_once('Class.DbObj.php');
include_once('Class.QueryDb.php');
include_once('Class.Action.php');
include_once('Class.Layout.php');
include_once('Class.Param.php');
include_once('Class.User.php');
include_once('Class.Permission.php');
include_once('Class.Lang.php');
include_once('Class.Style.php');
include_once('Lib.Http.php');
include_once('Lib.Common.php');

function N_($s) {return ($s);} // to tag gettext without change text immediatly

Class Application extends DbObj
{
var $fields = array ( "id",  "name","short_name",  "description",  "access_free",	 "available", "icon", "displayable", "with_frame", "childof","objectclass","ssl");

var $id_fields = array ( "id");

var $fulltextfields = array ("name","short_name","description");
var $sqlcreate = '
create table application ( 	id 	int not null,
     		primary key (id),
			name 	    varchar(20) not null,
			short_name varchar(30) ,
			description varchar(60) ,
			access_free  varchar(20),
			available  varchar(1),
                        icon varchar(30),
                        displayable varchar(1),
                        with_frame varchar(1),
                        childof varchar(20),
                        objectclass varchar(1),
                        ssl varchar(1));
create index application_idx1 on application(id);
create index application_idx2 on application(name);
create sequence SEQ_ID_APPLICATION start 10;
';

var $dbtable = "application";

var $def = array ( "criteria" => "",
                   "order_by" => "name"
                 );

var $criterias = array (
             "name" => array ("libelle" => "Nom",
                             "type" => "TXT")
                               );


var $parent="";

var $param;
 var $permission = ""; // permission object

var $jsref=array();
var $jscode=array();
var $logmsg=array();

var $cssref=array();
var $csscode=array();

function Set($name,&$parent)
{


  
  $this->log->debug("Entering : Set application to $name");


  $query=new QueryDb($this->dbaccess,"Application");
  $query->order_by = "";
  $query->criteria = "name";
  $query->operator = "=";
  $query->string = "'".$name."'";
  $list = $query->Query();
  if ($query->nb != 0) {
     $this=$list[0];
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
       global $HTTP_REFERER;
       Header("Location: $HTTP_REFERER");
     }
  }

  $this->param=new Param();
  $this->param->SetKey($this->id);

  $this->parent=&$parent;
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



  $this->InitStyle( );

}

function Complete() {
}

function SetSession(&$session) {
  $this->session=$session;
    // Set the user if possible
  if (is_object($this->session)) {
    if ($this->session->userid != 0) {
      $this->log->debug("Get user on ".$this->GetParam("CORE_USERDB"));
      $this->user = new User($this->GetParam("CORE_USERDB"),$this->session->userid);
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
    $this->id = $arr[0];
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

  $query->Query();

  return ($query->nb > 0);
}

function AddJsRef($ref) 
{
  // Js Ref are stored in the top level application
  $root = $this->Getparam("CORE_PUBDIR");
  if (file_exists($root."/".$this->name."/Layout/".$ref)) 
     $ref=$this->Getparam("CORE_PUBURL")."/".$this->name."/Layout/".$ref;
  if ($this->parent!="") {
     $this->parent->AddJsRef($ref);
  } else {
     (!isset($this->jscount) ? $this->jscount = 0 : $this->jscount++);
     $this->jsref[$this->jscount]=$ref;
     $this->log->debug("AddJsRef [{$this->jscount}] = <{$this->jsref[$this->jscount]}>");
  }
}

function AddJsCode($code) 
{
  // Js Code are stored in the top level application
  if ($this->parent!="") {
     $this->parent->AddJsCode($code);
  } else {
     $this->jscode[]=$code;
  }
}

function AddLogMsg($code) 
{
  // Js Code are stored in the top level application
  if ($this->parent!="") {
     $this->parent->AddLogMsg($code);
  } else {    
     $logmsg=$this->session->read("logmsg", array());
     $logmsg[]=strftime("%H:%M - ").str_replace("\n","\\n",addslashes(substr($code,0,80)));
     $this->session->register("logmsg",$logmsg);
  }
}
function GetJsRef() 
{
  if ($this->parent!="") {
    return($this->parent->GetJsRef());
  } else {
    return($this->jsref);
  }
}
   
function GetJsCode() 
{
  if ($this->parent!="") {
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
function AddCssRef($ref) 
{
  // Css Ref are stored in the top level application
  $root = $this->Getparam("CORE_PUBDIR");
  if (file_exists($root."/".$this->name."/Layout/".$ref)) 
     $ref=$this->Getparam("CORE_PUBURL")."/".$this->name."/Layout/".$ref;
  if ($this->parent!="") {
     $this->parent->AddCssRef($ref);
  } else {
     (!isset($this->csscount) ? $this->csscount = 0 : $this->csscount++);
     $this->cssref[$this->csscount]=$ref;
     $this->log->debug("AddCssRef [{$this->csscount}] = <{$this->cssref[$this->csscount]}>");
  }
}

function AddCssCode($code) 
{
  // Css Code are stored in the top level application
  if ($this->parent!="") {
     $this->parent->AddCssCode($code);
  } else {
     $this->csscode[]=$code;
  }
}
function GetCssRef() 
{
  if ($this->parent!="") {
    return($this->parent->GetCssRef());
  } else {
    return($this->cssref);
  }
}
   
function GetCssCode() 
{
  if ($this->parent!="") {
    return($this->parent->GetCssCode());
  } else {
    return($this->csscode);
  }
}
function HasPermission($acl_name) 
{
  if (!isset($this->user) || !is_object($this->user)) {
     $this->log->warning("Action {$this->parent->name}:{$this->name} requires authentification");
     return FALSE;
  }

  $acl=new Acl($this->dbaccess);
  if ( ! $acl->Set($acl_name,$this->id)) {
    $this->log->warning("Acl $acl_name not available for App $this->name");    
    return FALSE;
  }

  return($this->permission->HasPrivilege($acl->id));
}

function InitStyle()
{
  $this->style = new Style($this->dbaccess);
  $style = $this->param->Get("STYLE","");
  $this->style->Set($style,$this);
}

function InitText()
{
  
  // old init
  $this->text = new Lang($this->dbaccess);
  $this->text->SetEnv($this->id,
		      substr($this->Getparam("CORE_LANG"),0,2),
		      "en");

  // add parent text : CORE text
  if (isset($this->parent->id)) {
    $this->text->SetEnv($this->parent->id,
			substr($this->Getparam("CORE_LANG"),0,2),
			"en");
  }
}


function SetLayoutVars($lay) {
  if ($this->parent!="") {
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
  $app = "";
  if (file_exists($root."/".$this->name."/Images/".$img)) {
    return $root."/".$this->name."/Images/".$img;
  } else { // perhaps generic application
    if (file_exists($root."/".$this->childof."/Images/".$img)) {
      return $root."/".$this->name."/Images/".$img;
    } 
  }
  return false;
}
function GetImageUrl($img) {
  $root = $this->Getparam("CORE_PUBDIR");
  $app = "";
  if (file_exists($root."/".$this->name."/Images/".$img)) {
    $app = $this->name;
  } else { // perhaps generic application
    if (file_exists($root."/".$this->childof."/Images/".$img)) {
      $app = $this->childof;
    } 
  }
  if ($app != "") {
     $url = $this->style->GetImageUrl($img,
            $this->Getparam("CORE_PUBURL")."/".$app."/Images/".$img);
     return($url);
  }
  if ($this->parent != "") return($this->parent->getImageUrl($img));
  return  $this->style->GetImageUrl($img,
                     $this->Getparam("CORE_PUBURL")."/CORE/Images/noimage.png");
}

function GetLayoutFile($layname) {

  $nav=$this->session->Read("navigator");
  $ver=doubleval($this->session->Read("navversion"));

  $minver = 1000; // i think no more navigator version greater than 1000
  $laydir = $this->Getparam("CORE_PUBDIR")."/".$this->name."/Layout/";
  $file = $laydir.$layname; // default file

  if ($dir = @opendir($laydir)) {
    while ($xfile = readdir($dir)) {
      if (ereg($nav."-([0-9.]+)-".$layname,$xfile,$reg)) {
	$fver = doubleval($reg[1]); // file version 
	
	// search the file version  more or equal the navigator version
	// but if more than one try the nearest
	if ($fver >= $ver) {
	  if ($fver < $minver) { 
	    $file = $laydir.$xfile;
	    $minver = $fver;
	  }
	}
      }  
    }
    closedir($dir);
  }

  

  $file= $this->style->GetLayoutFile($layname,$file);
  if (file_exists($file)) {
     ###$file= $this->style->GetLayoutFile($layname,$file);
     return($file);
  } else {
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
  $this->param->Set($key,$val);
}

function SetVolatileParam($key,$val)
{
  $this->param->SetVolatile($key,$val);
}

function GetParam($key,$default="")
{ 
  if (!isset($this->param)) return ($default);
  if (($this->param->exists($key)) && isset($this->style)){
    return($this->style->GetParam($key,$this->param->Get($key)));
  }
  if ($this->parent!="") return($this->parent->GetParam($key,$default));
  return ($default);
}

function GetAllParam()
{
  $list=$this->param->buffer;
  if ($this->parent!="") {
    $list2 = $this->parent->GetAllParam();
    $list = array_merge($this->param->buffer,$list2);
  }
  $list3 = array_merge($list,$this->style->GetAllParam());
  return($list3);
}
  
function InitApp($name,$update=FALSE) {

  $this->log->info("Init : $name");
  if (file_exists("{$name}/{$name}.app")) {
     global $app_desc, $app_acl, $action_desc;

     // init global array
     $app_acl=array();
     $app_desc=array();
     $action_desc=array();
     include("{$name}/{$name}.app");

     if (sizeof($app_desc)>0) {
       if ($update) {
         $app=&$this;
       } else {
         $this->log->debug("InitApp :  new application ");
         $app = new Application($this->dbaccess);
       }
       reset($app_desc);
       while (list($k,$v) = each ($app_desc)) {
         $app->$k = $v;
       }
       $app->available = "Y";
       if ($update) {
         $app->Modify();
       } else {
         $app->Add();
         $this=$app;
         $this->param=new Param();
         $this->param->SetKey($this->id);
       }
     } else {
       die ("can't init $name");
     }

     // init acl
     $acl = new Acl($this->dbaccess);
     $acl->Init($app,$app_acl,$update);

     // init actions
     $action = new Action($this->dbaccess);
     $action->Init($app,$action_desc,$update);


     // init father if has
     if ($app->childof != "") {
       
       // init ACL & ACTION
       $app_acl=array();
       $action_desc=array();
       include("{$this->childof}/{$this->childof}.app");
       
       // init acl
       $acl = new Acl($this->dbaccess);
       $acl->Init($app,$app_acl,$update);

       // init actions
       $action = new Action($this->dbaccess);
       $action->Init($app,$action_desc,$update);
       
     }


     // init father application constant
     if (file_exists("{$this->childof}/{$this->childof}_init.php")) {
        include("{$this->childof}/{$this->childof}_init.php");
        global $app_const;
        if (isset($app_const)) {
          reset($app_const);
          while (list($k,$v) = each ($app_const)) {
	    if ($update) { // don't modify old parameters
	      if ($this->GetParam($k) == "")		
		$this->SetParam($k,$v);// set only new parameters
	    } else {
	      $this->SetParam($k,$v);
	    }
          }
        }
     }

     // init application constant
     if (file_exists("{$name}/{$name}_init.php")) {
        include("{$name}/{$name}_init.php");
        global $app_const;
        if (isset($app_const)) {
          reset($app_const);
          while (list($k,$v) = each ($app_const)) {
	    if ($update) { // don't modify old parameters
	      if ($this->GetParam($k) == "")		
		$this->SetParam($k,$v);// set only new parameters
	    } else {
	      $this->SetParam($k,$v);
	    }
          }
        }
     }
     
     
     $this->SetParam("APPNAME",$name); // use by generic application

     // Load app texts catalog
     if (file_exists("{$name}/{$name}_txt.php")) {
       include("{$name}/{$name}_txt.php");
       global $texts;

       // Load generic app texts catalog
       if (file_exists("{$this->childof}/{$this->childof}_txt.php")) {
	 $text1 = $texts;
	 include("{$this->childof}/{$this->childof}_txt.php");
	 $texts = array_merge($text1, $texts);	 	 
       }

       if (isset($texts)) {
	 $lang = new Lang();
	 reset($texts);
	 while (list($k, $v) = each($texts)) {
	   while (list($kl, $vl) = each($v)) {
	     $lang->store($this->id, $k, $kl, $vl);
	   }
	 }
       } else {
	 $this->log->Debug("Pas de catalogue de messages");
       }
     } else {
       $this->log->Debug("Pas de catalogue de messages");
     }
     
  } else {
    die ("No {$name}/{$name}.app available");
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

  // Delete lang catalog
  $lang = new Lang();
  $lang->deletecatalog($this->id);
  
  // delete application
  $this->Delete();
}


function Text($code, $args=NULL) {
  $set = false;


  if (!isset($this->text->buffer)) $this->InitText( );

  if ($code == "") return "";

  if ($this->text->GetText($code)) {
	$set = true;
      }


  if (!$set) {

    $this->text->fmttxt = _("$code");

  }
  return $this->text->fmttxt;
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

function GetIdFromName($name)
{
  $query = new QueryDb($this->dbaccess,$this->dbtable);
  $query -> AddQuery("name = '$name'");
  $app = $query->Query();
  if (is_array($app)) {
    return $app[0]->id;
  }
  return 0;
}

}
?>
