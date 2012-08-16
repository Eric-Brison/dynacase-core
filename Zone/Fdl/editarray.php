<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generate Layout to edit array (table)
 *
 * @author Anakeen
 * @version $Id: editarray.php,v 1.3 2008/06/05 12:53:30 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
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
function editarray(Action & $action)
{
    // -----------------------------------
    // GetAllParameters
    $docid = GetHttpVars("id", 0);
    $classid = GetHttpVars("classid");
    $row = GetHttpVars("row", -1);
    $arrayid = strtolower(GetHttpVars("arrayid"));
    $vid = GetHttpVars("vid"); // special controlled view
    // Set the globals elements
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    if ($docid == 0) {
        $doc = createDoc($dbaccess, $classid);
        if (fdl_setHttpVars($doc)) $doc->refresh();
    } else $doc = new_Doc($dbaccess, $docid);
    
    if (($vid != "") && ($doc->cvid > 0)) {
        /**
         * special controlled view
         * @var CVDoc $cvdoc
         */
        $cvdoc = new_Doc($dbaccess, $doc->cvid);
        $tview = $cvdoc->getView($vid);
        if ($tview) $doc->setMask($tview["CV_MSKID"]);
    }
    
    $oattr = $doc->getAttribute($arrayid);
    if (!$oattr) $action->lay->template = sprintf(_("attribute %s not found") , $arrayid);
    else {
        if ($oattr->type != "array") $action->lay->template = sprintf(_("attribute %s not an array") , $arrayid);
        else {
            $of = new DocFormFormat($doc);
            $of->getLayArray($action->lay, $doc, $oattr, $row);
        }
    }
}
?>
