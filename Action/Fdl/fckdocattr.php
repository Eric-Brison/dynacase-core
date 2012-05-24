<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 */
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000
 * @version $Id: fckdocattr.php,v 1.1 2008/12/12 17:46:48 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.DocFam.php");

function fckdocattr(&$action)
{
    
    $docid = GetHttpVars("famid");
    $dbaccess = $action->GetParam("FREEDOM_DB");
    $doc = new_Doc($dbaccess, $docid);
    if ($doc->isAlive()) {
        $listattr = $doc->GetNormalAttributes();
        foreach ($listattr as $k => $v) {
            $tatt[$k] = array(
                "aid" => "[V_" . strtoupper($k) . "]",
                "alabel" => str_replace("'", "\\'", $v->getLabel())
            );
        }
        $listattr = $doc->GetFileAttributes();
        foreach ($listattr as $k => $v) {
            if ($v->type == "image") {
                $tatt[$k] = array(
                    "aid" => "<img src=\"[V_" . strtoupper($k) . "]\">",
                    "alabel" => str_replace("'", "\\'", $v->getLabel())
                );
            } else {
                $tatt[$k] = array(
                    "aid" => "<a href=\"[V_" . strtoupper($k) . "]\">" . str_replace("'", "\\'", $v->getLabel()) . "</a>",
                    "alabel" => str_replace("'", "\\'", $v->getLabel())
                );
            }
        }
        
        $action->lay->setBlockData("ATTR", $tatt);
    }
}
?>