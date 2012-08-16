<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Upload image from CKeditor
 *
 * @author Anakeen
 * @version $Id: fckupload.php,v 1.3 2008/03/10 10:45:52 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/modcard.php");
/**
 * Upload image from CKeditor
 * @param Action &$action current action
 * @global $_FILES['upload'] Http var : file to store
 */
function ckupload(Action & $action)
{
    $err = "";
    
    $usage = new ActionUsage($action);
    /* Internal numFunc */
    $funcNum = $usage->addNeeded("CKEditorFuncNum", "CKEditorFuncNum");
    
    $usage->strict(false);
    
    $usage->verify();
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    global $_FILES;
    
    $doc = createDoc($dbaccess, "IMAGE");
    $filename = insert_file($doc, "upload", true);
    
    if ($filename != "") {
        $doc->setValue("img_file", $filename);
        $err.= $doc->store();
        if ($err == "") {
            $action->lay->set("docid", $doc->getProperty("id"));
            $action->lay->set("title", $doc->getTitle());
        }
    } else {
        $err.= _("Unable to store the file");
    }
    
    $action->lay->set("FUNCNUM", $funcNum);
    $action->lay->set("err", $err);
}
?>