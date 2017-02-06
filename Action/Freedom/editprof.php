<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Profil edition
 *
 * @author Anakeen
 * @version $Id: editprof.php,v 1.21 2007/07/27 07:42:31 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocAttr.php");
include_once ("FDL/Lib.Dir.php");

function editprof(Action & $action)
{
    $dbaccess = $action->dbaccess;
    $docid = GetHttpVars("id", 0);
    $createp = GetHttpVars("create", 0); // 1 if use for create profile (only for familly)
    $action->lay->Set("create", intval($createp));
    
    if ($createp) $action->lay->Set("TITLE", _("change creation profile"));
    else $action->lay->Set("TITLE", _("change profile"));
    $action->lay->Set("NOCREATE", (!$createp));
    
    $doc = new_Doc($dbaccess, $docid);
    // build values type array
    // control view acl
    $err = $doc->Control("viewacl");
    if ($err != "") $action->ExitError($err);
    $action->lay->Set("docid", $doc->id);
    $action->lay->eSet("doctitle", _("new profile document"));
    
    $selectclass = array();
    if (($doc->usefor != "P") && (strstr($doc->usefor, 'W') === false) && ($doc->fromid != 28)) { // cannot redirect profil document (only normal document) also workflow and iw control
        if ($createp) {
            // search from profil of the document family (not the family)
            $tdoc = createDoc($dbaccess, $doc->id);
            $tclassdoc = GetProfileDoc($dbaccess, $doc->id, $tdoc->defProfFamId);
        } else $tclassdoc = GetProfileDoc($dbaccess, $doc->id);
        if (is_array($tclassdoc)) {
            foreach ($tclassdoc as $k => $pdoc) {
                if ($pdoc["id"] != $doc->id) {
                    $selectclass[$k]["idpdoc"] = $pdoc["id"];
                    $selectclass[$k]["profname"] = $pdoc["title"];
                    $selectclass[$k]["selected"] = "";
                }
            }
        }
    }
    
    $nbattr = 0; // if new document
    // display current values
    $newelem = array();
    if ($docid > 0) {
        
        $doc->GetFathersDoc();
        $action->lay->eSet("doctitle", $doc->title);
        
        if ($createp) {
            /**
             * @var DocFam $doc
             */
            $sprofid = abs($doc->cprofid);
        } else {
            $sprofid = abs($doc->profid);
            // select dynamic profil if set
            if ($doc->dprofid != 0) $sprofid = abs($doc->dprofid);
        }
        
        if ($sprofid == $doc->id) $action->lay->Set("selected_spec", "selected");
        else {
            $action->lay->Set("selected_spec", "");
            // selected the current class document
            foreach ($selectclass as $k => $pdoc) {
                //      print $doc->doctype." == ".$selectclass[$k]["idcdoc"]."<BR>";
                if ($sprofid == $selectclass[$k]["idpdoc"]) {
                    $selectclass[$k]["selected"] = "selected";
                }
            }
        }
        
        $action->lay->SetBlockData("SELECTPROF", $selectclass);
    }
    if ((($doc->doctype != 'C') || $createp) && ($doc->doctype != "P") && (strstr($doc->usefor, 'W') === false) && ($doc->fromid != 28)) {
        
        setControlView($action, $doc, $createp);
        $action->lay->set("CV", true);
    } else {
        $action->lay->set("CV", false);
    }
}
/**
 * @param Action $action
 * @param Doc $doc
 * @param bool $createp
 */
function setControlView(&$action, &$doc, $createp = false)
{
    
    $filter = array();
    $chdoc = $doc->GetFromDoc();
    
    $filter[] = GetSqlCond($chdoc, "cv_famid");
    //   if ($doc->doctype=='C') $filter[]="cv_famid=".$doc->id;
    //   else $filter[]="cv_famid=".$doc->fromid;
    $tcv = internalGetDocCollection($doc->dbaccess, 0, 0, 100, $filter, $action->user->id, "TABLE", "CVDOC");
    
    foreach ($tcv as $k => $v) {
        
        $tcv[$k]["selcv"] = "";
        
        if ($createp) {
            /**
             * @var DocFam $doc
             */
            if ($v["id"] == $doc->ccvid) $tcv[$k]["selcv"] = "selected";
        } else {
            if ($v["id"] == $doc->cvid) $tcv[$k]["selcv"] = "selected";
        }
    }
    $action->lay->SetBlockData("SELECTCV", $tcv);
}
