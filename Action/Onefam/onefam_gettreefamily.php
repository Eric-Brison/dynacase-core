<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Retrieve search from onefam
 *
 * @author Anakeen 2009
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Dir.php");
include_once ("FDL/Class.SearchDoc.php");
/**
 *  Retrieve search from onefam
 *
 * @param Action &$action current action
 * @global appid Http var : application name
 */
function onefam_gettreefamily(&$action)
{
    $out = onefam_getDataTreeFamily($action);
    
    $action->lay->noparse = true; // no need to parse after - increase performances
    $action->lay->template = json_encode($out);
}

function onefam_getDataTreeFamily(&$action)
{
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $tfs = array();
    
    $mids = explode(",", $action->getParam("ONEFAM_MIDS"));
    
    foreach ($mids as $fid) {
        if ($fid) {
            $cdoc = new_Doc($dbaccess, $fid);
            if ($cdoc->isAlive() && $cdoc->control('view') == "") {
                $fs = getFamilySearches($dbaccess, $fid);
                if ($fs) $tfs[] = $fs;
            }
        }
    }
    $utfs = array();
    
    $umids = explode(",", $action->getParam("ONEFAM_IDS"));
    
    foreach ($umids as $fid) {
        if ($fid && ($fs = getFamilySearches($dbaccess, $fid))) $utfs[] = $fs;
    }
    
    $out = array(
        "application" => array(
            "name" => $action->parent->name,
            "label" => _($action->parent->description)
        ) ,
        "user" => $utfs,
        "admin" => $tfs
    );
    return $out;
}
function getFamilySearches($dbaccess, $fid)
{
    $fam = new_doc($dbaccess, $fid);
    
    if ($fam->isAlive()) {
        $to["info"] = array(
            "id" => $fam->id,
            "title" => $fam->getTitle() ,
            "icon" => $fam->getIcon()
        );
        
        $s = new SearchDoc($dbaccess, "SEARCH");
        $s->addFilter("owner=" . $fam->userid);
        $s->addFilter("se_famid='" . $fam->id . "'");
        $s->setObjectReturn();
        $s->setDebugMode();
        $t = $s->search();
        while ($v = $s->nextDoc()) {
            
            $to["userSearches"][] = array(
                "id" => $v->id,
                "icon" => $v->getIcon() ,
                "title" => $v->getTitle()
            );
        }
        
        $s = new SearchDoc($dbaccess, "SEARCH");
        $s->dirid = $fam->dfldid;
        $s->setObjectReturn();
        $s->setDebugMode();
        $t = $s->search();
        while ($v = $s->nextDoc()) {
            
            $to["adminSearches"][$fid] = array(
                "id" => $v->id,
                "icon" => $v->getIcon() ,
                "title" => $v->getTitle()
            );
        }
        
        if ($fam->wid > 0) {
            $w = new_doc($dbaccess, $fam->wid);
            if ($w->isAlive()) {
                
                foreach ($w->getStates() as $c) {
                    $to["workflow"][$c] = array(
                        "state" => $c,
                        "label" => _($c) ,
                        "activity" => $w->getActivity($c) ,
                        "color" => $w->getColor($c)
                    );
                }
            }
        }
        
        return $to;
    } else return null;
}
?>