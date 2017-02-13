<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: folders.php,v 1.18 2005/06/28 08:37:46 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: folders.php,v 1.18 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/folders.php,v $
// ---------------------------------------------------------------
include_once ("FDL/Lib.Dir.php");
include_once ("FDL/Class.QueryDir.php");
include_once ("FDL/freedom_util.php");
// -----------------------------------
function folders(Action & $action)
{
    // -----------------------------------
    global $nbfolders, $dbaccess, $pexport;
    $nbfolders = 0;
    // Get all the params
    $dirid = GetHttpVars("dirid", 0); // root directory
    $dbaccess = $action->dbaccess;
    
    include_once ("FDL/popup_util.php");
    //barmenu($action); // describe bar menu
    $homefld = new Dir($dbaccess);
    $homefld = $homefld->GetHome();
    
    if ($dirid == 0) $dirid = $action->getParam("ROOTFLD", getFirstDir($dbaccess));
    
    $doc = new_Doc($dbaccess, $dirid);
    $action->lay->rSet("dirid", urlencode($doc->id));
    $action->lay->rSet("reptitle", json_encode(sprintf("<i>%s</i>", $doc->getHTMLTitle())));
    $action->lay->rSet("icon", json_encode($doc->getIcon()));
    // ------------------------------------------------------
    // definition of popup menu
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
    // for the first (top) folder
    popupActive("popfld", $nbfolders, 'cancel');
    popupActive("popfld", $nbfolders, 'vprop');
    // get export permission
    $appfld = new Application();
    $appfld->Set("FDL", $action->parent->parent);
    $pexport = $appfld->HasPermission("EXPORT");
    
    if ($pexport) popupActive("popfld", $nbfolders, 'export');
    else popupInvisible("popfld", $nbfolders, 'export');
    
    popupActive("popfld", $nbfolders, 'mkdir');
    popupInvisible("popfld", $nbfolders, 'refresh');
    popupActive("poppaste", $nbfolders, 'staticpaste');
    popupActive("poppaste", $nbfolders, 'pastelatest');
    popupActive("poppaste", $nbfolders, 'cancel2');
    
    $nbfolders++; // one for the top
    // define icon from style
    $iconfolder = $action->parent->getImageLink("ftv2folderopen1.gif");
    $pathicon = explode("/", $iconfolder);
    if (count($pathicon) == 4) $action->lay->rSet("iconFolderPath", json_encode($pathicon[0] . "/" . $pathicon[1]));
    else $action->lay->rSet("iconFolderPath", json_encode("FREEDOM"));
    // define sub trees
    $stree = addfolder($doc, -1, "fldtop", false);
    $action->lay->rSet("subtree", $stree);
    
    $action->lay->rSet("idHomeFolder", (int)$nbfolders);
    
    $htree = addfolder($homefld, 0, "fldtop");
    $action->lay->rSet("hometree", $htree);
    //-------------- pop-up menu ----------------
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    // display popup js
    // display popup js
    popupGen($nbfolders);
}
// -----------------------------------
function addfolder(Doc $doc, $level, $treename, $thisfld = true)
{
    // -----------------------------------
    global $dbaccess;
    global $tmenuaccess;
    global $nbfolders;
    global $action;
    global $pexport;
    
    if ($thisfld) {
        if ($level == 0) $levelp = "";
        else $levelp = $level - 1;
        if ($doc->owner < 0) $ftype = 3;
        else if ($doc->id == 14) $ftype = 5;
        else if ($doc->doctype == 'D') $ftype = 1;
        else if ($doc->doctype == 'S') $ftype = 2;
        
        $hasChild = false;
        // if ($doc->doctype != 'S') {
        // no child for a search
        if (hasChildFld($dbaccess, $doc->initid, ($doc->doctype == 'S'))) $hasChild = true;
        //}
        $ftype = $doc->getIcon();
        $ltree = sprintf("%s%s = insFld(%s, gFld(%s, \"#\", %d, %s, %s))\n", $treename, $level, $treename . $levelp, json_encode($doc->getHTMLTitle()) , $doc->initid, json_encode($ftype) , ($hasChild ? 'true' : 'false'));
        
        popupActive("popfld", $nbfolders, 'cancel');
        popupActive("popfld", $nbfolders, 'vprop');
        if ($pexport) popupActive("popfld", $nbfolders, 'export');
        else popupInvisible("popfld", $nbfolders, 'export');
        if ($doc->doctype == 'D') {
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
    } else $ltree = "";
    if ($doc->doctype == 'D') {
        
        if ($level < 0) {
            $ldir = getChildDir($dbaccess, $action->user->id, $doc->id);
            
            if (count($ldir) > 0) {
                
                foreach ($ldir as $k => $v) {
                    $ltree.= addfolder($v, $level + 1, $treename);
                }
            }
        }
    }
    return $ltree;
}
