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
function freedom_modaccess(&$action)
{
    // -----------------------------------
    global $_SERVER;
    // get all parameters
    $acls = GetHttpVars("acls", array());
    $docid = GetHttpVars("docid"); // id for controlled object
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
            
            $before[$userid] = array(
                $perm->upacl,
                $perm->unacl
            );
            $perm->UnsetControl();
            
            foreach ($aclon as $k => $pos) {
                if (intval($pos) > 0) $perm->SetControlP($pos);
            }
            if ($perm->isAffected()) $err = $perm->modify();
            else $err = $perm->Add();
            if ($err != "") {
                if ($perm->isAffected()) $err = $perm->delete();
            }
            $after[$userid] = array(
                $perm->upacl,
                $perm->unacl
            );
        }
        if ($err != "") $action->exitError($err);
        // recompute all related profile
        $doc->recomputeProfiledDocument();
        // find username
        $tuid = array();
        foreach ($acls as $userid => $aclon) {
            $tuid[] = $userid;
        }
        $q = new QueryDb("", "User");
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
        
        foreach ($before as $k => $v) {
            $a = $after[$k][0];
            $b = $before[$k][0];
            if ($b != $a) {
                $tadd = array();
                $tdel = array();
                foreach ($doc->acls as $acl) {
                    $pos = $posacls[$acl];
                    
                    $a0 = ($a & (1 << $pos));
                    $b0 = ($b & (1 << $pos));
                    if ($a0 != $b0) {
                        if ($a0) $tadd[] = $acl;
                        else $tdel[] = $acl;
                    }
                }
                
                if (count($tadd) > 0) $tc[] = sprintf(_("Add acl %s for %s") , implode(", ", $tadd) , $tuname[$k]);
                if (count($tdel) > 0) $tc[] = sprintf(_("Delete acl %s for %s") , implode(", ", $tdel) , $tuname[$k]);
            }
        }
        if (count($tc) > 0) $doc->addComment(sprintf(_("Change control :\n %s") , implode("\n", $tc)));
    }
    RedirectSender($action); // return to sender
    
}
?>
