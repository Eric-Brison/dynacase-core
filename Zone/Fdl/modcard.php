<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Modification of document
 *
 * @author Anakeen
 * @version $Id: modcard.php,v 1.111 2009/01/12 12:11:42 eric Exp $
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.Doc.php");
include_once ("FDL/Class.DocAttr.php");
include_once ("FDL/freedom_util.php");
include_once ("FDL/Lib.Vault.php");
include_once ("VAULT/Class.VaultFile.php");
include_once ("Lib.FileMime.php");
/**
 * Modify a document
 * @param Action $action
 * @param int $ndocid
 * @param array $info
 * @return string
 */
function modcard(Action & $action, &$ndocid, &$info = array())
{
    // modify a card values from editcard
    // -----------------------------------
    // Get all the params
    $docid = GetHttpVars("id", 0);
    $classid = GetHttpVars("classid", 0);
    $usefor = GetHttpVars("usefor"); // use for default values for a document
    $vid = GetHttpVars("vid"); // special controlled view
    $noredirect = (GetHttpVars("noredirect")); // true  if return need edition
    $quicksave = (GetHttpVars("quicksave") == "1"); // true  if return need edition
    $force = (GetHttpVars("fstate", "no") == "yes"); // force change
    $dbaccess = $action->dbaccess;
    $ndocid = $docid;
    
    global $_POST;
    if (count($_POST) == 0) return sprintf(_("Document cannot be created.\nThe upload size limit is %s bytes.") , ini_get('post_max_size'));
    
    if (($usefor == "D") || ($usefor == "Q")) {
        //  set values to family document
        $err = specialmodcard($action, $usefor);
        $ndocid = $classid;
        return $err;
    }
    $commentSubstitute = '';
    if ($docid == 0) {
        // add new document
        // search the good class of document
        $doc = createDoc($dbaccess, $classid);
        if (!$doc) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document") , $classid));
        
        $fdoc = $doc->getFamilyDocument();
        if ($fdoc->control('icreate') != "") $action->exitError(sprintf(_("no privilege to create interactivaly this kind (%s) of document") , $fdoc->title));
        $doc->owner = $action->user->id;
        $doc->locked = 0;
        if ($doc->fromid <= 0) {
            $doc->profid = "0"; // NO PROFILE ACCESS
            
        }
        
        $incumbentName = $action->user->getIncumbentPrivilege($fdoc, 'icreate');
        if ($incumbentName) $commentSubstitute = sprintf(_("(substitute of %s) : ") , $incumbentName);
    } else {
        // initialise object
        $doc = new_Doc($dbaccess, $docid);
        
        $err = $doc->lock(true); // autolock
        if ($err != "") $action->ExitError($err);
        // test object permission before modify values (no access control on values yet)
        $err = $doc->canEdit();
        if ($err != "") $action->ExitError($err);
        $incumbentName = $action->user->getIncumbentPrivilege($doc, 'edit');
        if ($incumbentName) $commentSubstitute = sprintf(_("(substitute of %s) : ") , $incumbentName);
    }
    // apply specified mask
    if (($vid != "") && ($doc->cvid > 0)) {
        // special controlled view
        
        /**
         * @var CVDoc $cvdoc
         */
        $cvdoc = new_Doc($dbaccess, $doc->cvid);
        $cvdoc->Set($doc);
        $err = $cvdoc->control($vid); // control special view
        if ($err != "") $action->exitError($err);
        $tview = $cvdoc->getView($vid);
        $doc->setMask($tview["CV_MSKID"]); // apply mask to avoid modification of invisible attribute
        
    } else if ($doc->cvid > 0) {
        $doc->setMask($doc::USEMASKCVEDIT);
    }
    // ------------------------------
    $err = setPostVars($doc, $info);
    
    if ((!$noredirect) && ($err != "")) {
        $action->Addwarningmsg($err);
    }
    if (!$err) {
        // verify preStore.
        $err = $doc->preStore();
        if (!$err) {
            if (($action->getArgument("noconstraint") != "Y") || ($action->user->id != 1)) {
                $err = $doc->verifyAllConstraints(false, $info);
            }
        }
    }
    // verify attribute constraint
    if ($err && !$noredirect) {
        // redirect to edit action
        //get action where to redirect
        $eapp = getHttpVars("eapp");
        $eact = getHttpVars("eact");
        $eparams = getHttpVars("eparams");
        $appl = $action->parent;
        if ($eapp) {
            $appl->Set($eapp, $action->parent->parent);
            $action->set($eact, $appl);
            if ($eparams) {
                $eparams = explode('&', $eparams);
                foreach ($eparams as $eparam) {
                    $eparam = explode('=', $eparam);
                    setHttpVar($eparam[0], $eparam[1]);
                }
            }
        } else {
            if ($appl->name != "GENERIC") {
                global $core;
                $appl->Set("GENERIC", $core);
            }
            $action->Set("GENERIC_EDIT", $appl);
        }
        setHttpVar("zone", getHttpVars("ezone"));
        setHttpVar("viewconstraint", "Y");
        $action->addWarningMsg(_("Some constraint attribute are not respected.\nYou must correct these values before save document."));
        $action->addWarningMsg($err);
        echo ($action->execute());
        exit;
    }
    if ($err == "") {
        try {
            if ($docid == 0) {
                // now can create new doc
                $err = $doc->Add();
                if ($err != "") {
                    if ($noredirect) {
                        //$action->addWarningMsg($err);
                        return $err;
                    } else {
                        $action->ExitError($err);
                    }
                }
                $doc->disableEditControl(); // in case of dynamic profil from computed attributes
                $doc->initid = $doc->id; // it is initial doc
                $ndocid = $doc->id;
            }
            
            $doc->lmodify = 'Y'; // locally modified
            $ndocid = $doc->id;
            if (!$quicksave) { // else quick save
                $doc->refresh();
                
                if (needRefreshRn($doc)) $doc->refreshRn(); // hasNewFiles set by insertFile below
                $msg = $doc->postStore();
                if ($msg) $action->addWarningMsg($msg);
                // add trace to know when and who modify the document
                if ($docid == 0) {
                    //$doc->Addcomment(_("creation"));
                    
                } else {
                    $olds = $doc->getOldRawValues();
                    if (is_array($olds)) {
                        $keys = array();
                        foreach ($olds as $ka => $va) {
                            $oa = $doc->getAttribute($ka);
                            $keys[] = $oa->getLabel();
                        }
                        $skeys = implode(", ", $keys);
                        $doc->addHistoryEntry($commentSubstitute . sprintf(_("change %s") , $skeys) , HISTO_INFO, "MODIFY");
                    } else {
                        $doc->addHistoryEntry($commentSubstitute . _("change") , HISTO_INFO, "MODIFY");
                    }
                }
                if ($err == "") {
                    $err.= $doc->Modify();
                }
                // if ( $docid == 0 ) $err=$doc-> PostCreated();
                $doc->unlock(true); // disabled autolock
                if (($err == "") && ($doc->doctype != 'T')) {
                    // change state if needed
                    $newstate = GetHttpVars("newstate", "");
                    $comment = GetHttpVars("comment", "");
                    
                    $err = "";
                    
                    if (($newstate != "") && ($newstate != "-")) {
                        
                        if ($doc->wid > 0) {
                            if ($newstate != "-") {
                                /**
                                 * @var WDoc $wdoc
                                 */
                                $wdoc = new_Doc($dbaccess, $doc->wid);
                                
                                $wdoc->Set($doc);
                                $wdoc->disableEditControl(); // only to pass ask parameters
                                setPostVars($wdoc, $info); // set for ask values
                                $wdoc->enableEditControl();
                                $err = $wdoc->ChangeState($newstate, $comment, $force);
                            }
                        }
                    } else {
                        // test if auto revision
                        $fdoc = $doc->getFamilyDocument();
                        
                        if ($fdoc->schar == "R") {
                            $doc->revise(sprintf("%s : %s", _("auto revision") , $comment));
                        } else {
                            if ($comment != "") $doc->addHistoryEntry($commentSubstitute . $comment);
                        }
                    }
                    $ndocid = $doc->id;
                }
            } else {
                // just quick save
                if ($err == "") {
                    $err.= $doc->Modify();
                }
            }
        }
        catch(Exception $e) {
            $err = $e->getMessage();
        }
    }
    
    if (!$err) {
        if ($info) {
            foreach ($info as $k => $v) {
                if ($v["err"] != "") $err = $v["err"];
            }
        }
        // add events for  folders
        $fdlids = $doc->getParentFolderIds();
        foreach ($fdlids as $fldid) {
            $action->AddActionDone("MODFOLDERCONTAINT", $fldid);
        }
    }
    return $err;
}

function setPostVars(Doc & $doc, &$info = array())
{
    // update POSGRES text values
    global $_POST;
    global $_FILES;
    $err = "";
    
    foreach ($_POST as $k => $v) {
        
        if ($k[0] == "_") // document attributes  begin with  _
        {
            
            $attrid = substr($k, 1);
            if (is_array($v)) {
                if (isset($v["-1"])) unset($v["-1"]);
                if (isset($v["__1x_"])) unset($v["__1x_"]);
                
                if ((count($v) == 0)) $value = " "; // delete column
                else $value = $v;
                //$value = array_values($value);
                if (count($v) == 1 && $v[0] === '' && (count($doc->getMultipleRawValues($attrid)) == 0)) {
                    $value = '';
                    $oa = $doc->getAttribute($attrid);
                    if ($oa && $oa->fieldSet && $oa->fieldSet->type == "array") {
                        /**
                         * @var NormaLAttribute $oa
                         */
                        $arrayAids = $doc->attributes->getArrayElements($oa->fieldSet->id);
                        // detect if it is a empty row => in this case
                        $canCut = true;
                        foreach ($arrayAids as $taid) {
                            if (!empty($_POST['_' . $taid->id][0])) {
                                $canCut = false;
                                break;
                            }
                        }
                        if ($canCut) $value = '';
                    }
                }
            } else $value = $v;
            
            if ($value == "") $doc->SetValue($attrid, DELVALUE);
            else {
                $kerr = '';
                $seterr = $doc->SetValue($attrid, $value, -1, $kerr);
                if ($seterr) {
                    $oa = $doc->getAttribute($attrid);
                    if ($oa) {
                        $info[$oa->id] = array(
                            "id" => $oa->id,
                            "err" => $seterr
                        );
                        if ($oa->inArray()) {
                            $info[$oa->id]["index"] = $kerr;
                        }
                        $ola = $oa->getLabel();
                        $err.= sprintf("%s : %s\n", $ola, $seterr);
                    }
                }
            }
        }
    }
    // ------------------------------
    // update POSGRES files values
    foreach ($_FILES as $k => $v) {
        if ($k[0] == "_") // freedom attributes  begin with  _
        {
            $k = substr($k, 1);
            
            $filename = insert_file($doc, $k);
            if ($filename != "") {
                if (substr($k, 0, 4) == "UPL_") $k = substr($k, 4);
                $doc->SetValue($k, $filename);
            }
        }
    }
    // delete first empty row
    $ta = $doc->getNormalAttributes();
    foreach ($ta as $k => $v) {
        if ($v->type == "array") {
            $tv = $doc->getArrayRawValues($v->id);
            if (count($tv) == 1) {
                $fv = current($tv);
                $vempty = true;
                foreach ($fv as $fk => $fvv) {
                    if ($fvv) {
                        $vempty = false;
                        break;
                    }
                }
                if ($vempty) {
                    $doc->removeArrayRow($v->id, 0);
                }
            }
        }
    }
    return $err;
}
/**
 * insert file in VAULT from HTTP upload
 * @param Doc $doc
 * @param $attrid
 * @param bool $strict
 * @return array|string
 * @throws \Dcp\Core\Exception
 */
function insert_file(Doc & $doc, $attrid, $strict = false)
{
    /**
     * @var Action $action
     */
    global $action;
    global $_FILES;
    
    if ($strict) $postfiles = $_FILES[$attrid];
    else $postfiles = $_FILES["_" . $attrid];
    $oa = $doc->getAttribute(substr($attrid, 4));
    $oriid = "IFORI_" . substr($attrid, 4);
    $orinames = getHttpVars($oriid); // when use preventfilechange option
    if (is_array($postfiles['tmp_name'])) { // array of file
        /*
         * Transpose:
         *
         * $postfiles = array(
         *     'tmp_name' => array(
         *         0 => '/tmp/aaa',
         *         [...],
         *         N => '/tmp/nnn'
         *      ),
         *      [...],
         *     'error' => array(
         *         0 => 0,
         *         [...],
         *         N => 0
         *      )
         * );
         *
         * into:
         *
         * $tuserfiles = array(
         *     0 => array(
         *         'tmp_name' => '/tmp/aaa',
         *         [...],
         *         'error' => 0
         *     ),
         *     [...],
         *     N => array(
         *         'tmp_name' => '/tmp/nnn',
         *         [...],
         *         'error' => 0
         *     )
         * );
         *
        */
        $tuserfiles = array();
        foreach ($postfiles as $kp => $v) {
            foreach ($v as $k => $ufv) {
                if ($k >= 0) {
                    $tuserfiles[$k][$kp] = $ufv;
                    if (!empty($orinames[$k])) {
                        if (!$tuserfiles[$k]["realname"]) {
                            $tuserfiles[$k]["realname"] = $tuserfiles[$k]["name"];
                            $tuserfiles[$k]["name"] = $orinames[$k];
                        }
                    }
                    if ($oa) $tuserfiles[$k]["oldvalue"] = $doc->getMultipleRawValues($oa->id, "", $k);
                }
            }
        }
    } else { // only one file
        if ($orinames) {
            $postfiles["realname"] = $postfiles["name"];
            $postfiles["name"] = $orinames;
        }
        if ($oa) $postfiles["oldvalue"] = $doc->getRawValue($oa->id);
        $tuserfiles[] = $postfiles;
    }
    
    $rtold = array(); // array of file to be returned
    if ($doc) $rtold = $doc->rawValueToArray($doc->getOldRawValue(substr($attrid, 4))); // special in case of file modification by DAV in revised document
    $oa = $doc->getAttribute(substr($attrid, 4));
    $rt = $doc->getMultipleRawValues($attrid); // in case of modified only a part of array files
    unset($tuserfiles['__1x_']);
    
    foreach ($tuserfiles as $k => $userfile) {
        $rt[$k] = "";
        if ($userfile['name'] == " ") {
            $rt[$k] = " "; // delete reference file
            continue;
        }
        
        if ($userfile['error'] != UPLOAD_ERR_OK) {
            if ($userfile['error'] == UPLOAD_ERR_NO_FILE) {
                /**
                 * In our case, an empty input field is not an error but it
                 * means that the input field value has not been changed.
                 *
                 * So, UPLOAD_ERR_NO_FILE is not a real error, but a special
                 * case in our handling of uploaded files.
                 */
                // if no file specified, keep current file
                // reuse old value
                if (substr($attrid, 0, 3) == "UPL") {
                    $oldfile = getHttpVars(substr($attrid, 3));
                    if (!is_array($oldfile)) {
                        $vid1 = 0;
                        $vid2 = 0;
                        if (isset($rtold[0]) && preg_match(PREGEXPFILE, $rtold[0], $reg)) {
                            $vid1 = $reg[2];
                        }
                        if (preg_match(PREGEXPFILE, $oldfile, $reg)) {
                            $vid2 = $reg[2];
                        }
                        
                        if (($vid1 > 0) && ($vid2 > 0) && ($vid1 > $vid2)) {
                            $rt[$k] = $rtold[0];
                        } // in case of DAV auto clone when revised doc
                        else {
                            $rt[$k] = $oldfile;
                        }
                    } else {
                        if (isset($oldfile[$k])) {
                            //	      print "RECENT $oldfile[$k] :<b>".searchmorerecent($rtold,$oldfile[$k])."</b><br>";
                            $recent = searchmorerecent($rtold, $oldfile[$k]);
                            if ($recent) $rt[$k] = $recent;
                            else $rt[$k] = $oldfile[$k];
                        }
                    }
                }
            } else {
                /**
                 * Other codes are "real" errors and must be reported.
                 */
                switch ($userfile['error']) {
                    case UPLOAD_ERR_INI_SIZE: // 1
                        $err = sprintf(_("Filename '%s' cannot be transmitted.\nThe Size Limit is %s bytes.") , $userfile['name'], ini_get('upload_max_filesize'));
                        break;

                    case UPLOAD_ERR_FORM_SIZE: // 2
                        $err = sprintf(_("Filename '%s' cannot be transmitted.\nThe Size Limit was specified in the HTML form.") , $userfile['name']);
                        break;

                    case UPLOAD_ERR_PARTIAL: // 3
                        $err = sprintf(_("Filename '%s' cannot be transmitted completly.\nMay be saturation of server disk.") , $userfile['name']);
                        break;

                    case UPLOAD_ERR_NO_TMP_DIR: // 6
                        $err = sprintf(_("Filename '%s' cannot be transmitted.\nMissing a temporary folder.") , $userfile['name']);
                        break;

                    case UPLOAD_ERR_CANT_WRITE: // 7
                        $err = sprintf(_("Filename '%s' cannot be transmitted.\nFailed to write file to disk.") , $userfile['name']);
                        break;

                    case UPLOAD_ERR_EXTENSION: // 8
                        $err = sprintf(_("Filename '%s' cannot be transmitted.\nA PHP extension stopped the file upload.") , $userfile['name']);
                        break;

                    default:
                        $err = sprintf(_("Filename '%s' cannot be transmitted (%d).") , $userfile['name'], $userfile['error']);
                }
                $action->ExitError($err);
            }
        } else {
            /**
             * Handle successful non-empty input field uploads
             */
            if (file_exists($userfile['tmp_name'])) {
                if (is_uploaded_file($userfile['tmp_name'])) {
                    // move to add extension
                    $fname = rawurldecode($userfile['name']);
                    $err = vault_store($userfile['tmp_name'], $vid, $fname);
                    // read system mime
                    $userfile['type'] = getSysMimeFile($userfile['tmp_name'], $userfile['name']);
                    
                    if ($err != "") {
                        AddWarningMsg($err);
                        $doc->addHistoryEntry(sprintf(_("file %s : %s") , $fname, $err) , HISTO_WARNING);
                    } else {
                        if ($oa && $oa->getOption('preventfilechange') == "yes") {
                            if (preg_match(PREGEXPFILE, $userfile["oldvalue"], $reg)) {
                                $expectname = vault_uniqname($reg[2]);
                                if ($expectname && ($expectname != $userfile["realname"])) {
                                    $ext = substr($expectname, strrpos($expectname, '.'));
                                    $prefix = substr($expectname, 0, strrpos($expectname, '}') + 1);
                                    
                                    $realext = substr($userfile["realname"], strrpos($userfile["realname"], '.'));
                                    $realprefix = substr($userfile["realname"], 0, strrpos($userfile["realname"], '}', strrpos($expectname, '.') - 2) + 1);
                                    
                                    if (($ext != $realext) || ($prefix != $realprefix)) {
                                        $doc->addHistoryEntry(sprintf(_("%s : file %s has been replaced by new file %s") , $oa->getLabel() , $reg[3], $userfile["name"]) , HISTO_WARNING);
                                    }
                                }
                            }
                        }
                        $rt[$k] = $userfile['type'] . "|" . $vid . '|' . $fname; // return file type and upload file name
                        
                    }
                } else {
                    $err = sprintf(_("Possible file upload attack: filename '%s'.") , $userfile['name']);
                    $action->ExitError($err);
                }
            } else {
                $err = sprintf(_("Filename '%s' cannot be transmitted.\nNo file was uploaded.") , $userfile['name']);
                $action->exitError($err);
            }
        }
    }
    
    if ((count($rt) == 0) || ((count($rt) == 1) && (current($rt) == ""))) return "";
    // return file type and upload file name
    return ($rt);
}
function needRefreshRn(Doc & $doc)
{
    $fa = $doc->GetFileAttributes();
    foreach ($fa as $aid => $oa) {
        $rn = $oa->getOption("rn");
        $ov = $doc->getOldRawValue($aid);
        if ($rn && $ov) return true;
    }
    
    return false;
}
function searchmorerecent($rt, $file)
{
    foreach ($rt as $k => $v) {
        if (preg_match(PREGEXPFILE, $v, $reg)) {
            $vid1 = $reg[2];
            $fn1 = $reg[3];
            if (preg_match(PREGEXPFILE, $file, $reg)) {
                $vid2 = $reg[2];
                $fn2 = $reg[3];
                if (($vid1 > 0) && ($vid2 > 0) && ($vid1 > $vid2) && ($fn1 == $fn2)) return $v;
            }
        }
    }
    return false;
}
// -----------------------------------
function specialmodcard(Action & $action, $usefor)
{
    
    global $_POST;
    global $_FILES;
    
    $dbaccess = $action->dbaccess;
    $classid = GetHttpVars("classid", 0);
    /**
     * @var DocFam $cdoc
     */
    $cdoc = new_Doc($dbaccess, $classid); // family doc
    $tmod = array();
    $err = '';
    foreach ($_POST as $k => $v) {
        //print $k.":".$v."<BR>";
        if ($k[0] == "_") // freedom attributes  begin with  _
        {
            $attrid = substr($k, 1);
            if (is_array($v)) {
                if (isset($v["-1"])) unset($v["-1"]);
                if (isset($v["__1x_"])) unset($v["__1x_"]);
                $value = $v;
            } else {
                $value = trim($v);
            }
            if ($cdoc->getAttribute($attrid)) {
                if ($usefor == "D") $cdoc->setDefValue($attrid, $value);
                else if ($usefor == "Q") $err.= $cdoc->setParam($attrid, $value);
                $tmod[$attrid] = $value;
            }
        }
    }
    // ------------------------------
    // update POSGRES files values
    foreach ($_FILES as $k => $v) {
        if ($k[0] == "_") // freedom attributes  begin with  _
        {
            $k = substr($k, 1);
            
            $filename = insert_file($cdoc, $k);
            
            if ($filename != "") {
                if (substr($k, 0, 4) == "UPL_") $k = substr($k, 4);
                if ($usefor == "D") $cdoc->setDefValue($k, $filename);
                else if ($usefor == "Q") $err.= $cdoc->setParam($k, $filename);
                
                $tmod[$k] = $filename;
            }
        }
    }
    
    if (!$err) {
        $cdoc->modify();
        if (count($tmod) > 0) {
            $s = '';
            if ($usefor == "D") $s = _("modify default values :");
            else if ($usefor == "Q") $s = _("modify parameters :");
            $s.= " ";
            foreach ($tmod as $k => $v) {
                if (is_array($v)) {
                    $v = '[' . join(', ', $v) . ']';
                }
                $s.= "$k:$v, ";
            }
            $cdoc->addHistoryEntry($s);
        }
    }
    return $err;
}
/**
 * Check for PHP's max_input_vars corruption.
 *
 * The check is only performed if the action is executed with the variable checkMaxInputVars=yes
 *
 * @param Action $action The action that received the POSTed data
 * @param string $inputNameValue Name and value of last input to detect corruption (default is '__detect_max_input_vars__')
 * @return bool
 */
function check_max_input_vars(Action & $action, $inputNameValue = '__check_max_input_vars__')
{
    if ($action->getArgument('checkMaxInputVars') == 'yes' && $action->getArgument($inputNameValue) != $inputNameValue) {
        return false;
    }
    return true;
}
