<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: download.php,v 1.5 2003/08/18 15:46:41 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage ACCESS
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: download.php,v 1.5 2003/08/18 15:46:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Access/download.php,v $
// ---------------------------------------------------------------
//  O   Anakeen - 2000
// O*O  Anakeen development team
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

include_once("Class.QueryDb.php");
include_once("Class.Application.php");
include_once("Class.User.php");
include_once("Class.Acl.php");
include_once("Class.Permission.php");
include_once("Class.Domain.php");
include_once("Lib.Http.php");

// -----------------------------------
function download(&$action) {
// -----------------------------------

  // select the first user if not set
  // What user are we working on ? ask session.
  $q = new QueryDb($action->dbaccess,"Application");
  $q->AddQuery("(objectclass isnull) OR (objectclass != 'Y')");
  $applist = $q->Query();

  // specific query due to optimize
  $query = new QueryDb($action->dbaccess,"User");
  $userlist=$query->Query(0,0,"TABLE",
			  "select  u1.login||'@'||d1.name as login , u1.password, u1.firstname, u1.lastname, u1.isgroup, u2.login||'@'||d2.name as group ".
			  "from users u1, users u2, domain d1, domain d2, groups ".
			  "where (u1.iddomain=d1.iddomain) and (iduser=u1.id) and (idgroup=u2.id) and (u2.iddomain = d2.iddomain) and (u1.id != 1)".
			  "order by login");
  

  $lay = new Layout($action->GetLayoutFile("filedown.xml"));

  $tab=array();
  $tab2=array();
  $tabuser=array();


  //------------------------------
  // view user list
  //------------------------------
  $ku=0;
  $oldlogin = "";
  while (list($k2,$v2)=each($userlist)) {

    if ($v2->id == 1) continue;
    if ($oldlogin != $v2["login"]) {
      if (($ku>0) &&($tabuser[$ku]["groups"]!="")) // delete last ';'
	$tabuser[$ku]["groups"]=substr($tabuser[$ku]["groups"], 0, -1);
      $ku++;
      $tabuser[$ku]["login"]=$v2["login"];
      $tabuser[$ku]["passwd"]=$v2["password"];
      $tabuser[$ku]["firstname"]=$v2["firstname"];
      $tabuser[$ku]["lastname"]=$v2["lastname"];
      $tabuser[$ku]["isgroup"]=$v2["isgroup"];
      $tabuser[$ku]["groups"]=$v2["group"].";";
    } else {
      $tabuser[$ku]["groups"].=$v2["group"].";";
    }

    $oldlogin = $v2["login"];

  }
  // delete last ";"
  if ($tabuser[$ku]["groups"]!="") // delete last ';'
	$tabuser[$ku]["groups"]=substr($tabuser[$ku]["groups"], 0, -1);


  //------------------------------
  // view acls list
  //------------------------------
  $tabacl=array();
  $query = new QueryDb($action->dbaccess,"User");
  $applistp=$query->Query(0,0,"TABLE",
			  "select  application.name as app, users.login||'@'||domain.name as login,  acl.name as acl ".
			 "from application, acl, permission, users, domain ".
			 "where (permission.id_user = users.id) and ".
			 "(permission.id_acl = acl.id) and ".
			 "(users.iddomain = domain.iddomain) and ".
			 "(acl.id_application = application.id) and ". 
			 "(permission.id_application=  application.id) and ".
			 "(users.id != 1) and ".
			 "((application.objectclass isnull)  OR (application.objectclass != 'Y') ) ".
			 "order by app, login");

  // same for negative acls : just add '-' sign
  $applistn=$query->Query(0,0,"TABLE",
			  "select  application.name as app, users.login||'@'||domain.name as login,  '-'||acl.name as acl ".
			 "from application, acl, permission, users, domain ".
			 "where (permission.id_user = users.id) and ".
			 "(- permission.id_acl = acl.id) and ".
			 "(users.iddomain = domain.iddomain) and ".
			 "(acl.id_application = application.id) and ". 
			 "(permission.id_application=  application.id) and ".
			 "(users.id != 1) and ".
			 "((application.objectclass isnull)  OR (application.objectclass != 'Y') ) ".
			 "order by app, login");


  if (is_array($applistn)) $applist = array_merge($applistp, $applistn);
  else $applist = $applistp;
  sort($applist);

  $ka=0;
  $oldlogin = "";
  while (list($k2,$v2)=each($applist)) {

    if ($v2->id == 1) continue;
    if (($oldlogin != $v2["login"]) || 
	($oldapp != $v2["app"])) {
      if (($ka >0) && ($tabacl[$ka]["acl_name"]!="")) // delete last ';'
	$tabacl[$ka]["acl_name"]=substr($tabacl[$ka]["acl_name"], 0, -1);
      $ka++;
      $tabacl[$ka]["login"]=$v2["login"];
      $tabacl[$ka]["app_name"]=$v2["app"];
      $tabacl[$ka]["acl_name"]=$v2["acl"].";";
    } else {
      $tabacl[$ka]["acl_name"].=$v2["acl"].";";
    }

    $oldlogin = $v2["login"];
    $oldapp = $v2["app"];

  }
  // delete last ";"
  if ($tabacl[$ka]["acl_name"]!="") // delete last ';'
	$tabacl[$ka]["acl_name"]=substr($tabacl[$ka]["acl_name"], 0, -1);




  $lay->SetBlockData("THEUSERS",$tabuser);
  $lay->SetBlockData("APPLICATION",$tabacl);


  $out = $lay->gen(); 

  Http_Download($out,"acl","access");


}
?>
