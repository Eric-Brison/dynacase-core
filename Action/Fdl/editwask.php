<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Edit ask for a document
 *
 * @author Anakeen
 * @version $Id: fdl_card.php,v 1.42 2008/12/02 15:20:52 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Dir.php");
/**
 * View a document
 * @param Action &$action current action
 * @global docid Http var : document identificator where use the ask
 * @global waskid Http var : document identificator of the ask
 */
function editwask(&$action)
{
    $docid = GetHttpVars("docid");
    $dbaccess = $action->GetParam("FREEDOM_DB");
    if ($docid == "") $action->exitError(_("no document reference"));
    $doc = new_Doc($dbaccess, $docid);
    if (!$doc->isAlive()) $action->exitError(sprintf(_("unknow document reference '%s'") , GetHttpVars("docid")));
    
    $err = $doc->control("view");
    if ($err) $action->exitError($err);
    
    $answers = $doc->getWasks();
    
    $title = "";
    foreach ($answers as $ans) {
        $wask = new_doc($dbaccess, $ans["waskid"]);
        $t = $wask->getAvalues("was_t_answer");
        foreach ($t as $k => $v) {
            $t[$k]["waskid"] = $wask->id;
            $t[$k]["checked"] = ($ans["key"] == $v["was_keys"]);
        }
        $action->lay->setBlockData("OPTIONS" . $wask->id, $t);
        if ($title != "") $title.= ', ';
        $title.= $wask->getTitle();
        
        $action->lay->set("asktitle", $title);
        $tw[] = array(
            "waskid" => $wask->id,
            "ask" => $wask->getValue("was_ask")
        );
    }
    $action->lay->setBlockData("WASK", $tw);
    $action->parent->AddJsRef("FDL:viewdoc.js", true);
    $action->lay->set("docid", $doc->id);
}
?>