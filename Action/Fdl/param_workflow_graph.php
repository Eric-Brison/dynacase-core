<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * View workflow graph
 *
 * @author Anakeen
 * @version $Id: param_workflow_graph.php,v 1.2 2008/03/11 11:25:04 eric Exp $
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
 * @global id int Http var : document id to affect
 * @global viewdoc string Http var : with preview of affect document [Y|N]
 */
function param_workflow_graph(&$action)
{
    $docid = GetHttpVars("id");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/geometry.js");
    
    $dbaccess = $action->dbaccess;
    
    $doc = new_doc($dbaccess, $docid);
    $action->lay->rSet("id", $doc->id);
}
