<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
    $oas = $family->getNormalAttributes();
    $d = createDoc($action->dbaccess, $family->id, false, true, false);
    $tdefval = array();
    foreach ($oas as $oa) {
        if ($oa->type !== "array") {
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
            $tdefval[] = array(
                "aid" => $oa->id,
                "alabel" => htmlspecialchars(sprintf("%s / %s", $oa->fieldSet->getLabel() , $oa->getLabel())) ,
                "multiline" => ($oa->isMultiple() || $oa->type === "longtext" || $oa->type === "htmltext") ,
                "defval" => htmlspecialchars($defval, ENT_QUOTES) ,
                "htmlval" => $htmlvalue
            );
        }
    }
    
    $action->lay->setBlockData("DEFAULTS", $tdefval);
    
    $action->lay->set("famid", $family->id);
    $action->lay->set("family", $family->getHTMLTitle());
    $action->lay->set("icon", $family->getIcon("", 32));
}
