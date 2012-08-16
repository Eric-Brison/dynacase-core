<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Specific menu for family
 *
 * @author Anakeen
 * @version $Id: popuplistdetail.php,v 1.2 2007/09/11 07:31:22 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/popupdoc.php");
include_once ("FDL/popupdocdetail.php");

function popuplistdetail(&$action)
{
    $docid = GetHttpVars("id");
    if ($docid == "") $action->exitError(_("No identificator"));
    
    $zone = GetHttpVars("zone"); // special zone
    $famid = GetHttpVars("famid"); // special zone
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $doc = new_Doc($dbaccess, $docid);
    if ($doc->isAffected()) $docid = $doc->id;
    //  if ($doc->doctype=="C") return; // not for familly
    $tsubmenu = array();
    // -------------------- Menu menu ------------------
    $surl = $action->getParam("CORE_STANDURL");
    if ($famid == "") $target = 'finfo' . $doc->fromid;
    else $target = 'finfo' . $famid;
    
    $tlink = array(
        "headers" => array(
            "descr" => _("Properties") ,
            "url" => "$surl&app=FDL&action=IMPCARD&zone=FDL:VIEWPROPERTIES:T&id=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "headers",
            "visibility" => POPUP_CTRLACTIVE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        "latest" => array(
            "descr" => _("View latest") ,
            "url" => "$surl&app=FDL&action=FDL_CARD&latest=Y&id=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => $target,
            "visibility" => POPUP_INVISIBLE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        "editdoc" => array(
            "descr" => _("Edit") ,
            "url" => "$surl&app=GENERIC&action=GENERIC_EDIT&rzone=$zone&id=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => $target,
            "visibility" => POPUP_ACTIVE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        "editdocw" => array(
            "descr" => _("Edit in new window") ,
            "url" => "$surl&app=GENERIC&action=GENERIC_EDIT&rzone=$zone&id=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "_blank",
            "visibility" => POPUP_ACTIVE,
            "submenu" => "",
            "barmenu" => "false"
        )
    );
    
    addCvPopup($tlink, $doc, $target);
    // addStatesPopup($tlink,$doc);
    $tlink = array_merge($tlink, array(
        "delete" => array(
            "descr" => _("Delete") ,
            "url" => "$surl&app=GENERIC&action=GENERIC_DEL&id=$docid",
            "confirm" => "true",
            "control" => "false",
            "tconfirm" => sprintf(_("Sure delete %s ?") , str_replace("'", "&rsquo;", $doc->title)) ,
            "target" => $target,
            "visibility" => POPUP_INACTIVE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        
        "lockdoc" => array(
            "descr" => _("Lock") ,
            "url" => "$surl&app=FDL&action=LOCKFILE&id=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => $target,
            "visibility" => POPUP_CTRLACTIVE,
            "submenu" => N_("security") ,
            "barmenu" => "false"
        ) ,
        "unlockdoc" => array(
            "descr" => _("Unlock") ,
            "url" => "$surl&app=FDL&action=UNLOCKFILE&id=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => $target,
            "visibility" => POPUP_CTRLACTIVE,
            "submenu" => "security",
            "barmenu" => "false"
        ) ,
        "revise" => array(
            "descr" => _("Revise") ,
            "url" => "$surl&app=FREEDOM&action=REVCOMMENT&id=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => $target,
            "visibility" => POPUP_INACTIVE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        "editprof" => array(
            "descr" => _("Change profile") ,
            "url" => "$surl&app=FREEDOM&action=EDITPROF&id=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "",
            "visibility" => POPUP_ACTIVE,
            "submenu" => "security",
            "barmenu" => "false"
        ) ,
        "histo" => array(
            "descr" => _("History") ,
            "url" => "$surl&app=FREEDOM&action=HISTO&id=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "",
            "visibility" => POPUP_ACTIVE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        "duplicate" => array(
            "descr" => _("Duplicate") ,
            "url" => "$surl&app=GENERIC&action=GENERIC_DUPLICATE&id=$docid",
            "confirm" => "true",
            "control" => "false",
            "tconfirm" => sprintf(_("Sure duplicate %s ?") , str_replace("'", "&rsquo;", $doc->title)) ,
            "target" => $target,
            "visibility" => POPUP_ACTIVE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        "access" => array(
            "descr" => _("goaccess") ,
            "url" => "$surl&app=FREEDOM&action=FREEDOM_GACCESS&id=" . $doc->profid,
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "",
            "mwidth" => 800,
            "mheight" => 300,
            "visibility" => POPUP_ACTIVE,
            "submenu" => "security",
            "barmenu" => "false"
        ) ,
        "tobasket" => array(
            "descr" => _("Add to basket") ,
            "url" => "$surl&app=FREEDOM&action=ADDDIRFILE&docid=$docid&dirid=" . $action->getParam("FREEDOM_IDBASKET") ,
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "",
            "visibility" => POPUP_CTRLACTIVE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        
        "relations" => array(
            "descr" => _("Document relations") ,
            "url" => "$surl&app=FREEDOM&action=RNAVIGATE&id=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "",
            "visibility" => POPUP_CTRLACTIVE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        "path" => array(
            "descr" => _("Access path list") ,
            "url" => "$surl&app=FREEDOM&action=FREEDOM_IFLD&id=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "",
            "visibility" => POPUP_CTRLACTIVE,
            "submenu" => "",
            "barmenu" => "false"
        ) ,
        "reference" => array(
            "descr" => _("Search linked documents") ,
            "url" => "$surl&app=GENERIC&action=GENERIC_ISEARCH&id=$docid",
            "confirm" => "false",
            "control" => "false",
            "tconfirm" => "",
            "target" => "",
            "visibility" => POPUP_INVISIBLE,
            "submenu" => "",
            "barmenu" => "false"
        )
    ));
    
    changeMenuVisibility($action, $tlink, $doc);
    $tlink["editdocw"]["visibility"] = $tlink["editdoc"]["visibility"];
    popupdoc($action, $tlink);
}
?>