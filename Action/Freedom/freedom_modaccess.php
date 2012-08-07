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
 * @version $Id: freedom_modaccess.php,v 1.15 2008/10/22 16:14:42 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */
// ---------------------------------------------------------------
// $Id: freedom_modaccess.php,v 1.15 2008/10/22 16:14:42 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/freedom_modaccess.php,v $
// ---------------------------------------------------------------
include_once ("FDL/Class.Doc.php");
include_once ("FDL/Lib.Dir.php");
// -----------------------------------
function freedom_modaccess(Action & $action)
{
    // -----------------------------------
    global $_SERVER;
    // get all parameters
    
    /**
     * @var array $acls
     */
    $acls = $action->getArgument("acls", array());
    $docid = $action->getArgument("docid"); // id for controlled object
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $doc = new_Doc($dbaccess, $docid);
    // test if current user can modify ACL
    $err = $doc->Control("modifyacl");
    if ($err != "") $action->exitError($err);
    
    $before = array();
    $after = array();
    
    if (count($acls) > 0) {
        
        foreach ($acls as $userid => $aclon) {
            // modif permission for a particular user
            $perm = new DocPerm($dbaccess, array(
                $docid,
                $userid
            ));
            
            $before[$userid] = getUserAclNames($doc, $userid);
            
            $doc->removeControl($userid);
            foreach ($aclon as $k => $aclName) {
                
                $doc->addControl($userid, $aclName);
            }
            
            $after[$userid] = getUserAclNames($doc, $userid);
        }
        if ($err != "") $action->exitError($err);
        
        $doc->setViewProfil();
        // recompute all related profile
        $doc->recomputeProfiledDocument();
        //-------------------------------
        // compose history
        //** find username
        $tuid = array();
        foreach ($acls as $userid => $aclon) {
            $tuid[] = $userid;
        }
        $q = new QueryDb("", "Account");
        $q->AddQuery(getsqlcond($tuid, "id"));
        $l = $q->Query(0, 0, "TABLE");
        
        $tuname = array();
        if ($q->nb > 0) {
            foreach ($l as $k => $v) {
                $tuname[$v["id"]] = $v["firstname"] . ' ' . $v["lastname"];
            }
        }
        
        $q = new QueryDb("", "Vgroup");
        $q->AddQuery(getsqlcond($tuid, "num"));
        $l = $q->Query(0, 0, "TABLE");
        if ($q->nb > 0) {
            foreach ($l as $k => $v) {
                $tuname[$v["num"]] = sprintf(_("attribute %s") , $v["id"]);
            }
        }
        $tc = array();
        $posacls = array();
        foreach ($doc->dacls as $k => $v) {
            $posacls[$k] = $v["pos"];
        }
        
        foreach ($before as $uid => $acls) {
            
            $tadd = array();
            $tdel = array();
            foreach ($acls as $aclName => $granted) {
                if (($before[$uid][$aclName] === true) && ($after[$uid][$aclName] === false)) {
                    $tdel[] = $aclName;
                } elseif (($before[$uid][$aclName] === false) && ($after[$uid][$aclName] === true)) {
                    $tadd[] = $aclName;
                }
            }
            
            if (count($tadd) > 0) $tc[] = sprintf(_("Add acl %s for %s") , implode(", ", $tadd) , $tuname[$uid]);
            if (count($tdel) > 0) $tc[] = sprintf(_("Delete acl %s for %s") , implode(", ", $tdel) , $tuname[$uid]);
        }
        if (count($tc) > 0) $doc->addComment(sprintf(_("Change control :\n %s") , implode("\n", $tc)));
    }
    redirect($action, "FREEDOM", sprintf("FREEDOM_GACCESS&id=%s&allgreen=%s&group=%s", $docid, $action->getArgument("allgreen", "N") , $action->getArgument("group", "N")));
}

function getUserAclNames(Doc & $doc, $userid)
{
    $uperm = DocPerm::getUperm($doc->id, $userid, true);
    $doc->userid = $userid;
    $grant = array();
    foreach ($doc->acls as $aclName) {
        if ($doc->isExtendedAcl($aclName)) $grant[$aclName] = ($doc->controlExtId($doc->id, $aclName, true) == '');
        else $grant[$aclName] = ($doc->controlUp($uperm, $aclName) == '');
    }
    return ($grant);
}
?>
