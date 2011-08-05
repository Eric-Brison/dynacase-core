<?php
/**
 * Main page for WHAT
 *
 * @author Anakeen 2000 
 * @version $Id: main.php,v 1.12 2005/10/18 15:22:46 marc Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */


include_once('Class.Application.php');
include_once('Class.Session.php');

function main(&$action) {
  global $_GET;
  $app = new Application();
  $app->Set($_GET["app"], $action->parent);
  $action->lay->set("APP_TITLE", $app->description);
  $action->lay->set("SESSION",$action->session->id);

  $mainpdescr = $action->getParam("CORE_FRONTPAGE", "");
 
  $izone = 0; 
  $zonedef = array();
  $isOk = array( false, false, false );
  if ($mainpdescr!="" && $action->user->id!=1) {
    $tzone = explode("|", $mainpdescr);
    if (count($tzone)!=3) continue;
    foreach ($tzone as $k => $v) {
      $zargs = explode(":", $v);
      if (count($zargs)!=3) continue;
      if ($zargs[0]=="" || $zargs[1]=="" || $zargs[2]=="") continue;
      $zonedef[$izone]["app"] = $zargs[0];
      $zonedef[$izone]["action"] = $zargs[1];
      $zonedef[$izone]["size"] = $zargs[2];
      $isOk[$izone] = true;
      $izone++; 
   }
  }
  
    
  $headerSize = "52";
  $headerApp  = "CORE";
  $headerAct  = "HEAD";
  $mainSize = "*";
  $mainApp  = "CORE";
  $mainAct  = "GATE";
  $footerSize = "35";
  $footerApp  = "CORE";
  $footerAct  = "FOOTER";


  if ($action->parent->exists("FREEGATE") && ($action->getParam("GATE_USEOLD")!="yes")) {

    $mainApp = "FREEGATE";
    $mainAct = "FREEGATE_VIEWGATE";

  } else {
  
    if ($isOk[0] && $isOk[0] && $isOk[0]) {
      
      $headerSize = $zonedef[0]["size"];
      $headerApp  = $zonedef[0]["app"];
      $headerAct  = $zonedef[0]["action"];
      
      $mainSize = $zonedef[1]["size"];
      $mainApp  = $zonedef[1]["app"];
      $mainAct  = $zonedef[1]["action"];
      
      $footerSize = $zonedef[2]["size"];
      $footerApp  = $zonedef[2]["app"];
      $footerAct  = $zonedef[2]["action"];
      
    } 
  }

  $action->lay->set("headerSize", $headerSize);
  $action->lay->set("headerApp", $headerApp);
  $action->lay->set("headerAct", $headerAct);
  $action->lay->set("mainSize", $mainSize);
  $action->lay->set("mainApp", $mainApp);
  $action->lay->set("mainAct", $mainAct);
  $action->lay->set("footerSize", $footerSize);
  $action->lay->set("footerApp", $footerApp);
  $action->lay->set("footerAct", $footerAct);

  
  // reopen a new session
  $action->parent->session->Set("");
  $action->parent->SetSession($action->parent->session);
}
?>
