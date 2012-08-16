<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Regenrate workflow attributes
 *
 * @author Anakeen
 * @version $Id: workflow_init.php,v 1.5 2008/12/31 14:39:52 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
function workflow_init(&$action)
{
    
    $docid = GetHttpVars("id"); // view doc abstract attributes
    if ($docid == "") {
        $action->exitError(_("workflow_init :: id is empty"));
    }
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $wdoc = new_Doc($dbaccess, $docid);
    $wdoc->CreateProfileAttribute();
    if ($wdoc->doctype == 'C') $cid = $wdoc->id;
    else $cid = $wdoc->fromid;
    
    $query = new QueryDb($dbaccess, "DocFam");
    $query->AddQuery("id=$cid");
    $table1 = $query->Query(0, 0, "TABLE");
    if ($query->nb > 0) {
        $tdoc = $table1[0];
        
        if ($wdoc->isAffected() && strstr($wdoc->usefor, 'W')) {
            
            createDocFile($dbaccess, $tdoc);
            PgUpdateFamilly($dbaccess, $cid);
        } else {
            $action->exitError(sprintf(_("workflow_init :: id %s is not a workflow") , $docid));
        }
    } else {
        $action->exitError(sprintf(_("workflow_init :: workflow id %s not found") , $cid));
    }
    
    $s = new SearchDoc($dbaccess, $wdoc->fromid);
    $s->setObjectReturn();
    $s->search();
    while ($doc = $s->nextDoc()) {
        $doc->postModify();
    }
    
    $action->addWarningMsg(_("workflow has been recomposed"));
    redirect($action, "FDL", "FDL_CARD&id=$docid");
}
?>
