<?php
/*
 * @author Anakeen
 * @package FDL
*/

function pagination(Action & $action)
{
    /*
     array(
             "type",
             "tab",
             "dirid",
             "catg",
             "famid",
             "pds",
             "onglet"
         )
    */
    $paginationConfig = $action->getParam("paginationConfig");
    /*
       array(
        "next",
        "prev",
        "last",
        "numberofpage",
        "pagenumber",
        "numberofdocuments",
        "rangefrom",
        "rangeto",
        "hasnext"
       )
    */
    $searchConfig = $action->getParam("searchConfig");
    
    $action->parent->addJsRef("GENERIC:pagination.js");
    
    $layoutKeys = array(
        "alignleft",
        "aligncenter",
        "alignright"
    );
    $paginationDefButton = array(
        /*  "%n" => $searchConfig["hasnext"] ? getButtonLayout("nextpage", $paginationConfig, $searchConfig["next"], _("Next page")) : "",
        "%p" => $searchConfig["pagenumber"] != 1 ? getButtonLayout("prevpage", $paginationConfig, $searchConfig["prev"], _("Previous page")) : "",
        "%l" => $searchConfig["hasnext"] ? getButtonLayout("lastpage", $paginationConfig, $searchConfig["last"], _("Last page")) : "",
        "%f" => $searchConfig["pagenumber"] != 1 ? getButtonLayout("firstpage", $paginationConfig, "0", _("First page")) : ""
        */
        
        "%n" => getButtonLayout($action, "nextpage", $paginationConfig, $searchConfig["next"], _("Next page") , $searchConfig["hasnext"]) ,
        "%p" => getButtonLayout($action, "prevpage", $paginationConfig, $searchConfig["prev"], _("Previous page") , $searchConfig["pagenumber"] != 1) ,
        "%l" => getButtonLayout($action, "lastpage", $paginationConfig, $searchConfig["last"], _("Last page") , $searchConfig["hasnext"]) ,
        "%f" => getButtonLayout($action, "firstpage", $paginationConfig, "0", _("First page") , $searchConfig["pagenumber"] != 1)
    );
    
    $paginationDefOther = array(
        "%np" => $searchConfig["numberofpage"],
        "%cp" => $searchConfig["pagenumber"],
        "%nd" => $searchConfig["numberofdocuments"],
        "%br" => $searchConfig["rangefrom"],
        "%er" => $searchConfig["rangeto"]
    );
    
    $action->lay->set("pagination", true);
    if ($paginationConfig["type"] !== "none") {
        
        $type = $paginationConfig["type"];
        switch ($paginationConfig["type"]) {
            case "basic":
                $type = "%p%t%cp%t%n";
                break;

            case "pageNumber":
                $type = "%f%p%t%cp/%np%t%n%l";
                break;

            case "documentNumber":
                $type = _("Showing %br to %er of %nd documents%t%t%p%n");
                break;

            default:
                if ($type) {
                    $type = _($type);
                }
        }
        $parts = explode("%t", $type);
        $action->lay->set("centeralign", count($parts) > 1);
        $action->lay->set("rightalign", count($parts) > 2);
        
        foreach ($parts as $k => $v) {
            if ($k > 2) break;

            
            if (!empty($v)) {
                $v = str_replace(array_keys($paginationDefOther) , array_values($paginationDefOther) , $v);
                $action->lay->set($layoutKeys[$k], str_replace(array_keys($paginationDefButton) , array_values($paginationDefButton) , $v));
            } else $action->lay->set($layoutKeys[$k], "");
        }
    } else {
        $action->lay->set("pagination", false);
    }
}

function getButtonLayout(Action & $action, $buttonName, $paginationConfig, $page, $buttonTitle, $visible = true)
{
    $lay = new Layout("GENERIC/Layout/pagination_button.xml");
    foreach ($paginationConfig as $key => $value) {
        $lay->set($key, $value);
    }
    $lay->set("APPNAME", isset($action->parent->name) ? $action->parent->name : '');
    $lay->set("page", $page);
    $lay->set("buttonclass", $buttonName);
    $lay->set("buttontitle", $buttonTitle);
    $lay->set("buttonvisible", $visible == true);
    return $lay->gen();
}
