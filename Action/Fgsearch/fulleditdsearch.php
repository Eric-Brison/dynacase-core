<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Full Text Search document
 *
 * @author Anakeen
 * @version $Id: fulleditdsearch.php,v 1.1 2007/10/17 14:27:28 marc Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Class.Dir.php");
include_once ("FDL/Lib.Dir.php");
include_once ("FDL/freedom_util.php");
/**
 * Interface Fulltext Detailled  Search document
 * @param Action &$action current action
 * @global keyword Http var : word to search in any values
 * @global famid Http var : restrict to this family identioficator
 * @global start Http var : page number
 * @global dirid Http var : search identificator
 */
function fulleditdsearch(&$action)
{
    
    $famid = $action->getArgument("famid", 0);
    $substitute = ($action->getArgument("substitute") == "yes");
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    if (!is_numeric($famid)) $famid = getFamIdFromName($dbaccess, $famid);
    if ($famid == 0) $famid = 7; // FILE family
    $action->lay->set("searchtitle", _("detailled search"));
    $action->lay->set("substitute", $substitute);
    
    $tclassdoc = GetClassesDoc($dbaccess, $action->user->id, array(
        1,
        2
    ) , "TABLE");
    foreach ($tclassdoc as $k => $cdoc) {
        $selectclass[$k]["idcdoc"] = $cdoc["initid"];
        $selectclass[$k]["classname"] = $cdoc["title"];
        $selectclass[$k]["famselect"] = ($cdoc["initid"] == $famid) ? "selected" : "";
    }
    $action->lay->SetBlockData("SELECTFAM", $selectclass);
    
    if ($famid > 0) {
        $search = createTmpDoc($dbaccess, 16);
        
        $search->setValue("se_famid", $famid);
        $search->setValue("se_latest", "yes");
        $search->lay = $action->lay;
        $search->editdsearch();
        
        $fdoc = new_doc($dbaccess, $famid);
        $action->lay->set("famicon", $fdoc->getIcon());
        $action->lay->set("famid", $fdoc->id);
    }
}
?>
