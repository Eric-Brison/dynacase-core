<?php
/*
 * @author Anakeen
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
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
    private $target = '_self';
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
    private $htmlLink = true;
    
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
     * @param string $target
     * @param bool $htmlLink
     * @param int $index
     * @return string the formated value
     */
    public function getOooValue($oattr, $value, $target = "_self", $htmlLink = true, $index = - 1)
    {
        
        $this->oattr = $oattr;
        $this->target = $target;
        $this->index = $index;
        $this->cFormat = $this->oattr->format;
        $atype = $this->oattr->type;
        $this->htmlLink = $htmlLink;
        
        if (($this->oattr->repeat) && ($this->index <= 0)) {
            $tvalues = explode("\n", $value);
        } else {
            $tvalues[$this->index] = $value;
        }
        $this->attrid = $this->oattr->id;
        
        $this->cFormat = $this->oattr->format;
        
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
                $oooval = sprintf($this->cFormat, $oooval);
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
        $oooval = $avalue;
        $oooval = str_replace(array(
            "<",
            ">",
            '&'
        ) , array(
            "&lt;",
            "&gt;",
            "&amp;"
        ) , $oooval);
        return $oooval;
    }
    /**
     * format Idoc attribute
     *
     * @param string $avalue raw value of attribute
     * @return string openText value
     */
    public function formatIDoc($avalue)
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
     */
    public function formatPassword($avalue)
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
    public function formatArray($avalue)
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
    public function formatDoc($avalue)
    {
        $oooval = "";
        
        return $oooval;
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
            $multiple = ($this->oattr->getOption("multiple") == "yes");
            $dtarget = $this->target;
            if ($this->target != "mail") {
                $ltarget = $this->oattr->getOption("ltarget");
                if ($ltarget != "") $dtarget = $ltarget;
            }
            if ($multiple) {
                $avalue = str_replace("\n", "<BR>", $avalue);
                $tval = explode("<BR>", $avalue);
                $thval = array();
                foreach ($tval as $kv => $vv) {
                    if (trim($vv) == "") $thval[] = $vv;
                    else $thval[] = $this->doc->getDocAnchor(trim($vv) , $dtarget, false);
                }
                $oooval = implode("<text:tab/>", $thval);
            } else {
                if ($avalue == "") $oooval = $avalue;
                elseif ($this->oattr->link != "") $oooval = $this->doc->getTitle($avalue);
                else $oooval = $this->doc->getDocAnchor(trim($avalue) , $dtarget, false);
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
                    if ($thc->isAlive()) $thval[] = $thc->getSpecTitle();
                    else $thval[] = "th error $vv";
                }
            }
            $oooval = implode("<text:tab/>", $thval);
        } else {
            if ($avalue == "") $oooval = $avalue;
            else {
                $thc = new_doc($this->doc->dbaccess, $avalue);
                if ($thc->isAlive()) $oooval = $thc->getSpecTitle();
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
    public function formatOption($avalue)
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
        $html_body = str_replace(array(
            '&quot;',
            '&lt;',
            '&gt;'
        ) , array(
            '--quoteric--',
            '--lteric--',
            '--gteric--'
        ) , $html_body); // prevent pb for quot in quot
        if ($html_body[0] != '<') {
            // think it is raw text
            $html_body = str_replace("\n<br/>", "\n", $html_body);
            $html_body = str_replace('<br/>', "\n", $html_body);
            if (!strpos($html_body, '<br')) $html_body = str_replace(array(
                "<",
                ">",
                '&'
            ) , array(
                "&lt;",
                "&gt;",
                "&amp;"
            ) , $html_body);
            $html_body = '<p>' . nl2br($html_body) . '</p>';
        }
        $html_body = str_replace(">\r\n", ">", $html_body);
        $html_body = str_replace("\r", "", $html_body);
        
        $html_body = preg_replace("/<!--.*?-->/ms", "", $html_body); //delete comments
        $html_body = preg_replace("/<td(\s[^>]*?)?>(.*?)<\/td>/mse", "\$this->getHtmlTdContent('\\1','\\2')", $html_body); // accept only text in td tag
        $html_body = cleanhtml($html_body);
        $html_body = preg_replace("/(<\/?)([^\s>]+)([^>]*)(>)/e", "toxhtmltag('\\1','\\2','\\3','\\4')", $html_body); // begin tag transform to pseudo xhtml
        $html_body = str_replace(array(
            '\"',
            '&quot;'
        ) , '"', $html_body);
        $html_body = str_replace('&', '&amp;', html_entity_decode($html_body, ENT_NOQUOTES, 'UTF-8'));
        
        $html_body = str_replace(array(
            '--quoteric--',
            '--lteric--',
            '--gteric--'
        ) , array(
            '&quot;',
            '&lt;',
            '&gt;'
        ) , $html_body); // prevent pb for quot in quot
        $xmldata = '<xhtml:body xmlns:xhtml="http://www.w3.org/1999/xhtml">' . $html_body . "</xhtml:body>";
        $domHtml = new DOMDocument();
        $domHtml->load(DEFAULT_PUBDIR . "/CORE/Layout/html2odt.xsl");
        $xslt = new xsltProcessor;
        $xslt->importStyleSheet($domHtml);
        $dom = null;
        //	set_error_handler('HandleXmlError');
        try {
            $dom = new DOMDocument();
            @$dom->loadXML($xmldata);
        }
        catch(Exception $e) {
            addWarningMsg(sprintf(_("possible incorrect conversion HTML to ODT %s") , $this->doc->title));
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
        }
        //restore_error_handler();
        if ($dom) {
            $xmlout = $xslt->transformToXML($dom);
            $dxml = new DomDocument();
            $dxml->loadXML($xmlout);
            //office:text
            $ot = $dxml->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:office:1.0", "text");
            $ot1 = $ot->item(0);
            $officetext = $ot1->ownerDocument->saveXML($ot1);
            $oooval = str_replace(array(
                '<office:text>',
                '</office:text>',
                '<office:text/>'
            ) , "", $officetext);
            // work around : tables are not in paragraph
            $oooval = preg_replace("/(<text:p>[\s]*<table:table )/ ", "<table:table ", $oooval);
            $oooval = preg_replace("/(<\/table:table>[\s]*<\/text:p>)/ ", "</table:table> ", $oooval);
            
            $pppos = mb_strrpos($oooval, '</text:p>');
            
            $oooval = sprintf('<text:section text:style-name="Sect%s" text:name="Section%s" aid="%s">%s</text:section>', $attrid, $attrid, $attrid, $oooval);
        } else {
            
            addWarningMsg(sprintf(_("incorrect conversion HTML to ODT %s") , $this->doc->title));
        }
        //$oooval=preg_replace("/<\/?(\w+[^:]?|\w+\s.*?)>//g", "",$oooval  );
        return $oooval;
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
            if ($avalue) $oooval = strftime($this->cFormat, FrenchDateToUnixTs($avalue));
            else $oooval = $avalue;
        } elseif (trim($avalue) == "") {
            $oooval = "";
        } else {
            $oooval = FrenchDateToLocaleDate($avalue);
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
            if ($avalue) $oooval = strftime($this->cFormat, FrenchDateToUnixTs($avalue));
            else $oooval = $avalue;
        } elseif (trim($avalue) == "") {
            $oooval = "";
        } else {
            $oooval = FrenchDateToLocaleDate($avalue);
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
    public function formatIfile($avalue)
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
     * return raw content
     * @param string $attr html tag attributes
     * @param string $data html content (innerHTML)
     * @return string raw content
     */
    private static function getHtmlTdContent($attr, $data)
    {
        $data = preg_replace('|<(/?[^> ]+)(\s[^>]*?)?>|ms', '', $data); // delete all tags
        return '<td>' . $data . '</td>';
    }
}
?>