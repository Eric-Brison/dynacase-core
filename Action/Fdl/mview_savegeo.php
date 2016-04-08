<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: mview_savegeo.php,v 1.3 2008/10/10 07:26:45 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */
// ---------------------------------------------------------------
// $Id: mview_savegeo.php,v 1.3 2008/10/10 07:26:45 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Fdl/mview_savegeo.php,v $
// ---------------------------------------------------------------

// ==========================================================================
// save geometry of mini view
// ==========================================================================

// -----------------------------------
// -----------------------------------
function mview_savegeo(&$action)
{
    // -----------------------------------
    $geometry = GetHttpVars("geometry"); // the six geometries frame
    if ($geometry != "") {
        
        $action->setparamu("MVIEW_GEO", $geometry);
        
        $action->AddWarningMsg(sprintf(_("geometry saved : %s") , $geometry));
    }
    redirect($action, "CORE", "BLANK", $action->GetParam("CORE_STANDURL"));
}
?>
