<?php
// ---------------------------------------------------------------
// $Id: appl_access.php,v 1.1 2002/01/08 12:41:33 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Access/appl_access.php,v $
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
// $Log: appl_access.php,v $
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.9  2001/09/19 14:39:55  eric
// correction pour login identique : all group
//
// Revision 1.8  2001/09/07 16:52:01  eric
// gestion des droits sur les objets
//
// Revision 1.7  2001/08/28 10:12:50  eric
// modification pour la prise en comptes des groupes d'utilisateurs
//
// Revision 1.6  2001/08/20 16:48:58  eric
// changement des controles d'accessibilites
//
// Revision 1.5  2001/02/08 09:04:04  yannick
// Mise au point
//
// Revision 1.4  2001/02/07 17:21:51  yannick
// Integration de querygen
//
// Revision 1.3  2000/11/01 19:12:30  yannick
// Effet de bords sur les listes de droits des applications
//
// Revision 1.2  2000/10/24 17:14:33  yannick
// Import/Export
//
// Revision 1.1  2000/10/23 12:36:47  yannick
// Ajout de l'acces aux applications
//
// Revision 1.2  2000/10/23 09:09:37  marc
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
include_once("Class.SubForm.php");
include_once("Class.QueryGen.php");

// -----------------------------------
function appl_access(&$action, $isclass=false) {
// -----------------------------------

  $baseurl=$action->GetParam("CORE_BASEURL");
  $standurl=$action->GetParam("CORE_STANDURL");




  

  // affect the select form elements
  $query = new QueryDb("","Application");
  if (! $isclass) {
    $query-> AddQuery("(objectclass != 'Y' ) OR ( objectclass isnull)");
    $varreg = "access_appl_id";
    $paramedit="&isclass=no";
  } else {
    $query->AddQuery("objectclass = 'Y'");
    $varreg = "access_class_id";
    $paramedit="&isclass=yes";
  }
  $applist = $query->Query();
  unset($query);

  $action->lay->set("ACTION_CHG","ACCESS_APPL_CHG$paramedit");
  $action->lay->set("ACTION_MOD","APPL_ACCESS_MOD$paramedit");

  // select the first user if not set
  $appl_id=$action->Read($varreg);
  $action->log->debug("appl_id : $appl_id");
  if ($appl_id == "") $appl_id=0; 

  // Set the edit form element
  $form = new SubForm("edit",500,330,"not used",
                                     $standurl."app=ACCESS&action=EDIT&mod=app$paramedit");
  $form->SetParam("id","-1");
  $form->SetKey("id");

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsCode($form->GetMainJs());
  $jsscript=$form-> GetLinkJsMainCall();


  // display application / object class
  $tab=array();
  $appl_sel="";
  $i=0;
  if (is_array($applist)) {
    reset($applist);
    while(list($k,$v)=each($applist)) {
      $query = new QueryDb("","Acl");
      $query->basic_elem->sup_where=array("id_application={$v->id}");
      $acl_list = $query->Query("","","TABLE");
      if ($query->nb == 0) continue;
      if ($appl_id == 0) {
	$appl_id=$v->id;
	$action->Register($varreg,$appl_id);
      }
      if ($isclass) $tab[$i]["text"]=$v->short_name;
      else $tab[$i]["text"]=$v->name;
      $tab[$i]["id"]=$v->id;
      if ($appl_id == $v->id) {
	$appl_sel=$v;
	$appl_sel->acl=$acl_list;
	$tab[$i]["selected"]="selected";
      } else {
	$tab[$i]["selected"]="";
      }
      $i++;
    }
    
    $action->lay->SetBlockData("SELUSER",$tab);
    $action->parent->AddJsRef("change_acl.js");


    // Init a querygen object to select users
    $query = new QueryGen($action->dbaccess,"User",$action);  

    // 
    // Give some global elements for the table layout

    $query->table->fields= array("id","name","selname","description","lastname","firstname","edit","imgaccess");
    $query->table->headsortfields = array ("shortname"=>"login",
					   "desc"=>"lastname");

    $query->table->headcontent = array (
					"shortname" =>$action->text("userlogin"),
					"desc" =>$action->text("username"),
					"permission" => $action->text("permission"));


    // 1) Get all users except admin
    $query->AddQuery("id != 1");
    $query->Query();



    // 2) Get all acl for all users
    reset($query->table->array);
    unset($tab);

    while (list ($k,$v) = each($query->table->array)) {
      if (!isset($v["login"])) continue;
    
    
      $uperm = new Permission($action->dbaccess,array($v["id"], $appl_sel->id));
      $name = $v["login"];

      $tab=array();
      $aclids = $uperm-> privileges;
      if (! $aclids) { // no privilege
	$aclids=array(0);

      }

      while(list($k2,$v2)=each($aclids)) {
	$tab[$k2]["aclid"]=$v2;

	if ($v2 == 0) {
	  $tab[$k2]["aclname"]=$action->text("none");
	} else {
	  $acl=new Acl($action->dbaccess,  $v2);
	  $tab[$k2]["aclname"]=$acl->name;
	}
      }
      $action->lay->SetBlockData($v["id"],$tab);
      unset($tab);
    
      $query->table->array[$k]["name"]=$v["login"];
      $query->table->array[$k]["selname"]=$v["id"];
      $query->table->array[$k]["id"]=$v["id"];
      if (!isset($v["firstname"])) $v["firstname"]="";
      if (!isset($v["lastname"])) $v["lastname"]="";
      $query->table->array[$k]["description"]=$v["firstname"]." ".$v["lastname"];
      $query->table->array[$k]["edit"] = str_replace("[id]",$v["id"],$jsscript);
      if ($v["isgroup"] == "Y") {
	$query->table->array[$k]["imgaccess"]=$action->GetIcon("access2.gif", "modify",20);
      } else {  
	$query->table->array[$k]["imgaccess"]=$action->GetIcon("access.gif", "modify",18);    

      }
    
    }

    
    $query->table->Set();
  } else {
    $action-> ExitError("no class controlled");
  }
  

  $action->log->debug("FINACCESS : $appl_id");
}
?>
