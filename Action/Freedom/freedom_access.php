<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: freedom_access.php,v 1.7 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: freedom_access.php,v 1.7 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_access.php,v $
// ---------------------------------------------------------------
include_once ("FDL/Class.Doc.php");
include_once "FDL/editutil.php";
// -----------------------------------
function freedom_access(Action & $action)
{
    // -----------------------------------
    // export all selected card in a tempory file
    // this file is sent by dowload
    // -----------------------------------
    // Get all the params
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $docid = $action->getArgument("id");
    $userId = $action->getArgument("userid");
    if (!$userId) {
        $duid = $action->getArgument("_userid");
        if ($duid) {
            $userId = User::getUidFromFid($duid);
        }
    }
    if (!$userId) {
        $userId = $action->user->id;
    }
    
    $doc = new_Doc($dbaccess, $docid);
    // test if current user can view ACL
    $err = $doc->Control("viewacl");
    if ($err != "") $action->exitError($err);
    editmode($action);
    $action->lay->Set("title", $doc->getHtmlTitle());
    // contruct user id list
    $ouser = new User('', $userId);
    $tiduser = $ouser->GetUserAndGroupList();
    
    $action->lay->Set("docid", $doc->id);
    
    $action->lay->Set("toProfil", $doc->getDocAnchor($doc->id, 'account', true, false, false, 'latest', true));
    if ($doc->dprofid) {
        $action->lay->Set("dynamic", true);
        $action->lay->Set("dprofid", $doc->dprofid);
        $action->lay->Set("toDynProfil", $doc->getHTMLTitle($doc->dprofid));
    } else {
        $action->lay->Set("dynamic", false);
    }
    
    $action->lay->Set("fid", $doc->getDocAnchor($ouser->fid, 'account', true, false, false, 'latest', true));
    $action->lay->Set("userid", ($userId == 1) ? $tiduser[0]->id : $userId);
    $action->lay->Set("username", User::getDisplayName($userId));
}
?>
