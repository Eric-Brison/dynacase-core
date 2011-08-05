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
 * @version $Id: edit_search_fulltext.php,v 1.5 2005/02/08 11:34:37 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: edit_search_fulltext.php,v 1.5 2005/02/08 11:34:37 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/edit_search_fulltext.php,v $
// ---------------------------------------------------------------
include_once ("FDL/Lib.Dir.php");
include_once ("FREEDOM/search_fulltext.php");
// -----------------------------------
function edit_search_fulltext(&$action)
{
    // -----------------------------------
    if (!extension_loaded('mnogosearch')) search_error($action, _("mnogosearch php extension not loaded"));
    
    $tmatch = array(
        UDM_MODE_ALL => _("all") ,
        UDM_MODE_ANY => _("any") ,
        UDM_MODE_BOOL => _("boolean") ,
        UDM_MODE_PHRASE => _("full phrase")
    );
    $tsearch = array(
        UDM_MATCH_WORD => _("word") ,
        UDM_MATCH_SUBSTR => _("substring") ,
        UDM_MATCH_BEGIN => _("starting") ,
        UDM_MATCH_END => _("ending")
    );
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    // Get all the params
    $dir = GetHttpVars("dirid"); // insert search in this folder
    $action->lay->Set("dirid", $dir);
    
    while (list($k, $v) = each($tmatch)) {
        $selectmatch[$k]["idmatch"] = $k;
        $selectmatch[$k]["matchdescr"] = $action->Text($v);
    }
    
    while (list($k, $v) = each($tsearch)) {
        $selectsearchfor[$k]["idsearchfor"] = $k;
        $selectsearchfor[$k]["searchfordescr"] = $action->Text($v);
    }
    
    $tclassdoc = GetClassesDoc($dbaccess, $action->user->id, 0, "TABLE");
    
    while (list($k, $cdoc) = each($tclassdoc)) {
        $selectclass[$k]["idcdoc"] = $cdoc["initid"];
        $selectclass[$k]["classname"] = $cdoc["title"];
    }
    
    $action->lay->SetBlockData("SELECTMATCH", $selectmatch);
    $action->lay->SetBlockData("SELECTSEARCHFOR", $selectsearchfor);
    $action->lay->SetBlockData("SELECTCLASS", $selectclass);
}
?>
