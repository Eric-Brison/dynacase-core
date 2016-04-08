<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * View/Edit ACLs for a document
 *
 * @author Anakeen
 * @version $Id: freedom_gaccess.php,v 1.16 2008/10/02 12:34:03 eric Exp $
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
    $dbaccess = $action->dbaccess;
    $usage = new ActionUsage($action);
    $usage->setStrictMode(false);
    $usage->setDefinitionText("view or modify document accessibilities");
    $docid = $usage->addRequiredParameter("id", "document identifier to profil");
    $gid = $usage->addOptionalParameter("gid", "group identificator, view user access for this group");
    $green = ($usage->addOptionalParameter("allgreen", "view only up acl", array(
        "Y",
        "N"
    ) , "N") == "Y");
    $viewgroup = ($usage->addOptionalParameter("group", "view group", array(
        "Y",
        "N"
    ) , "N") == "Y");
    $limit = $usage->addOptionalParameter("memberLimit", "when gid option is set, limit members to display", array() , 100);
    $usage->verify();
    // edition of group accessibilities
    // ---------------------
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/geometry.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/AnchorPosition.js");
    $action->parent->addJsRef("lib/jquery/jquery.js");
    $action->parent->addCssRef("css/dcp/jquery-ui.css");
    $doc = new_Doc($dbaccess, $docid);
    $err = $doc->control("viewacl");
    if ($err != "") $action->exitError($err);
    
    $acls = $doc->acls;
    $acls[] = "viewacl";
    $acls[] = "modifyacl"; //add this acl global for every document
    // $acls=array_merge($acls, $doc->extendedAcls);
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
        $hacl[$k]["aclname"] = mb_ucfirst(_($v));
        $desc = isset($doc->dacls[$v]) ? $doc->dacls[$v]["description"] : "";
        if (!$desc) {
            $desc = $doc->extendedAcls[$v]["description"];
        } else {
            $desc = _($desc);
        }
        $hacl[$k]["acldesc"] = mb_ucfirst($desc);
        $hacl[$k]["oddoreven"] = ($k % 2) ? "even" : "odd";
    }
    
    $action->lay->SetBlockData("DACLS", $hacl);
    $action->lay->Set("title", $doc->title);
    $action->lay->Set("hasgid", ($gid > 0));
    $action->lay->Set("stitle", str_replace(array(
        "[",
        "]"
    ) , "", $doc->title));
    $tg = array(); // users or group list
    if ($green) {
        
        $sql = sprintf("SELECT users.* from docperm,users where docperm.docid=%d and users.id=docperm.userid and docperm.upacl != 0 order by users.lastname", $doc->profid);
        simpleQuery($dbaccess, $sql, $tusers);
        $tgreenUid = array();
        foreach ($tusers as $k => $v) {
            $tgreenUid[] = $v["id"];
            $title[$v["id"]] = $v["firstname"] . " " . $v["lastname"];
            $tg[] = array(
                "level" => 10,
                "gid" => $v["id"],
                "isdyn" => false,
                "accountType" => $v["accounttype"],
                "displaygroup" => ($v["accounttype"] != "U") ? "inline" : "none"
            );
        }
        
        if ($doc->extendedAcls) {
            // add more users
            $sql = sprintf("select users.id, users.firstname, users.lastname,users.accounttype, array_agg(docpermext.acl) as acls from docpermext,users where  users.id=docpermext.userid and docpermext.docid=%d", $doc->profid);
            if (!empty($tgreenUid)) $sql.= sprintf(" and id not in (%s)", implode(',', $tgreenUid));
            $sql.= " group by users.id, users.firstname, users.lastname, users.accounttype ;";
            simpleQuery($dbaccess, $sql, $tusers);
            //print_r($sql);
            //print_r($tusers);
            foreach ($tusers as $k => $v) {
                
                $title[$v["id"]] = $v["firstname"] . " " . $v["lastname"];
                $tg[] = array(
                    "level" => 10,
                    "gid" => $v["id"],
                    "isdyn" => false,
                    "extacl" => $v["acls"],
                    "accountType" => $v["accounttype"],
                    "displaygroup" => ($v["accounttype"] != "U") ? "inline" : "none"
                );
            }
        }
    } else if ($gid == 0) {
        //-----------------------
        // contruct grouplist
        $ouser = new Account();
        if ($viewgroup) {
            $tidAccount = array_merge($ouser->getGroupList("TABLE") , $ouser->getRoleList("TABLE"));
        } else {
            $tidAccount = $ouser->getRoleList("TABLE");
        }
        $hg = array();
        $userids = array();
        $sgroup = array(); // all group which are in a group i.e. not the root group
        foreach ($tidAccount as $k => $v) {
            $g = new Group("", $v["id"]);
            
            $title[$v["id"]] = $v["firstname"] . " " . $v["lastname"];
            foreach ($g->groups as $kg => $gid) {
                
                $hg[$gid][$v["id"]] = $v;
                $sgroup[$v["id"]] = $v["id"]; // to define root group
                
            }
        }
        //    foreach($hg as $k=>$v) {
        foreach ($tidAccount as $k => $v) {
            if (!in_array($v["id"], $sgroup)) {
                // it's a root group
                $tg = array_merge($tg, getTableG($hg, $v["id"], $v["accounttype"]));
            }
        }
        if ($action->user->id > 1) {
            $tg[] = array(
                "level" => 0,
                "gid" => $action->user->id,
                "isdyn" => false,
                "accountType" => "U",
                "displaygroup" => "none"
            );
            $title[$action->user->id] = $action->user->firstname . " " . $action->user->lastname;
        }
    } else {
        //-----------------------
        // contruct user list
        $ouser = new Account("", $gid);
        if ($ouser->accounttype == 'G') {
            $tusers = $ouser->getGroupUserList("TABLE", false, $limit);
        } else {
            $tusers = $ouser->getAllMembers($limit, false);
        }
        if (count($tusers) == $limit) $action->AddWarningMsg(sprintf(_("limit reached, only %d members has been displayed") , $limit));
        
        $tg[] = array(
            "level" => 0,
            "gid" => $gid,
            "isdyn" => false,
            "accountType" => $ouser->accounttype,
            "displaygroup" => "none"
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
                    "isdyn" => false,
                    "accountType" => $v["accounttype"],
                    "displaygroup" => "none"
                );
            }
        }
    }
    // add dynamic group for dynamic profile
    if ($doc->getRawValue("DPDOC_FAMID") > 0) {
        
        $pdoc = new_Doc($dbaccess, $doc->getRawValue("DPDOC_FAMID"));
        $pattr = $pdoc->GetProfilAttributes();
        /**
         * @var NormalAttribute $v
         */
        foreach ($pattr as $k => $v) {
            $vg = new Vgroup($dbaccess, $v->id);
            if (!$vg->isAffected()) {
                $vg->id = $v->id;
                $vg->Add();
            }
            $tg[] = array(
                "level" => 0,
                "gid" => $vg->num,
                "isdyn" => true,
                "accountType" => $v->isMultiple() ? "M" : "D",
                "displaygroup" => "none"
            );
            $title[$vg->num] = $v->getLabel();
        }
    }
    //print_r2($tg);
    // add  group title
    foreach ($tg as $k => $v) {
        $tacl[$v["gid"]] = getTacl($dbaccess, $doc->dacls, $acls, $doc->profid, $v["gid"]);
        $tg[$k]["gname"] = $title[$v["gid"]];
        $tg[$k]["ACLS"] = "ACL$k";
        $action->lay->setBlockData("ACL$k", $tacl[$v["gid"]]);
    }
    // print_r2($tacl);
    $action->lay->setBlockData("GROUPS", $tg);
    $action->lay->set("docid", $doc->id);
    
    $action->lay->eset("allgreen", $action->getArgument("allgreen", "N"));
    $action->lay->set("viewgroup", (bool)$viewgroup);
    $action->lay->eset("group", $action->getArgument("group", "N"));
    $action->lay->set("isgreen", (bool)$green);
    $err = $doc->control("modifyacl");
    $action->lay->set("profcount", "");
    $action->lay->set("cellWidth", "65");
    if (count($acls) > 15) $action->lay->set("cellWidth", "50");
    
    $action->lay->set("updateWaitText", sprintf(_("Update profiling is in progress.")));
    if ($err == "" && (!$doc->dprofid) && ($doc->profid == $doc->id)) {
        $action->lay->set("MODIFY", true);
        $action->lay->set("dmodify", "");
        if ($doc->isRealProfile()) {
            if ($doc->getRawValue("dpdoc_famid")) {
                
                simpleQuery($dbaccess, sprintf("select count(id) from docread where dprofid=%d", $doc->id) , $cont, true, true);
            } else {
                simpleQuery($dbaccess, sprintf("select count(id) from docread where profid=%d", $doc->id) , $cont, true, true);
                $cont--;
            }
            if ($cont > 0) {
                if ($cont > 1) $action->lay->set("profcount", sprintf(_("%d documents linked to the profil") , $cont));
                else $action->lay->set("profcount", _("only one document linked to the profil"));
                $action->lay->set("updateWaitText", sprintf(_("Update profiling of %d documents is in progress.") , $cont));
            }
        }
    } else {
        $action->lay->set("dmodify", "none");
        $action->lay->set("MODIFY", false);
    }
    
    $action->lay->Set("toOrigin", $doc->getDocAnchor($doc->id, 'account', true, false, false, 'latest', true));
    
    if ($doc->dprofid) {
        $action->lay->Set("dynamic", true);
        $action->lay->Set("dprofid", $doc->dprofid);
        $action->lay->Set("toDynProfil", $doc->getHtmlTitle($doc->dprofid));
        $action->lay->Set("ComputedFrom", _("Computed from profil"));
    } elseif ($doc->profid != $doc->id) {
        
        $action->lay->Set("dynamic", true);
        $action->lay->Set("dprofid", $doc->profid);
        $action->lay->Set("toDynProfil", $doc->getHtmlTitle($doc->profid));
        $action->lay->Set("ComputedFrom", _("Linked from profil"));
    } else {
        $action->lay->Set("dynamic", false);
    }
    $action->lay->setBlockData("legendcolor", array(
        array(
            "legendimage" => "G",
            "legendexplication" => _("Legend:Groups")
        ) ,
        array(
            "legendimage" => "U",
            "legendexplication" => _("Legend:Users")
        ) ,
        array(
            "legendimage" => "R",
            "legendexplication" => _("Legend:Roles")
        ) ,
        array(
            "legendimage" => "D",
            "legendexplication" => _("Legend:Dynamic")
        ) ,
        array(
            "legendimage" => "M",
            "legendexplication" => _("Legend:Dynamic multiple")
        )
    ));
}
//--------------------------------------------
function getTableG($hg, $id, $type, $level = 0)
{
    //--------------------------------------------
    $r[] = array(
        "gid" => $id,
        "level" => $level * 10,
        "isdyn" => false,
        "accountType" => $type,
        "displaygroup" => "inline"
    );
    
    if (isset($hg[$id])) {
        foreach ($hg[$id] as $kg => $account) {
            $r = array_merge($r, getTableG($hg, $kg, $account["accounttype"], $level + 1));
        }
    }
    
    return $r;
}
//--------------------------------------------
function getTacl($dbaccess, $dacls, $acls, $docid, $gid, $extAcl = '')
{
    //--------------------------------------------
    $perm = new DocPerm($dbaccess, array(
        $docid,
        $gid
    ));
    $tableacl = array();
    foreach ($acls as $k => $v) {
        $tableacl[$k]["aclname"] = $v;
        $pos = 0;
        if (!$extAcl && isset($dacls[$v])) $pos = $dacls[$v]["pos"];
        $tableacl[$k]["selected"] = "";
        $tableacl[$k]["bimg"] = "1x1.gif";
        $tableacl[$k]["oddoreven"] = ($k % 2) ? "even" : "odd";
        $tableacl[$k]["aclid"] = $v;
        $tableacl[$k]["iacl"] = $v; // index for table in xml
        if (!$pos) {
            $tableacl[$k]["aclname"] = $extAcl;
            $grant = DocPermExt::hasExtAclGrant($docid, $gid, $v);
            if ($grant) {
                if ($grant == 'green') {
                    $tableacl[$k]["bimg"] = "bgreen.png";
                    $tableacl[$k]["selected"] = "checked";
                } else {
                    $tableacl[$k]["bimg"] = "bgrey.png";
                }
            }
        } elseif ($perm->ControlUp($pos)) {
            $tableacl[$k]["selected"] = "checked";
            $tableacl[$k]["bimg"] = "bgreen.png";
        } else {
            if ($perm->ControlU($pos)) {
                $tableacl[$k]["bimg"] = "bgrey.png";
            }
        }
    }
    //print_r2($tableacl);
    return $tableacl;
}
