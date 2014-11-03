<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp;

class ExportDocument
{
    
    protected $alreadyExported = array();
    protected $lattr;
    protected $prevfromid = - 1;
    protected $familyName = '';
    protected $csvEnclosure = '"';
    protected $csvSeparator = ',';
    protected $encoding = 'utf-8';
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
    
    protected function getUserLogicName($uid)
    {
        $u = new \Account("", $uid);
        if ($u->isAffected()) {
            $du = getTDoc($u->dbaccess, $u->fid);
            if (($du["name"] != "") && ($du["us_whatid"] == $uid)) return $du["name"];
        }
        return $uid;
    }
    /**
     * @param resource $fout
     * @param string|int $docid
     */
    public function exportProfil($fout, $docid)
    {
        if (!$docid) return;
        // import its profile
        $doc = new_Doc("", $docid); // needed to have special acls
        $doc->acls[] = "viewacl";
        $doc->acls[] = "modifyacl";
        if ($doc->name != "") $name = $doc->name;
        else $name = $doc->id;
        
        $dbaccess = getDbAccess();
        $q = new \QueryDb($dbaccess, "DocPerm");
        $q->AddQuery(sprintf("docid=%d", $doc->profid));
        $acls = $q->Query(0, 0, "TABLE");
        
        $tpu = array();
        $tpa = array();
        if ($acls) {
            foreach ($acls as $va) {
                $up = $va["upacl"];
                $uid = $va["userid"];
                
                foreach ($doc->acls as $acl) {
                    $bup = ($doc->ControlUp($up, $acl) == "");
                    if ($bup) {
                        if ($uid >= STARTIDVGROUP) {
                            $qvg = new \QueryDb($dbaccess, "VGroup");
                            $qvg->AddQuery(sprintf("num=%d", $uid));
                            $tvu = $qvg->Query(0, 1, "TABLE");
                            $uid = $tvu[0]["id"];
                        }
                        
                        $tpu[] = $uid;
                        if ($bup) $tpa[] = $acl;
                        else $tpa[] = "-" . $acl;
                    }
                }
            }
        }
        // add extended Acls
        if ($doc->extendedAcls) {
            $extAcls = array_keys($doc->extendedAcls);
            $aclCond = GetSqlCond($extAcls, "acl");
            simpleQuery($dbaccess, sprintf("select * from docpermext where docid=%d and %s", $doc->profid, $aclCond) , $eAcls);
            
            foreach ($eAcls as $aAcl) {
                $uid = $aAcl["userid"];
                if ($uid >= STARTIDVGROUP) {
                    $qvg = new \QueryDb($dbaccess, "VGroup");
                    $qvg->AddQuery(sprintf("num=%d", $uid));
                    $tvu = $qvg->Query(0, 1, "TABLE");
                    $uid = $tvu[0]["id"];
                }
                $tpa[] = $aAcl["acl"];
                $tpu[] = $uid;
            }
        }
        
        if (count($tpu) > 0) {
            $data = array(
                "PROFIL",
                $name,
                "",
                ""
            );
            foreach ($tpu as $ku => $uid) {
                if ($uid > 0) $uid = $this->getUserLogicName($uid);
                //fputs_utf8($fout, ";" . $tpa[$ku] . "=" . $uid);
                $data[] = sprintf("%s=%s", $tpa[$ku], $uid);
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
        if ($doc->name != "") $name = $doc->name;
        else if ($wprof) {
            $err = $doc->setNameAuto(true);
            $name = $doc->name;
        } else if ($wident) $name = $doc->id;
        else $name = '';
        $data = array();
        if ($eformat == "I") {
            $data = array(
                "DOC",
                $this->familyName,
                $name,
                $efldid
            );
            // fputs_utf8($fout, "DOC;" . $this->familyName . ";" . $name . ";" . $efldid . ";");
            
        }
        // write values
        foreach ($this->lattr as $attr) {
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
                if ($value != "") {
                    if (strstr($value, "\n") || ($attr->getOption("multiple") == "yes")) {
                        $tid = $doc->rawValueToArray($value);
                        $tn = array();
                        foreach ($tid as $did) {
                            $brtid = explode("<BR>", $did);
                            $tnbr = array();
                            foreach ($brtid as $brid) {
                                $n = getNameFromId($dbaccess, $brid);
                                if ($n) $tnbr[] = $n;
                                else $tnbr[] = $brid;
                            }
                            $tn[] = implode('<BR>', $tnbr);
                        }
                        $value = implode("\n", $tn);
                    } else {
                        $n = getNameFromId($dbaccess, $value);
                        if ($n) $value = $n;
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
                        $doc = new_Doc(getDbAccess() , $docid);
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
            if ($doc->profid == $doc->id) {
                $this->exportProfil($fout, $doc->id);
            } else if ($doc->profid > 0) {
                $name = getNameFromId($dbaccess, $doc->profid);
                $dname = $doc->name;
                if (!$dname) $dname = $doc->id;
                if (!$name) $name = $doc->profid;
                if (!isset($tdoc[$doc->profid])) {
                    $tdoc[$doc->profid] = true;
                    $pdoc = new_doc($dbaccess, $doc->profid);
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
}
