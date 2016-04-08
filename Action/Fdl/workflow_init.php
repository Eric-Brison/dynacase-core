<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Regenrate workflow attributes
 *
 * @author Anakeen
 * @version $Id: workflow_init.php,v 1.5 2008/12/31 14:39:52 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.SearchDoc.php");
include_once ("FDL/Lib.Attr.php");
include_once ("FDL/Class.DocFam.php");
// -----------------------------------
function workflow_init(Action & $action)
{
    
    $docid = GetHttpVars("id"); // view doc abstract attributes
    if ($docid == "") {
        $action->exitError(_("workflow_init :: id is empty"));
    }
    
    $dbaccess = $action->dbaccess;
    /**
     * @var WDoc $wdoc
     */
    $wdoc = new_Doc($dbaccess, $docid);
    if (!$wdoc->isAlive()) {
        $action->exitError(sprintf(_("unknown document id %s") , $docid));
    }
    if (($err = $wdoc->control("edit")) !== "") {
        $action->exitError($err);
    }
    $wdoc->CreateProfileAttribute();
    if ($wdoc->doctype == 'C') $cid = $wdoc->id;
    else $cid = $wdoc->fromid;
    
    $query = new QueryDb($dbaccess, "DocFam");
    $query->AddQuery("id=$cid");
    $table1 = $query->Query(0, 0, "TABLE");
    if ($query->nb > 0) {
        if ($wdoc->isAffected() && strstr($wdoc->usefor, 'W')) {
            refreshPhpPgDoc($dbaccess, $cid);
        } else {
            $action->exitError(sprintf(_("workflow_init :: id %s is not a workflow") , $docid));
        }
    } else {
        $action->exitError(sprintf(_("workflow_init :: workflow id %s not found") , $cid));
    }
    
    $s = new SearchDoc($dbaccess, $wdoc->fromid);
    $s->setObjectReturn();
    $s->search();
    while ($doc = $s->getNextDoc()) {
        $doc->postStore();
    }
    
    $action->addWarningMsg(_("workflow has been recomposed"));
    redirect($action, "FDL", "FDL_CARD&id=$docid");
}
