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
// $Id: Class.DomainAlias.php,v 1.2 2003/04/21 11:18:28 marc Exp $
//
// $Log: Class.DomainAlias.php,v $
// Revision 1.2  2003/04/21 11:18:28  marc
// Version 0.2.3, see ChangeLog
//
// Revision 1.1  2002/01/08 12:41:34  eric
// first
//
// Revision 1.3  2000/10/31 16:37:48  yannick
// AJout du makeqmailconf + Test existance domaine
//
// Revision 1.2  2000/10/30 10:41:55  marc
// Renommage de Delete en Remove
//
// Revision 1.1  2000/10/27 10:09:32  marc
// MAILADMIN version 1.0.0
//
// Revision 1.6  2000/10/27 07:49:43  marc
// Mise au point MAILADMIN
//
// Revision 1.5  2000/10/26 18:18:13  marc
// - Gestion des references multiples à des JS
// - Gestion de variables de session
//
// Revision 1.4  2000/10/26 07:54:50  yannick
// Gestion du domaine sur les utilisateur
//
// Revision 1.3  2000/10/23 18:09:03  marc
// Conf du soir...
//
// Revision 1.2  2000/10/23 10:40:38  marc
// bugs correction
//
// Revision 1.1.1.1  2000/10/22 18:30:20  marc
// Initial release
//
//
// ---------------------------------------------------------------------------
include_once('Class.DbObj.php');
include_once('Class.QueryDb.php');
include_once('Class.Pop.php');

Class DomainAlias extends DbObj {

var $Class = '$Id: Class.DomainAlias.php,v 1.2 2003/04/21 11:18:28 marc Exp $';

var $fields = array ( "iddomainalias",
                      "iddomain",
		      "name",
                      "removed",
                      "uptime" );

var $id_fields = array ( "iddomainalias" );

var $dbtable = "domainalias";

var $sqlcreate = "
create table domainalias(
     iddomainalias   int not null,
     iddomain   int not null,
     primary key (iddomainalias),
     name	varchar(100) unique,
     removed    int,
     uptime     int );
create index domainalias_idx on domainalias(iddomainalias);
create sequence seq_iddomainalias start 10; 
";
 
 function PreInsert() {
   if ($this->iddomainalias == "") {
     $res = $this->exec_query("select nextval ('seq_iddomainalias')");
     $arr = $this->fetch_array (0);
     $this->iddomainalias = $arr[0];
   }
   $this->name = strtolower($this->name);
   $this->removed = '0';
   $this->uptime = time();
   $this->log->info("Adding domain alias {$this->name}  [alias:{$this->iddomainalias}|domain:{$this->iddomain}]"); 
 }
 
 function PreUpdate() {
   if ($this->removed != 1) 
   $this->log->info("Modifying domain alias {$this->name}  [alias:{$this->iddomainalias}|domain:{$this->iddomain}]"); 
   $this->name = strtolower($this->name);
   $this->uptime = time();
 }
 
 function ListAll($domain=-1) {
   $this->qcount = 0;
   $this->qlist  = NULL;
   if ($domain!=-1) {
     $query = new QueryDb($this->dbaccess,"DomainAlias");
     $query->basic_elem->sup_where = array("iddomain={$domain}", "removed='0'");
     $this->qlist = $query->Query();
     $this->qcount = $query->nb;
   }
   return;
 }

 function Remove() {
   $this->removed = '1';
   $this->log->info("Deleting domain alias {$this->name}  [alias:{$this->iddomainalias}|domain:{$this->iddomain}]"); 
   $this->Modify();
 }
 
}
?>
