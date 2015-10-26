<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * View Document
 */
/**
 */

include_once ("FDL/fdl_card.php");

include_once ("FDL/popupdocdetail.php");
include_once ("FDL/popupfamdetail.php");
/**
 * View a extjs document
 * @param Action &$action current action
 */
function viewextdoc(Action & $action)
{
    if (!file_exists('lib/ui/freedom-extui.js')) {
        $err = _("This action requires the installation of Dynacase Extui module");
        $action->ExitError($err);
    }
    
    $action->parent->set("EXTUI", $action->parent->parent);
    $action->set("EUI_VIEWDOC", $action->parent);
    $gen = $action->execute();
    $action->lay->template = $gen;
    $action->lay->noparse = true;
}

