<?php

/**
 * Send javascript onefam collection menu
 *
 * @author Anakeen 2010
 * @version $Id:  $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package API
 * @subpackage
 */
/**
 */

include_once("EXTUI/eui_xmlmenu.php");
/**
 * Colection menu
 * @param Action &$action current action
 * @global famid Http var : family id for menu
 * @global fldid Http var : id collection where is actually
 * @global menuxml Http var : the xml menu file APP:file.xml
 */
function onefam_ext_menu(&$action) {
    $fldid=$action->getArgument("fldid");
    $famid=$action->getArgument("famid");
    $menuxml=$action->getArgument("menuxml","EXTUI:default-collection-menu.xml");
    $menu=eui_getxmlmenu($docid,$menuxml,$fldid);
    $dbaccess=$action->getParam("FREEDOM_DB");


    if ($fldid) {
        $fld=new_doc($dbaccess, $fldid);
        if (! $famid) {
            if ($fld->isAlive()) {
                $famid=$fld->getValue("se_famid");
            }
        }
    }

    unset($menu["menu"]["createsearch"]);
    $fam=new_doc($dbaccess,$famid);
    if ($fam->isAlive()) {
        $main["family"]=array("type"=>"menu",
               "label"=>$fam->getTitle(),
               //"icon"=>$fam->getIcon(),
                "items"=>array());
         
        $main["family"]["items"]["create"]=array("script"=>array("file"=>"lib/ui/fdl-interface-action-common.js",
                                            "class"=>"Fdl.InterfaceAction.CreateDocument",
                                            "parameters"=>array("family"=>$fam->id)),
                            "label"=>sprintf(_("Create %s"),$fam->getTitle()),
                            "icon"=>$fam->getIcon());
        $controlcreate=true;
        $tfam=$fam->GetChildFam($fam->id, $controlcreate);
        if (count($tfam) > 0) {
            $main["family"]["items"]["subfam"]=array("type"=>"menu",
               "label"=>_("other families"),
                "items"=>array());
            foreach ($tfam as $k=>$v) {
                $main["family"]["items"]["subfam"]["items"]["create".$v["id"]]=array("script"=>array("file"=>"lib/ui/fdl-interface-action-common.js",
                                            "class"=>"Fdl.InterfaceAction.CreateDocument",
                                            "parameters"=>array("family"=>$v["id"])),
                            "label"=>sprintf(_("Create %s"),$v["title"]),
                            "icon"=>$fam->getIcon($v["icon"]));
            }

        }


        if ($fldid && $fld->isAlive() && ($fld->doctype !='T')) {
            $main["family"]["items"]["edit"]=array("script"=>array("file"=>"lib/ui/fdl-interface-action-common.js",
                                            "class"=>"Fdl.InterfaceAction.EditSearchFilter"),
                            "label"=>sprintf(_("Edit %s"),$fld->getTitle()),
                            "icon"=>$fld->getIcon());
        }

        $fmenu=$fam->getMenuAttributes();
        if (count($fmenu) > 0) {
            $first=true;
            foreach ($fmenu as $k=>$v) {
                if ($v->getOption("global")=="yes") {
                     
                    if ($first) $main["family"]["items"]["sepspec"]=array("type"=>"separator");
                    $main["family"]["items"]["glob".$k]=array("url"=>$fam->urlWhatEncode($v->link),
                                                               "label"=>$v->getLabel());
                    $first=false;
                }
            }
        }

        $menu=array("menu"=>array_merge($main,$menu["menu"]));
    }
     
    // print_r2($menu);
     
    $action->lay->noparse=true; // no need to parse after - increase performances
    $action->lay->template=json_encode($menu);
}
?>