<?php
/**
 * Domain Alias
 *
 * @author Anakeen 2000 
 * @version $Id: Class.DomainAlias.php,v 1.4 2005/10/05 16:28:42 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */


include_once('Class.DbObj.php');
include_once('Class.QueryDb.php');
include_once('Class.Pop.php');

Class DomainAlias extends DbObj {

var $Class = '$Id: Class.DomainAlias.php,v 1.4 2005/10/05 16:28:42 eric Exp $';

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
     $this->iddomainalias = $arr["nextval"];
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
