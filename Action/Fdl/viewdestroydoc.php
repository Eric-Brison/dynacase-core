<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Difference between 2 documents
 *
 * @author Anakeen
 * @version $Id: diffdoc.php,v 1.5 2008/08/14 09:59:14 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Dir.php");
include_once ("FDL/Class.DocLog.php");
/**
 * View last history items of destroyed documents
 * @param Action &$action current action
 * @global id int Http var : document to see
 */
function viewdestroydoc(Action & $action)
{
    $docid = $action->getArgument("id");
    $dbaccess = $action->dbaccess;
    $d = new_doc($dbaccess, $docid);
    if ($d->isAffected()) {
        $action->exitError(sprintf(_("document %s [%d] is not destroyed") , $d->getTitle() , $d->id));
    }
    
    $q = new QueryDb($dbaccess, "dochisto");
    $q->AddQuery("id=" . $docid);
    $l = $q->Query(0, 1, "TABLE");
    $title = '';
    if (is_array($l)) {
        
        $initid = $l[0]["initid"];
        if ($initid) {
            $q = new QueryDb($dbaccess, "dochisto");
            $q->AddQuery("initid=" . $initid);
            $q->order_by = 'date desc';
            $limit = 10;
            $l = $q->Query(0, $limit, "TABLE");
            $action->lay->eSetBlockData("HISTO", $l);
            $q = new QueryDb($dbaccess, "doclog");
            $q->AddQuery("initid=" . $initid);
            $limit = 0;
            $q->order_by = 'date desc';
            $l = $q->Query(0, $limit, "TABLE");
            if ($q->nb > 0) {
                $title = $l[0]["title"];
                $action->lay->eSetBlockData("LOG", $l);
            }
        }
    }
    
    $action->lay->eSet("title", $title);
    $action->lay->eSet("trace", sprintf(_("Last traces of %s document : %s") , $docid, $title));
}
