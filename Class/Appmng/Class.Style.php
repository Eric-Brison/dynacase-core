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
//  $Id: Class.Style.php,v 1.2 2002/05/27 14:51:30 eric Exp $
//
$CLASS_STYLE_PHP = '$Id: Class.Style.php,v 1.2 2002/05/27 14:51:30 eric Exp $';
include_once('Class.DbObj.php');
include_once('Class.QueryDb.php');
include_once('Class.Param.php');

Class Style extends DbObj
{
var $fields = array ( "name","description");

var $id_fields = array ( "name");

var $sqlcreate = '
create table style ( 	name 	    varchar(20) not null,
     		primary key (name),
			description varchar(60) );
create sequence SEQ_ID_STYLE start 10000;
';

var $dbtable = "style";




var $param;


function Set(&$parent)
{  
  $this->parent=&$parent;
}





function InitParam()
{
  $this->param = new Param($this->dbaccess);
  $this->param->SetKey($this->id);
}

function GetImageUrl($img,$default) {
  $root = $this->parent->Getparam("CORE_PUBDIR");


  if (file_exists($root."/STYLE/".$this->name."/Images/".$img)) {
    return("STYLE/".$this->name."/Images/".$img); 
  } else {
    return($default);
  }
}

function GetLayoutFile($layname,$default="") {
  $root = $this->parent->Getparam("CORE_PUBDIR");
  $file = $root."/STYLE/".$this->name."/Layout/".$layname;
  if (file_exists($file))  return($file);
  
  return($default);
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
