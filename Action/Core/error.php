<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: error.php,v 1.9 2003/08/18 15:46:41 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

// $Id: error.php,v 1.9 2003/08/18 15:46:41 eric Exp $

//
//
//
include_once('Class.Application.php');

function error(&$action) {

  $app = new Application();
  
  $app->Set($action->Read("LAST_ACT","CORE"), $action->parent);
  $action->lay->set("error", nl2br($action->Read("FT_ERROR","Session Error")));

  $action->lay->set("serror", str_replace("\n","\\n",addslashes($action->Read("FT_ERROR","Session Error"))));

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
