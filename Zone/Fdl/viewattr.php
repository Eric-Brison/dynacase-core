<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: viewattr.php,v 1.10 2008/08/14 09:59:14 eric Exp $
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
function viewattr(Action & $action, $htmlval = true, $htmllink = true)
{
    // -----------------------------------
    // GetAllParameters
    $docid = GetHttpVars("id");
    $abstract = (GetHttpVars("abstract", 'N') == "Y"); // view doc abstract attributes
    // Set the globals elements
    $dbaccess = $action->dbaccess;
    
    $doc = new_Doc($dbaccess, $docid);
    
    $listattr = $doc->GetNormalAttributes();
    // each value can be instanced with L_<ATTRID> for label text and V_<ATTRID> for value
    foreach ($listattr as $k => $v) {
        
        $value = chop($doc->getRawValue($v->id));
        //------------------------------
        // Set the table value elements
        if ($v->mvisibility != "H") {
            // don't see  non abstract if not
            if ($abstract && !$v->isInAbstract) {
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
    foreach ($listattr as $k => $v) {
        
        $action->lay->Set("L_" . strtoupper($v->id) , $v->getLabel());
    }
}
