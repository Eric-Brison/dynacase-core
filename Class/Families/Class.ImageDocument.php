<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Image document
 *
 */
namespace Dcp\Core;
class Image extends \Dcp\Family\Document
{
    
    var $defaultview = "FDL:VIEWIMGCARD";
    
    var $cviews = array(
        "FDL:VIEWIMGCARD:T"
    );
    /**
     * @templateController default view image fit to window
     * @param string $target
     * @param bool $ulink
     * @param bool $abstract
     */
    function viewimgcard($target = "_self", $ulink = true, $abstract = false)
    {
        // -----------------------------------
        $nbimg = 0; // number of image
        $this->viewattr($target, $ulink, $abstract);
        $this->viewprop($target, $ulink, $abstract);
        
        $listattr = $this->GetNormalAttributes();
        
        $tableimage = array();
        $vf = newFreeVaultFile($this->dbaccess);
        // view all (and only) images
        foreach ($listattr as $i => $attr) {
            
            $value = chop($this->getRawValue($i));
            //------------------------------
            // Set the table value elements
            if (($value != "") && ($attr->visibility != "H")) {
                // print values
                switch ($attr->type) {
                    case "file":
                    case "image":
                        
                        $tableimage[$nbimg]["imgsrc"] = $this->GetHtmlValue($attr, $value, $target, $ulink);
                        if (preg_match(PREGEXPFILE, $value, $reg)) {
                            // reg[1] is mime type
                            $tableimage[$nbimg]["type"] = $reg[1];
                            if ($vf->Show($reg[2], $info) == "") $fname = $info->name;
                            else $fname = _("no filename");
                            $tableimage[$nbimg]["name"] = $fname;
                        }
                        break;
                    }
                }
        }
        // Out
        $this->lay->SetBlockData("TABLEIMG", $tableimage);
    }
    
    function postStore()
    {
        return $this->SetValue("IMG_TITLE", $this->vault_filename("IMG_FILE"));
    }
}
