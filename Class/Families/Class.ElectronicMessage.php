<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Sent email document
 */
namespace Dcp\Core;
class SentEmail extends \Dcp\Family\Document
{
    
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
            /**
             * @var \Dcp\Family\Iuser $first
             */
            $first = $tdir[0];
            $vphoto = $first->getRawValue("us_photo");
            if ($vphoto) {
                $photo = $first->GetHtmlAttrValue("us_photo");
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
}
