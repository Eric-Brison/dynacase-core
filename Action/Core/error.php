<?php
// $Id: error.php,v 1.1 2002/01/08 12:41:33 eric Exp $
// $Log: error.php,v $
// Revision 1.1  2002/01/08 12:41:33  eric
// first
//
// Revision 1.5  2002/01/04 12:51:45  eric
// correction mineure
//
// Revision 1.4  2001/11/14 15:22:38  eric
// ajout test en cas d'erreur
//
// Revision 1.3  2001/10/17 10:01:43  eric
// mise en place de i18n via gettext
//
// Revision 1.2  2001/08/20 16:46:09  eric
// correction cas limite
//
// Revision 1.1  2001/08/10 08:06:03  eric
// ajout action ERROR
//
//
//
include_once('Class.Application.php');

function error(&$action) {

  $app = new Application();
  
  $app->Set($action->Read("LAST_ACT","CORE"), $action->parent);
  $action->lay->set("error", $action->Read("FT_ERROR"),"Session Error");
  $action->lay->set("nexturl", $action->GetParam("CORE_BASEURL")."app=".$app->name);
  $action->lay->set("appname", _($app->description));

  if ($app->name != "CORE") {
    $app = new Application();
  
    $app->Set($action->Read("FT_ERROR_APP","CORE"), $action->parent);
    $erract= new Action();
    $erract->Set($action->Read("FT_ERROR_ACT","MAIN"), $app);


    $action->lay->set("appact", "");
    if ($erract->long_name != "")
      $action->lay->set("appact", _($erract->name)." ("._($erract->long_name).")");
    else if ($erract->name != "")
      $action->lay->set("appact", _($erract->name));
    
  } 
  

  
  //clear error for next time
  $action-> ClearError();
  
}
?>
