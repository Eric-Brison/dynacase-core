<?php
/*
 * @author Anakeen
 * @package FDL
*/

namespace Dcp;

class ExportDocument
{
    const useAclDocumentType = ":useDocument";
    const useAclAccountType = ":useAccount";
    
    protected $alreadyExported = array();
    protected $lattr;
    protected $prevfromid = - 1;
    protected $familyName = '';
    protected $csvEnclosure = '"';
    protected $csvSeparator = ',';
    protected $encoding = 'utf-8';
    protected $verifyAttributeAccess = false;
    protected $attributeGrants = array();
    protected $noAccessText = \FormatCollection::noAccessText;
    protected $exportAccountType = self::useAclAccountType;
    
    private $logicalName = [];
    
    private $logins = [];
    /**
     * Use when cannot access attribut value
     * Due to visibility "I"
     * @param string $noAccessText
     */
    public function setNoAccessText($noAccessText)
    {
        $this->noAccessText = $noAccessText;
    }
    /**
     * If true, attribute with "I" visibility are not returned
     * @param boolean $verifyAttributeAccess
     */
    public function setVerifyAttributeAccess($verifyAttributeAccess)
    {
        $this->verifyAttributeAccess = $verifyAttributeAccess;
    }
    /**
     * @param string $encoding
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }
    /**
     * @param string $csvSeparator
     */
    public function setCsvSeparator($csvSeparator)
    {
        $this->csvSeparator = $csvSeparator;
    }
    /**
     * @param string $csvEnclosure
     */
    public function setCsvEnclosure($csvEnclosure)
    {
        $this->csvEnclosure = $csvEnclosure;
    }
    
    public function reset()
    {
        $this->alreadyExported = array();
    }
    /**
     * @return array
     */
    public function getTrans()
    {
        static $htmlTransMapping = false;
        if (!$htmlTransMapping) {
            // to invert HTML entities
            $htmlTransMapping = get_html_translation_table(HTML_ENTITIES);
            $htmlTransMapping = array_flip($htmlTransMapping);
            $htmlTransMapping = array_map("utf8_encode", $htmlTransMapping);
        }
        return $htmlTransMapping;
    }
    
    protected function getUserLogin($uid)
    {
        if (!isset($this->logins[$uid])) {
            simpleQuery("", sprintf("select login from users where id=%d", $uid) , $login, true, true);
            $this->logins[$uid] = $login ? $login : 0;
        }
        return $this->logins[$uid];
    }
    protected function getUserLogicalName($uid)
    {
        if (!isset($this->logicalName[$uid])) {
            simpleQuery("", sprintf("select name from docread where id=(select fid from users where id = %d)", $uid) , $logicalName, true, true);
            $this->logicalName[$uid] = $logicalName ? $logicalName : 0;
        }
        return $this->logicalName[$uid];
    }
    /**
     * @param resource $fout
     * @param string|int $docid
     */
    public function exportProfil($fout, $docid)
    {
        if (!$docid) return;
        // import its profile
        $doc = \new_Doc("", $docid); // needed to have special acls
        $doc->acls[] = "viewacl";
        $doc->acls[] = "modifyacl";
        if ($doc->name != "") $name = $doc->name;
        else $name = $doc->id;
        
        $dbaccess = getDbAccess();
        $q = new \QueryDb($dbaccess, "DocPerm");
        $q->AddQuery(sprintf("docid=%d", $doc->profid));
        $q->order_by = "userid";
        $acls = $q->Query(0, 0, "TABLE");
        
        $tAcls = array();
        if ($acls) {
            foreach ($acls as $va) {
                $up = $va["upacl"];
                $uid = $va["userid"];
                
                if ($uid >= STARTIDVGROUP) {
                    $qvg = new \QueryDb($dbaccess, "VGroup");
                    $qvg->AddQuery(sprintf("num=%d", $uid));
                    $tvu = $qvg->Query(0, 1, "TABLE");
                    $uid = sprintf("attribute(%s)", $tvu[0]["id"]);
                } else {
                    if ($this->exportAccountType === self::useAclDocumentType) {
                        $uln = $this->getUserLogicalName($uid);
                        if ($uln) {
                            if (preg_match('/^attribute\(.*\)$/', $uln)) {
                                $uid = sprintf("document(%s)", $uln);
                            } else {
                                $uid = $uln;
                            }
                        } else {
                            $uid = $this->getUserLogin($uid);
                            if ($uid) {
                                $uid = sprintf("account(%s)", $uid);
                            }
                        }
                    } else {
                        $uid = $this->getUserLogin($uid);
                        if (preg_match('/^attribute\(.*\)$/', $uid)) {
                            $uid = sprintf("account(%s)", $uid);
                        }
                    }
                }
                foreach ($doc->acls as $kAcl => $acl) {
                    $bup = ($doc->ControlUp($up, $acl) == "");
                    if ($uid && $bup) {
                        $tAcls[$kAcl . "-" . $uid] = ["uid" => $uid, "acl" => $acl];
                    }
                }
            }
        }
        // add extended Acls
        if ($doc->extendedAcls) {
            $extAcls = array_keys($doc->extendedAcls);
            $aclCond = GetSqlCond($extAcls, "acl");
            simpleQuery($dbaccess, sprintf("select * from docpermext where docid=%d and %s order by userid", $doc->profid, $aclCond) , $eAcls);
            
            foreach ($eAcls as $kAcl => $aAcl) {
                $uid = $aAcl["userid"];
                if ($uid >= STARTIDVGROUP) {
                    $qvg = new \QueryDb($dbaccess, "VGroup");
                    $qvg->AddQuery(sprintf("num=%d", $uid));
                    $tvu = $qvg->Query(0, 1, "TABLE");
                    $uid = sprintf("attribute(%s)", $tvu[0]["id"]);
                } else {
                    $uid = $this->getUserLogin($uid);
                    if (preg_match('/^attribute\(.*\)$/', $uid)) {
                        $uid = sprintf("account(%s)", $uid);
                    }
                }
                if ($uid) {
                    $tAcls["e".$kAcl . "-" . $uid] = ["uid" => $uid, "acl" => $aAcl["acl"]];
                }
            }
        }
        if (count($tAcls) > 0) {
            $data = array(
                "PROFIL",
                $name,
                $this->exportAccountType,
                ""
            );
            ksort($tAcls);
            foreach ($tAcls as $ku => $oneAcl) {
                //fputs_utf8($fout, ";" . $tpa[$ku] . "=" . $uid);
                $data[] = sprintf("%s=%s", $oneAcl["acl"], $oneAcl["uid"]);
            }
            \Dcp\WriteCsv::fput($fout, $data);
        }
    }
    /**
     * @deprecated rename to  csvExport
     */
    public function cvsExport(\Doc & $doc, &$ef, $fout, $wprof, $wfile, $wident, $wutf8, $nopref, $eformat)
    {
        $this->csvExport($doc, $ef, $fout, $wprof, $wfile, $wident, $wutf8, $nopref, $eformat);
    }
    public function csvExport(\Doc & $doc, &$ef, $fout, $wprof, $wfile, $wident, $wutf8, $nopref, $eformat)
    {
        
        if (!$doc->isAffected()) return;
        if (in_array($doc->id, $this->alreadyExported)) return;
        $this->alreadyExported[] = $doc->id;
        
        \Dcp\WriteCsv::$separator = $this->csvSeparator;
        \Dcp\WriteCsv::$enclosure = $this->csvEnclosure;
        \Dcp\WriteCsv::$encoding = ($wutf8) ? "utf-8" : "iso8859-15";
        
        $efldid = '';
        $dbaccess = $doc->dbaccess;
        if ($this->prevfromid != $doc->fromid) {
            if (($eformat != "I") && ($this->prevfromid > 0)) {
                \Dcp\WriteCsv::fput($fout, array());
            }
            $adoc = $doc->getFamilyDocument();
            if ($adoc->name != "") $this->familyName = $adoc->name;
            else $this->familyName = $adoc->id;
            if (!$this->familyName) return;
            $this->lattr = $adoc->GetExportAttributes($wfile, $nopref);
            $data = array();
            
            if ($eformat == "I") {
                $data = array(
                    "//FAM",
                    $adoc->title . "(" . $this->familyName . ")",
                    "<specid>",
                    "<fldid>"
                );
                //fputs_utf8($fout, "//FAM;" . $adoc->title . "(" . $this->familyName . ");<specid>;<fldid>;");
                
            }
            foreach ($this->lattr as $attr) {
                $data[] = $attr->getLabel();
                //fputs_utf8($fout, str_replace(SEPCHAR, ALTSEPCHAR, $attr->getLabel()) . SEPCHAR);
                
            }
            WriteCsv::fput($fout, $data);
            //fputs_utf8($fout, "\n");
            if ($eformat == "I") {
                $data = array(
                    "ORDER",
                    $this->familyName,
                    "",
                    ""
                );
                //fputs_utf8($fout, "ORDER;" . $this->familyName . ";;;");
                foreach ($this->lattr as $attr) {
                    $data[] = $attr->id;
                    //fputs_utf8($fout, $attr->id . ";");
                    
                }
                WriteCsv::fput($fout, $data);
                // fputs_utf8($fout, "\n");
                
            }
            $this->prevfromid = $doc->fromid;
        }
        $docName = '';
        if ($doc->name != "" && $doc->locked != - 1) {
            $docName = $doc->name;
        } else if ($wprof) {
            if ($doc->locked != - 1) {
                $err = $doc->setNameAuto(true);
                $docName = $doc->name;
            }
        } else if ($wident) {
            $docName = $doc->id;
        }
        $data = array();
        if ($eformat == "I") {
            $data = array(
                "DOC",
                $this->familyName,
                $docName,
                $efldid
            );
        }
        // write values
        foreach ($this->lattr as $attr) {
            if ($this->verifyAttributeAccess && !\Dcp\VerifyAttributeAccess::isAttributeAccessGranted($doc, $attr)) {
                $data[] = $this->noAccessText;
                continue;
            }
            
            if ($eformat == 'F') {
                if ($this->csvEnclosure) {
                    $value = str_replace(array(
                        '<BR>',
                        '<br/>'
                    ) , array(
                        "\n",
                        "\\n"
                    ) , $doc->getHtmlAttrValue($attr->id, '', false, -1, false));
                } else {
                    $value = str_replace(array(
                        '<BR>',
                        '<br/>'
                    ) , '\\n', $doc->getHtmlAttrValue($attr->id, '', false, -1, false));
                }
            } else {
                
                $value = $doc->getRawValue($attr->id);
            }
            // invert HTML entities
            if (($attr->type == "image") || ($attr->type == "file")) {
                $tfiles = $doc->vault_properties($attr);
                $tf = array();
                foreach ($tfiles as $f) {
                    $ldir = $doc->id . '-' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', unaccent($doc->title)) . "_D";
                    $fname = $ldir . '/' . unaccent($f["name"]);
                    $tf[] = $fname;
                    $ef[$fname] = array(
                        "path" => $f["path"],
                        "ldir" => $ldir,
                        "fname" => unaccent($f["name"])
                    );
                }
                $value = implode("\n", $tf);
            } else if ($attr->type == "docid" || $attr->type == "account" || $attr->type == "thesaurus") {
                $docrevOption = $attr->getOption("docrev", "latest");
                if ($value != "") {
                    if (strstr($value, "\n") || ($attr->getOption("multiple") == "yes")) {
                        $tid = $doc->rawValueToArray($value);
                        $tn = array();
                        foreach ($tid as $did) {
                            $brtid = explode("<BR>", $did);
                            $tnbr = array();
                            foreach ($brtid as $brid) {
                                $n = getNameFromId($dbaccess, $brid);
                                if ($n) {
                                    if ($docrevOption === "latest") {
                                        $tnbr[] = $n;
                                    } else {
                                        addWarningMsg(sprintf(_("Doc %s : Attribut \"%s\" reference revised identifier : cannot use logical name") , $doc->getTitle() , $attr->getLabel()));
                                        $tnbr[] = $brid;
                                    }
                                } else {
                                    $tnbr[] = $brid;
                                }
                            }
                            $tn[] = implode('<BR>', $tnbr);
                        }
                        $value = implode("\n", $tn);
                    } else {
                        $n = getNameFromId($dbaccess, $value);
                        if ($n) {
                            if ($docrevOption === "latest") {
                                $value = $n;
                            } else {
                                addWarningMsg(sprintf(_("Doc %s : Attribut \"%s\" reference revised identifier : cannot use logical name") , $doc->getTitle() , $attr->getLabel()));
                            }
                        }
                    }
                }
            } else if ($attr->type == "htmltext") {
                $value = $attr->prepareHtmltextForExport($value);
                if ($wfile) {
                    $value = preg_replace_callback('/(<img.*?src=")(((?=.*docid=(.*?)&)(?=.*attrid=(.*?)&)(?=.*index=(-?[0-9]+)))|(file\/(.*?)\/[0-9]+\/(.*?)\/(-?[0-9]+))).*?"/', function ($matches) use (&$ef)
                    {
                        if (isset($matches[7])) {
                            $docid = $matches[8];
                            $attrid = $matches[9];
                            $index = $matches[10] == "-1" ? 0 : $matches[10];
                        } else {
                            $docid = $matches[4];
                            $index = $matches[6] == "-1" ? 0 : $matches[6];
                            $attrid = $matches[5];
                        }
                        $doc = \new_Doc(getDbAccess() , $docid);
                        $attr = $doc->getAttribute($attrid);
                        $tfiles = $doc->vault_properties($attr);
                        $f = $tfiles[$index];
                        
                        $ldir = $doc->id . '-' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', unaccent($doc->title)) . "_D";
                        $fname = $ldir . '/' . unaccent($f["name"]);
                        $ef[$fname] = array(
                            "path" => $f["path"],
                            "ldir" => $ldir,
                            "fname" => unaccent($f["name"])
                        );
                        return $matches[1] . "file://" . $fname . '"';
                    }
                    , $value);
                }
            } else {
                $trans = $this->getTrans();
                $value = preg_replace_callback('/(\&[a-zA-Z0-9\#]+;)/s', function ($matches) use ($trans)
                {
                    return strtr($matches[1], $trans);
                }
                , $value);
                // invert HTML entities which ascii code like &#232;
                $value = preg_replace_callback('/\&#([0-9]+);/s', function ($matches)
                {
                    return chr($matches[1]);
                }
                , $value);
            }
            $data[] = $value;
        }
        \Dcp\WriteCsv::fput($fout, $data);
        if ($wprof) {
            $profid = ($doc->dprofid) ? $doc->dprofid : $doc->profid;
            if ($profid == $doc->id) {
                $this->exportProfil($fout, $doc->id);
            } else if ($profid > 0) {
                $name = getNameFromId($dbaccess, $profid);
                $dname = $doc->name;
                if (!$dname) $dname = $doc->id;
                if (!$name) $name = $profid;
                if (!isset($tdoc[$profid])) {
                    $tdoc[$profid] = true;
                    $pdoc = \new_Doc($dbaccess, $profid);
                    $this->csvExport($pdoc, $ef, $fout, $wprof, $wfile, $wident, $wutf8, $nopref, $eformat);
                }
                $data = array(
                    "PROFIL",
                    $dname,
                    $name,
                    ""
                );
                \Dcp\WriteCsv::fput($fout, $data);
            }
        }
    }
    /**
     * @param string $exportAccountType
     * @throws Exception
     */
    public function setExportAccountType($exportAccountType)
    {
        $availables = [self::useAclAccountType, self::useAclDocumentType];
        if (!in_array($exportAccountType, $availables)) {
            throw new Exception("PRFL0300", $exportAccountType, implode(", ", $availables));
        }
        $this->exportAccountType = $exportAccountType;
    }
}
