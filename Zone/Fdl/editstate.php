<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * State document edition
 *
 * @author Anakeen 2000
 * @version $Id: editstate.php,v 1.25 2008/11/13 16:45:57 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.WDoc.php");

include_once ("Class.QueryDb.php");
include_once ("FDL/freedom_util.php");
include_once ("FDL/editutil.php");
include_once ("FDL/editcard.php");
// -----------------------------------
function editstate(&$action)
{
    //print "<HR>EDITCARD<HR>";
    // Get All Parameters
    $docid = GetHttpVars("id", 0); // document to edit
    $classid = GetHttpVars("classid", 0); // use when new doc or change class
    $dirid = GetHttpVars("dirid", 0); // directory to place doc if new doc
    $usefor = GetHttpVars("usefor"); // special uses
    $wneed = (GetHttpVars("wneed", "N") == "Y"); // with needed js
    
    editmode($action);
    $dbaccess = $action->GetParam("FREEDOM_DB");
    if (!is_numeric($classid)) $classid = getFamIdFromName($dbaccess, $classid);
    // ------------------------------------------------------
    //  new or modify ?
    if ($docid == 0) {
        if ($classid > 0) {
            $doc = createDoc($dbaccess, $classid); // the doc inherit from chosen class
            if (!$doc) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document") , $classid));
            $doc->id = $classid;
        }
    } else {
        // when modification
        $doc = new_Doc($dbaccess, $docid);
    }
    $action->lay->set("tstates", "");
    $action->lay->set("ttransid", "");
    $action->lay->set("askes", "");
    $action->lay->Set("Wattrnid", "");
    $action->lay->Set("Wattrntitle", "");
    $action->lay->Set("dcomment", "hidden");
    $action->lay->Set("WID", false);
    $action->lay->set("dvalidate", "");
    if (($usefor != "D") && ($usefor != "Q")) {
        
        if ($doc->wid > 0) {
            // compute the changed state
            $wdoc = new_Doc($dbaccess, $doc->wid);
            $wdoc->Set($doc);
            
            if (in_array($doc->state, $wdoc->nosave)) {
                $action->lay->set("dvalidate", "none"); // don't see validate button if we want force change state
                
            } else {
                $action->lay->setBlockData("UNCHANGE", array(
                    array(
                        "zou"
                    )
                ));
            }
            $dcolor = $action->getParam("COLOR_A9");
            $fstate = $wdoc->GetFollowingStates();
            $tjsstate = array();
            $tjstransid = array();
            $tjsaskes = array();
            $action->lay->Set("initstatevalue", $doc->state);
            $action->lay->Set("initstatename", $action->text($doc->state));
            $tstate = array();
            $taskes = array();
            if (isset($wdoc->autonext[$doc->state])) $dstate = $wdoc->autonext[$doc->state];
            $action->lay->Set("dstate", ""); // default state
            foreach ($fstate as $k => $v) {
                $tr = $wdoc->getTransition($doc->state, $v);
                $tk = $tr["id"];
                $tstate[$k]["statevalue"] = $v;
                if ($v == $dstate) {
                    $tstate[$k]["checked"] = "selected";
                    $action->lay->Set("dstate", $dstate);
                    $action->lay->Set("dcomment", "visible");
                    $tstate[$k]["dsubmit"] = "boldstate";
                } else {
                    $tstate[$k]["checked"] = "";
                    $tstate[$k]["dsubmit"] = "state";
                }
                
                $tstate[$k]["statename"] = _($v);
                if (_("To" . $v) == "To" . $v) $lnextstate = sprintf(_("to %s") , _($v));
                else $lnextstate = _("To" . $v);
                $tstate[$k]["tostatename"] = ucfirst($lnextstate);
                $tstate[$k]["asktitle"] = str_replace("'", "&rsquo;", sprintf(_("parameters for %s state") , _($v)));
                $tstate[$k]["transid"] = $tk;
                $color = $wdoc->getColor($v);;
                $tstate[$k]["color"] = ($color) ? $color : $dcolor;
                if (is_array($tr["ask"])) $tjsaskes[] = "['" . implode("','", $tr["ask"]) . "']";
                else $tjsaskes[] = "[]";
                if (is_array($tr["ask"])) $taskes = array_merge($taskes, $tr["ask"]);
                $tjsstate[] = $v;
                $tjstransid[] = $tk;
            }
            $action->lay->set("tstates", "'" . implode("','", $tjsstate) . "'");
            $action->lay->set("ttransid", "'" . implode("','", $tjstransid) . "'");
            $action->lay->set("askes", "" . strtolower(implode(",", $tjsaskes)) . "");
            $action->lay->SetBlockData("NEWSTATE", $tstate);
            $action->lay->Set("WID", true);
            $action->lay->Set("NOSTATE", count($tstate) == 0);
            if ($wdoc->viewlist == "button") {
                $action->lay->SetBlockData("BUTTONSTATE", array(
                    0 => array(
                        "boo"
                    )
                ));
            } elseif ($wdoc->viewlist == "none") {
                $action->lay->Set("WID", false);
            } else {
                $action->lay->SetBlockData("LISTSTATE", array(
                    0 => array(
                        "boo"
                    )
                ));
            }
            $task = array();
            $tneed = array();
            $tinputs = array();
            $taskes = array_unique($taskes);
            foreach ($taskes as $ka => $va) {
                $oa = $wdoc->getAttribute($va);
                if ($oa) {
                    if ($oa->needed) $tneed[$oa->id] = $oa->getLabel();
                    if ($oa->usefor == 'Q') {
                        $wval = $wdoc->getParamValue($oa->id);
                        $wval = $wdoc->getValueMethod($wval);
                    } else {
                        $wval = $wdoc->getValue($oa->id);
                    }
                    $tinputs[] = array(
                        "alabel" => $oa->getLabel() ,
                        "labelclass" => ($oa->needed) ? "FREEDOMLabelNeeded" : "FREEDOMLabel",
                        "avalue" => getHtmlInput($wdoc, $oa, $wval) ,
                        "aid" => $oa->id,
                        "visibility" => ($oa->visibility == "H") ? "hidden" : "visible"
                    );
                    if ($oa->needed) $tneed[$oa->id] = $oa->getLabel();
                }
            }
            $action->lay->SetBlockData("FINPUTS", $tinputs);
            $action->lay->Set("Wattrntitle", "'" . implode("','", str_replace("'", "&rsquo;", $tneed)) . "'");
            $action->lay->Set("Wattrnid", "'" . implode("','", array_keys($tneed)) . "'");
        } else {
            $fdoc = $doc->getFamDoc();
            
            $action->lay->Set("NOSTATE", false);
            if ($fdoc->schar == "R") {
                $action->lay->SetBlockData("COMMENT", array(
                    0 => array(
                        "boo"
                    )
                ));
            }
        }
        if ($wneed) {
            setNeededAttributes($action, $doc);
            if ($wdoc->viewlist == "button") {
                $action->lay->set("dvalidate", "none");
            }
        }
    }
}
?>
