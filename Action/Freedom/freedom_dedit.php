<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: freedom_dedit.php,v 1.7 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: freedom_dedit.php,v 1.7 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_dedit.php,v $
// ---------------------------------------------------------------
include_once ("FDL/freedom_util.php");
include_once ("FDL/Lib.Dir.php");
// -----------------------------------
function freedom_dedit(&$action)
{
    // -----------------------------------
    // Get All Parameters
    $docid = 0; //GetHttpVars("id",0);        // document to edit
    $classid = GetHttpVars("classid", 0); // use when new doc or change class
    
    // Set the globals elements
    $dbaccess = $action->GetParam("FREEDOM_DB");
    if ($docid > 0) {
        
        $doc = new_Doc($dbaccess, $docid);
        
        if (!$doc->isAlive()) {
            // the doesn't exist
            $docid = 0; // to recreate a new one
            
        }
    }
    
    if ($docid == 0) {
        // create default if needed
        $doc = createDoc($dbaccess, $classid);
        $fdoc = new DocFam($dbaccess, $classid);
        
        $doc->usefor = 'D'; // default document
        $doc->profid = $fdoc->profid; // same profil as familly doc
        $doc->title = sprintf(_("default values for %s") , $fdoc->title);
        $doc->setDefaultValues($fdoc->getDefValues());
        $err = $doc->Add();
        
        if ($err != "") $action->exitError($err);
        $docid = $doc->id;
        // insert them if its family
        $fdoc = new DocFam($dbaccess, $classid);
        $fdoc->ddocid = $docid;
        $err = $fdoc->modify();
        if ($err != "") $action->exitError($err);
    }
    
    redirect($action, GetHttpVars("app") , "FREEDOM_EDIT&id=$docid");
}
?>
