<?php
// ---------------------------------------------------------------
// $Id: download.php,v 1.2 2002/01/10 11:13:11 eric Exp $
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
// $Log: download.php,v $
// Revision 1.2  2002/01/10 11:13:11  eric
// modif pour import export utilisateur
//
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.3  2001/10/08 12:11:19  eric
// correction import/export du aux modifs accessibilité (positif/négatif)
//
// Revision 1.2  2001/08/20 16:48:58  eric
// changement des controles d'accessibilites
//
// Revision 1.1  2000/10/24 17:15:22  yannick
// Import/Export
//
// Revision 1.2  2000/10/23 12:36:04  yannick
// Ajout de l'acces aux applications
//
// Revision 1.1  2000/10/23 09:10:27  marc
// Mise au point des utilisateurs
//
// Revision 1.1.1.1  2000/10/21 16:44:39  yannick
// Importation initiale
//
// Revision 1.2  2000/10/19 16:47:23  marc
// Evo TableLayout
//
// Revision 1.1.1.1  2000/10/19 10:35:49  yannick
// Import initial
//
//
//
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

  $u = new User($action->dbaccess);
  $userlist = $u->GetUserAndGroupList();
  
  $lay = new Layout($action->GetLayoutFile("filedown.xml"));

  $tab=array();
  $tab2=array();
  $tabuser=array();

  while (list($k2,$v2)=each($userlist)) {

    if ($v2->id == 1) continue;
    $tabuser[$k2]["login"]=$v2->login;
    $domain = new Domain($action->dbaccess, $v2->iddomain);

    $tabuser[$k2]["passwd"]=$v2->password;
    $tabuser[$k2]["firstname"]=$v2->firstname;
    $tabuser[$k2]["lastname"]=$v2->lastname;
    $tabuser[$k2]["isgroup"]=$v2->isgroup;
    $tabuser[$k2]["iddomain"]=$domain->name;

    $group = new Group($action->dbaccess,$v2->id);

    $tabuser[$k2]["groups"]="";
    while (list($kg,$g)=each($group->groups)) {

      $ug = new User($action->dbaccess, $g);
      $domain = new Domain($action->dbaccess, $v2->iddomain);
	  
      $tabuser[$k2]["groups"].=$ug->login."@".$domain->name.";";
    }
  }

  while (list($k,$v)=each($applist)) {
    $q=new QueryDb("","Acl");
    $q->basic_elem->sup_where=array("id_application={$v->id}");
    $aclist = $q->Query();
    if ($q->nb == 0) continue;

    $ip=0; // permission index
    reset($userlist);
    while (list($k2,$v2)=each($userlist)) {
      
      if ($v2->id == 1) continue;
      $access = new Permission($action->dbaccess,array($v2->id,$v->id));

      $action->log->debug("Acces {$v2->login} à {$v->name}  :");
      $action->log->debug("   Aclid = {$access->id_acl}");
      if ((count($access->upprivileges) == 0) &&
	  (count($access->unprivileges) == 0))  { // no specific privilege

	$tab2[$ip]["login"]=$v2->login;
	$domain = new Domain($action->dbaccess, $v2->iddomain);
	$tab2[$ip]["iddomain"]=$domain->name;



        $tab2[$ip]["acl_name"]="NONE";
        $tab2[$ip]["app_name"]="#".$v->name;


	$ip++;
      } else {

	$domain = new Domain($action->dbaccess, $v2->iddomain);
	    
	// write positive privilege
	if (count($access->upprivileges) > 0) {
	  while(list($k3,$aclid)=each($access->upprivileges)) {
	    $tab2[$ip]["login"]=$v2->login;
	    $tab2[$ip]["iddomain"]=$domain->name;
	  
	    $acl=new Acl($action->dbaccess,  $aclid);
	    $tab2[$ip]["acl_name"]=$acl->name;
	    $tab2[$ip]["app_name"]=$v->name;

	    $ip++;
	  }
	}
	// write negative privilege
	if (count($access->unprivileges) > 0) {
	  while(list($k3,$aclid)=each($access->unprivileges)) {
	    $tab2[$ip]["login"]=$v2->login;	    
	    $tab2[$ip]["iddomain"]=$domain->name;
	  
	    $acl=new Acl($action->dbaccess,  $aclid);
	    $tab2[$ip]["acl_name"]='-'.$acl->name;
	    $tab2[$ip]["app_name"]=$v->name;

	    $ip++;
	  }
	}
      }
    }
    $tab[$k]["name"]=$v->name;
    $lay->SetBlockData($v->name,$tab2);
    $tab2=array();
  }
  $lay->SetBlockData("THEUSERS",$tabuser);
  $lay->SetBlockData("APPLICATION",$tab);


  $out = $lay->gen(); 

  Http_Download($out,"acl","access");


}
?>
