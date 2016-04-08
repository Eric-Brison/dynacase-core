<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Get OpenText Value for document
 * @class DocOooFormat
 *
 */
class DocOooFormat
{
    /**
     * @var Doc
     */
    public $doc = null;
    private $index = - 1;
    /**
     * @var NormalAttribute
     */
    private $oattr = null;
    private $attrid = '';
    /**
     * format set in type
     * @var string
     */
    private $cFormat = '';
    
    public function __construct(Doc & $doc)
    {
        $this->setDoc($doc);
    }
    
    public function setDoc(Doc & $doc)
    {
        $this->doc = $doc;
    }
    /**
     * @param NormalAttribute $oattr
     * @param string $value
     * @param int $index
     * @return string the formated value
     */
    public function getOooValue($oattr, $value, $index = - 1)
    {
        
        $this->oattr = $oattr;
        $this->index = $index;
        $this->cFormat = $this->oattr->format;
        $atype = $this->oattr->type;
        
        if (($this->oattr->repeat) && ($this->index <= 0)) {
            $tvalues = explode("\n", $value);
        } else {
            $tvalues[$this->index] = $value;
        }
        $this->attrid = $this->oattr->id;
        
        $thtmlval = array();
        foreach ($tvalues as $kvalue => $avalue) {
            $oooval = "";
            switch ($atype) {
                case "idoc":
                    // nothing
                    break;

                case "image":
                    
                    $oooval = $this->formatImage($avalue);
                    break;

                case "file":
                    // file name
                    $oooval = $this->formatFile($avalue);
                    break;

                case "longtext":
                case "xml":
                    
                    $oooval = $this->formatLongtext($avalue);
                    break;

                case "password":
                    
                    break;

                case "enum":
                    
                    $oooval = $this->formatEnum($avalue);
                    break;

                case "thesaurus":
                    
                    $oooval = $this->formatThesaurus($avalue);
                    break;

                case "array":
                    break;

                case "doc":
                    break;

                case "account":
                    $oooval = $this->formatAccount($avalue);
                    break;

                case "docid":
                    $oooval = $this->formatDocid($avalue);
                    break;

                case "option":
                    break;

                case "money":
                    //$oooval=str_replace(" ","&nbsp;",$oooval); // need to replace space by non breaking spaces
                    $oooval = $this->formatMoney($avalue);
                    break;

                case "htmltext":
                    
                    $oooval = $this->formatHtmltext($avalue);
                    break;

                case 'date':
                    
                    $oooval = $this->formatDate($avalue);
                    break;

                case 'time':
                    
                    $oooval = $this->formatTime($avalue);
                    break;

                case 'timestamp':
                    
                    $oooval = $this->formatTimestamp($avalue);
                    break;

                case 'ifile':
                    
                    $oooval = $this->formatIfile($avalue);
                    break;

                case 'color':
                    
                    $oooval = $this->formatColor($avalue);
                    break;

                default:
                    
                    $oooval = $this->formatDefault($avalue);
                    break;
            }
            
            if (($this->cFormat != "") && ($atype != "doc") && ($atype != "array") && ($atype != "option")) {
                //printf($oooval);
                $oooval = sprintf($this->xmlEncode($this->cFormat) , $oooval);
            }
            
            $thtmlval[$kvalue] = $oooval;
        }
        return implode("<text:tab/>", $thtmlval);
    }
    /**
     * format Default attribute
     *
     * @param string $avalue raw value of attribute
     * @return string openText value
     */
    public function formatDefault($avalue)
    {
        $oooval = $this->xmlEncode($avalue);
        //$oooval=str_replace('"','&quot;',$oooval);
        return $oooval;
    }
    
    public static function xmlEncode($s)
    {
        return str_replace(array(
            '&',
            '"',
            "<",
            ">"
        ) , array(
            "&amp;",
            '&quot;',
            "&lt;",
            "&gt;"
        ) , $s);
    }
    /**
     * format Idoc attribute
     *
     * @param string $avalue raw value of attribute
     * @return string openText value
     */
    public function formatIDoc(
    /** @noinspection PhpUnusedParameterInspection */
    
    $avalue)
    {
        
        $oooval = "";
        
        return $oooval;
    }
    /**
     * format Image attribute
     *
     * @param string $avalue raw value of attribute
     * @return string openText value
     */
    public function formatImage($avalue)
    {
        
        $oooval = $this->doc->vault_filename_fromvalue($avalue, true);
        
        return $oooval;
    }
    /**
     * format File attribute
     *
     * @param string $avalue raw value of attribute
     * @return string openText value
     */
    public function formatFile($avalue)
    {
        
        $oooval = $this->doc->vault_filename_fromvalue($avalue, false);
        return $oooval;
    }
    /**
     * format Longtext attribute
     *
     * @param string $avalue raw value of attribute
     * @return string openText value
     */
    public function formatLongtext($avalue)
    {
        
        $oooval = str_replace("&", "&amp;", $avalue);
        $oooval = str_replace(array(
            "<",
            ">"
        ) , array(
            "&lt;",
            "&gt;"
        ) , $oooval);
        $oooval = str_replace("\n", "<text:line-break/>", $oooval);
        $oooval = str_replace("&lt;BR&gt;", "<text:line-break/>", $oooval);
        $oooval = str_replace("\r", "", $oooval);
        return $oooval;
    }
    /**
     * format Image attribute
     *
     * @param string $avalue raw value of attribute
     * @return string
     */
    public function formatPassword(
    /** @noinspection PhpUnusedParameterInspection */
    $avalue)
    {
        
        $oooval = "*****";
        return $oooval;
    }
    /**
     * format Image attribute
     *
     * @param string $avalue raw value of attribute
     * @return string openText value
     */
    public function formatEnum($avalue)
    {
        
        $enumlabel = $this->oattr->getEnumlabel();
        $colors = $this->oattr->getOption("boolcolor");
        if ($colors != "") {
            if (isset($enumlabel[$avalue])) {
                reset($enumlabel);
                $tcolor = explode(",", $colors);
                if (current($enumlabel) == $enumlabel[$avalue]) {
                    $color = $tcolor[0];
                    $oooval = sprintf('<pre style="background-color:%s;display:inline">&nbsp;-&nbsp;</pre>', $color);
                } else {
                    $color = $tcolor[1];
                    $oooval = sprintf('<pre style="background-color:%s;display:inline">&nbsp;&bull;&nbsp;</pre>', $color);
                }
            } else $oooval = $avalue;
        } else {
            if (isset($enumlabel[$avalue])) $oooval = $enumlabel[$avalue];
            else $oooval = $avalue;
        }
        return $oooval;
    }
    /**
     * format Image attribute
     *
     * @param string $avalue raw value of attribute
     * @return string openText value
     */
    public function formatArray(
    /** @noinspection PhpUnusedParameterInspection */
    $avalue)
    {
        
        $oooval = "";
        return $oooval;
    }
    /**
     * format Doc attribute
     *
     * @param string $avalue raw value of attribute
     * @return string openText value
     */
    public function formatDoc(
    /** @noinspection PhpUnusedParameterInspection */
    $avalue)
    {
        $oooval = "";
        
        return $oooval;
    }
    /**
     * format Account attribute
     * @param $avalue
     * @return string HTML value
     */
    public function formatAccount($avalue)
    {
        if (!$this->oattr->format) $this->oattr->format = "x";
        return $this->formatDocid($avalue);
    }
    /**
     * format Docid attribute
     *
     * @param string $avalue raw value of attribute
     * @return string openText value
     */
    public function formatDocid($avalue)
    {
        
        if ($this->oattr->format != "") {
            
            $this->cFormat = "";
            $isLatest = $this->oattr->getOption("docrev", "latest") == "latest";
            
            $multiple = ($this->oattr->getOption("multiple") == "yes");
            
            if ($multiple) {
                $avalue = str_replace("\n", "<BR>", $avalue);
                $tval = explode("<BR>", $avalue);
                $thval = array();
                foreach ($tval as $kv => $vv) {
                    if (trim($vv) == "") $thval[] = $vv;
                    else $thval[] = $this->doc->getDocAnchor(trim($vv) , '', false);
                }
                $oooval = implode("<text:tab/>", $thval);
            } else {
                if ($avalue == "") $oooval = $avalue;
                else {
                    $title = DocTitle::getRelationTitle(trim($avalue) , $isLatest, $this->doc);
                    if ($title === false) $title = $this->doc->htmlEncode($this->oattr->getOption("noaccesstext", _("information access deny")));
                    $oooval = $this->doc->htmlEncode($title);
                }
            }
        } else $oooval = $avalue;
        return $oooval;
    }
    /**
     * format Thesaurus attribute
     *
     * @param string $avalue raw value of attribute
     * @return string openText value
     */
    public function formatThesaurus($avalue)
    {
        
        $this->cFormat = "";
        $multiple = ($this->oattr->getOption("multiple") == "yes");
        if ($multiple) {
            $avalue = str_replace("\n", "<BR>", $avalue);
            $tval = explode("<BR>", $avalue);
            $thval = array();
            foreach ($tval as $kv => $vv) {
                if (trim($vv) == "") $thval[] = $vv;
                else {
                    $thc = new_doc($this->doc->dbaccess, trim($vv));
                    if ($thc->isAlive()) $thval[] = $thc->getCustomTitle();
                    else $thval[] = "th error $vv";
                }
            }
            $oooval = implode("<text:tab/>", $thval);
        } else {
            if ($avalue == "") $oooval = $avalue;
            else {
                $thc = new_doc($this->doc->dbaccess, $avalue);
                if ($thc->isAlive()) $oooval = $thc->getCustomTitle();
                else $oooval = "th error $avalue";
            }
        }
        return $oooval;
    }
    /**
     * format Option attribute
     *
     * @param string $avalue raw value of attribute
     * @return string openText value
     */
    public function formatOption(
    /** @noinspection PhpUnusedParameterInspection */
    $avalue)
    {
        
        $oooval = "";
        return $oooval;
    }
    /**
     * format Money attribute
     *
     * @param string $avalue raw value of attribute
     * @return string openText value
     */
    public function formatMoney($avalue)
    {
        
        $oooval = money_format('%!.2n', doubleval($avalue));
        return $oooval;
    }
    /**
     * format Htmltext attribute
     *
     * @param string $avalue raw value of attribute
     * @return string openText value
     */
    public function formatHtmltext($avalue)
    {
        $oooval = '';
        $attrid = $this->oattr->id;
        $html_body = trim($avalue);
        if (!$html_body) return '';
        $html_body = \Dcp\Utils\htmlclean::normalizeHTMLFragment($html_body, $error);
        if ($error != '') {
            addWarningMsg(sprintf(_("Malformed HTML in attribute '%s' from document '%s': %s") , $this->oattr->id, $this->doc->title, $error));
        }
        if ($html_body === false) {
            return '';
        }
        $xhtml_body = \Dcp\Utils\htmlclean::convertHTMLFragmentToXHTMLDocument($html_body, $error);
        if ($error != '') {
            addWarningMsg(sprintf(_("Error converting HTML from attribute '%s' from document '%s': %s") , $this->oattr->id, $this->doc->title, $error));
        }
        if ($xhtml_body === false) {
            return '';
        }
        
        $xhtml_body = preg_replace("/<!--.*?-->/ums", "", $xhtml_body); //delete comments
        $xhtml_body = preg_replace_callback('/(<\/?)([^\s>]+)([^>]*)(>)/u', function ($matches)
        {
            return $this->_fixupStyle($matches[1], $matches[2], $matches[3], $matches[4]);
        }
        , $xhtml_body);
        $xhtml_body = $this->cleanhtml($xhtml_body);
        
        $domHtml = new \Dcp\Utils\XDOMDocument();
        $domHtml->load(DEFAULT_PUBDIR . "/CORE/Layout/html2odt.xsl");
        $xslt = new xsltProcessor;
        $xslt->importStyleSheet($domHtml);
        $dom = null;
        //	set_error_handler('HandleXmlError');
        try {
            $dom = new \Dcp\Utils\XDOMDocument();
            $dom->loadXML($xhtml_body);
        }
        catch(Exception $e) {
            addWarningMsg(sprintf(_("possible incorrect conversion HTML to ODT %s: %s") , $this->doc->title, $e->getMessage()));
            /*
            print "Exception catched:\n";
            print "Code: ".$e->getCode()."\n";
            print "Message: ".$e->getMessage()."\n";
            print  "Line: ".$e->getLine();
            // error in XML
            print "\n<br>ERRORXSLT:".$this->doc->id.$this->doc->title."\n";
            print "\n=========RAWDATA=================\n";
            print  $avalue;
            print "\n=========XMLDATA=================\n";
            print_r2($xmldata);
            exit;*/
            
            $dom = null;
        }
        //restore_error_handler();
        if ($dom) {
            $xmlout = $xslt->transformToXML($dom);
            
            $dxml = new \Dcp\Utils\XDOMDocument();
            $dxml->loadXML($xmlout);
            
            $ot = $dxml->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:office:1.0", "text");
            $ot1 = $ot->item(0);
            $officetext = $ot1->ownerDocument->saveXML($ot1);
            $oooval = str_replace(array(
                '<office:text>',
                '</office:text>',
                '<office:text/>'
            ) , "", $officetext);
            // work around : tables are not in paragraph
            $oooval = preg_replace('!(<text:p>\s*<table:table )!u', "<table:table ", $oooval);
            $oooval = preg_replace('!(</table:table>\s*</text:p>)!u', "</table:table> ", $oooval);
            
            $oooval = sprintf('<text:section text:style-name="Sect%s" text:name="Section%s" aid="%s">%s</text:section>', $attrid, $attrid, $attrid, $oooval);
        } else {
            addWarningMsg(sprintf(_("incorrect conversion HTML to ODT %s") , $this->doc->title));
        }
        return $oooval;
    }
    /**
     * function use by Doc::getOOoValue()
     * use to convert html to xhtml
     * @param string $lt the < character
     * @param string $tag the tag name
     * @param string $attr all attributes of tag
     * @param string $gt the > tag
     * @return string the new tag
     */
    protected function _fixupStyle($lt, $tag, $attr, $gt)
    {
        $px2mm = 3 / 16;
        $attr = str_replace(':=', '=', $attr);
        $inlineStyle = array();
        if (preg_match('/\s+style\s*=\s*"(?P<style>[^"]*)"/u', $attr, $m)) {
            $inlineStyle = $this->parseInlineStyle($m['style']);
        };
        if ($tag === "img") {
            if (isset($inlineStyle['width']) && preg_match('/^(?P<width>[0-9\.]+)(?P<unit>[a-z]*)$/u', $inlineStyle['width'], $m)) {
                // width in mm :
                switch ($m['unit']) {
                    case "px":
                        $width = intval($m['width']) * $px2mm;
                        break;

                    case "cm":
                        $width = floatval($m['width']) * 10;
                        break;

                    case "mm":
                        $width = intval($m['width']);
                        break;

                    default:
                        $width = intval($m['width']);
                }
                $attr = sprintf(' width="%d" ', $width) . $attr;
            }
            if (isset($inlineStyle['height']) && preg_match('/^(?P<height>[0-9\.]+)(?P<unit>[a-z]*)$/u', $inlineStyle['height'], $m)) {
                // height in mm
                switch ($m['unit']) {
                    case "px":
                        $height = intval($m['height']) * $px2mm;
                        break;

                    case "cm":
                        $height = floatval($m['height']) * 10;
                        break;

                    case "mm":
                        $height = intval($m['height']);
                        break;

                    default:
                        $height = intval($m['height']);
                }
                $attr = sprintf(' height="%d" ', $height) . $attr;
            }
        }
        return ($lt . $tag . $attr . $gt);
    }
    
    protected function cleanhtml($html)
    {
        $html = preg_replace(':</?span[^>]*>:us', "", $html);
        $html = preg_replace(':</?font[^>]*>:us', "", $html);
        $html = preg_replace(':</?meta[^>]*>:us', "", $html);
        $html = preg_replace(':<style[^>]*>.*?</style>:us', "", $html);
        $html = preg_replace(':<([^>]*) style=\"[^\"]*\":us', "<\\1", $html);
        $html = preg_replace(':<([^>]*) class=\"[^\"]*\":us', "<\\1", $html);
        return $html;
    }
    /**
     * format Date attribute
     *
     * @param string $avalue raw value of attribute
     * @return string openText value
     */
    public function formatDate($avalue)
    {
        
        if (($this->cFormat != "") && (trim($avalue) != "")) {
            if ($avalue) $oooval = strftime($this->cFormat, stringDateToUnixTs($avalue));
            else $oooval = $avalue;
        } elseif (trim($avalue) == "") {
            $oooval = "";
        } else {
            $oooval = stringDateToLocaleDate($avalue);
        }
        $this->cFormat = "";
        return $oooval;
    }
    /**
     * format Time attribute
     *
     * @param string $avalue raw value of attribute
     * @return string openText value
     */
    public function formatTime($avalue)
    {
        
        if ($this->cFormat != "") {
            if ($avalue) $oooval = strftime($this->cFormat, strtotime($avalue));
            else $oooval = $avalue;
            $this->cFormat = "";
        } else {
            $oooval = substr($avalue, 0, 5); // do not display second
            
        }
        return $oooval;
    }
    /**
     * format TimeStamp attribute
     *
     * @param string $avalue raw value of attribute
     * @return string openText value
     */
    public function formatTimestamp($avalue)
    {
        
        if (($this->cFormat != "") && (trim($avalue) != "")) {
            if ($avalue) $oooval = strftime($this->cFormat, stringDateToUnixTs($avalue));
            else $oooval = $avalue;
        } elseif (trim($avalue) == "") {
            $oooval = "";
        } else {
            $oooval = stringDateToLocaleDate($avalue);
        }
        $this->cFormat = "";
        return $oooval;
    }
    /**
     * format iFile attribute
     *
     * @param string $avalue raw value of attribute
     * @return string openText value
     */
    public function formatIfile(
    /** @noinspection PhpUnusedParameterInspection */
    $avalue)
    {
        global $action;
        $lay = new Layout("FDL/Layout/viewifile.xml", $action);
        $lay->set("aid", $this->oattr->id);
        $lay->set("id", $this->doc->id);
        $lay->set("iheight", $this->oattr->getOption("height", "200px"));
        $oooval = $lay->gen();
        return $oooval;
    }
    /**
     * format Color attribute
     *
     * @param string $avalue raw value of attribute
     * @return string openText value
     */
    public function formatColor($avalue)
    {
        
        $oooval = sprintf("<span style=\"background-color:%s\">%s</span>", $avalue, $avalue);
        return $oooval;
    }
    /**
     * Basic inline style CSS parser
     *
     * Parse the value of a style="..." attribute and return a hash containing
     * the property-names => property-values.
     *
     * @param $str
     * @return array hash of property-name => property-value
     */
    protected function parseInlineStyle($str)
    {
        $rules = array();
        preg_match_all('/(?P<propName>[\w-]+)\s*:\s*(?P<propValue>[^;]*)(;|$)/u', $str, $matches, PREG_SET_ORDER);
        foreach ($matches as $m) {
            $rules[strtolower($m['propName']) ] = $m['propValue'];
        }
        return $rules;
    }
}
