<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 *  Address book methods for societies
 *
 * @author Anakeen 2005
 * @version $Id: Method.FAddBookSociety.php,v 1.6 2005/11/24 13:48:17 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage USERCARD
 */
/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
class _SOCIETYBOOK extends Doc
{
    /*
     * @end-method-ignore
    */
    var $faddbook_card = "USERCARD:FADDBOOKSOC_CARD:U";
    var $faddbook_resume = "USERCARD:FADDBOOKSOC_RESUME:T";
    /**
     * @templateController
     * @param string $target
     * @param bool $ulink
     * @param string $abstract
     */
    function faddbooksoc_card($target = "finfo", $ulink = true, $abstract = "Y")
    {
        global $action;
        $action->parent->AddCssRef("USERCARD:faddbook.css", true);
        $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/USERCARD/Layout/faddbook.js");
        // list of attributes displayed directly in layout
        $ta = array(
            "si_logo",
            "si_society",
            "si_town",
            "si_mail",
            "si_phone",
            "si_mobile",
            "si_fax",
            "si_web",
            "si_addr",
            "si_cedex",
            "si_country",
            "si_postcode",
            "si_t_sites"
        );
        
        $this->viewdefaultcard($target, $ulink, $abstract);
        $la = $this->getAttributes();
        
        $to = array();
        $tabs = array();
        foreach ($la as $k => $v) {
            $va = $this->getValue($v->id);
            if (($va || ($v->type == "array")) && (!in_array($v->id, $ta)) && (!$v->inArray())) {
                
                if ((($v->mvisibility == "R") || ($v->mvisibility == "W"))) {
                    if ($v->type == "array") {
                        $hv = $this->getHtmlValue($v, $va, $target, $ulink);
                        if ($hv) {
                            $to[] = array(
                                "lothers" => $v->labelText,
                                "aid" => $v->id,
                                "vothers" => $hv,
                                "isarray" => true
                            );
                            $tabs[$v->fieldSet->labelText][] = $v->id;
                        }
                    } else {
                        $to[] = array(
                            "lothers" => $v->labelText,
                            "aid" => $v->id,
                            "vothers" => $this->getHtmlValue($v, $va, $target, $ulink) ,
                            "isarray" => false
                        );
                        $tabs[$v->fieldSet->labelText][] = $v->id;
                    }
                }
            }
        }
        
        $logo = $this->getValue("si_logo");
        if ($logo) {
            $this->lay->set("logo", $this->getHtmlAttrValue("si_logo"));
            $this->lay->set("wlogo", "70");
        } else {
            $this->lay->set("logo", $this->getIcon());
            $this->lay->set("wlogo", "48");
        }
        $this->lay->setBlockData("OTHERS", $to);
        $ltabs = array();
        foreach ($tabs as $k => $v) {
            $ltabs[$k] = array(
                "tabtitle" => $k,
                "aids" => "['" . implode("','", $v) . "']"
            );
        }
        $this->lay->setBlockData("TABS", $ltabs);
        $this->lay->set("HasOthers", (count($to) > 0));
        $this->lay->set("HasLogo", ($logo != ""));
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