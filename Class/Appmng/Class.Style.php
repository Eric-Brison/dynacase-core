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
//  $Id: Class.Style.php,v 1.1 2002/01/08 12:41:34 eric Exp $
//
$CLASS_STYLE_PHP = '$Id: Class.Style.php,v 1.1 2002/01/08 12:41:34 eric Exp $';
include_once('Class.DbObj.php');
include_once('Class.QueryDb.php');
include_once('Class.Param.php');
include_once('Class.Lang.php');
include_once('Lib.Http.php');

Class Style extends DbObj
{
var $fields = array ( "id","name","description");

var $id_fields = array ( "id");

var $sqlcreate = '
create table style ( 	id 	int not null,
     		primary key (id),
			name 	    varchar(20) not null,
			description varchar(60) );
create index style_idx1 on style(id);
create index style_idx2 on style(name);
create sequence SEQ_ID_STYLE start 10000;
';

var $dbtable = "style";

var $def = array ( "criteria" => "",
                   "order_by" => "name"
                 );

var $criterias = array (
             "name" => array ("libelle" => "Nom",
                             "type" => "TXT")
                               );



var $param;


function Set($name,&$parent)
{
  if ($name != "") {
    $query=new QueryDb($this->dbaccess,"Style");
    $query->order_by = "";
    $query->criteria = "name";
    $query->operator = "=";
    $query->string = "'".$name."'";
    $list = $query->Query();
    if ($query->nb != 0) {
       $this=$list[0];
       $this->log->debug("Set style to $name");
    } else {
       // Init the database with the style file if it exists
       $this->InitStyle($name);
    }
    $this->InitParam();
  }
  $this->parent=$parent;
}

function Complete() {
}

function PreInsert( )
{
  if ($this->Exists( $this->name)) return "Ce nom de style existe deja...";  
  $res = $this->exec_query("select nextval ('seq_id_style')");
  $arr = $this->fetch_array (0);
  $this->id = $arr[0];
}

function PreUpdate()
{
  if ($this->dbid == -1) return FALSE;
  if ($this->Exists( $this->name,$this->id)) return "Ce Style existe deja..."; 
}

function Exists($name,$id='')
{
  $query=new QueryDb($this->dbaccess,"application");
  $query->order_by="";
  $query->criteria="";

  if ($id='') {
    $query->basic_elem->sup_where = array ("name='$name'","id!=$id");

  } else {
    $query->criteria="name";
    $query->operator="=";
    $query->string="'".$name."'";
  }

  $query->Query();

  return ($query->nb > 0);
}


function InitParam()
{
  $this->param = new Param($this->dbaccess);
  $this->param->SetKey($this->id);
}

function GetImageUrl($img,$default) {
  $root = $this->parent->Getparam("CORE_PUBDIR");

  if (file_exists($root."/STYLE/".$this->name."/Images/".$img)) {
    return($this->parent->Getparam("CORE_PUBURL")."/STYLE/".$this->name."/Images/".$img); 
  } else {
    return($default);
  }
}

function GetLayoutFile($layname,$default) {
  $root = $this->parent->Getparam("CORE_PUBDIR");
  $file = $root."/STYLE/".$this->name."/Layout/".$layname;
  if (file_exists($file)) {
     return($file);
  } else {
    if (file_exists($default)) {
      return($default);
    } else {
      // perhaps generic application
      $genlayfile = $root."/".$this->parent->childof."/Layout/".$layname;
      //print ($genlayfile)."<BR>";
      if (file_exists($genlayfile))
	  return ($genlayfile);
    }
    
  }
  return($default);
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
  if (isset($this->param)) {
    return($this->param->Get($key,$default));
  } else {
    return($default);
  }
}

function GetAllParam()
{
  if (isset($this->param)) {
    return($this->param->buffer);
  } else {
    return(array());
  }
}
  
function InitStyle($id) {

  $this->log->debug("Init : $id");
  if (file_exists("STYLE/{$id}/{$id}.sty")) {
     global $sty_desc,$sty_const;
     include("./STYLE/{$id}/{$id}.sty");
     if (sizeof($sty_desc)>0) {
       $sty = new Style($this->dbaccess);
       reset($sty_desc);
       while (list($k,$v) = each ($sty_desc)) {
         $sty->$k = $v;
       }
       $sty->Add();
       $this=$sty;
     } else {
       die ("can't init $id");
     }

     // init param
     if (isset($sty_const)) {
       reset($sty_const);
       while (list($k,$v) = each ($sty_const)) {
            $this->SetParam($k,$v);
       }
     }
     
  } else {
    die ("No ${id}.sty available");
  }
}
      
function UpdateStyle() {
  $name=$this->name;
  $this->DeleteStyle();
  $this->InitStyle($name);
}

function DeleteStyle() {

  // delete params
  $param = new Param($this->dbaccess);
  $param->DelAll($this->id);

  // delete Style
  $this->Delete();
}


}
?>
