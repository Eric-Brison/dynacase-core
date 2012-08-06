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
 * @version $Id: tabindex.php,v 1.3 2004/03/22 15:21:40 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage CORE
 */
/**
 */
// ---------------------------------------------------------------
include_once ("Class.Action.php");
// -----------------------------------
function tabindex(Action & $action)
{
    // -----------------------------------
    global $_GET;
    $appname = $_GET["app"];
    $actname = isset($_GET["action"])?$_GET["action"]:'';
    
    $appcalled = new Application();
    $appcalled->Set($appname, $action->parent);
    $actcalled = new Action();
    $actcalled->Set($actname, $appcalled, $action->session);
    
    $query = new QueryDb($action->dbaccess, "Action");
    $query->order_by = "toc_order";
    $query->basic_elem->sup_where = array(
        "toc='Y'",
        "available='Y'",
        "id_application=" . $appcalled->id
    );
    $query->Query();
    $itoc = 0;
    if ($query->nb > 0) {
        while (list($k, $v) = each($query->list)) {
            $v->Set($v->name, $actcalled->parent, $actcalled->session);
            if ($v->HasPermission($v->acl)) {
                $toc[$itoc]["classlabel"] = ($v->name == $actcalled->name ? "TABLabelSelected" : "TABLabel");
                $toc[$itoc]["classcell"] = ($v->name == $actcalled->name ? "TABBackgroundSelected" : "TABBackground");
                $toc[$itoc]["base"] = $action->parent->GetParam("CORE_BASEURL");
                $toc[$itoc]["app"] = $actcalled->parent->name;
                $toc[$itoc]["action"] = $v->name;
                $limg = ($v->name == $actcalled->name ? "tabselected.png" : "tab.png");
                $toc[$itoc]["img"] = $action->parent->GetImageUrl($limg);;
                if (substr($v->short_name, 0, 1) == '&') {
                    $sn = substr($v->short_name, 1, strlen($v->short_name));
                    $toc[$itoc]["label"] = $actcalled->text($sn);
                } else {
                    $toc[$itoc]["label"] = _($v->short_name);
                }
                $itoc++;
            }
        }
    }
    if (isset($toc)) {
        $action->lay->SetBlockCorresp("TAG", "TAG_LABELCLASS", "classlabel");
        $action->lay->SetBlockCorresp("TAG", "TAG_CELLBGCLASS", "classcell");
        $action->lay->SetBlockCorresp("TAG", "TAG_ENTRYURLROOT", "base");
        $action->lay->SetBlockCorresp("TAG", "TAG_ENTRYAPP", "app");
        $action->lay->SetBlockCorresp("TAG", "TAG_ENTRYPAGE", "action");
        $action->lay->SetBlockCorresp("TAG", "TAG_ENTRYIMG", "img");
        $action->lay->SetBlockCorresp("TAG", "TAG_ENTRYLABEL", "label");
        $action->lay->SetBlockData("TAG", $toc);
        $action->lay->SetBlockCorresp("COMPLETE", "TAG_CELLBGCLASS", "classcell");
        $action->lay->SetBlockData("COMPLETE", $toc);
        if ($appcalled->with_frame == "Y") {
            $action->lay->set("TARGET", "main");
        } else {
            $action->lay->set("TARGET", "_self");
        }
        $action->lay->SetBlockData("NOTAG", NULL);
    } else {
        $action->lay->SetBlockData("TAG", NULL);
        $action->lay->SetBlockCorresp("NOTAG", "TAG_NONE", "notag");
        $action->lay->SetBlockData("NOTAG", array(
            array(
                "notag" => " "
            )
        ));
    }
}
?>
