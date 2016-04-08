<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Add folder in user bookmarks
 *
 * @author Anakeen
 * @version $Id: freedom_addbookmark.php,v 1.3 2005/06/28 08:37:46 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */
include_once ("FDL/Class.Doc.php");
/**
 * Add folder bookmark
 * @param Action &$action current action
 * @global dirid int Http var : folder identifier to add
 */
function freedom_addbookmark(Action & $action)
{
    $dirid = GetHttpVars("dirid");
    
    $dbaccess = $action->dbaccess;
    $attrid = "FREEDOM_UBOOK";
    
    $ubook = $action->GetParam($attrid);
    if (strlen($ubook) > 2) $tubook = explode('][', substr($ubook, 1, -1));
    else $tubook = array();
    $err = "";
    $tid = array();
    foreach ($tubook as $k => $v) {
        list($id, $label) = explode("|", $v);
        $tid[$id] = $label;
    }
    // add new folder
    $doc = new_Doc($dbaccess, $dirid);
    if ($doc->isAlive()) {
        $tid[$doc->initid] = $doc->title;
    } else {
        $err = sprintf(_("folder is not valid: bookmark unchanged"));
    }
    // recompose the paramters
    $newbook = "";
    foreach ($tid as $k => $v) {
        $newbook.= "[$k|$v]";
    }
    
    if ($err != "") {
        AddWarningMsg($err);
    } else {
        AddWarningMsg(sprintf(_("folder %s as been added in your bookmark") , $doc->title));
        $action->parent->param->Set($attrid, $newbook, Param::PARAM_USER . $action->user->id, $action->parent->id);
    }
}
