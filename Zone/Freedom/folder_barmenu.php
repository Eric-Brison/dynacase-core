<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Folder barmenu
 *
 * @author Anakeen
 * @version $Id: folder_barmenu.php,v 1.16 2007/10/15 13:01:06 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Class.Dir.php");
include_once ("FDL/Class.QueryDir.php");
include_once ("FDL/freedom_util.php");

include_once ("FDL/popup_util.php");
// -----------------------------------
function folder_barmenu(Action & $action)
{
    // -----------------------------------
    // Get all the params
    $nbdoc = GetHttpVars("nbdoc");
    $count = getHttpVars("count", "");
    $dirid = GetHttpVars("dirid");
    $target = GetHttpVars("target"); // target for hyperlinks
    $dbaccess = $action->dbaccess;
    
    $dir = new_Doc($dbaccess, $dirid);
    
    $action->lay->set("wtarget", urlencode($target));
    $action->lay->set("title", $dir->getHTMLTitle());
    $action->lay->set("pds", $dir->urlWhatEncodeSpec("")); // parameters for searches
    if ($nbdoc > 1) {
        if (is_numeric($count) && $count > $nbdoc) {
            $nbdocMsg = sprintf(_("Showing %d of %d documents") , $nbdoc, $count);
        } else {
            $nbdocMsg = sprintf(_("Showing %d documents") , $nbdoc);
        }
        $action->lay->set("nbdoc", $nbdocMsg);
    } else {
        $action->lay->set("nbdoc", sprintf(_("Showing %d document") , $nbdoc));
    }
    
    $action->lay->set("dirid", urlencode($dirid));
    $tarch = array();
    $toolmenu = array(
        'tobasket',
        'insertbasket',
        'clear',
        'props',
        'openfolio',
        'applybatch',
        'export',
        'insert'
    );
    if ($dir->fromname != "ARCHIVING") { // no archive archive
        include_once ("FDL/Class.SearchDoc.php");
        $s = new SearchDoc($dir->dbaccess, "ARCHIVING");
        $s->setObjectReturn();
        $s->addFilter("arc_status = 'O'");
        $s->search();
        if ($s->count() > 0) {
            while ($archive = $s->getNextDoc()) {
                if ($archive->control("modify") == "") {
                    $toolmenu[] = "arch" . $archive->id;
                    $tarch[] = array(
                        "archid" => $archive->id,
                        "archtitle" => sprintf(_("Insert all into %s archive") , $archive->getHTMLTitle())
                    );
                }
            }
        }
        $action->lay->setBlockdata("ARCH", $tarch);
    }
    
    popupInit("viewmenu", array(
        'vlist',
        'vicon',
        'vcol',
        'vdetail'
    ));
    popupInit("toolmenu", $toolmenu);
    foreach ($tarch as $arch) {
        popupActive("toolmenu", 1, "arch" . $arch["archid"]);
    }
    popupActive("viewmenu", 1, 'vlist');
    popupActive("viewmenu", 1, 'vicon');
    popupActive("viewmenu", 1, 'vcol');
    popupActive("viewmenu", 1, 'vdetail');
    // clear only for basket :: too dangerous
    if (($dir->fromid == getFamIdFromName($dbaccess, "BASKET")) || ($dir->fromid == getFamIdFromName($dbaccess, "ARCHIVING"))) {
        popupInvisible("toolmenu", 1, 'tobasket');
        popupInvisible("toolmenu", 1, 'insertbasket');
        popupActive("toolmenu", 1, 'clear');
    } else {
        popupActive("toolmenu", 1, 'tobasket');
        if ($dir->defDoctype != 'D') popupInvisible("toolmenu", 1, 'insertbasket');
        else {
            popupActive("toolmenu", 1, 'insertbasket');
            popupActive("toolmenu", 1, 'insert');
        }
        popupInvisible("toolmenu", 1, 'clear');
    }
    if ($dir->initid == 10) {
        popupActive("toolmenu", 1, 'clear'); // import folder
        
    }
    popupActive("toolmenu", 1, 'props');
    popupActive("toolmenu", 1, 'openfolio');
    if ($action->HasPermission("FREEDOM_ADMIN")) {
        popupActive("toolmenu", 1, 'applybatch');
    }
    if ($action->HasPermission("EXPORT", "FDL")) {
        popupActive("toolmenu", 1, 'export');
    }
    
    popupGen(1);
}
