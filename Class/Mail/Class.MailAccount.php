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
// $Id: Class.MailAccount.php,v 1.2 2002/08/09 17:28:00 marc Exp $
//
// $Log: Class.MailAccount.php,v $
// Revision 1.2  2002/08/09 17:28:00  marc
// 0.1.3-5 Modification pour gestion multi-pop
//
// Revision 1.1  2002/01/08 12:41:34  eric
// first
//
// Revision 1.10  2001/03/22 11:03:37  marc
// Release 0.4.1, see CHANGELOG
//
// Revision 1.9  2001/02/09 17:32:42  yannick
// Anomalies diverses
//
// Revision 1.8  2000/12/22 21:37:36  marc
// Stable version....
//
// Revision 1.7  2000/10/31 16:37:48  yannick
// AJout du makeqmailconf + Test existance domaine
//
// Revision 1.6  2000/10/30 10:41:55  marc
// Renommage de Delete en Remove
//
// Revision 1.5  2000/10/27 10:09:32  marc
// MAILADMIN version 1.0.0
//
// Revision 1.4  2000/10/24 21:14:41  marc
// En cours...
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
include_once('Class.Domain.php');
include_once('Class.MailAlias.php');

Class MailAccount extends DbObj
{
var $Class = '$Id: Class.MailAccount.php,v 1.2 2002/08/09 17:28:00 marc Exp $';

var $fields = array ( "iddomain",
		      "iduser",
                      "login",
                      "pop",
                      "vacst",
                      "vacmsg",
                      "fwdst",
                      "fwdadd",
                      "type",
                      "keepfwd",
                      "uptime",
                      "remove",
                      "quota" );

var $id_fields = array ("iduser");

var $dbtable = "mailaccount";

var $sqlcreate = "
create table mailaccount(
     iddomain   int not null,
     iduser	int not null, 
     primary key (iduser),
     login	varchar(100),
     pop	varchar(100),
     vacst	int,
     vacmsg	varchar(300),
     fwdst	int,
     fwdadd	varchar(300),
     type	int,
     keepfwd int,
     uptime  int,
     remove   int,
     quota    int );
create index mailaccount_idx on mailaccount(iduser);
";

function setdef(&$v,$d) {
  if (!isset($v) || $v=="") $v=$d;
}

function PreInsert() {
  if (!isset($this->pop) || $this->pop == "" || $this->pop == 0) {
  $this->log->Debug("PreInsert for dom={$this->iddomain} iduser={$this->iduser}");
    $dom = new Domain($this->dbaccess, $this->iddomain);
    $this->pop = $dom->MasterPopId($this->dbaccess);
  }
  $this->setdef($this->quota, '0');
  $this->setdef($this->vacst, '0');
  $this->setdef($this->vacmsg, "");
  $this->setdef($this->fwdst, '0');
  $this->setdef($this->fwdadd, "");
  $this->setdef($this->keepfwd, '1');
  $this->uptime = time();
  $this->type = '0';
  $this->remove = '0';
}

function PreUpdate() {
  $this->uptime = time();
}

function Remove() {
  $q = new QueryDb($this->dbaccess,"MailAlias");
  $q->basic_elem->sup_where = array("iddomain={$this->iddomain}", "iduser={$this->iduser}");
  $l = $q->Query();
  if ($q->nb > 0) {
    while(list($k, $v) = each($l)) {
      $a = new MailAlias();
      $a->Set($v->iddomain, $v->iduser);
      $a->Delete();
    }
  }
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

 function ListAccount($dom=0, $admacc='N') {
   $this->qcount = 0;
   $this->qlist  = 0;
   if ($dom!=0) {
     $q = new QueryDb($this->dbaccess, "MailAccount");
     if ($admacc != 'Y') 
       $q->basic_elem->sup_where = array("iddomain={$dom}", "type='{$admacc}'", "remove=0");
     else 
       $q->basic_elem->sup_where = array("iddomain={$dom}", "remove=0");
     $this->qlist  = $q->Query();
     $this->qcount = $q->nb;
  }
  return;
 } 

 function ListAlias() {
   $q = new QueryDb($this->dbaccess, "MailAlias");
   $q->basic_elem->sup_where = array("iddomain={$this->iddomain}",
                                      "iduser={$this->iduser}",
                                      "type=0",
                                      "remove=0");
   $a = $q->Query();
   return $a ;
 }

 function CheckUptime($time) {
   $q = new QueryDb($this->dbaccess,"MailAccount");
   $q->basic_elem->sup_where=array("iddomain={$domain->iddomain}","uptime>$time");
   $l = $q->Query();
   return($q->nb >0);
 }


} // End Class
?>
