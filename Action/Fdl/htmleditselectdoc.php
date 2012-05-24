<?php

function htmleditselectdoc(Action $action ){

    $usage = new ActionUsage($action);

    $fam = $usage->addNeeded("fam", "fam");
    $docrev = $usage->addOption("docrev", "docrev", array(), "latest");
    $initid = $usage->addOption("initid", "initid");
    $title = $usage->addOption("title", "title");
    $filter = $usage->addOption("filter", "filter", array(), "");

    $usage->strict(false);

    $usage->verify();

    $action->lay->set("FAM", $fam);
    $action->lay->set("DOCREV", $docrev);
    $action->lay->set("INITID", $initid);
    $action->lay->set("TITLE", $title);
    $action->lay->set("FILTER", $filter);

}
?>