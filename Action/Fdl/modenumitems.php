<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Modify Enum items
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
 * Modify Enum Items
 * @param Action &$action current action
 * @global string $famid Http var : family id
 * @global string $enumid Http var : enum id
 * @global string $enumid Http var : modification properties (json encoded)
 */
function modenumitems(Action & $action)
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
    
    $modifications = $usage->addRequiredParameter("items", "Items modifications", function ($items)
    {
        $dit = json_decode($items);
        if (empty($dit)) {
            return sprintf("items \"%s\" must be json encoded structure", $items);
        } elseif (!is_array($dit)) {
            return sprintf("items \"%s\" must be json array", $items);
        }
        return '';
    });
    $usage->setStrictMode(false);
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
        /**
         * @var NormalAttribute $oa
         */
        $oa = $fam->getAttribute($enumId);
        $enums = DocEnum::getFamilyEnums($fam->id, $oa->id);
        
        $items = json_decode($modifications, true);
        foreach ($items as $item) {
            if (empty($item["key"])) {
                $err = sprintf("all keys must be not empty");
            }
        }
        if (empty($err)) {
            $enumKeys = array_keys($oa->getEnumLabel());
            $es = new EnumStructure();
            $err = '';
            try {
                /**
                 * @var EnumStructure $item
                 */
                foreach ($items as $item) {
                    $es->affect($item);
                    if (in_array($es->key, $enumKeys)) {
                        DocEnum::modifyEnum($fam->id, $oa->id, $es);
                    } else {
                        DocEnum::addEnum($fam->id, $oa->id, $es);
                    }
                }
            }
            catch(\Dcp\Exception $e) {
                $err = $e->getDcpMessage();
            }
        }
        $out = array(
            "familyName" => $fam->name,
            "enumId" => $oa->id,
            "error" => $err
        );
    }
    LibSystem::reloadLocaleCache();
    $action->lay->template = json_encode($out);
    $action->lay->noparse = true;
    header('Content-type: application/json');
}
?>