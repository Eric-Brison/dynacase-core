<?php
// $Id: main.php,v 1.2 2003/04/07 12:33:04 eric Exp $
// $Log: main.php,v $
// Revision 1.2  2003/04/07 12:33:04  eric
// portail
//
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.3  2001/11/14 15:23:45  eric
// modif mode opératoire pour standalone = 'R'
//
// Revision 1.2  2001/01/19 02:11:25  marianne
// Ajout param session sur retour au sommaire
//
// Revision 1.1  2000/10/13 08:52:39  marc
// Creation
//
//
include_once('Class.Application.php');

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
      
}
?>
