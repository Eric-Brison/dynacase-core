<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
*/

namespace Dcp;

class ExportXmlDocument
{
    /**
     * @var \Doc
     */
    protected $document = null;
    protected $exportProfil = false;
    
    protected $exportFiles = false;
    protected $exportDocumentNumericIdentiers = false;
    protected $attributeToExport = array();
    protected $includeSchemaReference = false;
    protected $structureAttributes;
    
    protected $verifyAttributeAccess = true;
    protected $writeToFile = false;
    /**
     * If true, attribute with "I" visibility are not returned
     * @param boolean $verifyAttributeAccess
     */
    public function setVerifyAttributeAccess($verifyAttributeAccess)
    {
        $this->verifyAttributeAccess = $verifyAttributeAccess;
    }
    /**
     * @param mixed $structureAttributes
     */
    public function setStructureAttributes($structureAttributes)
    {
        $this->structureAttributes = $structureAttributes;
    }
    /**
     * @param array $attributeToExport
     */
    public function setAttributeToExport($attributeToExport)
    {
        $this->attributeToExport = $attributeToExport;
    }
    /**
     * @param boolean $exportDocumentNumericIdentiers
     */
    public function setExportDocumentNumericIdentiers($exportDocumentNumericIdentiers)
    {
        $this->exportDocumentNumericIdentiers = $exportDocumentNumericIdentiers;
    }
    /**
     * @param boolean $exportFiles
     */
    public function setExportFiles($exportFiles)
    {
        $this->exportFiles = $exportFiles;
    }
    /**
     * @param boolean $includeSchemaReference
     */
    public function setIncludeSchemaReference($includeSchemaReference)
    {
        $this->includeSchemaReference = $includeSchemaReference;
    }
    /**
     * @param \Doc $document
     */
    public function setDocument($document)
    {
        $this->document = $document;
    }
    
    public function getXml()
    {
        if ($this->exportFiles) {
            throw new Exception("EXPC0103");
        }
        return $this->export();
    }
    
    public function writeTo($filePath)
    {
        $this->export($filePath);
    }
    protected function export($outfile = "")
    {
        $lay = new \Layout(getLayoutFile("FDL", "exportxml.xml"));
        //$lay=&$this->document->lay;
        $lay->set("famname", strtolower($this->document->fromname));
        $lay->set("id", ($this->exportDocumentNumericIdentiers ? $this->document->id : ''));
        $lay->set("name", $this->document->name);
        $lay->set("revision", $this->document->revision);
        $lay->set("version", $this->document->getVersion());
        $lay->set("state", $this->document->getState());
        $lay->set("title", str_replace(array(
            "&",
            '<',
            '>'
        ) , array(
            "&amp;",
            '&lt;',
            '&gt;'
        ) , $this->document->getTitle()));
        $lay->set("mdate", strftime("%FT%X", $this->document->revdate));
        $lay->set("flat", (!$this->includeSchemaReference || !$this->structureAttributes));
        $la = $this->document->GetFieldAttributes();
        $level1 = array();
        
        foreach ($la as $k => $v) {
            if ((!$v) || ($v->getOption("autotitle") == "yes") || ($v->usefor == 'Q')) unset($la[$k]);
        }
        $option = new \exportOptionAttribute();
        $option->outFile = $outfile;
        
        foreach ($la as $k => & $v) {
            if (($v->id != "FIELD_HIDDENS") && ($v->type == 'frame' || $v->type == "tab") && ((!$v->fieldSet) || $v->fieldSet->id == "FIELD_HIDDENS")) {
                // @TODO NOT HERE
                if ($this->verifyAttributeAccess && !$this->isAttributeAccessGranted($this->document, $v)) {
                    
                    $level1[] = array(
                        "level" => sprintf("<BOHH/>")
                    );
                } else {
                    $level1[] = array(
                        "level" => $this->getStructXmlValue($this->document, $v)
                    );
                }
            } else {
                // if ($v)  $tax[]=array("tax"=>$v->getXmlSchema());
                
            }
        }
        $lay->setBlockData("top", $level1);
        if ($outfile) {
            
            $this->writeToFile = true;
            if ($this->exportFiles) {
                $xmlcontent = $lay->gen();
                $fo = fopen($outfile, "w");
                if (!$fo) {
                    
                    throw new Exception("EXPC0101", $outfile);
                }
                $pos = strpos($xmlcontent, "[FILE64");
                
                $bpos = 0;
                while ($pos !== false) {
                    if (fwrite($fo, substr($xmlcontent, $bpos, $pos - $bpos))) {
                        $bpos = strpos($xmlcontent, "]", $pos) + 1;
                        
                        $filepath = substr($xmlcontent, $pos + 8, ($bpos - $pos - 9));
                        /* If you want to encode a large file, you should encode it in chunks that
                                            are a multiple of 57 bytes.  This ensures that the base64 lines line up
                                            and that you do not end up with padding in the middle. 57 bytes of data
                                            fills one complete base64 line (76 == 57*4/3):*/
                        $ff = fopen($filepath, "r");
                        $size = 6 * 1024 * 57;
                        while ($buf = fread($ff, $size)) {
                            fwrite($fo, base64_encode($buf));
                        }
                        $pos = strpos($xmlcontent, "[FILE64", $bpos);
                    } else {
                        throw new Exception("EXPC0102", $outfile);
                    }
                }
                fwrite($fo, substr($xmlcontent, $bpos));
                fclose($fo);
            } else {
                if (file_put_contents($outfile, $lay->gen()) === false) {
                    
                    throw new Exception("EXPC0100", $outfile);
                }
            }
        } else {
            $this->writeToFile = false;
            return $lay->gen();
        }
        return '';
    }
    /**
     * Verify is attribute has visible access
     * @param \Doc $doc the document to see
     * @param \BasicAttribute $attribute the attribut to see
     * @return bool return true if can be viewed
     */
    protected static function isAttributeAccessGranted(\Doc $doc, \BasicAttribute $attribute)
    {
        static $attributeGrants = array();
        $key = sprintf("%0d-%0d-%0d-%s", $doc->fromid, $doc->cvid, $doc->wid, $doc->state);
        
        if (!isset($attributeGrants[$key])) {
            $doc->setMask(\Doc::USEMASKCVVIEW);
            $attributeGrants[$key] = array();
            $oas = $doc->getNormalAttributes();
            foreach ($oas as $oa) {
                if ($oa->mvisibility === "I") {
                    $attributeGrants[$key][$oa->id] = false;
                }
            }
        }
        return (!isset($attributeGrants[$key][$attribute->id]));
    }
    /**
     * export values as xml fragment
     *
     * @param \Doc $doc working doc
     *
     * @return string
     */
    protected function getAttributeXmlValue(\Doc & $doc, \NormalAttribute $attribute, $indexValue)
    {
        if ($this->verifyAttributeAccess && !$this->isAttributeAccessGranted($this->document, $attribute)) {
            return sprintf("<%s granted=\"false\"/>", $attribute->id);
        }
        
        if ($indexValue > - 1) $v = $doc->getMultipleRawValues($attribute->id, null, $indexValue);
        else $v = $doc->getRawValue($attribute->id, null);
        //if (! $v) return sprintf("<!-- no value %s -->",$attribute->id);
        if ($attribute->getOption("autotitle") == "yes") {
            return sprintf("<!--autotitle %s %s -->", $attribute->id, $v);
        }
        if (($v === null) && ($attribute->type != 'array')) {
            if (($attribute->type == 'file') || ($attribute->type == 'image')) return sprintf('<%s mime="" title="" xsi:nil="true"/>', $attribute->id);
            else return sprintf('<%s xsi:nil="true"/>', $attribute->id);
        }
        switch ($attribute->type) {
            case 'timestamp':
            case 'date':
                $v = stringDateToIso($v);
                return sprintf("<%s>%s</%s>", $attribute->id, $v, $attribute->id);
            case 'array':
                $la = $doc->getAttributes();
                $xmlvalues = array();
                $av = $doc->getArrayRawValues($attribute->id);
                $axml = array();
                foreach ($av as $k => $col) {
                    $xmlvalues = array();
                    foreach ($col as $aid => $aval) {
                        $oa = $doc->getAttribute($aid);
                        if (empty($this->attributeToExport[$doc->fromid]) || in_array($aid, $this->attributeToExport[$doc->fromid])) {
                            $indexValue = $k;
                            $xmlvalues[] = $this->getAttributeXmlValue($doc, $oa, $indexValue);
                        }
                    }
                    $axml[] = sprintf("<%s>%s</%s>", $attribute->id, implode("\n", $xmlvalues) , $attribute->id);
                }
                $indexValue = - 1; // restore initial index
                return implode("\n", $axml);
            case 'image':
            case 'file':
                
                if (preg_match(PREGEXPFILE, $v, $reg)) {
                    if ($this->exportDocumentNumericIdentiers) {
                        $vid = $reg[2];
                    } else {
                        $vid = '';
                    }
                    $mime = $reg[1];
                    $name = $reg[3];
                    $base = getParam("CORE_EXTERNURL");
                    $href = $base . str_replace('&', '&amp;', $doc->getFileLink($attribute->id));
                    if ($this->exportFiles) {
                        $path = $doc->vault_filename_fromvalue($v, true);
                        
                        if (is_file($path)) {
                            if ($this->writeToFile) {
                                return sprintf('<%s vid="%d" mime="%s" title="%s">[FILE64:%s]</%s>', $attribute->id, $vid, $mime, $name, $path, $attribute->id);
                            } else {
                                return sprintf('<%s vid="%d" mime="%s" title="%s">%s</%s>', $attribute->id, $vid, $mime, $name, base64_encode(file_get_contents($path)) , $attribute->id);
                            }
                        } else {
                            return sprintf('<!-- file not found --><%s vid="%d" mime="%s" title="%s"/>', $attribute->id, $vid, $mime, $name, $attribute->id);
                        }
                    } else {
                        return sprintf('<%s vid="%d" mime="%s" href="%s" title="%s"/>', $attribute->id, $vid, $mime, $href, $attribute->encodeXml($name));
                    }
                } else {
                    return sprintf("<%s>%s</%s>", $attribute->id, $v, $attribute->id);
                }
            case 'thesaurus':
            case 'account':
            case 'docid':
                if (!$v) {
                    return sprintf('<%s xsi:nil="true"/>', $attribute->id);
                } else {
                    $info = getTDoc($doc->dbaccess, $v, array() , array(
                        "title",
                        "name",
                        "id",
                        "initid",
                        "locked"
                    ));
                    
                    if ($info) {
                        $docid = $info["id"];
                        $latestTitle = ($attribute->getOption("docrev", "latest") == "latest");
                        if ($latestTitle) {
                            $docid = $info["initid"];
                            if ($info["locked"] == - 1) {
                                $info["title"] = $doc->getLastTitle($docid);
                            }
                        }
                        if ($info["name"]) {
                            if ($this->exportDocumentNumericIdentiers) {
                                return sprintf('<%s id="%s" name="%s">%s</%s>', $attribute->id, $docid, $info["name"], $attribute->encodeXml($info["title"]) , $attribute->id);
                            } else {
                                return sprintf('<%s name="%s">%s</%s>', $attribute->id, $info["name"], $attribute->encodeXml($info["title"]) , $attribute->id);
                            }
                        } else {
                            if ($this->exportDocumentNumericIdentiers) {
                                return sprintf('<%s id="%s">%s</%s>', $attribute->id, $docid, $attribute->encodeXml($info["title"]) , $attribute->id);
                            } else {
                                
                                return sprintf('<%s>%s</%s>', $attribute->id, $attribute->encodeXml($info["title"]) , $attribute->id);
                            }
                        }
                    } else {
                        if ((strpos($v, '<BR>') === false) && (strpos($v, "\n") === false)) {
                            return sprintf('<%s id="%s">%s</%s>', $attribute->id, $v, _("unreferenced document") , $attribute->id);
                        } else {
                            
                            $tids = explode("\n", str_replace('<BR>', "\n", $v));
                            $mName = array();
                            $mId = array();
                            $foundName = false;
                            foreach ($tids as $id) {
                                $lName = getNameFromId($doc->dbaccess, $id);
                                $mName[] = $lName;
                                $mId[] = $id;
                                if ($lName) $foundName = true;
                            }
                            $sIds = '';
                            if ($this->exportDocumentNumericIdentiers) {
                                $sIds = sprintf('id="%s"', implode(',', $mId));
                            }
                            $sName = '';
                            if ($foundName) {
                                
                                $sName = sprintf('name="%s"', implode(',', $mName));
                            }
                            return sprintf('<%s %s %s>%s</%s>', $attribute->id, $sName, $sIds, _("multiple document") , $attribute->id);
                        }
                    }
                }
            default:
                return sprintf("<%s>%s</%s>", $attribute->id, $attribute->encodeXml($v) , $attribute->id);
            }
    }
    
    protected function getXmlValue(\Doc & $doc, \BasicAttribute $attribute, $indexValue)
    {
        if ($attribute->isNormal === true) {
            return $this->getAttributeXmlValue($doc, $attribute, $indexValue);
        } else {
            return $this->getStructXmlValue($doc, $attribute, $indexValue);
        }
    }
    /**
     * export values as xml fragment
     *
     * @param \Doc $doc working doc
     * @param \exportOptionAttribute $opt
     *
     * @return string
     */
    protected function getStructXmlValue(\Doc & $doc, \FieldSetAttribute $structAttribute, $indexValue = - 1)
    {
        $la = $doc->getAttributes();
        $xmlvalues = array();
        foreach ($la as $k => $v) {
            /**
             * @var \NormalAttribute $v
             */
            if ($v->fieldSet && $v->fieldSet->id == $structAttribute->id && (empty($this->attributeToExport[$doc->fromid]) || in_array($v->id, $this->attributeToExport[$doc->fromid]))) {
                $value = $this->getXmlValue($doc, $v, $indexValue);
                if ($v->type == "htmltext" && $this->exportFiles) {
                    $value = $v->prepareHtmltextForExport($value);
                    if ($this->exportFiles) {
                        $value = preg_replace_callback('/(&lt;img.*?)src="(((?=.*docid=(.*?)&)(?=.*attrid=(.*?)&)(?=.*index=(-?[0-9]+)))|(file\/(.*?)\/[0-9]+\/(.*?)\/(-?[0-9]+))).*?"/', function ($matches)
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
                            $docimg = new_Doc(getDbAccess() , $docid);
                            $attr = $docimg->getAttribute($attrid);
                            $tfiles = $docimg->vault_properties($attr);
                            $f = $tfiles[$index];
                            if (is_file($f["path"])) {
                                if ($this->writeToFile) {
                                    return sprintf('%s title="%s" src="data:%s;base64,[FILE64:%s]"', "\n" . $matches[1], unaccent($f["name"]) , $f["mime_s"], $f["path"]);
                                } else {
                                    return sprintf('%s title="%s" src="data:%s;base64,%s"', "\n" . $matches[1], unaccent($f["name"]) , $f["mime_s"], base64_encode(file_get_contents($f["path"])));
                                }
                            } else {
                                return sprintf('%s title="%s" src="data:%s;base64,file not found"', "\n" . $matches[1], unaccent($f["name"]) , $f["mime_s"]);
                            }
                        }
                        , $value);
                    }
                }
                $xmlvalues[] = $value;
            }
        }
        if (!$this->structureAttributes) return implode("\n", $xmlvalues);
        else return sprintf("<%s>%s</%s>", $structAttribute->id, implode("\n", $xmlvalues) , $structAttribute->id);
    }
}
