<?php
/**
 * Pop Server Mail
 *
 * @author Anakeen 2000 
 * @version $Id: Class.Pop.php,v 1.4 2005/10/05 16:28:42 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

//
//
// ---------------------------------------------------------------------------
include_once('Class.DbObj.php');

class Pop extends DbObj {

var $Class = '$Id: Class.Pop.php,v 1.4 2005/10/05 16:28:42 eric Exp $';

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
   $this->idpop = $arr["nextval"];
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
   $list = $query->Query(0,0,"TABLE");
   if ($query->nb>0) {
     $this->Affect($list[0]);
     return TRUE;
   } else {
     $this->log->warning("No such pop host {$query->string}");
     return FALSE;
   }
 }

  
}
?>
