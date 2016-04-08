<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Display interface to change state
 *
 * @author Anakeen
 * @version $Id: editchangestate.php,v 1.8 2008/10/02 15:41:45 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Lib.Dir.php");
include_once ("FDL/editutil.php");
include_once ("FDL/editcard.php");
/**
 * Display editor to fix a document version
 * @param Action &$action current action
 * @global string $id Http var : document id
 * @global string $nstate Http var : next state id
 */
function editchangestate(Action & $action)
{
    $docid = GetHttpVars("id");
    $nextstate = GetHttpVars("nstate");
    $viewext = GetHttpVars("viewext");
    
    $dbaccess = $action->dbaccess;
    
    editmode($action);
    $doc = new_doc($dbaccess, $docid, true);
    if (!$doc->isAlive()) $action->exitError(sprintf(_("Document %s is not alive") , $docid));
    if ($doc->wid > 0) {
        $tneed = array();
        $err = $doc->lock(true); // autolock
        if ($err == "") $action->AddActionDone("LOCKDOC", $doc->id);
        /**
         * @var WDoc $wdoc
         */
        $wdoc = new_Doc($dbaccess, $doc->wid);
        $wdoc->Set($doc);
        $action->lay->set("noreason", false);
        $action->lay->set("realtransition", true);
        $fstate = $wdoc->GetFollowingStates();
        $tr = null;
        foreach ($fstate as $k => $v) {
            if ($v == $nextstate) {
                $tr = $wdoc->getTransition($doc->state, $v);
                $tinputs = array();
                if (!empty($tr["ask"]) && is_array($tr["ask"])) {
                    foreach ($tr["ask"] as $ka => $va) {
                        /**
                         * @var NormalAttribute $oa
                         */
                        $oa = $wdoc->getAttribute($va);
                        if ($oa) {
                            if ($oa->needed) $tneed[$oa->id] = $oa->getLabel();
                            if ($oa->usefor == 'Q') {
                                $wval = $wdoc->getFamilyParameterValue($oa->id);
                                $wval = $wdoc->getValueMethod($wval);
                            } else {
                                $wval = $wdoc->getRawValue($oa->id);
                            }
                            if ($edittpl = $oa->getOption("edittemplate")) {
                                $input = sprintf("[ZONE FDL:EDITTPL?id=%d&famid=%d&wiid=%d&zone=%s]", $wdoc->id, $wdoc->fromid, $doc->id, $edittpl);
                            } else {
                                $input = getHtmlInput($wdoc, $oa, $wval, "", "", false);
                            }
                            $tinputs[] = array(
                                "alabel" => $oa->getLabel() ,
                                "labelclass" => ($oa->needed) ? "FREEDOMLabelNeeded" : "FREEDOMLabel",
                                "atype" => $oa->type,
                                "avalue" => $input,
                                "aid" => $oa->id,
                                "idisplay" => ($oa->visibility == "H") ? "none" : ""
                            );
                            if ($oa->needed) $tneed[$oa->id] = $oa->getLabel();
                        }
                    }
                }
                $action->lay->set("noreason", ((!empty($tr["nr"]) && $tr["nr"] == true)));
                $action->lay->eSet("viewext", $viewext);
                $action->lay->setBlockData("FINPUTS", $tinputs);
            }
        }
        
        setNeededAttributes($action, $wdoc);
        $activity = $wdoc->getActivity($nextstate);
        if ($activity) {
            
            $action->lay->set("tonewstate", sprintf(_("to the %s activity") , $action->text($activity)));
        } else {
            $action->lay->set("tonewstate", sprintf(_("to the %s state") , $action->text($nextstate)));
        }
        if ($tr) {
            if (_($tr["id"]) == $tr["id"]) {
                if ($activity) {
                    $transitionLabel = sprintf(_("to %s") , $action->text($activity));
                } else {
                    $transitionLabel = sprintf(_("to %s") , _($nextstate));
                }
            } else $transitionLabel = _($tr["id"]);
        } else {
            $action->lay->set("realtransition", false);
            if ($activity) $transitionLabel = sprintf(_("to %s") , $action->text($activity));
            else $transitionLabel = sprintf(_("to %s") , $action->text($nextstate));
        }
        
        $action->lay->set("tostate", mb_ucfirst($transitionLabel));
        $action->lay->set("wcolor", $wdoc->getColor($nextstate));
        $action->lay->Set("Wattrntitle", json_encode(array_values($tneed)));
        $action->lay->Set("Wattrnid", json_encode(array_keys($tneed)));
        $action->lay->set("docid", $doc->id);
        $currentActivity = $wdoc->getActivity($doc->state);
        if ($currentActivity) {
            $explanation[] = sprintf(_("The current activity is \"%s\".") , _($currentActivity));
        } else {
            $explanation[] = sprintf(_("The current state is \"%s\".") , _($doc->getState()));
        }
        $viewState = false;
        if ($viewState) {
            $explanation[] = sprintf(_("The document will be stored with \"%s\" state.") , _($nextstate));
        }
        
        if ($activity) {
            $explanation[] = sprintf(_("The next activity will be \"%s\".") , $action->text($activity));
        } else {
            $explanation[] = sprintf(_("The next state will be \"%s\".") , _($nextstate));
        }
        $thetitle = mb_ucfirst($transitionLabel);
        $action->lay->eset("thetitle", $thetitle);
        $action->lay->set("thetitle_url", urlencode($thetitle));
        $action->lay->eset("nextstate", $nextstate);
        $action->lay->set("Explanations", nl2br(implode("\n", $explanation)));
        
        $style = $action->parent->getParam("STYLE");
        if ($viewext) {
            $action->parent->AddCssRef("STYLE/DEFAULT/Layout/EXT-ADAPTER-SYSTEM.css");
            if (file_exists(DEFAULT_PUBDIR . "/STYLE/$style/Layout/EXT-ADAPTER-USER.css")) {
                $action->parent->AddCssRef("STYLE/$style/Layout/EXT-ADAPTER-USER.css");
            } else {
                $action->parent->AddCssRef("STYLE/DEFAULT/Layout/EXT-ADAPTER-USER.css");
            }
        }
    }
}
