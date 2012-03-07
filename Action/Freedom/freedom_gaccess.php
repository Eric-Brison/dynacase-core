<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * View/Edit ACLs for a document
 *
 * @author Anakeen 2000
 * @version $Id: freedom_gaccess.php,v 1.16 2008/10/02 12:34:03 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.VGroup.php");
// -----------------------------------
function freedom_gaccess(Action & $action)
{
    // -----------------------------------
    //
    // edition of group accessibilities
    // ---------------------
    // Get all the params
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $docid = GetHttpVars("id");
    $gid = GetHttpVars("gid"); // view user access for the gid group (view all groups if null)
    $green = (GetHttpVars("allgreen") == "Y"); // view only up or un acl
    //
    // edition of group accessibilities
    // ---------------------
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/geometry.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/AnchorPosition.js");
    
    $doc = new_Doc($dbaccess, $docid);
    $err = $doc->control("viewacl");
    if ($err != "") $action->exitError($err);
    
    $acls = $doc->acls;
    $acls[] = "viewacl";
    $acls[] = "modifyacl"; //add this acl global for every document
    // contruct headline
    reset($acls);
    $hacl = array();
    $title = array();
    $width = floor(70 / count($acls));
    $action->lay->set("cellwidth", $width . '%');
    /**
     * @var $v string
     */
    foreach ($acls as $k => $v) {
        $hacl[$k]["aclname"] = ucfirst(_($v));
        $hacl[$k]["acldesc"] = ucfirst(_($doc->dacls[$v]["description"]));
        $hacl[$k]["oddoreven"] = ($k % 2) ? "even" : "odd";
    }
    
    $action->lay->SetBlockData("DACLS", $hacl);
    $action->lay->Set("title", $doc->title);
    $action->lay->Set("stitle", str_replace(array(
        "[",
        "]"
    ) , "", $doc->title));
    $tg = array(); // users or group list
    if ($green) {
        $ouser = new User("", $gid);
        $tusers = $ouser->GetUserAndGroupList("TABLE");
        
        $q = new QueryDb($dbaccess, "DocPerm");
        $q->AddQuery("docid=" . $doc->id);
        $q->AddQuery("upacl != 0");
        $l = $q->Query(0, 0, "TABLE");
        
        $lu = array();
        if ($q->nb > 0) {
            foreach ($l as $lp) {
                $lu[] = $lp["userid"];
            }
            if ($tusers) {
                foreach ($tusers as $k => $v) {
                    if (in_array($v["id"], $lu)) {
                        $title[$v["id"]] = $v["firstname"] . " " . $v["lastname"];
                        $tg[] = array(
                            "level" => 10,
                            "gid" => $v["id"],
                            "displaydyn" => "none",
                            "isdyngroup" => false,
                            "displayuser" => ($v["isgroup"] != "Y") ? "inline" : "none",
                            "displaygroup" => ($v["isgroup"] == "Y") ? "inline" : "none"
                        );
                    }
                }
            }
        }
    } else if ($gid == 0) {
        //-----------------------
        // contruct grouplist
        $ouser = new User();
        $tiduser = $ouser->GetGroupList("TABLE");
        $hg = array();
        $userids = array();
        $sgroup = array(); // all group which are in a group i.e. not the root group
        foreach ($tiduser as $k => $v) {
            $g = new Group("", $v["id"]);
            
            $title[$v["id"]] = $v["firstname"] . " " . $v["lastname"];
            foreach ($g->groups as $kg => $gid) {
                
                $hg[$gid][$v["id"]] = $v["id"];
                $sgroup[$v["id"]] = $v["id"]; // to define root group
                
            }
        }
        //    foreach($hg as $k=>$v) {
        foreach ($tiduser as $k => $v) {
            if (!in_array($v["id"], $sgroup)) {
                // it's a root group
                $tg = array_merge($tg, getTableG($hg, $v["id"]));
            }
        }
        if ($action->user->id > 1) {
            $tg[] = array(
                "level" => 0,
                "gid" => $action->user->id,
                "displayuser" => "inline",
                "displaydyn" => "none",
                "isdyngroup" => false,
                "displaygroup" => "none"
            );
            $title[$action->user->id] = $action->user->firstname . " " . $action->user->lastname;
        }
    } else {
        //-----------------------
        // contruct user list
        $ouser = new User("", $gid);
        $tusers = $ouser->getGroupUserList("TABLE");
        
        $tg[] = array(
            "level" => 0,
            "gid" => $gid,
            "displayuser" => "none",
            "displaydyn" => "none",
            "isdyngroup" => false,
            "displaygroup" => "inline"
        );
        $title[$gid] = $ouser->firstname . " " . $ouser->lastname;
        if ($tusers) {
            foreach ($tusers as $k => $v) {
                
                if ($k > 100) {
                    $action->AddWarningMsg(sprintf(_("Not all users can be vieved.\nlimit %d has been reached") , $k));
                    break;
                }
                $title[$v["id"]] = $v["firstname"] . " " . $v["lastname"];
                $tg[] = array(
                    "level" => 10,
                    "gid" => $v["id"],
                    "displaydyn" => "none",
                    "isdyngroup" => false,
                    "displayuser" => "inline",
                    "displaygroup" => "none"
                );
            }
        }
    }
    // add dynamic group for dynamic profile
    if ($doc->getValue("DPDOC_FAMID") > 0) {
        
        $pdoc = new_Doc($dbaccess, $doc->getValue("DPDOC_FAMID"));
        $pattr = $pdoc->GetProfilAttributes();
        
        foreach ($pattr as $k => $v) {
            $vg = new Vgroup($dbaccess, $v->id);
            if (!$vg->isAffected()) {
                $vg->id = $v->id;
                $vg->Add();
            }
            $tg[] = array(
                "level" => 0,
                "gid" => $vg->num,
                "isdyngroup" => $v->inArray() ,
                "displaydyn" => "inline",
                "displayuser" => "none",
                "displaygroup" => "none"
            );
            $title[$vg->num] = $v->getLabel();
        }
    }
    // add  group title
    foreach ($tg as $k => $v) {
        $tacl[$v["gid"]] = getTacl($dbaccess, $doc->dacls, $acls, $docid, $v["gid"]);
        $tg[$k]["gname"] = $title[$v["gid"]];
        $tg[$k]["ACLS"] = "ACL$k";
        $action->lay->setBlockData("ACL$k", $tacl[$v["gid"]]);
    }
    
    $action->lay->setBlockData("GROUPS", $tg);
    $action->lay->set("docid", $docid);
    
    $action->lay->set("allgreen", getHttpVars("allgreen", "N"));
    $action->lay->set("isgreen", $green);
    $err = $doc->control("modifyacl");
    if ($err == "" && (!$doc->dprofid)) {
        $action->lay->set("MODIFY", true);
        $action->lay->set("dmodify", "");
    } else {
        $action->lay->set("dmodify", "none");
        $action->lay->set("MODIFY", false);
    }
    
    $action->lay->Set("toOrigin", $doc->getDocAnchor($doc->id, 'account', true, false, false, 'latest', true));
    if ($doc->dprofid) {
        $action->lay->Set("dynamic", true);
        $action->lay->Set("dprofid", $doc->dprofid);
        $action->lay->Set("toDynProfil", $doc->getHtmlTitle($doc->dprofid));
    } else {
        $action->lay->Set("dynamic", false);
    }
}
//--------------------------------------------
function getTableG($hg, $id, $level = 0)
{
    //--------------------------------------------
    $r[] = array(
        "gid" => $id,
        "level" => $level * 10,
        "displayuser" => "none",
        "displaydyn" => "none",
        "isdyngroup" => false,
        "displaygroup" => "inline"
    );
    if (isset($hg[$id])) {
        reset($hg[$id]);
        while (list($kg, $gid) = each($hg[$id])) {
            $r = array_merge($r, getTableG($hg, $gid, $level + 1));
        }
    }
    
    return $r;
}
//--------------------------------------------
function getTacl($dbaccess, $dacls, $acls, $docid, $gid)
{
    //--------------------------------------------
    $perm = new DocPerm($dbaccess, array(
        $docid,
        $gid
    ));
    $tableacl = array();
    foreach ($acls as $k => $v) {
        $tableacl[$k]["aclname"] = $v;
        $pos = $dacls[$v]["pos"];
        
        $tableacl[$k]["oddoreven"] = ($k % 2) ? "even" : "odd";
        $tableacl[$k]["aclid"] = $pos;
        $tableacl[$k]["iacl"] = $k; // index for table in xml
        if ($perm->ControlUp($pos)) {
            $tableacl[$k]["selected"] = "checked";
            $tableacl[$k]["bimg"] = "bgreen.png";
        } else {
            $tableacl[$k]["selected"] = "";
            if ($perm->ControlU($pos)) {
                $tableacl[$k]["bimg"] = "bgrey.png";
            }
        }
    }
    
    return $tableacl;
}
?>
