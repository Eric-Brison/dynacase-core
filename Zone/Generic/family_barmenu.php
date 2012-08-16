<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Viem bar menu for generic application
 *
 * @author Anakeen
 * @version $Id: generic_barmenu.php,v 1.10 2006/02/03 17:03:41 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("GENERIC/family_defaultmenu.php");
// -----------------------------------
function family_barmenu(Action & $action)
{
    $onefamOrigin = $action->getArgument("onefam");
    $famid = $action->getArgument("famid");
    //$action->parent->addJsRef('lib/jquery-ui/jquery-1.7.1.js');
    $action->parent->addJsRef('lib/jquery/jquery.js');
    
    $action->parent->addJsRef('lib/jquery-ui/src/ui/jquery.ui.core.js');
    $action->parent->addJsRef('lib/jquery-ui/src/ui/jquery.ui.widget.js');
    $action->parent->addJsRef('lib/jquery-ui/src/ui/jquery.ui.position.js');
    $action->parent->addJsRef('lib/jquery-ui/src/ui/jquery.ui.button.js');
    $action->parent->addCssRef('lib/jquery-ui/src/themes/base/jquery.ui.all.css');
    
    $action->parent->addJsRef('lib/jquery-ui/src/ui/jquery.ui.menu.js');
    $action->parent->addJsRef('lib/jquery-ui/src/ui/jquery.ui.menubar.js');
    
    $defaultMenu = family_defaultmenu($action);
    // print_r2($defaultMenu);
    //print_r2($onefamOrigin);
    $menu = getOnefamMenu($onefamOrigin, $famid, $defaultMenu);
    //print_r2($menu);
    $action->lay->set("familyMenu", objectMenu2Html($menu, false));
    $action->lay->set("famid", $action->getArgument("famid"));
}

function getOnefamMenu($onefam, $famid, $defaultMenu)
{
    if (!$onefam) return $defaultMenu;
    $sql = sprintf("SELECT val from paramv where name = 'ONEFAM_MENU' and appid = (select id from application where name='%s')", pg_escape_string($onefam));
    simpleQuery('', $sql, $onefamMenu, true, true);
    if (!$onefamMenu) return $defaultMenu;
    $confOnefam = json_decode($onefamMenu, true);
    $famName = getNameFromId(getDbAccess() , $famid);
    if (!isset($confOnefam["families"][$famName])) return $defaultMenu;
    $specMenu = $confOnefam["families"][$famName];
    if (!$specMenu) return $defaultMenu;
    $standardMenu = $specMenu["standardMenu"];
    if ($standardMenu) {
        // modify standard
        foreach ($standardMenu as $idMenu => $aMenu) {
            if ($defaultMenu[$idMenu]) {
                if ($aMenu["deleted"] == "all") {
                    $defaultMenu[$idMenu]["items"] = array();
                }
                if (is_array($aMenu["deleted"])) {
                    foreach ($aMenu["deleted"] as $kMenu) {
                        unset($defaultMenu[$idMenu]["items"][$kMenu]);
                    }
                }
                if (is_array($aMenu["custom"])) {
                    $defaultMenu[$idMenu]["items"] = array_merge($defaultMenu[$idMenu]["items"], $aMenu["custom"]);
                }
                if (count($defaultMenu[$idMenu]["items"]) == 0) {
                    $defaultMenu[$idMenu] = array();
                }
            } else {
                addWarningMsg(sprintf("custom menu : standard menu '%s' not exists", $idMenu));
            }
        }
    }
    $customMenu = $specMenu["customMenu"];
    if (is_array($customMenu)) {
        foreach ($customMenu as $kc => $cMenu) {
            if (!empty($cMenu["before"])) {
                $tmpMenu = array();
                foreach ($defaultMenu as $kd => $dMenu) {
                    if ($kd == $cMenu["before"]) {
                        $tmpMenu[$kc] = $cMenu;
                    }
                    $tmpMenu[$kd] = $dMenu;
                }
                $defaultMenu = $tmpMenu;
            } else {
                $defaultMenu[$kc] = $cMenu;
            }
        }
        $defaultMenu = array_merge($defaultMenu, $customMenu);
    }
    //print_r2($defaultMenu);
    return $defaultMenu;
}

function objectMenu2Html(array $menulist, $ul = true, $level = 0)
{
    $s = '';
    if ($ul) $s = '<ul>';
    foreach ($menulist as $k => $aMenu) {
        if (!empty($aMenu["items"])) {
            $attrs = '';
            foreach ($aMenu as $ki => $item) {
                if ($ki != "items" && $ki != "label") {
                    $attrs.= sprintf(' %s="%s" ', $ki, $item);
                }
            }
            if ($aMenu["label"]) $label = _($aMenu["label"]);
            else $label = '';
            $s.= sprintf('<li><a href="#%s"%s>%s</a>', $k, $attrs, mb_ucfirst($label));
            
            $s.= objectMenu2Html($aMenu["items"], true, $level + 1);
        } else {
            $noanchor = (empty($aMenu["url"])) && (empty($aMenu["href"]));
            $s.= "\n<li";
            
            $s.= " level=\"$level\" ";
            
            if ($level == 0) {
                if ($aMenu["url"]) $aMenu["href"] = $aMenu["url"];
                $s.= '><div role="button"';
            }
            if (!$noanchor) $s.= "><a";
            foreach ($aMenu as $ki => $item) {
                if ($ki != "label") $s.= sprintf(' %s="%s" ', $ki, $item);
            }
            $s.= '>';
            if ($aMenu["label"]) $s.= mb_ucfirst(_($aMenu["label"]));
            
            if (!$noanchor) $s.= "</a> ";
            if ($level == 0) $s.= '</div>';
        }
        $s.= "</li>\n";
    }
    if ($ul) $s.= "</ul>";
    return $s;
}
?>
