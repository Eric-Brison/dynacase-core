<?
// ---------------------------------------------------------------
// $Id: Class.User.php,v 1.1 2002/01/08 12:41:34 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Class/Appmng/Class.User.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2000
// O*O  Anakeen Development Team
//  O   dev@anakeen.com
// ---------------------------------------------------------------
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
// ---------------------------------------------------------------
// $Log: Class.User.php,v $
// Revision 1.1  2002/01/08 12:41:34  eric
// first
//
// Revision 1.19  2002/01/04 16:32:16  eric
// modif order_by
//
// Revision 1.18  2001/10/08 12:13:20  eric
// correction fonction SetLogin et checkpasword pour les groupes
//
// Revision 1.17  2001/09/12 10:17:36  eric
// no login group
//
// Revision 1.16  2001/08/29 13:28:09  eric
// droit par défaut sur les groupes
//
// Revision 1.15  2001/08/28 10:08:57  eric
// Gestion des groupes d'utilisateurs
//
// Revision 1.14  2001/08/21 08:49:23  eric
// suppression fonction SetPermission => reporté dans Class Permission SetUserPermission
//
// Revision 1.13  2001/07/23 16:21:24  eric
// droit par défaut
//
// Revision 1.12  2001/02/06 16:23:28  yannick
// QueryGen : first release
//
// Revision 1.11  2000/11/02 15:33:05  yannick
// Possibilité de creer des utilisateurs avec mot de passe crypté
//
// Revision 1.10  2000/10/31 16:37:48  yannick
// AJout du makeqmailconf + Test existance domaine
//
// Revision 1.9  2000/10/26 14:10:27  yannick
// Suite au login/domain => Modelage des sessions
//
// Revision 1.8  2000/10/26 12:52:13  yannick
// Bug : perte du mot de passe
//
// Revision 1.7  2000/10/26 07:54:50  yannick
// Gestion du domaine sur les utilisateur
//
// Revision 1.6  2000/10/23 14:13:45  yannick
// Contrôle des accès
//
// Revision 1.5  2000/10/21 16:40:50  yannick
// Gestion blocks imbriqués
//
// Revision 1.4  2000/10/18 14:55:34  yannick
// Prise en compte des références
//
// Revision 1.3  2000/10/11 13:09:47  yannick
// Mise au point Authentification/Session
//
// Revision 1.2  2000/10/11 12:18:41  yannick
// Gestion des sessions
//
//
// ---------------------------------------------------------------------------
$CLASS_USER_PHP = '$Id: Class.User.php,v 1.1 2002/01/08 12:41:34 eric Exp $';
include_once('Class.DbObj.php');
include_once('Class.QueryDb.php');
include_once('Class.Log.php');
include_once('Class.Application.php');
include_once('Class.Group.php');

Class User extends DbObj
{
var $fields = array ( "id","iddomain","lastname","firstname","login","password","isgroup");

var $id_fields = array ("id");

var $dbtable = "users";

var $order_by="lastname";

var $fulltextfields = array ("login","lastname","firstname");

var $sqlcreate = "
create table users ( id      int not null,
                     iddomain int not null,
                primary key (id),
                        lastname   varchar(30),
                        firstname  varchar(20),
                        login      varchar(30) not null,
                        password   varchar(30) not null,
                        isgroup      varchar(1));
create index users_idx1 on users(id);
create index users_idx2 on users(lastname);
create index users_idx3 on users(login);
create sequence seq_id_users start 10";



function SetLogin($login,$domain)
{
  $query = new QueryDb($this->dbaccess,"User");

  $query->basic_elem->sup_where=array("login='$login'",
				      "iddomain=$domain");

  $list = $query->Query();

  if ($query->nb != 0) {
    $this=$list[0];
  } else {
    return FALSE;
  }

  return TRUE;
}

function PreInsert()
{

  if ($this->Setlogin($this->login,$this->iddomain)) return "this login exists";
  if ($this->id == "") {
    $res = pg_exec($this->dbid, "select nextval ('seq_id_users')");
    $arr = pg_fetch_array ($res, 0);
    $this->id = $arr[0];
  }
  if (isset($this->isgroup) && ($this->isgroup == "Y")) {
    $this->password_new="no"; // no passwd for group
  } else {
    $this->isgroup = "N";
  }
  if (isset($this->password_new) && ($this->password_new!="")) {
    $this->computepass($this->password_new, $this->password);
  }
}

function PostInsert()     
{
  // create default ACL for each application
  // only for group
  if ($this->isgroup == "Y") {
    $app = new Application();
    $app-> UpdateUserAcl($this->id);
  }
  
}

function PreUpdate()
{
  if (isset($this->password_new) && ($this->password_new!="")) {
    $this->computepass($this->password_new, $this->password);
  }
}
function PostDelete()
{
  // delete reference in group table
  $group = new Group($this->dbaccess, $this->id);
  $group-> Delete();
}
// --------------------------------------------------------------------
function computepass($pass, &$passk)
{
        srand((double)microtime()*1000000);
        $salt = chr(rand(59,122)).chr(rand(59,122));
        $passk = crypt($pass, $salt);
}

function checkpassword($pass)
{
  if ($this->isgroup == 'Y') return false; // don't log in group 
  return($this->checkpass($pass,$this->password));
}    

// --------------------------------------------------------------------
function checkpass($pass, $passk)
{
        $salt = substr($passk, 0, 2);
        $passres = crypt($pass, $salt);
        return ($passres == $passk);
} 

function PostInit() {


  $group = new group($this->dbaccess);

  // Create admin user
  $this->iddomain=1;
  $this->id=1;
  $this->lastname="Master";
  $this->firstname="What";
  $this->password_new="anakeen";
  $this->login="admin";
  $this->Add();
  $group->iduser=$this->id;

  // Create default group
  $this->iddomain=1;
  $this->id=2;
  $this->lastname="Default";
  $this->firstname="What Group";
  $this->login="all";
  $this->isgroup="Y";
  $this->Add();
  $group->idgroup=$this->id;
  $group->Add();
  
  

  // Store error messages
     
}

function GetUserList() {
  $query = new QueryDb($this->dbaccess,"User");
  $query->order_by="lastname";
  $query-> AddQuery("(isgroup != 'Y') OR (isgroup isnull)");
  return($query->Query());
}

function GetGroupList() {
  $query = new QueryDb($this->dbaccess,"User");
  $query->order_by="lastname";
  $query-> AddQuery("isgroup = 'Y'");
  return($query->Query());
}

function GetUserAndGroupList() {
  $query = new QueryDb($this->dbaccess,"User");
  $query->order_by="isgroup desc, lastname";
  return($query->Query());
}
}
?>
