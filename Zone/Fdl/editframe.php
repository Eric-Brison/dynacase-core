<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generate Layout to edit frame (fieldset)
 *
 * @author Anakeen
 * @version $Id: editframe.php,v 1.27 2008/08/14 09:59:14 eric Exp $
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
function editframe(Action & $action)
{
    // -----------------------------------
    // GetAllParameters
    $docid = $action->getArgument("id", 0);
    $classid = $action->getArgument("classid");
    $frameid = strtolower($action->getArgument("frameid"));
    $vid = $action->getArgument("vid"); // special controlled view
    // Set the globals elements
    $dbaccess = $action->dbaccess;
    
    if ($docid == 0) {
        $doc = createDoc($dbaccess, $classid);
        if (fdl_setHttpVars($doc)) $doc->refresh();
    } else $doc = new_Doc($dbaccess, $docid);
    
    if (($vid != "") && ($doc->cvid > 0)) {
        /**
         * special controled view
         * @var CVDoc $cvdoc
         */
        $cvdoc = new_Doc($dbaccess, $doc->cvid);
        $tview = $cvdoc->getView($vid);
        if ($tview) $doc->setMask($tview["CV_MSKID"]);
    }
    
    $listattr = $doc->GetNormalAttributes();
    
    if ($action->getArgument("viewconstraint") == "Y") { // from modcard function if constraint error
        include_once ("FDL/modcard.php");
        setPostVars($doc); // HTTP VARS comes from previous edition
        
    }
    
    $help = $doc->getHelpPage();
    
    $thval = array();
    $tval = array();
    
    $foa = $doc->getAttribute($frameid);
    if (!$foa) $action->exitError(sprintf("attribute %s not found", $frameid));
    if ($foa->getOption("vlabel") == "none") $action->lay->set("flabel", '');
    else $action->lay->set("flabel", mb_ucfirst($foa->getLabel()));
    $action->lay->set("frameid", $foa->id);
    
    foreach ($listattr as $k => $v) {
        /**
         * @var NormalAttribute $v
         */
        if (($v->fieldSet->id != $frameid)) continue;
        if ($v->inArray()) continue;
        if ($v->mvisibility == "I") continue; // not editable
        $action->lay->set("ehelp", ($help->isAlive()) ? $help->getAttributeHelpUrl($v->fieldSet->id) : false);
        $action->lay->set("ehelpid", ($help->isAlive()) ? $help->id : false);
        //------------------------------
        // Set the table value elements
        $value = chop($doc->getRawValue($v->id));
        if ($docid == 0) {
            $value = $doc->GetValueMethod($value); // execute method for default values
            
        }
        if (($v->mvisibility == "H") || ($v->mvisibility == "R")) {
            
            $thval[$k]["avalue"] = getHtmlInput($doc, $v, $value);
            // special case for hidden values
            
        } else {
            $tval[$k]["alabel"] = $v->getLabel();
            if ($v->needed) $tval[$k]["labelclass"] = "FREEDOMLabelNeeded";
            else $tval[$k]["labelclass"] = "FREEDOMLabel";
            $tval[$k]["avalue"] = getHtmlInput($doc, $v, $value);
            
            $vlabel = $v->getOption("vlabel");
            $tval[$k]["winput"] = ($v->type == "array") ? "1%" : "30%"; // width
            $tval[$k]["TESTARRAY"] = (($v->type == "array") || (($v->type == "htmltext") && ($vlabel != 'left')) || ($vlabel == 'up') || ($vlabel == 'none'));
            $tval[$k]["viewlabel"] = (($v->type != "array") && ($vlabel != 'none'));
            $tval[$k]["aehelp"] = ($help->isAlive()) ? $help->getAttributeHelpUrl($v->id) : false;
            $tval[$k]["aehelpid"] = ($help->isAlive()) ? $help->id : false;
        }
    }
    $action->lay->setBlockData("FVALUES", $tval);
    $action->lay->setBlockData("FHIDDENS", $thval);
    if ((count($tval) + count($thval)) > 0) {
        
        $action->lay->setBlockData("FRAME", array(
            array(
                "bou"
            )
        ));
    }
}
