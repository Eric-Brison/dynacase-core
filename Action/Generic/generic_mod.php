<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Modify a document
 *
 * @author Anakeen
 * @version $Id: generic_mod.php,v 1.34 2008/03/14 13:58:03 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/modcard.php");

include_once ("FDL/Class.DocFam.php");
include_once ("FDL/Class.Dir.php");
// -----------------------------------
function generic_mod(Action & $action)
{
    // -----------------------------------
    // Get all the params
    $dirid = $action->getArgument("dirid", 0);
    $docid = $action->getArgument("id", 0);
    $catgid = $action->getArgument("catgid", 0);
    $retedit = ($action->getArgument("retedit", "N") == "Y"); // true  if return need edition
    $noredirect = ($action->getArgument("noredirect") == "1"); // true  if return need edition
    $quicksave = ($action->getArgument("quicksave") == "1"); // true  if return need edition
    $rzone = $action->getArgument("rzone"); // special zone when finish edition
    $rvid = $action->getArgument("rvid"); // special zone when finish edition
    $viewext = $action->getArgument("viewext") == "yes"; // special zone when finish edition
    $autoclose = $action->getArgument("autoclose") == "yes"; // special zone when finish edition
    $recallhelper = $action->getArgument("recallhelper") == "yes"; // special zone when finish edition
    $updateAttrid = $action->getArgument("updateAttrid");
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $action->parent->addJsRef("GENERIC:generic_mod.js", true);
    $err = modcard($action, $ndocid, $info); // ndocid change if new doc
    if (!$noredirect) $action->AddWarningMsg($err);
    $doc = null;
    if ($err == "") {
        $doc = new_Doc($dbaccess, $ndocid);
        if ($docid > 0) AddLogMsg(sprintf(_("%s has been modified") , $doc->title));
        
        if ($docid == 0) { // new file => add in a folder
            AddLogMsg(sprintf(_("%s has been created") , $doc->title));
            
            $cdoc = $doc->getFamDoc();
            //if (($cdoc->dfldid>0) && ($dirid==0))  $dirid=$cdoc->dfldid;// we not insert in defaut folder
            if ($dirid > 0) {
                /**
                 * @var Dir $fld
                 */
                $fld = new_Doc($dbaccess, $dirid);
                if ($fld->locked == - 1) { // it is revised document
                    $dirid = $fld->latestId();
                    if ($dirid != $fld->id) $fld = new_Doc($dbaccess, $dirid);
                }
                if (method_exists($fld, "AddFile")) {
                    $err = $fld->AddFile($doc->id);
                    if ($err != "") {
                        //try in home folder
                        $home = $fld->getHome(false);
                        if ($home && ($home->id > 0)) {
                            $fld = $home;
                            $err = $fld->AddFile($doc->id);
                        }
                    }
                    
                    if ($err != "") {
                        $action->AddLogMsg($err);
                    } else {
                        if (($doc->doctype == 'D') || ($doc->doctype == 'S')) $action->AddActionDone("ADDFOLDER", $fld->initid);
                        else $action->AddActionDone("ADDFILE", $fld->initid);
                    }
                } else {
                    //try in home folder
                    $fld = new Dir($dbaccess);
                    $home = $fld->getHome(false);
                    if ($home && ($home->id > 0)) {
                        $fld = $home;
                        $err = $fld->AddFile($doc->id);
                    }
                }
            }
        }
    }
    
    if ($noredirect) {
        if ((!$err) && $updateAttrid && $doc) {
            $action->lay->set("updateData", json_encode(array(
                "id" => $doc->id,
                "title" => $doc->getTitle() ,
                "attrid" => $updateAttrid,
                "recallhelper" => $recallhelper
            )));
        } else {
            $action->lay->set("updateData", "null");
        }
        
        $action->lay->set("autoclose", $autoclose ? "true" : "false");
        $action->lay->set("id", $ndocid);
        if (is_array($info)) {
            foreach ($info as $k => $v) {
                $info[$k]["prefix"] = sprintf(_("constraint not validated for %s attribute") , $v["label"]);
            }
        }
        $action->lay->set("constraintinfo", json_encode($info));
        $action->lay->set("quicksave", $quicksave);
        if ($rzone != "") $zone = "&zone=$rzone";
        else $zone = "";
        if ($rvid != "") $zone = "&vid=$rvid";
        if ($err == "-") $err = "";
        $action->lay->set("error", json_encode($err));
        $warning = $action->parent->getWarningMsg();
        if ($warning && count($warning) > 0) $warning = implode("\n", $warning);
        else $warning = '';
        $action->lay->set("warning", json_encode($warning));
        if ($retedit) $action->lay->set("url", sprintf("?app=%s&action=%s$zone", getHttpVars("redirect_app", "GENERIC") , getHttpVars("redirect_act", "GENERIC_EDIT&id=$ndocid")));
        else {
            if ($viewext) $action->lay->set("url", sprintf("?app=%s&action=%s$zone", getHttpVars("redirect_app", "FDL") , getHttpVars("redirect_act", "VIEWEXTDOC$zone&refreshfld=Y&id=$ndocid")));
            else $action->lay->set("url", sprintf("?app=%s&action=%s$zone", getHttpVars("redirect_app", "FDL") , getHttpVars("redirect_act", "FDL_CARD$zone&refreshfld=Y&id=$ndocid")));
        }
        return;
    }
    
    if ($ndocid == 0) {
        redirect($action, GetHttpVars("redirect_app", "GENERIC") , GetHttpVars("redirect_act", "GENERIC_LOGO") , $action->GetParam("CORE_STANDURL"));
    }
    if ($retedit) {
        redirect($action, GetHttpVars("redirect_app", "GENERIC") , GetHttpVars("redirect_act", "GENERIC_EDIT&id=$ndocid") , $action->GetParam("CORE_STANDURL"));
    } else {
        
        if ($rzone != "") $zone = "&zone=$rzone";
        else $zone = "";
        if ($rvid != "") $zone = "&vid=$rvid";
        // $action->register("reload$ndocid","Y"); // to reload cached client file
        redirect($action, GetHttpVars("redirect_app", "FDL") , GetHttpVars("redirect_act", "FDL_CARD$zone&refreshfld=Y&id=$ndocid") , $action->GetParam("CORE_STANDURL"));
    }
}
?>
