<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Incorporate scripts for extjs
 *
 * @author Anakeen
 * @version $Id:  $
 * @package FDL
 */
/**
 */

function addextscript(Action & $action)
{
    $debug = $action->getArgument("debug");
    $action->lay->eset("debug", $debug);
    $lang = strtolower(strtok($action->getParam("CORE_LANG") , "_"));
    
    if (file_exists(sprintf("%s/lib/ext/src/locale/ext-lang-%s.js", DEFAULT_PUBDIR, $lang))) {
        $action->lay->eset("lang", $lang);
    } else {
        $action->lay->set("lang", false);
    }
}
?>