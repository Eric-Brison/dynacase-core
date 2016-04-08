<?php
/*
 * generate form interface for document
 * @author Anakeen
 * @package FDL
*/
/**
 */
include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocAttr.php");
include_once ("FDL/editutil.php");

function editcard(Action & $action)
{
    $docid = GetHttpVars("id", 0); // document to edit
    $classid = GetHttpVars("classid", 0); // use when new doc or change class
    $zonebodycard = GetHttpVars("zone"); // define view action
    $usefor = GetHttpVars("usefor"); // default values for a document
    $vid = GetHttpVars("vid"); // special controlled view
    $mskid = GetHttpVars("mskid"); // special mask
    $dbaccess = $action->dbaccess;
    
    $temporaryMode = $action->getParam("overrideICreate") == "true";
    editmode($action);
    if (!is_numeric($classid)) $classid = getFamIdFromName($dbaccess, $classid);
    $doc = $fdoc = null;
    if (($usefor == "D") && ($zonebodycard == "")) $zonebodycard = "FDL:EDITBODYCARD"; // always default view for default document
    if ($docid == 0) {
        // new document
        if ($classid > 0) {
            if (!$doc) $doc = createDoc($dbaccess, $classid, true, ($usefor != "D"));
            if (!$doc) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document") , $classid));
            $fdoc = new DocFam($dbaccess, $classid);
            if (!$temporaryMode) {
                if ($fdoc->control('icreate') != "") $action->exitError(sprintf(_("no privilege to create interactivaly this kind (%s) of document") , $fdoc->title));
            }
        }
    } else {
        // update document
        $doc = new_Doc($dbaccess, $docid, true);
        $docid = $doc->id;
        if ($doc->isConfidential()) {
            redirect($action, "FDL", "FDL_CONFIDENTIAL&&id=" . $doc->id);
        }
        $classid = $doc->fromid;
        $fdoc = new DocFam($dbaccess, $classid);
    }
    
    if (($usefor == "D") || ($usefor == "Q")) {
        // special edit
        $zonebodycard = "FDL:EDITBODYCARD";
        switch ($usefor) {
            case "D":
                $doc->usefor = 'D';
                $doc->setDefaultValues($fdoc->getDefValues() , false);
                $doc->state = '';
                break;

            case "Q":
                $doc->usefor = 'Q';
                $doc->setDefaultValues($fdoc->getParams() , false);
                $doc->state = '';
                
                $action->parent->addJsRef("FDL:editfamilyparam.js", true);
                break;
        }
    } else {
        // normal edit
        if ($doc->wid > 0) {
            $err = $doc->setMask(0);
            if ($err) addWarningMsg($err);
        }
        /**
         * @var CVDoc $cvdoc
         */
        /*
         *  doc->cvid | vid != '' | mskid != '' || mask to apply
         * -----------+-----------+-------------++-----------------
         *      0     |     0     |      0      || setMask(0)
         *      0     |     0     |      1      || setMask(mskid)
         *      0     |     1     |      0      || (!) Not possible as a vid is the id of a view in doc->cvid
         *      0     |     1     |      1      || (!) Not possible as a vid is the id of a view in doc->cvid
         *      1     |     0     |      0      || setMask(Doc::USEMASKCVEDIT)
         *      1     |     0     |      1      || setMask(mskid)
         *      1     |     1     |      0      || setMask(cvid->vid->mskid) OR setMask(Doc::USEMASKCVEDIT)
         *      1     |     1     |      1      || setMask(cvid->vid->mskid) OR setMask(Doc::USEMASKCVEDIT)
        */
        if ($doc->cvid) {
            
            $cvdoc = new_Doc($dbaccess, $doc->cvid);
            $cvdoc->set($doc);
            if (!$cvdoc->isAlive()) {
                $err = "document not alive";
                $action->exitError("CV:" . $doc->cvid . "\n" . $err);
                return;
            }
            if ($vid != '') {
                $err = $cvdoc->control(trim($vid));
                if ($err != '') {
                    $action->exitError("CV:" . $cvdoc->title . "\n" . $err);
                    return;
                }
                $tview = $cvdoc->getView($vid);
                if (isset($tview['CV_MSKID']) && $tview['CV_MSKID'] != '') {
                    $err = $doc->setMask($tview["CV_MSKID"]);
                    if ($err != '') {
                        addWarningMsg($err);
                    }
                }
                if ($zonebodycard == "") {
                    $zonebodycard = $tview["CV_ZVIEW"];
                }
            } else {
                $vid = $doc->getDefaultView(true, "id");
                setHttpVar("vid", $vid);
                if ($vid && $zonebodycard == "") {
                    $tview = $cvdoc->getView($vid);
                    $zonebodycard = $tview["CV_ZVIEW"];
                }
                
                if ($mskid != '') {
                    /**
                     * Apply specified mask
                     *
                     * @var \Dcp\Family\MASK $mask
                     */
                    $mask = new_Doc($dbaccess, $mskid);
                    if ($mask->isAlive() && $mask->control('view') == '') {
                        $err = $doc->setMask($mask->id);
                        if ($err != '') {
                            addWarningMsg($err);
                        }
                    }
                } else {
                    $doc->setMask(Doc::USEMASKCVEDIT);
                }
            }
        } else {
            if ($vid != '') {
                /* Not possible to have a vid without a doc->cvid */
            } else {
                if ($mskid != '') {
                    /**
                     * Apply specified mask
                     *
                     * @var \Dcp\Family\MASK $mask
                     */
                    $mask = new_Doc($dbaccess, $mskid);
                    if ($mask->isAlive() && $mask->control('view') == '') {
                        $err = $doc->setMask($mask->id);
                        if ($err != '') {
                            addWarningMsg($err);
                        }
                    }
                } else {
                    $doc->setMask(0);
                }
            }
        }
        
        if (GetHttpVars("viewconstraint") == "Y") { // from modcard function if constraint error
            include_once ("FDL/modcard.php");
            setPostVars($doc); // HTTP VARS comes from previous edition
            
        }
        
        $msg = $doc->preEdition();
        if ($zonebodycard == "") {
            if ((!$docid) && $doc->defaultcreate != "") $zonebodycard = $doc->defaultcreate;
            else $zonebodycard = $doc->defaultedit;
        }
    }
    if ($zonebodycard == "") $zonebodycard = "FDL:EDITBODYCARD";
    $action->lay->eSet("classid", $classid);
    $action->lay->eSet("usefor", $usefor);
    
    if ($usefor == "D") {
        setDocDefaultValues($doc);
        // contruct js functions
        $jsfile = $action->GetLayoutFile("editcard.js");
        $jslay = new Layout($jsfile, $action);
        $jslay->Set("attrnid", '[]');
        $jslay->Set("attrntitle", '[]');
        $jslay->SetBlockData("RATTR", array());
        $action->parent->AddJsCode($jslay->gen());
        $action->lay->Set("ZONEBODYCARD", $doc->viewDoc($zonebodycard));
    } else {
        if ($doc->id == 0) {
            if (fdl_setHttpVars($doc)) $doc->refresh();
        }
        if ($usefor === "Q") {
            useOwnParamters($doc);
        }
        setRefreshAttributes($action, $doc);
        $action->lay->Set("ZONEBODYCARD", $doc->viewDoc($zonebodycard));
        setNeededAttributes($action, $doc);
    }
    $action->lay->set("maxFileUpload", ini_get("max_file_uploads"));
    $action->lay->Set("NOFORM", (preg_match("/[A-Z]+:[^:]+:U/", $zonebodycard, $reg)));
    // compute modify condition js
    
}
/**
 * set with own parameters
 * @param Doc $doc
 */
function useOwnParamters(Doc & $doc)
{
    $listattr = $doc->getParamAttributes();
    if (is_a($doc, "DocFam")) {
        $fam = $doc;
    } else {
        $fam = $doc->getFamilyDocument();
    }
    
    $expertMode = getHttpVars("mode") === "expert";
    foreach ($listattr as $aid => $attr) {
        /**
         * @var NormalAttribute $attr
         */
        $defParamValue = $fam->getDefValue($aid);
        if ($attr->type !== "array" && $expertMode) {
            if ($attr->type === "htmltext" || $attr->type === "longtext") {
                $attr->type = "longtext";
            } else {
                $attr->type = "text";
            }
            $attr->format = "";
            $attr->phpfile = '';
            $attr->phpfunc = '';
        }
        if ($defParamValue) {
            $attr->setOption("elabel", _("default value") . ": \n" . $defParamValue);
        }
        $doc->$aid = ''; // delete all value to set only own default values
        
    }
    
    $defVal = $fam->getOwnParams();
    foreach ($defVal as $aid => $value) {
        $doc->$aid = $value; // use raw affect to see method declaration
        $oa = $doc->getAttribute($aid);
        if (Doc::seemsMethod($value)) {
            if ($oa->type === "htmltext" || $oa->type === "longtext") {
                $oa->type = "longtext";
            } else {
                $oa->type = "text";
            }
            $oa->mvisibility = "W";
            $oa->format = "";
            $oa->phpfile = '';
            $oa->phpfunc = '';
        }
    }
}
/**
 * set all attribute in W visibility
 *
 * @param Doc $doc
 */
function setDocDefaultValues(Doc & $doc)
{
    // transform hidden to writted attribut for default document
    $listattr = $doc->GetAttributes();
    foreach ($listattr as $aid => $attr) {
        $attr->setVisibility("W");
        
        if (is_a($attr, "NormalAttribute")) {
            /**
             * @var NormalAttribute $attr
             */
            if ($attr->type == "enum") {
                $attr->setOption("eunset", "yes");
                $attr->setOption("eformat", "auto");
                $attr->setOption("etype", "free");
            } elseif ($attr->type == "array") {
                $attr->setOption("empty", "yes");
            }
            $doc->clearValue($attr->id); // delete all value to set only own default values
            
        }
    }
    if (is_a($doc, "DocFam")) {
        /**
         * @var DocFam $doc
         */
        $defVal = $doc->getOwnDefValues();
    } else {
        $fam = $doc->getFamilyDocument();
        $defVal = $fam->getOwnDefValues();
    }
    foreach ($defVal as $aid => $value) {
        $doc->$aid = $value; // use raw affect to see method declaration
        
    }
}

function setNeededAttributes(Action & $action, Doc & $doc)
{
    if ($doc->usefor == 'Q') {
        $attrn = array();
    } else {
        $attrn = $doc->GetNeededAttributes($doc->usefor == 'Q');
    }
    
    if (count($attrn) == 0) {
        $sattrNid = "[]";
        $sattrNtitle = "[]";
    } else {
        $attrNid = $attrNtitle = array();
        /**
         * @var NormalAttribute $v
         */
        foreach ($attrn as $v) {
            $attrNid[] = $v->id;
            $attrNtitle[] = addslashes($v->getLabel());
        }
        $sattrNid = "['" . implode("','", $attrNid) . "']";
        $sattrNtitle = "['" . implode("','", $attrNtitle) . "']";
    }
    //compute constraint for enable/disable input
    $tjsa = array();
    if ($doc->usefor != "D") {
        /*
        if (GetHttpVars("viewconstraint")!="Y") $doc->Refresh();
        else {
        $err=$doc->SpecRefresh(); // to use addParamRefresh
        $err.=$doc->SpecRefreshGen(true);
        }
        */
        $ka = 0;
        foreach ($doc->paramRefresh as $k => $v) {
            $tjsa[] = array(
                "jstain" => "['" . implode("','", $v["in"]) . "']",
                "jstaout" => "['" . implode("','", $v["out"]) . "']",
                "jska" => "$ka"
            );
            $ka++;
        }
    }
    // contruct js functions
    $jsfile = $action->GetLayoutFile("editcard.js");
    $jslay = new Layout($jsfile, $action);
    $jslay->Set("attrnid", $sattrNid);
    $jslay->Set("attrntitle", $sattrNtitle);
    $jslay->SetBlockData("RATTR", $tjsa);
    $action->parent->AddJsCode($jslay->gen());
}

function setRefreshAttributes(Action & $action, Doc & $doc)
{
    if ($doc->usefor != "D") {
        if ($doc->usefor == "Q") {
            // parameters
            $doc->SpecRefreshGen(true);
        } else {
            $doc->Refresh();
        }
    }
}

function moreone($v)
{
    return (strlen($v) > 1);
}

function cmp_cvorder2($a, $b)
{
    if ($a["cv_order"] == $b["cv_order"]) {
        return 0;
    }
    return ($a["cv_order"] < $b["cv_order"]) ? -1 : 1;
}
/**
 * set values from http var in case of creation of doc
 * values are set only if not set before
 *
 * @param Doc $doc current document to edit
 * @return bool true if , at least, one value is modified
 */
function fdl_setHttpVars(&$doc)
{
    global $_GET, $_POST, $ZONE_ARGS;
    $ismod = false;
    $http = array();
    foreach ($_POST as $k => $v) {
        $http[$k] = $v;
    }
    foreach ($_GET as $k => $v) {
        $http[$k] = $v;
    }
    if (is_array($ZONE_ARGS)) {
        foreach ($ZONE_ARGS as $k => $v) {
            $http[$k] = $v;
        }
    }
    
    foreach ($http as $k => $v) {
        $oa = $doc->getAttribute($k);
        if ($oa) {
            $curVal = $doc->getRawValue($k);
            if (($curVal === "") || ($oa->inArray() && trim($curVal) === "")) {
                if ($oa->inArray() && (!is_array($v))) $v = $doc->rawValueToArray(str_replace('\n', "\n", $v));
                $doc->setValue($k, $v);
                //		print "<br>Set $k to ";print_r($v);
                $ismod = true;
            }
        }
    }
    
    return $ismod;
}
