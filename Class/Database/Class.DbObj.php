<?php
/**
 * This class is a generic DB Class that can be used to create objects
 * based on the description of a DB Table. 
 *
 * @author Anakeen 2000 
 * @version $Id: Class.DbObj.php,v 1.28 2004/03/17 17:45:16 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

// ---------------------------------------------------------------------------
// Db Object
// @version $Id: Class.DbObj.php,v 1.28 2004/03/17 17:45:16 eric Exp $
// ---------------------------------------------------------------------------
// Anakeen 2000 - yannick.lebriquer@anakeen.com
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




include_once('Class.Log.php');
include_once('Class.Cache.php');
include_once('Lib.Common.php');

$CLASS_DBOBJ_PHP = '$Id: Class.DbObj.php,v 1.28 2004/03/17 17:45:16 eric Exp $';

/**
 * This class is a generic DB Class that can be used to create objects
 * based on the description of a DB Table. More Complex Objects will 
 * inherit from this basic Class.
 *
 */
Class DbObj extends Cache
{

/**
 * the database connection resource
 * @var resource 
 */
var $dbid = -1;
/**
 * coordinates to access to database
 * @var string 
 */
var $dbaccess = '';

/**
 * array of SQL fields use for the object 
 * @var array 
 */
var $fields=array ('*');

/**
 * name of the SQL table
 * @var string  
 */
var $dbtable='';

var $criterias=array();
/**
 * array of other SQL fields, not in attribute of object
 * @var array 
 */
var $sup_fields=array ();
var $sup_where=array ();
var $sup_tables=array ();
var $fulltextfields=array ();

/**
 * sql field to order
 * @var string 
 */
var $order_by="";
/**
 * indicates if fields has been affected 
 * @var string 
 * @see Affect()
 */
var $isset = false; // indicate if fields has been affected (call affect methods)

//----------------------------------------------------------------------------
/** 
 * Database Object constructor
 * 
 * 
 * @param string $dbaccess database specification
 * @param int $id identificator of the object
 * @param array $res array of result issue to QueryDb {@link QueryDb::Query()}
 * @param resource $dbid the database connection resource
 * @return bool false if error occured
 */
function DbObj ($dbaccess='', $id='',$res='',$dbid=0)
  {
    
    $this->dbaccess = $dbaccess;
    $this->init_dbid();

    //    $this->oname="zz";
    //    if (($this->isCacheble) && ($this->cache($dbaccess, $id, $res))) return true;
    if ($this->GetCache($this->CacheId($id, $res))) return true;
  
    //global ${$this->oname};
    $this->log = new Log("","DbObj",$this->dbtable);
    

    if ($this->dbid == 0) {
      $this->dbid = -1;
      return FALSE;
    }

    $this->selectstring="";
    // SELECTED FIELDS
    reset($this->fields);
    while(list($k,$v) = each($this->fields)) {
      $this->selectstring=$this->selectstring.$this->dbtable.".".$v.",";
      $this->$v="";
    }

    reset($this->sup_fields);
    while (list($k,$v) = each($this->sup_fields)) {
      $this->selectstring=$this->selectstring."".$v.",";
      $this->$v="";
    }  
    $this->selectstring=substr($this->selectstring,0,strlen($this->selectstring)-1);

    // select with the id
    if (($id!='') || (is_array($id)) || (!isset($this->id_fields[0])) ) {
      $ret=$this->Select($id);
      $this->SetCache($this->CacheId($id, $res));// set to the dbobj cache
      //      ${$this->oname} = $this;// set to the dbobj cache
      return($ret);
    }
    // affect with a query result
    if (is_array($res)) {
      $this->Affect($res);
    }
    //${$this->oname} = $this;// set to the dbobj cache
      $this->SetCache($this->CacheId($id, $res));// set to the dbobj cache
    return TRUE;
  }





function CacheId($id, $res) {

  $soid = "";
  if (($id != "") && ($res != "")) {
    if (is_array($id)) {
    
      while(list($k,$v) = each($id)) {
	$soid.= $v."_";
      }
    } elseif  (intval($id) > 0) 
      $soid = $id;
    else if (isset($res[$this->id_fields[0]])) {    
      while(list($k,$v) = each($this->id_fields)) {
	$soid.= $res[$this->id_fields[$k]]."_";
      }
    }


    if (count($this->id_fields) == 1) {
      if  (intval($id) > 0) $soid = $id;
      else if (isset($res[$this->id_fields[0]])) {
	$soid = $res[$this->id_fields[0]];
	//print "soid=$soid";
      }
    } //print "soid=$soid<BR>";
    
    
    if ($soid != "") {
      $soid=get_class($this)."::".$soid;
      if (ereg ("(.*)dbname=(.*)",$this->dbaccess, $reg)) {
	$soid.="::".$reg[2];
      }
    }
  }
  //      if ($soid != "")print "soid=$soid<HR>";
  
  return $soid;
}

function Select($id)
  {
    if ($this->dbid == -1) return FALSE;
    
    $msg=$this->PreSelect($id);
    if ($msg!='') return $msg;
    
    if ($this->dbtable=='') {
      return("error : No Tables");
    }
    $fromstr="{$this->dbtable}"; 
    if (is_array($this->sup_tables)) {
      reset($this->sup_tables);
      while(list($k,$v) = each($this->sup_tables)) {
	$fromstr.=",".$v;
      }
    } 
    $sql = "select {$this->selectstring} from {$fromstr} ";
    
    $count=0;
    if (is_array($id)) {
      $count=0;
      $wherestr=" where "; 
      reset($this->id_fields);
      while(list($k,$v) = each($this->id_fields)) {
	if ($count >0) {
	  $wherestr=$wherestr." AND ";
	}
	$wherestr=$wherestr."( ".$this->dbtable.".".$v."='".$id[$k]."' )";
	$count=$count+1;
	
	//$this->$v = $id[$k];
      }
    } else {
      if (isset($this->id_fields[0])) {
	$k = $this->id_fields[0];
	//$this->$k = $id;
	$wherestr= "where ".$this->dbtable.".".$this->id_fields[0]."='".$id."'" ;
      } else {
	$wherestr="";
      }
    }
    if (is_array($this->sup_where)) {
      reset($this->sup_where);
      while(list($k,$v) = each($this->sup_where)) {
	$wherestr=$wherestr." AND ";
	$wherestr=$wherestr."( ".$v." )";
	$count=$count+1;
      }
    } 
    
    $sql=$sql." ".$wherestr;
    
    $resultat = $this->exec_query($sql);
    
    if ($this->numrows() > 0) {
      $res = $this->fetch_array (0);
      $retour = $this->Affect($res);
      
    } else {
      return FALSE;
    }
    $msg=$this->PostSelect($id);
    if ($msg!='') return $msg;
    return TRUE;
  }


function Affect($array)
  {
    reset($array);
    while(list($k,$v) = each($array)) {
      if (!is_integer($k)) {
	$this->$k = $v;
      }
    }
    $this->Complete();
    $this->isset = true;
  }
 /**
   * verify that the object exists 
   *
   * if true values of the object has been set
   * @return bool
   */
function isAffected()
  {
    return $this->isset;
  }

function Complete()
  {
    // This function should be replaced by the Child Class
  }

/** 
 * Method use before Add method
 * This method should be replaced by the Child Class
 * 
 * @return string error message, if no error empty string
 * @see Add()
 */
function PreInsert()
  {
    // This function should be replaced by the Child Class
  }
/** 
 * Method use after Add method
 * This method should be replaced by the Child Class
 * 
 * @return string error message, if no error empty string, if message
 * error not empty the Add method is not completed
 * @see Add()
 */
function PostInsert()
  {
    // This function should be replaced by the Child Class
  }
/** 
 * Method use before Modify method
 * This method should be replaced by the Child Class
 * 
 * @return string error message, if no error empty string
 * @see Modify()
 */
function PreUpdate()
  {
    // This function should be replaced by the Child Class
  }
/** 
 * Method use after Modify method
 * This method should be replaced by the Child Class
 * 
 * @return string error message, if no error empty string, if message
 * error not empty the Modify method is not completed
 * @see Modify()
 */
function PostUpdate()
  {
    // This function should be replaced by the Child Class
  }
function PreDelete()
  {
    // This function should be replaced by the Child Class
  }
function PostDelete()
  {
    // This function should be replaced by the Child Class
  }
function PreSelect($id)
  {
    // This function should be replaced by the Child Class
  }
function PostSelect($id)
  {
    // This function should be replaced by the Child Class
  }

/** 
 * Add the object to the database
 * @param bool $nopost PostInsert method not apply if true
 * @return string error message, if no error empty string
 * @see PreInsert()
 * @see PostInsert()
 */
function Add($nopost=false)
  {
    if ($this->dbid == -1) return FALSE;
    
    $msg=$this->PreInsert();
    if ($msg!='') return $msg;
    
    $sfields = implode(",",$this->fields);
    $sql = "insert into ".$this->dbtable. "($sfields) values (";
    
    $valstring = "";
    reset($this->fields);
    while (list($k,$v) = each($this->fields)) {
      $valstring = $valstring.$this->lw(AddSlashes($this->$v)).",";
    }
    $valstring=substr($valstring,0,strlen($valstring)-1);
    $sql=$sql.$valstring.")";
    
    // exécution de la requête
      $msg_err = $this->exec_query($sql);
    
    if ($msg_err!=''){
      return $msg_err;
    }
    
    if (!$nopost) $msg=$this->PostInsert();
    $this->ClearCache();
    if ($msg!='') return $msg;
  }
/** 
 * Add the object to the database
 * @param bool $nopost PostUpdate() method not apply if true
 * @return string error message, if no error empty string
 * @see PreUpdate()
 * @see PostUpdate()
 */
function Modify($nopost=false,$sfields="")
  {
    if ($this->dbid == -1) return FALSE;
    $msg=$this->PreUpdate();
    if ($msg!='') return $msg;
    $sql = "update ".$this->dbtable." set ";
    
    
   
    $nb_keys=0;
    foreach ($this->id_fields as $k=>$v) {
      $notset[$v]="Y";
      $nb_keys++;
    }

    if (! is_array($sfields)) $fields=$this->fields;
    else {
      $fields=$sfields;
      foreach ($this->id_fields as $k=>$v) $fields[]=$v;
    }
    

    $setstr="";
    $wstr="";
    foreach ($fields as $k=>$v) {
      if (!isset($notset[$v])) {
        $setstr=$setstr." ".$v."=".$this->lw(AddSlashes($this->$v)).",";
      } else {
        $wstr=$wstr." ".$v."='".$this->$v."' AND";
      } 
    }
    $setstr=substr($setstr,0,strlen($setstr)-1);
    $wstr=substr($wstr,0,strlen($wstr)-3);
    $sql.=$setstr;
    if ($nb_keys>0) {
      $sql.=" where ".$wstr.";";
    }
    
    $msg_err = $this->exec_query($sql);

    // sortie
      if ($msg_err!=''){
	return $msg_err;
      }
    
    if (!$nopost) $msg=$this->PostUpdate();
    
    $this->ClearCache();
    if ($msg!='') return $msg;
  }	

function Delete($nopost=false)
  {
    $msg=$this->PreDelete();
    if ($msg!='') return $msg;
    $wherestr="";
    $count=0;
    
    reset($this->id_fields);
    while(list($k,$v) = each($this->id_fields)) {
      if ($count >0) {
        $wherestr=$wherestr." AND ";
      }
      $wherestr=$wherestr."( ".$v."='".AddSlashes($this->$v)."' )";
      $count++;
    }
    
    // suppression de l'enregistrement
      $sql = "delete from ".$this->dbtable." where ".$wherestr.";";
    
    $msg_err = $this->exec_query($sql);
    
    if ($msg_err!=''){
      return $msg_err;
    }
    
    if (!$nopost) $msg=$this->PostDelete();
    $this->ClearCache();
    if ($msg!='') return $msg;
  }

function lw($prop)
  {
    $result = ($prop==''?"null":"'$prop'");
    return $result;
  }
function CloseConnect()
  {
    pg_close("$this->dbid");
    return TRUE;
  }

function Create($nopost=false)
  {
    $msg = "";
    if (isset($this->sqlcreate)) {
      
      // step by step
      if (is_array($this->sqlcreate)) {
	while (list($k,$sqlquery)=each($this->sqlcreate)) {
	  $msg=$this->exec_query($sqlquery,1);
	}
      } else {	
	$sqlcmds = explode(";",$this->sqlcreate);
	while (list($k,$sqlquery)=each($sqlcmds)) {
	  $msg=$this->exec_query($sqlquery,1);
	}
      }
      $this->log->debug("DbObj::Create : {$this->sqlcreate}");
    }
    if (isset($this->sqlinit)) {
      $msg=$this->exec_query($this->sqlinit,1);
      $this->log->debug("Init : {$this->sqlinit}");
    }
    if ($msg != '') {
      $this->log->info("DbObj::Create $msg");   
      return $msg;
    }
    if (!$nopost) $this->PostInit();
    return($msg);
  }  

function PostInit() {
}

function init_dbid() {
  
  if ($this->dbaccess=="") {
    // don't test if file exist or must be searched in include_path 
      include("dbaccess.php");
    $this->dbaccess=$dbaccess;
    
  }
  $this->dbid= getDbid($this->dbaccess);
  return $this->dbid;
  
}
function exec_query($sql,$lvl=0)
  {
    global $SQLDELAY,$SQLDEBUG;

    if ($sql == "") return;

    if ($SQLDEBUG) $sqlt1=microtime(); // to test delay of request
    //    $mb=microtime();
    $this->init_dbid();
    $this->log->debug("exec_query : $sql");
    
    $this->res=@pg_query($this->dbid,$sql);
    //    print "<HR>exec_query $sql;".$this->dbid; print " - <B>".microtime_diff(microtime(),$mb)."</B>";
    $pgmess = pg_last_error($this->dbid);
    //    if ($pgmess != "") print "[$sql]";
    
    $this->msg_err = chop(ereg_replace("ERROR:  ","",$pgmess));
    
    
    $action_needed= "none";
    if ($lvl==0) { // to avoid recursivity
		     if ((ereg("Relation ['\"]([a-zA-Z_]*)['\"] does not exist",$this->msg_err) ||
			  ereg("class \"([a-zA-Z_]*)\" not found",$this->msg_err)) ) {
		       $action_needed = "create";
		     } else if ((ereg("No such attribute or function '([a-zA-Z_0-9]*)'",$this->msg_err)) ||
				(ereg("Attribute ['\"]([a-zA-Z_0-9]*)['\"] not found",$this->msg_err))) {
		       $action_needed = "update";
		     }
		 }
    
    switch ($action_needed)
      {
      case "create":
	$st = $this->Create();
	if ($st == "") {
	  $this->msg_err = $this->exec_query($sql);
	} else {
	  return "Table {$this->dbtable} doesn't exist and can't be created"; 
	}
	break;
      case "update":
	$this->log->warning("sql fail: $sql");
	$this->log->warning("try update :: ".$this->msg_err);
	$st = $this->Update();
	if ($st == "") {
	  $this->msg_err = $this->exec_query($sql);
	} else {
	  return "Table {$this->dbtable} cannot be updated"; 
	}
	break;
      case "none":
	break;
      }
    if ($this->msg_err != "") {
      $this->log->warning("exec_query :".$sql);
      $this->log->warning("PostgreSQL Error : ".$this->msg_err);
    }
    
     if ($SQLDEBUG) {
       global $TSQLDELAY;
       $SQLDELAY+=microtime_diff(microtime(),$sqlt1);// to test delay of request
       $TSQLDELAY[]="t=>".microtime_diff(microtime(),$sqlt1)."s=>$sql";
     }
    return ($this->msg_err);
  }

function numrows()
  {
    if ($this->msg_err == "") {
      return(pg_num_rows($this->res));
    } else {
      return(0);
    }
  }

function fetch_array($c,$type=PGSQL_BOTH)
  {
    
    return(pg_fetch_array($this->res,$c,$type));
  }

function Update()
  {
	print $this->msg_err;
    print(" - need update table ".$this->dbtable);
    $this->log->error("need Update table ".$this->dbtable);
    exit;
    $this->log->info("Update table ".$this->dbtable);
    
    // need to exec altering queries
      $objupdate = new DbObj($this->dbaccess);
    
    // ------------------------------
      // first : save table to updated
	$dumpfile = uniqid("/tmp/".$this->dbtable);
    $err = $objupdate-> exec_query("COPY ".$this->dbtable.
				   "  TO '".$dumpfile."'");
    $this->log->info("Dump table ".$this->dbtable." in ".$dumpfile);
    
    if ($err != "") return ($err);
    
    
    
    
    // ------------------------------
      // second : rename table to save data
	//$err = $objupdate-> exec_query("CREATE  TABLE ".$this->dbtable."_old ( ) INHERITS (".$this->dbtable.")",1);
    //$err = $objupdate-> exec_query("COPY ".$this->dbtable."_old FROM '".$dumpfile."'",				1 );
    $err = $objupdate-> exec_query("ALTER TABLE ".$this->dbtable.
				   " RENAME TO ".$this->dbtable."_old",
				   1 );
    
    
    if ($err != "") return ($err);
    
    // remove index : will be recreated in the following step (create)
      $err = $this-> exec_query("select indexname from pg_indexes where tablename='".$this->dbtable."_old'",1);
    $nbidx = $this->numrows();
    for ($c=0; $c < $nbidx; $c++) {
      
      $row = $this->fetch_array($c,PGSQL_ASSOC);
      $err = $objupdate-> exec_query("DROP INDEX ".$row["indexname"],
				     1 );
      
    }
    
    
    // --------------------------------------------
      // third : Create new table with new attributes
	$this->Create(true);
    
    
    
    // ---------------------------------------------------
      // 4th : copy compatible data from old table to new table
	$first=true;
    
    $this->exec_query("SELECT * FROM ".$this->dbtable."_old");
    $nbold = $this->numrows();
    for ($c=0; $c<$nbold;$c++) {
      
      
      $row = $this->fetch_array($c,PGSQL_ASSOC);
      
      if ($first) {
	// compute compatible fields
	  $inter_fields = array_intersect(array_keys($row),$this->fields);
	reset($this->fields);
	$fields = "(";
	while (list($k,$v)=each($inter_fields)) {
	  $fields .= $v.",";
	}
	$fields=substr($fields,0,strlen($fields)-1); // remove last comma
	  $fields .= ")";
	$first=false;
      }
      
      // compute compatible values
	$values = "(";
      reset($inter_fields);
      while (list($k,$v)=each($inter_fields)) {
	$values.= "'".addslashes($row[$v])."',";
      }
      $values=substr($values,0,strlen($values)-1); // remove last comma
	$values .= ")";
      
      // copy compatible values
	$err = $objupdate-> exec_query ("INSERT INTO ".$this->dbtable." ".$fields.
					" VALUES ".$values,1);
      if ($err != "") return ($err);
      
    }
    
    // ---------------------------------------------------
      // 5th :delete old table (has been saved before - dump file)
	$err = $objupdate-> exec_query ("DROP TABLE ".$this->dbtable."_old",1);
    
    return ($err);
  }

// FIN DE CLASSE
}
?>
