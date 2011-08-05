<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * View Help Document
 *
 * @author Anakeen 2000
 * @version $Id: family_help.php,v 1.4 2007/09/04 09:09:10 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 */

include_once ("Lib.Http.php");
include_once ("FDL/Class.Doc.php");

function dochelp(Action & $action)
{
    
    $docid = $action->getArgument("id");
    $anchor = $action->getArgument("anchor");
    $dbaccess = $action->getParam("FREEDOM_DB");
    $doc = new_doc($dbaccess, $docid);
    if (!$doc->isAlive()) {
    }
    
    redirect($action, "FDL", "IMPCARD&id=$docid#$anchor");
}
?>