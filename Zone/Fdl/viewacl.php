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
        $doc->profid,
        $userid
    ));
    
    $acls = $doc->acls;
    $acls[] = "viewacl";
    $acls[] = "modifyacl"; //add this acl global for every document
    $tableacl = array();
    
    $user = new Account($dbaccess, $userid);
    foreach ($acls as $k => $acl) {
        $tableacl[$k]["aclname"] = mb_ucfirst(_($acl));
        $tableacl[$k]["acldesc"] = " (" . _($doc->dacls[$acl]["description"]) . ")";
        
        $pos = $doc->dacls[$acl]["pos"];
        
        $tableacl[$k]["aclid"] = $acl;
        $tableacl[$k]["iacl"] = $acl; // index for table in xml
        if ($doc->extendedAcls[$acl]) {
            $grant = DocPermExt::hasExtAclGrant($docid, $user->id, $acl);
            if ($grant == 'green') {
                
                $tableacl[$k]["selectedup"] = "checked";
                $tableacl[$k]["imgacl"] = "bgreen.png";
            } else {
                $tableacl[$k]["selectedup"] = "";
                if ($grant == 'grey') {
                    $tableacl[$k]["imgacl"] = "bgrey.png";
                } else {
                    $tableacl[$k]["imgacl"] = "bred.png";
                }
            }
        } elseif ($perm->ControlUp($pos)) {
            
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
        $tableacl[$k]["aclcause"] = getAclCause($acl, $doc, $perm, $user);
    }
    $action->lay->set("readonly", ($doc->control("modifyacl") != '' || $doc->dprofid || $doc->profid != $doc->id));
    $action->lay->SetBlockData("SELECTACL", $tableacl);
    $action->lay->set("updateWaitText", sprintf(_("Update profiling is in progress.")));
}

function getAclCause($acl, Doc & $doc, DocPerm & $perm, Account & $user)
{
    $Aclpos = $doc->dacls[$acl]["pos"];
    $msg = '?';
    if ($perm->ControlUp($Aclpos) || DocPermExt::hasExtAclGrant($doc->id, $user->id, $acl) == 'green') {
        if (!$doc->dprofid) {
            // direct green
            if ($doc->profid == $doc->id) {
                $msg = sprintf(_("Direct set through document itself \"%s\"") , $doc->getHtmlTitle());
            } else {
                // linked  green
                $msg = sprintf(_("Set through \"%s\" linked profil") , $doc->getHtmlTitle($doc->profid));
            }
        } else {
            // Dynamic profiling
            $dperm = new DocPerm($perm->dbaccess, array(
                $doc->dprofid,
                $perm->userid
            ));
            
            $tAtt = array();
            if ($dperm->isAffected()) {
                if ($dperm->ControlUp($Aclpos)) {
                    $tAtt[] = sprintf(_("explicit privilege"));
                    $msg = sprintf(_("Set from template profil \"%s\"") , $doc->getHtmlTitle($doc->dprofid));
                } else {
                    $msg = sprintf(_("Something wrong. No acl found in %s (user #%d)") , $doc->getHtmlTitle($doc->dprofid) , $perm->userid);
                }
            }
            // search in dynamic
            $sql = sprintf('select vgroup.id as aid from docperm,vgroup where docid=%d and userid >= %d and upacl & %d != 0 and docperm.userid=vgroup.num', $doc->dprofid, STARTIDVGROUP, 1 << $Aclpos);
            simpleQuery($perm->dbaccess, $sql, $dynAids, true);
            foreach ($dynAids as $aid) {
                $va = $doc->getValue($aid);
                if ($va) {
                    $tva = explode("\n", str_replace('<BR>', "\n", $va));
                    if (in_array($user->fid, $tva)) {
                        $oa = $doc->getAttribute($aid);
                        if ($oa) $alabel = $oa->getLabel();
                        else $alabel = $aid;
                        $tAtt[] = sprintf(_("the attribute %s") , $alabel);
                    }
                }
            }
            if (count($tAtt) > 0) {
                $sAtt = '<ul><li>' . implode('</li><li>', $tAtt) . '</li></ul>';
                $msg = sprintf(_("Set by %s from template profil \"%s\"") , $sAtt, $doc->getHtmlTitle($doc->dprofid));
            }
        }
    } else if ($perm->ControlU($Aclpos) || DocPermExt::hasExtAclGrant($doc->id, $user->id, $acl) == 'grey') {
        $msg = '? role/group';
        if (!$doc->dprofid) {
            // grey
            $msg = '? profid role/group';
            if ($doc->extendedAcls[$acl]) {
                $sql = sprintf("SELECT userid from docpermext where docid=%d and acl = '%s'", $doc->profid, pg_escape_string($acl));
            } else {
                $sql = sprintf("SELECT userid from docperm where docid=%d and upacl & %d != 0", $doc->profid, 1 << $Aclpos);
            }
            simpleQuery($perm->dbaccess, $sql, $gids, true);
            $mo = $user->getMemberOf();
            
            $asIds = array_intersect($gids, $mo);
            $sFrom = "?";
            if (count($asIds) > 0) {
                $sql = sprintf("select fid, accounttype, lastname, login from users where %s", GetSqlCond($asIds, "id", true));
                simpleQuery($perm->dbaccess, $sql, $uas);
                
                $tFrom = array();
                foreach ($uas as $as) {
                    if ($as["accounttype"] == 'R') {
                        $tFrom[] = sprintf(_("Role \"%s\"") , $as["lastname"]);
                    } else {
                        $tFrom[] = sprintf(_("Group \"%s\"") , $as["lastname"]);
                    }
                }
                if (count($tFrom) > 0) {
                    $sFrom = '<ul><li>' . implode('</li><li>', $tFrom) . '</li></ul>';
                } else {
                    $sFrom = implode(', ', $tFrom);
                }
            }
            if ($doc->profid == $doc->id) {
                $msg = sprintf(_("Set by %s through document itself \"%s\"") , $sFrom, $doc->getHtmlTitle());
            } else {
                $msg = sprintf(_("Set by %s through \"%s\" linked profil") , $sFrom, $doc->getHtmlTitle($doc->profid));
            }
        } else {
            $msg = '? dprofid role/group';
            
            $sql = sprintf("SELECT userid from docperm where docid=%d and upacl & %d != 0", $doc->dprofid, 1 << $Aclpos);
            simpleQuery($perm->dbaccess, $sql, $gids, true);
            $mo = $user->getMemberOf();
            
            $asIds = array_intersect($gids, $mo);
            $sFrom = "?";
            if (count($asIds) > 0) {
                $sql = sprintf("select fid, accounttype, lastname, login from users where %s", GetSqlCond($asIds, "id", true));
                simpleQuery($perm->dbaccess, $sql, $uas);
                
                $tFrom = array();
                foreach ($uas as $as) {
                    if ($as["accounttype"] == 'R') {
                        $tFrom[] = sprintf(_("Role \"%s\"") , $as["lastname"]);
                    } else {
                        $tFrom[] = sprintf(_("Group \"%s\"") , $as["lastname"]);
                    }
                }
                if (count($tFrom) > 0) {
                    $sFrom = '<ul><li>' . implode('</li><li>', $tFrom) . '</li></ul>';
                } else {
                    $sFrom = implode(', ', $tFrom);
                }
                $msg = sprintf(_("Set by %s through template profil \"%s\"") , $sFrom, $doc->getHtmlTitle($doc->dprofid));
            } else {
                $msg = sprintf(_("Set by %s through template profil \"%s\"") , $sFrom, $doc->getHtmlTitle($doc->dprofid));
                // search in dynamic
                $sql = sprintf('select vgroup.id as aid from docperm,vgroup where docid=%d and userid >= %d and upacl & %d != 0 and docperm.userid=vgroup.num', $doc->dprofid, STARTIDVGROUP, 1 << $Aclpos);
                simpleQuery($perm->dbaccess, $sql, $dynAids, true);
                $mo = $user->getMemberOf(false);
                foreach ($dynAids as $aid) {
                    $va = $doc->getValue($aid);
                    if ($va) {
                        $tva = explode("\n", str_replace('<BR>', "\n", $va));
                        $as = array_intersect($tva, $mo);
                        if (count($as) > 0) {
                            $oa = $doc->getAttribute($aid);
                            if ($oa) $alabel = $oa->getLabel();
                            else $alabel = $aid;
                            $gv = array();
                            foreach ($as as $gid) {
                                $gv[] = $doc->getHtmlTitle($gid);
                            }
                            
                            $msg = sprintf(_("Set by \"%s\" attribute (%s) from template profil \"%s\"") , $alabel, implode(', ', $gv) , $doc->getHtmlTitle($doc->dprofid));
                        }
                    }
                }
            }
        }
    } else {
        $msg = '';
    }
    return $msg;
}
?>