<?php
// ---------------------------------------------------------------------------
// Param
// ---------------------------------------------------------------------------
// Anakeen 2000 - yannick.lebriquer@anakeen.com
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
//  $Id: Class.Param.php,v 1.2 2002/01/18 08:10:34 eric Exp $
//
include_once('Class.Log.php');
include_once('Class.DbObj.php');

$CLASS_PARAM_PHP = '$Id: Class.Param.php,v 1.2 2002/01/18 08:10:34 eric Exp $';

Class Param extends DbObj
{
var $fields = array ("key","name","val");

var $id_fields = array ("key","name");

var $dbtable = "param";

var $sqlcreate = '
      create table param (
              key    int not null,
              name    varchar(50),
              val    varchar(200));
      create index param_idx1 on param(key);
      create index param_idx2 on param(name);
                 ';

var $buffer=array();
   
function PreInsert( )
{
    if (strpos($this->name," ")!=0) {
      return "Le nom du paramètre ne doit pas contenir d'espace";
    }
}

function PreUpdate( )
{
   $this->PreInsert(); 
}

function SetKey($key) {
  $this->key=$key;
  $this->buffer=$this->GetAll($key);
}

function Set($name,$val)
{
  $this->name = $name;
  $this->val = $val;
  if ($this->Exists($name)) {
    $this->Modify();
  } else {
    $this->Add();
  }
  $this->buffer[$name]=$val;
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
   
function GetAll($key="")
{
   if ($key=="") $key=$this->key;
   $query = new QueryDb($this->dbaccess,"Param");
   $query->basic_elem->sup_where = array ("key=$key");
   $list = $query->Query(0,0,"TABLE");
   if ($query->nb != 0) {
     while(list($k,$v)=each($list)) {
       $out[$v["name"]]=$v["val"];
     }
   } else {
     $out = NULL;
     $this->log->debug("$key, no constant define for this key");
   }
   return($out);
}

function DelAll($key="")
{
   if ($key=="") $key=$this->key;
   $query = new QueryDb($this->dbaccess,"Param");
   $query->basic_elem->sup_where = array ("key=$key");
   $list = $query->Query();
   if ($query->nb != 0) {
     while(list($k,$v)=each($list)) {
       $v->Delete();
     }
   } else {
     $out = NULL;
     $this->log->debug("$key, no constant define for this key");
   }
   $this->buffer=array();
}

function Exists($name)
{
   return(isset($this->buffer[$name]));
}
// FIN DE CLASSE
}
?>
