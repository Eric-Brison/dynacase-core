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
 * @version $Id: user_table.php,v 1.9 2005/07/08 15:29:51 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage USERS
 */
/**
 */
// ---------------------------------------------------------------
// $Id: user_table.php,v 1.9 2005/07/08 15:29:51 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/core/Action/Users/user_table.php,v $
// ---------------------------------------------------------------
include_once ("Class.TableLayout.php");
include_once ("Class.QueryDb.php");
include_once ("Class.SubForm.php");
include_once ("Class.MailAccount.php");
include_once ("Class.Domain.php");
include_once ("Class.SubForm.php");
include_once ("Class.QueryGen.php");
// -----------------------------------
function user_table(&$action, $group = false)
{
    // -----------------------------------
    // Set the globals elements
    $baseurl = $action->GetParam("CORE_BASEURL");
    $standurl = $action->GetParam("CORE_STANDURL");
    
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    // Set the search elements
    $query = new QueryGen($action->GetParam("CORE_DB") , "User", $action);
    $query->slice = 20;
    $action->lay->set("slice9", $query->slice + 9);
    if ($group) {
        $query->AddQuery("isgroup = 'Y'");
        $action->lay->set("title", $action->text("titlegroup"));
        $action->lay->set("createuser", $action->text("titlecreateg"));
        $action->lay->set("duser", "none");
        $action->lay->set("yngroup", "Y");
    } else {
        $query->AddQuery("(isgroup != 'Y') OR (isgroup isnull)");
        $action->lay->set("title", $action->text("titleuser"));
        $action->lay->set("createuser", $action->text("titlecreateu"));
        $action->lay->set("duser", "");
        $action->lay->set("yngroup", "N");
    }
    
    $action->lay->set("fhelp", ($action->Read("navigator", "") == "EXPLORER") ? "_blank" : "fhidden");
    // The content depends on Access Permission
    if (!isset($action->user)) {
        $action->log->info("Attempt to use {$action->parent->name}/{$action->name} without permission");
        Redirect($action, "CORE", "");
    }
    if (!$action->HasPermission("ADMIN")) {
        if ($action->HasPermission("DOMAIN_MASTER")) {
            $query->AddQuery("iddomain={$action->user->iddomain}");
        } elseif ($action->HasPermission("USER")) {
            $query->AddQuery("id={$action->user->id}");
        }
    }
    // Give some global elements for the table layout
    $query->table->fields = array(
        "domain",
        "id",
        "edit",
        "lastname",
        "delete",
        "fullname",
        "login",
        "group",
        "expires"
    );
    
    $query->table->headsortfields = array(
        "head_lastname" => "lastname",
        "head_login" => "login",
        "head_expires" => "expires"
    );
    if ($group) {
        $query->table->headcontent = array(
            "head_lastname" => $action->text("groupdesc") ,
            "head_domain" => $action->text("domain") ,
            "head_login" => $action->text("group")
        );
    } else {
        $query->table->headcontent = array(
            "head_lastname" => $action->text("fullname") ,
            "head_domain" => $action->text("domain") ,
            "head_login" => $action->text("login") ,
            "head_expires" => $action->text("expires")
        );
    }
    // Perform the query
    $query->Query();
    // Affect the modif icons and the fullname field
    //  $jsscript=$form->GetLinkJsMainCall();
    reset($query->table->array);
    while (list($k, $v) = each($query->table->array)) {
        $query->table->array[$k]["group"] = $isgroup;
        
        if (!$group) {
            $query->table->array[$k]["fullname"] = ucfirst((isset($query->table->array[$k]["firstname"]) ? $query->table->array[$k]["firstname"] : "(?)")) . " " . ucfirst((isset($query->table->array[$k]["lastname"]) ? $query->table->array[$k]["lastname"] : "(?)"));
            
            $query->table->array[$k]["expires"] = intval($v["expires"]) == 0 ? "" : strftime("%d/%m/%Y %X", intval($v["expires"]));
        } else {
            $query->table->array[$k]["fullname"] = ucfirst(isset($query->table->array[$k]["lastname"]) ? $query->table->array[$k]["lastname"] : "(?)");
        }
        // $query->table->array[$k]["edit"] = str_replace("[id]",$v["id"],$jsscript);
        if (($query->table->array[$k]["id"] != 1) && ($query->table->array[$k]["lastname"] != "Postmaster") && ($query->table->array[$k]["login"] != "all") && ($action->HasPermission("DOMAIN_MASTER"))) {
            $query->table->array[$k]["delete"] = $action->GetIcon("delete.gif", "delete");
        }
        if ($v["iddomain"] == 1) {
            $query->table->array[$k]["domain"] = $action->text("nomail");
        } else {
            $dom = new Domain($action->GetParam("CORE_DB") , $v["iddomain"]);
            $query->table->array[$k]["domain"] = $dom->name;
        }
    }
    // Out
    $action->lay->Set("TABLE", $query->table->Set());
}
?>
