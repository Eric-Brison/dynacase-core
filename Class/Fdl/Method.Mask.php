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
 * @version $Id: Method.Mask.php,v 1.23 2008/09/12 10:14:48 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: Method.Mask.php,v 1.23 2008/09/12 10:14:48 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Class/Freedom/Method.Mask.php,v $
// ---------------------------------------------------------------

/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
class _MASK extends Doc
{
    /*
     * @end-method-ignore
    */
    
    var $defaultedit = "FREEDOM:EDITMASK";
    var $defaultview = "FREEDOM:VIEWMASK";
    
    function SpecRefresh()
    {
        $err = '';
        //  gettitle(D,AR_IDCONST):AR_CONST,AR_IDCONST
        $this->refreshDocTitle("MSK_FAMID", "MSK_FAM");
        
        return $err;
    }
    
    function getLabelVis()
    {
        return array(
            "-" => " ",
            "R" => _("read only") ,
            "W" => _("read write") ,
            "O" => _("write only") ,
            "H" => _("hidden") ,
            "S" => _("read disabled") ,
            "U" => _("static array") ,
            "I" => _("invisible")
        );
    }
    function getLabelNeed()
    {
        return array(
            "-" => " ",
            "Y" => _("Y") ,
            "N" => _("N")
        );
    }
    /**
     * suppress unmodified attributes visibilities
     * to simplify the mask structure
     */
    function postModify()
    {
        $tneed = $this->getTValue("MSK_NEEDEEDS");
        $tattrid = $this->getTValue("MSK_ATTRIDS");
        $tvis = $this->getTValue("MSK_VISIBILITIES");
        
        $tvisibilities = array();
        foreach ($tattrid as $k => $v) {
            if (($tneed[$k] == '-') && ($tvis[$k] == '-')) {
                unset($tneed[$k]);
                unset($tvis[$k]);
                unset($tattrid[$k]);
            }
        }
        $this->setValue("MSK_NEEDEEDS", $tneed);
        $this->setValue("MSK_ATTRIDS", $tattrid);
        $this->setValue("MSK_VISIBILITIES", $tvis);
        
        return '';
    }
    
    function getVisibilities()
    {
        $tvisid = $this->getTValue("MSK_VISIBILITIES");
        $tattrid = $this->getTValue("MSK_ATTRIDS");
        
        $tvisibilities = array();
        while (list($k, $v) = each($tattrid)) {
            $tvisibilities[$v] = $tvisid[$k];
        }
        return $tvisibilities;
    }
    
    function getCVisibilities()
    {
        $tvisid = $this->getTValue("MSK_VISIBILITIES");
        $tattrid = $this->getTValue("MSK_ATTRIDS");
        $docid = $this->getValue("MSK_FAMID", 1);
        $doc = new_Doc($this->dbaccess, $docid);
        
        $tsvis = $this->getVisibilities();
        $tvisibilities = array();
        
        foreach ($tattrid as $k => $v) {
            $attr = $doc->getAttribute($v);
            $fvisid = $attr->fieldSet->id;
            if ($tvisid[$k] == "-") $vis = $attr->visibility;
            else $vis = $tvisid[$k];
            
            $tvisibilities[$v] = ComputeVisibility($vis, isset($tvisibilities[$fvisid]) ? $tvisibilities[$fvisid] : '', isset($attr->fieldSet->fieldSet) ? $attr->fieldSet->fieldSet->mvisibility : '');
        }
        return $tvisibilities;
    }
    function getNeedeeds()
    {
        $tvisid = $this->getTValue("MSK_NEEDEEDS");
        $tattrid = $this->getTValue("MSK_ATTRIDS");
        
        $tvisibilities = array();
        while (list($k, $v) = each($tattrid)) {
            $tvisibilities[$v] = $tvisid[$k];
        }
        return $tvisibilities;
    }
    /**
     * @templateController view attributes differences
     * @param string $target
     * @param bool $ulink
     * @param bool $abstract
     */
    function viewmask($target = "_self", $ulink = true, $abstract = false)
    {
        
        $docid = $this->getValue("MSK_FAMID", 1);
        
        $tvisibilities = $this->getCVisibilities();
        $tkey_visibilities = array_keys($tvisibilities);
        $tinitvisibilities = $tvisibilities;
        
        $tneedeeds = $this->getNeedeeds();
        
        $this->lay->Set("docid", $docid);
        
        $doc = new_Doc($this->dbaccess, $docid);
        $doc->applyMask();
        $origattr = $doc->attributes->attr;
        
        $tmpdoc = createTmpDoc($this->dbaccess, $docid);
        $tmpdoc->applyMask($this->id);
        // display current values
        $tmask = array();
        
        $labelvis = $this->getLabelVis();
        
        uasort($tmpdoc->attributes->attr, "tordered");
        
        foreach ($tmpdoc->attributes->attr as $k => $attr) {
            /**
             * @var $attr NormalAttribute|ActionAttribute
             */
            if (!$attr->visibility) continue;
            if ($attr->usefor == 'Q') continue;
            $tmask[$k]["attrname"] = $attr->getLabel();
            $tmask[$k]["type"] = $attr->type;
            $tmask[$k]["visibility"] = $labelvis[$attr->visibility];
            $tmask[$k]["wneed"] = (!empty($origattr[$k]->needed)) ? "bold" : "normal";
            $tmask[$k]["bgcolor"] = "inherits";
            $tmask[$k]["mvisibility"] = $labelvis[$attr->mvisibility];
            $tmask[$k]["classtype"] = strtok($attr->type, '(');
            
            if (in_array($k, $tkey_visibilities)) {
                $tmask[$k]["classtype"].= " directmodified";
            } elseif ($tmask[$k]["visibility"] != $tmask[$k]["mvisibility"]) {
                if ($tmask[$k]["mvisibility"] != $labelvis[$origattr[$k]->mvisibility]) $tmask[$k]["classtype"].= " inheritmodified";
            }
            
            if (isset($tneedeeds[$attr->id])) {
                if (($tneedeeds[$attr->id] == "Y") || (($tneedeeds[$attr->id] == "-") && ($attr->needed))) $tmask[$k]["waneed"] = "bold";
                else $tmask[$k]["waneed"] = "normal";
                if ($tneedeeds[$attr->id] != "-") $tmask[$k]["bgcolor"] = getParam("CORE_BGCOLORALTERN");
            } else $tmask[$k]["waneed"] = $tmask[$k]["wneed"];
            
            if ($tmask[$k]["wneed"] != $tmask[$k]["waneed"]) {
                $tmask[$k]["bgcolor"] = getParam("COLOR_B5");
            }
            
            if ($attr->fieldSet && $attr->fieldSet->id && $attr->fieldSet->id != "FIELD_HIDDENS") $tmask[$k]["framelabel"] = $attr->fieldSet->getLabel();
            else $tmask[$k]["framelabel"] = "";
            if (!empty($attr->waction)) $tmask[$k]["framelabel"] = _("Action");
            
            $tmask[$k]["displayorder"] = ($attr->ordered) ? $attr->ordered : -2;
            if ($attr->type == "menu" || $attr->type == "action") $tmask[$k]["displayorder"]+= 1000000; // at then end
            if (($attr->ordered > 0) && $attr->fieldSet->id && $attr->fieldSet->ordered < - 1) {
                $attr->fieldSet->ordered = $attr->ordered - 1;
                $tmask[$attr->fieldSet->id]["displayorder"] = $attr->ordered - 1;
                if ($attr->fieldSet->fieldSet && $attr->fieldSet->fieldSet->id) {
                    if ($attr->fieldSet->fieldSet->ordered < - 1) {
                        $attr->fieldSet->fieldSet->ordered = $attr->fieldSet->ordered - 1;
                        $tmask[$attr->fieldSet->fieldSet->id]["displayorder"] = $attr->fieldSet->ordered - 1;
                    }
                }
            }
        }
        unset($tmask["FIELD_HIDDENS"]);
        uasort($tmask, array(
            get_class($this) ,
            'sortnewelem'
        ));
        $this->lay->SetBlockData("MASK", $tmask);
    }
    /**
     * @templateController special edition for mask
     */
    function editmask()
    {
        global $action;
        
        $docid = $this->getValue("MSK_FAMID");
        
        $this->lay->Set("docid", $docid);
        
        $doc = new_Doc($this->dbaccess, $docid);
        
        $tvisibilities = $this->getVisibilities();
        $tneedeeds = $this->getNeedeeds();
        
        if ($docid == 0) {
            // only choose family in creation
            $selectclass = array();
            $tclassdoc = GetClassesDoc($this->dbaccess, $this->userid, 0, "TABLE");
            foreach ($tclassdoc as $k => $cdoc) {
                $selectclass[$k]["idcdoc"] = $cdoc["id"];
                $selectclass[$k]["classname"] = $cdoc["title"];
                $selectclass[$k]["selected"] = "";
            }
            
            $this->lay->SetBlockData("SELECTCLASS", $selectclass);
        }
        // display current values
        $newelem = array();
        $this->lay->set("creation", ($docid == 0));
        $this->lay->set("family", $doc->getTitle());
        
        if ($docid > 0) {
            $ka = 0; // index attribute
            $labelvis = $this->getLabelVis();
            while (list($k, $v) = each($labelvis)) {
                $selectvis[] = array(
                    "visid" => $k,
                    "vislabel" => $v
                );
            }
            $labelneed = $this->getLabelNeed();
            while (list($k, $v) = each($labelneed)) {
                $selectneed[] = array(
                    "needid" => $k,
                    "needlabel" => $v
                );
            }
            //    ------------------------------------------
            //  -------------------- NORMAL ----------------------
            $tattr = $doc->getAttributes();
            uasort($tattr, "tordered");
            foreach ($tattr as $k => $attr) {
                /**
                 * @var $attr NormalAttribute|FieldSetAttribute|ActionAttribute
                 */
                if ($attr->usefor == "Q") continue; // not parameters
                if ($attr->docid == 0) continue; // not parameters
                $newelem[$k]["attrid"] = $attr->id;
                $newelem[$k]["attrname"] = $attr->getLabel();
                $newelem[$k]["type"] = strtok($attr->type, '(');
                $newelem[$k]["visibility"] = $labelvis[$attr->visibility];
                
                $newelem[$k]["wneed"] = (!empty($attr->needed)) ? "bold" : "normal";
                $newelem[$k]["neweltid"] = $k;
                $newelem[$k]["attrinfo"] = $attr->id;
                if ($attr->fieldSet->id && $attr->fieldSet->id != 'FIELD_HIDDENS') {
                    $newelem[$k]["attrinfo"].= '/' . $attr->fieldSet->id;
                    if ($attr->fieldSet->fieldSet->id && $attr->fieldSet->fieldSet->id != 'FIELD_HIDDENS') $newelem[$k]["attrinfo"].= '/' . $attr->fieldSet->fieldSet->id;
                }
                
                if (($attr->type == "array") || (strtolower(get_class($attr)) == "fieldsetattribute")) $newelem[$k]["fieldweight"] = "bold";
                else $newelem[$k]["fieldweight"] = "";
                
                if ($attr->docid == $docid) {
                    $newelem[$k]["disabled"] = "";
                } else {
                    $newelem[$k]["disabled"] = "disabled";
                }
                
                if ($attr->fieldSet->docid > 0) $newelem[$k]["framelabel"] = $attr->fieldSet->getLabel();
                else $newelem[$k]["framelabel"] = "";
                if (!empty($attr->waction)) $newelem[$k]["framelabel"] = _("Action");
                
                reset($selectvis);
                while (list($kopt, $opt) = each($selectvis)) {
                    if (isset($tvisibilities[$attr->id]) && $opt["visid"] == $tvisibilities[$attr->id]) {
                        $selectvis[$kopt]["selected"] = "selected";
                    } else {
                        $selectvis[$kopt]["selected"] = "";
                    }
                }
                // idem for needed
                reset($selectneed);
                while (list($kopt, $opt) = each($selectneed)) {
                    if (isset($tneedeeds[$attr->id]) && $opt["needid"] == $tneedeeds[$attr->id]) {
                        $selectneed[$kopt]["selectedneed"] = "selected";
                    } else {
                        $selectneed[$kopt]["selectedneed"] = "";
                    }
                }
                $newelem[$k]["displayorder"] = ($attr->ordered) ? $attr->ordered : -2;
                if ($attr->type == "menu" || $attr->type == "action") $newelem[$k]["displayorder"]+= 1000000; // at then end
                if (($attr->ordered > 0) && $attr->fieldSet->id && $attr->fieldSet->ordered < - 1) {
                    $attr->fieldSet->ordered = $attr->ordered - 1;
                    $newelem[$attr->fieldSet->id]["displayorder"] = $attr->ordered - 1;
                    if ($attr->fieldSet->fieldSet && $attr->fieldSet->fieldSet->id) {
                        if ($attr->fieldSet->fieldSet->ordered < - 1) {
                            $attr->fieldSet->fieldSet->ordered = $attr->fieldSet->ordered - 1;
                            $newelem[$attr->fieldSet->fieldSet->id]["displayorder"] = $attr->fieldSet->ordered - 1;
                        }
                    }
                }
                $newelem[$k]["SELECTVIS"] = "SELECTVIS_$k";
                $this->lay->SetBlockData($newelem[$k]["SELECTVIS"], $selectvis);
                $newelem[$k]["SELECTNEED"] = "SELECTNEED_$k";
                $this->lay->SetBlockData($newelem[$k]["SELECTNEED"], $selectneed);
                
                $ka++;
            }
            unset($newelem["FIELD_HIDDENS"]);
            uasort($newelem, array(
                get_class($this) ,
                'sortnewelem'
            ));
            $this->lay->SetBlockData("NEWELEM", $newelem);
        }
        $this->editattr();
    }
    /** 
     * use to usort attributes
     * @param BasicAttribute $a
     * @param BasicAttribute $b
     */
    static function sortnewelem($a, $b)
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
     * @begin-method-ignore
     * this part will be deleted when construct document class until end-method-ignore
     */
}
/*
 * @end-method-ignore
*/
?>