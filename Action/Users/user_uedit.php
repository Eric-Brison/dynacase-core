<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: user_uedit.php,v 1.3 2005/07/08 15:29:51 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage USERS
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: user_uedit.php,v 1.3 2005/07/08 15:29:51 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Users/user_uedit.php,v $
// ---------------------------------------------------------------

include_once("Class.SubForm.php");
include_once("Class.Domain.php");
include_once("Class.MailAccount.php");
include_once("Class.User.php");

// -----------------------------------
function user_uedit(&$action) {
  // -----------------------------------


  // Get all the params   
  $id=$action->user->id; // himself


  if ($id == 0) $action->exitError(_("the user identification is unknow"));

  $papp = GetHttpVars("papp","APPMNG");
  $paction = GetHttpVars("paction","PARAM_CUACCOUNT");
  $pargs = GetHttpVars("pargs","");

  // initialise if user group or single user
  $group = (GetHttpVars("group") == "yes");

  $tpasswd =array();
  $tpasswd[0]["passwd"]="";   

 

  $tfirstname =array();
  $tfirstname[0]["firstname"]="";
  
  $user = $action->user;
   
    
  $action->lay->Set("firstname", $user->firstname);    
  $action->lay->Set("lastname",$user->lastname);
  $action->lay->Set("login",$user->login);
  $action->lay->Set("expdate",$user->expires>0?strftime("%d/%m/%Y %X",intval($user->expires)):"no date limit");

    
  $dom = new Domain($action->GetParam("CORE_DB"),$user->iddomain);
  $action->lay->Set("domain",$dom->name);
    

  $ug = new Group($action->GetParam("CORE_DB"),$user->id);
  $ugroup = $ug->groups;  // direct group 
  

  $tab = array();




  // search user group
  $tab = array();

  // 
  while (list($k, $v) = each($ugroup)) {
    $gu = new User($action->GetParam("CORE_DB"), $v);
    $tab[$k]["groupid"] = $v;
    $dom = new Domain($action->GetParam("CORE_DB"),$gu->iddomain);
    $tab[$k]["groupname"] = "{$gu->login}@{$dom->name}";
  }
  
  
  $action->lay->SetBlockData("VIEWGROUP", $tab);



    
  
  
  
  $action->lay->Set("APP", $papp);
  $action->lay->Set("ACTION", $paction);
  $action->lay->Set("ARGS", $pargs);

  
  $form = new SubForm("edit");
  $form->SetParam("firstname");
  $form->SetParam("lastname");
  $form->SetParam("passwd");
  $form->SetParam("id");
  $form->SetParam("domainid","","sel");
  $action->parent->AddJsCode($form->GetSubJs());
  $control=$action->GetLayoutFile("user_control.js");
  $lay = new Layout($control);
  $action->parent->AddJsCode($lay->gen());

}
?>
