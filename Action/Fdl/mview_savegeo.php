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
 * @version $Id: mview_savegeo.php,v 1.3 2008/10/10 07:26:45 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
