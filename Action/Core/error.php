<?php
// $Id: error.php,v 1.7 2003/03/18 18:26:32 eric Exp $

//
//
//
include_once('Class.Application.php');

function error(&$action) {

  $app = new Application();
  
  $app->Set($action->Read("LAST_ACT","CORE"), $action->parent);
  $action->lay->set("error", nl2br($action->Read("FT_ERROR","Session Error")));

  $action->lay->set("serror", addslashes($action->Read("FT_ERROR","Session Error")));

  $action->lay->set("appname", _($app->description));

  if ($app->name != "CORE") {
    $app = new Application();
  
    $app->Set($action->Read("FT_ERROR_APP","CORE"), $action->parent);

    $actname = $action->Read("FT_ERROR_ACT","MAIN");



    $action->lay->set("appact", "");
    if ($actname != "") {
      $action->lay->set("appact", _($actname));
      $act = new Action();
      if ($act->Exists($actname,$app->id)) {
	$act->Set($actname, $app);
	$action->lay->set("appact", _($act->short_name));
      } 
      
    }
    
  } 
  

  
  //clear error for next time
  $action-> ClearError();
  
}
?>
