<?php
/**
 * Main page for WHAT
 *
 * @author Anakeen 2000 
 * @version $Id: main.php,v 1.4 2004/01/13 09:32:33 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */


include_once('Class.Application.php');
include_once('Class.Session.php');

function main(&$action) {
  global $HTTP_GET_VARS;
  $app = new Application();
  $app->Set($HTTP_GET_VARS["app"], $action->parent);
  $action->lay->set("APP_TITLE", $app->description);
  $action->lay->set("SESSION",$action->session->id);

  $appd = GetHttpVars("appd","CORE");
  $actd = GetHttpVars("actd","GATE");
  $action->lay->set("appd", $appd);
  $action->lay->set("actd", $actd);
      
  // reopen a new session
  $action->parent->session->Set("");
  $action->parent->SetSession($action->parent->session);
}
?>
