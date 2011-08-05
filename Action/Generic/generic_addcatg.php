<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: generic_addcatg.php,v 1.8 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: generic_addcatg.php,v 1.8 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Generic/generic_addcatg.php,v $
// ---------------------------------------------------------------


include_once("FDL/modcard.php");

include_once("FDL/Class.Dir.php");
include_once("GENERIC/generic_util.php"); 


// -----------------------------------
function generic_addcatg(&$action) {
  // -----------------------------------

  // Get all the params      
   $dirid=GetHttpVars("dirid", getDefFld($action));
//   $newcatg=GetHttpVars("newcatg"); 

//   if ($newcatg == "") $action->exitError(_("the title of the new category cannot be empty"));
  


  $dbaccess = $action->GetParam("FREEDOM_DB");

  
      
  $err = modcard($action, $ndocid); // ndocid change if new doc

  if ($err != "")  $action-> ExitError($err);
  

  

  if ($dirid > 0)  {
    $fld = new_Doc($dbaccess, $dirid);

    $doc= new_Doc($dbaccess, $ndocid);
    
    $fld->AddFile($doc->id);
    
  } 
  redirect($action,"FDL","FDL_CARD&id=$ndocid");
  
}


?>
