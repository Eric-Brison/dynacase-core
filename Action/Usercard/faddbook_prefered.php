<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Display Prefered personns
 *
 * @author Anakeen 2005
 * @version $Id: faddbook_prefered.php,v 1.7 2005/11/24 13:48:17 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage USERCARD
 */
/**
 */
include_once ("FDL/freedom_util.php");
include_once ("FDL/Class.Doc.php");

function faddbook_prefered(&$action)
{
    
    $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FDL/Layout/common.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    $dbaccess = $action->getParam("FREEDOM_DB");
    
    $cpref = $action->getParam("FADDBOOK_PREFERED", "");
    $tc = explode("|", $cpref);
    //   $sfam = $action->getParam("DEFAULT_FAMILY");
    //   $fam = new_Doc($dbaccess, $sfam);
    //   $action->lay->set("icon", $fam->getIcon());
    $cu = array();
    foreach ($tc as $k => $v) {
        if ($v == "") continue;
        $cc = new_Doc($dbaccess, $v);
        $cu[] = array(
            "id" => $cc->id,
            "resume" => $cc->viewDoc((isset($cc->faddbook_resume) ? $cc->faddbook_resume : "FDL:VIEWTHUMBCARD")) ,
            "icon" => $cc->getIcon() ,
            "title" => mb_convert_case(mb_strtolower($cc->title) , MB_CASE_TITLE) ,
            "fabzone" => (isset($cc->faddbook_card) ? $cc->faddbook_card : $cc->defaultview) ,
            "jstitle" => addslashes(mb_convert_case(mb_strtolower($cc->title) , MB_CASE_TITLE))
        );
    }
    usort($cu, "sortmya");
    $action->lay->setBlockData("Contacts", $cu);
}
function sortmya($a, $b)
{
    return strcmp($a["title"], $b["title"]);
}
?>
