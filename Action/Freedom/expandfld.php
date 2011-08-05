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
 * @version $Id: expandfld.php,v 1.18 2005/09/27 16:16:50 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: expandfld.php,v 1.18 2005/09/27 16:16:50 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/expandfld.php,v $
// ---------------------------------------------------------------

include_once ("FREEDOM/folders.php");
// -----------------------------------
function expandfld(&$action)
{
    // -----------------------------------
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $dirid = GetHttpVars("dirid", 9); // root directory
    $inavmode = GetHttpVars("inavmode"); // root directory
    $dir = new_Doc($dbaccess, $dirid);
    $navigate = $action->getParam('FREEDOM_VIEWFRAME'); // standard navigation
    $action->lay->Set("dirid", $dirid);
    $action->lay->Set("reptitle", $dir->title);
    
    $action->lay->Set("navmode", $navigate);
    if ($inavmode == 'inverse') {
        if ($navigate == 'navigator') $action->lay->Set("navmode", "folder");
        else if ($navigate == 'folder') $action->lay->Set("navmode", "navigator");
    }
    
    $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FREEDOM/Layout/expandfld.js");
    // get export permission
    $appfld = new Application();
    $appfld->Set("FDL", $action->parent->parent);
    $pexport = $appfld->HasPermission("EXPORT");
    // ------------------------------------------------------
    // definition of popup menu
    include_once ("FDL/popup_util.php");
    popupInit("popfld", array(
        'vprop',
        'mkdir',
        'export',
        'refresh',
        'cancel'
    ));
    popupInit("poppaste", array(
        'staticpaste',
        'pastelatest',
        'cancel2'
    ));
    $ldir = getChildDir($dbaccess, $action->user->id, $dir->id, false, "TABLE");
    $stree = "";
    if (count($ldir) > 0) {
        $nbfolders = 1;
        while (list($k, $doc) = each($ldir)) {
            
            popupActive("popfld", $nbfolders, 'cancel');
            popupActive("popfld", $nbfolders, 'vprop');
            
            if ($pexport) popupActive("popfld", $nbfolders, 'export');
            else popupInvisible("popfld", $nbfolders, 'export');
            
            if ($doc["doctype"] == 'D') {
                popupActive("popfld", $nbfolders, 'mkdir');
                popupActive("popfld", $nbfolders, 'refresh');
            } else {
                popupInvisible("popfld", $nbfolders, 'mkdir');
                popupInvisible("popfld", $nbfolders, 'refresh');
            }
            popupActive("poppaste", $nbfolders, 'staticpaste');
            popupActive("poppaste", $nbfolders, 'pastelatest');
            popupActive("poppaste", $nbfolders, 'cancel2');
            $nbfolders++;
            
            if ($doc["owner"] < 0) $ftype = 3;
            else if ($doc["doctype"] == 'D') $ftype = 1;
            else if ($doc["doctype"] == 'S') $ftype = 2;
            else continue; // it 'is not a folder
            $hasChild = 'false';
            // no child for a search
            if (hasChildFld($dbaccess, $doc["initid"], ($doc["doctype"] == 'S'))) $hasChild = 'true';
            
            $ftype = $dir->getIcon($doc["icon"]);
            $stree.= "ffolder.insFld(fldtop, ffolder.gFld(\"" . str_replace('"', '\"', $doc["title"]) . "\", \"#\"," . $doc["initid"] . ",\"$ftype\",$hasChild))\n";
        }
    }
    // define icon from style
    $iconfolder = $action->GetImageUrl("ftv2folderopen1.gif");
    $pathicon = explode("/", $iconfolder);
    if (count($pathicon) == 4) $action->lay->set("iconFolderPath", $pathicon[0] . "/" . $pathicon[1]);
    else $action->lay->set("iconFolderPath", "FREEDOM");
    
    $action->lay->Set("subtree", $stree);
    // display popup js
    popupGen($nbfolders);
}
?>