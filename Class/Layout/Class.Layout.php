<?php
/**
 * Layout Class
 *
 * @author Anakeen 2000 
 * @version $Id: Class.Layout.php,v 1.46 2008/05/06 14:11:50 eric Exp $
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @package WHAT
 * @subpackage CORE
 */
 /**
 */

// 
// PHP Layout Class
//   this class is designed to perform the final page layout of
//   an application.
//   this class uses a template with three dynamic zones header,toc and main 
//   doc.
//   
// 
// Layout Class can manage three kind of datas :
//  
// 1) Simple tags :
//    those tags are enclosed into brackets [] and can be replaced with any
//    dynamic data given with the Set method.
//    e.g : [MYDATA]  => $this->Set("MYDATA","this is my text");
//
// 2) Block of Data :
//    those tags are used to manage repeated set of data (as table for instance)
//    You can assign a table of data to a specific block.
//    e.g : $table = array ( "0" => array ( "name" => "John",
//                                          "surname" => "Smith"),
//                           "1" => array ( "name" => "Robert",
//                                          "surname" => "Martin"));
//
//    the block : [BLOCK IDENTITY]
//                <tr><td align="left">[NAME]</td>
//                    <td align="right">[SURNAME]</td>
//                </tr>
//                [ENDBLOCK IDENTITY]
//
//   the code :   $lay = new Layout ("file containing the block");
//                $lay->SetBlockCorresp("IDENTITY","NAME","name");
//                $lay->SetBlockCorresp("IDENTITY","SURNAME","surname");
//                $lay->SetBlockData("IDENTITY",$table);
//   
//                $out = $lay->gen();
//
//      $out  :   <tr><td align="left">John</td>
//                    <td align="right">Smith</td>
//                </tr>
//                <tr><td align="left">Robert</td>
//                    <td align="right">Martin</td>
//                </tr>
//
// 3) Call a specific script (need Core App Environment to work)
//   tag syntax : [ZONE zonename]
//
//     the zone name is linked to a specific application/function
//
//          eg :  [ZONE CORE:APPLIST]
//        
//         then the APPLIST function in the CORE Application is called
//           this function can then use another layout etc......
//
//
// Copyright (c) 1999 Anakeen S.A.
//               Yannick Le Briquer
//
//  $Id: Class.Layout.php,v 1.46 2008/05/06 14:11:50 eric Exp $

$CLASS_LAYOUT_PHP="";
include_once('Class.Log.php');  
include_once('Class.Action.php');  
include_once('Class.Application.php');


class Layout {

//############################################

//#

  private $strip='Y';
  public $encoding="";
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
  function Layout($caneva="",$action="",$template="[OUT]") {
    $this->LOG = new Log("","Layout");     
    $this->template = sprintf(_("Template [%s] not found"),$caneva);
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
	  $fd = fopen($file,"r");
	  if ($fd) {
	    $this->file=$file;
	    $this->template = fread($fd,filesize($file));
	  }
	}
      } 
    }
  }


 function SetBlockCorresp($p_nom_block,$p_nom_modele,$p_nom=NULL) {
   $this->corresp["$p_nom_block"]["[$p_nom_modele]"]=($p_nom==NULL?$p_nom_modele:"$p_nom");
  }

  function SetBlockData($p_nom_block,$data=NULL) {
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
    $this->data["$p_nom_block"]=$data;
    // affect the corresp block if not
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
  }

  function GetBlockData($p_nom_block) {
    if (isset($this->data["$p_nom_block"])) return $this->data["$p_nom_block"];
    return false;
  }
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
	  if ((! is_object($v[$v2])) && (! is_array($v[$v2]))) $loc = str_replace( $k2, $v[$v2], $loc);
           
	}
	$this->rif=&$v;
	$this->ParseIf($loc);
        $out .= $loc;
      }
    }
    $this->ParseBlock($out);
    return ($out);
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
       "/(?m)\[IF(NOT)?\s+([^\]]*)\](.*?)\[ENDIF\s*\\2\]/se", 
       "\$this->TestIf('\\2','\\3','\\1')",
       $out);
  }

  function ParseZone(&$out) {
    $out = preg_replace(
       "/\[ZONE\s+([^:]*):([^\]]*)\]/e",
       "\$this->execute('\\1','\\2')",
       $out);
  }

  function ParseKey(&$out) {
    if (isset ($this->rkey)) {
      $out=str_replace($this->pkey,$this->rkey,$out);
    }
  }

  /**
   * define new encoding text
   * default is iso8859-1
   */
  function setEncoding($enc) {
    if ($enc=="utf-8") {
      $this->encoding = $enc;
      // bind_textdomain_codeset("what", 'UTF-8');
    }
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
    $this->pkey[$tag]="[$tag]";
    $this->rkey[$tag]=$val;
  }

  function get($tag) {
    if (isset($this->rkey)) return $this->rkey[$tag];
    return "";
  }

  function ParseRef(&$out) {

     $out = preg_replace("/\[IMG:([^\]]*)\]/e",
                         "\$this->action->GetImageUrl('\\1')",
                         $out);

     $out = preg_replace("/\[IMGF:([^\]]*)\]/e",
                         "\$this->action->GetFilteredImageUrl('\\1')",
                         $out);

     $out = preg_replace("/\[SCRIPT:([^\]]*)\]/e",
                         "\$this->action->GetScriptUrl('\\1')",
                         $out);
     if ($this->encoding=="utf-8") bind_textdomain_codeset("what", 'UTF-8');
     $out = preg_replace("/\[TEXT:([^\]]*)\]/e",
                         "\$this->Text('\\1')",
                         $out);
     if ($this->encoding=="utf-8") bind_textdomain_codeset("what", 'ISO-8859-15'); // restore
  }

  function Text($s) {
    if ($s=="") return $s;
    return _($s);
  }

  function GenJsRef() {
    $js = "";
    $list[]=$this->action->GetParam("CORE_JSURL")."/logmsg.js?wv=".$this->action->GetParam("WVERSION");
    $list = array_merge($list,$this->action->parent->GetJsRef());

    reset($list);
    while(list($k,$v) = each($list)) {
      $js .= "<script language=\"JavaScript\" src=\"$v\"></script>\n";
    }
    return $js;
  }

  function GenJsCode($showlog) {
    $list = $this->action->parent->GetJsCode();
    reset($list);
    $out = "";
    while(list($k,$v) = each($list)) {
      $out .= $v."\n";
    }

    if ($showlog) {
      // Add log messages
      $list = $this->action->parent->GetLogMsg();
      reset($list);
      $out .= "var logmsg=new Array();\n";
      while(list($k,$v) = each($list)) {
        $out .= "logmsg[$k]='$v';\n";
      }
      $out .= "displayLogMsg(logmsg);\n";
      $this->action->parent->ClearLogMsg();
  
      // Add warning messages
      $list = $this->action->parent->GetWarningMsg();   
      if (count($list) > 0) $out .= "displayWarningMsg('".implode("\\n---------\\n",$list)."');\n";
      $this->action->parent->ClearWarningMsg();
    }

    // Add action notification messages
    $this->action->getActionDone($actcode,$actarg);
    if (count($actcode) > 0) {
    $out .= "var actcode=new Array();\n";
    $out .= "var actarg=new Array();\n";
    foreach ($actcode as $k=>$v) {
      $out .= "actcode[$k]='$v';\n";    
      $out .= "actarg[$k]='".$actarg[$k]."';\n";      
    }
    $out .= "sendActionNotification(actcode,actarg);\n"; 
    $this->action->clearActionDone();
    }
    return($out);
  }

  function ParseJs(&$out) {
       $out = preg_replace("/\[JS:REF\]/e",
                           "\$this->GenJsRef()",
                           $out);
       
       $out = preg_replace("/\[JS:CODE\]/e",
                           "\$this->GenJsCode(true)",
                           $out);
       
       $out = preg_replace("/\[JS:CODENLOG\]/e",
                           "\$this->GenJsCode(false)",
                           $out);
  }

  function GenCssRef() {
    $js = "";
    $list = $this->action->parent->GetCssRef();

    foreach ($list as $k=>$v) {
      $js .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"$v\">\n";
    }
    return $js;
  }

  function GenCssCode() {
    $list = $this->action->parent->GetCssCode();
    reset($list);
    $out = "";
    while(list($k,$v) = each($list)) {
      $out .= $v."\n";
    }
    return($out);
  }
  function ParseCss(&$out) {
       $out = preg_replace("/\[CSS:REF\]/e",
                           "\$this->GenCssRef()",
                           $out);
       
       $out = preg_replace("/\[CSS:CODE\]/e",
                           "\$this->GenCssCode()",
                           $out);
  }
  function gen() {

    // if used in an app , set the app params
    if (is_object($this->action)) {
      $list=$this->action->parent->GetAllParam();
      while (list($k,$v)=each($list)) {
        $this->set($k,$v);
      }
    }  
    $out = $this->template;

    $this->ParseBlock($out);
    $this->rif=&$this->rkey;
    $this->ParseIf($out);

    // Parse IMG: and LAY: tags
    $this->ParseRef($out);
    $this->ParseKey($out);
    $this->ParseZone($out);
    $this->ParseJs($out);
    $this->ParseCss($out);

    return($out);
  }
}
?>
