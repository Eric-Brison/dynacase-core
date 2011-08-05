<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: toc.php,v 1.3 2004/03/22 15:21:40 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: toc.php,v 1.3 2004/03/22 15:21:40 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Core/toc.php,v $
// ---------------------------------------------------------------
// $Log: toc.php,v $
// Revision 1.3  2004/03/22 15:21:40  eric
// change HTTP variable name to put register_globals = Off
//
// Revision 1.2  2003/08/18 15:46:41  eric
// phpdoc
//
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
  global $_GET;
  $app = $_GET["app"];
  $act = $_GET["action"];

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
