<?
// ---------------------------------------------------------------------------
// Db Object
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
//  $Id: Class.DbObj.php,v 1.1 2002/01/08 12:41:34 eric Exp $
//  $Log: Class.DbObj.php,v $
//  Revision 1.1  2002/01/08 12:41:34  eric
//  first
//
//  Revision 1.16  2002/01/04 16:33:06  eric
//  ajout message d'erreur sur create
//
//  Revision 1.15  2001/11/21 16:07:17  eric
//  prise en compte des AddSlahes pour les champs
//
//  Revision 1.14  2001/10/10 15:55:31  eric
//  correction pour champ oid
//
//  Revision 1.13  2001/10/04 08:09:18  eric
//  ajout includepath pour php sur update
//
//  Revision 1.12  2001/09/07 16:48:59  eric
//  gestion des droits sur les objets
//
//  Revision 1.11  2001/08/28 10:08:06  eric
//  ajout option execution postinit sur create
//
//  Revision 1.10  2001/08/20 16:43:35  eric
//  ajout fonction IsAffected pour savoir si champs remplis
//
//  Revision 1.9  2001/07/25 12:43:46  eric
//  ajout migration des bases automatique : fonction update
//
//  Revision 1.8  2001/02/26 14:13:12  yannick
//  Optimization and compatibility with php 4.0.4
//
//  Revision 1.7  2001/02/07 16:41:22  yannick
//  Gestion des header et tris
//
//  Revision 1.6  2001/02/06 16:23:28  yannick
//  QueryGen : first release
//
//  Revision 1.5  2000/10/31 16:37:48  yannick
//  AJout du makeqmailconf + Test existance domaine
//
//  Revision 1.4  2000/10/26 07:54:50  yannick
//  Gestion du domaine sur les utilisateur
//
//  Revision 1.3  2000/10/16 17:23:07  yannick
//  utilisation preg dans Layout et ménage dans les logs
//
//  Revision 1.2  2000/10/11 12:18:41  yannick
//  Gestion des sessions
//
//  Revision 1.1.1.1  2000/10/05 17:29:10  yannick
//  Importation
//
//  Revision 1.14  2000/09/05 10:18:10  marianne
//  Ajout PreSelect et PostSelect
//
//  Revision 1.13  2000/09/05 08:41:39  marianne
//  Mise au point Pre/Post insert/delete/update
//
//  Revision 1.12  2000/09/04 15:54:17  marianne
//  Ajout Pro/Pre Insert/Update/Delete + prise en compte tables sans clefs
//
//  Revision 1.11  2000/08/17 13:04:34  marianne
//  Correction bug sur retour suppression
//
//  Revision 1.9  2000/08/08 13:40:59  marianne
//  prise en compte des sup_fields
//
//  Revision 1.8  2000/07/07 10:11:08  yannick
//  Mise au point
//
//  Revision 1.7  2000/07/06 15:51:54  yannick
//  Mise au point Authent
//
//  Revision 1.6  2000/07/05 13:19:59  yannick
//  Mise au point
//
//  Revision 1.5  2000/06/30 12:45:46  yannick
//  Retourne faux si le DbId n'existe pas
//
//  Revision 1.4  2000/06/06 12:49:42  yannick
//  suppression des warning de forme
//
//  Revision 1.3  2000/06/05 16:13:55  yannick
//  Fonction tournepage OK
//
//  Revision 1.2  2000/06/05 13:58:27  yannick
//  Mise au point
//
//  Revision 1.1  2000/05/30 15:03:32  yannick
//  Nouveau
//
//  Revision 1.3  2000/05/26 14:28:57  xavier
//  Gestion du delete
//
//  Revision 1.2  2000/05/26 08:24:28  xavier
//  mise à jour du 05 26
//
// ---------------------------------------------------------------------------
// Fonctions : 
//  This class is a generic DB Class that can be used to create objects
//  based on the description of a DB Table. More Complex Objects will 
//  inherit from this basic Class.
// ---------------------------------------------------------------------------
//
include_once('Class.Log.php');

$CLASS_DBOBJ_PHP = '$Id: Class.DbObj.php,v 1.1 2002/01/08 12:41:34 eric Exp $';

Class DbObj
{
var $dbid = -1;
var $dbaccess = '';

var $fields=array ('*');
var $dbtable='';

var $criterias=array();
var $sup_fields=array ();
var $sup_where=array ();
var $sup_tables=array ();
var $fulltextfields=array ();

var $order_by="";

var $isset = false; // indicate if fields has been affected (call affect methods)


//----------------------------------------------------------------------------
function DbObj ($dbaccess='', $id='',$res='',$dbid=0)
{
        $this->log = new Log("","DbObj",$this->dbtable);
        if ($dbaccess=="") {
          // don't test if file exist or must be searched in include_path 
             include("dbaccess.php");
           
        }

        // Use only one connection to increase performances and
        // to avoid the multi connection bug (known in 4.01 and 4.0.1pl1)
        global $CORE_DBID;
	if (!isset($CORE_DBID) || !isset($CORE_DBID["$dbaccess"])) {
           $CORE_DBID["$dbaccess"] = pg_connect("$dbaccess");
        } 
        $this->dbid=$CORE_DBID["$dbaccess"];

	$this->dbaccess = $dbaccess;
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
	// add oid fields : always to identify
	if (! in_array("oid",$this->sup_fields))
	  $this->sup_fields[]="oid";
        reset($this->sup_fields);
        while (list($k,$v) = each($this->sup_fields)) {
           $this->selectstring=$this->selectstring."".$v.",";
           $this->$v="";
        }  
        $this->selectstring=substr($this->selectstring,0,strlen($this->selectstring)-1);

	// select with the id
	if (($id!='') || (is_array($id)) || (!isset($this->id_fields[0])) ) {
		$ret=$this->Select($id);
                return($ret);
	}
	// affect with a query result
	if (is_array($res)) {
		$this->Affect($res);
	}
	return TRUE;
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
            $this->$k = stripslashes($v);
          }
        }
        $this->Complete();
	$this->isset = true;
}

function IsAffected()
{
  return $this->isset;
}

function Complete()
{
// This function should be replaced by the Child Class
}
function PreInsert()
{
// This function should be replaced by the Child Class
}
function PostInsert()
{
// This function should be replaced by the Child Class
}
function PreUpdate()
{
// This function should be replaced by the Child Class
}
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

function Add()
{
  if ($this->dbid == -1) return FALSE;
  
  $msg=$this->PreInsert();
  if ($msg!='') return $msg;
	
  $sql = "insert into ".$this->dbtable." values (";

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
	
  $msg=$this->PostInsert();
  if ($msg!='') return $msg;
}

function Modify()
{
  if ($this->dbid == -1) return FALSE;
  $msg=$this->PreUpdate();
  if ($msg!='') return $msg;
  $sql = "update ".$this->dbtable." set ";

  reset($this->id_fields);
  $nb_keys=0;
  while (list($k,$v) = each ($this->id_fields)) {
     $notset[$v]="Y";
  $nb_keys++;
  }
  $setstr="";
  $wstr="";
  reset ($this->fields);
  while (list($k,$v) = each ($this->fields)) {
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

  $msg=$this->PostUpdate();
  if ($msg!='') return $msg;
}	

function Delete()
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

  $msg=$this->PostDelete();
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
      $sqlcmds = explode(";",$this->sqlcreate);
      while (list($k,$sqlquery)=each($sqlcmds)) {
	$msg=$this->exec_query($sqlquery,1);
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

function exec_query($sql,$lvl=0)
{
   error_reporting(4);
   $this->log->debug("exec_query : $sql");
   $this->res=pg_exec($this->dbid,$sql);
   error_reporting(15);
   $pgmess = pg_errormessage($this->dbid);
   $this->msg_err = chop(ereg_replace("ERROR:  ","",$pgmess));


   $action_needed= "none";
   if ($lvl==0) { // to avoid recursivity
     if ((ereg("Relation '([a-zA-Z_]*)' does not exist",$this->msg_err) ||
	  ereg("class \"([a-zA-Z_]*)\" not found",$this->msg_err)) ) {
       $action_needed = "create";
     } else if ((ereg("No such attribute or function '([a-zA-Z_]*)'",$this->msg_err)) ||
		(ereg("Attribute '([a-zA-Z_]*)' not found",$this->msg_err))) {
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

   return ($this->msg_err);
}

function numrows()
{
   if ($this->msg_err == "") {
     return(pg_numrows($this->res));
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
	$values.= "'".$row[$v]."',";
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
