<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/
/**
 * Import single documents
 *
 * @author Anakeen 2000
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/import_file.php");

class importSingleDocument
{
    
    protected $dirid = 0;
    protected $analyze = false;
    protected $importFilePath = '';
    protected $policy = 'add';
    protected $orders = array();
    protected $preValues = array();
    protected $keys = array();
    protected $tcr = array();
    protected $error = array();
    public $dbaccess = '';
    /**
     * @var DocFam
     */
    private $doc;
    
    public function __construct()
    {
        
        $this->dbaccess = getDbAccess();
    }
    
    public function analyzeOnly($analyze)
    {
        $this->analyze = $analyze;
    }
    
    public function setPolicy($policy)
    {
        $this->policy = $policy;
    }
    
    public function setPreValues(array $preValues)
    {
        $this->preValues = $preValues;
    }
    public function setTargetDirectory($dirid)
    {
        $this->dirid = $dirid;
    }
    
    public function setFilePath($path)
    {
        $this->importFilePath = $path;
    }
    public function setOrder(array $order)
    {
        $this->orders = $order;
    }
    
    public function setKey(array $keys)
    {
        $this->keys = $keys;
    }
    
    public function getError()
    {
        return implode("\n", $this->error);
    }
    /**
     * @return bool
     */
    public function hasError()
    {
        return (count($this->error) > 0);
    }
    /**
     * short cut to call ErrorCode::getError
     * @param $code
     * @param null $args
     */
    private function setError($code, $args = null)
    {
        if ($code) {
            $tArgs = array(
                $code
            );
            $nargs = func_num_args();
            for ($ip = 1; $ip < $nargs; $ip++) {
                $tArgs[] = func_get_arg($ip);
            }
            
            $this->error[] = call_user_func_array("ErrorCode::getError", $tArgs);
            $this->tcr["err"] = $this->getError();
            $this->tcr["action"] = "ignored";
        }
    }
    public function import(array $data)
    {
        // return structure
        $this->tcr = array(
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
        else $fromid = getFamIdFromName($this->dbaccess, $data[1]);
        if ($fromid == 0) {
            $this->tcr["action"] = "ignored";
            $this->tcr["err"] = sprintf(_("Not a family [%s]") , $data[1]);
            return $this->tcr;
        }
        $fam = createDoc($this->dbaccess, $fromid);
        if (!$fam) {
            $this->tcr["action"] = "ignored";
            $this->tcr["err"] = sprintf(_("cannot create from family [%s]") , $data[1]);
            return $this->tcr;
        }
        
        $msg = ""; // information message
        $fam->fromid = $fromid;
        $this->tcr["familyid"] = $fam->fromid;
        $this->tcr["familyname"] = $fam->getTitle($fam->fromid);
        if ($data[2] > 0) {
            $fam->id = $data[2]; // static id
            $fam->initid = $data[2];
        } elseif (trim($data[2]) != "") {
            if (!is_numeric(trim($data[2]))) {
                $fam->name = trim($data[2]); // logical name
                $docid = getIdFromName($this->dbaccess, $fam->name, $fromid);
                if ($docid > 0) {
                    $fam->id = $docid;
                    $fam->initid = $docid;
                }
            }
        }
        if ($fam->id > 0) {
            $this->doc = new_doc($fam->dbaccess, $fam->id, true);
            if (!$this->doc->isAffected()) $this->doc = $fam;
        } else {
            $this->doc = $fam;
        }
        
        if ((intval($this->doc->id) == 0) || (!$this->doc->isAffected())) {
            
            $this->tcr["action"] = "added";
        } else {
            if ($this->doc->fromid != $fromid) {
                //       $doc = new_Doc($this->doc->dbaccess,$this->doc->latestId());
                $this->tcr["action"] = "ignored";
                $this->tcr["id"] = $this->doc->id;
                $this->tcr["err"] = sprintf(_('not same family %s (%d)') , $this->doc->getTitle() , $this->doc->id);
                return $this->tcr;
            }
            if ($this->doc->doctype == 'Z') {
                if (!$this->analyze) $this->doc->revive();
                $this->tcr["msg"].= _("restore document") . "\n";
            }
            
            if ($this->doc->locked == - 1) {
                //       $doc = new_Doc($this->doc->dbaccess,$this->doc->latestId());
                $this->tcr["action"] = "ignored";
                $this->tcr["id"] = $this->doc->id;
                $this->tcr["err"] = _('fixed document');
                return $this->tcr;
            }
            
            $this->tcr["action"] = "updated";
            $this->tcr["id"] = $this->doc->id;
            $msg.= $err . sprintf(_("update id [%d] ") , $this->doc->id);
        }
        
        if ($this->hasError()) {
            global $nline, $gerr;
            $gerr = "\nline $nline:" . $err;
            $this->tcr["err"] = $err;
            return $this->tcr;
        }
        
        if (count($this->orders) == 0) {
            $lattr = $this->doc->GetImportAttributes();
            $this->orders = array_keys($lattr);
        } else {
            $lattr = $this->doc->GetNormalAttributes();
        }
        $iattr = 4; // begin in 5th column
        foreach ($this->orders as $attrid) {
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
                        $this->tcr["foldername"] = $this->importFilePath;
                        $this->tcr["filename"] = $dv;
                        
                        if (!$this->analyze) {
                            if ($attr->inArray()) {
                                $tabsfiles = $this->doc->_val2array($dv);
                                $tvfids = array();
                                foreach ($tabsfiles as $fi) {
                                    if (preg_match(PREGEXPFILE, $fi, $reg)) {
                                        $tvfids[] = $fi;
                                    } elseif (preg_match('/^http:/', $fi, $reg)) {
                                        $tvfids[] = '';
                                    } elseif ($fi) {
                                        $absfile = "$this->importFilePath/$fi";
                                        $err = AddVaultFile($this->dbaccess, $absfile, $this->analyze, $vfid);
                                        if ($err != "") {
                                            $this->setError("DOC0002", $err, $fi);
                                            // $this->tcr["err"].= "$err: $fi\n";
                                            
                                        } else {
                                            $tvfids[] = $vfid;
                                        }
                                    } else {
                                        $tvfids[] = '';
                                    }
                                }
                                $err.= $this->doc->setValue($attr->id, $tvfids);
                            } else {
                                // one file only
                                if (preg_match(PREGEXPFILE, $dv, $reg)) {
                                    $this->doc->setValue($attr->id, $dv);
                                    $this->tcr["values"][$attr->getLabel() ] = $dv;
                                } elseif (preg_match('/^http:/', $dv, $reg)) {
                                    // nothing
                                    
                                } elseif ($dv) {
                                    $absfile = "$this->importFilePath/$dv";
                                    $err = AddVaultFile($this->dbaccess, $absfile, $this->analyze, $vfid);
                                    if ($err != "") {
                                        $this->tcr["err"] = $err;
                                    } else {
                                        $this->tcr["err"] = $this->doc->setValue($attr->id, $vfid);
                                    }
                                }
                            }
                        } else {
                            // just for analyze
                            if ($dv == $this->doc->getValue($attr->id)) $this->tcr["values"][$attr->getLabel() ] = ("/no change/");
                            else $this->tcr["values"][$attr->getLabel() ] = $dv;
                        }
                    } else {
                        $errv = $this->doc->setValue($attr->id, $dv);
                        
                        if ($errv) {
                            //$err.= sprintf("%s:%s.", $attr->id, $errv);
                            $this->setError("DOC0001", $attr->id, $errv);
                        }
                        if ($this->doc->getOldValue($attr->id) !== false) $this->tcr["values"][$attr->getLabel() ] = $dv;
                        else $this->tcr["values"][$attr->getLabel() ] = ("/no change/");
                    }
                }
            }
            $iattr++;
        }
        
        if (($err == "") && (!$this->analyze)) {
            if (($this->doc->id > 0) || ($this->policy != "update")) {
                $err = $this->doc->preImport();
                if ($err) $this->setError("DOC0003", $err);
            }
        }
        // update title in finish
        if (!$this->analyze) $this->doc->refresh(); // compute read attribute
        if ($this->hasError()) {
            
            return $this->tcr;
        }
        
        if (($this->doc->id == "") && ($this->doc->name == "")) {
            switch ($this->policy) {
                case "add":
                    $this->tcr["action"] = "added"; # N_("added")
                    if (!$this->analyze) {
                        
                        if ($this->doc->id == "") {
                            // insert default values
                            foreach ($this->preValues as $k => $v) {
                                $this->doc->setValue($k, $v);
                            }
                            $err = $this->doc->preImport();
                            if ($err != "") {
                                $this->tcr["action"] = "ignored";
                                $this->tcr["err"] = sprintf(_("pre-import:%s") , $err);
                                return $this->tcr;
                            }
                            $err = $this->doc->Add();
                            $this->tcr["err"] = $err;
                        }
                        if ($err == "") {
                            $this->tcr["id"] = $this->doc->id;
                            $msg.= $err . sprintf(_("add %s id [%d]  ") , $this->doc->title, $this->doc->id);
                            $this->tcr["msg"] = sprintf(_("add %s id [%d]  ") , $this->doc->title, $this->doc->id);
                        } else {
                            $this->tcr["action"] = "ignored";
                        }
                    } else {
                        $this->doc->RefreshTitle();
                        $this->tcr["msg"] = sprintf(_("%s to be add") , $this->doc->title);
                    }
                    break;

                case "update":
                    $this->doc->RefreshTitle();
                    $lsdoc = $this->doc->GetDocWithSameTitle($this->keys[0], $this->keys[1]);
                    // test if same doc in database
                    if (count($lsdoc) == 0) {
                        $this->tcr["action"] = "added";
                        if (!$this->analyze) {
                            if ($this->doc->id == "") {
                                // insert default values
                                foreach ($this->preValues as $k => $v) {
                                    if ($this->doc->getValue($k) == "") $this->doc->setValue($k, $v);
                                }
                                $err = $this->doc->preImport();
                                if ($err != "") {
                                    $this->tcr["action"] = "ignored";
                                    $this->tcr["err"] = sprintf(_("pre-import:%s") , $err);
                                    return $this->tcr;
                                }
                                $err = $this->doc->Add();
                                $this->tcr["err"] = $err;
                            }
                            if ($err == "") {
                                $this->tcr["id"] = $this->doc->id;
                                $this->tcr["action"] = "added";
                                $this->tcr["msg"] = sprintf(_("add id [%d] ") , $this->doc->id);
                            } else {
                                $this->tcr["action"] = "ignored";
                            }
                        } else {
                            $this->tcr["msg"] = sprintf(_("%s to be add") , $this->doc->title);
                        }
                    } elseif (count($lsdoc) == 1) {
                        // no double title found
                        $this->tcr["action"] = "updated"; # N_("updated")
                        if (!$this->analyze) {
                            $err = $lsdoc[0]->preImport();
                            if ($err != "") {
                                $this->tcr["action"] = "ignored";
                                $this->tcr["err"] = sprintf(_("pre-import:%s") , $err);
                                return $this->tcr;
                            }
                        }
                        $lsdoc[0]->transfertValuesFrom($this->doc);
                        $this->doc = $lsdoc[0];
                        $this->tcr["id"] = $this->doc->id;
                        if (!$this->analyze) {
                            if (($data[2] != "") && (!is_numeric(trim($data[2]))) && ($this->doc->name == "")) {
                                $this->doc->name = $data[2];
                            }
                            $this->tcr["msg"] = sprintf(_("update %s [%d] ") , $this->doc->title, $this->doc->id);
                        } else {
                            $this->tcr["msg"] = sprintf(_("to be update %s [%d] ") , $this->doc->title, $this->doc->id);
                        }
                    } else {
                        //more than one double
                        $this->tcr["action"] = "ignored"; # N_("ignored")
                        $this->tcr["err"] = sprintf(_("too many similar document %s <strong>ignored</strong> ") , $this->doc->title);
                        
                        return $this->tcr;
                    }
                    
                    break;

                case "keep":
                    $this->doc->RefreshTitle();
                    $lsdoc = $this->doc->GetDocWithSameTitle($this->keys[0], $this->keys[1]);
                    if (count($lsdoc) == 0) {
                        $this->tcr["action"] = "added";
                        if (!$this->analyze) {
                            if ($this->doc->id == "") {
                                // insert default values
                                foreach ($this->preValues as $k => $v) {
                                    if ($this->doc->getValue($k) == "") $this->doc->setValue($k, $v);
                                }
                                $err = $this->doc->Add();
                            }
                            $this->tcr["id"] = $this->doc->id;
                            $msg.= $err . sprintf(_("add id [%d] ") , $this->doc->id);
                        } else {
                            $this->tcr["msg"] = sprintf(_("%s to be add") , $this->doc->title);
                        }
                    } else {
                        //more than one double
                        $this->tcr["action"] = "ignored";
                        $this->tcr["err"] = sprintf(_("similar document %s found. keep similar") , $this->doc->title);
                        
                        return $this->tcr;
                    }
                    
                    break;
                }
            } else {
                // add special id
                if (!$this->doc->isAffected()) {
                    $this->tcr["action"] = "added";
                    if (!$this->analyze) {
                        // insert default values
                        foreach ($this->preValues as $k => $v) {
                            if ($this->doc->getValue($k) == "") $this->doc->setValue($k, $v);
                        }
                        $err = $this->doc->preImport();
                        if ($err != "") {
                            $this->tcr["action"] = "ignored";
                            $this->tcr["err"] = sprintf(_("pre-import:%s") , $err);
                            return $this->tcr;
                        }
                        $err = $this->doc->Add();
                        
                        $this->tcr["id"] = $this->doc->id;
                        $msg.= $err . sprintf(_("add %s id [%d]  ") , $this->doc->title, $this->doc->id);
                        $this->tcr["msg"] = sprintf(_("add %s id [%d]  ") , $this->doc->title, $this->doc->id);
                    } else {
                        $this->doc->RefreshTitle();
                        $this->tcr["id"] = $this->doc->id;
                        $this->tcr["msg"] = sprintf(_("%s to be add") , $this->doc->title);
                    }
                }
            }
            
            $this->tcr["title"] = $this->doc->title;
            if (!$this->analyze) {
                if ($this->doc->isAffected()) {
                    $this->tcr["specmsg"] = $this->doc->Refresh(); // compute read attribute
                    $err = $this->doc->PostModify(); // compute read attribute
                    if ($err == "") $err = $this->doc->modify();
                    if ($err == "-") $err = ""; // not really an error add addfile must be tested after
                    if ($err == "") {
                        $this->doc->AddComment(sprintf(_("updated by import")));
                        $msg.= $this->doc->postImport();
                    }
                    $this->tcr["err"].= $err;
                    $this->tcr["msg"].= $msg;
                }
            }
            //------------------
            // add in folder
            if (($err == "") && ($data[3] != "-")) {
                
                if (is_numeric($data[3])) $ndirid = $data[3];
                else $ndirid = getIdFromName($this->dbaccess, $data[3], 2);
                
                if ($ndirid > 0) { // dirid
                    $dir = new_Doc($this->dbaccess, $ndirid);
                    if ($dir->isAffected()) {
                        $this->tcr["folderid"] = $dir->id;
                        $this->tcr["foldername"] = dirname($this->importFilePath) . "/" . $dir->title;
                        if (!$this->analyze) {
                            if ($dir->isAlive() && method_exists($dir, "AddFile")) {
                                $this->tcr["err"].= $dir->AddFile($this->doc->id);
                            }
                        }
                        $this->tcr["msg"].= $err . " " . sprintf(_("and add in %s folder ") , $dir->title);
                    }
                } else if ($ndirid == 0) {
                    if ($this->dirid) {
                        /**
                         * @var $dir Dir
                         */
                        $dir = new_Doc($this->dbaccess, $this->dirid);
                        if ($dir->isAlive() && method_exists($dir, "AddFile")) {
                            $this->tcr["folderid"] = $dir->id;
                            $this->tcr["foldername"] = dirname($this->importFilePath) . "/" . $dir->title;
                            if (!$this->analyze) {
                                if ($dir->isAlive() && method_exists($dir, "AddFile")) {
                                    $this->tcr["err"].= $dir->AddFile($this->doc->id);
                                }
                            }
                            $this->tcr["msg"].= $err . " " . sprintf(_("and add in %s folder ") , $dir->title);
                        }
                    }
                }
            }
            
            return $this->tcr;
        }
    }
?>