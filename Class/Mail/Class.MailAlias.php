<?php
/**
 * Mail Alias
 *
 * @author Anakeen 2000 
 * @version $Id: Class.MailAlias.php,v 1.6 2005/10/05 16:28:42 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */


// ---------------------------------------------------------------------------
include_once('Class.DbObj.php');
include_once('Class.Domain.php');

Class MailAlias extends DbObj
{
var $Class = '$Id: Class.MailAlias.php,v 1.6 2005/10/05 16:28:42 eric Exp $';

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
   $this->idalias = $arr["nextval"];
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
   $l = $q->Query(0,0,"TABLE");
   if ($q->nb > 0) $this->Affect($l[0]);
 }

 function CheckUptime($domain, $time) {
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
