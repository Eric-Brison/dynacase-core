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
 * @version $Id: gate.php,v 1.6 2006/06/01 12:54:33 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage CORE
 */
/**
 */
// ---------------------------------------------------------------
// $Id: gate.php,v 1.6 2006/06/01 12:54:33 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Core/gate.php,v $
// ---------------------------------------------------------------
// -----------------------------------
function gate(&$action)
{
    
    $geo = $action->GetParam("GATE_GEO");
    $url = $action->GetParam("GATE_URL");
    
    $action->lay->set("bw", "30px");
    $turl = explode(",", $url);
    if (count($turl) != 6) {
        for ($i = 0; $i < 6; $i++) $turl[$i] = "";
    }
    // geometry set
    $tgeo = explode(",", $geo);
    if (count($tgeo) != 6) {
        $G1W = ($turl[0] == "") ? "0%" : "*";
        $G2W = ($turl[2] == "") ? "0%" : "*";
        $G3W = ($turl[4] == "") ? "0%" : "*";
        $G1H = ($turl[1] == "") ? "0%" : "*";
        $G2H = ($turl[3] == "") ? "0%" : "*";
        $G3H = ($turl[5] == "") ? "0%" : "*";
    } else {
        list($G1W, $G1H) = explode("x", $tgeo[0]);
        list($G2W, $G2H) = explode("x", $tgeo[2]);
        list($G3W, $G3H) = explode("x", $tgeo[4]);
    }
    if (($turl[0] == "") && ($turl[1] == "")) $G1W = "0%";
    if (($turl[2] == "") && ($turl[3] == "")) $G2W = "0%";
    if (($turl[4] == "") && ($turl[5] == "")) $G3W = "0%";
    if ($turl[0] == "") $G1H = "0%";
    if ($turl[2] == "") $G2H = "0%";
    if ($turl[4] == "") $G3H = "0%";
    
    if ($turl[0] != "") {
        if ($G1H == "0%") $G1H = "10%";
        if ($G1W == "0%") $G1W = "10%";
    }
    if ($turl[1] != "") {
        //if ($G1H == "0%") $G1H="10%";
        if ($G2W == "0%") $G2W = "10%";
    }
    if ($turl[2] != "") {
        if ($G2H == "0%") $G2H = "10%";
        if ($H2W == "0%") $G2W = "10%";
    }
    if ($turl[3] != "") {
        //if ($G2H == "0%") $G2H="10%";
        if ($G2W == "0%") $G2W = "10%";
    }
    if ($turl[4] != "") {
        if ($G3H == "0%") $G3H = "10%";
        if ($G3W == "0%") $G3W = "10%";
    }
    if ($turl[5] != "") {
        //if ($G2H == "0%") $G2H="10%";
        if ($G3W == "0%") $G3W = "10%";
    }
    $action->lay->set("G1W", $G1W);
    $action->lay->set("G2W", $G2W);
    $action->lay->set("G3W", $G3W);
    $action->lay->set("G1H", $G1H);
    $action->lay->set("G2H", $G2H);
    $action->lay->set("G3H", $G3H);
    // url set
    while (list($k, $url) = each($turl)) {
        $turl[$k] = urlWhatEncode($action, $url);
    }
    
    $action->lay->set("urlG11", $turl[0]);
    $action->lay->set("urlG12", $turl[1]);
    $action->lay->set("urlG21", $turl[2]);
    $action->lay->set("urlG22", $turl[3]);
    $action->lay->set("urlG31", $turl[4]);
    $action->lay->set("urlG32", $turl[5]);
    if (($G1W == "0%") && ($G2W == "0%") && ($G3W == "0%")) $action->lay->set("bw", "*");
    $action->session->Close(); // reinit session
    
}

function urlWhatEncode(&$action, $link)
{
    // -----------------------------------
    return str_replace(array(
        "%S%",
        "%B%",
        "%U%",
        "%L%"
    ) , array(
        $action->GetParam("CORE_STANDURL") ,
        $action->GetParam("CORE_BASEURL") ,
        $action->user->id,
        $action->user->login
    ) , $link);
}
?>