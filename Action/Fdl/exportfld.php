<?php
/**
 * Export Document from Folder
 *
 * @author Anakeen 2003
 * @version $Id: exportfld.php,v 1.44 2009/01/12 13:23:11 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Lib.Dir.php");
include_once("FDL/Lib.Util.php");
include_once("FDL/Class.DocAttr.php");
include_once("VAULT/Class.VaultFile.php");
include_once("FDL/import_file.php");
/**
 * Exportation of documents from folder or searches
 * @param Action &$action current action
 * @global fldid Http var : folder identificator to export
 * @global wprof Http var : (Y|N) if Y export associated profil also
 * @global wfile Http var : (Y|N) if Y export attached file export format will be tgz
 * @global wident Http var : (Y|N) if Y specid column is set with identificator of document
 * @global wutf8 Http var : (Y|N) if Y encoding is utf-8 else iso8859-1
 * @global wcolumn Http var :  if - export preferences are ignored
 * @global eformat Http var :  (I|R|F) I: for reimport, R: Raw data, F: Formatted data
 * @global selection Http var :  JSON document selection object
 */
function exportfld(Action &$action, $aflid="0", $famid="") {
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $fldid = GetHttpVars("id",$aflid);
  $wprof = (GetHttpVars("wprof","N")=="Y"); // with profil
  $wfile = (GetHttpVars("wfile","N")=="Y"); // with files
  $wident = (GetHttpVars("wident","Y")=="Y"); // with numeric identificator
  $wutf8 = (GetHttpVars("code","utf8")=="utf8"); // with numeric identificator
  $nopref = (GetHttpVars("wcolumn")=="-"); // no preference read
  $eformat = GetHttpVars("eformat","I"); // export format 
  $selection = GetHttpVars("selection"); // export selection  object (JSON)

  if ($eformat == "X") {
      // XML redirect
      include_once("FDL/exportxmlfld.php");
      return exportxmlfld($action,$aflid,$famid);
      
  }

  if ((! $fldid) && $selection) {
      $selection=json_decode($selection);
      include_once("DATA/Class.DocumentSelection.php");
      include_once("FDL/Class.SearchDoc.php");
      $os=new Fdl_DocumentSelection($selection);
      $ids=$os->getIdentificators();
      $s=new SearchDoc($dbaccess);
      $s->addFilter(getSqlCond($ids,"id",true));
      $tdoc=$s->search();
      $fname="selection";
       
  } else {
      if (! $fldid) $action->exitError(_("no export folder specified"));

      $fld = new_Doc($dbaccess, $fldid);
      if ($famid=="") $famid=GetHttpVars("famid");
      $fname=str_replace(array(" ","'"),array("_",""),$fld->title);
      $tdoc = getChildDoc($dbaccess, $fldid,"0","ALL",array(),$action->user->id,"TABLE",$famid);
  }
  usort($tdoc,"orderbyfromid");


  if ($wfile) {
    $foutdir=uniqid(getTmpDir()."/exportfld");
    if (! mkdir($foutdir)) exit();
    
    $foutname = $foutdir."/fdl.csv";
  } else {
    $foutname = uniqid(getTmpDir()."/exportfld").".csv";
  }
  $fout = fopen($foutname,"w");
  // set encoding
  if (!$wutf8) fputs_utf8($fout,"",true);
 
  
  if (count($tdoc) > 0) {

    
    $send="\n"; // string to be writed in last
    
    $doc = createDoc($dbaccess,0);

    // compose the csv file
    reset($tdoc);
    
    $ef=array(); //   files to export
    $tmoredoc=array();
    foreach ($tdoc as $k=>$zdoc) {
      if (! is_array($zdoc)) continue;
      if ($zdoc["doctype"]=="C")   {
	$wname="";
	$cvname="";
	$cpname="";
	$fpname="";
	$doc->Affect($zdoc,true);
	// it is a family
	if ($wprof) {
	  if ($doc->profid != $doc->id) {
	    $fp=getTDoc($dbaccess,$doc->profid);
	    $tmoredoc[$fp["id"]]=$fp;
	    if ($fp["name"]!="") $fpname=$fp["name"];
	    else $fpname=$fp["id"];
	  } else {
	    exportProfil($fout,$dbaccess,$doc->profid);
	  }
	  if ($doc->cprofid) {
	    $cp=getTDoc($dbaccess,$doc->cprofid);
	    if ($cp["name"]!="") $cpname=$cp["name"];
	    else $cpname=$cp["id"];
	    $tmoredoc[$cp["id"]]=$cp;
	  }
	  if ($doc->ccvid > 0) {
	    $cv=getTDoc($dbaccess,$doc->ccvid);
	    if ($cv["name"]!="") $cvname=$cv["name"];
	    else $cvname=$cv["id"];
	    $tmskid=$doc->_val2array($cv["cv_mskid"]);

	    foreach ($tmskid as $kmsk=>$imsk) {
	      if ($imsk != "") {
		$msk=getTDoc($dbaccess,$imsk);
		if ($msk) $tmoredoc[$msk["id"]]=$msk;
	      }
	    }
	    
	    $tmoredoc[$cv["id"]]=$cv;
	  }
	  
	  if ($doc->wid > 0) {
	      $wdoc=new_doc($dbaccess,$doc->wid);
	      if ($wdoc->name!="") $wname=$wdoc->name;
	      else $wname=$wdoc->id;
	      $tattr=$wdoc->getAttributes();
	      foreach ($tattr as $ka=>$oa) {
	          if ($oa->type=="docid") {
	              $tdid=$wdoc->getTValue($ka);
	              foreach ($tdid as $did) {
	                  if ($did != "") {
	                      $m=getTDoc($dbaccess,$did);
	                      if ($m) {
	                          $tmoredoc[$m["id"]]=$m;
	                          if ($m["cv_mskid"]!='') {
	                              $tmskid=$doc->_val2array($m["cv_mskid"]);
	                              foreach ($tmskid as $kmsk=>$imsk) {
	                                  if ($imsk != "") {
	                                      $msk=getTDoc($dbaccess,$imsk);
	                                      if ($msk) $tmoredoc[$msk["id"]]=$msk;
	                                  }
	                              }
	                          }
	                          if ($m["tm_tmail"]!='') {
	                              $tmskid=$doc->_val2array(str_replace('<BR>',"\n",$m["tm_tmail"]));
	                              foreach ($tmskid as $kmsk=>$imsk) {
	                                  if ($imsk != "") {
	                                      $msk=getTDoc($dbaccess,$imsk);
	                                      if ($msk) $tmoredoc[$msk["id"]]=$msk;
	                                  }
	                              }
	                          }
	                      }
	                  }
	              }
	          }
	      }
	      $tmoredoc[$doc->wid]=getTDoc($dbaccess,$doc->wid);
	      	      
	  }
	  if ($cvname || $wname || $cpname || $fpname) {
	    $send.="BEGIN;;;;;".$doc->name."\n";
	    if ($fpname) $send.="PROFID;".$fpname."\n";
	    if ($cvname) $send.="CVID;".$cvname."\n";
	    if ($wname) $send.="WID;".$wname."\n";
	    if ($doc->cprofid) $send.="CPROFID;".$cpname."\n";
	    $send.="END;\n";
	  }
	}
      }
    }
    
    $tdoc=array_merge($tdoc,$tmoredoc);
    $cachedoc=array();
    foreach ($tdoc as $k=>$zdoc) {
      if ($cachedoc[$zdoc["fromid"]]) $doc=$cachedoc[$zdoc["fromid"]];
      else {
	$cachedoc[$zdoc["fromid"]]=createDoc($dbaccess,$zdoc["fromid"],false);
	$doc=$cachedoc[$zdoc["fromid"]];
      }

      $doc->Affect($zdoc,true);

      if ($doc->doctype!="C")  {
	exportonedoc($doc,$ef,$fout,$wprof,$wfile,$wident,$wutf8,$nopref,$eformat);
      }
    }
    fputs_utf8($fout,$send);
  }
  fclose($fout);
  if ($wfile) {
    foreach ($ef as $info) {
      $source=$info["path"];
      $ddir=$foutdir.'/'.$info["ldir"];
      if (! is_dir($ddir)) mkdir($ddir);
      $dest=$ddir.'/'.$info["fname"];
      if (!@copy($source,$dest )) $err.=sprintf(_("cannot copy %s"),$dest);
      
    }
    if ($err) $action->addWarningMsg($err);
    system(sprintf("cd %s && zip -r fdl * > /dev/null", escapeshellarg($foutdir)), $ret);
    if (is_file("$foutdir/fdl.zip")) {
      $foutname=$foutdir."/fdl.zip";
      Http_DownloadFile($foutname, "$fname.zip", "application/x-zip",false,false);
      //if (deleteContentDirectory($foutdir)) rmdir($foutdir);

    } else {
      $action->exitError(_("Zip Archive cannot be created"));
    }
    

  } else {
    Http_DownloadFile($foutname, "$fname.csv", "text/csv",false,false);
    unlink($foutname);
  }
  exit;
}
function fputs_utf8($r,$s,$iso=false) { 
  static $utf8=true;
  if ($iso===true) $utf8=false;
  
  if ($s) {
    if (! $utf8)  fputs($r,utf8_decode($s));
    else fputs($r,$s);
    
  }
  
}
function orderbyfromid($a, $b) {
  
    if ($a["fromid"] == $b["fromid"]) return 0;
    if ($a["fromid"] > $b["fromid"]) return 1;
  
  return -1;
}

/**
 * Removes content of the directory (not sub directory)
 *
 * @param string the directory name to remove
 * @return boolean True/False whether the directory was deleted.
 */
function deleteContentDirectory($dirname) {
  if (!is_dir($dirname))
    return false;
  $dcur=realpath($dirname);
  $darr = array();
  $darr[] = $dcur;
  if ($d=opendir($dcur)) {
    while ($f=readdir($d)) {
      if ($f=='.' || $f=='..')  continue;
      $f=$dcur.'/'.$f;
      if (is_file($f)) {
	unlink($f);$darr[]=$f;
      }
    }
    closedir($d);
  }
   

  return true;;
}
function exportProfil($fout,$dbaccess,$docid) {
  if (! $docid) return;
  // import its profile
  $doc = new_Doc($dbaccess,$docid); // needed to have special acls
  $doc->acls[]="viewacl";
  $doc->acls[]="modifyacl";
  if ($doc->name != "") $name=$doc->name;
  else $name=$doc->id;
  $q= new QueryDb($dbaccess,"DocPerm");
  $q->AddQuery("docid=".$doc->profid);
  $acls=$q->Query(0,0,"TABLE");
	
  $tpu=array();
  $tpa=array();
  if ($acls) {
    foreach ($acls as $va) {
      $up=$va["upacl"];
      $un=$va["unacl"];
      $uid=$va["userid"];

      foreach ($doc->acls as $acl) {
          $bup=($doc->ControlUp($up,$acl) == "");
          $bun=($doc->ControlUp($un,$acl) == "");
          if ($bup || $bun) {
              if ($uid >= STARTIDVGROUP) {
                  $vg=new Vgroup($dbaccess,$uid);
                  $qvg=new QueryDb($dbaccess,"VGroup");
                  $qvg->AddQuery("num=$uid");
                  $tvu=$qvg->Query(0,1,"TABLE");
                  $uid=$tvu[0]["id"];
              }

              $tpu[]=$uid;
              if ($bup) $tpa[]=$acl;
              else $tpa[]="-".$acl;
	}
      }
    }
  }
  if (count($tpu) > 0) {
    fputs_utf8($fout,"PROFIL;".$name.";;");

    foreach ($tpu as $ku=>$uid) {
      if ($uid > 0) $uid=getUserLogicName($dbaccess,$uid);
      fputs_utf8($fout,";".$tpa[$ku]."=".$uid);
    }
    fputs_utf8($fout,"\n");
  }
}

function getUserLogicName($dbaccess,$uid) {
  $u=new User("",$uid);
  if ($u->isAffected()) {
    $du=getTDoc($dbaccess,$u->fid);
    if (($du["name"]!="")&&($du["us_whatid"]==$uid))  return $du["name"];
  }
  return $uid;
}
function exportonedoc(&$doc,&$ef,$fout,$wprof,$wfile,$wident,$wutf8,$nopref,$eformat) {
  static $prevfromid=-1;
  static $lattr;
  static $trans=false;
  static $fromname;

  if (!$doc->isAffected()) return;
  if (! $trans) {
    // to invert HTML entities
    $trans = get_html_translation_table (HTML_ENTITIES);
    $trans = array_flip($trans);
    $trans=array_map("utf8_encode",$trans);
  }
  $efldid='-';
  $dbaccess=$doc->dbaccess;
  if ($prevfromid != $doc->fromid) {
    if (($eformat!="I") && ($prevfromid>0)) fputs_utf8($fout,"\n");
    $adoc = $doc->getFamDoc();
    if ($adoc->name != "") $fromname=$adoc->name;
    else $fromname=$adoc->id;
    if (! $fromname) return;
    $lattr=$adoc->GetExportAttributes($wfile,$nopref);
    if ($eformat=="I") fputs_utf8($fout,"//FAM;".$adoc->title."(".$fromname.");<specid>;<fldid>;");
    foreach($lattr as $ka=>$attr) {
      fputs_utf8($fout,str_replace(SEPCHAR,ALTSEPCHAR,$attr->getLabel()).SEPCHAR);
    }
    fputs_utf8($fout,"\n");
    if ($eformat=="I") {
      fputs_utf8($fout,"ORDER;".$fromname.";;;");
      foreach($lattr as $ka=>$attr) {
	fputs_utf8($fout,$attr->id.";");
      }
      fputs_utf8($fout,"\n");
    }
    $prevfromid = $doc->fromid;
	
  }
  reset($lattr);
  if ($doc->name != "") $name=$doc->name;
  else if ($wprof) {
    $err=$doc->setNameAuto();
    $name=$doc->name;
  } else if ($wident) $name=$doc->id;
  else $name='';
  if ($eformat=="I") fputs_utf8($fout,"DOC;".$fromname.";".$name.";".$efldid.";");
  // write values
  foreach ($lattr as $ka=>$attr) {
    if ($eformat=='F') $value= str_replace(array('<BR>','<br/>'),'\\n',$doc->getHtmlAttrValue($attr->id,'',false,-1,false));
    else $value= $doc->getValue($attr->id);
    // invert HTML entities
    if (($attr->type=="image") || ($attr->type=="file")) {
      $tfiles=$doc->vault_properties($attr);
      $tf=array();
      foreach ($tfiles as $f) {
	$ldir=$doc->id.'-'.preg_replace('/[^a-zA-Z0-9_.-]/', '_', unaccent($doc->title))."_D";
	$fname=$ldir.'/'.unaccent($f["name"]);
	$tf[]=$fname;
	$ef[$fname]=array("path"=>$f["path"],
			  "ldir"=>$ldir,
			  "fname"=>unaccent($f["name"]));
      }
      $value=implode("\n",$tf);
    } else if ($attr->type=="docid") {
      if ($value != "") {
	if (strstr($value,"\n") || ($attr->getOption("multiple")=="yes") ) {
	  $tid=$doc->_val2array($value);
	  $tn=array();
	  foreach ($tid as $did) {
	    $brtid=explode("<BR>",$did);
	    $tnbr=array();
	    foreach ($brtid as $brid) {
	      $n=getNameFromId($dbaccess,$brid);
	      if ($n) $tnbr[]=$n;
	      else $tnbr[]=$brid;		
	    }
	    $tn[]=implode('<BR>',$tnbr);
	  }
	  $value=implode("\n",$tn);
	} else {
	  $n=getNameFromId($dbaccess,$value);
	  if ($n) $value=$n;
	}
      }
    } else {
      $value = preg_replace("/(\&[a-zA-Z0-9\#]+;)/es", "strtr('\\1',\$trans)", $value);
 
      // invert HTML entities which ascii code like &#232;

      $value = preg_replace("/\&#([0-9]+);/es", "chr('\\1')", $value);

    }
    fputs_utf8($fout,str_replace(array("\n",";","\r"),
				 array("\\n",ALTSEPCHAR,""),
				 $value) .";");
     
  }
  fputs_utf8($fout,"\n");

  if ($wprof) {
    if ($doc->profid == $doc->id) exportProfil($fout,$dbaccess,$doc->id);
    else if ($doc->profid > 0) {
      $name=getNameFromId($dbaccess,$doc->profid);
      $dname=$doc->name;
      if (! $dname) $dname=$doc->id;
      if (! $name) $name=$doc->profid;
      if (! isset($tdoc[$doc->profid])) {
	  $tdoc[$doc->profid]=true;
	  $pdoc=new_doc($dbaccess,$doc->profid);
	  exportonedoc($pdoc,$ef,$fout,$wprof,$wfile,$wident,$wutf8,$nopref,$eformat);
	  //	  exportProfil($fout,$dbaccess,$doc->profid);
      }
      fputs_utf8($fout,"PROFIL;$dname;$name;;\n");

      
    }
  }
}

?>
