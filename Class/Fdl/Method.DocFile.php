<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: Method.DocFile.php,v 1.10 2008/03/10 10:45:52 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */
/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
class _FILE extends Doc
{
    /*
     * @end-method-ignore
    */
    
    function PostModify()
    {
        $filename = $this->vault_filename("FI_FILE");
        /* to not view extension file
        $pos = strrpos($filename , ".");
        if ($pos !== false) {
        $filename=substr($filename,0,$pos);
        }
        */
        if ($this->getValue("FI_TITLEW") == "") $this->SetValue("FI_TITLE", $filename);
        else $this->SetValue("FI_TITLE", $this->getValue("FI_TITLEW"));
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
