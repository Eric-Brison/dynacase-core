<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Generate Layout to edit array (table)
 *
 * @author Anakeen 2005
 * @version $Id: viewarray.php,v 1.4 2007/09/27 12:23:40 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocAttr.php");

include_once ("FDL/freedom_util.php");
include_once ("FDL/editutil.php");
// Compute value to be inserted in a specific layout
// -----------------------------------
function viewarray(&$action)
{
    // -----------------------------------
    // GetAllParameters
    $docid = GetHttpVars("id", 0);
    $classid = GetHttpVars("classid");
    $arrayid = strtolower(GetHttpVars("arrayid"));
    $vid = GetHttpVars("vid"); // special controlled view
    $ulink = (GetHttpVars("ulink", '2')); // add url link
    $target = GetHttpVars("target"); // may be mail
    // $width=GetHttpVars("width","100%"); // table width
    // Set the globals elements
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    if ($docid == 0) $doc = createDoc($dbaccess, $classid);
    else $doc = new_Doc($dbaccess, $docid);
    
    if (($vid != "") && ($doc->cvid > 0)) {
        // special controlled view
        $cvdoc = new_Doc($dbaccess, $doc->cvid);
        $tview = $cvdoc->getView($vid);
        if ($tview) $doc->setMask($tview["CV_MSKID"]);
    }
    //$oattr=$doc->getAttribute($arrayid);
    //$xmlarray=$doc->GetHtmlAttrValue($arrayid,$target,$ulink);
    $xmlarray = $doc->GetHtmlValue($doc->getAttribute($arrayid) , $doc->getValue($arrayid) , $target, $ulink);
    
    $action->lay->set("array", $xmlarray);
}
?>
