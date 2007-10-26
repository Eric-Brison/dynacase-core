<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Class.Session.php,v 1.26 2007/10/26 15:29:50 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

// ---------------------------------------------------------------------------
// Marc Claverie (marc.claverie@anakeen.com)- anakeen 2000 
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
// $Id: Class.Session.php,v 1.26 2007/10/26 15:29:50 eric Exp $
//
// ---------------------------------------------------------------------------
// Syntaxe :
// ---------
//     $session = new Session();
//
// ---------------------------------------------------------------------------

$CLASS_SESSION_PHP = '$Id: Class.Session.php,v 1.26 2007/10/26 15:29:50 eric Exp $';
include_once('Class.QueryDb.php');
include_once('Class.DbObj.php');
include_once('Class.Log.php');

include_once('Class.SessionConf.php');
include_once ("Class.SessionCache.php");

Class Session extends DbObj{

var $fields = array ( "id","userid");

var $id_fields = array ("id");

var $dbtable = "sessions";

var $sqlcreate = "create table sessions ( id         varchar(100),
                        userid   int);
                  create index sessions_idx on sessions(id);";



 var $isCacheble= false;
var $sessiondb;

  
 
  function Set($id)  {
      global $_SERVER;
      $query=new QueryDb($this->dbaccess,"Session");
      $query->criteria = "id";
      $query->operator = "=";
      $query->string = "'".pg_escape_string($id)."'";
      $query->casse = "OUI";
      $list = $query->Query(0,0,"TABLE");
      if ($query->nb != 0) {
        $this->Affect($list[0]);
	session_id($id);
	@session_start();
	@session_write_close(); // avoid block
	//	$this->initCache();
        
      } else {
        // Init the database with the app file if it exists

	$u = new User();
	if ($u->SetLoginName($_SERVER['PHP_AUTH_USER'])) {	

	  $this->open($u->id);	
	} else {
	  $this->open();//anonymous session
	}
      }

      // set cookie session
      if ($_SERVER['HTTP_HOST'] != "") setcookie ("session",$this->id,$this->SetTTL(),"/");
      return true;
    }

       

  /** 
   * Closes session and removes all datas
   */
  function Close()  {
      global $_SERVER; // use only cache with HTTP
      if ($_SERVER['HTTP_HOST'] != "") {
	session_unset();
	$this->Delete();
	setcookie ("session","",0,"/");
      }
      $this->status = $this->SESSION_CT_CLOSE;
      return $this->status;
    }  

  /** 
   * Closes all session 
   */
  function CloseAll() {
      $this->exec_query("delete from sessions");
      $this->status = $this->SESSION_CT_CLOSE;
      return $this->status;
    }  
  
  function Open($uid=ANONYMOUS_ID)  {
      $idsess  = $this->newId();
      global $_SERVER; // use only cache with HTTP
      if ($_SERVER['HTTP_HOST'] != "") {
	//session_id($idsess);
	@session_start();
	@session_write_close(); // avoid block
	
	
	
	//	$this->initCache();
      }
      $this->id         = $idsess;
      $this->userid   = $uid;
      $this->Add();
      $this->log->debug("Nouvelle Session : {$this->id}");
    }
 
  // --------------------------------
  // Stocke une variable de session args
  // $v est une chaine !
  // --------------------------------
  function Register($k = "", $v = "")   {

      if ($k == "" ){
	$this->status = $this->SESSION_CT_ARGS;
	return $this->status;
      }
      //      global $_SESSION;
      //      $$k=$v;

      global $_SERVER; // use only cache with HTTP
      if ($_SERVER['HTTP_HOST'] != "") {
	//	session_register($k);
	//	session_id($this->id);
	@session_start();
	$_SESSION[$k]=$v;
	session_write_close();// avoid block
      }

      return true;

    }       
  
  // --------------------------------
  // Récupère une variable de session
  // $v est une chaine !
  // --------------------------------
  function Read($k = "", $d="") {    

    // global $_SESSION;
    //  if (session_is_registered ($k)) {

 
    if (isset($_SESSION[$k])) {
      //	global $$k;
      // return($$k);
      return $_SESSION[$k];
    } else {
      return($d);
    }
  }       

  
  // --------------------------------
  // Détruit une variable de session
  // $v est une chaine !
  // --------------------------------
  function Unregister($k = "")   {
      global $_SERVER; // use only cache with HTTP
      if ($_SERVER['HTTP_HOST'] != "") {
	//session_unregister($k);
	//	global $_SESSION;
	session_id($this->id);
	@session_start();
	unset($_SESSION[$k]);
	session_write_close();// avoid block
      }
      return;
    }       
  
 
  
  
  // ------------------------------------------------------------------------
  // utilities functions (private)
  // ------------------------------------------------------------------------
  
  function newId()   {
      $this->log->debug("newId");
      $magic = new SessionConf($this->dbaccess, "MAGIC");
      $m = $magic->val;
      unset($magic);
      return md5(uniqid($m));
    }

  function SetTTL()  {
      $ttli = new SessionConf($this->dbaccess, "TTL_INTERVAL");
      $ttliv = $ttli->val;
      //$ttli->CloseConnect();
      unset($ttli);
      return (time() + $ttliv);
    }
  
 
} // Class Session
?>
