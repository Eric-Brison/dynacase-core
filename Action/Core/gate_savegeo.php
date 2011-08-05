<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: gate_savegeo.php,v 1.2 2003/08/18 15:46:41 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: gate_savegeo.php,v 1.2 2003/08/18 15:46:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Core/gate_savegeo.php,v $
// ---------------------------------------------------------------






// -----------------------------------
// -----------------------------------
function gate_savegeo(&$action) {
// -----------------------------------

  $geometry    = GetHttpVars("geometry");    // the six geometries frame
  
  $action->parent->param->Set("GATE_GEO",implode(",",$geometry),
			      PARAM_USER.$action->user->id,$action->parent->id);



  redirect($action,"CORE","GATE_EDIT",
	   $action->GetParam("CORE_STANDURL"));

}
?>
