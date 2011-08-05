<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Modify a document
 *
 * @author Anakeen 2000
 * @version $Id: modoption.php,v 1.2 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/modcard.php");

include_once ("FDL/Class.DocFam.php");
include_once ("FDL/Class.Dir.php");
// -----------------------------------
function modoption(&$action)
{
    // -----------------------------------
    // Get all the params
    $docid = GetHttpVars("id"); // document id
    $aid = GetHttpVars("aid"); // linked attribute id
    $dbaccess = $action->GetParam("FREEDOM_DB");
    
    $doc = new_Doc($dbaccess, $docid);
    if (!$doc->isAlive()) $action->exitError(sprintf(_("modoption: document [%d] is not alive") , $docid));
    
    $err = setPostVars($doc);
    
    if ($err != "") $action->AddWarningMsg($err);
    else {
        $action->lay->set("aid", $aid);
        $action->lay->set("docid", $docid);
        $listattr = $doc->GetNormalAttributes();
        $vo = "";
        foreach ($listattr as $k => $v) {
            if ($v->usefor == "O") {
                $vo.= "[" . $v->id . "|" . $doc->getValue($v->id) . "]";
            }
        }
        $action->lay->set("vo", $vo);
        $action->lay->set("uuvo", urlencode($vo));
    }
}
?>
