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
// $Id: Class.Domain.php,v 1.2 2003/04/21 11:18:28 marc Exp $
//
// $Log: Class.Domain.php,v $
// Revision 1.2  2003/04/21 11:18:28  marc
// Version 0.2.3, see ChangeLog
//
// Revision 1.1  2002/01/08 12:41:34  eric
// first
//
// Revision 1.13  2001/03/22 11:03:37  marc
// Release 0.4.1, see CHANGELOG
//
// Revision 1.12  2001/02/10 16:34:35  yannick
// supwhere au lieu de criteria
//
// Revision 1.11  2001/01/31 09:27:39  yannick
// Correction Set Domain => Boucle sur les requètes
//
// Revision 1.10  2000/11/13 11:40:19  marc
// Action : retour $def sur GetParam....
// Domain : selection domaine local.
//
// Revision 1.9  2000/10/31 16:37:48  yannick
// AJout du makeqmailconf + Test existance domaine
//
// Revision 1.8  2000/10/30 10:41:55  marc
// Renommage de Delete en Remove
//
// Revision 1.7  2000/10/27 10:09:31  marc
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

Class Domain extends DbObj {

var $Class = '$Id: Class.Domain.php,v 1.2 2003/04/21 11:18:28 marc Exp $';

var $fields = array ( "iddomain",
		      "name",
                      "status",
                      "root",
                      "gentime",
                      "quotast",
                      "quota",
		      "quotaalert",
		      "quotatext",
		      "nobodymsg",
		      "nobodyalert",
		      "autoreplaymsg"
		      );

var $id_fields = array ( "iddomain" );

var $dbtable = "domain";

var $sqlcreate = "
create table domain(
     iddomain   int not null,
     primary key (iddomain),
     name	varchar(100),
     status     int,
     root	varchar(255),
     gentime	int,
     quotast   int,
     quota      int,
     quotaalert varchar(255),
     quotatext  varchar(255),
     nobodymsg  varchar(255),
     nobodyalert varchar(255),
     autoreplaymsg varchar(255)
     );
create index domain_idx on domain(iddomain);
create sequence seq_iddomain start 10; 
";
 
 function PreInsert() {
   if ($this->exists($this->name)) return "Domain already exists";
   if ($this->iddomain == "") {
     $res = $this->exec_query("select nextval ('seq_iddomain')");
     $arr = $this->fetch_array (0);
     $this->iddomain = $arr[0];
   }
   $this->name = strtolower($this->name);
   $this->status = '1';
   $this->log->info("Adding domain {$this->name} / {$this->iddomain}"); 
 }
 
 function PreUpdate() {
   if ($this->status != 2) 
     $this->log->info("Modifying domain {$this->name} / {$this->iddomain}"); 
   $this->name = strtolower($this->name);
 }
 
 function ListAll($local=1) {
   $this->qcount = 0;
   $this->qlist = NULL;
   $query = new QueryDb($this->dbaccess,"Domain");
   $query->basic_elem->sup_where = array("iddomain>{$local}", "status != 2");
   $this->qlist = $query->Query();
   $this->qcount = $query->nb;
   return;
 }
 
 
 function MasterPopId() {
   $p = new Pop($this->dbaccess);
   $pm = $p->GetMaster($this->iddomain);
   return $pm->idpop;
 }
 

 function Remove() {
   $this->status = '2';
   $this->log->info("Deleting domain {$this->name} / {$this->iddomain}"); 
   $this->Modify();
 }

 function Set($name=NULL) {
   if ($name==NULL) return FALSE;
   $query = new QueryDb($this->dbaccess, "Domain");
   $query->basic_elem->sup_where[] = "name = '$name'" ;
   $list = $query->Query();
   if ($query->nb>0) {
     $this=$list[0];
     return TRUE;
   } else {
     $this->log->warning("No such domain {$query->string}");
     return FALSE;
   }
 }

 function PostInit() {
   $this->iddomain=1;
   $this->name="local";
   $this->admin="";
   $this->Add();
 }


 function Created() {
   $this->status = '0';
   $this->Modify();
 }

 function SetGenTime($time) {
   $this->gentime=$time;
   $this->update();
 }

 function Exists($name) {
   $query=new QueryDb($this->dbaccess,"Domain");
   $query->basic_elem->sup_where = array ("name='$name'");
   $list = $query->Query();
   return($query->nb > 0);
 }

}
?>
