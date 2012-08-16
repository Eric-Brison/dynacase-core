<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * javascript utilities for view document
 *
 * @author Anakeen
 * @version $Id: viewdocjs.php,v 1.4 2008/08/12 12:42:10 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

function viewdocjs(&$action)
{
    
    setHeaderCache("text/javascript");
    // set default geo for mini view
    $mgeo = $action->GetParam("MVIEW_GEO");
    if (preg_match("/([0-9]+)\+([0-9]+)\+([0-9]+)x([0-9]+)/", $mgeo, $reg)) {
        $action->lay->set("mgeox", intval($reg[1]));
        $action->lay->set("mgeoy", intval($reg[2]));
        $action->lay->set("mgeow", intval($reg[3]));
        $action->lay->set("mgeoh", intval($reg[4]));
    } else {
        $action->lay->set("mgeox", "250");
        $action->lay->set("mgeoy", "210");
        $action->lay->set("mgeow", "300");
        $action->lay->set("mgeoh", "200");
    }
}
?>