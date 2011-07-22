<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_preview.php,v 1.8 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: freedom_preview.php,v 1.8 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_preview.php,v $
// ---------------------------------------------------------------

include_once("FDL/duplicate.php");
include_once("FDL/modcard.php");


// -----------------------------------
// -----------------------------------
function freedom_preview(&$action) {
  // -----------------------------------
  
  $docid = GetHttpVars("id",0);
  $classid=GetHttpVars("classid",0);


  $dbaccess = $action->GetParam("FREEDOM_DB");

  if ($docid > 0) {
    $doc = new_Doc($dbaccess, $docid);

    $action->lay->Set("TITLE",$doc->title);
    $ndoc= duplicate($action, 0, $docid, true); // temporary document
    
    $ndoc->modify();
  } else {
    // new doc
    $ndoc = createDoc($dbaccess, $classid);
    if (! $ndoc) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document"),$classid));
    
    $ndoc->doctype='T';
    $err = $ndoc-> Add();
    if ($err != "")  $action->ExitError($err);
    
  }
  SetHttpVar("id", $ndoc->id);
  $err = modcard($action, $ndocid); // ndocid change if new doc

   
  $tdoc = new_Doc($dbaccess, $ndocid);
  $tdoc->modify();
  //if ($err != "")  $action-> ExitError($err);

}

?>
