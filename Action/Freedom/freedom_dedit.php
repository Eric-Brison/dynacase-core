<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: freedom_dedit.php,v 1.7 2005/06/28 08:37:46 eric Exp $
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
function freedom_dedit(Action & $action)
{
    // -----------------------------------
    // Get All Parameters
    $docid = 0; //GetHttpVars("id",0);        // document to edit
    $classid = GetHttpVars("classid", 0); // use when new doc or change class
    // Set the globals elements
    $dbaccess = $action->dbaccess;
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
