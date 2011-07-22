<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: generic_duplicate.php,v 1.9 2007/11/27 16:36:19 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: generic_duplicate.php,v 1.9 2007/11/27 16:36:19 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Generic/generic_duplicate.php,v $
// ---------------------------------------------------------------


include_once("FDL/duplicate.php");

include_once("FDL/Class.Dir.php");
include_once("GENERIC/generic_util.php"); 


// -----------------------------------
function generic_duplicate(&$action) {
  // -----------------------------------

    // Get all the params      
  $dirid=GetHttpVars("dirid"); // where to duplicate
  $docid=GetHttpVars("id",0);       // doc to duplicate

  if ($dirid == "") {
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $fdoc = new_Doc($dbaccess,$docid);
    
    $dirid=$fdoc->dfldid;
  }
  $copy=duplicate($action, $dirid, $docid);

 
  redirect($action,"FDL","FDL_CARD&refreshfld=Y&id=".$copy->id, $action->GetParam("CORE_STANDURL"));
  
}


?>
