<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Class.Action.php,v 1.17 2003/12/09 10:46:46 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

// ---------------------------------------------------------------------------
// anakeen 2000 - Yannick Le Briquer
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
//  $Id: Class.Action.php,v 1.17 2003/12/09 10:46:46 eric Exp $
// ---------------------------------------------------------------------------
//
$CLASS_PAGE_PHP = '$Id: Class.Action.php,v 1.17 2003/12/09 10:46:46 eric Exp $';
include_once('Class.DbObj.php');
include_once('Class.User.php');
include_once('Class.QueryDb.php');
include_once('Class.Application.php');

Class Action extends DbObj
{
var $fields = array ( "id","id_application","name","short_name","long_name","script","function","layout","available","acl","grant_level","root","icon","toc","father","toc_order");


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
  
    $query=new QueryDb($this->dbaccess,"Action");
    if ($name!="") {
      $query->basic_elem->sup_where = array ("name='$name'","id_application={$parent->id}");
    } else {
      $query->basic_elem->sup_where = array ("root='Y'","id_application={$parent->id}");
    }
    $query->Query();

    if ($query->nb > 0) {
      $this = $query->list[0];
      $this->log->debug("Set Action to {$this->name}");
    } else {
    
      $err = sprintf(_("function '%s' not available for application %s (%d)"), $name, $parent->name, $parent->id);
      print $err;
      exit;
    }
  
  $this->CompleteSet(&$parent);
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
  $this->logaction = new Log("",$this->parent->name,$this->name);



  return "";
}

function Complete() 
{
}

function Read($k, $d="") {
  if (is_object($this->session)) {
    return($this->session->Read($k, $d));
  }
  return($d);
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
  $arr = $this->fetch_array (0);
  $this->id = $arr[0];
  
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

function GetImageUrl($name) {
  if (isset ($this->parent)) {
   return($this->parent->GetImageUrl($name));
  }
}

function GetImageFile($name) {
  if (isset ($this->parent)) {
   return($this->parent->GetImageFile($name));
  }
}


function AddLogMsg($msg) {
  if (isset ($this->parent)) {
   return($this->parent->AddLogMsg($msg));
  }
}

function AddWarningMsg($msg) {
  if (isset ($this->parent)) {
   return($this->parent->AddWarningMsg($msg));
  }
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

function HasPermission($acl_name="")
{
  if ($acl_name == "") return(true); // no control for this action
  return($this->parent->HasPermission($acl_name));
}

function execute()
{
 
  // If no parent set , it's a misconfiguration
  if (!isset($this->parent)) return;

  // check if this action is permitted
  if (!$this->HasPermission($this->acl)) { 
    if ($this->session->status == $this->session->SESSION_CT_ACTIVE) {
      $this->ExitError(_("Access denied"));
    } else {
      //$this->ExitError(_("Invalid Session"));
      global $HTTP_GET_VARS;
      $getargs="";
      while (list($k, $v) =each($HTTP_GET_VARS)) {
	if ( ($k != "session") &&
	     ($k != "app") &&
	     ($k != "sole") &&
	     ($k != "action") )
	$getargs .= "&".$k."=".$v;
      }

      Redirect($this,"AUTHENT",
               "LOGINFORM&appd=".$this->parent->name."&actd=".$this->name."&argd=".urlencode($getargs), 
               $this->parent->GetParam("CORE_BASEURL"));
    }

  }
  
  if ($this->id>0) {
    global $QUERY_STRING;
    $suser = sprintf("%s %s [%d] - ",$this->user->firstname, $this->user->lastname, $this->user->id);
    $this->log->info("$suser{$this->parent->name}:{$this->name} [".substr($QUERY_STRING,48)."]");

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

  // Memo last application to return case of error
  $err = $this->Read("FT_ERROR","");
  if ($err == "") {
    if ($this->parent->name != "CORE") {
      $this->register("LAST_ACT",$this->parent->name);
    }
  }

  $out = $this->lay->gen();
  $this->log->pop();

  return($out);
}

// display  error to user
function ExitError($texterr)
{
  $this->Register("FT_ERROR",$texterr);
  $this->Register("FT_ERROR_APP",$this->parent->name);
  $this->Register("FT_ERROR_ACT",$this->name);

  redirect($this,"CORE&sole=Y","ERROR");
  exit;
}
// unregister FT error 
function ClearError()
{
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
  reset($action_desc);
  while (list($k,$node)= each ($action_desc)) {
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
  if (isset ($this->parent)) {
   return($this->parent->Text($code, $args));
  }
}

// Log functions
function debug($msg) {
  $this->logaction->debug($msg);
}
function info($msg) {
  $this->logaction->info($msg);
}
function warning($msg) {
  $this->logaction->warning($msg);
}
function error($msg) {
  $this->logaction->error($msg);
}
function fatal($msg) {
  $this->logaction->fatal($msg);
}

// verify if the application is really installed in localhost
function AppInstalled($appname) {
  
  $pubdir = $this->parent->GetParam("CORE_PUBDIR");
  
  return (@is_dir($pubdir."/".$appname));
}
}
?>
