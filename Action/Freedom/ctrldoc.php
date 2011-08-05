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
 * @version $Id: ctrldoc.php,v 1.6 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: ctrldoc.php,v 1.6 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/ctrldoc.php,v $
// ---------------------------------------------------------------
include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocAttr.php");

include_once ("Class.TableLayout.php");
include_once ("Class.QueryDb.php");
include_once ("Class.QueryGen.php");
include_once ("FDL/freedom_util.php");
// -----------------------------------
// -----------------------------------
function ctrldoc(&$action)
{
    // -----------------------------------
    // Set the globals elements
    $baseurl = $action->GetParam("CORE_BASEURL");
    $standurl = $action->GetParam("CORE_STANDURL");
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $docid = GetHttpVars("id");
    
    $doc = new_Doc($dbaccess, $docid);
    
    $doc->SetControl();
    
    redirect($action, "FDL", "FDL_CARD&id=$docid");
}
?>
