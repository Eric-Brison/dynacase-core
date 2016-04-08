<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Difference between 2 documents
 *
 * @author Anakeen
 * @version $Id: diffdoc.php,v 1.5 2008/08/14 09:59:14 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Dir.php");
/**
 * Compare 2 documents
 * @param Action &$action current action
 * @global id1 int Http var : first document identifier to compare
 * @global id2 int Http var : second document identifier to compare
 */
function diffdoc(&$action)
{
    $docid1 = GetHttpVars("id1");
    $docid2 = GetHttpVars("id2");
    if (intval($docid1) > intval($docid2)) {
        $docid2 = GetHttpVars("id1");
        $docid1 = GetHttpVars("id2");
    }
    $dbaccess = $action->dbaccess;
    $d1 = new_doc($dbaccess, $docid1);
    if (!$d1->isAffected()) {
        $action->exitError(sprintf(_("Document %s not found") , $docid1));
    }
    $err = $d1->control("view");
    if ($err != "") $action->exitError($err);
    $d2 = new_doc($dbaccess, $docid2);
    if (!$d2->isAffected()) {
        $action->exitError(sprintf(_("Document %s not found") , $docid2));
    }
    $err = $d2->control("view");
    if ($err != "") $action->exitError($err);
    
    if ($d1->fromid != $d2->fromid) $action->exitError(sprintf(_("cannot compare two document which comes from two different family")));
    
    $la = $d1->GetNormalAttributes();
    
    $tattr = array();
    foreach ($la as $k => $a) {
        
        if ($a->type == "array") {
            $v1 = $d1->getArrayRawValues($a->id);
            $v2 = $d2->getArrayRawValues($a->id);
            if ($v1 == $v2) $cdiff = "eq";
            else $cdiff = "ne";
        } else {
            $v1 = $d1->getRawValue($a->id);
            $v2 = $d2->getRawValue($a->id);
            if ($v1 == $v2) $cdiff = "eq";
            else $cdiff = "ne";
        }
        
        if ($a->visibility == "H") $vdiff = "hi";
        else $vdiff = $cdiff;
        
        if (!$a->inArray()) {
            
            switch ($a->type) {
                case "image":
                    $tattr[$a->id] = array(
                        "attname" => htmlspecialchars($a->getLabel() , ENT_QUOTES) ,
                        "v1" => sprintf("<img src=\"%s\">", $d1->getHtmlValue($a, $v1)) ,
                        "v2" => sprintf("<img src=\"%s\">", $d2->getHtmlValue($a, $v2)) ,
                        "cdiff" => htmlspecialchars($cdiff, ENT_QUOTES) ,
                        "vdiff" => htmlspecialchars($vdiff, ENT_QUOTES) ,
                        "EQ" => ($cdiff == "eq")
                    );
                    break;

                default:
                    $tattr[$a->id] = array(
                        "attname" => htmlspecialchars($a->getLabel() , ENT_QUOTES) ,
                        "v1" => $d1->getHtmlValue($a, $v1) ,
                        "v2" => $d2->getHtmlValue($a, $v2) ,
                        "cdiff" => htmlspecialchars($cdiff, ENT_QUOTES) ,
                        "vdiff" => htmlspecialchars($vdiff, ENT_QUOTES) ,
                        "EQ" => ($cdiff == "eq")
                    );
            }
        }
    }
    
    $action->parent->AddJsRef($action->GetParam("CORE_JSURL") . "/subwindow.js");
    $action->parent->AddJsRef($action->GetParam("CORE_PUBURL") . "/FDL/Layout/common.js");
    $action->lay->eset("document1", $d1->title);
    $action->lay->rSet("id1", $d1->id);
    $action->lay->eSet("date1", strftime("%a %d %b %Y %H:%M", $d1->revdate));
    $action->lay->eSet("version1", $d1->version);
    $action->lay->eSet("revision1", $d1->revision);
    
    $action->lay->eSet("document2", $d2->title);
    $action->lay->rSet("id2", $d2->id);
    $action->lay->eSet("date2", strftime("%a %d %b %Y %H:%M", $d2->revdate));
    $action->lay->eSet("version2", $d2->version);
    $action->lay->eSet("revision2", $d2->revision);
    
    $action->lay->rSet("title", sprintf(_("comparison between<br>%s (rev %d) and %s (rev %d)") , $d1->getHTMLTitle() , htmlspecialchars($d1->revision, ENT_QUOTES) , $d2->getHTMLTitle() , htmlspecialchars($d2->revision, ENT_QUOTES)));
    
    $action->lay->setBlockData("ATTRS", $tattr);
}
