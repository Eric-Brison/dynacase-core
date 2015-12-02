<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Display list of enumrate attribute for a family
 *
 * @author Anakeen
 * @version $Id: generic_chooseenumattr.php,v 1.3 2008/12/11 10:06:52 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("GENERIC/generic_util.php");
/**
 * Display list of enumrate attribute for a family
 * @param Action &$action current action
 * @global string $famid Http var : family document identifier where find enum attributes
 */
function generic_chooseenumattr(Action &$action)
{
    $famid = GetHttpVars("famid", getDefFam($action));
    $dbaccess = $action->dbaccess;
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    
    $tcf = array();
    /**
     * @var DocFam $fdoc
     */
    $fdoc = new_doc($dbaccess, $famid);
    $action->lay->set("famid", $fdoc->id);
    
    $lattr = $fdoc->getNormalAttributes();
    foreach ($lattr as $k => $a) {
        if ((($a->type == "enum") || ($a->type == "enumlist")) && (($a->phpfile == "") || ($a->phpfile == "-")) && ($a->getOption("system") != "yes")) {
            
            $tcf[] = array(
                "label" => $a->getLabel() ,
                "famid" => $a->docid,
                "ftitle" => $fdoc->getTitle($a->docid) ,
                "kindid" => $a->id
            );
        }
    }
    
    $action->lay->setBlockData("CATG", $tcf);
    $action->lay->set("title", sprintf(_("modify enumerate attributes for family : %s") , $fdoc->title));
    $action->lay->set("icon", $fdoc->getIcon());
}
