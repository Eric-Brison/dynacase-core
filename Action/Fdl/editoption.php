<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Edition of option sttribute for a document
 *
 * @author Anakeen 2004
 * @version $Id: editoption.php,v 1.3 2005/10/17 14:02:37 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");

include_once ("Class.QueryDb.php");
include_once ("GENERIC/generic_util.php");
// -----------------------------------
function editoption(&$action)
{
    // -----------------------------------
    // Get All Parameters
    $docid = GetHttpVars("id", 0); // document to edit
    $aid = GetHttpVars("aid"); // linked attribute id
    
    $aid = GetHttpVars("aid"); // linked attribute id
    // Set the globals elements
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $doc = getdocoption($action);
    
    $action->lay->Set("iconsrc", $doc->geticon());
    
    if ($doc->fromid > 0) {
        $fdoc = $doc->getFamDoc();
        $action->lay->Set("FTITLE", $fdoc->title);
    } else {
        $action->lay->Set("FTITLE", _("no family"));
    }
    
    $action->lay->Set("id", $docid);
    // control view of special constraint button
    $action->lay->Set("boverdisplay", "none");
    
    if (GetHttpVars("viewconstraint") == "Y") {
        $action->lay->Set("bconsdisplay", "none");
        if ($action->user->id == 1) $action->lay->Set("boverdisplay", ""); // only admin can do this
        
    } else {
        // verify if at least on attribute constraint
        $action->lay->Set("bconsdisplay", "none");
        /*
        $listattr = $doc->GetNormalAttributes();
        foreach ($listattr as $k => $v) {
        if ($v->phpconstraint != "")  {
        $action->lay->Set("bconsdisplay", "");
        break;
        }
        }
        */
    }
    $action->lay->set("tablefoot", "tableborder");
    $action->lay->set("tablehead", "tableborder");
    $action->lay->set("ddivfoot", "none");
    if ($action->Read("navigator", "") == "NETSCAPE") {
        if (preg_match("/rv:([0-9.]+).*/", $_SERVER['HTTP_USER_AGENT'], $reg)) {
            if (floatval($reg[1] >= 1.6)) {
                $action->lay->set("ddivfoot", "");
                $action->lay->set("tablefoot", "tablefoot");
                $action->lay->set("tablehead", "tablehead");
            }
        }
    }
    // information propagation
    $action->lay->Set("classid", $classid);
    $action->lay->Set("dirid", $dirid);
    $action->lay->Set("id", $docid);
    $action->lay->Set("aid", $aid);
}

function viewoption(&$action)
{
    // -----------------------------------
    // Get All Parameters
    $zonebodycard = GetHttpVars("zone", "FDL:VIEWOPTCARD:T"); // define view action
    $doc = getdocoption($action);
    if ($doc) {
        $zonedoc = $doc->viewDoc($zonebodycard);
        
        $action->lay->set("zonedoc", $zonedoc);
    } else {
        $action->lay->set("zonedoc", _("click to change option"));
    }
}
function getdocoption(&$action)
{
    // -----------------------------------
    // Get All Parameters
    $docid = GetHttpVars("id", 0); // document to edit
    
    $valopt = GetHttpVars("opt"); // value of linked attribute id
    // Set the globals elements
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $doc = new_Doc($dbaccess, $docid);
    if (!$doc->isAlive()) return false;
    $fdoc = $doc->getFamDoc();
    $fdoc->opt = $valopt;
    $topt = $fdoc->getXValues("opt");
    
    $doc->setDefaultValues($topt);
    $doc = $doc->copy(true, false);
    setHttpVar("id", $doc->id);
    
    return $doc;
}
?>
