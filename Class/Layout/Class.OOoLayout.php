<?php
/**
 * Layout Class for OOo files
 *
 * @author Anakeen 2000 
 * @version $Id: Class.OOoLayout.php,v 1.13 2008/02/18 17:24:03 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */




include_once('Class.Layout.php');


class OOoLayout extends Layout {

//############################################

//#

  private $strip='Y';
  public $encoding="utf-8";
//########################################################################
//# Public methods
//#  
//#



/**
   * construct layout for view card containt
   *
   * @param string $caneva file of the template
   * @param Action $action current action
   * @param string $template default template
   */
  function __construct($caneva="",$action="",$template="[OUT]") {
    $this->LOG = new Log("","Layout");     
    $this->template = $template;
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
	  $this->dom=new DOMDocument();
	  $this->dom->loadXML($this->template);
	}
      } else {
	$this->template="file $file not exists";
      }
    }
  }



  function ParseBlock(&$out) {
    $out = preg_replace(
       "/(?m)\[BLOCK\s*([^\]]*)\](.*?)\[ENDBLOCK\s*\\1\]/se", 
       "\$this->SetBlock('\\1','\\2')",
       $out);
  }

  function TestIf($name,$block,$not=false) {    
    $out = "";     
    if (isset($this->rif[$name]) || isset($this->rkey[$name]) ) {
      $n = (isset($this->rif[$name]))?$this->rif[$name]:$this->rkey[$name];
      if ($n xor $not)  {
	if ($this->strip=='Y') {
	  $block = str_replace("\\\"","\"",$block);
	}
	$out=$block;      
	//$this->ParseBlock($out);
	//	$this->ParseIf($out);
      }
    } else {
      if ($this->strip=='Y') $block = str_replace("\\\"","\"",$block);
      
      if ($not) $out="[IFNOT $name]".$block."[ENDIF $name]";
      else $out="[IF $name]".$block."[ENDIF $name]";
    }
    return ($out);
  } 
  function ParseIf() {
    $this->template = preg_replace(
       "/(?m)\[IF(NOT)?\s*([^\]]*)\](.*?)\[ENDIF\s*\\2\]/se", 
       "\$this->TestIf('\\2','\\3','\\1')",
       $this->template);
  }

  function ParseZone(&$out) {
    $out = preg_replace(
       "/\[ZONE\s*([^:]*):([^\]]*)\]/e",
       "\$this->execute('\\1','\\2')",
       $out);
  }

  function ParseKey() {
    if (isset ($this->rkey)) {      
	$this->template=preg_replace($this->pkey,$this->rkey,$this->template);
	$this->style_template=preg_replace($this->pkey,$this->rkey,$this->style_template);
    }
  }
  function ParseKeyXML() {
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

 function odf2content($odsfile) {
  if (! file_exists($odsfile)) return "file $odsfile not found";
  $this->cibledir=uniqid("/var/tmp/odf");
  
  $cmd = sprintf("unzip  %s  -d %s >/dev/null", $odsfile, $this->cibledir );
  system($cmd);
  
  $contentxml=$this->cibledir."/content.xml";
  if (file_exists($contentxml)) {
    $this->template=file_get_contents($contentxml);
    unlink($contentxml);
  }
  $contentxml=$this->cibledir."/styles.xml";
  if (file_exists($contentxml)) {
    $this->style_template=file_get_contents($contentxml);
    unlink($contentxml);
  }
  
}
 function content2odf($odsfile) {
  if (file_exists($odsfile)) return "file $odsfile must not be present";
  
  
  $contentxml=$this->cibledir."/content.xml";
    $this->template=preg_replace("/<text:span ([^>]*)>\s*<text:section>/s","<text:section>",$this->template);
   $this->template=preg_replace("/<\/text:section>\s*<\/text:span>/s","</text:section>",$this->template);


  $this->template=preg_replace("/<table:table-cell ([^>]*)>\s*<text:section>/s","<table:table-cell \\1>",$this->template);
  $this->template=preg_replace("/<\/text:section>\s*<\/table:table-cell>/s","</table:table-cell>",$this->template);



  $this->template=preg_replace("/<text:p ([^>]*)>\s*<text:section[^>]*>/s","<text:section>",$this->template);
  $this->template=preg_replace("/<text:p ([^>]*)><text:([^\/]*)\/>\s*<text:section[^>]*>/s","<text:section><text:\\2/>",$this->template);
  $this->template=preg_replace("/<\/text:section>\s*<\/text:p>/s","</text:section>",$this->template);


  file_put_contents($contentxml,$this->template);
  
  $contentxml=$this->cibledir."/styles.xml";
  file_put_contents($contentxml,$this->style_template);
  
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
	if (ereg("([^=]*)=(.*)",$v, $regs)) {
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
           

  function set($tag,$val) {
    if ( !isUTF8($val)) $val = utf8_encode($val);
    if (! $this->isXml($val)) {
      $this->pkey[$tag]="/\[$tag\]/";
      $this->rkey[$tag]=$val;
    } else {
      
      $this->rkeyxml[$tag]=$val;
      
    }
  }


  function isXML($val) {
    return false;
    return preg_match("/<text:/",$val);
  }

  function get($tag) {
    if (isset($this->rkey)) return $this->rkey[$tag];
    return "";
  }

  function ParseText() {    
     if ($this->encoding=="utf-8") bind_textdomain_codeset("what", 'UTF-8');
     $this->template = preg_replace("/\[TEXT:([^\]]*)\]/e",
                         "\$this->Text('\\1')",
                         $this->template);
     if ($this->encoding=="utf-8") bind_textdomain_codeset("what", 'ISO-8859-15'); // restore
  }

  function parseDraw() {
    $draws=$this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:drawing:1.0","frame");
    //    $draws=$this->dom->getElementsByTagName("frame");
    //    print count($draws);
    foreach ($draws as $draw) {
      $name=trim($draw->getAttribute('draw:name'));

      if (substr($name,0,3)=='[V_') {
	$imgs=$draw->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:drawing:1.0","image");
	
	if ($imgs->length > 0) {
	  $img=$imgs->item(0);

	  $href=$img->getAttribute('xlink:href');
	  $name=substr(trim($name),1,-1);
	  $draw->setAttribute('draw:name',substr($name,2));
	  $file=$this->rkey[$name];
	  
	  if (!copy($file, $this->cibledir.'/'.$href)) {
	    $err="copy fail";
	  } 
	  
	  if ($err=="") { // need to respect image proportion
	    $width=$draw->getAttribute('svg:width');
	    $size=getimagesize($file);
	    $unit="";
	    if (ereg("[0-9\.]+(.*)$",$width,$reg)) $unit=$reg[1];	    
	    $height=sprintf("%.03f%s",(doubleval($width)/$size[0])*$size[1],$unit);
	    $draw->setAttribute('svg:height',$height);
	  }
	}
      }
        
    }

    return $err;
  }

  function replaceNodeText( &$objNode, $strOldContent,$strNewContent){
    /*
    This function replaces a node's string content with strNewContent
    */
    $objNodeListNested = &$objNode->childNodes;
    foreach ( $objNodeListNested as $objNodeNested ){
      if ($objNodeNested->nodeType == XML_TEXT_NODE) {
	if ($objNode->nodeValue!="") {
	  $objNode->nodeValue=str_replace($strOldContent,$strNewContent,$objNode->nodeValue);
	}
      }
      if ($objNodeNested->nodeType == XML_ELEMENT_NODE) $this->replaceNodeText($objNodeNested,$strOldContent,$strNewContent);
    }
   
}

  function parseListItem() {
    $lists=$this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0","list");
    foreach ($lists as $list) {
	$items=$list->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0","list-item");
	if ($items->length > 0) {
	  $item=$items->item(0);
	  if (preg_match("/\[V_[A-Z0-9_-]+\]/",$item->textContent ,$reg)) {
	    
	    $key=substr(trim($reg[0]),1,-1);
	    $vkey=$this->rkey[$key];
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
    return $err;
  }


  function parseTableRow() {
    $lists=$this->dom->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:table:1.0","table");
    foreach ($lists as $list) {
      $items=$list->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:table:1.0","table-row");
      if ($items->length > 0) {
	$findv=false;
	foreach ($items as $item) {	    
	  if (preg_match("/\[V_[A-Z0-9_-]+\]/",$item->textContent ,$reg)) {
	    $findv=true;;
	    break;
	  }
	}
	if ($findv) {
	  if (preg_match_all("/\[V_[A-Z0-9_-]+\]/",$item->textContent ,$reg)) {
	    $reg0=$reg[0];
	    $tvkey=array();
	    $maxk=0;
	    foreach ($reg0 as $k=>$v) {	      
	      $key=substr(trim($v),1,-1);
	      $vkey=$this->rkey[$key];
	      $tvkey[$key]=explode('<text:tab/>',$vkey);
	      $maxk=max(count($tvkey[$key]),$maxk);
	    }
	    if ($maxk > 1) {
	      for ($i=0;$i<$maxk;$i++) {
		$clone=$item->cloneNode(true);
		$item->parentNode->appendChild($clone);
		foreach ($tvkey as $kk=>$key) {
		  $this->replaceNodeText($clone,"[$kk]",$key[$i]);
		}
	      }
	      $item->parentNode->removeChild($item);
	    }	    
	  }	
	}
      }
    }
    return $err;
  }

  /**
   * Initialize of list
   * $key must begin with V_ and be uppercase
   */
  function setColumn($key,$t) {
    $this->set($key,implode('<text:tab/>',$t));
  }
  function SetBlockData($nothing,$data) {
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
  function addHTMLStyle() {
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

  function GenJsRef() {return "";  }
  function GenJsCode($showlog) { return("");  }
  function ParseJs(&$out) {  }
  function GenCssRef() { return "";  }
  function GenCssCode() { return("");  }
  function ParseCss(&$out) {  }
  function gen() {

    // if used in an app , set the app params
    if (is_object($this->action)) {
      $list=$this->action->parent->GetAllParam();
      while (list($k,$v)=each($list)) {
        $this->set($k,$v);
      }
    }  

    //    $this->ParseBlock($out);
    // $this->rif=&$this->rkey;
    // $this->ParseIf($out);

    // Parse IMG: and LAY: tags
    if ($this->dom) {
    $this->ParseDraw();
    $this->parseListItem();
    $this->parseTableRow();
    $this->addHTMLStyle();
    $this->template=$this->dom->saveXML();
    // Parse i18n text

    $this->ParseIf();
    //$this->ParseKeyXml();
    //$this->template=$this->dom->saveXML();
    //      print $this->template;exit;
    $this->ParseKey();
    $this->ParseText();

    $outfile=uniqid("/var/tmp/odf").'.odt';
    $this->content2odf($outfile);
    } else {
      $outfile=$this->template;
    }
    return($outfile);
  }
}
?>
