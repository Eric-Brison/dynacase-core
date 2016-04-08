<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Relation Navigation
 *
 * @author Anakeen
 * @version $Id: rnavigate.php,v 1.7 2008/06/03 16:31:53 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Lib.Dir.php");

function rnavigate(Action & $action, $onlyGetResult = false)
{
    $dbaccess = $action->dbaccess;
    
    $usage = new ActionUsage($action);
    $docid = $usage->addRequiredParameter("id", "id of the current document");
    $usage->setStrictMode(false);
    $usage->verify(true);
    
    $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FDL/Layout/common.js");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    $limit = 100;
    
    $doc = new_Doc($dbaccess, $docid);
    if ($doc->locked == - 1) {
        $doc = new_Doc($dbaccess, $doc->getLatestId());
    }
    $idocid = $doc->initid;
    
    $rdoc = new DocRel($dbaccess, $idocid);
    $rdoc->sinitid = $idocid;
    
    $action->lay->set("Title", $doc->getHTMLTitle());
    
    $relationsFrom = array();
    $relationsTo = array();
    
    $i18nFolder = _("folder");
    
    $trel = $rdoc->getIRelations("", "", $limit);
    foreach ($trel as $currentResult) {
        $relationsFrom[$currentResult["sinitid"]] = array(
            "iconsrc" => $doc->getIcon($currentResult["sicon"]) ,
            "initid" => $currentResult["sinitid"],
            "title" => $currentResult["stitle"],
            "url" => "?app=FDL&action=OPENDOC&mode=view&id=" . $currentResult["sinitid"],
            "attributeId" => $currentResult["type"]
        );
    }
    
    $trel = $rdoc->getRelations("", "", $limit);
    foreach ($trel as $currentResult) {
        $relationsTo[$currentResult["cinitid"]] = array(
            "iconsrc" => $doc->getIcon($currentResult["cicon"]) ,
            "initid" => $currentResult["cinitid"],
            "title" => $currentResult["ctitle"],
            "url" => "?app=FDL&action=OPENDOC&mode=view&id=" . $currentResult["sinitid"],
            "attributeId" => $currentResult["type"]
        );
    }
    /* Get attribute that have the relation */
    
    $attributesId = array_unique(array_merge(array_map(function ($value)
    {
        return $value["attributeId"];
    }
    , $relationsFrom) , array_map(function ($value)
    {
        return $value["attributeId"];
    }
    , $relationsTo)));
    $attributesId = array_filter($attributesId);
    if (!empty($attributesId)) {
        $query = new QueryDb($dbaccess, "DocAttr");
        $query->AddQuery(GetSqlCond($attributesId, "id"));
        $queryResults = $query->Query(0, 0, "TABLE");
        if ($queryResults) {
            $attributesValues = array();
            foreach ($queryResults as $currentResult) {
                $attributesValues[$currentResult["id"]] = $currentResult["labeltext"];
            }
            foreach ($relationsFrom as & $currentRelation) {
                if (isset($attributesValues[$currentRelation["attributeId"]])) {
                    $currentRelation["attributeLabel"] = $attributesValues[$currentRelation["attributeId"]] != "folder" ? $attributesValues[$currentRelation["attributeId"]] : $i18nFolder;
                }
            }
            foreach ($relationsTo as & $currentRelation) {
                if (isset($attributesValues[$currentRelation["attributeId"]])) {
                    $currentRelation["attributeLabel"] = $attributesValues[$currentRelation["attributeId"]] != "folder" ? $attributesValues[$currentRelation["attributeId"]] : $i18nFolder;
                }
            }
        }
    }
    // Verify visibility for current user
    $tids = array_unique(array_merge(array_map(function ($value)
    {
        if (!empty($value["initid"])) {
            return $value["initid"];
        } else {
            return null;
        }
    }
    , $relationsFrom) , array_map(function ($value)
    {
        if (!empty($value["initid"])) {
            return $value["initid"];
        } else {
            return null;
        }
    }
    , $relationsTo)));
    $tids = array_filter($tids);
    if (!empty($tids)) {
        $vdoc = getVisibleDocsFromIds($dbaccess, $tids, $action->user->id);
        $tids = array_map(function ($value)
        {
            return $value["initid"];
        }
        , $vdoc);
    }
    
    $filtersOnlyVisible = function ($value) use ($tids)
    {
        return in_array($value["initid"], $tids);
    };
    
    $relationsFrom = array_filter($relationsFrom, $filtersOnlyVisible);
    $relationsTo = array_filter($relationsTo, $filtersOnlyVisible);
    
    $results = array(
        "currentDocument" => array(
            "id" => $docid,
            "title" => $doc->getHTMLTitle()
        ) ,
        "relationsTo" => $relationsTo,
        "relationsFrom" => $relationsFrom
    );
    
    if ($onlyGetResult) {
        return $results;
    } else {
        $initData = array(
            "i18n" => array(
                "view document" => _("view document") ,
                "noone document" => _("noone document") ,
                "referenced from" => _("Referenced from") ,
                "referenced" => _("Reference")
            ) ,
            'core_stand_url' => getParam("CORE_STANDURL") ,
            'initial_data' => $results
        );
        $action->lay->set("init_data", json_encode($initData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP));
        $action->lay->set("RNAVIGATE_JS", $action->parent->getJsLink("FREEDOM:rnavigate.js"));
        return null;
    }
}

function rnavigate_json(&$action)
{
    $return = array(
        "success" => true,
        "error" => "",
        "data" => array()
    );
    
    try {
        $return["data"] = rnavigate($action, true);
    }
    catch(Exception $e) {
        $return["success"] = false;
        $return["error"][] = $e->getMessage();
        unset($return["data"]);
    }
    
    $action->lay->template = json_encode($return);
    $action->lay->noparse = true;
    header('Content-type: application/json');
}
