<?php
/**
 * Parameters values
 *
 * @author Anakeen 2000 
 * @version $Id: Class.Param.php,v 1.24 2006/11/16 17:06:24 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */


include_once('Class.Log.php');
include_once('Class.DbObj.php');
include_once('Class.ParamDef.php');


define("PARAM_APP","A");
define("PARAM_GLB","G");
define("PARAM_USER","U");
define("PARAM_STYLE","S");

Class Param extends DbObj
{
var $fields = array ("name","type","appid","val");

var $id_fields = array ("name","type","appid");

var $dbtable = "paramv";

var $sqlcreate = '
      create table paramv (
              name   varchar(50),
              type   varchar(21),
              appid  int4,
              val    text);
      create index paramv_idx2 on paramv(name);
      create unique index paramv_idx3 on paramv(name,type,appid);
                 ';

var $buffer=array();
   
function PreInsert( )
{
    if (strpos($this->name," ")!=0) {
      return "Le nom du paramètre ne doit pas contenir d'espace";
    }
}
 function PostInit() {
   $opd=new Paramdef();
   $opd->create();
 }
function PreUpdate( )
{
   $this->PreInsert(); 
}

function SetKey($appid,$userid,$styleid="0") {
  $this->appid=$appid;
  $this->buffer=array_merge($this->buffer,$this->GetAll($appid,$userid,$styleid));
}

function Set($name,$val,$type=PARAM_GLB,$appid='')
{
  $this->name = $name;
  $this->val = $val;
  $this->type = $type;
  $pdef = new paramdef($this->dbaccess,$name);

  if ($pdef->isAffected()) {
    if ($pdef->isglob=='Y') $appid=$pdef->appid;
  }
  $this->appid = $appid;


  $paramt = new Param($this->dbaccess,array($name,$type,$appid));
  if ($paramt->isAffected()) $this->Modify();
  else $this->Add();

  $this->buffer[$name]=$val;
  unset($_SESSION["sessparam".$this->appid]);
}

function SetVolatile($name,$val)
{
   $this->buffer[$name]=$val;
}

function Get($name,$def="")
{
   if (isset($this->buffer[$name])) {
     return ($this->buffer[$name]);
   } else {
     return ($def);
   }
}
   
function GetAll($appid="",$userid,$styleid="0")
{
   if ($appid=="") $appid=$this->appid;
   $psize = new Param($this->dbaccess,array("FONTSIZE",PARAM_USER.$userid,"1"));
   if ($psize->val != '')  $size=$psize->val;
   else $size='normal';
   $size='SIZE_'.strtoupper($size);
   $query = new QueryDb($this->dbaccess,"Param");
   if ($userid) {
   $list = $query->Query(0,0,"TABLE","select distinct on(paramv.name) paramv.* from paramv left join paramdef on (paramv.name=paramdef.name) where ". 
			 
			 "(paramv.type = '".PARAM_GLB."') ".
			 " OR (paramv.type='".PARAM_APP."' and paramv.appid=$appid)".
			 " OR (paramv.type='".PARAM_USER.$userid."' and paramv.appid=$appid)".
			 " OR (paramv.type='".PARAM_USER.$userid."' and paramdef.isglob='Y')".
			 " OR (paramv.type='".PARAM_STYLE.$styleid."' and paramv.appid=$appid)".
			 " OR (paramv.type='".PARAM_STYLE.$styleid."' and paramdef.isglob='Y')".
			 " OR (paramv.type='".PARAM_STYLE.$size."')".
			 " order by paramv.name, paramv.type desc");
   } else {
   $list = $query->Query(0,0,"TABLE","SELECT * from paramv where type='G' or (type='A' and appid=$appid);");
     
   }
   $out=array();
   if ($query->nb != 0) {
     while(list($k,$v)=each($list)) {
       $out[$v["name"]]=$v["val"];
     }
   } else {     
     $this->log->debug("$appid no constant define for this application");
   }
   return($out);
}
 
function GetUser($userid=ANONYMOUS_ID,$styleid="")
{
   $query = new QueryDb($this->dbaccess,"Param");
   
   $tlist = $query->Query(0,0,"TABLE","select  distinct on(paramv.name, paramv.appid) paramv.*,  paramdef.descr, paramdef.kind  from paramv, paramdef where paramv.name = paramdef.name and paramdef.isuser='Y' and (". 
			 " (type = '".PARAM_GLB."') ".
			 " OR (type='".PARAM_APP."')".
			 " OR (type='".PARAM_STYLE.$styleid."' )".
			 " OR (type='".PARAM_USER.$userid."' ))".
			 " order by paramv.name, paramv.appid, paramv.type desc");


   return($tlist);
}
/**
 * get list of parameters for a style
 * @param bool $onlystyle if false return all parameters excepts user parameters with style parameters
 * if true return only parameters redifined by the style
 * @return array of parameters values
 */
function GetStyle($styleid,$onlystyle=false)
{
   $query = new QueryDb($this->dbaccess,"Param");
   if ($onlystyle) {
     $query->AddQuery("type='".PARAM_STYLE.$styleid."'");
     $tlist = $query->Query(0,0,"TABLE");
   } else {
   $tlist = $query->Query(0,0,"TABLE","select  distinct on(paramv.name, paramv.appid) paramv.*,  paramdef.descr, paramdef.kind  from paramv, paramdef where paramv.name = paramdef.name and paramdef.isstyle='Y' and (". 
			 " (type = '".PARAM_GLB."') ".
			 " OR (type='".PARAM_APP."')".
			 " OR (type='".PARAM_STYLE.$styleid."' ))".
			 " order by paramv.name, paramv.appid, paramv.type desc");

   }
   return($tlist);
}

function GetApps()
{
   $query = new QueryDb($this->dbaccess,"Param");
   
   $tlist = $query->Query(0,0,"TABLE","select  paramv.*, paramdef.descr, paramdef.kind  from paramv, paramdef where paramv.name = paramdef.name and  (". 
			 " (type = '".PARAM_GLB."') ".
			 " OR (type='".PARAM_APP."'))".
			 " order by paramv.appid,  type desc");



   return($tlist);
}

function GetUParam($p, $u=ANONYMOUS_ID, $appid="") {
   if ($appid=="") $appid=$this->appid;
   $req = "select val from paramv where name='".$p."' and type='U".$u."' and appid=".$appid.";";
   $query = new QueryDb($this->dbaccess,"Param");
   $tlist = $query->Query(0,0,"TABLE",$req);
   if ($query->nb != 0) return $tlist[0]["val"];
   return "";
}

// delete paramters that cannot be change after initialisation
function DelStatic($appid)
{

    $query = new QueryDb($this->dbaccess,"Param");
    $list = $query->Query(0,0,"LIST","select paramv.*  from paramv, paramdef where paramdef.name=paramv.name and paramdef.kind='static' and paramdef.isuser!='Y' and paramv.appid=$appid;");

    if ($query->nb != 0) {
      reset($list);
      while(list($k,$v)=each($list)) {
        $v->Delete();
	if (isset($this->buffer[$v->name])) unset($this->buffer[$v->name]);
      }
    } 

}

function PostDelete() {
  if (isset($this->buffer[$this->name])) unset($this->buffer[$this->name]);
}

function DelAll($appid="")
{
  $query = new QueryDb($this->dbaccess,"Param");

  // delete all parameters not used by application
  $query->Query(0,0,"TABLE","delete from paramv where appid not in (select id from application) ");
  return;

}


// FIN DE CLASSE
}
?>
