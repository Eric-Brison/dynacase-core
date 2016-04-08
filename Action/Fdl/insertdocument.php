<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 *  Insert documents in  folder
 *
 * @author Anakeen
 * @version $Id: insertdocument.php,v 1.2 2007/08/07 16:56:59 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Dir.php");
include_once ("FDL/Class.DocFam.php");
/**
 * Insert documents in  folder
 * @param Action &$action current action
 * @global id int Http var : folder document identifier to see
 */
function insertdocument(Action & $action)
{
    
    $docid = GetHttpVars("id");
    $uchange = GetHttpVars("uchange");
    $dbaccess = $action->dbaccess;
    
    if ($docid == "") $action->exitError(_("no document reference"));
    if (!is_numeric($docid)) $docid = getIdFromName($dbaccess, $docid);
    if (intval($docid) == 0) $action->exitError(sprintf(_("unknow logical reference '%s'") , GetHttpVars("id")));
    /**
     * @var Dir $doc
     */
    $doc = new_Doc($dbaccess, $docid);
    if (!$doc->isAffected()) $action->exitError(sprintf(_("cannot see unknow reference %s") , $docid));
    if ($doc->defDoctype != 'D') $action->exitError(sprintf(_("not a static folder %s") , $doc->title));
    
    $err = $doc->canModify();
    if ($err != "") $action->exitError($err);
    
    $erradd = array();
    $errdel = array();
    foreach ($uchange as $initid => $state) {
        if ($initid > 0) {
            
            switch ($state) {
                case "new":
                    $erradd[$initid] = $doc->addFile($initid);
                    break;

                case "deleted":
                    $errdel[$initid] = $doc->delFile($initid);
                    break;
            }
        }
    }
    
    $n = 0;
    $err = "";
    foreach ($erradd as $k => $v) {
        if ($v == "") $n++;
        else $err.= "$v\n";
    }
    if ($n > 0) $action->addWarningMsg(sprintf(_("Add %d document(s)") , $n));
    $n = 0;
    foreach ($errdel as $k => $v) {
        if ($v == "") $n++;
        else $err.= "$v\n";
    }
    if ($n > 0) $action->addWarningMsg(sprintf(_("Suppress %d document(s)") , $n));
    if ($err != "") $action->addWarningMsg($err);
    
    redirect($action, GetHttpVars("redirect_app", "FDL") , GetHttpVars("redirect_act", "FDL_CARD&refreshfld=Y&id=$docid") , $action->GetParam("CORE_STANDURL"));
}
