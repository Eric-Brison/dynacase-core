<?php
/**
 * Footer window
 *
 * @author Anakeen 2000 
 * @version $Id: footer.php,v 1.3 2005/02/17 07:54:22 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */



// -----------------------------------
function footer(&$action) {
  // -----------------------------------

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");


  $plugzone = $action->getParam("CORE_PLUGZONE");

  $action->lay->set("plugzone","");
  if ($plugzone != "") {
    if ( preg_match("/([A-Z]+):([^:]+)/", $plugzone, $reg)) {
      $appplug=$reg[1];
      $actplug=$reg[2];
      if ($idappplug=$action->parent->Exists($appplug)) {
      $permission = new Permission($action->dbaccess, array($action->user->id,$idappplug));

      if ($permission->isAffected() && (count($permission->privileges) > 0)) {
	  // can see the plug
	  $action->lay->set("plugzone","[ZONE $plugzone]");
	}
      }
      
    }
  }

}

// EOF