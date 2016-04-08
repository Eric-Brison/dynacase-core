<?php
/*
 * @author Anakeen
 * @package FDL
*/

include_once ("FDL/Class.Dir.php");
include_once ("FDL/editcard.php");
/**
 * Compose family parameter edit field
 * @param Action $action
 * @return bool
 */
function editfamilyparameter(Action & $action)
{
    $usage = new ActionUsage($action);
    $famid = $usage->addRequiredParameter("famid", _("family id"));
    $attrid = $usage->addRequiredParameter("attrid", _("attribute id"));
    $default = $usage->addOptionalParameter("emptyValue", _("value for empty field"));
    $value = $usage->addOptionalParameter("value", _("value in field"));
    $onChange = $usage->addOptionalParameter("submitOnChange", _("Sending input on change?"));
    $localSubmit = $usage->addOptionalParameter("localSubmit", _("Adding button to submit")) == "yes" ? true : false;
    $submitLabel = $usage->addOptionalParameter("submitLabel", _("Label of submit button") , array() , _("Submit"));
    $usage->setStrictMode();
    $usage->verify();
    
    editmode($action);
    
    $action->lay->eset("famid", $famid);
    $action->lay->eset("attrid", strtolower($attrid));
    /**
     * @var DocFam $doc
     */
    $doc = new_Doc($action->dbaccess, $famid, true);
    if ($doc->isAlive()) {
        /**
         * @var NormalAttribute $attr
         */
        $attr = $doc->getAttribute($attrid);
        if (!$attr) {
            $action->AddWarningMsg(sprintf(_("Attribute [%s] is not found") , $attrid));
            $action->lay->template = htmlspecialchars(sprintf(_("Attribute [%s] is not found") , $attrid) , ENT_QUOTES);
            $action->lay->noparse = true;
            return false;
        }
        $action->lay->eset("label", $attr->getLabel());
        
        if ($onChange == "no") {
            $onChange = "";
        } elseif ($onChange == "yes" || (!$onChange && !$localSubmit)) {
            $onChange = "yes";
        }
        $action->lay->eset("local_submit", $localSubmit);
        $action->lay->eset("submit_label", $submitLabel);
        
        if (!$value) {
            if ($default !== null) {
                $value = $default;
            } else {
                $value = $doc->getParameterRawValue($attrid, $doc->GetValueMethod($attrid));
            }
        }
        $d = createTmpDoc($action->dbaccess, $doc->id);
        $fdoc = $d->getFamilyDocument();
        $d->setDefaultValues($fdoc->getParams() , false);
        useOwnParamters($d);
        $input_field = getHtmlInput($d, $attr, $value, "", "", true);
        $action->lay->set("input_field", $input_field);
        $action->lay->set("change", ($onChange != ""));
    } else {
        $action->AddWarningMsg(sprintf(_("Family [%s] not found") , $famid));
        $action->lay->template = htmlspecialchars(sprintf(_("Family [%s] not found") , $famid) , ENT_QUOTES);
        $action->lay->noparse = true;
        return false;
    }
    
    $action->parent->addJsRef("FDL/Layout/editparameter.js");
    return true;
}
