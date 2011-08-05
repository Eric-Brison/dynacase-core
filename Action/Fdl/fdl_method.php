<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Apply document methods
 *
 * @author Anakeen 2000
 * @version $Id: fdl_method.php,v 1.8 2008/12/12 14:38:29 marc Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
function fdl_method(&$action)
{
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $docid = GetHttpVars("id", 0);
    $method = GetHttpVars("method");
    $zone = GetHttpVars("zone");
    $noredirect = (strtolower(substr(GetHttpVars("redirect") , 0, 1)) == "n");
    
    $doc = new_Doc($dbaccess, $docid);
    
    if ($doc && $doc->isAlive()) {
        
        $err = $doc->control("view");
        if ($err != "") $action->exitError($err);
        if (!strpos($method, '(')) $method.= '()';
        if (!strpos($method, '::')) $method = '::' . $method;
        $err = $doc->ApplyMethod($method);
    }
    
    if ($err != "") $action->AddWarningMsg($err);
    $action->AddLogMsg(sprintf(_("method %s executed for %s ") , $method, $doc->title));
    
    if (!$noredirect) {
        if ($zone) $opt = "&zone=$zone";
        if ($location = $_SERVER["HTTP_REFERER"]) {
            Header("Location: $location");
            exit;
        } else {
            redirect($action, "FDL", sprintf("FDL_CARD%s&id=%d", $opt, $doc->id));
        }
    } else $action->lay->template = sprintf(_("method %s applied to document %s #%d") , $method, $doc->title, $doc->id);
}
?>
