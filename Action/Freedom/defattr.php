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
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Lib.Dir.php");
include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocAttr.php");

function defattr(Action & $action)
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
    
    if ($doc->fromid) {
        $action->lay->set("inherit", sprintf(_("Inherit from \"%s\"") , $doc->getTitle($doc->fromid)));
    } else {
        $action->lay->set("inherit", _("no inheritance"));
    }
    $fromids = $doc->getFromDoc();
    
    $sql = sprintf("select * from docattr where docid in (%s) and usefor != 'Q' order by ordered", implode(',', $fromids));
    
    simpleQuery($action->dbaccess, $sql, $attrs);
    
    foreach ($attrs as $k => $v) {
        // if ($v["type"]=="frame") $v["ordered"]=-1;
        // if ($v["type"]=="tab") $v["ordered"]=-2;
        $attrs[$v["id"]] = $v;
        unset($attrs[$k]);
    }
    
    $oDocAttr = new DocAttr($dbaccess);
    $oAttrs = $doc->getAttributes();
    foreach ($oAttrs as $oa) {
        if (($oa->usefor == 'A') && (empty($attrs[$oa->id]))) {
            
            $oDocAttr->id = $oa->id;
            $oDocAttr->type = $oa->type;
            $oDocAttr->docid = $oa->docid;
            $oDocAttr->usefor = $oa->usefor;
            $oDocAttr->ordered = $oa->ordered;
            $oDocAttr->visibility = $oa->visibility;
            $oDocAttr->labeltext = $oa->labelText;
            $oDocAttr->abstract = ($oa->isInAbstract) ? "Y" : "N";
            $oDocAttr->title = ($oa->isInTitle) ? "Y" : "N";
            $oDocAttr->needed = ($oa->needed) ? "Y" : "N";
            $oDocAttr->frameid = ($oa->fieldSet->id != "FIELD_HIDDENS") ? $oa->fieldSet->id : '';
            
            $oDocAttr->link = $oa->link;
            $oDocAttr->elink = $oa->elink;
            $oDocAttr->options = $oa->options;
            $oDocAttr->phpfile = $oa->phpfile;
            $oDocAttr->phpfunc = $oa->phpfunc;
            $oDocAttr->phpconstraint = $oa->phpconstraint;
            
            $attrs[$oa->id] = $oDocAttr->getValues();
        }
    }
    
    uasort($attrs, 'reOrderAttr');
    $action->lay->Set("docid", $docid);
    $action->lay->Set("dirid", $dirid);
    // build values type array
    $action->lay->Set("TITLE", _("new document family"));
    // when modification
    if (($classid == 0) && ($docid != 0)) $classid = $doc->fromid;
    else
    // to show inherit attributes
    if (($docid == 0) && ($classid > 0)) $doc = new_Doc($dbaccess, $classid); // the doc inherit from chosen class
    $selectclass = array();
    $tclassdoc = GetClassesDoc($dbaccess, $action->user->id, 0, "TABLE");
    foreach ($tclassdoc as $k => $cdoc) {
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
        
        $ka = 0; // index attribute
        //    ------------------------------------------
        $selectframe = array();
        $selectframe[0]["framevalue"] = "-";
        $selectframe[0]["frameid"] = "";
        $selectframe[0]["frameclass"] = "";
        $selectframe[0]["selected"] = "";
        $selecttab = $selectframe;
        /**
         * @var array $rawAttr
         */
        foreach ($attrs as $k => $rawAttr) {
            $oDocAttr->Affect($rawAttr);
            if ($oDocAttr->isStructure()) {
                
                $selectframe[$k]["framevalue"] = $oDocAttr->labeltext;
                $selectframe[$k]["frameid"] = $oDocAttr->id;
                $selectframe[$k]["frameclass"] = strtok($oDocAttr->type, '(');
                $selectframe[$k]["selected"] = "";
                if ($oDocAttr->type == "tab") $selecttab[$k] = $selectframe[$k];
            }
        }
        
        $nextOrder = 0;
        
        foreach ($attrs as $k => $rawAttr) {
            
            if ($k[0] == ':') continue;
            $oDocAttr->Affect($rawAttr);
            // if ($odocattr->isStructure()) continue;
            if ($oDocAttr->getRawType() == "array") {
                $selectframe[$k]["framevalue"] = $oDocAttr->labeltext;
                $selectframe[$k]["frameid"] = $oDocAttr->id;
                $selectframe[$k]["selected"] = "";
            }
            $newelem[$k]["attrid"] = $oDocAttr->id;
            $newelem[$k]["idtype"] = "hidden";
            if (($oDocAttr->docid == $docid) && ($oDocAttr->usefor != "A")) {
                $newelem[$k]["disabled"] = "";
            } else {
                $newelem[$k]["disabled"] = "disabled";
                /**
                 * @var NormalAttribute $oa
                 */
                $oa = $doc->getAttribute($k);
                if ($oa) {
                    // case MODATTR : view new
                    if (!$oDocAttr->isStructure()) $oDocAttr->ordered = $oa->ordered;
                    $oDocAttr->visibility = $oa->visibility;
                    $oDocAttr->labeltext = $oa->labelText;
                    $oDocAttr->frameid = (isset($oa->fieldSet) && $oa->fieldSet->id != "FIELD_HIDDENS") ? $oa->fieldSet->id : '';
                    
                    $oDocAttr->options = $oa->options;
                    if (is_a($oDocAttr, "NormalAttribute")) {
                        $oDocAttr->abstract = ($oa->isInAbstract) ? "Y" : "N";
                        $oDocAttr->title = ($oa->isInTitle) ? "Y" : "N";
                        $oDocAttr->needed = ($oa->needed) ? "Y" : "N";
                        $oDocAttr->link = $oa->link;
                        $oDocAttr->elink = $oa->elink;
                        $oDocAttr->phpfile = $oa->phpfile;
                        $oDocAttr->phpfunc = $oa->phpfunc;
                        $oDocAttr->phpconstraint = $oa->phpconstraint;
                    }
                }
            }
            
            $newelem[$k]["attrname"] = $oDocAttr->labeltext;
            $newelem[$k]["order"] = $oDocAttr->ordered;
            if ($oDocAttr->isStructure()) $newelem[$k]["displayorder"] = - 2;
            else {
                if ($oDocAttr->getRawType() == "menu" || $oDocAttr->getRawType() == "action") {
                    $newelem[$k]["displayorder"] = $nextOrder++;
                } else {
                    $newelem[$k]["displayorder"] = $oDocAttr->ordered;
                    $nextOrder = $oDocAttr->ordered + 1;
                }
            }
            $newelem[$k]["profond"] = getAttributeProfunder($k, $attrs) * 10;
            $newelem[$k]["profundator"] = getPuceAttributeProfunder($oDocAttr, $attrs);
            $newelem[$k]["visibility"] = $oDocAttr->visibility;
            $newelem[$k]["link"] = $oDocAttr->link;
            $newelem[$k]["phpfile"] = $oDocAttr->phpfile;
            $newelem[$k]["phpfunc"] = htmlspecialchars($oDocAttr->phpfunc);
            $newelem[$k]["options"] = $oDocAttr->options;
            $newelem[$k]["phpconstraint"] = $oDocAttr->phpconstraint;
            $newelem[$k]["elink"] = $oDocAttr->elink;
            $newelem[$k]["disabledid"] = "disabled";
            $newelem[$k]["neweltid"] = $k;
            if ($oDocAttr->isAbstract()) {
                $newelem[$k]["abscheck"] = "checked";
            } else {
                $newelem[$k]["abscheck"] = "";
            }
            if ($oDocAttr->isTitle()) {
                $newelem[$k]["titcheck"] = "checked";
            } else {
                $newelem[$k]["titcheck"] = "";
            }
            
            $newelem[$k]["neededcheck"] = ($oDocAttr->isNeeded()) ? "checked" : "";
            
            $newelem[$k]["typevalue"] = $oDocAttr->type;
            $newelem[$k]["classvalue"] = $oDocAttr->getRawType();
            if (isset($attrs[$oDocAttr->frameid]["type"])) $newelem[$k]["classvalue"].= ' F' . $oDocAttr->getRawType($attrs[$oDocAttr->frameid]["type"]);
            
            $selectedSet = false;
            foreach ($selectframe as $kopt => $opt) {
                if ($opt["frameid"] == $oDocAttr->frameid) {
                    $selectframe[$kopt]["selected"] = "selected";
                    $selectedSet = true;
                    
                    if (isset($newelem[$kopt]) && $newelem[$kopt]["displayorder"] < 0) {
                        $newelem[$kopt]["displayorder"] = $oDocAttr->ordered - 1;
                        if ($attrs[$oDocAttr->frameid]) {
                            if ($newelem[$attrs[$oDocAttr->frameid]["id"]]["displayorder"] < 0) {
                                $newelem[$attrs[$oDocAttr->frameid]["id"]]["displayorder"] = $newelem[$kopt]["displayorder"] - 1;
                            }
                        }
                    }
                } else {
                    $selectframe[$kopt]["selected"] = "";
                }
            }
            
            if ($oDocAttr->frameid && (!$attrs[$oDocAttr->frameid])) {
                
                $kset = $oDocAttr->frameid;
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
    foreach ($selectframe as $kopt => $opt) $selectframe[$kopt]["selected"] = "";
    
    $action->lay->SetBlockData("SELECTCLASS", $selectclass);
    
    uasort($newelem, 'sortnewelem');
    //    ------------------------------------------
    // add 3 new attributes to be defined
    $ka = count($newelem);
    for ($k = $ka; $k < 3 + $ka; $k++) {
        $newelem[$k]["idtype"] = "text";
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
    
    foreach ($newelem as $ia => $va) {
        foreach ($va as $ip => $vp) {
            $newelem[$ia][$ip] = str_replace('"', '&quot;', $vp);
        }
    }
    
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
/**
 * use to usort attributes
 * @param BasicAttribute $a
 * @param BasicAttribute $b
 */
function reOrderAttr($a, $b)
{
    
    if ($a["type"] == "tab") return -1;
    if ($b["type"] == "tab") return 1;
    if ($a["type"] == "frame") return -1;
    if ($b["type"] == "frame") return 1;
    if ($a["type"] == "menu" && $b["type"] != "menu") return 1;
    if ($b["type"] == "menu" && $a["type"] != "menu") return -1;
    
    if ($a["ordered"] == $b["ordered"]) return 0;
    if ($a["ordered"] > $b["ordered"]) return 1;
    return -1;
}

function getAttributeProfunder($aid, array $attrs)
{
    if (!$aid) return 0;
    $oa = $attrs[$aid];
    if (!$oa["frameid"]) return 0;
    
    return 1 + getAttributeProfunder($oa["frameid"], $attrs);
}
function getPuceAttributeProfunder(DocAttr & $oa, array $attrs)
{
    $p = getAttributeProfunder($oa->id, $attrs);
    if (empty($attrs["frameid"])) $fromType = '';
    else $fromType = $oa->getRawType($attrs["frameid"]);
    switch ($p) {
        case 0:
            return '';
        case 1:
            if ($oa->getRawType() == 'frame') {
                return "<span class='frame'>---</span>";
            } elseif ($fromType == 'tab') {
                return "<span class='tab'>---</span>";
            } else {
                return "<span class='unknow'>---</span>";
            }
        case 2:
            if ($fromType == 'array') {
                return "<span class='frame'>---</span><span class='array'>---</span>";
            } elseif ($fromType == 'frame') {
                return "<span class='tab'>---</span><span class='frame'>---</span>";
            } else {
                return "<span class='unknow'>---</span>";
            }
        case 3:
            return "<span class='tab'>---</span><span class='frame'>---</span><span class='array'>---</span>";
    }
    return '';
}

