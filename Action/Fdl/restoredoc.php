<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * UnTrash document
 *
 * @author Anakeen
 * @version $Id: restoredoc.php,v 1.1 2007/10/16 14:07:14 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Lib.Dir.php");
/**
 * Get a doc from the trash
 * @param Action &$action current action
 * @global id int Http var : document id to restore
 * @global reload string Http var : [Y|N] if Y not xml but redirect to fdl_card
 * @global containt string Http var : if 'yes' restore also folder items
 */
function restoredoc(Action & $action)
{
    
    $docid = GetHttpVars("id");
    $dbaccess = $action->dbaccess;
    
    $doc = new_doc($dbaccess, $docid);
    
    $err = '';
    if ($doc->isAffected()) {
        if (!$doc->isAlive()) {
            $err = $doc->undelete();
        }
    } else $err = sprintf(_("document [%s] not found"));
    
    if ($err) $action->addWarningMsg($err);
    
    redirect($action, "FDL", "FDL_CARD&sole=Y&refreshfld=Y&latest=Y&id=$docid");
}
