<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Modify item os enumerate attributes
 *
 * @author Anakeen
 * @version $Id: generic_modkind.php,v 1.8 2008/08/14 09:59:14 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocAttr.php");
include_once ("FDL/Lib.Attr.php");
include_once ("GENERIC/generic_util.php");
// -----------------------------------
function generic_modkind(Action & $action)
{
    // -----------------------------------
    $dbaccess = $action->dbaccess;
    
    $aid = GetHttpVars("aid"); // attribute id
    $famid = GetHttpVars("fid"); // family id
    $tlevel = GetHttpVars("alevel"); // levels
    $tref = GetHttpVars("aref"); // references
    $tlabel = GetHttpVars("alabel"); // label
    $tsref = array();
    $tsenum = array();
    $ref = "";
    $ple = 1;
    if (is_array($tref)) {
        while (list($k, $v) = each($tref)) {
            $le = intval($tlevel[$k]);
            if ($le == 1) {
                $ref = '';
            } else if ($ple < $le) {
                // add level ref index
                $ref = $ref . str_replace(".", "-dot-", $tref[$k - 1]) . '.';
            } else if ($ple > $le) {
                // suppress one or more level ref index
                for ($l = 0; $l < $ple - $le; $l++) {
                    $ref = substr($ref, 0, strrpos($ref, '.') - 1);
                }
            }
            $ple = $le;
            $tsenum[stripslashes($ref . str_replace(".", "-dot-", $v)) ] = $tlabel[$k];
        }
    }
    
    $attr = new DocAttr($dbaccess, array(
        $famid,
        $aid
    ));
    if ($attr->isAffected()) {
        
        $oe = new DocEnum($action->dbaccess);
        $oe->savePoint("enum");
        $oe->exec_query(sprintf("delete from docenum where famid=%d and attrid='%s'", $famid, $aid));
        $oe->famid = $famid;
        $oe->attrid = $aid;
        $oe->eorder = 0;
        foreach ($tsenum as $key => $label) {
            if (strpos($key, '.') === false) {
                $oe->key = str_replace('-dot-', '.', $key);
                $oe->parentkey = '';
            } else {
                $keys = explode('.', $key);
                $oe->key = str_replace('-dot-', '.', array_pop($keys));
                $oe->parentkey = str_replace('-dot-', '.', array_pop($keys));
            }
            $oe->eorder++;
            $oe->label = $label;
            if (!$oe->exists()) $oe->add();
            else $oe->modify();
        }
        $oe->commitPoint("enum");
    } else {
        $action->exitError(sprintf(_("Cannot update enum. Attribute '%s'[family %s] not found") , $aid, $famid));
    }
    
    $fdoc = new_doc($dbaccess, $famid);
    /**
     * @var NormalAttribute $a;
     */
    $a = $fdoc->getAttribute($aid);
    if ($a) {
        $enum = $a->getenum();
        $tvkind = array();
        foreach ($enum as $kk => $ki) {
            $klabel = $a->getEnumLabel($ki);
            //array_pop(explode('/',$ki,substr_count($kk, '.')+1));
            $tvkind[] = array(
                "ktitle" => $klabel,
                "level" => substr_count($kk, '.') * 20,
                "kid" => $kk
            );
        }
        
        $action->lay->SetBlockData("vkind", $tvkind);
    }
    
    $action->lay->Set("desc", sprintf(_("Modification for attribute %s for family %s") , $a->getLabel() , $fdoc->title));
}
