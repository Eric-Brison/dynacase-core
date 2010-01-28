<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: gate_modurl.php,v 1.2 2003/08/18 15:46:41 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: gate_modurl.php,v 1.2 2003/08/18 15:46:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Core/gate_modurl.php,v $
// ---------------------------------------------------------------






// -----------------------------------
// -----------------------------------
function gate_modurl(&$action) {
// -----------------------------------

  $turl[]    = GetHttpVars("urlG11");    // the six urls 
  $turl[]    = GetHttpVars("urlG12"); 
  $turl[]    = GetHttpVars("urlG21"); 
  $turl[]    = GetHttpVars("urlG22"); 
  $turl[]    = GetHttpVars("urlG31"); 
  $turl[]    = GetHttpVars("urlG32"); 
  

  $action->parent->param->Set("GATE_URL",implode(",",$turl),
			      PARAM_USER.$action->user->id,$action->parent->id);



  redirect($action,"CORE","GATE",
	   $action->GetParam("CORE_STANDURL"));

}
?>
