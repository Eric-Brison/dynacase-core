<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: popupcard.php,v 1.8 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */
// ---------------------------------------------------------------
// $Id: popupcard.php,v 1.8 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Generic/popupcard.php,v $
// ---------------------------------------------------------------
include_once ("FDL/Class.Doc.php");
// -----------------------------------
function popupcard(&$action)
{
    // -----------------------------------
    // ------------------------------
    // define accessibility
    $docid = GetHttpVars("id");
    $abstract = (GetHttpVars("abstract", 'N') == "Y");
    $headers = (GetHttpVars("head", 'no') == "yes");
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $doc = new_Doc($dbaccess, $docid);
    $kdiv = 1; // only one division
    $action->lay->Set("id", $docid);
    
    include_once ("FDL/popup_util.php");
    // ------------------------------------------------------
    // definition of popup menu
    popupInit('popupcard', array(
        'editdoc',
        'editstate',
        'unlockdoc',
        'chgcatg',
        'properties',
        'duplicate',
        'headers',
        'delete',
        'cancel'
    ));
    
    $clf = ($doc->CanLockFile() == "");
    $cuf = ($doc->CanUnLockFile() == "");
    $cud = ($doc->canEdit() == "");
    
    Popupactive('popupcard', $kdiv, 'cancel');
    
    if ($doc->isLocked()) {
        if ($cuf) popupActive('popupcard', $kdiv, 'unlockdoc');
        else popupInactive('popupcard', $kdiv, 'unlockdoc');
    } else popupInvisible('popupcard', $kdiv, 'unlockdoc');
    
    popupActive('popupcard', $kdiv, 'duplicate');
    
    popupInvisible('popupcard', $kdiv, 'editstate');
    
    if ($doc->locked == - 1) { // fixed document
        popupInvisible('popupcard', $kdiv, 'editdoc');
        popupInvisible('popupcard', $kdiv, 'delete');
        popupInvisible('popupcard', $kdiv, 'unlockdoc');
        popupInvisible('popupcard', $kdiv, 'chgcatg');
    } else {
        if ($cud || $clf) {
            popupActive('popupcard', $kdiv, 'editdoc');
            $action->lay->Set("deltitle", $doc->title);
            popupActive('popupcard', $kdiv, 'delete');
            popupActive('popupcard', $kdiv, 'chgcatg');
        } else {
            popupInactive('popupcard', $kdiv, 'editdoc');
            popupInactive('popupcard', $kdiv, 'delete');
            popupInactive('popupcard', $kdiv, 'chgcatg');
        }
        if ($doc->wid > 0) {
            $wdoc = new_Doc($doc->dbaccess, $doc->wid);
            $wdoc->Set($doc);
            if (count($wdoc->GetFollowingStates()) > 0) popupActive('popupcard', $kdiv, 'editstate');
            else popupInactive('popupcard', $kdiv, 'editstate');
        }
    }
    
    if ($abstract) popupActive('popupcard', $kdiv, 'properties');
    else popupInvisible('popupcard', $kdiv, 'properties');
    if ($headers) popupInvisible('popupcard', $kdiv, 'headers');
    else Popupactive('popupcard', $kdiv, 'headers');
    
    popupGen($kdiv);
}
