<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * View workflow graph
 *
 * @author Anakeen 2000
 * @version $Id: param_workflow_graph.php,v 1.2 2008/03/11 11:25:04 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.WDoc.php");
// -----------------------------------

/**
 * Edition to affect document
 * @param Action &$action current action
 * @global id Http var : document id to affect
 * @global viewdoc Http var : with preview of affect document [Y|N]
 */
function param_workflow_graph(&$action)
{
    $docid = GetHttpVars("id");
    $viewdoc = (GetHttpVars("viewdoc", "N") == "Y");
    $type = GetHttpVars("type", "simple"); // type of graph
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/geometry.js");
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $doc = new_doc($dbaccess, $docid);
    $action->lay->set("id", $doc->id);
}
