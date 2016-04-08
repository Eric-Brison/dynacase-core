<?php
/*
 * @author Anakeen
 * @package FDL
 */

function geteditenumhelpmessage(Action & $action)
{
    $help = _("EditEnumHelpMessage");
    
    $action->lay->template = str_replace(array(
        "\n",
        '"'
    ) , array(
        " ",
        '&quot;"'
    ) , $help);
    $action->lay->noparse = true;
}
