<?php
// ---------------------------------------------------------------
// $Id: paramlist.php,v 1.2 2002/03/21 17:52:38 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Appmng/Attic/paramlist.php,v $
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
// $Log: paramlist.php,v $
// Revision 1.2  2002/03/21 17:52:38  eric
// prise en compte application répartie sur plusieurs machines
//
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.3  2001/09/10 16:46:49  eric
// modif pour libwhat 4.8 : accessibilté objet
//
// Revision 1.2  2001/02/26 16:57:14  yannick
// remove tablelayout bug
//
// Revision 1.1  2001/01/29 15:50:59  marianne
// prise en compte de la gestion des parametres
//
// ---------------------------------------------------------------
include_once("Class.TableLayout.php");
include_once("Class.QueryDb.php");
include_once("Class.Param.php");
include_once("Class.SubForm.php");
// -----------------------------------
function paramlist(&$action) {
// -----------------------------------

    // Set the globals elements
  $baseurl=$action->GetParam("CORE_BASEURL");
  $standurl=$action->GetParam("CORE_STANDURL");

  $action->lay->set("ACTION_CHG","PARAM_APPL_CHG");

  $err = $action->Read("err_add_parameter");
  if ($err != "") {
    $action->lay->Set("ERR_MSG",$err);
    $action->Unregister("err_add_parameter");
  } else {
    $action->lay->Set("ERR_MSG","");
  }

  // select the first user if not set
  $appl_id=$action->Read("param_appl_id");
  $action->log->debug("appl_id : $appl_id");
  if ($appl_id == "") $appl_id=0;

  // affect the select form elements
  $query = new QueryDb("","Application");
  $query-> AddQuery("(objectclass != 'Y' ) OR ( objectclass isnull)");
  $query->order_by = "name";
  $applist = $query->Query();
  unset($query);
  $tab=array();
  $appl_sel="";
  $i=0;
  reset($applist);
  while(list($k,$v)=each($applist)) {
    if ($action->AppInstalled($v->name)) {
      if ($appl_id == 0) {
	$appl_id=$v->id;
	$action->Register("param_appl_id",$appl_id);
      }
      $tab[$i]["text"]=$v->name;
      $tab[$i]["id"]=$v->id;
      if ($appl_id == $v->id) {
	$appl_sel=$v;
	$tab[$i]["selected"]="selected";
      } else {
	$tab[$i]["selected"]="";
      }
      $i++;
    }
  }

  $action->lay->SetBlockData("SELAPPLI",$tab);
  $action->parent->AddJsRef("change_acl.js");


  // Set the form element
  $form = new SubForm("edit",350,330,$standurl."app=APPMNG&action=PARAM_MOD",
                                     $standurl."app=APPMNG&action=PARAM_EDIT");
  $form->SetParam("id","-1");
  $form->SetParam("creation","");
  $form->SetParam("name","");
  $form->SetParam("val","");

  $form->SetKey("id");

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsCode($form->GetMainJs());
  $action->lay->set("MAINFORM",$form->GetMainForm());

  $add_icon = new Layout($action->GetLayoutFile("add_icon_param.xml"),$action);
  $add_icon->set("JSCALL",$form->GetEmptyJsMainCall());
  $action->lay->set("ADD_ICON",$add_icon->gen());


  // Set the table elements
  $tablelay= new TableLayout($action->lay);

  $query = new QueryDb("","Param");
  $query->basic_elem->sup_where=array ("key=$appl_id");
  $query->order_by="key,name";
  
  $tablelay->start=GetHttpVars("start");
  $tablelay->slice=20;
  $tablelay->array = $query->Query($tablelay->start,$tablelay->slice,"LISTC");
  $tablelay->nb_tot = $query->nb;

  if ($tablelay->nb_tot!=0) {
      $tablelay->fields= array("name","key","imgedit","edit","delete","val");
    $jsscript=$form->GetLinkJsMainCall();
    // Affect the modif icons
    reset ($tablelay->array);
    while(list($k,$v) = each($tablelay->array)) {
      $tablelay->array[$k]->imgedit = "<img border=0 src=\"".$action->GetImageUrl("edit.gif")."\" alt=\"".$action->text("edit")."\">";
      $tablelay->array[$k]->edit = str_replace("[id]",$v->name,$jsscript);
      $tablelay->array[$k]->delete = "<img border=0 src=\"".$action->GetImageUrl("delete.gif")."\" alt=\"".$action->text("delparam")."\">";

    } 
  } else {
    $tablelay->fields= array("name","val","imgedit","delete");
    $tablelay->array[0]->imgedit='&nbsp;';
    $tablelay->array[0]->delete='&nbsp;';
    $tablelay->array[0]->name='--&nbsp;';
    $tablelay->array[0]->val='--&nbsp;';
    $tablelay->array[0]->key='';
    $action->lay->Set("ERR_MSG", "$err<BR>Pas de param&egrave;tres d&eacute;finis pour cette application");
  } 
      $baseurl=$action->GetParam("CORE_BASEURL");

      $tablelay->page_link= $baseurl."app=".$action->parent->name."&action=".$action->name."&start=%s";

      $tablelay->prev="<img border=0 src=\"".$action->GetImageUrl("prev.png")."\">";
      $tablelay->next="<img  border=0 src=\"".$action->GetImageUrl("next.png")."\">";

      $tablelay->Set();


}
?>
