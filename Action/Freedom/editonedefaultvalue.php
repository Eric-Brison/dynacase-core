<?php
/*
 * @author Anakeen
 * @package FDL
*/

include_once ("FDL/Class.Doc.php");
include_once ("FDL/editutil.php");
function editonedefaultvalue(Action $action)
{
    $usage = new ActionUsage($action);
    
    $famid = $usage->addRequiredParameter("famid", "Family identifier", function ($value)
    {
        $family = new_doc("", $value);
        if ($family->doctype !== "C") {
            return "Must be a family identifier";
        }
        return '';
    });
    /**
     * @var DocFam $family
     */
    $family = new_doc("", $famid);
    $attrid = $usage->addRequiredParameter("attrid", "Attribute identifier", function ($value) use ($family)
    {
        $oa = $family->getAttribute($value);
        if (!$oa) {
            return sprintf("Attribute \"%s\" not found in family \"%s\"", $value, $family->name);
        }
        return '';
    });
    
    $usage->verify();
    
    $err = $family->control("edit");
    if ($err) {
        $action->exitError($err);
    }
    
    editmode($action);
    $action->parent->addJsRef("FREEDOM/Layout/editonedefaultvalue.js");
    
    $oa = $family->getAttribute($attrid);
    
    $defval = $family->getDefValue($oa->id);
    
    if ($oa->fieldSet->type === "array") {
        $oa->fieldSet->type = "frame";
    }
    
    $oa->repeat = ($oa->getOption('multiple') === "yes");
    $oa->setVisibility("W");
    switch ($oa->type) {
        case "longtext":
        case "htmltext":
            $multiline = true;
            break;

        default:
            $multiline = $oa->repeat;
    }
    switch ($oa->type) {
        case "file":
        case "image";
        $inputName = "_UPL_" . $oa->id;
        break;

    default:
        $inputName = "_" . $oa->id;
        if ($oa->repeat && $oa->type === "enum") {
            $inputName.= '[]';
        }
}

$action->lay->set("inputname", $inputName);
$action->lay->set("multiline", $multiline);
$action->lay->set("family", $family->getHTMLTitle());
$action->lay->eset("value", $defval);
$action->lay->set("aid", $oa->id);
$action->lay->set("alabel", $oa->getLabel());
$action->lay->set("famid", $family->id);

$action->lay->set("icon", $family->getIcon("", 32));
$action->lay->set("formatInput", getHtmlInput($family, $oa, $defval, "", "", true));
}
