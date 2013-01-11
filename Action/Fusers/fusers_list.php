<?php
/*
 * display users and groups list
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

include_once ("FDL/Lib.Dir.php");
function fusers_list(Action & $action)
{
    
    $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FDL/Layout/common.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/geometry.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/AnchorPosition.js");
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    // create group tree
    $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FDL/Layout/mktree.js");
    
    $action->lay->set("isMaster", $action->parent->Haspermission("FUSERS_MASTER"));
    
    $user = new Account();
    //$ugroup=$user->GetGroupsId();
    $q2 = new queryDb("", "Account");
    $groups = $q2->Query(0, 0, "TABLE", "select users.*, groups.idgroup from users, groups where users.id = groups.iduser  and users.accounttype='G'");
    // top group
    $q2 = new queryDb("", "Account");
    $mgroups = $q2->Query(0, 0, "TABLE", "select users.* from users where accounttype='G' and id not in (select iduser from groups, users u where groups.idgroup = u.id and u.accounttype='G')");
    
    if ($groups) {
        foreach ($groups as $k => $v) {
            $groupuniq[$v["id"]] = $v;
            $groupuniq[$v["id"]]["checkbox"] = "";
            //if (in_array($v["id"],$ugroup)) 	 $groupuniq[$v["id"]]["checkbox"]="checked";
            
        }
    }
    if (!$groups) $groups = array();
    $group = new_doc($action->dbaccess, "IGROUP");
    $groupIcon = $group->getIcon('', 14);
    $action->lay->set("iconGroup", $groupIcon);
    if ($mgroups) {
        $doc = createTmpDoc($dbaccess, 1);
        uasort($mgroups, "cmpgroup");
        foreach ($mgroups as $k => $v) {
            $cgroup = fusers_getChildsGroup($v["id"], $groups, $groupIcon);
            $tgroup[$k] = $v;
            $tgroup[$k]["SUBUL"] = $cgroup;
            $fid = $v["fid"];
            if ($fid) {
                $tdoc = getTDoc($dbaccess, $fid);
                $icon = $doc->getIcon($tdoc["icon"], 14);
                $tgroup[$k]["icon"] = $icon;
            } else {
                $tgroup[$k]["icon"] = $groupIcon;
            }
            $groupuniq[$v["id"]] = $v;
            $groupuniq[$v["id"]]["checkbox"] = "";
            //if (in_array($v["id"],$ugroup)) $groupuniq[$v["id"]]["checkbox"]="checked";
            
        }
    }
    $action->lay->setBlockData("LI", $tgroup);
    $action->lay->setBlockData("SELECTGROUP", $groupuniq);
    
    $action->lay->set("expand", (count($groups) < 30));
    // add button to change categories
    $tcf = array();
    foreach (array(
        "IUSER",
        "IGROUP"
    ) as $fid) {
        
        $fdoc = new_doc($dbaccess, $fid);
        
        $lattr = $fdoc->getNormalAttributes();
        foreach ($lattr as $k => $a) {
            if ((($a->type == "enum") || ($a->type == "enumlist")) && (($a->phpfile == "") || ($a->phpfile == "-")) && ($a->getOption("system") != "yes")) {
                
                $tcf[] = array(
                    "label" => $a->getLabel() ,
                    "famid" => $a->docid,
                    "ftitle" => $fdoc->getTitle($a->docid) ,
                    "kindid" => $a->id
                );
            }
        }
    }
    $action->lay->setBlockData("CATG", $tcf);
    $filter[] = "grp_isrefreshed = '0'";
    $tdoc = internalGetDocCollection($dbaccess, 0, 0, "ALL", $filter, 1, "TABLE", "IGROUP");
    $ngr = count($tdoc);
    if ($ngr > 0) $action->lay->set("textgroup", sprintf(_("<b>%d group(s) to refresh</b>") , $ngr));
    else $action->lay->set("textgroup", sprintf(_("No need to refresh group")));
}
/**
 * internal function use for choosegroup
 * use to compute displayed group tree
 */
function fusers_getChildsGroup($id, $groups, $groupIcon)
{
    static $dbaccess;
    static $doc;
    if (!$dbaccess) $dbaccess = getParam("FREEDOM_DB");
    if (!$doc) $doc = createTmpDoc($dbaccess, 1);
    $tlay = array();
    foreach ($groups as $k => $v) {
        if ($v["idgroup"] == $id) {
            $tlay[$k] = $v;
            $tlay[$k]["SUBUL"] = fusers_getChildsGroup($v["id"], $groups, $groupIcon);
            $fid = $v["fid"];
            if ($fid) {
                $tdoc = getTDoc($dbaccess, $fid);
                $icon = $doc->getIcon($tdoc["icon"], 14);
                $tlay[$k]["icon"] = $icon;
            } else {
                $tlay[$k]["icon"] = $groupIcon;
            }
        }
    }
    
    if (count($tlay) == 0) return "";
    global $action;
    $lay = new Layout("FUSERS/Layout/fusers_ligroup.xml", $action);
    
    uasort($tlay, "cmpgroup");
    $lay->setBlockData("LI", $tlay);
    return $lay->gen();
}
function cmpgroup($a, $b)
{
    return strcasecmp($a['lastname'], $b['lastname']);
}
?>