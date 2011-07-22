<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: generic_changecatg.php,v 1.8 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: generic_changecatg.php,v 1.8 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Generic/generic_changecatg.php,v $
// ---------------------------------------------------------------


include_once("FDL/modcard.php");

include_once("FDL/Class.Dir.php");
include_once("GENERIC/generic_util.php");


// -----------------------------------
function generic_changecatg(&$action) {
  // -----------------------------------

  // special for onefam application
  // Get all the params      
   $dirids=GetHttpVars("dirid", getDefFld($action));
   $ndirids=GetHttpVars("ndirid"); // catg to deleted
   $docid=GetHttpVars("docid"); // the user to change catg




   $dbaccess = $action->GetParam("FREEDOM_DB");

   if (is_array($dirids)) {
     while (list($k,$dirid) = each($dirids)) {	
       $fld = new_Doc($dbaccess, $dirid);
       $fld->AddFile($docid);
     }
   }
   if (is_array($ndirids)) {
     while (list($k,$dirid) = each($ndirids)) {	
       $fld = new_Doc($dbaccess, $dirid);
       $err = $fld->DelFile($docid);

     }
   }
      
  

  

   redirect($action,"FDL","FDL_CARD&id=$docid");
  
}


?>
