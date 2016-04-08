<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen
 * @version $Id: fckdocattr.php,v 1.1 2008/12/12 17:46:48 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.DocFam.php");

function fckdocattr(Action & $action)
{
    
    $docid = GetHttpVars("famid");
    $dbaccess = $action->dbaccess;
    $doc = new_Doc($dbaccess, $docid);
    if ($doc->isAlive()) {
        $tatt = array();
        $listattr = $doc->GetNormalAttributes();
        foreach ($listattr as $k => $v) {
            $tatt[$k] = array(
                "aid" => "[V_" . strtoupper($k) . "]",
                "alabel" => str_replace('"', '\\"', $v->getLabel())
            );
        }
        $listattr = $doc->GetFileAttributes();
        foreach ($listattr as $k => $v) {
            if ($v->type == "image") {
                $tatt[$k] = array(
                    "aid" => "<img src='[V_" . strtoupper($k) . "]'>",
                    "alabel" => str_replace('"', '\\"', $v->getLabel())
                );
            } else {
                $tatt[$k] = array(
                    "aid" => "<a href='[V_" . strtoupper($k) . "]'>" . $v->getLabel() . "</a>",
                    "alabel" => str_replace('"', '\\"', $v->getLabel())
                );
            }
        }
        
        $action->lay->set("DOCATTR", json_encode($tatt));
    }
}
