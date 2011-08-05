<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Display family attributes
 *
 * @author Anakeen 2000
 * @version $Id: defattr.php,v 1.28 2009/01/14 09:18:05 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Lib.Dir.php");
include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocAttr.php");

function defattr(&$action)
{
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $docid = GetHttpVars("id", 0);
    $classid = GetHttpVars("classid", 0); // use when new doc or change class
    $dirid = GetHttpVars("dirid", 0); // directory to place doc if new doc
    
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/geometry.js");
    
    $doc = new_Doc($dbaccess, $docid);
    if (!$doc->isAlive()) {
        $action->exitError(sprintf(_("document %s not found") , $docid));
    } elseif ($doc->doctype != 'C') {
        $action->exitError(sprintf(_("document %s is not a family") , $doc->getTitle()));
    }
    $docid = $doc->id;
    
    $action->lay->Set("docid", $docid);
    $action->lay->Set("dirid", $dirid);
    // build values type array
    $odocattr = new DocAttr($dbaccess);
    
    $action->lay->Set("TITLE", _("new document family"));
    // when modification
    if (($classid == 0) && ($docid != 0)) $classid = $doc->fromid;
    else
    // to show inherit attributes
    if (($docid == 0) && ($classid > 0)) $doc = new_Doc($dbaccess, $classid); // the doc inherit from chosen class
    $selectclass = array();
    $tclassdoc = GetClassesDoc($dbaccess, $action->user->id, $classid, "TABLE");
    while (list($k, $cdoc) = each($tclassdoc)) {
        $selectclass[$k]["idcdoc"] = $cdoc["id"];
        $selectclass[$k]["classname"] = $cdoc["title"];
        $selectclass[$k]["selected"] = "";
    }
    
    $selectframe = array();
    
    $nbattr = 0; // if new document
    // display current values
    $newelem = array();
    if ($docid > 0) {
        // control if user can update
        $err = $doc->CanLockFile();
        if ($err != "") $action->ExitError($err);
        $action->lay->Set("TITLE", $doc->title);
    }
    if (($classid > 0) || ($doc->doctype = 'C')) {
        // selected the current class document
        while (list($k, $cdoc) = each($selectclass)) {
            
            if ($classid == $selectclass[$k]["idcdoc"]) {
                
                $selectclass[$k]["selected"] = "selected";
            }
        }
        
        $ka = 0; // index attribute
        //    ------------------------------------------
        //  -------------------- FIELDSET ----------------------
        $tattr = $doc->GetFieldAttributes();
        
        $selectframe = array();
        $selectframe[0]["framevalue"] = "-";
        $selectframe[0]["frameid"] = "";
        $selectframe[0]["frameclass"] = "";
        $selectframe[0]["selected"] = "";
        $selecttab = $selectframe;
        
        foreach ($tattr as $k => $attr) {
            if ($attr->docid > 0 && $attr->usefor != 'Q') {
                $selectframe[$k]["framevalue"] = $attr->getLabel();
                $selectframe[$k]["frameid"] = $attr->id;
                $selectframe[$k]["frameclass"] = strtok($attr->type, '(');
                $selectframe[$k]["selected"] = "";
                if ($attr->type == "tab") $selecttab[$k] = $selectframe[$k];
            }
        }
        foreach ($tattr as $k => $attr) {
            if ($attr->docid > 0 && $attr->usefor != 'Q') {
                $newelem[$k]["attrid"] = $attr->id;
                $newelem[$k]["attrname"] = $attr->getLabel();
                $newelem[$k]["neweltid"] = $k;
                $newelem[$k]["visibility"] = $attr->visibility;
                $newelem[$k]["options"] = $attr->options;
                $newelem[$k]["typevalue"] = $attr->type;
                $newelem[$k]["classvalue"] = strtok($attr->type, '(') . ' F' . strtok($attr->fieldSet->type, '(');
                $newelem[$k]["disabledid"] = "disabled";
                $newelem[$k]["order"] = "0";
                $newelem[$k]["displayorder"] = - 2;
                $newelem[$k]["profond"] = getAttributeProfunder($attr) * 10;
                $newelem[$k]["profundator"] = getPuceAttributeProfunder($attr);
                $newelem[$k]["SELECTFRAME"] = "SELECTFRAME_$k";
                
                if ($attr->type == "frame") {
                    foreach ($selecttab as $kopt => $opt) {
                        if ($opt["frameid"] == $attr->fieldSet->id) {
                            $selecttab[$kopt]["selected"] = "selected";
                        } else {
                            $selecttab[$kopt]["selected"] = "";
                        }
                    }
                    $action->lay->SetBlockData($newelem[$k]["SELECTFRAME"], $selecttab);
                }
                if ($attr->docid == $docid) {
                    $newelem[$k]["disabled"] = "";
                } else {
                    $newelem[$k]["disabled"] = "disabled";
                }
                // unused be necessary for layout
                $newelem[$k]["link"] = "";
                $newelem[$k]["phpfile"] = "";
                $newelem[$k]["phpfunc"] = "";
                $newelem[$k]["phpconstraint"] = "";
                $newelem[$k]["elink"] = "";
                $newelem[$k]["abscheck"] = "";
                $newelem[$k]["neededcheck"] = "";
                $newelem[$k]["titcheck"] = "";
            }
            $ka++;
        }
        //    ------------------------------------------
        //  -------------------- NORMAL ----------------------
        $tattr = $doc->GetNormalAttributes();
        
        uasort($tattr, "tordered");
        reset($tattr);
        while (list($k, $attr) = each($tattr)) {
            if ($attr->type == "array") {
                $selectframe[$k]["framevalue"] = $attr->getLabel();
                $selectframe[$k]["frameid"] = $attr->id;
                $selectframe[$k]["selected"] = "";
            }
            $newelem[$k]["attrid"] = $attr->id;
            $newelem[$k]["attrname"] = $attr->getLabel();
            $newelem[$k]["order"] = $attr->ordered;
            $newelem[$k]["displayorder"] = $attr->ordered;
            $newelem[$k]["profond"] = getAttributeProfunder($attr) * 10;
            $newelem[$k]["profundator"] = getPuceAttributeProfunder($attr);
            $newelem[$k]["visibility"] = $attr->visibility;
            $newelem[$k]["link"] = $attr->link;
            $newelem[$k]["phpfile"] = $attr->phpfile;
            $newelem[$k]["phpfunc"] = htmlspecialchars($attr->phpfunc);
            $newelem[$k]["options"] = $attr->options;
            $newelem[$k]["phpconstraint"] = $attr->phpconstraint;
            $newelem[$k]["elink"] = $attr->elink;
            $newelem[$k]["disabledid"] = "disabled";
            $newelem[$k]["neweltid"] = $k;
            if ($attr->isInAbstract) {
                $newelem[$k]["abscheck"] = "checked";
            } else {
                $newelem[$k]["abscheck"] = "";
            }
            if ($attr->isInTitle) {
                $newelem[$k]["titcheck"] = "checked";
            } else {
                $newelem[$k]["titcheck"] = "";
            }
            
            $newelem[$k]["neededcheck"] = ($attr->needed) ? "checked" : "";
            
            if (($attr->docid == $docid) && ($attr->usefor != "A")) {
                $newelem[$k]["disabled"] = "";
            } else {
                $newelem[$k]["disabled"] = "disabled";
            }
            
            $newelem[$k]["typevalue"] = $attr->type;
            $newelem[$k]["classvalue"] = strtok($attr->type, '(') . ' F' . strtok($attr->fieldSet->type, '(');
            //if (($attr->repeat) && (!$attr->inArray())) $newelem[$k]["typevalue"].="list"; // add list if repetable attribute without array
            if ($attr->format != "") $newelem[$k]["typevalue"].= "(\"" . $attr->format . "\")";
            if ($attr->eformat != "") $newelem[$k]["phpfunc"] = "[" . $attr->eformat . "]" . $newelem[$k]["phpfunc"];
            
            $selectedSet = false;
            
            foreach ($selectframe as $kopt => $opt) {
                if ($opt["frameid"] == $attr->fieldSet->id) {
                    $selectframe[$kopt]["selected"] = "selected";
                    $selectedSet = true;
                    if ($newelem[$kopt]["displayorder"] < 0) {
                        $newelem[$kopt]["displayorder"] = $attr->ordered - 1;
                        if ($attr->fieldSet->fieldSet && $attr->fieldSet->fieldSet->id) {
                            if ($newelem[$attr->fieldSet->fieldSet->id]["displayorder"] < 0) {
                                $newelem[$attr->fieldSet->fieldSet->id]["displayorder"] = $newelem[$kopt]["displayorder"] - 1;
                            }
                        }
                    }
                } else {
                    $selectframe[$kopt]["selected"] = "";
                }
            }
            
            if (!$attr->fieldSet) {
                simpleQuery($dbaccess, sprintf("select frameid from docattr where id='%s'", $attr->id) , $kset, true, true);
                
                $selectframe[$kset] = $selectframe[0];
                $selectframe[$kset]["selected"] = "selected";
                $selectframe[$kset]["framevalue"] = "INVALID $kset";
                $selectframe[$kset]["frameid"] = "$kset";
                $selectframe[$kset]["frameclass"] = "invalid";
                $newelem[$k]["classvalue"] = "invalid";
            }
            $newelem[$k]["SELECTFRAME"] = "SELECTFRAME_$k";
            $action->lay->SetBlockData($newelem[$k]["SELECTFRAME"], $selectframe);
            
            $ka++;
        }
    }
    // reset default values
    while (list($kopt, $opt) = each($selectframe)) $selectframe[$kopt]["selected"] = "";
    
    $action->lay->SetBlockData("SELECTCLASS", $selectclass);
    
    uasort($newelem, 'sortnewelem');
    //    ------------------------------------------
    //  -------------------- MENU ----------------------
    $tattr = $doc->GetMenuAttributes(true);
    
    foreach ($tattr as $k => $attr) {
        if ($attr->docid > 0) {
            $newelem[$k]["attrid"] = $attr->id;
            $newelem[$k]["attrname"] = $attr->getLabel();
            $newelem[$k]["neweltid"] = $k;
            $newelem[$k]["visibility"] = $attr->visibility;
            $newelem[$k]["typevalue"] = $attr->type;
            $newelem[$k]["classvalue"] = $attr->type;
            $newelem[$k]["order"] = $attr->ordered;
            $newelem[$k]["displayorder"] = $attr->ordered;
            $newelem[$k]["disabledid"] = "disabled";
            $newelem[$k]["options"] = $attr->options;
            $newelem[$k]["SELECTFRAME"] = "SELECTFRAME_$k";
            if ($attr->docid == $docid) {
                $newelem[$k]["disabled"] = "";
            } else {
                $newelem[$k]["disabled"] = "disabled";
            }
            
            $newelem[$k]["link"] = $attr->link;
            $newelem[$k]["phpfunc"] = $attr->precond;;
            // unused be necessary for layout
            $newelem[$k]["phpfile"] = "";
            $newelem[$k]["phpconstraint"] = "";
            $newelem[$k]["elink"] = "";
            $newelem[$k]["abscheck"] = "";
            $newelem[$k]["titcheck"] = "";
        }
        $ka++;
    }
    //    ------------------------------------------
    //  -------------------- Action ----------------------
    $tattr = $doc->GetActionAttributes();
    
    foreach ($tattr as $k => $attr) {
        if ($attr->docid > 0) {
            $newelem[$k]["attrid"] = $attr->id;
            $newelem[$k]["attrname"] = $attr->getLabel();
            $newelem[$k]["neweltid"] = $k;
            $newelem[$k]["visibility"] = $attr->visibility;
            $newelem[$k]["typevalue"] = $attr->type;
            $newelem[$k]["classvalue"] = $attr->type;
            $newelem[$k]["order"] = $attr->ordered;
            $newelem[$k]["displayorder"] = $attr->ordered;
            $newelem[$k]["disabledid"] = "disabled";
            $newelem[$k]["options"] = $attr->options;
            $newelem[$k]["SELECTFRAME"] = "SELECTFRAME_$k";
            if ($attr->docid == $docid) {
                $newelem[$k]["disabled"] = "";
            } else {
                $newelem[$k]["disabled"] = "disabled";
            }
            
            $newelem[$k]["link"] = $attr->link;
            $newelem[$k]["phpfile"] = $attr->wapplication;
            $newelem[$k]["phpfunc"] = $attr->waction;
            // unused be necessary for layout
            $newelem[$k]["phpconstraint"] = "";
            $newelem[$k]["elink"] = "";
            $newelem[$k]["abscheck"] = "";
            $newelem[$k]["titcheck"] = "";
        }
        $ka++;
    }
    // add 3 new attributes to be defined
    
    for ($k = $ka; $k < 3 + $ka; $k++) {
        $newelem[$k]["neweltid"] = $k;
        $newelem[$k]["attrname"] = "";
        $newelem[$k]["disabledid"] = "";
        $newelem[$k]["typevalue"] = "";
        $newelem[$k]["classvalue"] = "";
        $newelem[$k]["visibility"] = "W";
        $newelem[$k]["link"] = "";
        $newelem[$k]["elink"] = "";
        $newelem[$k]["phpfile"] = "";
        $newelem[$k]["phpfunc"] = "";
        $newelem[$k]["phpconstraint"] = "";
        $newelem[$k]["order"] = "";
        $newelem[$k]["displayorder"] = "";
        $newelem[$k]["attrid"] = "";
        
        $newelem[$k]["SELECTFRAME"] = "SELECTFRAME_$k";
        $action->lay->SetBlockData($newelem[$k]["SELECTFRAME"], $selectframe);
        $newelem[$k]["disabled"] = "";
    }
    unset($newelem["FIELD_HIDDENS"]);
    $action->lay->SetBlockData("NEWELEM", $newelem);
}
/** 
 * use to usort attributes
 * @param BasicAttribute $a
 * @param BasicAttribute $b
 */
function sortnewelem($a, $b)
{
    if (isset($a["displayorder"]) && isset($b["displayorder"])) {
        if ($a["displayorder"] == $b["displayorder"]) return 0;
        if ($a["displayorder"] > $b["displayorder"]) return 1;
        return -1;
    }
    if (isset($a["displayorder"])) return 1;
    if (isset($b["displayorder"])) return -1;
    return 0;
}

function getAttributeProfunder(&$oa)
{
    if (!$oa) return 0;
    if (!$oa->fieldSet) return 0;
    if ($oa->fieldSet->id == 'FIELD_HIDDENS') return 0;
    return 1 + getAttributeProfunder($oa->fieldSet);
}
function getPuceAttributeProfunder(&$oa)
{
    $p = getAttributeProfunder($oa);
    switch ($p) {
        case 0:
            return '';
        case 1:
            if ($oa->fieldSet->type == 'frame') {
                return '<span class="frame">---</span>';
            } elseif ($oa->fieldSet->type == 'tab') {
                return '<span class="tab">---</span>';
            } else {
                return '<span class="unknow">---</span>';
            }
        case 2:
            if ($oa->fieldSet->type == 'array') {
                return '<span class="frame">---</span><span class="array">---</span>';
            } elseif ($oa->fieldSet->type == 'frame') {
                return '<span class="tab">---</span><span class="frame">---</span>';
            } else {
                return '<span class="unknow">---</span>';
            }
        case 3:
            return '<span class="tab">---</span><span class="frame">---</span><span class="array">---</span>';
    }
}
?>
