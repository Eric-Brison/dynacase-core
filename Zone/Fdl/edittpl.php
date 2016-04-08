<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Edit special template
 *
 * @author Anakeen
 * @version $Id: fdl_card.php,v 1.42 2008/12/02 15:20:52 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Dir.php");
/**
 * Edit special template for an attribute
 *
 * @param Action &$action current action
 * @global id int Http var : document identifier to see
 * @global famid int Http var :
 * @global zone string Http var : zone
 */
function edittpl(Action & $action)
{
    $zone = $action->getArgument("zone");
    $docid = $action->getArgument("id");
    $famid = $action->getArgument("famid");
    if (!$zone) $action->addWarningMsg(_("no template defined"));
    if ((!$docid) && (!$famid)) $action->addWarningMsg(sprintf(_("template %s no document found") , $zone));
    $dbaccess = $action->dbaccess;
    
    $reg = Doc::parseZone($zone);
    if ($reg === false) {
        return sprintf(_("error in pzone format %s") , $zone);
    }
    
    if (array_key_exists('argv', $reg)) {
        foreach ($reg['argv'] as $k => $v) {
            setHttpVar($k, $v);
        }
    }
    if ($docid) {
        $doc = new_doc($dbaccess, $docid);
    } else {
        $doc = createDoc($dbaccess, $famid);
        fdl_setHttpVars($doc);
    }
    if ($doc) {
        $zonefile = $doc->getZoneFile($zone);
        if (!$zonefile) {
            addWarningMsg(sprintf(_("cannot access edit template file %s") , $zone));
        } else {
            $action->lay = new Layout($zonefile, $action);
            $doc->lay = & $action->lay;
            
            $method = strtok(strtolower($reg['layout']) , '.');
            if (method_exists($doc, $method)) {
                $doc->$method();
            } else {
                $doc->editattr($doc->getZoneOption($zone) == "U");
                $doc->viewprop();
            }
        }
    }
}
