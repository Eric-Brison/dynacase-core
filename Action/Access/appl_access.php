<?php
// ---------------------------------------------------------------
// $Id: appl_access.php,v 1.4 2002/08/26 13:04:58 eric Exp $
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

include_once("Class.QueryDb.php");
include_once("Class.SubForm.php");
include_once("Class.QueryGen.php");

// -----------------------------------
function appl_access(&$action, $oid=0) {
// -----------------------------------

  $baseurl=$action->GetParam("CORE_BASEURL");
  $standurl=$action->GetParam("CORE_STANDURL");




  

  // affect the select form elements
  $query = new QueryDb("","Application");
  if ($oid == 0) {
    $query-> AddQuery("(objectclass != 'Y' ) OR ( objectclass isnull)");
    $varreg = "access_appl_id";
    $paramedit="&isclass=no";
  } else {
    $query->AddQuery("objectclass = 'Y'");
    $varreg = "access_class_id";
    $paramedit="&isclass=yes&oid=$oid";
  }
  $applist = $query->Query();
  unset($query);

  $action->lay->set("ACTION_CHG","ACCESS_APPL_CHG$paramedit");
  $action->lay->set("ACTION_MOD","APPL_ACCESS_MOD$paramedit");

  // select the first user if not set
  $appl_id=$action->Read($varreg);

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
      
      if (($v->objectclass=="Y") || (true)) {
	$query = new QueryDb("","Acl");
	$query->basic_elem->sup_where=array("id_application={$v->id}");
	$acl_list = $query->Query("","","TABLE");
	if ($query->nb == 0) continue;
	if ($appl_id == 0) {
	  $appl_id=$v->id;
	  $action->Register($varreg,$appl_id);
	}
	if ($oid != 0) $tab[$i]["text"]=_($v->short_name);
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
					"shortname" =>_("userlogin"),
					"desc" =>_("username"),
					"permission" => _("permissions"));


    // 1) Get all users except admin
    $query->AddQuery("id != 1");
    $query->slice=20;
    $query->Query();



    // 2) Get all acl for all users
    reset($query->table->array);
    unset($tab);

    while (list ($k,$v) = each($query->table->array)) {
      if (!isset($v["login"])) continue;
    
    
      if ($oid == 0) $uperm = new Permission($action->dbaccess,array($v["id"], $appl_sel->id));
      else $uperm = new ObjectPermission($action->dbaccess,array($v["id"],
								 $oid,
								 $appl_sel->id));
      $name = $v["login"];

      $tab=array();
      $aclids = $uperm->privileges;
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
  

}
?>
