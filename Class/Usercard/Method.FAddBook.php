<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Adsress book methods for persons
 *
 * @author Anakeen 2005
 * @version $Id: Method.FAddBook.php,v 1.15 2008/05/13 10:21:01 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage USERCARD
 */
/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
class _USERBOOK extends Doc
{
    /*
     * @end-method-ignore
    */
    public $faddbook_card = "USERCARD:VIEWPERSON:T";
    public $faddbook_resume = "USERCARD:FADDBOOK_RESUME:U";
    /**
     * @templateController
     * @param string $target
     * @param bool $ulink
     * @param bool $abstract
     * @return mixed
     */
    function faddbook_resume($target = "finfo", $ulink = true, $abstract = false)
    {
        
        global $action;
        $action->parent->AddCssRef("USERCARD:faddbook.css", true);
        
        $imgu = "";
        $img = $this->getValue("us_photo");
        if ($img == "") {
            $this->lay->set("hasPhoto", false);
        } else {
            $this->lay->set("hasPhoto", true);
            $imgu = $this->GetHtmlValue($this->getAttribute("us_photo") , $img);
            $this->lay->set("photo", $imgu);
        }
        
        $this->lay->set("nom", $this->getValue("us_lname"));
        $this->lay->set("prenom", $this->getValue("us_fname"));
        
        $soc = $this->getValue("us_society");
        $this->lay->set("hasSoc", ($soc != "" ? true : false));
        $this->lay->set("societe", $soc);
        
        $mail = $this->getValue("us_mail");
        $this->lay->set("hasMail", ($mail != "" ? true : false));
        $this->lay->set("addmail", $mail);
        
        $mob = $this->getValue("us_mobile");
        $this->lay->set("nomob", $mob);
        $this->lay->set("hasMob", ($mob != "" ? true : false));
        
        $tel = $this->getValue("us_phone");
        $this->lay->set("notel", $tel);
        $this->lay->set("hasTel", ($tel != "" ? true : false));
        
        $sky = $this->getValue("us_skypeid");
        $this->lay->set("skypeid", $sky);
        $this->lay->set("hasSky", ($sky != "" ? true : false));
        
        $msn = $this->getValue("us_msnid");
        $this->lay->set("msnid", $msn);
        $this->lay->set("hasMsn", ($msn != "" ? true : false));
        
        return;
    }
    /**
     * @templateController
     * @param string $target
     * @param bool $ulink
     * @param bool $abstract
     */
    function faddbook_card($target = "finfo", $ulink = true, $abstract = false)
    {
        // list of attributes displayed directly in layout
        global $action;
        $action->parent->AddCssRef("USERCARD:faddbook.css", true);
        $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/USERCARD/Layout/faddbook.js");
        
        setHttpVar("specialmenu", "menuab");
        
        $ta = array(
            "us_workweb",
            "us_photo",
            "us_lname",
            "us_fname",
            "us_society",
            "us_civility",
            "us_mail",
            "us_phone",
            "us_mobile",
            "us_fax",
            "us_intphone",
            "us_workaddr",
            "us_workcedex",
            "us_country",
            "us_workpostalcode",
            "us_worktown"
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
        $this->lay->setBlockData("OTHERS", $to);
        $ltabs = array();
        foreach ($tabs as $k => $v) {
            $ltabs[$k] = array(
                "tabtitle" => $k,
                "aids" => "['" . implode("','", $v) . "']"
            );
        }
        $this->lay->setBlockData("TABS", $ltabs);
    }
    /**
     * @templateController
     * @param string $target
     * @param bool $ulink
     * @param bool $abstract
     */
    function viewperson($target = "finfo", $ulink = true, $abstract = false)
    {
        $this->viewdefaultcard($target, $ulink, $abstract);
        $socid = $this->getValue("us_idsociety");
        if ($socid) $soc = new_doc($this->dbaccess, $socid);
        if ($socid && $soc->isAlive()) {
            $this->lay->set("socphone", $soc->getValue("si_phone"));
            $this->lay->set("socfax", $soc->getValue("si_fax"));
        } else {
            $this->lay->set("socphone", "");
            $this->lay->set("socfax", "");
        }
        $secid = $this->getValue("us_idsecr");
        if ($secid) $sec = new_doc($this->dbaccess, $secid);
        if ($secid && $sec->isAlive()) {
            $this->lay->set("secrphone", $sec->getValue("us_pphone"));
        } else {
            $this->lay->set("secrphone", "");
        }
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
