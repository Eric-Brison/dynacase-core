<?php
/*
 * @author Anakeen
 * @package FDL
*/

include_once ("FDL/Class.Doc.php");
/**
 * Modify enum items
 * @param Action &$action current action
 * @global string $famid Http var : document id
 */
function editfamilyenums(Action & $action)
{
    $usage = new ActionUsage($action);
    $usage->setDefinitionText("Modify enum items");
    $famId = $usage->addRequiredParameter("famid", "Family identifier", function ($id)
    {
        $fam = new_doc("", $id);
        if (!$fam->doctype == "C") {
            return sprintf("identifier %s is not a family", $id);
        }
        return '';
    });
    $viewOldInterface = $usage->addOptionalParameter("viewoldinterface", "link to old enum interface", array(
        "yes",
        "no"
    ) , "no");
    $usage->verify();
    $fam = new_doc("", $famId);
    $err = $fam->control("edit");
    if ($err) {
        $action->exitError($err);
    }
    
    $action->lay->set("title", sprintf(_("Enum attributes for %s") , $fam->getHTMLTitle()));
    $action->lay->set("famicon", $fam->getIcon('', 30));
    $action->parent->addJsRef('lib/jquery/jquery.js');
    
    $action->parent->addJsRef("lib/jquery-ui/js/jquery-ui.js");
    $action->parent->addJsRef("FDL:editfamilyenums.js");
    $action->parent->addCssRef("css/dcp/jquery-ui.css");
    
    $lattr = $fam->getAttributes();
    $tcf = array();
    $lastFieldId = '';
    /**
     * @var NormalAttribute $oa
     */
    foreach ($lattr as $k => $oa) {
        if ((($oa->type == "enum") || ($oa->type == "enumlist")) && (($oa->phpfile == "") || ($oa->phpfile == "-")) && ($oa->getOption("system") != "yes")) {
            $parentLabel = '';
            $parentId = '';
            $label = $oa->getLabel();
            if (!empty($oa->fieldSet)) {
                $parentLabel = $oa->fieldSet->getLabel();
                $parentId = $oa->fieldSet->id;
                if ($parentLabel == $label) {
                    if (!empty($oa->fieldSet->fieldSet)) {
                        $parentLabel = $oa->fieldSet->fieldSet->getLabel();
                        $parentId = $oa->fieldSet->fieldSet->id;
                    } else {
                        $parentId = "";
                        $parentLabel = '';
                    }
                }
            }
            $tcf[] = array(
                "label" => $label,
                "parentLabel" => $parentLabel,
                "sameParent" => ($parentId == $lastFieldId) ,
                "famid" => $oa->docid,
                "enumid" => $oa->id
            );
            $lastFieldId = $parentId;
        }
    }
    
    $action->lay->set("linkOld", $viewOldInterface == "yes");
    $action->lay->set("familyid", $fam->id);
    
    $action->lay->set("NOENUMS", empty($tcf));
    $action->lay->setBlockData("ENUMS", $tcf);
}
?>