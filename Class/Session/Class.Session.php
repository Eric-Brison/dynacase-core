<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Class.Session.php,v 1.11 2004/01/13 09:31:57 eric Exp $
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
// $Id: Class.Session.php,v 1.11 2004/01/13 09:31:57 eric Exp $
//
// ---------------------------------------------------------------------------
// Syntaxe :
// ---------
//     $session = new Session();
//
// ---------------------------------------------------------------------------

$CLASS_SESSION_PHP = '$Id: Class.Session.php,v 1.11 2004/01/13 09:31:57 eric Exp $';
include_once('Class.QueryDb.php');
include_once('Class.DbObj.php');
include_once('Class.Log.php');

include_once('Class.SessionConf.php');
include_once ("Class.SessionCache.php");

Class Session extends DbObj{

var $fields = array ( "id","userid", "timetolive");

var $id_fields = array ("id");

var $dbtable = "sessions";

var $sqlcreate = "create table sessions ( id         varchar(100),
                        userid   int,
                        timetolive int);
                  create index sessions_idx on sessions(id);";


  // Status constant 
  var $SESSION_CT_INIT                = 0;
  var $SESSION_CT_EXIST               = 1;
  var $SESSION_CT_ACTIVE              = 2;
  var $SESSION_CT_CLOSE               = 3;
  var $SESSION_CT_TIMEOUT             = -1;
  var $SESSION_CT_NONE                = -2;
  var $SESSION_CT_ARGS                = -3;
  var $SESSION_CT_OOOPS               = -99;

 var $isCacheble= false;
var $sessiondb;

  function status2str($st)
    {
      switch($st) {
      case $this->SESSION_CT_INIT:
	return "SESSION_CT_INIT";
      case $this->SESSION_CT_EXIST:
	return "SESSION_CT_EXIST";
      case $this->SESSION_CT_ACTIVE:
	return "SESSION_CT_ACTIVE";
      case $this->SESSION_CT_CLOSE:
	return "SESSION_CT_CLOSE";
      case $this->SESSION_CT_TIMEOUT:
	return "SESSION_CT_TIMEOUT";
      case $this->SESSION_CT_NONE:
	return "SESSION_CT_NONE";
      case $this->SESSION_CT_ARGS:
	return "SESSION_CT_ARGS";
      case $this->SESSION_CT_ARGS:
	return "SESSION_CT_ARGS";
     default:
	return "SESSION_CT_OOOPS";
      }
    }

 
  function Set($id)
    {
      $query=new QueryDb($this->dbaccess,"Session");
      $query->criteria = "id";
      $query->operator = "=";
      $query->string = "'".$id."'";
      $list = $query->Query();
      if ($query->nb != 0) {
        $this=$list[0];
        if (isset($this->timetolive) && $this->timetolive < time()) {
          $this->Close();
          $this->Open();
          $this->status = $this->SESSION_CT_TIMEOUT;
        } else if ($this->userid == 0) {
          $this->status     = $this->SESSION_CT_EXIST;
          $this->timetolive = $this->SetTTL();
          $this->InitBuffer();
          $this->Modify();
        } else {
          $this->status     = $this->SESSION_CT_ACTIVE;
          $this->timetolive = $this->SetTTL();
          $this->InitBuffer();
          $this->Modify();
        }
      } else {
	global $PHP_AUTH_USER;
        // Init the database with the app file if it exists
        $this->Open();
        $this->status     = $this->SESSION_CT_EXIST;
	
	$u = new User();
	if ($u->SetLoginName($PHP_AUTH_USER)) {	  	
	  $this->activate($u->id);	
	} else {
	  return false;
	}
      }

      $this->GCollector();
      setcookie ("session",$this->id,0,"/");
      return true;
    }

       

  // Closes session and removes all datas
  // ------------------------------------
  function Close()
    {
      global $HTTP_CONNECTION; // use only cache with HTTP
      if ($HTTP_CONNECTION != "") {
	session_unset();
	@session_destroy();
      }
      $this->Delete();
      $this->status = $this->SESSION_CT_CLOSE;
      setcookie ("session","",0,"/");
      return $this->status;
    }  
  
  function Open()
    {
      $idsess  = $this->newId();
      global $HTTP_CONNECTION; // use only cache with HTTP
      if ($HTTP_CONNECTION != "") {
	session_id($idsess);
	@session_start();
      }
      $this->id         = $idsess;
      $this->timetolive = $this->SetTTL();
      $this->userid   = ANONYMOUS_ID;
      $this->Add();
      $this->log->debug("Nouvelle Session : {$this->id}");
    }
  // ----------------------
  // Rend active la session
  // ----------------------
  function Activate(&$userid)
    {
      if ($userid == "" ){
	$this->status = $this->SESSION_CT_ARGS;
	return $this->status;
      }
      global $HTTP_CONNECTION; // use only cache with HTTP
      if ($HTTP_CONNECTION != "") {
	session_id($this->id);
	@session_start();
	// see if the object cache is up-to-date
	$ocache = new SessionCache();
	$ocache->InitCache();
      }
      $this->userid = $userid;
      $this->timetolive = $this->SetTTL();
      $this->Modify();
      $this->status = $this->SESSION_CT_ACTIVE;
      return $this->status;
    }
  
  function DeActivate()
    {
      global $HTTP_CONNECTION; // use only cache with HTTP
      if ($HTTP_CONNECTION != "") {
	session_unset();
      }
      $this->userid = ANONYMOUS_ID;
      $this->timetolive = $this->SetTTL();
      $this->Modify();
      $this->status = $this->SESSION_CT_EXIST;
      return $this->status;

    }
  // --------------------------------
  // Stocke une variable de session args
  // $v est une chaine !
  // --------------------------------
  function Register($k = "", $v = "")
    {

      if ($k == "" ){
	$this->status = $this->SESSION_CT_ARGS;
	return $this->status;
      }
      //      global $_SESSION;
      //      $$k=$v;

      global $HTTP_CONNECTION; // use only cache with HTTP
      if ($HTTP_CONNECTION != "") {
	//	session_register($k);
	$_SESSION[$k]=$v;
	syslog(LOG_WARNING,( "session W)".session_id()." ==$k:".$_SESSION[$k]));
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

	syslog(LOG_WARNING,( "session R)".session_id()." ==$k:".$_SESSION[$k]));
 
    if (isset($_SESSION[$k])) {
      //	global $$k;
      // return($$k);
      return $_SESSION[$k];
    } else {
      return($d);
    }
  }       

  // -------------------------------
  // Get all vars
  //
  function InitBuffer() {
	$ocache = new SessionCache();
	$ocache->InitCache();
    return;
  }
  
  // --------------------------------
  // Détruit une variable de session
  // $v est une chaine !
  // --------------------------------
  function Unregister($k = "")
    {
      global $HTTP_CONNECTION; // use only cache with HTTP
      if ($HTTP_CONNECTION != "") {
	//session_unregister($k);
	//	global $_SESSION;
	unset($_SESSION[$k]);
      }
      return;
    }       
  
  // --------------------------------
  // --------------------------------
  function GCollector()
    {
      global $HTTP_CONNECTION; // use only cache with HTTP
      $gcdate = $this->GetGCDate();
      if (time() >= $gcdate) {

        $this->log->debug("GCollector");
	$query = new QueryDb($this->dbaccess, "Session");
	$query->AddQuery("timetolive < ".time());

	$liste = $query->Query();
	$i = 0;
	$current_sid = session_id();

	while ($i<$query->nb) {
	  // 1, suppression des variables de session
	  // automaticaly deleted by php session 'session.gc_maxlifetime'
          $this->log->debug("Remove session: {$liste[$i]->id}");

	  if ($HTTP_CONNECTION != "") {
	    @session_start();
	    session_id($liste[$i]->id);
	  }
	  // 2, suppression de la session
	  $liste[$i]->Close();
	  
	  $i++;
	}
	if ($HTTP_CONNECTION != "") {
	  session_id($current_sid);
	  @session_start();
	}
	$this->SetGCDate((time()+$this->GetGCInterval()));
	unset($query);
      }
      return;
    }

  
  function removesessionvars()
    {
      $qvar = new QueryDb($this->dbaccess, "SessionVar");
      $qvar->order_by='';
      $qvar->criteria = "";
      $lvar = $qvar->Query(0,0,"LIST",
			   "select session_vars.session, session_vars.key, session_vars.val "
			   ." from session_vars where session_vars.session = ' {$this->id}'");
      $j = 0;
      while ($j<$qvar->nb) {
	$lvar[$j]->Delete();
	$j++;
      }
      unset($qvar);
      unset($this->buffer);
    }
  
  
  // ------------------------------------------------------------------------
  // utilities functions (private)
  // ------------------------------------------------------------------------
  
  function newId()
    {
      $this->log->debug("newId");
      $magic = new SessionConf($this->dbaccess, "MAGIC");
      $m = $magic->val;
      unset($magic);
      return md5(uniqid($m));
    }
  
  function SetTTL()
    {
      $ttli = new SessionConf($this->dbaccess, "TTL_INTERVAL");
      $ttliv = $ttli->val;
      //$ttli->CloseConnect();
      unset($ttli);
      return (time() + $ttliv);
    }
  
  function GetGCInterval()
    {
      $t = new SessionConf($this->dbaccess, "GC_INTERVAL");
      $tv = $t->val;
      unset($t);
      return $tv;
    }

  function GetGCDate()
    {
      $t = new SessionConf($this->dbaccess, "GC_DATE");
      $tv = $t->val;
      unset($t);
      return $tv;
    }

  function SetGCDate($gcd)
    {
      $t = new SessionConf($this->dbaccess, "GC_DATE");
      $t->val = $gcd;
      $t->Modify();;
      return;
    }
} // Class Session
?>
