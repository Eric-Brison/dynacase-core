<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Modification of documents
 *
 * @author Anakeen 2000
 * @version $Id: freedom_mod.php,v 1.24 2007/10/09 16:44:47 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/modcard.php");

include_once ("FDL/Class.Dir.php");
include_once ("FDL/Class.DocFam.php");
// -----------------------------------
function freedom_mod(Action & $action)
{
    // -----------------------------------
    // Get all the params
    $dirid = GetHttpVars("dirid", 0);
    $docid = GetHttpVars("id", 0);
    $retedit = GetHttpVars("retedit", "N") == "Y"; // true  if return need edition
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $err = modcard($action, $ndocid); // ndocid change if new doc
    if ($err != "") $action->AddWarningMsg($err);
    else {
        
        $doc = new_Doc($dbaccess, $ndocid);
        if ($docid > 0) $action->AddLogMsg(sprintf(_("%s has been modified") , $doc->title));
        
        if ($docid == 0) {
            AddLogMsg(sprintf(_("%s has been created") , $doc->title));
            $fld = null;
            if ($dirid > 0) {
                /**
                 * @var Dir $fld
                 */
                $fld = new_Doc($dbaccess, $dirid);
                if ($fld->doctype != 'D') $dirid = 0;
            }
            // first try in current folder
            if ($dirid > 0) {
                $err = $fld->AddFile($doc->id);
                if ($err != "") {
                    $action->AddLogMsg($err);
                    $dirid = 0;
                }
            }
            // second try in default folder for family
            /*
            if ($dirid == 0) {
            $cdoc = $doc->getFamDoc();
            if ($cdoc->dfldid>0)  {
            $dirid=$cdoc->dfldid;
            $fld = new_Doc($dbaccess,$dirid);
            $err=$fld->AddFile($doc->id);
            if ($err != "") {
            $action->AddLogMsg($err);
            $dirid=0;
            }
            }
            }
            */
            // third try in home folder
            if ($dirid == 0) {
                $fld = new Dir($dbaccess);
                $home = $fld->getHome();
                
                if ($home->id > 0) $fld = $home;
                $err = $fld->AddFile($doc->id);
                if ($err != "") $action->AddLogMsg($err);
            }
        }
    }
    
    if ($retedit) {
        redirect($action, GetHttpVars("redirect_app", "FREEDOM") , GetHttpVars("redirect_act", "FREEDOM_EDIT&id=$ndocid") , $action->GetParam("CORE_STANDURL"));
    } else {
        // $action->register("reload$ndocid","Y"); // to reload cached client file
        redirect($action, GetHttpVars("redirect_app", "FDL") , GetHttpVars("redirect_act", "FDL_CARD&refreshfld=Y&id=$ndocid") , $action->GetParam("CORE_STANDURL"));
    }
}
?>
