<?php
// ---------------------------------------------------------------------------
//    O   Anakeen - 2000
//   O*O  Marc Claverie
//    O   marc.claverie@anakeen.com
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
// $Id: Class.Pop.php,v 1.1 2002/01/08 12:41:34 eric Exp $
//
// $Log: Class.Pop.php,v $
// Revision 1.1  2002/01/08 12:41:34  eric
// first
//
// Revision 1.6  2001/02/09 17:37:06  yannick
// Anomalies diverses
//
// Revision 1.5  2001/02/09 17:32:42  yannick
// Anomalies diverses
//
// Revision 1.4  2000/10/26 18:18:13  marc
// - Gestion des references multiples à des JS
// - Gestion de variables de session
//
// Revision 1.3  2000/10/24 21:14:41  marc
// En cours...
//
// Revision 1.2  2000/10/23 18:09:03  marc
// Conf du soir...
//
// Revision 1.1.1.1  2000/10/22 18:30:20  marc
// Initial release
//
//
// ---------------------------------------------------------------------------
include_once('Class.DbObj.php');

Class Pop extends DbObj {

var $Class = '$Id: Class.Pop.php,v 1.1 2002/01/08 12:41:34 eric Exp $';

var $fields = array ( "idpop",
		      "iddomain",
                      "popname", 
                      "master" );

var $id_fields = array ( "idpop" );

var $dbtable = 'pop';

var $sqlcreate = "
create table pop(
     idpop int not null,
     primary key (idpop),
     iddomain   int not null,
     popname    varchar(100),
     master     varchar(1) );
create index pop_idx on pop(popname);
create sequence seq_idpop start 10;
grant all on seq_idpop to anakeen;
";

 function PreInsert() {
   $res = $this->exec_query("select nextval ('seq_idpop')");
   $arr = $this->fetch_array (0);
   $this->idpop = $arr[0];
   $this->popname = strtolower($this->popname);
   $this->log->info("Adding pop name : {$this->popname} master={$this->master} domaid id = {$this->iddomain}");
 } 
 
 function PreUpdate( ) {
   $this->popname = strtolower($this->popname);
 } 
 
 function GetMaster($dom) {
   $query = new QueryDb($this->dbaccess,"Pop");
   $query->basic_elem->sup_where = array("iddomain={$dom}","master='Y'");
   $list = $query->Query();
   return  $list[0];
 }
 
 function GetSlaves($dom=-1) {
   $query = new QueryDb($this->dbaccess,"Pop");
   if ($dom!=-1) $query->basic_elem->sup_where = array("iddomain={$dom}","master='N'");
   $list = $query->Query();
   return $list;
 }
 
 function GetAll($dom=-1) {
   $query = new QueryDb($this->dbaccess,"Pop");
   if ($dom!=-1) $query->basic_elem->sup_where = array("iddomain={$dom}");
   $list = $query->Query();
   return $list;
 }
 
 function Set($name=NULL, $id=NULL) {
   if ($name==NULL && $id==NULL) return FALSE;
   $query = new QueryDb($this->dbaccess, "Pop");
   if ($name!=NULL) {
     $query->basic_elem->sup_where = array ("popname = '$name'");
   } else {
     $query->basic_elem->sup_where = array ("idpop = $id");
   }
   $list = $query->Query();
   if ($query->nb>0) {
     $this=$list[0];
     return TRUE;
   } else {
     $this->log->warning("No such pop host {$query->string}");
     return FALSE;
   }
 }

  
}
?>
