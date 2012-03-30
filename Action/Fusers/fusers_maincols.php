<?php
/*
 * Choose attribute column to display
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

/**
 */
include_once ("FDL/Lib.Dir.php");

function fusers_maincols(Action &$action)
{
    
    global $_GET, $_POST, $ZONE_ARGS;
    $dbaccess = $action->getParam("FREEDOM_DB");
    // Get default visibilty => Abstract view from freedom
    $sfam = GetHttpVars("dfam", $action->getParam("DEFAULT_FAMILY"));
    $action->lay->set("dfam", $sfam);
    $dnfam = new_Doc($dbaccess, $sfam);
    $action->lay->set("dfamname", $dnfam->title);
    
    $reset = GetHttpVars("resetcols", 0);
    
    $ncols = array();
    $prefix = "faddb_cols_";
    if ($reset != 1) {
        foreach ($_POST as $k => $v) {
            if (substr($k, 0, strlen($prefix)) != $prefix) continue;
            $id = substr($k, strlen($prefix));
            $ncols[$sfam][$id] = ($v == "on" ? 1 : 0);
        }
    }
    
    $dfam = createDoc($dbaccess, $sfam, false);
    $fattr = $dfam->GetNormalAttributes();
    $cols = array();
    foreach ($fattr as $k => $v) {
        if ($v->type != "menu" && $v->type != "frame" && $v->type != "array" && $v->visibility != "H" && $v->visibility != "O" && $v->visibility != "I") {
            $cols[$v->id] = array(
                "l" => ($v->isInAbstract == 1 ? 1 : 0) ,
                "order" => $v->ordered,
                "label" => $v->fieldSet->getLabel().'/'.$v->getLabel()
            );
        }
    }
    
    $pc = $action->getParam("FUSERS_MAINCOLS", "");
    if (count($ncols) > 0 || $reset == 1) { // Modified state
        $allcol = array();
        foreach ($cols as $k => $v) {
            if ($reset != 1) $cols[$k]["l"] = 0;
            if (isset($ncols[$sfam][$k])) $cols[$k]["l"] = ($ncols[$sfam][$k] != "" ? $ncols[$sfam][$k] : 0);
            if ($cols[$k]["l"] == 1) $allcol[] = $sfam . "%" . $k;
        }
        //     AddWarningMsg("FUSERS_MAINCOLS = [$scol]");
        if ($pc != "") {
            $tccols = explode("|", $pc);
            foreach ($tccols as $k => $v) {
                if ($v == "") continue;
                $x = explode("%", $v);
                if ($x[0] != $sfam) $allcol[] = $x[0] . "%" . $x[1];
            }
        }
        $scol = implode("|", $allcol);
        if ($action->user->id == 1) $action->parent->param->Set("FUSERS_MAINCOLS", $scol, PARAM_APP, $action->parent->id);
        $action->parent->param->set("FUSERS_MAINCOLS", $scol, PARAM_USER . $action->user->id, $action->parent->id);
    } else { // User initial state
        if ($pc != "") {
            $tccols = explode("|", $pc);
            // reset first
            foreach ($cols as $k => $v) $cols[$k]["l"] = 0;
            
            foreach ($tccols as $k => $v) {
                if ($v == "") continue;
                $x = explode("%", $v);
                if ($x[0] == $sfam && isset($cols[$x[1]])) {
                    $cols[$x[1]]["l"] = 1;
                }
            }
        }
    }
    $vcols=array();
    foreach ($cols as $k => $v) {
        $vcols[] = array(
            "id" => $k,
            "label" => $v["label"],
            "pos" => $v["order"],
            "l_view" => ($v["l"] == 1 ? "checked" : "")
        );
    }
    $action->lay->setBlockData("Columns", $vcols);
}
?>
