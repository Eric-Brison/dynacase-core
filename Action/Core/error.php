<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: error.php,v 1.12 2008/02/12 15:19:28 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

// $Id: error.php,v 1.12 2008/02/12 15:19:28 eric Exp $

//
//
//
include_once('Class.Application.php');

function error(&$action) {  

  $app = new Application();
  
  $app->Set($action->Read("LAST_ACT","CORE"), $action->parent);
  $action->lay->set("error", nl2br($action->Read("FT_ERROR",getHttpVars("err","Session Error"))));

  $action->lay->set("serror", str_replace("\n","\\n",addslashes($action->Read("FT_ERROR","Session Error"))));


  if (strlen($action->Read("FT_ERROR"))==0) print "<h1>Empty</h1>";
  $action->lay->set("appname", _($app->description));

  if ($app->name != "CORE") {
    $app = new Application();  
    $app->Set($action->Read("FT_ERROR_APP","CORE"), $action->parent);
    $actname = $action->Read("FT_ERROR_ACT","");

    $action->lay->set("appact", "");
    if ($actname != "") {
      $action->lay->set("appact", _($actname));
      $act = new Action();
      if ($act->Exists($actname,$app->id)) {
	$act->Set($actname, $app);
	if ($act->short_name != "")	$action->lay->set("appact", _($act->short_name));
      }       
    }    
  } 
  

  
  //clear error for next time
  if (strlen($action->Read("FT_ERROR"))>0) {
    // $action->ClearError();
  } 
  
}
?>
