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
 * @version $Id: freedom_editpreffam.php,v 1.3 2005/02/08 11:34:37 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: freedom_editpreffam.php,v 1.3 2005/02/08 11:34:37 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_editpreffam.php,v $
// ---------------------------------------------------------------

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Lib.Dir.php");

function freedom_editpreffam(&$action)
{
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/geometry.js");
    $tcdoc = GetClassesDoc($dbaccess, $action->user->id, array(
        1,
        2
    ) , "TABLE");
    $idsfam = $action->GetParam("FREEDOM_PREFFAMIDS");
    $tidsfam = explode(",", $idsfam);
    
    $selectclass = array();
    if (is_array($tcdoc)) {
        while (list($k, $pdoc) = each($tcdoc)) {
            
            $selectclass[$k]["cid"] = $pdoc["id"];
            $selectclass[$k]["ctitle"] = $pdoc["title"];
            $selectclass[$k]["selected"] = (in_array($pdoc["id"], $tidsfam)) ? "checked" : "";
        }
    }
    
    uasort($selectclass, "cmpselect");
    $action->lay->SetBlockData("SELECTPREF", $selectclass);
}

function cmpselect($a, $b)
{
    return strcasecmp($a["ctitle"], $b["ctitle"]);
}
?>
