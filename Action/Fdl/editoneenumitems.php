<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * @param Action $action
 */
function editoneenumitems(Action & $action)
{
    $usage = new ActionUsage($action);
    $famid = $usage->addRequiredParameter("famid", "Family id");
    $enumid = $usage->addRequiredParameter("enumid", "Enum id");
    
    $usage->setStrictMode(false);
    //Maybe use exception and try vatch to send info in json for datatable
    $usage->verify();
    
    $action->lay->eset("famid", $famid);
    $action->lay->eset("enumid", $enumid);
    
    $action->parent->addCssRef("css/dcp/jquery-ui.css");
    $action->parent->addCssRef("lib/tipsy/src/stylesheets/tipsy.css");
    $action->parent->addCssRef("lib/jquery-dataTables/css/jquery.dataTables.css");
    $action->parent->addCssRef("ACCESS:user_access.css");
    $action->parent->addCssRef("FDL:editoneenumitems.css");
    
    $action->parent->addJsRef("lib/jquery/jquery.js");
    $action->parent->addJsRef("lib/tipsy/src/javascripts/jquery.tipsy.js");
    $action->parent->addJsRef("lib/jquery-ui/js/jquery-ui.js");
    $action->parent->addJsRef("lib/jquery-dataTables/js/jquery.dataTables.js");
    $action->parent->addJsRef("FDL:editenumitemswidget.js", true);
    $action->parent->addJsRef("FDL:editoneenumitems.js", true);
}
