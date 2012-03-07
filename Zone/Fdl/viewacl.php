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
 * @version $Id: viewacl.php,v 1.5 2007/03/12 17:38:41 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */
// ---------------------------------------------------------------
// $Id: viewacl.php,v 1.5 2007/03/12 17:38:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Fdl/viewacl.php,v $
// ---------------------------------------------------------------
// ---------------------------------------------------------------
include_once ("FDL/Class.Doc.php");
// -----------------------------------
function viewacl(Action & $action)
{
    // ------------------------
    $docid = intval($action->getArgument("docid"));
    $userid = intval($action->getArgument("userid"));
    
    $action->lay->Set("docid", $docid);
    $action->lay->Set("userid", $userid);
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $doc = new_Doc($dbaccess, $docid);
    $err = $doc->control('viewacl');
    if ($err) $action->exitError($err);
    //-------------------
    $perm = new DocPerm($dbaccess, array(
        $docid,
        $userid
    ));
    
    $acls = $doc->acls;
    $acls[] = "viewacl";
    $acls[] = "modifyacl"; //add this acl global for every document
    $tableacl = array();
    
    reset($acls);
    while (list($k, $v) = each($acls)) {
        $tableacl[$k]["aclname"] = mb_ucfirst(_($v));
        $tableacl[$k]["acldesc"] = " (" . _($doc->dacls[$v]["description"]) . ")";
        
        $pos = $doc->dacls[$v]["pos"];
        
        $tableacl[$k]["aclid"] = $pos;
        $tableacl[$k]["iacl"] = $k; // index for table in xml
        if ($perm->ControlUp($pos)) {
            
            $tableacl[$k]["selectedup"] = "checked";
            $tableacl[$k]["imgacl"] = "bgreen.png";
        } else {
            $tableacl[$k]["selectedup"] = "";
            if ($perm->ControlU($pos)) {
                $tableacl[$k]["imgacl"] = "bgrey.png";
            } else {
                $tableacl[$k]["imgacl"] = "bred.png";
            }
        }
    }
    $action->lay->set("readonly", ($doc->control("modifyacl") != '' || $doc->dprofid));
    $action->lay->SetBlockData("SELECTACL", $tableacl);
}
?>