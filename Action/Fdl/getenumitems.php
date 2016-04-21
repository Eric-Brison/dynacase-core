<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Get Enum items in jsonD
 *
 * @author Anakeen
 * @version $Id: editchangestate.php,v 1.8 2008/10/02 15:41:45 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once "FDL/Class.Doc.php";
/**
 * Get Enum Items
 * @param Action &$action current action
 * @global string $famid Http var : family id
 * @global string $enumid Http var : enum id
 */
function getenumitems(Action & $action)
{
    $usage = new ActionUsage($action);
    $usage->setDefinitionText("Return list of items for an enumerate attribute");
    $famid = $usage->addRequiredParameter("famid", "Family identifier", function ($fid)
    {
        $fam = new_doc("", $fid);
        if (!$fam->isAlive()) {
            return sprintf("document \"%s\" not found", $fid);
        } elseif ($fam->doctype != 'C') {
            return sprintf("document \"%s\" is not a family", $fid);
        }
        return '';
    });
    $enumId = $usage->addRequiredParameter("enumid", "Enum identifier", function ($eid) use ($famid)
    {
        $fam = new_doc("", $famid);
        $oa = $fam->getAttribute($eid);
        if (!$oa) {
            return sprintf("enumerate attribute \"%s\" not found", $eid);
        } elseif ($oa->type != "enum") {
            return sprintf("attribute \"%s\" is not an enum. Is is a \"%s\" ", $eid, $oa->type);
        }
        return '';
    });
    $out = array();
    
    try {
        $usage->verify(true);
    }
    catch(\Dcp\ApiUsage\Exception $e) {
        $err = $e->getDcpMessage();
        $out = array(
            "error" => $err
        );
    }
    
    if (empty($err)) {
        $fam = new_doc("", $famid);
        $oa = $fam->getAttribute($enumId);
        $enums = DocEnum::getFamilyEnums($fam->id, $oa->id);
        
        $lang = getLocales();
        
        $items = array();
        
        foreach ($enums as $enum) {
            $key = $enum["key"];
            $items[$key] = array(
                "key" => $key,
                "label" => $enum["label"],
                "parentKey" => $enum["parentkey"],
                "disabled" => ($enum["disabled"] == "t") ,
                "active" => ($enum["disabled"] != "t") ,
                "order" => $enum["eorder"]
            );
        }
        $localeConfig = array();
        foreach ($lang as $klang => $locale) {
            if (empty($locale["localeLabel"])) {
                $locale["localeLabel"] = $locale["label"];
            }
            $localeConfig[] = array_merge($locale, array(
                "id" => $klang,
                "flag" => sprintf("Images/flags/%s.png", strtolower(substr($klang, -2)))
            ));
            setLanguage($klang);
            foreach ($enums as $enum) {
                $key = $enum["key"];
                $lkey = sprintf("%s#%s#%s", $fam->name, $oa->id, $key);
                $l10n = _($lkey);
                if ($l10n == $lkey) {
                    $l10n = '';
                }
                $items[$enum["key"]]["locale"][] = array(
                    "lang" => $klang,
                    "label" => $l10n
                );
            }
        }
        setLanguage($action->getParam("CORE_LANG"));
        $parentLabel = ''; // get parent label - not use direct parent label if the same
        $label = $oa->getLabel();
        if (!empty($oa->fieldSet)) {
            $parentLabel = $oa->fieldSet->getLabel();
            if ($parentLabel == $label) {
                if (!empty($oa->fieldSet->fieldSet)) {
                    $parentLabel = $oa->fieldSet->fieldSet->getLabel();
                } else {
                    $parentLabel = '';
                }
            }
        }
        $out = array(
            "error" => '',
            "familyName" => $fam->name,
            "familyTitle" => $fam->getHtmlTitle() ,
            "enumId" => $oa->id,
            "enumLabel" => $oa->getLabel() ,
            "parentLabel" => $parentLabel,
            "items" => $items,
            "localeConfig" => $localeConfig
        );
    }
    $action->lay->template = json_encode($out);
    $action->lay->noparse = true;
    header('Content-type: application/json');
}
?>