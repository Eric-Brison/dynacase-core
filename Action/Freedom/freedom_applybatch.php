<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Use to help to the construction of batch document
 *
 * @author Anakeen 2005
 * @version $Id: freedom_applybatch.php,v 1.7 2008/08/14 09:59:14 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Lib.Dir.php");
/**
 * Choose a batch document
 * @param Action &$action current action
 * @global id Http var : folder identificator to use to construct batch
 */
function freedom_applybatch(&$action)
{
    
    $dirid = GetHttpVars("id");
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $bdoc = new_Doc($dbaccess, "BATCH");
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    
    $tb = $bdoc->getChildFam();
    foreach ($tb as $k => $v) {
        $tb[$k]["iconsrc"] = $bdoc->getIcon($v["icon"]);
        
        $fa = new_doc($dbaccess, $v["id"]);
        $la = $fa->getActionAttributes();
        $ta = array();
        if ($la) {
            foreach ($la as $ka => $va) {
                $ta[] = $va->getLabel();
            }
        }
        $tb[$k]["actions"] = implode(",<br>", $ta);
    }
    
    $action->lay->setBlockData("BATCHFAMS", $tb);
    $action->lay->set("dirid", $dirid);
}
?>