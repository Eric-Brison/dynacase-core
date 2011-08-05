<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Duplicate a document
 *
 * @author Anakeen 2000
 * @version $Id: freedom_duplicate.php,v 1.12 2005/06/07 16:06:24 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/duplicate.php");

include_once ("FDL/Class.Dir.php");
// -----------------------------------
function freedom_duplicate(&$action)
{
    // -----------------------------------
    // Get all the params
    $dirid = GetHttpVars("dirid", 10); // where to duplicate
    $docid = GetHttpVars("id", 0); // doc to duplicate
    $folio = GetHttpVars("folio", "N") == "Y"; // return in folio
    duplicate($action, $dirid, $docid);
    
    if ($folio) redirect($action, "FREEDOM", "FOLIOLIST&dirid=" . $dirid);
    else redirect($action, "FREEDOM", "FREEDOM_VIEW&dirid=" . $dirid);
}
?>
