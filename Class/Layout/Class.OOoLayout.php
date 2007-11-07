<?php
/**
 * Layout Class for OOo files
 *
 * @author Anakeen 2000 
 * @version $Id: Class.OOoLayout.php,v 1.2 2007/11/07 15:09:00 eric Exp $
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
	$this->ParseBlock($out);
	$this->ParseIf($out);
      }
    } else {
      if ($this->strip=='Y') $block = str_replace("\\\"","\"",$block);
      
      if ($not) $out="[IFNOT $name]".$block."[ENDIF $name]";
      else $out="[IF $name]".$block."[ENDIF $name]";
    }
    return ($out);
  } 
  function ParseIf(&$out) {
    $out = preg_replace(
       "/(?m)\[IF(NOT)?\s*([^\]]*)\](.*?)\[ENDIF\s*\\2\]/se", 
       "\$this->TestIf('\\2','\\3','\\1')",
       $out);
  }

  function ParseZone(&$out) {
    $out = preg_replace(
       "/\[ZONE\s*([^:]*):([^\]]*)\]/e",
       "\$this->execute('\\1','\\2')",
       $out);
  }

  function ParseKey(&$out) {
    if (isset ($this->rkey)) {
      $out=preg_replace($this->pkey,$this->rkey,$out);
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
  
}
 function content2odf($odsfile,&$out) {
  if (file_exists($odsfile)) return "file $odsfile must not be present";
  
  
  $contentxml=$this->cibledir."/content.xml";
  if (file_exists($contentxml)) {
    $this->template=file_get_contents($contentxml);
    unlink($contentxml);
  }

  file_put_contents($contentxml,$out);
  
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
    if ($this->encoding=="utf-8" && !isUTF8($val)) $val = utf8_encode($val);
    $this->pkey[$tag]="/\[$tag\]/";
    $this->rkey[$tag]=$val;
  }

  function get($tag) {
    if (isset($this->rkey)) return $this->rkey[$tag];
    return "";
  }

  function ParseText(&$out) {
    
     if ($this->encoding=="utf-8") bind_textdomain_codeset("what", 'UTF-8');
     $out = preg_replace("/\[TEXT:([^\]]*)\]/e",
                         "\$this->Text('\\1')",
                         $out);
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
	
	foreach ($imgs as $img) {
	  $href=$img->getAttribute('xlink:href');
	  $name=substr(trim($name),1,-1);
	  $file=$this->rkey[$name];
	  if (!copy($file, $this->cibledir.'/'.$href)) {
	    $err="copy fail";
	  }
	}
      }
      
    }

    return $err;
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
    $out = $this->template;

    //    $this->ParseBlock($out);
    // $this->rif=&$this->rkey;
    // $this->ParseIf($out);

    // Parse IMG: and LAY: tags
    $this->ParseDraw();

    // Parse i18n text
    $this->ParseKey($out);
    $this->ParseText($out);
    $outfile=uniqid("/var/tmp/odf").'.odt';
    $this->content2odf($outfile,$out);
    return($outfile);
  }
}
?>
