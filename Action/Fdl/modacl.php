<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: modacl.php,v 1.7 2005/06/28 08:37:46 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */
// ---------------------------------------------------------------
// $Id: modacl.php,v 1.7 2005/06/28 08:37:46 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Fdl/modacl.php,v $
// ---------------------------------------------------------------
include_once ("FDL/Class.Doc.php");
// -----------------------------------
function modacl(Action & $action)
{
    // -----------------------------------
    // get all parameters
    $usage = new ActionUsage($action);
    $usage->setDefinitionText("modify document acl");
    $userid = $usage->addRequiredParameter("userid", "user identificator");
    $aclp = $usage->addOptionalParameter("aclup", "acls to add", function ($argVal, $argName, $apiUsage)
    {
        return ActionUsage::isArray($argVal, $argName, $apiUsage);
    });
    $docid = $usage->addRequiredParameter("docid", "profil identificator");
    /**
     * @var array $aclp
     */
    $usage->verify();
    $dbaccess = $action->dbaccess;
    
    $doc = new_Doc($dbaccess, $docid);
    // test if current user can modify ACL
    $err = $doc->Control("modifyacl");
    if ($err != "") $action->exitError($err);
    
    $doc->removeControl($userid);
    if (is_array($aclp)) {
        foreach ($aclp as $k => $aclName) {
            $doc->addControl($userid, $aclName);
        }
    }
    
    $doc->setViewProfil();
    // recompute all related profile
    $doc->recomputeProfiledDocument();
    if (is_array($aclp) && (count($aclp) > 0)) {
        $aclName = array();
        foreach ($doc->dacls as $aclK => $aclInfo) {
            if (in_array($aclInfo["pos"], $aclp)) {
                $aclName[] = _($aclK);
            }
        }
        
        $doc->addHistoryEntry(sprintf(_("Change control for %s user. Set %s privileges") , Account::getDisplayName($userid) , implode(', ', $aclName)));
    } else {
        $doc->addHistoryEntry(sprintf(_("Change control for %s user. No one privilege") , Account::getDisplayName($userid)));
    }
    redirect($action, "FREEDOM", sprintf("FREEDOM_ACCESS&userid=%d&id=%d", $userid, $docid));
}
