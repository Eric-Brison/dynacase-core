<?php
/*
 * @author Anakeen
 * @package FDL
*/
/**
 * Layout Class for OOo files
 *
 * @author Anakeen
 * @version $Id: Class.OOoLayout.php,v 1.16 2008/10/31 17:01:18 eric Exp $
 * @package FDL
 * @subpackage CORE
 */
/**
 */

include_once ('Class.Layout.php');
include_once ('Lib.FileMime.php');
/**
 * @class OOoLayout
 * use an open document text file as template
 */
class OOoLayout extends Layout
{
    //############################################
    //#
    private $strip = 'Y';
    public $encoding = "utf-8";
    
    private $saved_sections = array();
    private $added_images = array();
    private $removed_images = array();
    //########################################################################
    //# Public methods
    //#
    //#
    public $content_template = '';
    public $style_template = '';
    public $meta_template = '';
    public $template = '';
    public $manifest = '';
    
    protected $arrayKeys = array();
    protected $arrayMainKeys = array();
    protected $rkeyxml = array();
    protected $cibledir;
    /**
     * @var array error list
     */
    protected $errors = array();
    /**
     /**
     * @var DOMDocument
     */
    protected $dom;
    /**
     * construct template using an open document text file
     * @param string $caneva open document file of the template
     * @param Action $action current action
     * @param Doc $doc document
     */
    public function __construct($caneva = "", Action & $action = null, Doc & $doc = null)
    {
        $this->LOG = new Log("", "Layout");
        $this->doc = $doc;
        $this->template = "";
        $this->action = & $action;
        $this->generation = "";
        $file = $caneva;
        $this->file = "";
        if ($caneva != "") {
            if ((!file_exists($file)) && ($file[0] != '/')) {
                $file = DEFAULT_PUBDIR . "/$file"; // try absolute
                
            }
            if (file_exists($file)) {
                if (filesize($file) > 0) {
                    $this->odf2content($file);
                    $this->file = $file;
                }
            } else {
                
                $this->template = "file  [$caneva] not exists";
            }
        }
    }
    /**
     * return inside string of a node
     * @param DOMnode $node
     * @return string
     */
    protected function innerXML(DOMnode & $node)
    {
        if (!$node) return false;
        $document = $node->ownerDocument;
        $nodeAsString = $document->saveXML($node);
        preg_match('!\<.*?\>(.*)\</.*?\>!s', $nodeAsString, $match);
        return $match[1];
    }
    /**
     * @deprecated BLOCK are not supported
     * Enter description here ...
     * @param string $block
     * @param string $aid
     * @param string $vkey
     * @return string
     */
    protected function parseListInBlock($block, $aid, $vkey)
    {
        deprecatedFunction();
        $head = '<?xml version="1.0" encoding="UTF-8"?>
<office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" office:version="1.0">';
        $foot = '</office:document-content>';
        $domblock = new DOMDocument();
        $frag1 = '';
        $frag2 = '';
        $block = trim($block);
        if (substr($block, 0, 2) == '</') {
            // fragment of block
            $firsttag = strpos($block, '>');
            $lasttag = strrpos($block, '<');
            $frag1 = substr($block, 0, $firsttag + 1);
            $frag2 = substr($block, $lasttag);
            $block = substr($block, $firsttag + 1, $lasttag - strlen($block));
            // print("\nfrag1:$frag1  $lasttag rag2:$frag2\n");
            //      print("\n====================\n");
            //print("\nNB:[$block]\n");
            
        }
        
        if (!$domblock->loadXML($head . $block . $foot)) {
            print "\n=============\n";
            print $head . trim($block) . $foot;
            return $block;
        }
        
        $lists = $domblock->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0", "list");
        foreach ($lists as $list) {
            /**
             * @var $list DOMElement
             */
            $items = $list->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0", "list-item");
            if ($items->length > 0) {
                $item = $items->item(0);
                
                if (preg_match('/\[V_[A-Z0-9_-]+\]/', $item->textContent, $reg)) {
                    $skey = $reg[0];
                    //	    print "serack key : [$skey] [$aid] [$vkey]";
                    if ($skey == $aid) {
                        //  $vkey=$this->rkey[$key];
                        $tvkey = explode('<text:tab/>', $vkey);
                        
                        foreach ($tvkey as $key) {
                            $clone = $item->cloneNode(true);
                            $item->parentNode->appendChild($clone);
                            $this->replaceNodeText($clone, $reg[0], $key);
                        }
                        $item->parentNode->removeChild($item);
                    }
                }
            }
        }
        return $frag1 . $this->innerXML($domblock->firstChild) . $frag2;
        //return $frag1 . $domblock->saveXML($domblock->firstChild->firstChild) . $frag2;
        
    }
    
    protected function getAncestor(&$node, $type)
    {
        $mynode = $node;
        while (!empty($mynode->parentNode)) {
            $mynode = $mynode->parentNode;
            if ($mynode->tagName == $type) {
                return $mynode;
            }
        }
        return false;
    }
    /**
     * get depth in dom tree
     * @param DOMNode $node
     * @return int
     */
    private function getNodeDepth(DOMNode & $node)
    {
        $mynode = $node;
        $depth = 0;
        while (!empty($mynode->parentNode)) {
            $depth++;
            $mynode = $mynode->parentNode;
        }
        return $depth;
    }
    
    protected function ParseBlock(&$out = null)
    {
        $this->template = preg_replace_callback('/(?m)\[BLOCK\s*([^\]]*)\](.*?)\[ENDBLOCK\s*\\1\]/s', function ($matches)
        {
            /** @noinspection PhpDeprecationInspection */
            return $this->SetBlock($matches[1], $matches[2]);
        }
        , $this->template);
    }
    /**
     *
     * @param string $name name of the IF
     * @param string $block xml string which containt the condition
     * @param boolean $not negative condition
     * @param array $levelPath Path use to retrieve condition value in recursive repeatable mode
     * @return string
     */
    protected function TestIf($name, $block, $not = false, $levelPath = null)
    {
        $out = "";
        $cond = null;
        if ($levelPath) {
            $val = $this->getArrayKeyValue($name, $levelPath);
            if (is_array($val)) $val = null; // it is not the good level
            if ($val !== null) $cond = ($val == true);
        } else {
            if (isset($this->rif[$name]) && $this->rif[$name] !== null) $cond = ($this->rif[$name] == true);
            elseif (isset($this->rkey[$name]) && $this->rkey[$name] !== null) $cond = ($this->rkey[$name] == true);
        }
        if ($cond !== null) {
            if ($cond xor $not) {
                $out = $block;
            }
        } else {
            // return  condition
            if ($not) $out = "[IFNOT $name]" . $block . "[ENDIF $name]";
            else $out = "[IF $name]" . $block . "[ENDIF $name]";
        }
        if ($this->strip == 'Y') $out = str_replace("\\\"", "\"", $out);
        return ($out);
    }
    /**
     * Top level parse condition
     * @param string|null $out
     * @throws \Dcp\Exception
     */
    protected function ParseIf(&$out = null)
    {
        $templateori = '';
        $level = 0;
        //header('Content-type: text/xml; charset=utf-8');print $this->template;exit;
        while ($templateori != $this->template && ($level < 10)) {
            $templateori = $this->template;
            $this->template = preg_replace_callback('/(?m)\[IF(NOT)?\s*([^\]]*)\](.*?)\[ENDIF\s*\\2\]/s', function ($matches)
            {
                return $this->TestIf($matches[2], $matches[3], $matches[1]);
            }
            , $this->template);
            $level++; // to prevent infinite loop
            
        }
        $this->fixSpanIf($this->template);
        // header('Content-type: text/xml; charset=utf-8');print $this->template;exit;
        // restore user fields
        if (!$this->dom->loadXML($this->template)) {
            print $this->template;
            throw new Dcp\Exception("Error in parse condition");
        }
        //header('Content-type: text/xml; charset=utf-8');print $this->dom->saveXML();exit;
        $lists = $this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0", "user-field-get");
        
        $domElemsToRemove = array();
        $domElemsToClean = array();
        foreach ($lists as $list) {
            /**
             * @var $list DOMElement
             */
            if (!$list->getAttribute('office:string-value')) {
                if ($list->textContent == '') {
                    $domElemsToRemove[] = $list;
                } else {
                    //$list->setAttribute("text:name",'');
                    $domElemsToClean[] = $list;
                }
            }
        }
        /**
         * @var $domElemsToRemove DOMElement[]
         */
        foreach ($domElemsToRemove as $domElement) {
            $domElement->parentNode->removeChild($domElement);
        }
        
        $this->template = $this->dom->saveXML();
    }
    /**
     * to not parse user fields set
     */
    protected function hideUserFieldSet()
    {
        //$this->dom->loadXML($this->template);
        $lists = $this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0", "user-field-decl");
        foreach ($lists as $list) {
            /**
             * @var $list DOMElement
             */
            $list->setAttribute('office:string-value', str_replace('[', '-CROCHET-', $list->getAttribute('office:string-value')));
            $list->setAttribute('text:name', str_replace('[', '-CROCHET-', $list->getAttribute('text:name')));
        }
        // detect user field to force it into a span
        $lists = $this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0", "user-field-get");
        /**
         * @var $list DOMElement
         */
        foreach ($lists as $list) {
            /**
             * @var DOMElement $lp
             */
            $lp = $list->parentNode;
            if ($lp->tagName != 'text:span') {
                $nt = $this->dom->createElement("text:span");
                $lp->insertBefore($nt, $list);
                $nt->appendChild($list);
            }
        }
        // header('Content-type: text/xml; charset=utf-8');print $this->dom->saveXML();exit;
        $lists = $this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0", "user-field-get");
        $userFields = array();
        // set the key of fields to up
        foreach ($lists as $list) {
            $textContent = $list->nodeValue;
            if (substr($textContent, 0, 1) == '[') {
                $userFields[] = $list;
                $nt = $this->dom->createTextNode($textContent);
                $list->parentNode->insertBefore($nt, $list);
            }
        }
        foreach ($userFields as $list) {
            $list->parentNode->removeChild($list);
        }
        
        $this->template = $this->dom->saveXML();
    }
    /**
     * to get xml warning as Exception
     * @param string $strXml
     * @return \Dcp\Utils\XDOMDocument
     * @throws \Dcp\Utils\XDOMDocumentException
     */
    protected function XmlLoader($strXml)
    {
        $this->dom = new \Dcp\Utils\XDOMDocument();
        $this->dom->loadXML($strXml);
        return $this->dom;
    }
    /**
     * replace brackets
     */
    protected function restoreUserFieldSet()
    {
        try {
            $this->XmlLoader(\Dcp\Utils\htmlclean::cleanXMLUTF8($this->template));
        }
        catch(\Dcp\Utils\XDOMDocumentException $e) {
            $outfile = uniqid(getTmpDir() . "/oooKo") . '.xml';
            $this->addError("LAY0004", $outfile);
            file_put_contents($outfile, $this->template);
            $this->exitError($outfile);
        }
        /*  if (!$this->dom->loadXML($this->template)) {
        
            $xmlErr=libxml_get_last_error();
            if (is_array($xmlErr)) $err=sprintf("XML error line %d, column %d,: %s",$xmlErr["line"],$xmlErr["column"], $xmlErr["message"]);
            else $err="";
        
            $outfile = uniqid(getTmpDir() . "/oooKo") . '.xml';
            $this->addError("LAY0004", $outfile);
            file_put_contents($outfile, $this->template);
            $this->exitError($outfile);
        }*/
        
        $lists = $this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0", "user-field-decl");
        
        foreach ($lists as $list) {
            /**
             * @var $list DOMElement
             */
            $list->setAttribute('office:string-value', str_replace('-CROCHET-', '[', $list->getAttribute('office:string-value')));
            $list->setAttribute('text:name', str_replace('-CROCHET-', '[', $list->getAttribute('text:name')));
        }
        $this->template = $this->dom->saveXML();
    }
    /**
     * not use for the moment
     * @deprecated BLOCK are not supported
     * @param $name
     * @param $block
     * @return string
     */
    protected function SetBlock($name, $block)
    {
        deprecatedFunction();
        if ($this->strip == 'Y') {
            //      $block = StripSlashes($block);
            $block = str_replace("\\\"", "\"", $block);
        }
        $out = "";
        if (isset($this->data) && isset($this->data["$name"]) && is_array($this->data["$name"])) {
            foreach ($this->data["$name"] as $k => $v) {
                $loc = $block;
                
                foreach ($this->corresp["$name"] as $k2 => $v2) {
                    
                    if (strstr($v[$v2], '<text:tab/>')) {
                        /** @noinspection PhpDeprecationInspection */
                        $loc = $this->parseListInBlock($loc, $k2, $v[$v2]);
                    } elseif ((!is_object($v[$v2])) && (!is_array($v[$v2]))) $loc = str_replace($k2, $v[$v2], $loc);
                }
                $this->rif = & $v;
                //	$this->ParseIf($loc);
                $out.= $loc;
            }
        }
        //    $this->ParseBlock($out);
        return ($out);
    }
    /**
     * not use for the moment
     * @deprecated ZONE are not supported
     * @param $out
     */
    protected function ParseZone(&$out)
    {
        deprecatedFunction();
        
        $out = preg_replace_callback('/\[ZONE\s*([^:]*):([^\]]*)\]/', function ($matches)
        {
            return $this->execute($matches[1], $matches[2]);
        }
        , $out);
    }
    /**
     * replace simple key in xml string
     * @param string|null $out
     */
    protected function ParseKey(&$out = null)
    {
        if (isset($this->rkey)) {
            $this->template = str_replace($this->pkey, $this->rkey, $this->template);
        }
    }
    /**
     * read odt file and insert xmls in object
     * @param string $odtfile path to the odt file
     * @return string
     * @throws \Dcp\Layout\Exception|\Dcp\Core\Exception
     */
    protected function odf2content($odtfile)
    {
        if (!file_exists($odtfile)) {
            $this->addError("LAY0001", $odtfile);
            $this->exitError();
        }
        $this->cibledir = uniqid(getTmpDir() . "/odf");
        
        $cmd = sprintf("unzip %s -d %s 2>&1", escapeshellarg($odtfile) , escapeshellarg($this->cibledir));
        if (exec($cmd, $out, $ret) === false) {
            $err = error_get_last();
            if (isset($err['message'])) {
                $err = $err['message'];
            } else {
                $err = 'unknown PHP error...';
            }
            throw new Dcp\Core\Exception("LAY0006", $err);
        }
        if ($ret !== 0) {
            $err = join("\n", $out);
            throw new Dcp\Core\Exception("LAY0007", $odtfile, $err);
        }
        
        $contentxml = $this->cibledir . "/content.xml";
        if (file_exists($contentxml)) {
            $this->content_template = file_get_contents($contentxml);
            unlink($contentxml);
        }
        $contentxml = $this->cibledir . "/META-INF/manifest.xml";
        if (file_exists($contentxml)) {
            $this->manifest = file_get_contents($contentxml);
            unlink($contentxml);
        }
        $contentxml = $this->cibledir . "/styles.xml";
        if (file_exists($contentxml)) {
            $this->style_template = file_get_contents($contentxml);
            unlink($contentxml);
        }
        $contentxml = $this->cibledir . "/meta.xml";
        if (file_exists($contentxml)) {
            $this->meta_template = file_get_contents($contentxml);
            unlink($contentxml);
        }
        return '';
    }
    /**
     * recompose odt file
     * @param string $odsfile output file path
     * @return string
     */
    protected function content2odf($odsfile)
    {
        if (file_exists($odsfile)) return "file $odsfile must not be present";
        
        $contentxml = $this->cibledir . "/content.xml";
        
        $this->content_template = preg_replace("!</?text:bookmark-(start|end)([^>]*)>!s", "", $this->content_template);
        //$this->content_template=preg_replace("!<text:section>(\s*<text:p/>)+!s","<text:section>",$this->content_template);
        //$this->content_template=preg_replace("!(<text:p/>\s*)+</text:section>!s","</text:section>",$this->content_template);
        //$this->content_template=preg_replace("/<text:span([^>]*)>\s*<text:section>/s","<text:section>",$this->content_template);
        //$this->content_template=preg_replace("/<\/text:section>\s*<\/text:span>/s","</text:section>",$this->content_template);
        //$this->content_template=preg_replace("/<text:p([^>]*)>\s*<text:section([^>]*)>/s","<text:section\\2>",$this->content_template);
        //$this->content_template=preg_replace("/<\/text:section>\s*<\/text:p>/s","</text:section>",$this->content_template);
        //$this->content_template=preg_replace("/<text:p ([^>]*)>\s*<text:([^\/]*)\/>\s*<text:section[^>]*>/s","<text:section><text:\\2/>",$this->content_template);
        //$this->content_template=preg_replace("/<\/text:section>\s*<text:([^\/]*)\/>\s*<\/text:p>/s","</text:section><text:\\1/>",$this->content_template);
        //$this->content_template=preg_replace("/<table:table-cell ([^>]*)>\s*<text:section>/s","<table:table-cell \\1>",$this->content_template);
        //$this->content_template=preg_replace("/<\/text:section>\s*<\/table:table-cell>/s","</table:table-cell>",$this->content_template);
        $this->content_template = str_replace("&lt;text:line-break/&gt;", "<text:line-break/>", $this->content_template);
        //  header('Content-type: text/xml; charset=utf-8');print($this->content_template);exit;
        file_put_contents($contentxml, $this->content_template);
        
        $contentxml = $this->cibledir . "/META-INF/manifest.xml";
        file_put_contents($contentxml, $this->manifest);
        
        $contentxml = $this->cibledir . "/styles.xml";
        file_put_contents($contentxml, $this->style_template);
        
        $contentxml = $this->cibledir . "/meta.xml";
        file_put_contents($contentxml, $this->meta_template);
        
        $cmd = sprintf("cd %s;zip -q -Z store -X %s mimetype ;zip -q -r -X -u  %s  *  && /bin/rm -fr %s", escapeshellarg($this->cibledir) , escapeshellarg($odsfile) , escapeshellarg($odsfile) , escapeshellarg($this->cibledir));
        
        system($cmd);
        //rmdir($this->cibledir);
        return '';
    }
    
    protected function execute($appname, $actionargn)
    {
        
        if ($this->action == "") return ("Layout not used in a core environment");
        // analyse action & its args
        $actionargn = str_replace(":", "--", $actionargn); //For buggy function parse_url in PHP 4.3.1
        $acturl = parse_url($actionargn);
        $actionname = $acturl["path"];
        
        global $ZONE_ARGS;
        $OLD_ZONE_ARGS = $ZONE_ARGS;
        if (isset($acturl["query"])) {
            $acturl["query"] = str_replace("--", ":", $acturl["query"]); //For buggy function parse_url in PHP 4.3.1
            $zargs = explode("&", $acturl["query"]);
            foreach ($zargs as $k => $v) {
                if (preg_match("/([^=]*)=(.*)/", $v, $regs)) {
                    // memo zone args for next action execute
                    $ZONE_ARGS[$regs[1]] = urldecode($regs[2]);
                }
            }
        }
        
        if ($appname != $this->action->parent->name) {
            $appl = new Application();
            $appl->Set($appname, $this->action->parent);
        } else {
            $appl = & $this->action->parent;
        }
        
        if (($actionname != $this->action->name) || ($OLD_ZONE_ARGS != $ZONE_ARGS)) {
            $act = new Action();
            $res = '';
            if ($act->Exists($actionname, $appl->id)) {
                
                $act->Set($actionname, $appl);
            } else {
                // it's a no-action zone (no ACL, cannot be call directly by URL)
                $act->name = $actionname;
                
                $res = $act->CompleteSet($appl);
            }
            if ($res == "") {
                $res = $act->execute();
            }
            $ZONE_ARGS = $OLD_ZONE_ARGS; // restore old zone args
            return ($res);
        } else {
            return ("Fatal loop : $actionname is called in $actionname");
        }
    }
    /**
     * set key/value pair assume key if XML fragment well formed
     * @param string $tag the key to replace
     * @param string|string[] $val the value for the key
     */
    public function set($tag, $val)
    {
        if (!isUTF8($val)) $val = utf8_encode($val);
        if (!$this->isXml($val)) {
            $this->pkey[$tag] = "[$tag]";
            if (is_array($val)) $val = implode('<text:tab/>', $val);
            $this->rkey[$tag] = $val;
        } else {
            
            $this->rkeyxml[$tag] = $val;
        }
    }
    /**
     * set key/value pair and XML entity encode
     * @param string $tag the key to replace
     * @param string $val the value for the key
     */
    public function eSet($tag, $val)
    {
        $this->set($tag, $this->xmlEntities($val));
    }
    /**
     * replace entities & < >
     * @param string $s text to encode
     * @return string
     */
    static public function xmlEntities($s)
    {
        return str_replace(array(
            "&",
            '<',
            '>'
        ) , array(
            "&amp;",
            '&lt;',
            '&gt;'
        ) , $s);
    }
    /**
     *
     * @param string $val
     * @return bool
     */
    protected function isXML($val)
    {
        return false;
        //return preg_match("/<text:/", $val);
        
    }
    /**
     * get value of $tag key
     * @param string $tag
     * @return string
     */
    public function get($tag)
    {
        if (isset($this->rkey)) return $this->rkey[$tag];
        return "";
    }
    /**
     * parse text
     * @param string|null $out
     */
    protected function ParseText(&$out = null)
    {
        $this->template = preg_replace_callback('/\[TEXT(\([^\)]*\))?:([^\]]*)\]/', function ($matches)
        {
            $s = $matches[2];
            if ($s == "") return $s;
            if (!$matches[1]) {
                return _($s);
            } else {
                return ___($s, trim($matches[1], '()'));
            }
        }
        , $this->template);
    }
    /**
     *
     */
    protected function updateManifest()
    {
        $manifest = new DomDocument();
        $manifest->loadXML($this->manifest);
        /**
         * @var DOMDocument $manifest_root
         */
        $manifest_root = null;
        $items = $manifest->childNodes;
        /**
         * @var $item DOMElement
         */
        foreach ($items as $item) {
            if ($item->nodeName == 'manifest:manifest') {
                $manifest_root = $item;
                break;
            }
        }
        if ($manifest_root === null) {
            return false;
        }
        
        $items = $manifest->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:manifest:1.0", "file-entry");
        foreach ($items as $aItem) {
            /**
             * @var $aItem DOMElement
             */
            $type = $aItem->getAttribute("manifest:media-type");
            if (substr($type, 0, 6) == "image/") {
                $file = $aItem->getAttribute("manifest:full-path");
                if (in_array($file, $this->removed_images)) {
                    $aItem->parentNode->removeChild($aItem);
                }
            }
        }
        foreach ($this->added_images as $image) {
            $mime = getSysMimeFile($this->cibledir . '/' . $image);
            $new = $manifest->createElement("manifest:file-entry");
            $new->setAttribute("manifest:media-type", $mime);
            $new->setAttribute("manifest:full-path", $image);
            $manifest_root->appendChild($new);
        }
        
        $this->manifest = $manifest->saveXML();
        return true;
    }
    /**
     *  parse images
     */
    protected function parseHtmlDraw()
    {
        $draws = $this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:drawing:1.0", "frame");
        foreach ($draws as $draw) {
            /**
             * @var $draw DOMElement
             */
            $name = trim($draw->getAttribute('draw:name'));
            if ($name === "htmlgraphic") {
                $this->setHtmlDraw($draw);
            }
        }
    }
    /**
     * set image from html fragment
     * @param DOMElement $node
     * @return string
     */
    protected function setHtmlDraw(DOMElement & $draw)
    {
        
        $imgs = $draw->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:drawing:1.0", "image");
        $err = "";
        if ($imgs->length > 0) {
            /**
             * @var $img DOMElement
             */
            $img = $imgs->item(0);
            
            $href = $img->getAttribute('xlink:href');
            $fileInfo = new VaultFileInfo();
            
            if (preg_match('/^file\/([^\/]+)\/([0-9]+)/', $href, $reg)) {
                $vid = $reg[2];
                $docid = $reg[1];
                $docimg = new_doc('', $docid, true);
                if ($docimg->isAlive()) {
                    $err = $docimg->control("view");
                    if (!$err) {
                        $fileInfo = \Dcp\VaultManager::getFileInfo($vid);
                    } else {
                        $fileInfo->path = "Images/erreur.png";
                    }
                } else {
                    $fileInfo->path = "Images/noimage.png";
                }
            } elseif (preg_match('/action=EXPORTFILE.*&docid=([^&]+).*&attrid=([a-z0_9_-]+).*index=([0-9-]+)/i', $href, $reg)) {
                
                $docid = $reg[1];
                $attrid = $reg[2];
                $index = intval($reg[3]);
                
                $docimg = new_doc('', $docid, true);
                if ($docimg->isAlive()) {
                    $err = $docimg->control("view");
                    if (!$err) {
                        if ($index < 0) {
                            $fileValue = $docimg->getRawValue($attrid);
                        } else {
                            $fileValue = $docimg->getMultipleRawValues($attrid, '', $index);
                        }
                        $fileInfo = (Object)$docimg->getFileInfo($fileValue);
                    } else {
                        $fileInfo->path = "Images/erreur.png";
                    }
                } else {
                    $fileInfo->path = "Images/noimage.png";
                }
            }
            if ($fileInfo->path) {
                $href = sprintf('Pictures/dcp%s', basename($fileInfo->path));
                $img->setAttribute('xlink:href', $href);
                $this->added_images[] = $href;
                if (!is_dir($this->cibledir . '/Pictures')) {
                    mkdir($this->cibledir . '/Pictures');
                }
                
                if (!copy($fileInfo->path, $this->cibledir . '/' . $href)) {
                    $err = "setHtmlDraw::file copy fail";
                }
                //  print_r2($this->dom->saveXML());exit;
                
            }
        }
        return $err;
    }
    /**
     * set image
     * @param DOMElement $draw
     * @param string $name
     * @param string $file
     * @return string
     */
    protected function setDraw(DOMElement & $draw, $name, $file)
    {
        if (strpos($file, '<text:tab') !== false) {
            return 'muliple values : fail';
        }
        $imgs = $draw->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:drawing:1.0", "image");
        $err = "";
        if ($imgs->length > 0) {
            /**
             * @var $img DOMElement
             */
            $img = $imgs->item(0);
            if (file_exists($file)) {
                $draw->setAttribute('draw:name', substr($name, 2) . ' ' . uniqid() . mt_rand(1000, 9999));
                $href = 'Pictures/dcp' . uniqid() . mt_rand(1000000, 9999999) . substr($img->getAttribute('xlink:href') , -9);
                $img->setAttribute('xlink:href', $href);
                $this->added_images[] = $href;
                if (!copy($file, $this->cibledir . '/' . $href)) {
                    $err = "copy fail";
                }
                
                if ($err == "") { // need to respect image proportion
                    $width = $draw->getAttribute('svg:width');
                    $size = getimagesize($file);
                    $unit = "";
                    if (preg_match('/[0-9\.]+(.*)$/', $width, $reg)) $unit = $reg[1];
                    $height = sprintf("%.03f%s", (doubleval($width) / $size[0]) * $size[1], $unit);
                    $draw->setAttribute('svg:height', $height);
                }
            }
        }
        return $err;
    }
    /**
     *  parse images
     */
    protected function parseDraw()
    {
        $draws = $this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:drawing:1.0", "frame");
        
        foreach ($draws as $draw) {
            /**
             * @var $draw DOMElement
             */
            
            $name = trim($draw->getAttribute('draw:name'));
            if (preg_match('/\[(V_[A-Z0-9_-]+)\]/', $name, $reg)) {
                $key = $reg[1];
                if (isset($this->rkey[$key])) {
                    $this->setDraw($draw, $key, $this->rkey[$key]);
                }
            }
        }
    }
    /**
     * remove all xml:id attributes in children nodes
     * @param DomNode $objNode
     */
    protected function removeXmlId(&$objNode)
    {
        $objNodeListNested = $objNode->childNodes;
        foreach ($objNodeListNested as $objNodeNested) {
            /**
             * @var $objNodeNested DOMElement
             */
            if ($objNodeNested->nodeType == XML_ELEMENT_NODE) {
                $objNodeNested->removeAttribute("xml:id");
                $this->removeXmlId($objNodeNested);
            }
        }
    }
    /**
     * This function replaces a node's string content with strNewContent
     * @param DomNode $objNode
     * @param string $strOldContent
     * @param string $strNewContent
     * @throws \Dcp\Exception
     */
    protected function replaceNodeText(DOMNode & $objNode, $strOldContent, $strNewContent)
    {
        if ($strNewContent === null) return;
        if (is_array($strNewContent)) {
            throw new Dcp\Exception("node replacement must be a string : array found");
        }
        $objNodeListNested = & $objNode->childNodes;
        foreach ($objNodeListNested as $objNodeNested) {
            /**
             * @var $objNodeNested DOMElement
             */
            if ($objNodeNested->nodeType == XML_TEXT_NODE) {
                if ($objNodeNested->nodeValue != "") {
                    if (strpos($strNewContent, '<text:p>') !== false) {
                        $strNewContent = str_replace('<', '--Lower.Than--', $strNewContent);
                        $strNewContent = str_replace('>', '--Greater.Than--', $strNewContent);
                        $strNewContent = htmlspecialchars_decode($strNewContent);
                    }
                    $objNodeNested->nodeValue = str_replace($strOldContent, $strNewContent, $objNodeNested->nodeValue);
                }
            } elseif ($objNodeNested->nodeType == XML_ELEMENT_NODE) {
                if ($objNodeNested->nodeName == 'text:text-input') {
                    $name = $objNodeNested->getAttribute('text:description');
                    if ($name == $strOldContent) {
                        $this->setInputField($objNodeNested, substr($name, 1, -1) , $strNewContent);
                    }
                } elseif ($objNodeNested->nodeName == 'text:drop-down') {
                    $name = $objNodeNested->getAttribute('text:name');
                    if ($name == $strOldContent) {
                        $this->setDropDownField($objNodeNested, substr($name, 1, -1) , $strNewContent);
                    }
                } elseif ($objNodeNested->nodeName == 'draw:frame') {
                    $name = $objNodeNested->getAttribute('draw:name');
                    if (substr($name, 0, strlen($strOldContent)) == $strOldContent) {
                        $this->setDraw($objNodeNested, substr($strOldContent, 1, -1) , $strNewContent);
                    }
                } else {
                    $this->replaceNodeText($objNodeNested, $strOldContent, $strNewContent);
                }
            }
        }
    }
    /**
     * parse bullet lists
     */
    protected function parseListItem()
    {
        $err = '';
        $lists = $this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0", "list");
        foreach ($lists as $list) {
            /**
             * @var $list DOMElement
             */
            $items = $list->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0", "list-item");
            if ($items->length > 0) {
                $item = $items->item(0);
                $skey = implode('|', array_keys($this->arrayMainKeys));
                if (preg_match_all("/\[($skey)\]/", $this->innerXML($list) , $reg)) {
                    $reg0 = $reg[0];
                    $tvkey = array();
                    $maxk = 0;
                    foreach ($reg0 as $k => $v) {
                        $key = substr(trim($v) , 1, -1);
                        $tvkey[$key] = $this->arrayMainKeys[$key];
                        $maxk = max(count($tvkey[$key]) , $maxk);
                    }
                    if ($maxk > 0) {
                        for ($i = 0; $i < $maxk; $i++) {
                            $clone = $item->cloneNode(true);
                            $item->parentNode->appendChild($clone);
                            foreach ($tvkey as $kk => $key) {
                                $this->replaceNodeText($clone, "[$kk]", $key[$i]);
                            }
                        }
                    }
                    $item->parentNode->removeChild($item);
                }
            }
        }
        return $err;
    }
    
    private function _section_cmp($k1, $k2)
    {
        if ($k2 > $k1) return 1;
        else if ($k2 < $k1) return -1;
        return 0;
    }
    /**
     * parse section repeat
     */
    protected function parseSection()
    {
        
        $lists = $this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0", "section");
        $ks = 0;
        $section = array();
        // need to inpect section with max depth before to avoid et repeat top level section
        
        /**
         * @var $aSection DOMElement
         */
        foreach ($lists as $aSection) {
            $depth = $this->getNodeDepth($aSection);
            $section[($depth * 200) + $ks] = $aSection;
            $ks++;
        }
        //reorder by depth
        uksort($section, array(
            $this,
            "_section_cmp"
        ));
        foreach ($section as $aSection) {
            /**
             * @var $aSection DOMElement
             */
            $skey = implode('|', array_keys($this->arrayMainKeys));
            if (preg_match_all("/\\[($skey)\\]/", $this->innerXML($aSection) , $reg)) {
                $reg0 = $reg[0];
                $tvkey = array();
                $maxk = 0;
                foreach ($reg0 as $k => $v) {
                    $key = substr(trim($v) , 1, -1);
                    $tvkey[$key] = $this->arrayMainKeys[$key];
                    $maxk = max(count($tvkey[$key]) , $maxk);
                }
                if ($maxk > 0) {
                    for ($i = 0; $i < $maxk; $i++) {
                        /**
                         * @var DOMElement $clone
                         */
                        $clone = $aSection->cloneNode(true);
                        $aSection->parentNode->insertBefore($clone, $aSection);
                        foreach ($tvkey as $kk => $key) {
                            $this->replaceNodeText($clone, "[$kk]", $key[$i]);
                        }
                        $this->replaceRowIf($clone, array(
                            $i
                        )); // main level
                        $this->replaceRowNode($clone, array(
                            $i
                        )); // inspect sub levels
                        
                    }
                }
                $aSection->parentNode->removeChild($aSection);
            }
        }
    }
    /**
     * modify a text:input field value
     *
     * @param DomElement $node
     * @param string $name
     * @param string $value
     * @return string error
     */
    protected function setInputField(DomElement & $node, $name, $value)
    {
        if (strpos($value, '<text:tab') !== false) {
            return 'muliple values : fail';
        }
        
        $node->nodeValue = $value;
        $node->setAttribute("text:description", '[PP' . $name . 'PP]');
        return '';
    }
    /**
     * modify a text:drop-down list
     *
     * @param DOMElement $node
     * @param string $name
     * @param string $value
     * @return string error message
     */
    protected function setDropDownField(DOMElement & $node, $name, $value)
    {
        if (strpos($value, '<text:tab') !== false) {
            return 'muliple values : fail';
        }
        $this->removeAllChilds($node);
        $node->setAttribute("text:name", '[PP' . $name . 'PP]');
        $value = str_replace(array(
            "&lt;",
            "&gt;",
            "&amp;"
        ) , array(
            "<",
            ">",
            '&'
        ) , $value);
        $item = new DOMElement('text:label', '', 'urn:oasis:names:tc:opendocument:xmlns:text:1.0');
        $item = $node->appendChild($item);
        /**
         * @var $item DOMElement
         */
        $item->setAttribute("text:current-selected", 'true');
        $item->setAttribute("text:value", $value);
        $node->appendChild(new DOMText($value));
        return '';
    }
    /**
     * remove all child nodes
     *
     * @param DomNode $objNode
     */
    protected function removeAllChilds(DOMNode & $objNode)
    {
        $objNodeListNested = $objNode->childNodes;
        $objNode->nodeValue = '';
        if (!empty($objNodeListNested) && $objNodeListNested->length > 0) {
            foreach ($objNodeListNested as $objNodeNested) {
                $objNode->removeChild($objNodeNested);
            }
        }
    }
    /**
     * parse tables
     */
    protected function parseTableRow()
    {
        $err = '';
        $lists = $this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:table:1.0", "table-row");
        $validRow = array();
        
        $skey = implode('|', array_keys($this->arrayMainKeys));
        /**
         * @var $rowItem DOMElement
         */
        foreach ($lists as $rowItem) {
            if (preg_match("/\[($skey)\]/", $this->innerXML($rowItem) , $reg)) {
                $validRow[] = $rowItem;
            }
        }
        foreach ($validRow as $rowItem) {
            /**
             * @var $rowItem DOMElement
             */
            if (preg_match_all("/\[($skey)\]/", $this->innerXML($rowItem) , $reg)) {
                $reg0 = $reg[0];
                $tvkey = array();
                $maxk = 0; // search values which has the greatest number of values
                foreach ($reg0 as $k => $v) {
                    $key = substr(trim($v) , 1, -1);
                    $tvkey[$key] = $this->arrayMainKeys[$key];
                    $maxk = max(count($tvkey[$key]) , $maxk);
                }
                if ($maxk > 0) {
                    for ($i = 0; $i < $maxk; $i++) {
                        /**
                         * @var DOMElement $clone
                         */
                        $clone = $rowItem->cloneNode(true);
                        
                        $rowItem->parentNode->insertBefore($clone, $rowItem);
                        foreach ($tvkey as $kk => $key) {
                            $this->replaceNodeText($clone, "[$kk]", $key[$i]);
                        }
                        $this->replaceRowIf($clone, array(
                            $i
                        )); // main level
                        $this->replaceRowNode($clone, array(
                            $i
                        )); // inspect sub levels
                        
                    }
                }
                
                $rowItem->parentNode->removeChild($rowItem);
            }
        }
        return $err;
    }
    /**
     * return the number of array in arrays
     * @param array $v
     * @return int
     */
    private static function getArrayDepth($v)
    {
        $depth = - 1;
        while (is_array($v)) {
            $depth++;
            $v = current($v);
        }
        return $depth;
    }
    /**
     *
     * Retrieve one of values for a multi value key
     * @param string $key the key name (multiple values)
     * @param array $levelPath path to access of a particular value
     * @return string|null
     */
    protected function getArrayKeyValue($key, array $levelPath)
    {
        $value = null;
        
        if (count($levelPath) == 1) {
            if (isset($this->arrayMainKeys[$key])) {
                $index = current($levelPath);
                return $this->arrayMainKeys[$key][$index];
            }
        }
        if (!isset($this->arrayKeys[$key])) return null;
        
        $value = $this->arrayKeys[$key];
        foreach ($levelPath as $index) {
            $value = $value[$index];
        }
        
        return $value;
    }
    /**
     * fix span cause when IF/ENDIF are not on the same depth
     * @param $s
     */
    private function fixSpanIf(&$s)
    {
        $s = preg_replace('/<text:span ([^>]*)>\s*<\/text:p>/s', "</text:p>", $s);
        $s = preg_replace('/<text:p ([^>]*)>\s*<\/text:span>/s', "<text:p \\1>", $s);
    }
    /**
     *
     * Inspect conditions in cells
     * @param DOMNode $row
     * @param array $levelPath
     */
    protected function replaceRowIf(DOMNode & $row, array $levelPath)
    {
        
        $this->removeXmlId($row);
        
        $inner = $row->ownerDocument->saveXML($row);
        
        $head = '<?xml version="1.0" encoding="UTF-8"?><office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" office:version="1.0">';
        $foot = '</office:document-content>';
        
        $level = 0;
        while ($level < 10) {
            $replacement = preg_replace_callback('/(?m)\[IF(NOT)?\s*([^\]]*)\](.*?)\[ENDIF\s*\\2\]/s', function ($matches) use ($levelPath)
            {
                return $this->TestIf($matches[2], $matches[3], $matches[1], $levelPath);
            }
            , $inner);
            if ($inner == $replacement) break;
            else $inner = $replacement;
            $level++;
        }
        $this->fixSpanIf($replacement);
        
        $dxml = new DomDocument();
        
        $dxml->loadXML($head . $replacement . $foot);
        $ot = $dxml->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:office:1.0", "document-content");
        if ($ot->length > 0) {
            $newnode = $this->dom->importNode($ot->item(0)->firstChild, true);
            // copy inside
            // first delete inside
            while ($row->firstChild) {
                $row->removeChild($row->firstChild);
            }
            // move to
            while ($newnode->firstChild) {
                $row->appendChild($newnode->firstChild);
            }
        }
    }
    /**
     * @param DOMElement $row
     * @param array $levelPath
     */
    protected function replaceRowNode(DOMElement & $row, array $levelPath)
    {
        // Inspect sub tables, rows
        $this->replaceRowSomething($row, $levelPath, "table", "table-row", true);
        // Inspect list in sub tables
        $this->replaceRowSomething($row, $levelPath, "text", "list-item", false);
        // Inspect list in subsection
        $this->replaceRowSomething($row, $levelPath, "text", "section", true);
    }
    /**
     *
     * Inspect list in sub tables
     * @param DOMElement $row
     * @param array $levelPath
     * @param string $ns namespace for filter items (like text or table)
     * @param string $tag tag for filter (like table-row or list-item)
     * @param boolean $recursive recursive mode
     */
    protected function replaceRowSomething(DOMElement & $row, array $levelPath, $ns, $tag, $recursive)
    {
        if (count($this->arrayKeys) == 0) return; // nothing to do
        $keys = array();
        $subIndex = count($levelPath);
        foreach ($this->arrayKeys as $k => $v) {
            if ($this->getArrayDepth($v) == $subIndex) $keys[] = $k;
        }
        
        $rowList = $row->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:$ns:1.0", $tag);
        if ($rowList->length > 0) {
            $skey = implode('|', $keys);
            /* print "<h1>$tag</h1>";
            print_r2($levelPath);
            print_r2($keys);*/
            $tvkey = array();
            foreach ($rowList as $item) {
                /**
                 * @var $item DOMElement
                 */
                if (preg_match_all("/\\[($skey)\\]/", $this->innerXML($item) , $reg)) {
                    
                    $maxk = 0;
                    foreach ($reg[1] as $k => $v) {
                        $vkey = $this->getArrayKeyValue($v, $levelPath);
                        $tvkey[$v] = $vkey;
                        $maxk = max(count($tvkey[$v]) , $maxk);
                    }
                    
                    if ($maxk > 0) {
                        for ($i = 0; $i < $maxk; $i++) {
                            /**
                             * @var DOMElement $clone
                             */
                            $clone = $item->cloneNode(true);
                            $item->parentNode->insertBefore($clone, $item);
                            foreach ($tvkey as $kk => $key) {
                                $this->replaceNodeText($clone, "[$kk]", $key[$i]);
                            }
                            $newPath = array_merge($levelPath, array(
                                $i
                            ));
                            $this->replaceRowIf($clone, $newPath);
                            //if ($recursive) $this->replaceRowSomething($clone,$newPath,$ns,$tag,$recursive);
                            if ($recursive) $this->replaceRowNode($clone, $newPath);
                        }
                    }
                    $item->parentNode->removeChild($item);
                }
            }
        }
    }
    /**
     * parse text:input
     */
    protected function parseInput()
    {
        $lists = $this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0", "text-input");
        foreach ($lists as $list) {
            /**
             * @var $list DOMElement
             */
            $name = $list->getAttribute("text:description");
            if (preg_match('/\[(V_[A-Z0-9_-]+)\]/', $name, $reg)) {
                $key = $reg[1];
                if (isset($this->rkey[$key])) {
                    $this->setInputField($list, $key, $this->rkey[$key]);
                }
            }
        }
    }
    /**
     * parse text:drop-down
     */
    protected function parseDropDown()
    {
        $lists = $this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0", "drop-down");
        foreach ($lists as $list) {
            /**
             * @var $list DOMElement
             */
            $name = $list->getAttribute("text:name");
            if (preg_match('/\[(V_[A-Z0-9_-]+)\]/', $name, $reg)) {
                $key = $reg[1];
                if (isset($this->rkey[$key])) {
                    $this->setDropDownField($list, $key, $this->rkey[$key]);
                }
            }
        }
    }
    /**
     * restore protected values
     */
    protected function restoreProtectedValues()
    {
        $this->template = preg_replace('/\[PP(V_[A-Z0-9_]+)PP\]/s', '[$1]', $this->template);
        $this->template = str_replace('--Lower.Than--', '<', $this->template);
        $this->template = str_replace('--Greater.Than--', '>', $this->template);
    }
    /**
     * parse section and clone "tpl_xxx" sections into saved_sections
     */
    protected function parseTplSection()
    {
        $this->saved_sections = array();
        // remove old generated sections
        $lists = $this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0", "section");
        foreach ($lists as $list) {
            /**
             * @var $list DOMElement
             */
            $name = $list->getAttribute("text:name");
            if (substr($name, 0, 5) == '_tpl_') {
                $list->parentNode->removeChild($list);
            }
        }
        // clone sections and generate them again
        $lists = $this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0", "section");
        foreach ($lists as $list) {
            /**
             * @var $list DOMElement
             */
            $name = $list->getAttribute("text:name");
            if (substr($name, 0, 4) == 'tpl_') {
                $this->removeXmlId($list);
                // restore original style name of first head
                $heads = $list->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0", "h");
                if ($heads->length > 0) {
                    /**
                     * @var $firsthead DOMElement
                     */
                    $firsthead = $heads->item(0);
                    $firsthead->setAttribute("text:style-name", trim($firsthead->getAttribute('text:style-name') , '_'));
                }
                $this->saved_sections[$name] = $list->cloneNode(true);
                /**
                 * @var $originSection DOMElement
                 */
                $originSection = $this->saved_sections[$name];
                $list->setAttribute("text:name", '_' . $name);
                $list->setAttribute("text:protected", 'true');
                $list->setAttribute("text:display", 'true');
                // // special treatment to have correct chapter numeration search first header
                $heads = $originSection->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0", "h");
                if ($heads->length > 0) {
                    /**
                     * @var $firsthead DOMElement
                     */
                    $firsthead = $heads->item(0);
                    $styleName = $firsthead->getAttribute("text:style-name");
                    if ($styleName) {
                        $styleName = trim($styleName, '_');
                        $styles = $this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:style:1.0", "style");
                        //addLogMsg(array("style"=>$styleName,"length"=>$styles->length));
                        $tStyleName = array();
                        foreach ($styles as $style) {
                            /**
                             * @var $style DOMElement
                             */
                            $aStyleName = $style->getAttribute("style:name");
                            $tStyleName[] = $aStyleName;
                            if ($aStyleName == $styleName) {
                                $copyName = '_' . $styleName . '_';
                                if (!(in_array($copyName, $tStyleName))) {
                                    /**
                                     * @var $cloneStyle DOMElement
                                     */
                                    $cloneStyle = $style->cloneNode(true);
                                    $cloneStyle->setAttribute("style:name", $copyName);
                                    $cloneStyle->setAttribute("style:list-style-name", ""); // unset numeration chapter
                                    $style->parentNode->insertBefore($cloneStyle, $style);
                                }
                                $firsthead->setAttribute("text:style-name", $copyName);
                                break;
                            }
                        }
                    }
                }
            }
        }
    }
    /**
     * restore cloned and saved sections at the end
     */
    protected function restoreSection()
    {
        
        $inserts_to_do = array();
        
        $lists = $this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0", "section");
        foreach ($lists as $list) {
            /**
             * @var $list DOMElement
             */
            $name = $list->getAttribute("text:name");
            if (substr($name, 0, 5) == '_tpl_' && isset($this->saved_sections[substr($name, 1) ])) {
                $node = $this->dom->importNode($this->saved_sections[substr($name, 1) ], true);
                $inserts_to_do[] = array(
                    $node,
                    $list
                );
            }
        }
        foreach ($inserts_to_do as $insert_to_do) {
            //$node = $insert_to_do[1]->parentNode->insertBefore($insert_to_do[0], $insert_to_do[1]);
            // insert after
            if ($insert_to_do[1]->nextSibling) {
                /** @noinspection PhpUndefinedMethodInspection */
                $node = $insert_to_do[1]->parentNode->insertBefore($insert_to_do[0], $insert_to_do[1]->nextSibling);
            } else {
                /** @noinspection PhpUndefinedMethodInspection */
                $node = $insert_to_do[1]->parentNode->appenChild($insert_to_do[0]);
            }
            /**
             * @var $node DOMElement
             */
            $node->setAttribute("text:protected", 'true');
            $node->setAttribute("text:display", 'none');
        }
    }
    /**
     * Initialize of list to be used in table or list
     * @param string $key the key variable
     * @param array $t the values of the key
     */
    public function setColumn($key, array $t)
    {
        $ti = array();
        $k = 0;
        foreach ($t as $v) $ti[$k++] = $v; // delete associative keys
        if (is_array(current($t))) $this->setArray($key, $ti);
        else $this->arrayMainKeys[$key] = $ti;
        //else $this->set($key,implode('<text:tab/>',$t));
        
    }
    
    protected function setArray($key, array $t)
    {
        if (!$key) throw new Dcp\Exception('Key must not be empty');
        $this->arrayKeys[$key] = $t;
    }
    /**
     * @deprecated BLOCK not supported, use setColumn instead
     * @param string $p_nom_block
     * @param array $data
     */
    public function setBlockData($p_nom_block, $data = NULL)
    {
        deprecatedFunction();
        if ($p_nom_block != "") {
            if ($data != null && $this->encoding == "utf-8") {
                if (is_array($data)) {
                    foreach ($data as $k => $v) {
                        foreach ($v as $kk => $vk) {
                            if (!isUTF8($vk)) {
                                $data[$k][$kk] = utf8_encode($vk);
                            }
                        }
                    }
                } else {
                    if (!isUTF8($data)) $data = utf8_encode($data);
                }
            }
            $this->data[$p_nom_block] = $data;
            if (is_array($data)) {
                reset($data);
                $elem = current($data);
                if (isset($elem) && is_array($elem)) {
                    reset($elem);
                    foreach ($elem as $k => $v) {
                        if (!isset($this->corresp["$p_nom_block"]["[$k]"])) {
                            $this->setBlockCorresp($p_nom_block, $k);
                        }
                    }
                }
            }
        } else {
            $this->setrepeatable($data);
        }
    }
    /*
     * set array to be use in repeat set like table, list-item or section
     * @param array $data the arry to set
    */
    public function setrepeatable(array $data)
    {
        
        $t = array();
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                foreach ($v as $ki => $vi) {
                    $t[$ki][$k] = $vi;
                }
            }
        }
        // fill array
        $max = 0;
        foreach ($t as $k => $v) {
            if (count($v) > $max) $max = count($v);
        }
        foreach ($t as $k => $v) {
            if (count($v) < $max) {
                $fill = array_fill(0, $max, '');
                foreach ($v as $idx => $vi) {
                    $fill[$idx] = $vi;
                }
                $t[$k] = $fill;
            }
        }
        // affect completed columns
        foreach ($t as $k => $v) {
            $this->setColumn($k, $v);
        }
    }
    
    protected function addHTMLStyle()
    {
        $xmldata = '<xhtml:html xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "</xhtml:html>";
        
        $ddXsl = new DOMDocument();
        $ddXsl->load(DEFAULT_PUBDIR . "/CORE/Layout/html2odt.xsl");
        $xslt = new xsltProcessor;
        
        $xslt->importStyleSheet($ddXsl);
        
        $ddData = new DOMDocument();
        $ddData->loadXML($xmldata);
        $xmlout = $xslt->transformToXML($ddData);
        
        $dxml = new DomDocument();
        $dxml->loadXML($xmlout);
        $ot = $dxml->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:office:1.0", "automatic-styles");
        if ($ot->length <= 0) {
            $this->addError("LAY0008", DEFAULT_PUBDIR . "/CORE/Layout/html2odt.xsl");
            $this->exitError();
        }
        $ot1 = $ot->item(0);
        $ass = $this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:office:1.0", "automatic-styles");
        if ($ass->length <= 0) {
            $outfile = uniqid(getTmpDir() . "/oooKo") . '.xml';
            file_put_contents($outfile, $this->template);
            $this->addError("LAY0009", $this->file);
            $this->exitError($outfile);
        }
        $ass0 = $ass->item(0);
        foreach ($ot1->childNodes as $ots) {
            $c = $this->dom->importNode($ots, true);
            $ass0->appendChild($c);
        }
    }
    /**
     * Delete not used images (need when reuse template where repeat section)
     */
    protected function removeOrphanImages()
    {
        $imgs = $this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:drawing:1.0", "image");
        $used_images = array();
        foreach ($imgs as $img) {
            /**
             * @var $img DOMElement
             */
            $href = basename($img->getAttribute('xlink:href'));
            if (substr($href, 0, 7) == 'dcp') {
                $used_images[] = $href;
            }
        }
        $files = glob($this->cibledir . '/Pictures/dcp*');
        if (is_array($files)) {
            foreach ($files as $file) {
                if (!in_array(basename($file) , $used_images)) {
                    $this->removed_images[] = 'Pictures/' . basename($file);
                    @unlink($file);
                }
            }
        }
    }
    
    protected function GenJsRef()
    {
        return "";
    }
    
    public function GenJsCode($showlog, $onlylog = false)
    {
        return ("");
    }
    
    protected function ParseJs(&$out)
    {
    }
    
    protected function GenCssRef($oldCompatibility = true)
    {
        return "";
    }
    
    protected function GenCssCode()
    {
        return ("");
    }
    
    protected function ParseCss(&$out)
    {
    }
    /**
     * generate OOo document style part
     */
    protected function genStyle()
    {
        
        $this->dom = new DOMDocument();
        
        $this->dom->loadXML($this->style_template);
        if ($this->dom) {
            $this->template = $this->style_template;
            
            $this->parseDraw();
            $this->template = $this->dom->saveXML();
            
            $this->hideUserFieldSet();
            $this->ParseIf();
            $this->ParseKey();
            $this->ParseText();
            $this->restoreUserFieldSet();
            $this->style_template = $this->template;
        }
    }
    /**
     * generate OOo document meta part
     */
    protected function genMeta()
    {
        $this->dom = new DOMDocument();
        
        $this->dom->loadXML($this->meta_template);
        if ($this->dom) {
            $this->template = $this->meta_template;
            
            $this->ParseIf();
            $this->ParseKey();
            $this->ParseText();
            
            $this->meta_template = $this->template;
        }
    }
    /**
     * clean section done by htmltext values
     * delete unecessary span or p
     * delete section tag if needed (not in cell or text:section or text:body
     */
    protected function parseHtmlText()
    {
        $this->dom->loadXML($this->template);
        $lists = $this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0", "section");
        $htmlSections = array();
        $htmlCleanSections = array();
        foreach ($lists as $list) {
            /**
             * @var $list DOMElement
             */
            $aid = $list->getAttribute("aid");
            if ($aid) {
                if ($list->parentNode->childNodes->length == 1) {
                    $htmlSections[] = $list;
                } else {
                    // remove section
                    $htmlCleanSections[] = $list;
                }
                $list->removeAttribute("aid");
            }
        }
        foreach ($htmlSections as $htmlSection) {
            /**
             * @var $htmlSection DOMElement
             */
            $pParentHtml = $htmlSection->parentNode->parentNode;
            $parentHtml = $htmlSection->parentNode;
            
            if ($parentHtml->nextSibling) {
                $pParentHtml->insertBefore($htmlSection, $parentHtml->nextSibling);
            } else {
                $pParentHtml->appendChild($htmlSection);
            }
            $pParentHtml->removeChild($parentHtml);
            // double up
            $pParentHtml = $htmlSection->parentNode->parentNode;
            $parentHtml = $htmlSection->parentNode;
            
            if (($parentHtml->nodeName == "text:p") && ($parentHtml->childNodes->length == 1)) {
                $htmlPs = $htmlSection->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0", "p");
                /**
                 * @var DOMElement $p
                 */
                foreach ($htmlPs as $p) {
                    foreach ($parentHtml->attributes as $attribute) {
                        
                        $p->setAttribute('text:' . $attribute->name, $attribute->value);
                    }
                }
                
                if ($parentHtml->nextSibling) {
                    $pParentHtml->insertBefore($htmlSection, $parentHtml->nextSibling);
                } else {
                    $pParentHtml->appendChild($htmlSection);
                }
                
                $pParentHtml->removeChild($parentHtml);
                
                if (in_array($htmlSection->parentNode->nodeName, array(
                    "text:list-item",
                    //   "draw:text-box",
                    //  "text:p"
                    
                ))) {
                    $htmlCleanSections[] = $htmlSection;
                    $attrid = substr($htmlSection->getAttribute("text:name") , 7);
                    $this->addError("LAY0002", "[V_" . strtoupper($attrid) . "]");
                }
                //print "Parent Node is ".$htmlSection->parentNode->nodeName."\n";
                
            } else {
                if (!in_array($parentHtml->nodeName, array(
                    'office:text',
                    'text:text-content',
                    'text:office-text-content-main',
                    'table:table-cell',
                    'draw:text-box'
                ))) {
                    $htmlCleanSections[] = $htmlSection;
                    $attrid = substr($htmlSection->getAttribute("text:name") , 7);
                    $this->addError("LAY0005", "[V_" . strtoupper($attrid) . "]");
                }
            }
        }
        foreach ($htmlCleanSections as $htmlSection) {
            /**
             * @var $htmlSection DOMElement
             */
            
            $attrid = substr($htmlSection->getAttribute("text:name") , 7);
            
            $pp = $this->dom->createElement("text:span");
            $pp->nodeValue = "HTML attribute misplaced  : " . "[V_" . strtoupper($attrid) . "]";
            $htmlSection->parentNode->appendChild($pp);
        }
        
        $this->parseHtmlDraw();
        $this->template = $this->dom->saveXML();
    }
    /**
     * get all error stored by addError
     * @return string
     */
    public function getErrors()
    {
        $s = array();
        foreach ($this->errors as $err) {
            $s[] = ErrorCode::getError($err["code"], $err["key"]);
        }
        return implode("\n", $s);
    }
    /**
     * send exception and exit generation
     * use sored error
     * @see addError()
     * @param string $outfile corrupted file path
     * @throws Dcp\Layout\Exception
     */
    protected function exitError($outfile = '')
    {
        foreach ($this->errors as $err) {
            if ($err["code"]) {
                $e = new Dcp\Layout\Exception($err["code"], $err["key"]);
                if ($outfile) {
                    error_log(sprintf("Error {%s}: corrupted temporary file is %s", $err["code"], $outfile));
                    $e->setCorruptedFile($outfile);
                }
                throw $e;
            }
        }
    }
    /**
     * store an error code
     * @param string $code
     * @param string $key
     * @param string $message
     */
    protected function addError($code, $key, $message = '')
    {
        $this->errors[] = array(
            "key" => $key,
            "code" => $code,
            "message" => $message
        );
    }
    /**
     * Change Element Name
     * @param DOMElement $node
     * @param string $name
     * @return DOMElement
     */
    protected function changeElementName($node, $name)
    {
        
        $newElement = $this->dom->createElement($name);
        // Clone the attributes:
        foreach ($node->attributes as $attribute) {
            
            $newElement->setAttribute($attribute->name, $attribute->value);
        }
        // Add clones of the old element's children to the replacement
        
        /**
         * @var DOMElement $child
         */
        foreach ($node->childNodes as $child) {
            
            $newElement->appendChild($child->cloneNode(true));
        }
        // Replace the old node
        $node->parentNode->replaceChild($newElement, $node);
        return $newElement;
    }
    /**
     * generate OOo document content
     */
    protected function genContent()
    {
        
        $this->dom = new DOMDocument();
        
        $this->dom->loadXML($this->content_template);
        if ($this->dom) {
            $this->template = $this->content_template;
            
            $this->parseTplSection();
            //header('Content-type: text/xml; charset=utf-8');print $this->dom->saveXML();exit;
            $this->hideUserFieldSet();
            $this->parseTableRow();
            
            $this->parseSection();
            $this->parseListItem();
            $this->parseDraw();
            
            $this->parseInput();
            $this->parseDropDown();
            
            $this->addHTMLStyle();
            $this->template = $this->dom->saveXML();
            // Parse i18n text
            $this->ParseBlock();
            $this->ParseIf();
            //$this->ParseKeyXml();
            //$this->template=$this->dom->saveXML();
            //      print $this->template;exit;
            $this->ParseKey();
            $this->ParseText();
            
            $this->restoreUserFieldSet();
            
            $this->restoreProtectedValues();
            
            $this->ParseHtmlText();
            
            $this->template = \Dcp\Utils\htmlclean::cleanXMLUTF8($this->template);
            $this->dom = new DOMDocument();
            if ($this->dom->loadXML($this->template)) {
                $this->restoreSection();
                // not remove images because delete images defined in style.xml
                //$this->removeOrphanImages();
                $this->template = $this->dom->saveXML();
                
                $this->content_template = $this->template;
            } else {
                $outfile = uniqid(getTmpDir() . "/oooKo") . '.xml';
                file_put_contents($outfile, $this->template);
                $this->addError("LAY0004", $this->file);
                $this->exitError($outfile);
            }
        } else {
            $this->addError("LAY0001", $this->file);
            $this->exitError();
        }
    }
    /**
     * generate OOo document
     * get temporary file path of result
     * @throws Dcp\Layout\Exception
     * @return string odt file path
     */
    public function gen()
    {
        if (!$this->file) {
            $this->addError("LAY0001", $this->template);
            $this->exitError();
        }
        // if used in an app , set the app params
        if (is_object($this->action)) {
            $list = $this->action->parent->GetAllParam();
            foreach ($list as $k => $v) {
                $v = str_replace(array(
                    '<BR>',
                    '<br>',
                    '<br/>',
                    '<br />'
                ) , '<text:line-break/>', $v);
                $this->set($k, $v);
            }
        }
        // $this->rif=&$this->rkey;
        // $this->ParseIf($out);
        // Parse IMG: and LAY: tags
        $this->genContent();
        $this->genStyle();
        $this->genMeta();
        $this->updateManifest();
        $outfile = uniqid(getTmpDir() . "/odf") . '.odt';
        $this->content2odf($outfile);
        
        if (!empty($this->errors)) {
            //error_log(sprintf("Error {LAY0001}: corrupted temporary file is %s", $outfile));
            //throw new Dcp\Layout\Exception("LAY0001", $this->getErrors() , $outfile);
            $this->exitError($outfile);
        }
        //print_r2($this->content_template);
        return ($outfile);
    }
}

