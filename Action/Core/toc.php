<?php
// ---------------------------------------------------------------
// $Id: toc.php,v 1.1 2002/01/08 12:41:33 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Core/toc.php,v $
// ---------------------------------------------------------------
//    O   Anakeen - 2000
//   O*O  Anakeen Hacking Group
//    O   hack@anakeen.com
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
// $Log: toc.php,v $
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.2  2000/10/10 19:09:11  marc
// Mise au point
//
// Revision 1.1  2000/10/06 19:37:44  marc
// Creation
//
//
// ---------------------------------------------------------------
include_once("Class.Action.php");


// -----------------------------------
function toc(&$action) {
// -----------------------------------
  global $HTTP_GET_VARS;
  $app = $HTTP_GET_VARS["app"];
  $act = $HTTP_GET_VARS["action"];

  echo "GET[app] = [".$app.", GET[action] = [".$act."]<br>";

  if ($app == "CORE") {
    $query = new QueryDb($action->dbaccess, "Application");
    $query->basic_elem->sup_where=array("available='Y'");
    $query->Query(0,0,"TABLE");
    $itoc = 0;
    if ($query->nb>0) { 
      while(list($k, $v) = each($query->list)) {
	if ($v["name"] != "CORE" ) {
	  $toc[$itoc]["base"]  = $action->parent->GetParam("CORE_BASEURL");
	  $toc[$itoc]["app"]   = $v["name"];
	  $toc[$itoc]["action"]  = "";
	  $toc[$itoc]["style"] = "tocitem0off";
	  $toc[$itoc]["label"] = $v["description"];
	  $itoc++;
	}
      }
    }
  } else {
    $appcalled = new Application();
    $appcalled->Set($app, $action->parent);
    $actcalled = new Action();
    $actcalled->Set($act, $appcalled);

    $query = new QueryDb($action->dbaccess, "Action");
    $query->order_by = "toc_order";
    echo "id_application=".$actcalled->id_application."<br>";
    if ($actcalled->father == 0) {
      $selectview = array( "toc='Y'", 
			   "available='Y'",
			   "father=0", 
			   "id_application=".$actcalled->id_application);
      // $exclude    = "id=".$actcalled->id;
    } else {
      $selectview = "father=".$actcalled->id;
      $exclude    = "id=".$actcalled->id;
    }
    $query->basic_elem->sup_where= $selectview;
    $query->Query(0,0,"TABLE");
    $itoc = 0;
    if ($query->nb>0) { 
      while(list($k, $v) = each($query->list)) {
	$toc[$itoc]["base"]  = $action->parent->GetParam("CORE_BASEURL");
	$toc[$itoc]["app"]   = $actcalled->parent->name;
	$toc[$itoc]["action"]  = $v["name"];
	$toc[$itoc]["style"] = ($v["name"]==$actcalled->name?"tocitem0on":"tocitem0off");
	$toc[$itoc]["label"] = $v["name"];
	 $itoc++;
      }
    }
  }
  if (isset($toc)) {
    $action->lay->SetBlockCorresp("TOC_ENTRY", "TOC_ENTRYURLROOT", "base");
    $action->lay->SetBlockCorresp("TOC_ENTRY", "TOC_ENTRYAPP", "app");
    $action->lay->SetBlockCorresp("TOC_ENTRY", "TOC_ENTRYPAGE", "action");
    $action->lay->SetBlockCorresp("TOC_ENTRY", "TOC_ENTRYSTYLE", "style");
    $action->lay->SetBlockCorresp("TOC_ENTRY", "TOC_ENTRYLABEL", "label");
    $action->lay->SetBlockData("TOC_ENTRY", $toc);
  } else {
    $action->lay->SetBlockData("TOC_ENTRY", NULL);
  }
}
?>
