<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Class.Session.php,v 1.32 2008/06/24 16:05:51 jerome Exp $
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
// $Id: Class.Session.php,v 1.32 2008/06/24 16:05:51 jerome Exp $
//
// ---------------------------------------------------------------------------
// Syntaxe :
// ---------
//     $session = new Session();
//
// ---------------------------------------------------------------------------

$CLASS_SESSION_PHP = '$Id: Class.Session.php,v 1.32 2008/06/24 16:05:51 jerome Exp $';
include_once('Class.QueryDb.php');
include_once('Class.DbObj.php');
include_once('Class.Log.php');
include_once('Class.User.php');
include_once('Class.SessionConf.php');
include_once ("Class.SessionCache.php");

Class Session extends DbObj{

  var $fields = array ( "id","userid","name");

  var $id_fields = array ("id");
  
  var $dbtable = "sessions";
  
  var $sqlcreate = "create table sessions ( id         varchar(100),
                        userid   int,
                        name text not null);
                  create index sessions_idx on sessions(id);";
    
  var $isCacheble= false;
  var $sessiondb;

  var $session_name = 'freedom_param';

  function __construct($session_name = 'freedom_param') {
    parent::__construct();
    $this->session_name = $session_name;
  }

  function Set($id)  {
    global $_SERVER;
    $query=new QueryDb($this->dbaccess,"Session");
    $query->addQuery("id = '".pg_escape_string($id)."'");
    $query->addQuery("name = '".pg_escape_string($this->session_name)."'");
    $list = $query->Query(0,0,"TABLE");
    if ($query->nb != 0) {
      $this->Affect($list[0]);
      session_name($this->session_name);
      session_id($id);
      @session_start();
      @session_write_close(); // avoid block
      //print_r2("@session_write_close");
      //	$this->initCache();
    } else {
      // Init the database with the app file if it exists      
      $u = new User();
      if ($u->SetLoginName($_SERVER['PHP_AUTH_USER'])) {	
	$this->open($u->id);
      } else {
	$this->open(ANONYMOUS_ID);//anonymous session
      }
    }
    
    // set cookie session
    if ($_SERVER['HTTP_HOST'] != "") {
      setcookie ($this->name,$this->id,$this->SetTTL(),"/");
    }
    return true;
  }
  
  /** 
   * Closes session and removes all datas
   */
  function Close()  {
    global $_SERVER; // use only cache with HTTP
    if ($_SERVER['HTTP_HOST'] != "") {
      session_name($this->name);
      session_id($this->id);
      @session_unset();
      @session_destroy();
      @session_write_close();
      setcookie ($this->name,"",0,"/");
      $this->Delete();
    }
    $this->status = $this->SESSION_CT_CLOSE;
    return $this->status;
  }  
  
  /** 
   * Closes all session 
   */
  function CloseAll() {
    $this->exec_query("delete from sessions where name = '".pg_escape_string($this->name)."'");
    $this->status = $this->SESSION_CT_CLOSE;
    return $this->status;
  }  

  function Open($uid=ANONYMOUS_ID)  {
    $idsess  = $this->newId();
    global $_SERVER; // use only cache with HTTP
    if ($_SERVER['HTTP_HOST'] != "") {
      session_name($this->session_name);
      session_id($idsess);
      @session_start();
      @session_write_close(); // avoid block
      //	$this->initCache();
    }
    $this->name     = $this->session_name;
    $this->id       = $idsess;
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
      @session_write_close();// avoid block
    }
    
    return true;
  }       
  
  // --------------------------------
  // Récupère une variable de session
  // $v est une chaine !
  // --------------------------------
  function Read($k = "", $d="") {
    if( $_SERVER['HTTP_HOST'] != "" ) {
      session_name($this->name);
      session_id($this->id);
      session_start();
      if (isset($_SESSION[$k])) {
	$val = $_SESSION[$k];
	session_write_close();
	return $val;
      } else {
	session_write_close();
	return($d);
      }
    }
    return($d);
  }       
  
  // --------------------------------
  // Détruit une variable de session
  // $v est une chaine !
  // --------------------------------
  function Unregister($k = "")   {
    global $_SERVER; // use only cache with HTTP
    if ($_SERVER['HTTP_HOST'] != "") {
      session_name($this->name);
      session_id($this->id);
      @session_start();
      unset($_SESSION[$k]);
      @session_write_close();// avoid block
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