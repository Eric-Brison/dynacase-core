<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Edit enum attribute items
 *
 * @author Anakeen 2000
 * @version $Id: generic_editcatg.php,v 1.9 2008/08/14 09:59:14 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Dir.php");
include_once ("GENERIC/generic_util.php");
// -----------------------------------
function generic_editcatg(&$action)
{
    // -----------------------------------
    global $dbaccess;
    
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $aid = GetHttpVars("aid"); // attribute id
    $famid = GetHttpVars("fid"); // family id
    $action->lay->set("aid", $aid);
    $doc = new_Doc($dbaccess, $famid);
    
    $err = $doc->control("edit"); // need edit permission
    if ($err != "") $action->exitError($err);
    
    $a = $doc->getAttribute($aid);
    $action->lay->set("fid", $a->docid);
    
    $action->lay->set("TITLE", sprintf(_("definition of enumerate attribute %s of %s family") , $a->getLabel() , $doc->title));
    $action->lay->set("icon", $doc->getIcon());
    $tref = array();
    $tlabel = array();
    $tlevel = array();
    
    $enum = $a->getEnum();
    foreach ($enum as $k => $v) {
        $k = str_replace("\\.", "-dot-", $k);
        $tk = explode(".", $k);
        $tv = $v;
        $sp = "";
        $loff = "";
        for ($i = 1; $i < count($tk); $i++) $loff.= ".....";
        
        $tlevel[] = array(
            "alevel" => count($tk)
        );
        $tref[] = array(
            "eref" => str_replace("-dot-", ".", array_pop($tk))
        );
        $vlabel = $tv;
        $tlabel[] = array(
            "elabel" => htmlspecialchars($vlabel) ,
            "velabel" => htmlspecialchars($loff . $vlabel)
        );
    }
    
    $action->lay->setBlockData("ALEVEL", $tlevel);
    $action->lay->setBlockData("AREF", $tref);
    $action->lay->setBlockData("ALABEL", $tlabel);
    $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/GENERIC/Layout/generic_editcatg.js");
}
?>
