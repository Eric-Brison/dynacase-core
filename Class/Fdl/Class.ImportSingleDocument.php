<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Import single documents
 *
 * @author Anakeen
 * @package FDL
 * @subpackage
 */
/**
 */

include_once ("FDL/import_file.php");

class importSingleDocument
{
    private $currentAttrid = "";
    protected $dirid = 0;
    protected $analyze = false;
    protected $importFilePath = '';
    protected $policy = 'add';
    protected $orders = array();
    protected $preValues = array();
    /**
     * @var bool verify attribute access (visibility "I")
     */
    protected $verifyAttributeAccess = true;
    /**
     * @var array folder id where insert the new document
     */
    protected $folderIds = array();
    protected $keys = array(
        'title',
        null
    );
    protected $tcr = array();
    protected $error = array();
    public $dbaccess = '';
    /**
     * @var string family reference
     */
    public $famId;
    /**
     * @var string special logical name
     */
    public $specId;
    /**
     * @var string folder reference where insert document
     */
    public $folderId;
    /**
     * @var DocFam
     */
    private $doc;
    /**
     * @var
     */
    private $knownLogicalNames = array();
    
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
    /**
     * @param boolean $verifyAttributeAccess
     */
    public function setVerifyAttributeAccess($verifyAttributeAccess)
    {
        $this->verifyAttributeAccess = $verifyAttributeAccess;
    }
    public function setTargetDirectory($dirid)
    {
        if ($dirid == '-') $dirid = 0;
        $this->dirid = $dirid;
    }
    /**
     * identify  where to insert document
     * @param array $dirid folder identifier
     */
    public function setTargetDirectories(array $dirid)
    {
        $this->folderIds = $dirid;
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
        if (!isset($this->keys[1])) $this->keys[1] = null;
    }
    
    public function getError()
    {
        return implode("\n", $this->error);
    }
    
    public function getImportResult()
    {
        return $this->tcr;
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
    /**
     * import a single document from $data
     * import line beginning with  DOC
     * @param array $data
     * @return importSingleDocument
     */
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
        
        $this->famId = isset($data[1]) ? trim($data[1]) : '';
        $this->specId = isset($data[2]) ? trim($data[2]) : '';
        $this->folderId = isset($data[3]) ? trim($data[3]) : '';
        
        if (is_numeric($this->famId)) $fromid = $this->famId;
        else $fromid = getFamIdFromName($this->dbaccess, $this->famId);
        if ($fromid == 0) {
            // no need test here it is done by checkDoc class DOC0005 DOC0006
            $this->tcr["action"] = "ignored";
            $this->tcr["err"] = sprintf(_("Not a family [%s]") , $this->famId);
            return $this;
        }
        $tmpDoc = createDoc($this->dbaccess, $fromid);
        if (!$tmpDoc) {
            // no need test here it is done by checkDoc class DOC0007
            $this->tcr["action"] = "ignored";
            $this->tcr["err"] = sprintf(_("cannot create from family [%s]") , $this->famId);
            return $this;
        }
        
        $msg = ""; // information message
        $tmpDoc->fromid = $fromid;
        $this->tcr["familyid"] = $tmpDoc->fromid;
        $this->tcr["familyname"] = $tmpDoc->getTitle($tmpDoc->fromid);
        if ($this->specId > 0) {
            $tmpDoc->id = $this->specId; // static id
            $tmpDoc->initid = $this->specId;
        } elseif (trim($this->specId) != "") {
            if (!is_numeric(trim($this->specId))) {
                $tmpDoc->name = trim($this->specId); // logical name
                $docid = getIdFromName($this->dbaccess, $tmpDoc->name, $fromid);
                if ($docid > 0) {
                    $tmpDoc->id = $docid;
                    $tmpDoc->initid = $docid;
                }
            }
        }
        
        if ($tmpDoc->id > 0) {
            $this->doc = new_doc($tmpDoc->dbaccess, $tmpDoc->id, true);
            if (!$this->doc->isAffected()) $this->doc = $tmpDoc;
        } else {
            $this->doc = $tmpDoc;
        }
        
        if ((intval($this->doc->id) == 0) || (!$this->doc->isAffected())) {
            
            $this->tcr["action"] = "added";
        } else {
            if ($this->doc->fromid != $fromid) {
                
                $this->tcr["id"] = $this->doc->id;
                $this->setError("DOC0008", $this->doc->getTitle() , $this->doc->fromname, getNameFromId($this->dbaccess, $fromid));
                return $this;
            }
            if ($this->doc->doctype == 'Z') {
                if (!$this->analyze) $this->doc->undelete();
                $this->tcr["msg"].= _("restore document") . "\n";
            }
            
            if ($this->doc->locked == - 1) {
                $this->tcr["id"] = $this->doc->id;
                $this->setError("DOC0009", $this->doc->getTitle() , $this->doc->fromname);
                return $this;
            }
            
            $this->tcr["action"] = "updated";
            $this->tcr["id"] = $this->doc->id;
            $msg.= sprintf(_("update id [%d] ") , $this->doc->id);
        }
        
        if ($this->hasError()) {
            
            return $this;
        }
        if (!$this->verifyAttributeAccess) {
            $this->inhibitInvisibleAttributes($this->doc);
        }
        if (count($this->orders) == 0) {
            $lattr = $this->doc->GetImportAttributes();
            $this->orders = array_keys($lattr);
        } else {
            $lattr = $this->doc->GetNormalAttributes();
        }
        $extra = array();
        $iattr = 4; // begin in 5th column
        foreach ($this->orders as $attrid) {
            if (isset($lattr[$attrid])) {
                $attr = $lattr[$attrid];
                if (isset($data[$iattr]) && ($data[$iattr] != "")) {
                    $dv = $data[$iattr];
                    if (!isUTF8($dv)) $dv = utf8_encode($dv);
                    if (($attr->type == "file") || ($attr->type == "image")) {
                        // insert file
                        $this->tcr["foldername"] = $this->importFilePath;
                        $this->tcr["filename"] = $dv;
                        
                        if (!$this->analyze) {
                            if ($attr->inArray()) {
                                $tabsfiles = $this->doc->rawValueToArray($dv);
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
                                            $this->setError("DOC0101", $err, $fi, $attrid, $this->doc->name);
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
                                        
                                        $this->setError("DOC0102", $err, $dv, $attrid, $this->doc->name);
                                    } else {
                                        $err = $this->doc->setValue($attr->id, $vfid);
                                        if ($err) $this->setError("DOC0103", $err, $dv, $attrid, $this->doc->name);
                                    }
                                }
                            }
                        } else {
                            // just for analyze
                            $this->tcr["values"][$attr->getLabel() ] = $dv;
                        }
                    } else {
                        if ($attr->type == "htmltext" && !$this->analyze) {
                            $this->currentAttrid = $attrid;
                            $dv = preg_replace_callback('/(<img.*?src=")file:\/\/(.*?)(".*?\/>)/', function ($matches)
                            {
                                return $this->importHtmltextFiles($matches);
                            }
                            , $dv);
                        }
                        if ($attr->type == "docid" || $attr->type == "account" || $attr->type == "thesaurus") {
                            /**
                             * Check for unknown logical names in docid's raw value
                             */
                            $unknownLogicalNames = $this->getUnknownDocIdLogicalNames($this->doc, $attr, $dv);
                            if (count($unknownLogicalNames) > 0) {
                                foreach ($unknownLogicalNames as $logicalName) {
                                    $warnMsg = sprintf(_("Unknown logical name '%s' in attribute '%s'.") , $logicalName, $attr->id);
                                    $this->doc->log->warning($warnMsg);
                                    $this->tcr['specmsg'].= (($this->tcr['specmsg'] != '') ? "\n" . $warnMsg : $warnMsg);
                                }
                            }
                        }
                        $errv = $this->doc->setValue($attr->id, $dv);
                        if ($errv) {
                            $this->setError("DOC0100", $attr->id, $errv);
                        }
                        $this->tcr["values"][$attr->getLabel() ] = $dv;
                    }
                }
            } else if (strpos($attrid, "extra:") === 0) {
                $attr = substr($attrid, strlen("extra:"));
                if (isset($data[$iattr]) && ($data[$iattr] != "")) {
                    $dv = str_replace(array(
                        '\n',
                        ALTSEPCHAR
                    ) , array(
                        "\n",
                        ';'
                    ) , $data[$iattr]);
                    if (!isUTF8($dv)) $dv = utf8_encode($dv);
                    $extra[$attr] = $dv;
                }
            }
            $iattr++;
        }
        if ((!$this->hasError()) && (!$this->analyze)) {
            if (($this->doc->id > 0) || ($this->policy != "update")) {
                $err = $this->doc->preImport($extra);
                if ($err) $this->setError("DOC0104", $this->doc->name, $err);
            }
        }
        // update title in finish
        if (!$this->analyze) $this->doc->refresh(); // compute read attribute
        if ($this->hasError()) {
            
            return $this;
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
                            $err = $this->doc->preImport($extra);
                            if ($err != "") {
                                if ($err) {
                                    $this->setError("DOC0105", $this->doc->name, $err);
                                }
                                return $this;
                            }
                            $err = $this->doc->Add();
                            
                            if ($err) {
                                $this->setError("DOC0107", $this->doc->name, $err);
                            }
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
                                    if ($this->doc->getRawValue($k) == "") {
                                        $this->doc->setValue($k, $v);
                                    }
                                }
                                $err = $this->doc->preImport($extra);
                                if ($err != "") {
                                    
                                    if ($err) {
                                        $this->setError("DOC0106", $this->doc->name, $err);
                                    }
                                    
                                    return $this;
                                }
                                $err = $this->doc->Add();
                                
                                if ($err) {
                                    $this->setError("DOC0108", $this->doc->name, $err);
                                }
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
                        
                        /**
                         * @var Doc $doc
                         */
                        $doc = $lsdoc[0];
                        if (!$this->analyze) {
                            $err = $doc->preImport($extra);
                            if ($err != "") {
                                if ($err) {
                                    $this->setError("DOC0109", $this->doc->name, $err);
                                }
                                
                                return $this;
                            }
                        }
                        $err = $doc->transfertValuesFrom($this->doc);
                        if ($err != "") {
                            $this->setError("DOC0113", $this->doc->name, $err);
                            
                            return $this;
                        }
                        $this->doc = $doc;
                        $this->tcr["id"] = $this->doc->id;
                        if (!$this->analyze) {
                            if (($this->specId != "") && (!is_numeric(trim($this->specId))) && ($this->doc->name == "")) {
                                $this->doc->name = $this->specId;
                            }
                            $this->tcr["msg"] = sprintf(_("update %s [%d] ") , $this->doc->title, $this->doc->id);
                        } else {
                            $this->tcr["msg"] = sprintf(_("to be update %s [%d] ") , $this->doc->title, $this->doc->id);
                        }
                    } else {
                        //more than one double
                        $this->tcr["action"] = "ignored"; # N_("ignored")
                        $this->setError("DOC0110", $this->doc->getTitle());
                        
                        return $this;
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
                                    if ($this->doc->getRawValue($k) == "") {
                                        $this->doc->setValue($k, $v);
                                    }
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
                        $this->tcr["msg"] = sprintf(_("similar document %s found. keep similar") , $this->doc->title);
                        
                        return $this;
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
                        if ($this->doc->getRawValue($k) == "") {
                            $this->doc->setValue($k, $v);
                        }
                    }
                    $err = $this->doc->preImport($extra);
                    if ($err != "") {
                        $this->setError("DOC0111", $this->doc->name, $err);
                        return $this;
                    }
                    $err = $this->doc->Add();
                    if ($err != "") {
                        $this->setError("DOC0111", $this->doc->name, $err);
                        return $this;
                    }
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
        if ($this->hasError()) {
            return $this;
        }
        if (!$this->analyze) {
            if ($this->doc->isAffected()) {
                $warnMsg = $this->doc->Refresh();
                $this->tcr["specmsg"].= (($this->tcr["specmsg"] != '') ? "\n" . $warnMsg : $warnMsg); // compute read attribute
                $msg.= $this->doc->postStore(); // compute read attribute
                $err = $this->doc->modify();
                if ($err == "-") $err = ""; // not really an error add addfile must be tested after
                if ($err == "") {
                    $this->doc->addHistoryEntry(sprintf(_("updated by import")));
                    $msg.= $this->doc->postImport($extra);
                } else {
                    $this->setError("DOC0112", $this->doc->name, $err);
                }
                
                $this->tcr["msg"].= $msg;
            }
        }
        if ($this->hasError()) {
            return $this;
        }
        //------------------
        // add in folder
        if ($this->folderId != "-") {
            if ($this->folderId) {
                $this->addIntoFolder($this->folderId);
            } elseif ($this->dirid) {
                $this->addIntoFolder($this->dirid);
            }
        }
        if ($this->folderIds) {
            foreach ($this->folderIds as $fid) {
                if ($fid) $this->addIntoFolder($fid);
            }
        }
        if ($this->doc->id) {
            clearCacheDoc($this->doc->id);
        } // clear cache to clean unused
        return $this;
    }
    
    protected function inhibitInvisibleAttributes(Doc $doc)
    {
        $oas = $doc->getNormalAttributes();
        foreach ($oas as $oa) {
            if ($oa->mvisibility === "I") {
                $oa->setVisibility("H");
            }
        }
    }
    
    public function importHtmltextFiles($matches)
    {
        $dvCahnged = $matches[0];
        $absfile = "$this->importFilePath/$matches[2]";
        $err = AddVaultFile(getDbAccess() , $absfile, $this->analyze, $vfid);
        if ($err != "" || $this->analyze) {
            $this->setError("DOC0102", $err, $matches[2], $this->currentAttrid, $this->doc->name);
        } else {
            $fileImgAttrid = "img_file";
            /**
             * @var Doc $imgDoc
             */
            $imgDoc = createDoc(getDbAccess() , "IMAGE");
            
            if (is_object($imgDoc)) {
                $imgDoc->setAttributeValue($fileImgAttrid, $vfid);
                $err = $imgDoc->store();
                if ($err) $this->setError("DOC0100", $this->currentAttrid, $err);
                else $dvCahnged = $matches[1] . htmlspecialchars($imgDoc->getFileLink($fileImgAttrid)) . $matches[3];
            }
        }
        return $dvCahnged;
    }
    /**
     * insert imported document into a folder
     * @param string $folderId
     */
    protected function addIntoFolder($folderId)
    {
        if ($folderId) {
            /**
             * @var $dir Dir
             */
            $dir = new_Doc($this->dbaccess, $folderId);
            if ($dir->isAlive()) {
                $this->tcr["folderid"] = $dir->id;
                $this->tcr["foldername"] = dirname($this->importFilePath) . "/" . $dir->title;
                if (!$this->analyze) {
                    if (method_exists($dir, "insertDocument")) {
                        $err = $dir->insertDocument($this->doc->id);
                        if ($err) $this->setError("DOC0200", $this->doc->name, $dir->getTitle() , $err);
                    } else {
                        $this->setError("DOC0202", $dir->getTitle() , $dir->fromname, $this->doc->name);
                    }
                }
                $this->tcr["msg"].= " " . sprintf(_("and add in %s folder ") , $dir->title);
            } else {
                $this->setError("DOC0201", $folderId, ($this->doc->name) ? $this->doc->name : $this->doc->getTitle());
            }
        }
    }
    /**
     * Parse a docid's raw value (single or multiple) for unknown logical names
     *
     * @param Doc $doc
     * @param NormalAttribute $oattr
     * @param string $value docid's raw value
     * @return array List of unknown logical names referenced by the value
     */
    protected function getUnknownDocIdLogicalNames(Doc & $doc, NormalAttribute & $oattr, $value)
    {
        $res = array();
        if ($value === ' ') {
            return $res;
        }
        $value = trim($value, " \x0B\r"); // suppress white spaces end & begin
        if ($oattr->repeat) {
            $tvalues = $doc->rawValueToArray($value);
        } else {
            $tvalues[] = $value;
        }
        foreach ($tvalues as $kvalue => $avalue) {
            if (($avalue != "") && ($avalue != "\t")) {
                $unresolvedLogicalNames = array();
                $tvalues[$kvalue] = $doc->resolveDocIdLogicalNames($oattr, $avalue, $unresolvedLogicalNames, $this->knownLogicalNames);
                if (count($unresolvedLogicalNames) > 0) {
                    $res = array_merge($res, $unresolvedLogicalNames);
                }
            }
        }
        return $res;
    }
    /**
     * Set the list of known logical names to check for unknown logical names
     * @param array $knownLogicalNames List of known logical names
     * @return array|bool Return the previous list of known logical names or bool(false) if the given list is not an array
     */
    public function setKnownLogicalNames($knownLogicalNames = array())
    {
        if (!is_array($knownLogicalNames)) {
            return false;
        }
        $old = $this->knownLogicalNames;
        $this->knownLogicalNames = $knownLogicalNames;
        return $old;
    }
}
