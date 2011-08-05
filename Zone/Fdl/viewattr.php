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
 * @version $Id: viewattr.php,v 1.10 2008/08/14 09:59:14 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */
// ---------------------------------------------------------------
// $Id: viewattr.php,v 1.10 2008/08/14 09:59:14 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Fdl/viewattr.php,v $
// ---------------------------------------------------------------
include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocAttr.php");

include_once ("FDL/freedom_util.php");
// Compute value to be inserted in a specific layout
// -----------------------------------
function viewattr(&$action, $htmlval = true, $htmllink = true)
{
    // -----------------------------------
    // GetAllParameters
    $docid = GetHttpVars("id");
    $abstract = (GetHttpVars("abstract", 'N') == "Y"); // view doc abstract attributes
    // Set the globals elements
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $doc = new_Doc($dbaccess, $docid);
    
    $listattr = $doc->GetNormalAttributes();
    // each value can be instanced with L_<ATTRID> for label text and V_<ATTRID> for value
    while (list($k, $v) = each($listattr)) {
        
        $value = chop($doc->GetValue($v->id));
        //------------------------------
        // Set the table value elements
        if ($v->mvisibility != "H") {
            // don't see  non abstract if not
            if (($abstract) && ($v->abstract != "Y")) {
                $action->lay->Set("V_" . $v->id, "");
                $action->lay->Set("L_" . $v->id, "");
            } else {
                $action->lay->Set("V_" . strtoupper($v->id) , $htmlval ? $doc->GetHtmlValue($v, $value, "_self", $htmllink) : $value);
                $action->lay->Set("L_" . strtoupper($v->id) , $v->getLabel());
            }
        }
    }
    
    $listattr = $doc->GetFieldAttributes();
    // each value can be instanced with L_<ATTRID> for label text and V_<ATTRID> for value
    while (list($k, $v) = each($listattr)) {
        
        $action->lay->Set("L_" . strtoupper($v->id) , $v->getLabel());
    }
}
?>
