<?php
/*
 * @author Anakeen
 * @package FDL
*/

include_once ("FDL/Class.Doc.php");
include_once ("FDL/editutil.php");
function editdefaultvalues(Action $action)
{
    $usage = new ActionUsage($action);
    $famid = $usage->addRequiredParameter("famid", "Family identifier", function ($value)
    {
        $family = new_doc("", $value);
        if ($family->doctype !== "C") {
            return "Must be a family identifier";
        }
        return "";
    });
    $family = new_doc("", $famid);
    
    $usage->verify();
    
    $err = $family->control("edit");
    if ($err) {
        $action->exitError($err);
    }
    editmode($action);
    $action->parent->addJsRef("lib/jquery-ui/js/jquery-ui.js");
    $action->parent->addJsRef("lib/jquery-dataTables/js/jquery.dataTables.min.js");
    $action->parent->addCssRef("css/dcp/jquery-ui.css");
    $action->parent->addCssRef("lib/jquery-dataTables/css/jquery.dataTables.css");
    $action->parent->addJsRef("FREEDOM/Layout/editdefaultvalues.js");
    /**
     * @var DocFam $family
     */
    $oas = $family->getAttributes();
    $d = createDoc($action->dbaccess, $family->id, false, true, false);
    $taDefval = $tpDefval = array();
    foreach ($oas as $oa) {
        if ($oa->isNormal && $oa->type !== "array") {
            /**
             * @var NormalAttribute $oa
             */
            $defval = $family->getDefValue($oa->id);
            if ($defval) {
                if ($oa->type === "file" || $oa->type === "image") {
                    $htmlvalue = $family->getHtmlValue($oa, $defval, "_blank");
                } else {
                    $htmlvalue = $d->getHtmlAttrValue($oa->id, "_blank");
                }
                if ($oa->type === "image") {
                    $htmlvalue = sprintf('<img class="image" src="%s">', $htmlvalue);
                }
            } else {
                $htmlvalue = '';
            }
            $row = array(
                "aid" => $oa->id,
                "alabel" => htmlspecialchars(sprintf("%s / %s", $oa->fieldSet->getLabel() , $oa->getLabel())) ,
                "multiline" => ($oa->isMultiple() || $oa->type === "longtext" || $oa->type === "htmltext") ,
                "defval" => htmlspecialchars($defval, ENT_QUOTES) ,
                "htmlval" => $htmlvalue
            );
            
            if ($oa->usefor === "Q") {
                $tpDefval[] = $row;
            } else {
                $taDefval[] = $row;
            }
        }
    }
    
    $action->lay->setBlockData("ADEFAULTS", $taDefval);
    $action->lay->setBlockData("PDEFAULTS", $tpDefval);
    $action->lay->set("hasParam", count($tpDefval) > 0);
    
    $action->lay->set("famid", $family->id);
    $action->lay->set("family", $family->getHTMLTitle());
    $action->lay->set("icon", $family->getIcon("", 32));
}
