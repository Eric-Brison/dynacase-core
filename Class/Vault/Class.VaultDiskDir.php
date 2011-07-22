<?php

/**
 * Directories for vault files
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package VAULT
 */

// ---------------------------------------------------------------
// $Id: Class.VaultDiskDir.php,v 1.10 2006/12/06 11:12:13 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/vault/Class/Class.VaultDiskDir.php,v $
// ---------------------------------------------------------------

//
// ---------------------------------------------------------------
include_once("Class.QueryDb.php");

define("VAULT_MAXENTRIESBYDIR",1000);
define("VAULT_MAXDIRBYDIR",100);
class VaultDiskDir extends DbObj {

  public $fields = array ( "id_dir", "id_fs", "free_entries", "l_path" );
  public $id_fields = array ("id_dir");
  public $dbtable_tmpl = "vaultdiskdir%s";
  public $order_by="";
  public $seq_tmpl="seq_id_vaultdiskdir%s";
  public $sqlcreate_tmpl = "
           create table vaultdiskdir%s  ( id_dir     int not null,
                                 primary key (id_dir),
				 id_fs          int,
				 free_entries   int,
                                 l_path varchar(2048)
                               );
           create sequence seq_id_vaultdiskdir%s start 10";

  // --------------------------------------------------------------------
  function __construct($dbaccess,  $id_dir='',$def='') {
  // --------------------------------------------------------------------
    $this->specific = $def;
    $this->dbtable = sprintf($this->dbtable_tmpl, $this->specific);
    $this->sqlcreate = sprintf($this->sqlcreate_tmpl, $this->specific, $this->specific);
    $this->seq = sprintf($this->seq_tmpl, $this->specific);

    parent::__construct($dbaccess, $id_dir);
  }

  /**
   * return name of next directory
   * 1/1 => 1/2
   * 1/10 => 2/1
   * 1/2  = 1/3
   */
  function nextdir($d,$max=VAULT_MAXDIRBYDIR) {
    $td=explode('/',$d);
    $dend=intval(end($td));
  
    $lastkey = end(array_keys($td));
    if ($dend < $max) {
      $td[$lastkey]++;
    } else {
      $good=false;;
      $key=$lastkey;
      while (($key>=0) && (!$good)) {
	$prev=intval(prev($td));
	$td[$key]=1;
	$key--;
	if ($prev) {
	  if ($prev < $max) {
	    $td[$key]++;	  
	    $good=true;
	  } 
	}
      }
      if (!$good) $td=array_fill(0,count($td)+1,1);
    }
    return implode('/',$td);
  }

  // --------------------------------------------------------------------
  function SetFreeDir($fs) {
  // --------------------------------------------------------------------
    $query = new QueryDb($this->dbaccess, $this->dbtable);
    $id_fs=$fs["id_fs"];
    $query->basic_elem->sup_where=array("id_fs=".$id_fs, 
					"free_entries>0");
    $t = $query->Query(0,0,"TABLE");
    if ($query->nb > 0) {
      $this->Select($t[0]["id_dir"]);
      unset($t);
      $this->free_entries--;
      $this->Modify();
    } else {
      $t=$query->Query(0,0,"TABLE","SELECT * from vaultdiskdirstorage where id_fs=".intval($id_fs)." order by id_dir desc limit 1");
      $lpath=$t[0]["l_path"];
      $npath=$this->nextdir($lpath);
      $rpath=$fs["r_path"];
      
      $this->id_dir = "";
      $this->id_fs = $id_fs;
      $this->l_path = $npath;
      $this->free_entries = VAULT_MAXENTRIESBYDIR;
      $this->free_entries--;
      $err=$this->Add();
      if ($err == "") {
	mkdir($rpath."/".$npath, VAULT_DMODE);
	chown($rpath."/".$npath, HTTP_USER);
	chgrp($rpath."/".$npath, HTTP_USER);
      } else {	
	error_log("Vault dirs full");
	return(_("no empty vault dir found").$err);
      }      
    }
    return "";
  }

  // --------------------------------------------------------------------
  function PreInsert() {
  // --------------------------------------------------------------------
    if ($this->Exists( $this->l_path, $this->id_fs)) return (_("Directory already exists"));
    $res = $this->exec_query("select nextval ('".$this->seq."')");
    $arr = $this->fetch_array(0);
    $this->id_dir = $arr["nextval"];
    return '';
  }

  // --------------------------------------------------------------------
  function Exists($path, $id_fs) {
  // --------------------------------------------------------------------
    $query = new QueryDb($this->dbaccess, $this->dbtable);
    $query->basic_elem->sup_where=array("l_path='".$path."'", "id_fs=".$id_fs);
    $t = $query->Query(0,0,"TABLE");
    return ($query->nb > 0);
  }

  // --------------------------------------------------------------------
  function DelEntry() {
  // --------------------------------------------------------------------
    $this->free_entries += 1;
    $this->Modify();
  }

  // --------------------------------------------------------------------
  function FreeEntries($id_fs) {
  // --------------------------------------------------------------------
    $free_entries = 0;
    $query = new QueryDb($this->dbaccess, $this->dbtable);
    $query->basic_elem->sup_where=array("free_entries>0", "id_fs=".$id_fs);
    $t = $query->Query(0,0,"TABLE");
    while ($query->nb>0 && (list($k,$v) = each($t))) $free_entries += $v["free_entries"];
    unset($t);
    return ($free_entries);
  }

}

?>