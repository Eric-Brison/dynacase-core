<?php
/**
 * Import directory with document descriptions
 *
 * @author Anakeen 2000 
 * @version $Id: freedom_import_dir.php,v 1.5 2007/01/19 16:23:32 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage GED
 */
 /**
 */


include_once("FDL/import_tar.php");


function freedom_import_xml(Action &$action) {

  
  $filename = GetHttpVars("filename"); 
  $dbaccess=$action->getParam("FREEDOM_DB");
  global $_FILES;
  if (intval(ini_get("max_execution_time")) < 300) ini_set("max_execution_time", 300);
  if (isset($_FILES["file"])) {
    $filename=$_FILES["file"]['name'];
    $xmlfiles=$_FILES["file"]['tmp_name'];
    $ext=substr($filename,strrpos($filename,'.')+1);
    rename($xmlfiles,$xmlfiles.".$ext");
    $xmlfile.=".$ext";
  } else {
    $filename=GetHttpVars("file");
    $xmlfiles=$filename;
  }
  
  $splitdir=uniqid("/var/tmp/xmlsplit");
  @mkdir($splitdir);
  if (! is_dir($splitdir)) $action->exitError(_("Cannot create directory %s for xml import"),$splitdir);
  $err=splitXmlDocument($xmlfiles,$splitdir);
  if ($err) $action->exiterror($err);
		      
print "Split OK in $splitdir";
$tlog=array();
if ($handle = opendir($splitdir)) {
    while (false !== ($file = readdir($handle))) {
      if ($file[0]!=".") {
          $err=importXmlDocument($dbaccess,"$splitdir/$file",$log);
          $tlog[]=$log;
          if ($err) {
              print_r2("$splitdir/$file : $err");
          }
      }
      
    }
  }
  print_r2($tlog);
}


function importXmlDocument($dbaccess,$xmlfile,&$log) {
    static $families=array();
    print "import xml $xmlfile\n";
    if (! is_file($xmlfile)) {
        $err=sprintf(_("Xml import file %s not found"),$xmlfile);
        $log["err"]=$err;
        return $err;
    }
    $importdirid=0;
    $analyze=true;
    $policy="update";
    $tkey=array("title");
    $prevalues=array();
    $dom = new DOMDocument();
    try {
        $ok=@$dom->load($xmlfile);

        if (! $ok) {
            throw new XMLParseErrorException($xmlfile);
        }
    } catch (Exception $e) {
        $log["err"]=$e->userInfo;
        return $e->userInfo;
    }
       // print $doc->saveXML();
    $root=$dom->documentElement;
    $id=$root->getAttribute("id");
    $name=$root->getAttribute("name");
    $family=$root->tagName;
    $famid=getFamIdFromName($dbaccess,$family);
    if (! isset($families[$famid])) {
        $families[$famid]=new_doc($dbaccess, $famid);
    }
    print("family : $family $id $name $famid\n");
    $la=$families[$famid]->getNormalAttributes();
    $tord=array();
    $tdoc=array("DOC",$famid,($id)?$id:$name,'-');
    foreach ($la as $k=>&$v) {
        $n=$dom->getElementsByTagName($v->id);
        foreach($n as $item) {
            
            $tord[]=$v->id;
            switch ($v->type) {
                case 'docid':
                    $tdoc[]=$item->getAttribute("id");
                    break;
                default:
                    $tdoc[]=$item->nodeValue;
            }
             print $v->id.":".$item->nodeValue."\n";
        }
    }
    print_r2($tord);
    print_r2($tdoc);
        
    $log= csvAddDoc($dbaccess, $tdoc, $importdirid,$analyze,'',$policy,
                    $tkey,$prevalues,$tord);
            
}


function splitXmlDocument($xmlfiles,$splitdir) {
    $f=fopen($xmlfiles,"r");
    if (!$f) return sprintf(_("Xml import : Cannot open file %s"),$xmlfiles);
    // find first document
    $findfirst=false;
    while ((!feof($f))&& (!$findfirst)) {
        $buffer = fgets($f, 4096);
        if (strpos($buffer,"<documents")!==false) {
            $findfirst=true;
        }
        echo "header:$buffer";
    }

    while (!feof($f)) {
        $buffer = fgets($f, 4096);
        if (preg_match("/<([a-z-_0-9]+)/",$buffer,$reg)) {
            //print_r2($reg);
            $top=$reg[1];
            if (preg_match("/name=[\"|']([a-z-_0-9]+)[\"|']/",$buffer,$reg)) {
                $fname=$reg[1];
            } else if (preg_match("/id=[\"|']([0-9]+)[\"|']/",$buffer,$reg)) {
                $fname=$reg[1];
            } else {
                $fname=uniqid("new");
            }
            $fxo=$splitdir.'/'.$fname.".xml";
            $xo=fopen($fxo,"w");
            if (! $xo) return sprintf(_("Xml import : Cannot create file %s"),$fxo);
            fputs($xo,'<?xml version="1.0" encoding="UTF-8"?>'."\n");
            fputs($xo,$buffer);
            $theend=false;
            while (!feof($f) && (! $theend)) {
                $buffer = fgets($f, 4096);
                if (strpos($buffer,'</'.$top.'>')!==false) {
                    $theend=true;
                }
                fputs($xo,$buffer);
            }
            fclose($xo);
        }
        if (strpos($buffer,"<documents")!==false) {
            $findfirst=true;
        }
        //echo $buffer;
    }
    
    fclose($f);
    
}
class XMLParseErrorException extends Exception { 
     
    public function __construct($filename) { 
        set_error_handler(array($this,"errorHandler")); 
        $dom = new DomDocument(); 
        $dom->load($filename); 
        restore_error_handler(); 
        $this->message = "XML Parse Error in $filename"; 
        parent::__construct(); 
    } 
     
    public function errorHandler($errno, $errstr, $errfile, $errline) { 
        $pos = strpos($errstr,"]:") ; 
        if ($pos) { 
            $errstr = substr($errstr,$pos+ 2); 
        } 
        $this->userInfo .="$errstr"; 
    } 
} 
?>
