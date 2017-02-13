<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Form to edit or create a document
 *
 * @author Anakeen
 * @version $Id: freedom_edit.php,v 1.44 2008/08/14 09:59:14 eric Exp $
 * @package FDL
 * @subpackage GED
 */
/**
 */

include_once ("GENERIC/generic_edit.php");
/**
 * Edit or create a document
 * @param Action &$action current action
 * @global string $id Http var : document identifier to édit (empty means create)
 * @global string $classid Http var : family identifier use for create
 * @global string $dirid Http var : folder identifier to add when create
 * @global string $usefor Http var : set to  "D" for edit default values
 * @global string $onlysubfam Http var : to show in family list only sub family of classid
 * @global string $alsosubfam Http var : N default (Y|N) in case of only sub fam view also the mother family
 */
function freedom_edit(Action & $action)
{
    // -----------------------------------
    // Get All Parameters
    $docid = GetHttpVars("id", 0); // document to edit
    $classid = GetHttpVars("classid", 0); // use when new doc or change class
    $dirid = GetHttpVars("dirid", 0); // directory to place doc if new doc
    $usefor = GetHttpVars("usefor"); // default values for a document
    $onlysubfam = GetHttpVars("onlysubfam"); // restricy to sub fam of
    $alsosub = (GetHttpVars("alsosubfam", "N") == "Y");
    $type = GetHttpVars("type", "");
    // Set the globals elements
    $dbaccess = $action->dbaccess;
    if (!is_numeric($classid)) $classid = getFamIdFromName($dbaccess, $classid);
    else $classid = abs($classid);
    setHttpVar("classid", $classid);
    
    $tmpDoc = createDoc($action->dbaccess, $classid);
    $isSystemDoc = (is_object($tmpDoc) && substr($tmpDoc->usefor, 0, 1) == 'S');
    unset($tmpDoc);
    $doc = null;
    if ($docid > 0) {
        $doc = new_Doc($dbaccess, $docid);
        if (!$doc->isAlive()) $action->exitError(sprintf(_("document id %d not found") , $docid));
        $fdoc = $doc->getFamilyDocument();
        $tclassdoc[$doc->fromid] = array(
            "id" => $fdoc->id,
            "title" => $fdoc->getTitle()
        );
    } else {
        // new document select special classes
        if ($dirid > 0) {
            /**
             * @var Dir $dir
             */
            $dir = new_Doc($dbaccess, $dirid);
            if (method_exists($dir, "isAuthorized")) {
                if ($dir->locked == - 1) $dir = new_Doc($dbaccess, $dir->getLatestId());
                
                if ($dir->isAuthorized($classid)) {
                    // verify if classid is possible
                    if (($dir->hasNoRestriction()) || (!$classid)) {
                        $tclassdoc = getFamiliesWithTypeOrClassId($dbaccess, $action->user->id, $type, $classid, "TABLE");
                    } else $tclassdoc = $dir->getAuthorizedFamilies();
                } else {
                    $tclassdoc = $dir->getAuthorizedFamilies();
                    $first = current($tclassdoc);
                    $classid = abs($first["id"]);
                    setHttpVar("classid", abs($classid)); // propagate to subzones
                    
                }
            } else {
                $tclassdoc = getFamiliesWithTypeOrClassId($dbaccess, $action->user->id, $type, $classid, "TABLE"); // ($isSystemDoc) ? getSystemFamilies($dbaccess, $action->user->id, "TABLE") : getNonSystemFamilies($dbaccess, $action->user->id, "TABLE);");
                
            }
        } else {
            
            if ($onlysubfam) {
                
                if (!is_numeric($onlysubfam)) $onlysubfam = getFamIdFromName($dbaccess, $onlysubfam);
                $cdoc = new_Doc($dbaccess, $onlysubfam);
                $tsub = $cdoc->GetChildFam($cdoc->id, true);
                if ($alsosub) {
                    $tclassdoc[$classid] = array(
                        "id" => $cdoc->id,
                        "title" => $cdoc->getTitle()
                    );
                    $tclassdoc = array_merge($tclassdoc, $tsub);
                } else {
                    $tclassdoc = $tsub;
                }
                $first = current($tclassdoc);
                if ($classid == "") $classid = $first["id"];
                setHttpVar("classid", abs($classid)); // propagate to subzones
                
            } else {
                $tclassdoc = getFamiliesWithTypeOrClassId($dbaccess, $action->user->id, $type, $classid, "TABLE"); //($isSystemDoc) ? getSystemFamilies($dbaccess, $action->user->id, "TABLE") : getNonSystemFamilies($dbaccess, $action->user->id, "TABLE);");
                
            }
        }
    }
    // when modification
    if (($classid == 0) && ($docid != 0)) $classid = $doc->fromid;
    setHttpVar("forcehead", "yes");
    
    generic_edit($action);
    // build list of class document
    $selectclass = array();
    $k = 0;
    if ($tclassdoc) {
        $first = false;
        foreach ($tclassdoc as $k => $cdoc) {
            if ($cdoc["id"] == $classid) $first = true;
            $selectclass[$k]["idcdoc"] = $cdoc["id"];
            $selectclass[$k]["classname"] = ucfirst(DocFam::getLangTitle($cdoc));
            $selectclass[$k]["selected"] = "";
        }
        if (!$first) {
            reset($tclassdoc);
            $first = current($tclassdoc);
            $classid = $first["id"];
            setHttpVar("classid", abs($classid)); // propagate to subzones
            
        }
    }
    // add no inherit for class document
    if (($docid > 0) && ($doc->doctype == "C")) {
        $selectclass[$k + 1]["idcdoc"] = "0";
        $selectclass[$k + 1]["classname"] = _("no document type");
    }
    if ($docid == 0) {
        switch ($classid) {
            case 2:
                $action->lay->Set("refreshfld", "yes");
                break;

            case 3:
            case 4:
                //$action->lay->Set("TITLE", _("new profile"));
                break;

            default:
                //$action->lay->Set("TITLE", _("new document"));
                
        }
        
        if ($usefor == "D") $action->lay->Set("TITLE", _("default values"));
        if ($classid > 0) {
            $doc = createDoc($dbaccess, $classid); // the doc inherit from chosen class
            if ($doc === false) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document") , $classid));
            // restrict to possible family creation permission
            $tfid = array();
            foreach ($selectclass as $k => $cdoc) {
                $tfid[] = abs($cdoc["idcdoc"]);
            }
            $tfid = getFamilyCreationIds($dbaccess, $action->user->id, $tfid);
            foreach ($selectclass as $k => $cdoc) {
                if (!in_array(abs($cdoc["idcdoc"]) , $tfid)) unset($selectclass[$k]);
            }
        }
        // selected the current class document
        foreach ($selectclass as $k => $cdoc) {
            if ($classid == abs($cdoc["idcdoc"])) {
                $selectclass[$k]["selected"] = "selected";
            }
        }
    } else {
        if (!$doc->isAlive()) $action->ExitError(_("document not referenced"));
        // selected the current class document
        foreach ($selectclass as $k => $cdoc) {
            if ($doc->fromid == abs($selectclass[$k]["idcdoc"])) {
                $selectclass[$k]["selected"] = "selected";
            }
        }
    }
    
    $action->lay->eSet("id", $docid);
    $action->lay->eSet("dirid", $dirid);
    $action->lay->eSet("onlysubfam", $onlysubfam);
    $action->lay->eSet("alsosubfam", GetHttpVars("alsosubfam"));
    if ($docid > 0) $action->lay->Set("doctype", $doc->doctype);
    // sort by classname
    uasort($selectclass, "cmpselect");
    $action->lay->SetBlockData("SELECTCLASS", $selectclass);
    // control view of special constraint button
    $action->lay->Set("boverdisplay", "none");
}
function cmpselect($a, $b)
{
    return strcasecmp($a["classname"], $b["classname"]);
}
function getFamiliesWithTypeOrClassId($dbaccess, $userid, $type, $classid, $qtype)
{
    switch ($type) {
        case 'system':
            return getSystemFamilies($dbaccess, $userid, $qtype);
            break;

        case 'not(system)':
            return getNonSystemFamilies($dbaccess, $userid, $qtype);
            break;
    }
    return GetClassesDoc($dbaccess, $userid, $classid, $qtype);
}
