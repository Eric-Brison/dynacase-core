<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

include_once ("FDL/Class.Doc.php");
/**
 * @param Action $action
 */
function zone_tag_management(Action & $action)
{
    $usage = new ActionUsage($action);
    $usage->setText(_("Display tag footer"));
    $docid = $usage->addNeeded("id", "document id");
    $type = $usage->addOption("type", "type of zone");
    $mode = $usage->addOption("mode", "Tagable property") == "true" ? true : false;
    $usage->strict(false);
    $usage->verify();
    
    $doc = new_Doc($action->dbaccess, $docid);
    if (!$doc->isAlive()) $action->exitError(sprintf(_("tagmanagement: document [%d] is not alive") , $docid));
    $action->lay->set("edit", false);
    $action->lay->set("docid", $doc->initid);
    $action->lay->set("tagMode", $mode);
    switch ($type) {
        case "edit":
            $action->lay->set("edit", true);
            break;
    }
    $tags = $doc->tag()->getTagsValue($doc->tag()->getTag());
    $listoftags = array();
    if (count($tags) > 0) {
        foreach ($tags as $tag) {
            $listoftags[] = array(
                "tagName" => $tag,
                "coma" => ","
            );
        }
        $listoftags[count($listoftags) - 1]["coma"] = "";
    } else {
        $listoftags[] = array(
            "tagName" => "",
            "coma" => ""
        );
        $action->lay->set("edit", false);
    }
    $action->lay->SetBlockData("docTags", $listoftags);
    $action->parent->addJsRef("lib/jquery/jquery.js");
    $action->parent->addJsRef("lib/jquery-ui/js/jquery-ui-1.8.21.custom.min.js");
    $action->parent->addCssRef("lib/jquery-ui/css/redmond/jquery-ui-1.8.21.custom.css");
    $action->parent->addJsRef("FDL/Layout/zone_tag_management.js");
}
