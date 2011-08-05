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
 * @version $Id: adddirquery.php,v 1.5 2004/03/25 11:10:10 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.Directory.php");
include_once ("FDL/freedom_util.php");
// -----------------------------------
function adddirquery(&$action)
{
    // -----------------------------------
    
    // Get all the params
    $docid = GetHttpVars("docid");
    
    redirect($action, "FDL", "FDL_CARD&id=$docid");
}
?>
