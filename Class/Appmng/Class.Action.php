<?
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
//  $Id: Class.Action.php,v 1.2 2002/01/10 11:11:56 eric Exp $
//  $Log: Class.Action.php,v $
//  Revision 1.2  2002/01/10 11:11:56  eric
//  modif pour pour authentification sur perte de session
//
//  Revision 1.1  2002/01/08 12:41:34  eric
//  first
//
//  Revision 1.48  2002/01/04 16:34:22  eric
//  precision msg erreur
//
//  Revision 1.47  2001/11/28 15:12:58  eric
//  correction pour ajout arg. sur connexion lors de perte de session
//
//  Revision 1.46  2001/11/21 16:05:15  eric
//  correction hauteur sur getIcon
//
//  Revision 1.45  2001/11/14 15:15:14  eric
//  ecriture sur stdout fonction non possible si inexistante
//
//  Revision 1.44  2001/10/17 09:08:49  eric
//  mise en place de i18n via gettext
//
//  Revision 1.43  2001/10/05 15:30:36  eric
//  modif pour aspect exiterror
//
//  Revision 1.42  2001/08/31 09:27:01  eric
//  ajout possibilité de choix layout par version de navigateur
//
//  Revision 1.41  2001/08/29 15:50:45  yannick
//  See Changelog
//
//  Revision 1.40  2001/08/29 13:26:24  eric
//  ajout tag TITLE pour afficher les popup image
//
//  Revision 1.39  2001/08/20 16:41:38  eric
//  changement des controles d'accessibilites
//
//  Revision 1.38  2001/08/10 08:07:34  eric
//  ajout fonction ExitError pour action CORE ERROR
//
//  Revision 1.37  2001/07/05 10:35:48  eric
//  correction erreur de guillemet dans geticon
//
//  Revision 1.36  2001/06/22 08:42:09  eric
//  gestion application générique
//
//  Revision 1.35  2001/06/14 14:53:52  eric
//  modif param du redirect suite au multi frame
//
//  Revision 1.34  2001/02/26 13:50:57  yannick
//  Optimization
//
//  Revision 1.33  2001/02/09 17:32:42  yannick
//  Anomalies diverses
//
//  Revision 1.32  2001/02/07 11:30:44  yannick
//  Traitement résultat vide, 1 seule page
//
//  Revision 1.31  2001/02/06 16:23:28  yannick
//  QueryGen : first release
//
//  Revision 1.30  2001/02/06 11:52:19  marianne
//  prise en compte du navigateur
//
//  Revision 1.29  2001/01/25 17:17:03  yannick
//  Gestion des updates applications
//
//  Revision 1.28  2001/01/19 01:47:44  marianne
//  Prise en compte des styles
//
//  Revision 1.27  2000/11/13 11:40:19  marc
//  Action : retour $def sur GetParam....
//  Domain : selection domaine local.
//
//  Revision 1.26  2000/11/10 14:02:13  yannick
//  Version 0.2.0 et insertion des actions
//
//  Revision 1.25  2000/11/08 11:04:13  marc
//  Trace for unauthorized access
//
//  Revision 1.24  2000/10/26 19:43:20  marc
//  isser -> isset
//
//  Revision 1.23  2000/10/26 18:18:13  marc
//  - Gestion des references multiples à des JS
//  - Gestion de variables de session
//
//  Revision 1.22  2000/10/26 16:05:21  yannick
//  test user
//
//  Revision 1.21  2000/10/26 15:18:51  yannick
//  Ajout du Unregister sur Action
//
//  Revision 1.20  2000/10/26 14:10:27  yannick
//  Suite au login/domain => Modelage des sessions
//
//  Revision 1.19  2000/10/26 12:52:13  yannick
//  Bug : perte du mot de passe
//
//  Revision 1.18  2000/10/24 21:16:42  marc
//  Retour demande si pas de variable de session touvee
//
//  Revision 1.17  2000/10/23 14:13:45  yannick
//  Contrôle des accès
//
//  Revision 1.16  2000/10/23 09:07:36  marc
//  Ajout des sessions dans Action
//
//  Revision 1.15  2000/10/21 16:40:50  yannick
//  Gestion blocks imbriqués
//
//  Revision 1.14  2000/10/19 17:07:10  yannick
//  *** empty log message ***
//
//  Revision 1.13  2000/10/19 16:34:45  yannick
//  Pour Marc
//
//  Revision 1.12  2000/10/19 10:15:13  marc
//  Finalisation de l'internationalisation
//
//  Revision 1.11  2000/10/18 19:55:43  marc
//  Internationalisation
//
//  Revision 1.10  2000/10/18 16:11:21  yannick
//  Pb d'init sur base vide CORE = id 1
//
//  Revision 1.9  2000/10/18 14:55:34  yannick
//  Prise en compte des références
//
//  Revision 1.8  2000/10/16 17:23:07  yannick
//  utilisation preg dans Layout et ménage dans les logs
//
//  Revision 1.7  2000/10/13 14:10:49  yannick
//  Ajout de GetLayoutFile et possibilite des Get* dans Action
//
//  Revision 1.6  2000/10/11 16:31:51  yannick
//  Nouvelle gestion de l'init App et Action
//
//  Revision 1.5  2000/10/11 13:09:47  yannick
//  Mise au point Authentification/Session
//
//  Revision 1.4  2000/10/11 12:18:41  yannick
//  Gestion des sessions
//
//  Revision 1.3  2000/10/10 19:03:40  marc
//  Mise au point
//
//  Revision 1.2  2000/10/06 14:01:10  yannick
//  Ajout des règles de codage
//
//  Revision 1.1.1.1  2000/10/05 17:29:10  yannick
//  Importation
//
// ---------------------------------------------------------------------------
// ---------------------------------------------------------------------------
//
$CLASS_PAGE_PHP = '$Id: Class.Action.php,v 1.2 2002/01/10 11:11:56 eric Exp $';
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
                   short_name varchar(40) ,
                   long_name varchar(100), 
                   script varchar(100),
                   function varchar(100),
                   layout varchar(100) ,
                   available varchar(3),
                   acl varchar(20),
                   grant_level int,
                   root varchar(1),
                   icon varchar(100),
                   toc  varchar(1),
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
  $this->parent=&$parent;
  if ($this->function=="") $this->function = substr($this->script,0,strpos($this->script,'.php'));
  $this->session=&$parent->session;

  $this->user= &$parent->user;
  // Set the hereurl if possible
  $this->url = $this->GetParam("CORE_BASEURL")."app=".$this->parent->name."&action=".$this->name;

  // Init a log attribute
  $this->logaction = new Log("",$this->parent->name,$this->name);
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

function GetParam($key, $def="") {
  if (isset ($this->parent)) {
   return($this->parent->GetParam($key, $def));
  }
}

function GetImageUrl($name) {
  if (isset ($this->parent)) {
   return($this->parent->GetImageUrl($name));
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
    global $HTTP_REFERER;
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
  $this->Register("FT_ERROR",addslashes($texterr));
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
}
?>
