<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: freedom_editstate.php,v 1.5 2005/06/28 08:37:46 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Class.Doc.php");

function freedom_editstate(Action & $action)
{
    $dbaccess = $action->dbaccess;
    $docid = GetHttpVars("id", 0);
    
    $doc = new_Doc($dbaccess, $docid);
    $action->lay->Set("docid", $doc->id);
    $action->lay->Set("title", $doc->getHTMLTitle());
    
    $action->lay->set("tablehead", "tableborder");
    
    if ($action->Read("navigator", "") == "NETSCAPE") {
        if (preg_match("/rv:([0-9.]+).*/", $_SERVER['HTTP_USER_AGENT'], $reg)) {
            if (floatval($reg[1] >= 1.6)) {
                $action->lay->set("tablehead", "tablehead");
            }
        }
    }
}
