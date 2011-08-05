<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: appl_access.php,v 1.7 2007/02/16 14:11:14 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage ACCESS
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: appl_access.php,v 1.7 2007/02/16 14:11:14 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Access/appl_access.php,v $
// ---------------------------------------------------------------

include_once("Class.QueryDb.php");
include_once("Class.SubForm.php");
include_once("Class.QueryGen.php");

// -----------------------------------
function appl_access(&$action, $oid=0) {
// -----------------------------------

  $baseurl=$action->GetParam("CORE_BASEURL");
  $standurl=$action->GetParam("CORE_STANDURL");
  
  $action->lay->set("usefilter",false);
  $action->lay->set("maxreach",false);

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

  $action->lay->set("hasuser",true);

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
