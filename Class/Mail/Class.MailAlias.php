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
// $Id: Class.MailAlias.php,v 1.1 2002/01/08 12:41:34 eric Exp $
//
// $Log: Class.MailAlias.php,v $
// Revision 1.1  2002/01/08 12:41:34  eric
// first
//
// Revision 1.5  2000/12/22 21:37:36  marc
// Stable version....
//
// Revision 1.4  2000/11/01 18:19:32  yannick
// Passage de l'utilisateur et du mode
//
// Revision 1.3  2000/10/31 16:37:48  yannick
// AJout du makeqmailconf + Test existance domaine
//
// Revision 1.2  2000/10/24 21:14:41  marc
// En cours...
//
// Revision 1.1.1.1  2000/10/22 18:30:20  marc
// Initial release
//
//
// ---------------------------------------------------------------------------
include_once('Class.DbObj.php');
include_once('Class.Domain.php');

Class MailAlias extends DbObj
{
var $Class = '$Id: Class.MailAlias.php,v 1.1 2002/01/08 12:41:34 eric Exp $';

var $fields = array ( "idalias",
                      "iddomain",
                      "iduser",
                      "alias",
                      "type",
                      "uptime",
		      "remove" );

var $id_fields = array ( "idalias" );

var $dbtable = "mailalias";

var $sqlcreate = "
create table mailalias(
     idalias    int not null,
     iddomain   int not null,
     iduser	int not null, 
     primary key (idalias),
     alias	varchar(100),
     type	int,
     uptime     int,
     remove     int );
create index mailalias_idx on mailalias(idalias);
create sequence seqidalias;
";

 function PreInsert() {
   if ($this->Exists($this->alias,$this->iddomain)) return("alias already exists");
   $res = $this->exec_query("select nextval ('seqidalias')");
   $arr = $this->fetch_array (0);
   $this->idalias = $arr[0];
   $this->uptime = time();
   $this->remove = '0';
 }

 function PreUpdate() {
   $this->uptime = time();
 }

 function Remove() {
   $this->uptime = time();
   $this->remove = '1';
   $this->Modify();
 }
 
 function PostInsert() {
   //$adm = new MailAdmin($this->db, $this->iddomain);
   //$adm->Gen();
 }
 
 function PostUpdate() {
   $this->PostInsert();
 }

 function Set($d, $u) {
   $q = new QueryDb($this->dbaccess,"MailAlias");
   $q->basic_elem->sup_where = array("iddomain={$this->iddomain}", "iduser={$this->iduser}");
   $l = $q->Query();
   if ($q->nb > 0) $this = $l[0];
 }

 function CheckUptime($domain) {
   ($domain->gentime=="")?$time=0:$time=$domain->gentime;
   $q = new QueryDb($this->dbaccess,"MailAlias");
   $q->basic_elem->sup_where=array("iddomain={$domain->iddomain}","uptime>$time");
   $l = $q->Query();
   return($q->nb >0);
 }

 function Exists($alias,$iddomain) {
   $q = new QueryDb($this->dbaccess,"MailAlias");
   $q->basic_elem->sup_where = array("iddomain=$iddomain", "alias='$alias'");
   $l = $q->Query();
   return($q->nb>0);
 }


 
} // End Class
?>
