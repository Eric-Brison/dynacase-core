<?php
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
// $Id: Class.Session.php,v 1.4 2002/01/28 16:55:29 eric Exp $
//
// $Log: Class.Session.php,v $
// Revision 1.4  2002/01/28 16:55:29  eric
// modif pour update cache pour autre session
//
// Revision 1.3  2002/01/25 14:31:37  eric
// gestion de cache objet - variable de session
//
// Revision 1.2  2002/01/18 08:12:32  eric
// optimization for speed
//
// Revision 1.1  2002/01/08 12:41:34  eric
// first
//
// Revision 1.14  2001/05/21 13:07:46  yannick
// Mise au point
//
// Revision 1.13  2001/05/21 10:23:00  yannick
// Problème sur les variables de sessions(tourne-page...)
//
// Revision 1.12  2001/02/26 15:11:25  yannick
// Optimization
//
// Revision 1.11  2001/02/07 11:30:44  yannick
// Traitement résultat vide, 1 seule page
//
// Revision 1.10  2001/01/25 15:54:33  marianne
// Suppression du 'register(user_id)' dans Activate ....
//
// Revision 1.9  2001/01/25 03:22:09  marianne
// register du user_id dans Activate
//
// Revision 1.8  2000/10/26 18:18:13  marc
// - Gestion des references multiples à des JS
// - Gestion de variables de session
//
// Revision 1.7  2000/10/26 15:18:51  yannick
// Ajout du Unregister sur Action
//
// Revision 1.6  2000/10/26 14:10:27  yannick
// Suite au login/domain => Modelage des sessions
//
// Revision 1.5  2000/10/24 21:17:32  marc
// Valeur de retour specifiee si pas de variable de session trouvee
//
// Revision 1.4  2000/10/23 09:07:36  marc
// Ajout des sessions dans Action
//
// Revision 1.3  2000/10/11 13:19:13  yannick
// Function GCollector OK
//
// Revision 1.2  2000/10/11 12:18:41  yannick
// Gestion des sessions
//
// Revision 1.1  2000/10/09 10:44:02  yannick
// Déplacement des sessions
//
// Revision 1.1.1.1  2000/10/05 17:29:10  yannick
// Importation
//
// Revision 1.7  2000/08/16 12:30:10  marc
// Initialisation du champ criteria ("")
//
// Revision 1.6  2000/08/10 16:02:28  yannick
// Contournement Query et mise à jour du TTL
//
// Revision 1.5  2000/07/09 09:27:09  yannick
// Conflits avec phplib (sessions)
//
// Revision 1.4  2000/07/05 12:24:50  yannick
// Utilisation de LIBPHPINCLUDE !!!!!!!!!!!!!!!
//
// Revision 1.3  2000/07/04 17:32:19  marc
// Mise en conf
//
// Revision 1.2  2000/06/30 15:29:24  marc
// Mise en conf, version initiale 2
//
// Revision 1.1  2000/06/30 15:24:49  marc
// Mise en conf, version initiale
//
//
// ---------------------------------------------------------------------------
// Syntaxe :
// ---------
//     $session = new Session();
//
// ---------------------------------------------------------------------------

$CLASS_SESSION_PHP = '$Id: Class.Session.php,v 1.4 2002/01/28 16:55:29 eric Exp $';
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
        // Init the database with the app file if it exists
        $this->Open();
        $this->status     = $this->SESSION_CT_EXIST;
      }
      $this->GCollector();
    }

       

  // Closes session and removes all datas
  // ------------------------------------
  function Close()
    {
      global $HTTP_CONNECTION; // use only cache with HTTP
      if ($HTTP_CONNECTION != "") {
	session_destroy();
      }
      $this->Delete();
      $this->status = $this->SESSION_CT_CLOSE;
      return $this->status;
    }  
  
  function Open()
    {
      $idsess  = $this->newId();
      global $HTTP_CONNECTION; // use only cache with HTTP
      if ($HTTP_CONNECTION != "") {
	session_id($idsess);
	session_start();
      }
      $this->id         = $idsess;
      $this->timetolive = $this->SetTTL();
      $this->userid   = 0;
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
	session_start();
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
      $this->userid = 0;
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
      global $$k;
      $$k=$v;

      global $HTTP_CONNECTION; // use only cache with HTTP
      if ($HTTP_CONNECTION != "") {
	session_register($k);
      }

      return true;

    }       
  
  // --------------------------------
  // Récupère une variable de session
  // $v est une chaine !
  // --------------------------------
  function Read($k = "", $d="") {
    
      global $HTTP_SESSION_VARS;

      if (isset($HTTP_SESSION_VARS[$k])) {
        return($HTTP_SESSION_VARS[$k]);
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
	session_unregister($k);
      }
      return;
    }       
  
  // --------------------------------
  // --------------------------------
  function GCollector()
    {
      $gcdate = $this->GetGCDate();
      if (time() >= $gcdate) {
        $this->log->debug("GCollector");
	$query = new QueryDb($this->dbaccess, "Session");
        $query->order_by='';
	$query->criteria = "timetolive";
	$query->operator = "<";
	$query->string   = time();
	$liste = $query->Query();
	$i = 0;
	while ($i<$query->nb) {
	  // 1, suppression des variables de session
	  // automaticaly deleted by php session 'session.gc_maxlifetime'
          $this->log->debug("Remove session: {$liste[$i]->id}");
	  // 2, suppression de la session
	  $liste[$i]->Delete();
	  $i++;
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
