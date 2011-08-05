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
 * @version $Id: duplicate.php,v 1.17 2008/10/09 16:26:43 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Dir.php");
// -----------------------------------
function duplicate(&$action, $dirid, $docid, $temporary = false)
{
    $dbaccess = $action->GetParam("FREEDOM_DB");
    // test if doc with values
    $doc = new_Doc($dbaccess, $docid);
    
    if ($doc->isConfidential()) redirect($action, "FDL", "FDL_CONFIDENTIAL&id=" . $doc->id);
    
    $cdoc = $doc->getFamDoc();
    
    $err = $cdoc->control('create');
    if ($err != "") $action->exitError(sprintf(_("no privilege to create this kind (%d) of document") , $doc->fromid));
    
    $values = $doc->getValues();
    if (!is_array($values)) $action->exitError(_("this kind of document cannot be duplicate"));
    // initiate a copy of the doc
    $copy = $doc->copy($temporary);
    if (!is_object($copy)) $action->exitError($copy);
    
    if ($err != "") $action->exitError($err);
    //  $copy->SetTitle($copy->title);
    $copy->refresh();
    $copy->postmodify();
    $err = $copy->modify();
    if ($err != "") $action->exitError($err);
    
    $copy->title = _("duplication of") . " " . $doc->title;
    $copy->modify(true, array(
        "title"
    ) , true);
    // add to the same folder
    if (($dirid == 0) && ($copy->id > 0)) {
        $dirid = $doc->prelid;
    }
    
    if (($dirid > 0) && ($copy->id > 0)) {
        $fld = new_Doc($dbaccess, $dirid);
        if ($fld->isAlive()) {
            $err = $fld->AddFile($copy->id);
            if ($err != "") {
                $copy->Delete();
                $action->exitError($err);
            } else {
                $action->AddActionDone("ADDFILE", $fld->initid);
            }
        }
    }
    
    $action->AddLogMsg(sprintf(_("new duplicate document is named : %s") , $copy->title));
    
    return $copy;
}
?>
