<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
