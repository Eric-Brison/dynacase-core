<?php
// ---------------------------------------------------------------
// $Id: download.php,v 1.1 2002/01/08 12:41:33 eric Exp $
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

  $u = new QueryDb($action->dbaccess,"User");
  $u->basic_elem->sup_where=array("id != 1");
  $userlist = $u->Query();
  
  $lay = new Layout($action->GetLayoutFile("filedown.xml"));

  $tab=array();
  $tab2=array();
  while (list($k,$v)=each($applist)) {
    $tab[$k]["name"]=$v->name;
    reset($userlist);
    $q=new QueryDb("","Acl");
    $q->basic_elem->sup_where=array("id_application={$v->id}");
    $aclist = $q->Query();
    if ($q->nb == 0) continue;

    $ip=0; // permission index
    while (list($k2,$v2)=each($userlist)) {
      $access = new Permission($action->dbaccess,array($v2->id,$v->id));


      $action->log->debug("Acces {$v2->login} à {$v->name}  :");
      $action->log->debug("   Aclid = {$access->id_acl}");
      if ((count($access->upprivileges) == 0) &&
	  (count($access->unprivileges) == 0))  { // no specific privilege
	$tab2[$ip]["user_name"]=$v2->login;
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
	    $tab2[$ip]["user_name"]=$v2->login;
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
	    $tab2[$ip]["user_name"]=$v2->login;	    $tab2[$ip]["iddomain"]=$domain->name;
	  
	    $acl=new Acl($action->dbaccess,  $aclid);
	    $tab2[$ip]["acl_name"]='-'.$acl->name;
	    $tab2[$ip]["app_name"]=$v->name;

	    $ip++;
	  }
	}
      }
    }
    $lay->SetBlockData($v->name,$tab2);
    $tab2=array();
  }
  $lay->SetBlockData("APPLICATION",$tab);

  $out = $lay->gen(); 

  Http_Download($out,"acl","access");


}
?>
