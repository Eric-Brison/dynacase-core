<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Import documents
 *
 * @author Anakeen 2000
 * @version $Id: import_file.php,v 1.149 2008/11/14 12:40:07 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/Class.DocFam.php");
include_once ("FDL/Class.DocSearch.php");
include_once ("FDL/Class.Dir.php");
include_once ("FDL/Class.QueryDir.php");
include_once ("FDL/Lib.Attr.php");
include_once ("FDL/Class.DocAttrLDAP.php");

define("ALTSEPCHAR", ' --- ');
define("SEPCHAR", ';');

function add_import_file(Action & $action, $fimport)
{
    if (intval(ini_get("max_execution_time")) < 300) ini_set("max_execution_time", 300);
    $dirid = GetHttpVars("dirid", 10); // directory to place imported doc
    $analyze = (GetHttpVars("analyze", "N") == "Y"); // just analyze
    $policy = GetHttpVars("policy", "update");
    $reinit = GetHttpVars("reinitattr");
    $comma = GetHttpVars("comma", SEPCHAR);
    
    $if = new importDocumentDescription($fimport);
    $if->setImportDirectory($dirid);
    $if->analyzeOnly($analyze);
    $if->setPolicy($policy);
    $if->reinitAttribute($reinit == "yes");
    $if->setComma($comma);
    return $if->import();
}
/**
 * Add a document from csv import file
 * @param string $dbaccess database specification
 * @param array $data  data information conform to {@link Doc::GetImportAttributes()}
 * @param int $dirid default folder id to add new document
 * @param bool $analyze true is want just analyze import file (not really import)
 * @param string $ldir path where to search imported files
 * @param string $policy add|update|keep policy use if similar document
 * @param array $tkey attribute key to search similar documents
 * @param array $prevalues default values for new documents
 * @param array $torder array to describe CSV column attributes
 * @global double Http var : Y if want double title document
 * @return array properties of document added (or analyzed to be added)
 */
function csvAddDoc($dbaccess, $data, $dirid = 10, $analyze = false, $ldir = '', $policy = "add", $tkey = array(
    "title"
) , $prevalues = array() , $torder = array())
{
    // return structure
    $tcr = array(
        "err" => "",
        "msg" => "",
        "specmsg" => "",
        "folderid" => 0,
        "foldername" => "",
        "filename" => "",
        "title" => "",
        "id" => "",
        "values" => array() ,
        "familyid" => 0,
        "familyname" => "",
        "action" => "-"
    );
    // like : DOC;120;...
    $err = "";
    if (is_numeric($data[1])) $fromid = $data[1];
    else $fromid = getFamIdFromName($dbaccess, $data[1]);
    if ($fromid == 0) {
        $tcr["action"] = "ignored";
        $tcr["err"] = sprintf(_("Not a family [%s]") , $data[1]);
        return $tcr;
    }
    $docc = createDoc($dbaccess, $fromid);
    if (!$docc) return;
    
    $msg = ""; // information message
    $docc->fromid = $fromid;
    $tcr["familyid"] = $docc->fromid;
    $tcr["familyname"] = $docc->getTitle($docc->fromid);
    if ($data[2] > 0) {
        $docc->id = $data[2]; // static id
        $docc->initid = $data[2];
    } elseif (trim($data[2]) != "") {
        if (!is_numeric(trim($data[2]))) {
            $docc->name = trim($data[2]); // logical name
            $docid = getIdFromName($dbaccess, $docc->name, $fromid);
            if ($docid > 0) {
                $docc->id = $docid;
                $docc->initid = $docid;
            }
        }
    }
    if ($docc->id > 0) {
        $doc = new_doc($docc->dbaccess, $docc->id, true);
        if (!$doc->isAffected()) $doc = $docc;
    } else {
        $doc = $docc;
    }
    
    if ((intval($doc->id) == 0) || (!$doc->isAffected())) {
        
        $tcr["action"] = "added";
    } else {
        if ($doc->fromid != $fromid) {
            //       $doc = new_Doc($doc->dbaccess,$doc->latestId());
            $tcr["action"] = "ignored";
            $tcr["id"] = $doc->id;
            $tcr["err"] = sprintf(_('not same family %s (%d)') , $doc->getTitle() , $doc->id);
            return $tcr;
        }
        if ($doc->doctype == 'Z') {
            if (!$analyze) $doc->revive();
            $tcr["msg"].= _("restore document") . "\n";
        }
        
        if ($doc->locked == - 1) {
            //       $doc = new_Doc($doc->dbaccess,$doc->latestId());
            $tcr["action"] = "ignored";
            $tcr["id"] = $doc->id;
            $tcr["err"] = _('fixed document');
            return $tcr;
        }
        
        $tcr["action"] = "updated";
        $tcr["id"] = $doc->id;
        $msg.= $err . sprintf(_("update id [%d] ") , $doc->id);
    }
    
    if ($err != "") {
        global $nline, $gerr;
        $tcr["action"] = "ignored";
        $gerr = "\nline $nline:" . $err;
        $tcr["err"] = $err;
        return $tcr;
    }
    
    if (count($torder) == 0) {
        $lattr = $doc->GetImportAttributes();
        $torder = array_keys($lattr);
    } else {
        $lattr = $doc->GetNormalAttributes();
    }
    $iattr = 4; // begin in 5th column
    foreach ($torder as $attrid) {
        if (isset($lattr[$attrid])) {
            $attr = $lattr[$attrid];
            if (isset($data[$iattr]) && ($data[$iattr] != "")) {
                $dv = str_replace(array(
                    '\n',
                    ALTSEPCHAR
                ) , array(
                    "\n",
                    ';'
                ) , $data[$iattr]);
                if (!isUTF8($dv)) $dv = utf8_encode($dv);
                if (($attr->type == "file") || ($attr->type == "image")) {
                    // insert file
                    $tcr["foldername"] = $ldir;
                    $tcr["filename"] = $dv;
                    
                    if (!$analyze) {
                        if ($attr->inArray()) {
                            $tabsfiles = $doc->_val2array($dv);
                            $tvfids = array();
                            foreach ($tabsfiles as $fi) {
                                if (preg_match(PREGEXPFILE, $fi, $reg)) {
                                    $tvfids[] = $fi;
                                } elseif (preg_match('/^http:/', $fi, $reg)) {
                                    $tvfids[] = '';
                                } elseif ($fi) {
                                    $absfile = "$ldir/$fi";
                                    $err = AddVaultFile($dbaccess, $absfile, $analyze, $vfid);
                                    if ($err != "") {
                                        $tcr["err"].= "$err: $fi\n";
                                    } else {
                                        $tvfids[] = $vfid;
                                    }
                                } else {
                                    $tvfids[] = '';
                                }
                            }
                            $err.= $doc->setValue($attr->id, $tvfids);
                        } else {
                            // one file only
                            if (preg_match(PREGEXPFILE, $dv, $reg)) {
                                $doc->setValue($attr->id, $dv);
                                $tcr["values"][$attr->getLabel() ] = $dv;
                            } elseif (preg_match('/^http:/', $dv, $reg)) {
                                // nothing
                                
                            } elseif ($dv) {
                                $absfile = "$ldir/$dv";
                                $err = AddVaultFile($dbaccess, $absfile, $analyze, $vfid);
                                if ($err != "") {
                                    $tcr["err"] = $err;
                                } else {
                                    $tcr["err"] = $doc->setValue($attr->id, $vfid);
                                }
                            }
                        }
                    } else {
                        // just for analyze
                        if ($dv == $doc->getValue($attr->id)) $tcr["values"][$attr->getLabel() ] = ("/no change/");
                        else $tcr["values"][$attr->getLabel() ] = dv;
                    }
                } else {
                    $errv = $doc->setValue($attr->id, $dv);
                    if ($errv) $err.= sprintf("%s:%s.", $attr->id, $errv);
                    if ($doc->getOldValue($attr->id) !== false) $tcr["values"][$attr->getLabel() ] = $dv;
                    else $tcr["values"][$attr->getLabel() ] = ("/no change/");
                }
            }
        }
        $iattr++;
    }
    
    if (($err == "") && (!$analyze)) {
        if (($doc->id > 0) || ($policy != "update")) {
            $err = $doc->preImport();
        }
    }
    // update title in finish
    if (!$analyze) $doc->refresh(); // compute read attribute
    if ($err != "") {
        $tcr["action"] = "ignored";
        $tcr["err"] = $err;
        return $tcr;
    }
    
    if (($doc->id == "") && ($doc->name == "")) {
        switch ($policy) {
            case "add":
                $tcr["action"] = "added"; # N_("added")
                if (!$analyze) {
                    
                    if ($doc->id == "") {
                        // insert default values
                        foreach ($prevalues as $k => $v) {
                            $doc->setValue($k, $v);
                        }
                        $err = $doc->preImport();
                        if ($err != "") {
                            $tcr["action"] = "ignored";
                            $tcr["err"] = sprintf(_("pre-import:%s") , $err);
                            return $tcr;
                        }
                        $err = $doc->Add();
                        $tcr["err"] = $err;
                    }
                    if ($err == "") {
                        $tcr["id"] = $doc->id;
                        $msg.= $err . sprintf(_("add %s id [%d]  ") , $doc->title, $doc->id);
                        $tcr["msg"] = sprintf(_("add %s id [%d]  ") , $doc->title, $doc->id);
                    } else {
                        $tcr["action"] = "ignored";
                    }
                } else {
                    $doc->RefreshTitle();
                    $tcr["msg"] = sprintf(_("%s to be add") , $doc->title);
                }
                break;

            case "update":
                $doc->RefreshTitle();
                $lsdoc = $doc->GetDocWithSameTitle($tkey[0], $tkey[1]);
                // test if same doc in database
                if (count($lsdoc) == 0) {
                    $tcr["action"] = "added";
                    if (!$analyze) {
                        if ($doc->id == "") {
                            // insert default values
                            foreach ($prevalues as $k => $v) {
                                if ($doc->getValue($k) == "") $doc->setValue($k, $v);
                            }
                            $err = $doc->preImport();
                            if ($err != "") {
                                $tcr["action"] = "ignored";
                                $tcr["err"] = sprintf(_("pre-import:%s") , $err);
                                return $tcr;
                            }
                            $err = $doc->Add();
                            $tcr["err"] = $err;
                        }
                        if ($err == "") {
                            $tcr["id"] = $doc->id;
                            $tcr["action"] = "added";
                            $tcr["msg"] = sprintf(_("add id [%d] ") , $doc->id);
                        } else {
                            $tcr["action"] = "ignored";
                        }
                    } else {
                        $tcr["msg"] = sprintf(_("%s to be add") , $doc->title);
                    }
                } elseif (count($lsdoc) == 1) {
                    // no double title found
                    $tcr["action"] = "updated"; # N_("updated")
                    if (!$analyze) {
                        $err = $lsdoc[0]->preImport();
                        if ($err != "") {
                            $tcr["action"] = "ignored";
                            $tcr["err"] = sprintf(_("pre-import:%s") , $err);
                            return $tcr;
                        }
                    }
                    $lsdoc[0]->transfertValuesFrom($doc);
                    $doc = $lsdoc[0];
                    $tcr["id"] = $doc->id;
                    if (!$analyze) {
                        if (($data[2] != "") && (!is_numeric(trim($data[2]))) && ($doc->name == "")) {
                            $doc->name = $data[2];
                        }
                        $tcr["msg"] = sprintf(_("update %s [%d] ") , $doc->title, $doc->id);
                    } else {
                        $tcr["msg"] = sprintf(_("to be update %s [%d] ") , $doc->title, $doc->id);
                    }
                } else {
                    //more than one double
                    $tcr["action"] = "ignored"; # N_("ignored")
                    $tcr["err"] = sprintf(_("too many similar document %s <B>ignored</B> ") , $doc->title);
                    $msg.= $err . $tcr["err"];
                    return $tcr;
                }
                
                break;

            case "keep":
                $doc->RefreshTitle();
                $lsdoc = $doc->GetDocWithSameTitle($tkey[0], $tkey[1]);
                if (count($lsdoc) == 0) {
                    $tcr["action"] = "added";
                    if (!$analyze) {
                        if ($doc->id == "") {
                            // insert default values
                            foreach ($prevalues as $k => $v) {
                                if ($doc->getValue($k) == "") $doc->setValue($k, $v);
                            }
                            $err = $doc->Add();
                        }
                        $tcr["id"] = $doc->id;
                        $msg.= $err . sprintf(_("add id [%d] ") , $doc->id);
                    } else {
                        $tcr["msg"] = sprintf(_("%s to be add") , $doc->title);
                    }
                } else {
                    //more than one double
                    $tcr["action"] = "ignored";
                    $tcr["err"] = sprintf(_("similar document %s found. keep similar") , $doc->title);
                    $msg.= $err . $tcr["err"];
                    return $tcr;
                }
                
                break;
            }
        } else {
            // add special id
            if (!$doc->isAffected()) {
                $tcr["action"] = "added";
                if (!$analyze) {
                    // insert default values
                    foreach ($prevalues as $k => $v) {
                        if ($doc->getValue($k) == "") $doc->setValue($k, $v);
                    }
                    $err = $doc->preImport();
                    if ($err != "") {
                        $tcr["action"] = "ignored";
                        $tcr["err"] = sprintf(_("pre-import:%s") , $err);
                        return $tcr;
                    }
                    $err = $doc->Add();
                    
                    $tcr["id"] = $doc->id;
                    $msg.= $err . sprintf(_("add %s id [%d]  ") , $doc->title, $doc->id);
                    $tcr["msg"] = sprintf(_("add %s id [%d]  ") , $doc->title, $doc->id);
                } else {
                    $doc->RefreshTitle();
                    $tcr["id"] = $doc->id;
                    $tcr["msg"] = sprintf(_("%s to be add") , $doc->title);
                }
            }
        }
        
        $tcr["title"] = $doc->title;
        if (!$analyze) {
            if ($doc->isAffected()) {
                $tcr["specmsg"] = $doc->Refresh(); // compute read attribute
                $err = $doc->PostModify(); // compute read attribute
                if ($err == "") $err = $doc->modify();
                if ($err == "-") $err = ""; // not really an error add addfile must be tested after
                if ($err == "") {
                    $doc->AddComment(sprintf(_("updated by import")));
                    $msg.= $doc->postImport();
                }
                $tcr["err"].= $err;
                $tcr["msg"].= $msg;
            }
        }
        //------------------
        // add in folder
        if (($err == "") && ($data[3] != "-")) {
            $msg.= $doc->title;
            if (is_numeric($data[3])) $ndirid = $data[3];
            else $ndirid = getIdFromName($dbaccess, $data[3], 2);
            
            if ($ndirid > 0) { // dirid
                $dir = new_Doc($dbaccess, $ndirid);
                if ($dir->isAffected()) {
                    $tcr["folderid"] = $dir->id;
                    $tcr["foldername"] = dirname($ldir) . "/" . $dir->title;
                    if (!$analyze) {
                        if ($dir->isAlive() && method_exists($dir, "AddFile")) {
                            $tcr["err"].= $dir->AddFile($doc->id);
                        }
                    }
                    $tcr["msg"].= $err . " " . sprintf(_("and add in %s folder ") , $dir->title);
                }
            } else if ($ndirid == 0) {
                if ($dirid) {
                    
                    $dir = new_Doc($dbaccess, $dirid);
                    if ($dir->isAlive() && method_exists($dir, "AddFile")) {
                        $tcr["folderid"] = $dir->id;
                        $tcr["foldername"] = dirname($ldir) . "/" . $dir->title;
                        if (!$analyze) {
                            if ($dir->isAlive() && method_exists($dir, "AddFile")) {
                                $tcr["err"].= $dir->AddFile($doc->id);
                            }
                        }
                        $tcr["msg"].= $err . " " . sprintf(_("and add in %s folder ") , $dir->title);
                    }
                }
            }
        }
        
        return $tcr;
    }
    
    function AddImportLog($msg)
    {
        global $action;
        if ($action->lay) {
            $tmsg = $action->lay->GetBlockData("MSG");
            $tmsg[] = array(
                "msg" => $msg
            );
            $action->lay->SetBlockData("MSG", $tmsg);
        } else {
            print "\n$msg";
        }
    }
    
    function getOrder($orderdata)
    {
        return array_map("trim", array_slice($orderdata, 4));
    }
    
    function AddVaultFile($dbaccess, $path, $analyze, &$vid)
    {
        global $importedFiles;
        
        $path = str_replace("//", "/", $path);
        // return same if already imported (case of multi links)
        if (isset($importedFiles[$path])) {
            $vid = $importedFiles[$path];
            return "";
        }
        
        $absfile2 = str_replace('"', '\\"', $path);
        // $mime=mime_content_type($absfile);
        $mime = trim(shell_exec(sprintf("file -ib %s", escapeshellarg($absfile2))));
        if (!$analyze) {
            $vf = newFreeVaultFile($dbaccess);
            $err = $vf->Store($path, false, $vid);
        }
        if ($err != "") {
            
            AddWarningMsg($err);
            return $err;
        } else {
            $base = basename($path);
            $importedFiles[$path] = "$mime|$vid|$base";
            $vid = "$mime|$vid|$base";
            
            return "";
        }
        return false;
    }
    function seemsODS($filename)
    {
        if (preg_match('/\.ods$/', $filename)) return true;
        $sys = trim(shell_exec(sprintf("file -bi %s", escapeshellarg($filename))));
        if ($sys == "application/x-zip") return true;
        if ($sys == "application/vnd.oasis.opendocument.spreadsheet") return true;
        return false;
    }
    /**
     * convert ods file in csv file
     * the csv file must be delete by caller after using it
     * @return strint the path to the csv file
     */
    function ods2csv($odsfile)
    {
        $csvfile = uniqid(getTmpDir() . "/csv") . "csv";
        $wsh = getWshCmd();
        $cmd = sprintf("%s --api=ods2csv --odsfile=%s --csvfile=%s >/dev/null", getWshCmd() , escapeshellarg($odsfile) , escapeshellarg($csvfile));
        $err = system($cmd, $out);
        if ($err === false) return false;
        return $csvfile;
    }
?>
