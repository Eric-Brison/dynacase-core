<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Open Document
 *
 * @author Anakeen 2000
 * @version $Id:  $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
/**
 * open in edit mode if can else in view mode
 * all parameters use in GENERIC_GENERIC_EDIT can be use in edit mode
 * all parameters use in FDL:FDL_CARD can be use in view mode
 * @param Action &$action current action
 * @global id Http var : document identificator to see
 * @global mode Http var : edit or view mode
 *
 */
function opendoc(Action & $action)
{
    $docid = $action->getArgument("id");
    $mode = $action->getArgument("mode", "none");
    
    if ($mode == "none") {
        if (!$docid) {
            $famid = $action->getArgument("famid", $action->getArgument("classid"));
            if ($famid) {
                $mode = "new";
            }
        } else {
            $dbaccess = $action->GetParam("FREEDOM_DB");
            $doc = new_doc($dbaccess, $docid, true);
            if ($doc->control('edit') == "") {
                $mode = 'edit';
            } else if ($doc->control('view') == "") {
                $mode = 'view';
            }
        }
    }
    switch ($mode) {
        case 'new':
        case 'edit':
            $action->parent->set("GENERIC", $action->parent->parent);
            $action->set("GENERIC_EDIT", $action->parent);
            $gen = $action->execute();
            $action->lay->template = $gen;
            $action->lay->noparse = true;
            break;

        case 'view':
            $action->set("FDL_CARD", $action->parent);
            $gen = $action->execute();
            $action->lay->template = $gen;
            $action->lay->noparse = true;
            break;
    }
}
?>
