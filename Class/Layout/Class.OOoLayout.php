<?php
/**
 * Layout Class for OOo files
 *
 * @author Anakeen 2000
 * @version $Id: Class.OOoLayout.php,v 1.16 2008/10/31 17:01:18 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package WHAT
 * @subpackage CORE
 */
/**
 */




include_once('Class.Layout.php');
include_once('Lib.FileMime.php');


class OOoLayout extends Layout {

	//############################################

	//#

	private $strip='Y';
	public $encoding="utf-8";
	
	private $saved_sections=array();
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

	protected $arrayKeys=array();
	protected $arrayMainKeys=array();

	/**
	 * construct layout for view card containt
	 *
	 * @param string $caneva file of the template
	 * @param Action $action current action
	 * @param Doc $doc document
	 */
	function __construct($caneva="",Action &$action=null,Doc &$doc=null) {
		$this->LOG = new Log("","Layout");
		$this->doc = $doc;
		$this->template = "";
		$this->action=&$action;
		$this->generation="";
		$file = $caneva;
		$this->file="";
		if ($caneva != "") {
			if ((! file_exists($file))&&($file[0]!='/')) {
				$file=GetParam("CORE_PUBDIR")."/$file"; // try absolute
			}
			if (file_exists($file)) {
				if (filesize($file) > 0) {
					$this->odf2content($file);
					$this->file=$file;
				}
			} else {
				$this->template="file  [$caneva] not exists";
			}
		}
	}

	function innerXML(DOMnode &$node){
		if(!$node) return false;
		$document = $node->ownerDocument;
		$nodeAsString = $document->saveXML($node);
		preg_match('!\<.*?\>(.*)\</.*?\>!s',$nodeAsString,$match);
		return $match[1];
	}

	function parseListInBlock($block,$aid,$vkey) {
		$head='<?xml version="1.0" encoding="UTF-8"?>
<office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" office:version="1.0">';
		$foot='</office:document-content>';
		$domblock=new DOMDocument();

		$block=trim($block);
		if (substr($block,0,2)=='</') {
			// fragment of block
			$firsttag=strpos($block,'>');
			$lasttag=strrpos($block,'<');
			$frag1=substr($block,0,$firsttag+1);
			$frag2=substr($block,$lasttag);
			$block=substr($block,$firsttag+1,$lasttag-strlen($block));
			// print("\nfrag1:$frag1  $lasttag rag2:$frag2\n");
			//      print("\n====================\n");
			//print("\nNB:[$block]\n");

		}

		if (! $domblock->loadXML($head.$block.$foot)) {
			print "\n=============\n";
			print $head.trim($block).$foot;
			return $block;
		}

		$lists=$domblock->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0","list");
		foreach ($lists as $list) {
			$items=$list->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0","list-item");
			if ($items->length > 0) {
				$item=$items->item(0);

				if (preg_match("/\[V_[A-Z0-9_-]+\]/",$item->textContent ,$reg)) {
				    $skey=$reg[0];
				    //	    print "serack key : [$skey] [$aid] [$vkey]";
				    if ($skey == $aid) {
				    	//  $vkey=$this->rkey[$key];
				    	$tvkey=explode('<text:tab/>',$vkey);
			
				    	foreach ($tvkey as $key) {
							$clone=$item->cloneNode(true);
							$item->parentNode->appendChild($clone);
							$this->replaceNodeText($clone,$reg[0],$key);
				    	}
				    	$item->parentNode->removeChild($item);
				    }
				}
			}
		}
		return $frag1.$this->innerXML($domblock->firstChild).$frag2;
		return $frag1.$domblock->saveXML($domblock->firstChild->firstChild).$frag2;
	}
	
	function getAncestor(&$node, $type) {
		$mynode = $node;
		while(!empty($mynode->parentNode)) {
			$mynode = $mynode->parentNode;
			if($mynode->tagName == $type) {
				return $mynode;
			}
		}
		return false;
	}
	/**
	 * get depth in dom tree
	 * @param DOMNode $node
	 */
	private function getNodeDepth(DOMNode &$node) {
		$mynode = $node;
		$depth=0;
		while(!empty($mynode->parentNode)) {
			$depth++;
			$mynode = $mynode->parentNode;
			
		}
		return $depth;
	}
	function ParseBlock() {
		$this->template = preg_replace(
       "/(?m)\[BLOCK\s*([^\]]*)\](.*?)\[ENDBLOCK\s*\\1\]/se", 
       "\$this->SetBlock('\\1','\\2')",
		$this->template);
	}

	/**
	 * 
	 * @param string $name name of the IF
	 * @param string $block xml string which containt the condition
	 * @param boolean $not negative condition
	 * @param array $levelPath Path use to retrieve condition value in recursive repeatable mode
	 */
	function TestIf($name,$block,$not=false,$levelPath=null) {
		$out = "";
		$cond=null;
		if ($levelPath) {
			$val=$this->getArrayKeyValue($name,$levelPath);
			if (is_array($val)) $val=null; // it is not the good level
				
			if ($val !== null) $cond=($val == true);
		} else {
			if ($this->rif[$name] !== null) $cond=($this->rif[$name] == true);
			elseif ($this->rkey[$name] !== null) $cond=($this->rkey[$name] == true);
		}
		if ($cond !== null ) {
			if ($cond xor $not)  {
				$out=$block;
			}
		} else {
			// return  condition
			if ($not) $out="[IFNOT $name]".$block."[ENDIF $name]";
			else $out="[IF $name]".$block."[ENDIF $name]";
		}
		if ($this->strip=='Y') $out = str_replace("\\\"","\"",$out);
		return ($out);
	}
	
	/**
	 * Top level parse condition
	 */
	function ParseIf() {
		$templateori='';
		$level=0;

	

		//header('Content-type: text/xml; charset=utf-8');print $this->template;exit;
		while ($templateori != $this->template && ($level < 10))  {
			$templateori=$this->template;
			$this->template = preg_replace(
       "/(?m)\[IF(NOT)?\s*([^\]]*)\](.*?)\[ENDIF\s*\\2\]/se", 
       "\$this->TestIf('\\2','\\3','\\1')",
			$this->template);
			$level++; // to prevent infinite loop
		}
		$this->fixSpanIf($this->template);
		// header('Content-type: text/xml; charset=utf-8');print $this->template;exit;
		// restore user fields
		if (! $this->dom->loadXML($this->template)) {
			print $this->template;
			throw new Exception("Error in parse condition");
		}
		//header('Content-type: text/xml; charset=utf-8');print $this->dom->saveXML();exit;
		
		$lists=$this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0","user-field-get");

		$domElemsToRemove = array();
		$domElemsToClean = array();

		foreach ($lists as $list) {
			if (! $list->getAttribute('office:string-value')) {
				if ($list->textContent==''){
					$domElemsToRemove[] = $list;
				} else {
					//$list->setAttribute("text:name",'');
					$domElemsToClean[] = $list;
						
				}
			}
		}
		foreach( $domElemsToClean as $domElement ){
			//$domElement->parentNode->nodeValue=$domElement->nodeValue;
		}
		foreach( $domElemsToRemove as $domElement ){
			$domElement->parentNode->removeChild($domElement);
		}

		$this->template=$this->dom->saveXML();
	}
	
	/**
	 * to not parse user fields set
	 */
	protected function hideUserFieldSet() {
		//$this->dom->loadXML($this->template);
		$lists=$this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0","user-field-decl");
		foreach ($lists as $list) {
			$list->setAttribute('office:string-value', str_replace('[','-CROCHET-',$list->getAttribute('office:string-value')));
			$list->setAttribute('text:name', str_replace('[','-CROCHET-',$list->getAttribute('text:name')));
		}
			// detect user field to force it into a span
		$lists=$this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0","user-field-get");
		foreach ($lists as $list) {						
			if ($list->parentNode->tagName != 'text:span') {
				$nt = $this->dom->createElement("text:span");
				$list->parentNode->insertBefore($nt,$list);
				$nt->appendChild($list);
			}	
		}
		// header('Content-type: text/xml; charset=utf-8');print $this->dom->saveXML();exit;

		$lists=$this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0","user-field-get");
		$userFields=array();
		// set the key of fields to up
		foreach ($lists as $list) {
			$textContent=$list->nodeValue;				
			if (substr($textContent,0,1)=='[') {
				$userFields[]=$list;
				$nt=$this->dom->createTextNode($textContent);
				$list->parentNode->insertBefore($nt,$list);
			}
		}
		foreach ($userFields as $list) {
			$list->parentNode->removeChild($list);
		}

		$this->template=$this->dom->saveXML();
	}
	/**
	 * replace brackets
	 */
	protected function restoreUserFieldSet() {
		// header('Content-type: text/xml; charset=utf-8');print $this->template;exit;
		$this->dom->loadXML($this->template);
		$lists=$this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0","user-field-decl");
		foreach ($lists as $list) {
			$list->setAttribute('office:string-value', str_replace('-CROCHET-','[',$list->getAttribute('office:string-value')));
			$list->setAttribute('text:name', str_replace('-CROCHET-','[',$list->getAttribute('text:name')));
		}
		$this->template=$this->dom->saveXML();
	}
	/**
	 * not use for the moment
	 * @deprecated
	 * @param $name
	 * @param $block
	 */
	function SetBlock($name,$block) {
		if ($this->strip=='Y') {
			//      $block = StripSlashes($block);
			$block = str_replace("\\\"","\"",$block);
		}
		$out = "";
		if (isset ($this->data) && isset ($this->data["$name"]) && is_array($this->data["$name"])) {
			foreach($this->data["$name"] as $k=>$v) {
				$loc=$block;

				foreach ($this->corresp["$name"] as $k2=>$v2) {

					if (strstr( $v[$v2], '<text:tab/>')) {
						$loc=$this->parseListInBlock($loc,$k2,$v[$v2]);
					} elseif ((! is_object($v[$v2])) && (! is_array($v[$v2]))) $loc = str_replace( $k2, $v[$v2], $loc);
					 
				}
				$this->rif=&$v;
				//	$this->ParseIf($loc);
				$out .= $loc;
			}
		}
		//    $this->ParseBlock($out);
		return ($out);
	}
	/**
	 * not use for the moment
	 * @deprecated
	 * @param $out
	 */
	function ParseZone(&$out) {
		$out = preg_replace(
       "/\[ZONE\s*([^:]*):([^\]]*)\]/e",
       "\$this->execute('\\1','\\2')",
		$out);
	}

	/**
	 * replace simple key in xml string
	 */
	function ParseKey() {
		if (isset ($this->rkey)) {
			$this->template=preg_replace($this->pkey,$this->rkey,$this->template);
		}
	}
	private function ParseKeyXML() {
		if (isset ($this->rkeyxml)) {
			 
			$lists=$this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0","p");
			foreach ($this->rkeyxml as $k=>$xmlkey) {
				print "\n\nserach [$k]\n";
				foreach ($lists as $list) {
					$pstyle=$list->getAttribute("text:style-name");
					$content=$this->dom->saveXML($list);
					 
					if (strstr($content, "[$k]")) {
						print "\n----------------\nfind C:$xmlkey $k:\n$content";
						 
	    }

				}
			}
		}
	}

	/**
	 * read odt file and insert xmls in object
	 * @param string $odsfile path to the odt file
	 */
	function odf2content($odtfile) {
		if (! file_exists($odtfile)) return "file $odtfile not found";
		$this->cibledir=uniqid(getTmpDir()."/odf");

		$cmd = sprintf("unzip  %s  -d %s >/dev/null", $odtfile, $this->cibledir );
		system($cmd);

		$contentxml=$this->cibledir."/content.xml";
		if (file_exists($contentxml)) {
			$this->content_template=file_get_contents($contentxml);
			unlink($contentxml);
		}
		$contentxml=$this->cibledir."/META-INF/manifest.xml";
		if (file_exists($contentxml)) {
			$this->manifest=file_get_contents($contentxml);
			unlink($contentxml);
		}
		$contentxml=$this->cibledir."/styles.xml";
		if (file_exists($contentxml)) {
			$this->style_template=file_get_contents($contentxml);
			unlink($contentxml);
		}
		$contentxml=$this->cibledir."/meta.xml";
		if (file_exists($contentxml)) {
			$this->meta_template=file_get_contents($contentxml);
			unlink($contentxml);
		}

	}
	

	/**
	 * recompose odt file
	 * @param string $odsfile output file path
	 */
	function content2odf($odsfile) {
		if (file_exists($odsfile)) return "file $odsfile must not be present";

		$contentxml=$this->cibledir."/content.xml";

		$this->content_template=preg_replace("!</?text:bookmark-(start|end)([^>]*)>!s","",$this->content_template);

		$this->content_template=preg_replace("!<text:section>(\s*<text:p/>)+!s","<text:section>",$this->content_template);
		$this->content_template=preg_replace("!(<text:p/>\s*)+</text:section>!s","</text:section>",$this->content_template);

		$this->content_template=preg_replace("/<text:span([^>]*)>\s*<text:section>/s","<text:section>",$this->content_template);
		$this->content_template=preg_replace("/<\/text:section>\s*<\/text:span>/s","</text:section>",$this->content_template);

		$this->content_template=preg_replace("/<text:p([^>]*)>\s*<text:section([^>]*)>/s","<text:section\\2>",$this->content_template);
		$this->content_template=preg_replace("/<\/text:section>\s*<\/text:p>/s","</text:section>",$this->content_template);

		//$this->content_template=preg_replace("/<text:p ([^>]*)>\s*<text:([^\/]*)\/>\s*<text:section[^>]*>/s","<text:section><text:\\2/>",$this->content_template);
		//$this->content_template=preg_replace("/<\/text:section>\s*<text:([^\/]*)\/>\s*<\/text:p>/s","</text:section><text:\\1/>",$this->content_template);

		$this->content_template=preg_replace("/<table:table-cell ([^>]*)>\s*<text:section>/s","<table:table-cell \\1>",$this->content_template);
		$this->content_template=preg_replace("/<\/text:section>\s*<\/table:table-cell>/s","</table:table-cell>",$this->content_template);

		$this->content_template=str_replace("&lt;text:line-break/&gt;","<text:line-break/>",$this->content_template);

		//  header('Content-type: text/xml; charset=utf-8');print($this->content_template);exit;
		file_put_contents($contentxml,$this->content_template);
		
		$contentxml=$this->cibledir."/META-INF/manifest.xml";
		file_put_contents($contentxml,$this->manifest);
		
		$contentxml=$this->cibledir."/styles.xml";
		file_put_contents($contentxml,$this->style_template);

		$contentxml=$this->cibledir."/meta.xml";
		file_put_contents($contentxml,$this->meta_template);

		$cmd = sprintf("cd %s;zip -r %s * >/dev/null && /bin/rm -fr %s", $this->cibledir, $odsfile, $this->cibledir );
		system($cmd);
		//rmdir($this->cibledir);
	}


	function execute($appname,$actionargn) {


		if ($this->action=="") return ("Layout not used in a core environment");

		// analyse action & its args
		$actionargn=str_replace(":","--",$actionargn); //For buggy function parse_url in PHP 4.3.1
		$acturl = parse_url($actionargn);
		$actionname =  $acturl ["path"];

		global $ZONE_ARGS;
		$OLD_ZONE_ARGS=$ZONE_ARGS;
		if (isset($acturl ["query"])) {
			$acturl["query"]=str_replace("--",":",$acturl["query"]); //For buggy function parse_url in PHP 4.3.1
			$zargs = explode("&", $acturl ["query"] );
			while (list($k, $v) = each($zargs)) {
				if (preg_match("/([^=]*)=(.*)/",$v, $regs)) {
					// memo zone args for next action execute
					$ZONE_ARGS[$regs[1]]=urldecode($regs[2]);
				}
			}
		}

		if ($appname != $this->action->parent->name) {
			$appl = new Application();
			$appl->Set($appname,$this->action->parent);
		} else {
			$appl =& $this->action->parent;
		}


		if (($actionname != $this->action->name)||($OLD_ZONE_ARGS!=$ZONE_ARGS)) {
			$act = new Action();

			if ($act->Exists($actionname, $appl->id)) {

				$res = $act->Set($actionname,$appl);
			} else {
				// it's a no-action zone (no ACL, cannot be call directly by URL)
				$act->name = $actionname;

				$res = $act->CompleteSet($appl);

			}
			if ($res == "") {
				$res=$act->execute();
			}
			$ZONE_ARGS=$OLD_ZONE_ARGS; // restore old zone args
			return($res);
		} else {
			return("Fatal loop : $actionname is called in $actionname");
		}

	}
	/**
	 * set key/value pair
	 * @param string $tag the key to replace
	 * @param string $val the value for the key
	 */
	public function set($tag,$val) {
		if ( !isUTF8($val)) $val = utf8_encode($val);
		if (! $this->isXml($val)) {
			$this->pkey[$tag]="/\[$tag\]/";
			if (is_array($val)) $val=implode('<text:tab/>',$val);
			$this->rkey[$tag]=$val;
		} else {

			$this->rkeyxml[$tag]=$val;

		}
	}
	/**
         * set key/value pair and XML entity encode
         * @param string $tag the key to replace
         * @param string $val the value for the key
         */
	public function eSet($tag,$val) {
		$this->set($tag,$this->xmlEntities($val));
	}
	/**
	 * replace entities & < >
	 * @param string $s text to encode
	 */
	static public function xmlEntities($s) {
		return str_replace(array("&",'<','>'),array("&amp;",'&lt;','&gt;'),$s);
	}
	
	/**
	 * 
	 * @param string $val
	 */
	function isXML($val) {
		return false;
		return preg_match("/<text:/",$val);
	}

	/**
	 * get value of $tag key
	 * @param string $tag
	 */
	public function get($tag) {
		if (isset($this->rkey)) return $this->rkey[$tag];
		return "";
	}
	/**
	 * parse text
	 */
	protected function ParseText() {
		if ($this->encoding=="utf-8") bind_textdomain_codeset("what", 'UTF-8');
		$this->template = preg_replace("/\[TEXT:([^\]]*)\]/e",
                         "\$this->Text('\\1')",
		$this->template);
		if ($this->encoding=="utf-8") bind_textdomain_codeset("what", 'ISO-8859-15'); // restore
	}
	/**
	 * 
	 */
	protected function updateManifest() {
		$manifest = new DomDocument();
		$manifest->loadXML($this->manifest);
		
		$manifest_root = null;
		$items = $manifest->childNodes;
		foreach($items as $item) {
			if($item->nodeName == 'manifest:manifest') {
				$manifest_root=$item;
				break;
			}
		}
		if($manifest_root === null) {
			return false;
		}
		
		$items = $manifest->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:manifest:1.0","file-entry");
		foreach($items as $item) {
			$type = $item->getAttribute("manifest:media-type");
			if(substr($type, 0, 6) == "image/") {
				$file = $item->getAttribute("manifest:full-path");
				if(in_array($file, $this->removed_images)) {
					$item->parentNode->removeChild($item);
				}
			}
		}
		foreach($this->added_images as $image) {
			$mime = getSysMimeFile($this->cibledir.'/'.$image);
			$new = $manifest->createElement("manifest:file-entry");
			$new->setAttribute("manifest:media-type", $mime);
			$new->setAttribute("manifest:full-path", $image);
			$manifest_root->appendChild($new);
		}
		
		$this->manifest=$manifest->saveXML();
	}
	/**
	 * set image 
	 * @param DomNode $node
	 * @param string $name
	 * @param string $value
	 */
	protected function setDraw(DOMNode &$draw, $name, $file) {
		if(strpos($file, '<text:tab') !== false) {
			return 'muliple values : fail';
		}
		$imgs=$draw->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:drawing:1.0","image");
		$err = "";
		if ($imgs->length > 0) {
			$img=$imgs->item(0);
			if(file_exists($file)) {
				$draw->setAttribute('draw:name',substr($name,2).' '.uniqid().mt_rand(1000,9999));
				$href='Pictures/dcp'.uniqid().mt_rand(1000000,9999999).substr($img->getAttribute('xlink:href'), -9);
				$img->setAttribute('xlink:href',$href);
				$this->added_images[] = $href;
				if (!copy($file, $this->cibledir.'/'.$href)) {
					$err="copy fail";
				}
				 
				if ($err=="") { // need to respect image proportion
					$width=$draw->getAttribute('svg:width');
					$size=getimagesize($file);
					$unit="";
					if (preg_match("/[0-9\.]+(.*)$/",$width,$reg)) $unit=$reg[1];
					$height=sprintf("%.03f%s",(doubleval($width)/$size[0])*$size[1],$unit);
					$draw->setAttribute('svg:height',$height);
				}
			}
		}
		return $err;
	}
	/**
	 *  parse images
	 */
	protected function parseDraw() {
		$draws=$this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:drawing:1.0","frame");
		foreach ($draws as $draw) {
			$name=trim($draw->getAttribute('draw:name'));
			if(preg_match("/\[(V_[A-Z0-9_-]+)\]/", $name, $reg)) {
				$key=$reg[1];
				if(isset($this->rkey[$key])) {
					$this->setDraw($draw, $key, $this->rkey[$key]);
				}
			}
		}
	}

	/**
	 * remove all xml:id attributes in children nodes
	 * @param DomNode $objNode
	 */
	protected function removeXmlId( &$objNode){
		$objNodeListNested = $objNode->childNodes;
		foreach ( $objNodeListNested as $objNodeNested ){
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
	 */
	protected function replaceNodeText(DOMNode &$objNode, $strOldContent,$strNewContent){
	    if ($strNewContent === null) return;
	    if (is_array($strNewContent)) {
	    	throw new Exception("node replacement must be a string : array found");
	    }
		$objNodeListNested = &$objNode->childNodes;
		foreach ( $objNodeListNested as $objNodeNested ){
			if ($objNodeNested->nodeType == XML_TEXT_NODE) {
				if ($objNodeNested->nodeValue!="") {
					if(strpos($strNewContent, '<text:section>') !== false) {
						$strNewContent = str_replace('<', '--Lower.Than--', $strNewContent);
						$strNewContent = str_replace('>', '--Greater.Than--', $strNewContent);
					}
					$objNodeNested->nodeValue=str_replace($strOldContent,$strNewContent,$objNodeNested->nodeValue);
				}
			}
			elseif ($objNodeNested->nodeType == XML_ELEMENT_NODE) {
				if($objNodeNested->nodeName == 'text:text-input') {
					$name = $objNodeNested->getAttribute('text:description');
					if($name == $strOldContent) {
						$this->setInputField($objNodeNested, substr($name, 1, -1), $strNewContent);
					}
				} elseif($objNodeNested->nodeName == 'text:drop-down') {
					$name = $objNodeNested->getAttribute('text:name');
					if($name == $strOldContent) {
						$this->setDropDownField($objNodeNested, substr($name, 1, -1), $strNewContent);
					}
				} elseif($objNodeNested->nodeName == 'draw:frame') {
					$name = $objNodeNested->getAttribute('draw:name');
					if(substr($name, 0, strlen($strOldContent)) == $strOldContent) {
						$this->setDraw($objNodeNested, substr($strOldContent, 1, -1), $strNewContent);
					}
				} else {
					$this->replaceNodeText($objNodeNested,$strOldContent,$strNewContent);
				}
			}
		}
		 
	}
	/**
	 * parse bullet lists
	 */
	protected function parseListItem() {
		$lists=$this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0","list");
		foreach ($lists as $list) {
			$items=$list->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0","list-item");
			if ($items->length > 0) {
				$item=$items->item(0);
				$skey=implode('|',array_keys($this->arrayMainKeys));
				if (preg_match_all("/\[($skey)\]/",$this->innerXML($list) ,$reg)) {
					$reg0=$reg[0];
					$tvkey=array();
					$maxk=0;
					foreach ($reg0 as $k=>$v) {
						$key=substr(trim($v),1,-1);
						$tvkey[$key]=$this->arrayMainKeys[$key];
						$maxk=max(count($tvkey[$key]),$maxk);
					}
					if ($maxk > 0) {
						for ($i=0;$i<$maxk;$i++) {
							$clone=$item->cloneNode(true);
							$item->parentNode->appendChild($clone);
							foreach ($tvkey as $kk=>$key) {
								$this->replaceNodeText($clone,"[$kk]",$key[$i]);
							}
						}
					}
					$item->parentNode->removeChild($item);
				}
			}
		}
		return $err;
	}

	
	private function _section_cmp($k1,$k2) {
		if ($k2 > $k1) return 1;
		else if ($k2 < $k1) return -1;
		return 0;
	}
	/**
	 * parse section repeat
	 */
	protected function parseSection() {

		$lists=$this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0","section");
		$ks=0;
		$section=array();
		// need to inpect section with max depth before to avoid et repeat top level section
		foreach ($lists as $item) {
			$depth=$this->getNodeDepth($item);				
			$section[($depth*200)+$ks]=$item;
			$ks++;
		}
		//reorder by depth
		uksort($section,array($this, "_section_cmp"));
		
	
		foreach ($section as $item) {
			
				$skey=implode('|',array_keys($this->arrayMainKeys));
				if (preg_match_all("/\[($skey)\]/",$this->innerXML($item) ,$reg)) {
					$reg0=$reg[0];
					$tvkey=array();
					$maxk=0;
					foreach ($reg0 as $k=>$v) {
						$key=substr(trim($v),1,-1);
						$tvkey[$key]=$this->arrayMainKeys[$key];
						$maxk=max(count($tvkey[$key]),$maxk);
					}
					if ($maxk > 0) {
						for ($i=0;$i<$maxk;$i++) {
							$clone=$item->cloneNode(true);
							$item->parentNode->insertBefore($clone,$item);
							foreach ($tvkey as $kk=>$key) {
								$this->replaceNodeText($clone,"[$kk]",$key[$i]);
							}
							$this->replaceRowIf($clone,array($i)); // main level
	                        $this->replaceRowNode($clone,array($i)); // inspect sub levels
						}
					}
					$item->parentNode->removeChild($item);
				}
			
		}
	}

	
	/**
	 * modify a text:input field value
	 * 
	 * @param DomNode $node
	 * @param string $name
	 * @param string $value
	 */
	protected function setInputField(DOMNode &$node, $name, $value) {
		if(strpos($file, '<text:tab') !== false) {
			return 'muliple values : fail';
		}
		$node->nodeValue = $value;
		$node->setAttribute("text:description", '[PP'.$name.'PP]');
	}
	
	/**
	 * modify a text:drop-down list
	 * 
	 * @param DomNode$node
	 * @param string $name
	 * @param string $value
	 */
	protected function setDropDownField(DOMNode &$node, $name, $value) {
		if(strpos($file, '<text:tab') !== false) {
			return 'muliple values : fail';
		}
		$this->removeAllChilds($node);
		$node->setAttribute("text:name", '[PP'.$name.'PP]');
		$value = str_replace(array("&lt;","&gt;","&amp;"),array("<",">",'&'),$value);
		$item = new DOMElement('text:label', '', 'urn:oasis:names:tc:opendocument:xmlns:text:1.0');
		$item = $node->appendChild($item);
		$item->setAttribute("text:current-selected", 'true');
		$item->setAttribute("text:value", $value);
		$node->appendChild(new DOMText($value));
	}
	/**
	 * remove all child nodes
	 * 
	 * @param DomNode $objNode
	 */
	protected function removeAllChilds(DOMNode &$objNode) {
		$objNodeListNested = $objNode->childNodes;
		$objNode->nodeValue = '';
		if(!empty($objNodeListNested) && $objNodeListNested->length > 0) {
			foreach ( $objNodeListNested as $objNodeNested ){
				$objNode->removeChild($objNodeNested);
			}
		}
	}
	/**
	 * parse tables
	 */
	protected function parseTableRow() {
		$lists=$this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:table:1.0","table-row");
		$validRow=array();
		
		$skey=implode('|',array_keys($this->arrayMainKeys));
		foreach ($lists as $rowItem) {
			if (preg_match("/\[($skey)\]/",$this->innerXML($rowItem) ,$reg)) {
				$validRow[]=$rowItem;
			}
		}
		foreach ($validRow as $rowItem) {
			if (preg_match_all("/\[($skey)\]/",$this->innerXML($rowItem),$reg)) {
				$reg0=$reg[0];
				$tvkey=array();
				$maxk=0; // search values which has the greatest number of values
				foreach ($reg0 as $k=>$v) {
					$key=substr(trim($v),1,-1);
					$tvkey[$key]=$this->arrayMainKeys[$key];
					$maxk=max(count($tvkey[$key]),$maxk);
				}
				if ($maxk > 0) {
					for ($i=0;$i<$maxk;$i++) {
						$clone=$rowItem->cloneNode(true);
						$rowItem->parentNode->insertBefore($clone,$rowItem);
						foreach ($tvkey as $kk=>$key) {
							$this->replaceNodeText($clone,"[$kk]",$key[$i]);
						}
						$this->replaceRowIf($clone,array($i)); // main level
						$this->replaceRowNode($clone,array($i)); // inspect sub levels
						 
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
	 */
	private static function getArrayDepth($v) {
	    $depth=-1;
	    while (is_array($v)) {
	        $depth++;
	        $v=current($v);
	    }
	    return $depth;	    
	}
	/**
	 * 
	 * Retrieve one of values for a multi value key
	 * @param string $key the key name (multiple values) 
	 * @param array $levelPath path to access of a particular value
	 */
	protected function getArrayKeyValue($key,array $levelPath) {
		$value=null;

		if (count($levelPath)==1) {
			if ( isset($this->arrayMainKeys[$key])) {
				$index=current($levelPath);
				return $this->arrayMainKeys[$key][$index];
			}
		}
		if (! isset($this->arrayKeys[$key])) return null;

		$value=$this->arrayKeys[$key];
		foreach ($levelPath as $index) {
			$value=$value[$index];
		}

		return $value;
		 
	}
	/**
	 * fix span cause when IF/ENDIF are not on the same depth
	 * @param $s
	 */
	private function fixSpanIf(&$s) {
				$s=preg_replace("/<text:span ([^>]*)>\s*<\/text:p>/s","</text:p>",$s);
				$s=preg_replace("/<text:p ([^>]*)>\s*<\/text:span>/s","<text:p \\1>",$s);
		
	}
	
	/**
	 * 
	 * Inspect conditions in cells
	 * @param string_type $row
	 * @param array $levelPath
	 */
	protected function replaceRowIf(DOMNode &$row, array $levelPath) {

		$this->removeXmlId($row);
		$inner=$row->ownerDocument->saveXML($row);

		$head='<?xml version="1.0" encoding="UTF-8"?><office:document-content xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" office:version="1.0">';
		$foot='</office:document-content>';

		$replacement = preg_replace(
       "/(?m)\[IF(NOT)?\s*([^\]]*)\](.*?)\[ENDIF\s*\\2\]/se", 
       "\$this->TestIf('\\2','\\3','\\1',\$levelPath)",
		$inner);

		$this->fixSpanIf($replacement);
	  
	
		$dxml=new DomDocument();

		$dxml->loadXML($head.$replacement.$foot);
		$ot=$dxml->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:office:1.0","document-content");
		if ($ot->length > 0) {
			$newnode=$this->dom->importNode($ot->item(0)->firstChild, true);
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
	 * 
	 * Inspect sub tables
	 * @param string_type $row
	 * @param array $levelPath
	 */
	protected function replaceRowNode(DOMNode &$row, array $levelPath) {
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
	 * @param DOMNode $row
	 * @param array $levelPath
	 * @param string $ns namespace for filter items (like text or table)
	 * @param string $tag tag for filter (like table-row or list-item)
	 * @param boolean $recursive recursive mode 
	 */
	protected function replaceRowSomething(DOMNode &$row, array $levelPath,$ns,$tag,$recursive) {
	    if (count($this->arrayKeys)==0) return;// nothing to do
	    $keys=array();
	    $subIndex=count($levelPath);
	    foreach ($this->arrayKeys as $k=>$v) {
	        if ($this->getArrayDepth($v) == $subIndex) $keys[]=$k;
	        
	    }
	   
	    $rowList=$row->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:$ns:1.0",$tag);
	    if ($rowList->length > 0) {
	        $skey=implode('|',$keys);
	       /* print "<h1>$tag</h1>";
	        print_r2($levelPath);
	        print_r2($keys);*/
	        foreach ($rowList as $item) {
	            if (preg_match_all("/\[($skey)\]/",$this->innerXML($item) ,$reg)) {
	              
	            	
	                    $maxk=0;
	                    foreach ($reg[1] as $k=>$v) {	        	                        
	                        $vkey=$this->getArrayKeyValue($v, $levelPath);
	                        $tvkey[$v]=$vkey;
	                        $maxk=max(count($tvkey[$v]),$maxk);
	                    }
	                   
	                if ($maxk > 0) {
	                    for ($i=0;$i<$maxk;$i++) {
	                        $clone=$item->cloneNode(true);
	                        $item->parentNode->appendChild($clone);
	                        foreach ($tvkey as $kk=>$key) {
	                            $this->replaceNodeText($clone,"[$kk]",$key[$i]);
	                        }
	                        $newPath=array_merge($levelPath, array($i));
	                        $this->replaceRowIf($clone,$newPath);
	                        //if ($recursive) $this->replaceRowSomething($clone,$newPath,$ns,$tag,$recursive);
	                        if ($recursive) $this->replaceRowNode($clone,$newPath);
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
	protected function parseInput() {
		$lists=$this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0","text-input");
		foreach ($lists as $list) {
			$name=$list->getAttribute("text:description");
			if(preg_match("/\[(V_[A-Z0-9_-]+)\]/", $name, $reg)) {
				$key=$reg[1];
				if(isset($this->rkey[$key])) {
					$this->setInputField($list, $key, $this->rkey[$key]);
				}
			}
		}
	}
	/**
	 * parse text:drop-down
	 */
	protected function parseDropDown() {
		$lists=$this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0","drop-down");
		foreach ($lists as $list) {
			$name=$list->getAttribute("text:name");
			if(preg_match("/\[(V_[A-Z0-9_-]+)\]/", $name, $reg)) {
				$key=$reg[1];
				if(isset($this->rkey[$key])) {
					$this->setDropDownField($list, $key, $this->rkey[$key]);
				}
			}
		}
	}
	/**
	 * restore protected values
	 */
	protected function restoreProtectedValues() {
		$this->template = preg_replace('/\[PP(V_[A-Z0-9_]+)PP\]/s', '[$1]', $this->template);
		$this->template = str_replace('--Lower.Than--', '<', $this->template);
		$this->template = str_replace('--Greater.Than--', '>', $this->template);
	}
	/**
	 * parse section and clone "tpl_xxx" sections into saved_sections
	 */
	protected function parseTplSection() {
		$this->saved_sections=array();
		// remove old generated sections
		$lists=$this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0","section");
		foreach ($lists as $list) {
			$name=$list->getAttribute("text:name");
			if(substr($name, 0, 5) == '_tpl_') {
				$list->parentNode->removeChild($list);
			}
		}
		// clone sections and generate them again
		$lists=$this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0","section");
		foreach ($lists as $list) {
		    $name=$list->getAttribute("text:name");
		    if(substr($name, 0, 4) == 'tpl_') {
		        $this->removeXmlId($list);
		        // restore original style name of first head
		        $heads=$list->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0","h");
		         if ($heads->length > 0) {
                            $firsthead=$heads->item(0);
                            $firsthead->setAttribute("text:style-name", trim($firsthead->getAttribute('text:style-name'),'_'));
		         }
		        $originSection = $this->saved_sections[$name] = $list->cloneNode(true);
		        $list->setAttribute("text:name", '_'.$name);
		        $list->setAttribute("text:protected", 'true');
		        $list->setAttribute("text:display", 'true');
                        
		        // // special treatment to have correct chapter numeration search first header
                        $heads=$originSection->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0","h");
                        if ($heads->length > 0) {
                            $firsthead=$heads->item(0);
                            $styleName=$firsthead->getAttribute("text:style-name");
                            if ($styleName) {
                                $styleName=trim($styleName,'_');
                                $styles=$this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:style:1.0","style");

                                //addLogMsg(array("style"=>$styleName,"length"=>$styles->length));
                                $tStyleName=array();
                                foreach ($styles as $style) {

                                    $aStyleName=$style->getAttribute("style:name");
                                    $tStyleName[]=$aStyleName;
                                    if ($aStyleName == $styleName) {
                                        $copyName='_'.$styleName.'_';
                                        if (! (in_array($copyName, $tStyleName))) {
                                            $cloneStyle=$style->cloneNode(true);
                                            $cloneStyle->setAttribute("style:name", $copyName);
                                            $cloneStyle->setAttribute("style:list-style-name",""); // unset numeration chapter
                                            
                                            $style->parentNode->insertBefore($cloneStyle,$style);
                                        }
                                        $firsthead->setAttribute("text:style-name",$copyName);
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
	protected function restoreSection() {
		
		$inserts_to_do = array();
	
		$lists=$this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0","section");
		foreach ($lists as $list) {
			$name=$list->getAttribute("text:name");
			if(substr($name, 0, 5) == '_tpl_' && isset($this->saved_sections[substr($name, 1)])) {
				$node = $this->dom->importNode($this->saved_sections[substr($name, 1)], true);
				$inserts_to_do[] = array($node, $list);
			}
		}
		foreach($inserts_to_do as $insert_to_do) {
                    //$node = $insert_to_do[1]->parentNode->insertBefore($insert_to_do[0], $insert_to_do[1]);
                        // insert after
                        if ($insert_to_do[1]->nextSibling) {
		    $node = $insert_to_do[1]->parentNode->insertBefore($insert_to_do[0], $insert_to_do[1]->nextSibling);
                        } else {
                        $node = $insert_to_do[1]->parentNode->appenChild($insert_to_do[0]);
                        }
		    $node->setAttribute("text:protected", 'true');
		    $node->setAttribute("text:display", 'none');
		}
	}

	/**
	 * Initialize of list
	 * @param string $key the key variable
	 * @param array $t the values of the key
	 */
	public function setColumn($key,array $t) {
	    $ti=array();
	    $k=0;
	    foreach ($t as $v) $ti[$k++]=$v; // delete associative keys
	    if (is_array(current($t))) $this->setArray($key,$ti);
	    else $this->arrayMainKeys[$key]=$ti;
	    //else $this->set($key,implode('<text:tab/>',$t));
	}
	
	protected function setArray($key,array $t) {
	    if (!$key ) throw new Exception('Key must not be empty');
	    $this->arrayKeys[$key]=$t;
	}
	/**
	 * @deprecated
	 * @param unknown_type $p_nom_block
	 * @param unknown_type $data
	 */
	public function SetBlockData($p_nom_block,$data) {
		if ($p_nom_block != "") {
			if ($data!=null && $this->encoding=="utf-8") {
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
			$this->data[$p_nom_block]=$data;
			if (is_array($data))  {
				reset($data);
				$elem = pos($data);
				if ( isset($elem) && is_array($elem)) {
					reset($elem);
					while (list($k,$v)=each($elem)) {
						if (!isset($this->corresp["$p_nom_block"]["[$k]"])) {
							$this->SetBlockCorresp($p_nom_block,$k);
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
	public function setrepeatable(array $data) {
	
			$t=array();
			if (is_array($data)) {
				foreach ($data as $k=>$v) {
					foreach ($v as $ki=>$vi) {
						$t[$ki][$k]=$vi;
					}
				}
			}
			// fill array
			$max=0;
			foreach ($t as $k=>$v) {
				if (count($v) > $max) $max=count($v);
			}
			foreach ($t as $k=>$v) {
				if (count($v) < $max) {
					$fill=array_fill(0,$max,'');
					foreach ($v as $idx=>$vi) {
						$fill[$idx]=$vi;
					}
					$t[$k]=$fill;
				}
			}
			// affect completed columns
			foreach ($t as $k=>$v) {
				$this->setColumn($k,$v);
			}
		
	}
	
	protected function addHTMLStyle() {
		$xmldata='<xhtml:html xmlns:xhtml="http://www.w3.org/1999/xhtml">'."</xhtml:html>";

		$xslt = new xsltProcessor;
		$xslt->importStyleSheet(DomDocument::load(DEFAULT_PUBDIR."/CORE/Layout/html2odt.xsl"));
		$xmlout= $xslt->transformToXML(DomDocument::loadXML($xmldata));

		$dxml=new DomDocument();
		$dxml->loadXML($xmlout);
		$ot=$dxml->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:office:1.0","automatic-styles");
		$ot1=$ot->item(0);
		$ass=$this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:office:1.0","automatic-styles");

		$ass0=$ass->item(0);
		foreach ($ot1->childNodes as $ots) {
			$c=$this->dom->importNode($ots,true);
			$ass0->appendChild($c);
		}

	}
	/**
	 * Delete not used images (need when reuse template where repeat section)
	 */
	protected function removeOrphanImages() {
		$imgs=$this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:drawing:1.0","image");
		$used_images = array();
		foreach($imgs as $img) {
			$href = basename($img->getAttribute('xlink:href'));
			if(substr($href, 0, 7) == 'dcp') {
				$used_images[] = $href;
			}
		}
		$files = glob($this->cibledir.'/Pictures/dcp*');
		if(is_array($files)) {
			foreach($files as $file) {
				if(!in_array(basename($file), $used_images)) {
					$this->removed_images[] = 'Pictures/'.basename($file);
					@unlink($file);
				}
			}
		}
	}

	function GenJsRef() {return "";  }
	function GenJsCode($showlog) { return("");  }
	function ParseJs(&$out) {  }
	function GenCssRef() { return "";  }
	function GenCssCode() { return("");  }
	function ParseCss(&$out) {  }
	
	
	/**
	 * generate OOo document style part
	 */
	protected function genStyle() {

		$this->dom=new DOMDocument();

		$this->dom->loadXML($this->style_template);
		if ($this->dom) {
			$this->template=$this->style_template;
			
			$this->parseDraw();
			$this->template=$this->dom->saveXML();
			
			$this->hideUserFieldSet();
			$this->ParseIf();
			$this->ParseKey();
			$this->ParseText();
			$this->restoreUserFieldSet();
			$this->style_template=$this->template;
		}
	}

	/**
	 * generate OOo document meta part
	 */
	protected function genMeta() {
		$this->dom=new DOMDocument();

		$this->dom->loadXML($this->meta_template);
		if ($this->dom) {
			$this->template=$this->meta_template;
			
			$this->ParseIf();
			$this->ParseKey();
			$this->ParseText();
			
			$this->meta_template=$this->template;
		}
	}
	/**
	 * generate OOo document content
	 */
	protected function genContent() {

		$this->dom=new DOMDocument();

		$this->dom->loadXML($this->content_template);
		if ($this->dom) {
			$this->template=$this->content_template;
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
			$this->template=$this->dom->saveXML();
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

			$this->dom=new DOMDocument();
			if ($this->dom->loadXML($this->template)) {
				$this->restoreSection();
				// not remove images because delete images defined in style.xml
				//$this->removeOrphanImages();
				$this->template=$this->dom->saveXML();
					
				$this->content_template=$this->template;
			} else {
				print $this->template;
				throw new Exception("cannot product ooo template");
			}
		} else {
			throw new Exception(sprintf("not openDocument file %s",$this->file));
		}
	}
	
	/**
	 * generate OOo document
	 */
	public function gen() {
		// if used in an app , set the app params
		if (is_object($this->action)) {
			$list=$this->action->parent->GetAllParam();
			while (list($k,$v)=each($list)) {
				$v=str_replace(array('<BR>','<br>','<br/>','<br />'),'<text:line-break/>',$v);
				$this->set($k,$v);
			}
		}

		// $this->rif=&$this->rkey;
		// $this->ParseIf($out);

		// Parse IMG: and LAY: tags

		$this->genContent();
			
		$this->genStyle();
		$this->genMeta();
		$this->updateManifest();
		$outfile=uniqid(getTmpDir()."/odf").'.odt';
		$this->content2odf($outfile);
//print_r2($this->content_template);exit;
		return($outfile);
	}
}
?>