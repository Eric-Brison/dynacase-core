<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Open Document
 *
 * @author Anakeen
 * @version $Id:  $
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
 * @global id int Http var : document identifier to see
 * @global mode string Http var : edit or view mode
 *
 */
function opendoc(Action & $action)
{
    $docid = $action->getArgument("id");
    $mode = $action->getArgument("mode", "none");
    $err = '';
    if ($mode == "none") {
        if (!$docid) {
            $famid = $action->getArgument("famid", $action->getArgument("classid"));
            if ($famid) {
                $mode = "new";
            }
        } else {
            $dbaccess = $action->dbaccess;
            $doc = new_doc($dbaccess, $docid, true);
            if (($err = $doc->control('edit')) == "") {
                $mode = 'edit';
            } else if (($err = $doc->control('view')) == "") {
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

        default:
            if ($err) redirectAsGuest($action);
        }
        if ($err) $action->exitError($err);
    }
    