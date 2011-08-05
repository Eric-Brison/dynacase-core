<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Interface to create new execution from batch
 *
 * @author Anakeen 2000
 * @version $Id: freedom_searchprocess.php,v 1.1 2005/08/19 16:14:50 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.DocSearch.php");
include_once ("FDL/freedom_util.php");
include_once ("GENERIC/generic_util.php");
/**
 * View a document
 * @param Action &$action current action
 * @global id Http var : document identificator for process document
 * @global target Http var : window name when click on document
 */
function freedom_searchprocess(&$action)
{
    // -----------------------------------
    // Get all the params
    $docid = GetHttpVars("id"); // id doc to search
    $target = GetHttpVars("target"); //
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    if (($famid !== 0) && (!is_numeric($famid))) {
        $famid = getFamIdFromName($dbaccess, $famid);
    }
    if ($docid == "") $action->exitError(_("related search aborted : no parameter found"));
    
    $doc = new_Doc($dbaccess, $docid);
    
    $sdoc = createDoc($dbaccess, 5); //new DocSearch($dbaccess);
    $sdoc->doctype = 'T'; // it is a temporary document (will be delete after)
    $sdoc->title = sprintf(_("process search comes from %s") , $doc->title);
    
    $sdoc->Add();
    
    $sqlfilter[] = "locked != -1";
    
    $tdoc = $doc->getRevisions("TABLE");
    $tid = array();
    foreach ($tdoc as $k => $v) $tid[] = $v["id"];
    
    $sqlfilter[] = GetSqlCond($tid, 'exec_idref');
    //  $sqlfilter[]= "doctype ='F'";
    //  $sqlfilter[]= "usefor != 'D'";
    //  $sqlfilter[]= "(".implode(") OR (",$tfil).")";
    $query = getSqlSearchDoc($dbaccess, 0, getFamIdFromName($dbaccess, "EXEC") , $sqlfilter);
    $sdoc->AddQuery($query);
    redirect($action, "FREEDOM", "FREEDOM_VIEW&target=$target&view=column&dirid=" . $sdoc->id);
    // redirect($action,GetHttpVars("app"),"GENERIC_LIST&dirid=".$sdoc->id."&famid=$famid&catg=0");
    
    
}
?>