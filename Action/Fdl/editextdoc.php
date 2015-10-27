<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * View a extjs document
 * @param Action &$action current action
 */
function editextdoc(Action & $action)
{
    if (!file_exists('lib/ui/freedom-extui.js')) {
        $err = _("This action requires the installation of Dynacase Extui module");
        $action->ExitError($err);
    }

    $action->log->deprecated("Action FDL:EDITEXTDOC deprecated use EXTUI:EUI_EDITDOC instead");
    $action->parent->set("EXTUI", $action->parent->parent);
    $action->set("EUI_EDITDOC", $action->parent);
    $gen = $action->execute();
    $action->lay->template = $gen;
    $action->lay->noparse = true;
}
