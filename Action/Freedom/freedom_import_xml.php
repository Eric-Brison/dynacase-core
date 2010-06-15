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

/**
 * export global xml file
 * @param Action $action main action
 * @param string $filename xml filename to import
 */
function freedom_import_xml(Action &$action, $filename="") {

    $opt["analyze"]=(substr(strtolower(getHttpVars("analyze","Y")),0,1)=="y");
    $opt["policy"]=getHttpVars("policy","update");
    $dbaccess=$action->getParam("FREEDOM_DB");
    global $_FILES;
    if (intval(ini_get("max_execution_time")) < 300) ini_set("max_execution_time", 300);
    if ($filename=="") {
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
    } else {
            $xmlfiles=$filename;
    }
    $splitdir=uniqid("/var/tmp/xmlsplit");
    @mkdir($splitdir);
    if (! is_dir($splitdir)) $action->exitError(_("Cannot create directory %s for xml import"),$splitdir);
    $err=splitXmlDocument($xmlfiles,$splitdir);
    if ($err) $action->exiterror($err);

    //print "Split OK in $splitdir";
    $err=extractFilesFromXmlDirectory($splitdir);
    if ($err) $action->exiterror($err);
    
    $log= importXmlDirectory($dbaccess,$splitdir,$opt);
    system(sprintf("/bin/rm -fr %s ",$splitdir));
    //print "look : $splitdir\n";
    return $log;
}


/**
 * read a directory to import all xml files
 * @param string $splitdir
 * @param array options analyze (boolean) , policy (string)
 */
function importXmlDirectory($dbaccess,$splitdir,$opt) {
    $tlog=array();
    if ($handle = opendir($splitdir)) {
        while (false !== ($file = readdir($handle))) {
            if ($file[0]!="." && is_file("$splitdir/$file")) {
                $err=importXmlDocument($dbaccess,"$splitdir/$file",$log,$opt);
                $tlog[]=$log;               
            }
        }
    }
    return $tlog;
}

/**
 * read a directory to extract all encoded files
 * @param $splitdir
 */
function extractFilesFromXmlDirectory($splitdir) {
    $err='';
    if ($handle = opendir($splitdir)) {
        while (false !== ($file = readdir($handle))) {
            if ($file[0]!=".") {
                $err.=extractFileFromXmlDocument("$splitdir/$file");
            }
        }
    }
    return $err;
}
/**
 * extract encoded base 64 file from xml and put it in local media directory
 * the file is rewrite without encoded data and replace by href attribute
 * @param $file
 * @return string error message empty if no errors
 */
function extractFileFromXmlDocument($file) {
    $err='';
    $dir=dirname($file);
    if (! file_exists($file)) return sprintf(_("import Xml extract : file %s not found"),$file);
    $mediadir="media";
    if (!is_dir("$dir/$mediadir")) mkdir ("$dir/$mediadir");
    $f=fopen($file,"r");
    $nf=fopen($file.".new","w");
    $i=0;
    while (!feof($f)) {
        $buffer = fgets($f, 4096);
        $i++;
        if (preg_match("/<([a-z_0-9-]+)[^>]*mime=[^>]*(.)>(.*)/",$buffer,$reg)) {
            //print_r2($reg);
            if ($reg[2]!="/") { // not empty tag
                $tag=$reg[1];
                if (preg_match("/<([a-z_0-9-]+)[^>]*title=\"([^\"]*)\"/",$buffer,$regtitle)) {
                    $title=$regtitle[2];
                } else if (preg_match("/<([a-z_0-9-]+)[^>]*title='([^']*)'/",$buffer,$regtitle)) {
                    $title=$regtitle[2];
                }
                mkdir(sprintf("%s/%s/%d",$dir,$mediadir,$i));
                $rfin=sprintf("%s/%d/%s",$mediadir,$i,$title);
                $fin=sprintf("%s/%s",$dir,$rfin);
                $fi=fopen($fin,"w");
                
                if (preg_match("/(.*)>/",$buffer,$regend)) {
                    fputs($nf,$regend[1].' href="'.$rfin.'">');
                }
                if (preg_match("/>(.*)<\/$tag>(.*)/",$buffer,$regend)) {
                    // end of file
                    fputs($fi,$regend[1]);
                    fputs($nf,"</$tag>");
                    fputs($nf,$regend[2]);
                } else {
                    // find end of file
                    fputs($fi,$reg[3]);
                    $findtheend=false;
                    while (!feof($f) && (! $findtheend)) {
                        $buffer = fgets($f, 4096);
                        if (preg_match("/(.*)<\/$tag>(.*)/",$buffer,$regend)) {
                            fputs($fi,$regend[1]);
                            fputs($nf,"</$tag>");
                            fputs($nf,$regend[2]);
                            $findtheend=true;
                        } else {
                            fputs($fi,$buffer);
                        }
                    }
                }
                fclose($fi);
                base64_decodefile($fin);
            } else {
            fputs($nf,$buffer);
        }
        } else {
            fputs($nf,$buffer);
        }
    }
    fclose($f);
    fclose($nf);
    rename($file.".new",$file);
    return $err;
}

function importXmlDocument($dbaccess,$xmlfile,&$log,$opt) {
    static $families=array();
    $log=array("err"=>"",
             "msg"=>"",
             "specmsg"=>"",
             "folderid"=>0,
             "foldername"=>"",
             "filename"=>"",
             "title"=>"",
             "id"=>"",
             "values"=>array(),
             "familyid"=>0,
             "familyname"=>"",
             "action"=>"-");
    
    if (! is_file($xmlfile)) {
        $err=sprintf(_("Xml import file %s not found"),$xmlfile);
        $log["err"]=$err;
        return $err;
    }
    $importdirid=0;
    $analyze=true;
    $policy="update";
    if ($opt["policy"]) $policy=$opt["policy"];
    if ($opt["analyze"]!==null) $analyze=$opt["analyze"];
    $splitdir=dirname($xmlfile);
    $tkey=array("title");
    $prevalues=array();
    $dom = new DOMDocument();
    try {
        $ok=@$dom->load($xmlfile);

        if (! $ok) {
            throw new XMLParseErrorException($xmlfile);
        }
    } catch (Exception $e) {
        $log["action"]='ignored';
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
    //print("family : $family $id $name $famid\n");
    $la=$families[$famid]->getNormalAttributes();
    $tord=array();
    $tdoc=array("DOC",$famid,($id)?$id:$name,'-');
    foreach ($la as $k=>&$v) {
        $n=$dom->getElementsByTagName($v->id);
        $val=array();                
        foreach($n as $item) {   
            switch ($v->type) {
                case 'docid':
                    $id=$item->getAttribute("id");
                    if (! $id) {
                        $name=$item->getAttribute("name");
                        if ($name) $id=getIdFromName($dbaccess,$name);
                    }
                    $val[]=$id;
                    break;
                case 'image':
                case 'file':
                    $href=$item->getAttribute("href");
                    if ($href) {
                        $val[]=$href;
                    } else {
                      $vid=$item->getAttribute("vid");
                      $mime=$item->getAttribute("mime");
                      $title=$item->getAttribute("title");
                      if ($vid) {
                         $val[]="$mime|$vid|$title";
                      } else $val[]='';
                    }
                    break;
                   // print_r2($val);
                default:
                    $val[]=$item->nodeValue;
            }
           //  print $v->id.":".$item->nodeValue."\n";
        }      
            $tord[]=$v->id;
        $tdoc[]=implode("\n",$val);
    }
   
    $log= csvAddDoc($dbaccess, $tdoc, $importdirid,$analyze,$splitdir,$policy,
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
    }
    fclose($f);
}

function base64_decodefile($filename) {
    $dir=dirname($filename);
    $tmpdest=uniqid("/var/tmp/fdlbin");
    $chunkSize = 1024*30;
    $src = fopen($filename, 'rb');
    $dst = fopen($tmpdest, 'wb');
    while (!feof($src)) {
        fwrite($dst, base64_decode(fread($src, $chunkSize)));
    }
    fclose($dst);
    fclose($src);
    rename($tmpdest,$filename);
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
