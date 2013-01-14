<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
class _SENTMESSAGE extends Doc
{
    /*
     * @end-method-ignore
    */
    var $defaultview = "FDL:VIEWEMESSAGE";
    /**
     * @templateController
     */
    function viewemessage($target = "_self", $ulink = true, $abstract = false)
    {
        include_once ("FDL/Lib.Dir.php");
        $this->viewdefaultcard($target, $ulink, $abstract);
        
        $from = $this->getRawValue("emsg_from");
        if (preg_match("/<([^>]*)>/", $from, $erg)) {
            $from = $erg[1];
        }
        $this->lay->set("hasphoto", false);
        $filter[] = "us_mail='" . pg_escape_string($from) . "'";
        $tdir = internalGetDocCollection($this->dbaccess, 0, "0", 1, $filter, 1, "LIST", "IUSER");
        if (count($tdir) == 1) {
            $vphoto = $tdir[0]->getValue("us_photo");
            if ($vphoto) {
                $photo = $tdir[0]->GetHtmlAttrValue("us_photo");
                $this->lay->set("photo", $photo);
                $this->lay->set("hasphoto", ($photo != ""));
            }
        }
        $hashtml = ($this->getRawValue("emsg_htmlbody") != "");
        
        $this->lay->set("hashtml", $hashtml);
        
        $this->lay->set("TO", false);
        $this->lay->set("CC", false);
        
        $recips = $this->getMultipleRawValues("emsg_recipient");
        $reciptype = $this->getMultipleRawValues("emsg_sendtype");
        $tto = array();
        $tcc = array();
        $tbcc = array();
        foreach ($recips as $k => $addr) {
            $addr = str_replace(array(
                "<",
                ">"
            ) , array(
                "&lt;",
                "&gt;"
            ) , $addr);
            if ($reciptype[$k] == "cc") $tcc[] = $addr;
            elseif ($reciptype[$k] == "bcc") $tbcc[] = $addr;
            else $tto[] = $addr;
        }
        
        if (count($tto) > 0) {
            $this->lay->set("TO", implode("; ", $tto));
        }
        if (count($tcc) > 0) {
            $this->lay->set("CC", implode("; ", $tcc));
        }
    }
    /**
     * force no edition
     */
    function control($aclname, $strict = false)
    {
        if (($this->id > 0) && ($this->doctype != 'C') && ($aclname == "edit") && ($this->getFamilyParameterValue("emsg_editcontrol") != "freeedit")) return _("electronic messages cannot be modified");
        else return parent::control($aclname, $strict);
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