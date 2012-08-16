<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Function utilities to manipulate users
 *
 * @author Anakeen
 * @version $Id: Lib.Usercard.php,v 1.5 2007/02/16 07:35:54 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage USERCARD
 */
/**
 */
include_once ("Class.Group.php");
include_once ("FDL/Class.Dir.php");
/**
 * refresh a set of group
 * @param array $tg the groups which has been modify by insertion/deletion of user
 * @return
 */

function refreshGroups($groupIdList, $refresh = false, &$currentPath = array() , &$groupDepth = array())
{
    /**
     * @var Group $wg
     */
    static $wg = null;
    
    if (!$wg) $wg = new group("", 2); // working group;
    // Iterate over given groups list
    foreach ($groupIdList as $groupId) {
        // Detect loops in groups
        if (array_search($groupId, $currentPath)) {
            error_log(__CLASS__ . "::" . __FUNCTION__ . " " . sprintf("Loop detected in group with id '%s' (path=[%s])", $groupId, join('-', $currentPath)));
            continue;
        }
        // Get direct parent groups list
        $parentGroupIdList = $wg->getParentsGroupId($groupId);
        // Compute depth of current group and recursively compute depth on parent groups
        array_push($currentPath, $groupId);
        if (isset($groupDepth[$groupId])) $groupDepth[$groupId] = max($groupDepth[$groupId], count($currentPath));
        else $groupDepth[$groupId] = count($currentPath);
        refreshGroups($parentGroupIdList, $refresh, $currentPath, $groupDepth);
        array_pop($currentPath);
    }
    // End of groups traversal
    if (count($currentPath) <= 0) {
        // We can now refresh the groups based on their ascending depth
        uasort($groupDepth, create_function('$a,$b', 'return ($a-$b);'));
        foreach ($groupDepth as $group => $depth) {
            refreshOneGroup($group, $refresh);
        }
    }
    
    return $groupIdList;
}

function array_unset(&$t, $vp)
{
    foreach ($t as $k => $v) {
        if ($v == $vp) unset($t[$k]);
    }
}

function refreshOneGroup($gid, $refresh)
{
    global $_SERVER;
    $g = new Account("", $gid);
    if ($g->fid > 0 && $g->accounttype == 'G') {
        $dbaccess = GetParam("FREEDOM_DB");
        /**
         * @var _IGROUP $doc
         */
        $doc = new_Doc($dbaccess, $g->fid);
        if ($doc->isAlive()) {
            //if ($_SERVER['HTTP_HOST'] == "") error_log(sprintf("\trefreshing %s\n", $doc->title));
            wbartext(sprintf(_("refreshing %s") , $doc->title));
            if ($refresh) $doc->refreshMembers();
            $doc->SetGroupMail();
            $doc->modify();
            $doc->specPostInsert();
            $doc->setValue("grp_isrefreshed", "1");
            $doc->modify(true, array(
                "grp_isrefreshed"
            ) , true);
        }
    }
}
?>