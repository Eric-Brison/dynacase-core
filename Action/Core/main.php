<?php
/**
 * Main page for WHAT
 *
 * @author Anakeen 2000 
 * @version $Id: main.php,v 1.7 2004/07/08 08:52:03 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
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

      
  if ($action->parent->exists("FREEGATE") && ($action->getParam("GATE_USEOLD")!="yes")) {
    $action->lay->set("appd", "FREEGATE");
    $action->lay->set("actd", "FREEGATE_VIEWGATE");
  } else {
    $appd = GetHttpVars("appd","CORE");
    $actd = GetHttpVars("actd","GATE");
    $action->lay->set("appd", $appd);
    $action->lay->set("actd", $actd);
  }

  // reopen a new session
  $action->parent->session->Set("");
  $action->parent->SetSession($action->parent->session);
}
?>
