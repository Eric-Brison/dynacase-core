<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Edit special template
 *
 * @author Anakeen 2000
 * @version $Id: fdl_card.php,v 1.42 2008/12/02 15:20:52 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
 * @global id Http var : document identificator to see
 * @global famid Http var :
 * @global zone Http var : zone
 */
function edittpl(Action & $action)
{
    $zone = $action->getArgument("zone");
    $docid = $action->getArgument("id");
    if (!$docid) $docid = $action->getArgument("famid");
    if (!$zone) $action->addWarningMsg(_("no template defined"));
    if (!$docid) $action->addWarningMsg(sprintf(_("template %s no document found") , $zone));
    $dbaccess = $action->getParam("FREEDOM_DB");
    
    $reg = Doc::parseZone($zone);
    if ($reg === false) {
        return sprintf(_("error in pzone format %s") , $zone);
    }
    
    if (array_key_exists('argv', $reg)) {
        foreach ($reg['argv'] as $k => $v) {
            setHttpVar($k, $v);
        }
    }
    
    $doc = new_doc($dbaccess, $docid);
    if ($doc->isAlive()) {
        $action->lay = new Layout($doc->getZoneFile($zone) , $action);
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
?>