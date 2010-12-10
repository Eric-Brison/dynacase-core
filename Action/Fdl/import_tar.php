<?php
/**
 * Import Set of documents and files with directories
 *
 * @author Anakeen 2000 
 * @version $Id: import_tar.php,v 1.8 2007/08/02 15:34:12 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */




include_once("FDL/import_file.php");

define("TARUPLOAD",getTmpDir()."/upload/");
define("TAREXTRACT","/extract/");
define("TARTARS","/tars/");


function getTarUploadDir(&$action) {
  $dtar = $action->getParam("FREEDOM_UPLOADDIR");
  if ($dtar=="") $dtar=TARUPLOAD;
  return $dtar."/".$action->user->login.TARTARS;
}
function getTarExtractDir(&$action,$tar) {
  $dtar = $action->getParam("FREEDOM_UPLOADDIR");
  if ($dtar=="") $dtar=TARUPLOAD;
  return $dtar."/".$action->user->login.TAREXTRACT.$tar."_D";
}


/**
 * import a directory files
 * @param action $action current action
 * @param string $ftar tar file
 */
function import_tar(&$action,$ftar,$dirid=0,$famid=7) {


}

/**
 * import a directory files
 * @param action $action current action
 * @param string $ldir local directory path
 */
function import_directory(&$action, $ldir,$dirid=0,$famid=7,$dfldid=2,
			  $onlycsv=false,$analyze=false) {
  // first see if fdl.csv file
  global $importedFiles;
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $tr=array();
  if (is_dir($ldir)) {
    if ($handle = opendir($ldir)) {
      $lfamid=0;
      while (false !== ($file = readdir($handle))) {
	$absfile="$ldir/$file";
	$absfile=str_replace("//","/","$ldir/$file");
     
	if (is_file($absfile) && ($file=="fdl.csv")) {
	  $tr = analyze_csv($absfile,$dbaccess,$dirid,$lfamid,$lfldid,$analyze);
	
	}
      }
      if ($lfamid > 0) $famid=$lfamid; // set local default family identificator
      if ($lfldid > 0) $dfldid=$lfldid; // set local default family folder identificator

      rewinddir($handle);
   
      /* This is the correct way to loop over the directory. */
      $defaultdoc= createDoc($dbaccess,$famid);
      if (! $defaultdoc) $action->AddWarningMsg(sprintf(_("you cannot create this kind [%s] of document"),$famid));
      if (($lfamid == 0) && ($famid==7)) {
	$defaultimg= createDoc($dbaccess,"IMAGE");
	$fimgattr=$defaultimg->GetFirstFileAttributes();
      }
      $newdir= createDoc($dbaccess,$dfldid);
      if (! $newdir) $action->AddWarningMsg(sprintf(_("you cannot create this kind [%s] of folder"),$dfldid));
      $ffileattr=$defaultdoc->GetFirstFileAttributes();
  
      if ($dirid > 0) {
	$dir = new_Doc($dbaccess,$dirid);
      }

      $nfile=0;
      while (false !== ($file = readdir($handle))) {
	$nfile++;
	$absfile=str_replace("//","/","$ldir/$file");
	$level = substr_count( $absfile,"/");
	$index="f$level/$nfile";
	if (is_file($absfile)) {
	  if (!$onlycsv) { // add also unmarked files
	  
	    if (!isset($importedFiles[$absfile])) {
	      if (!isUTF8($file))    $file=utf8_encode($file);
	      if (!isUTF8($ldir))    $ldir=utf8_encode($ldir);
	      $tr[$index]=array("err"=>($defaultdoc)?"":sprintf(_("you cannot create this kind [%s] of document"),$famid),
				"folderid"=>0,
				"foldername"=>$ldir,
				"filename"=>$file,
				"title"=>"",
				"id"=>0,
				"anaclass"=>"fileclass",
				"familyid"=>$ddoc->fromid,
				"familyname"=>"",
				"action"=>"");
	      $err=AddVaultFile($dbaccess,$absfile,$analyze,$vfid);
      
	      if ($err != "") {
		$tr[$index]["err"]=$err;
	      } else {
		if (($lfamid == 0) && ($famid==7) && (substr($vfid,0,5)=="image")){
		  $ddoc=&$defaultimg;
		  $fattr=$fimgattr->id;
		} else {
		  $ddoc=&$defaultdoc;
		  $fattr=$ffileattr->id;
		}
		$tr[$index]["familyid"]=$ddoc->fromid;
		$tr[$index]["action"]=_("to be add");
		if (! $analyze) {
		  $ddoc->Init();
		  $ddoc->setValue($fattr,$vfid);
		  $err=$ddoc->Add();
		  if ($err!="") {
		    $tr[$index]["action"]=_("not added");
		  } else {
		    $tr[$index]["action"]=_("added");
		    $tr[$index]["id"]=$ddoc->id;
		    $ddoc->PostModify();
		    $ddoc->Modify();
		    if ($dirid > 0) {
		      $dir->AddFile($ddoc->id);
		    }
		  }
		}
	      }
	    }
	  }
	} else if (is_dir($absfile) && ($file[0]!='.')) {

	  if (!isUTF8($file))    $file=utf8_encode($file);
	  if (!isUTF8($ldir))    $ldir=utf8_encode($ldir);

	  if ((!$onlycsv) || (! preg_match("/^[0-9]+-.*_D$/i", $file))) {
	    $tr[$index]=array("err"=>($newdir)?"":sprintf(_("you cannot create this kind [%s] of folder"),$dfldid),
			      "folderid"=>0,
			      "foldername"=>$ldir,
			      "filename"=>$file,
			      "title"=>"",
			      "id"=>0,
			      "anaclass"=>"fldclass",
			      "familyid"=>$newdir->fromid,
			      "familyname"=>"",
			      "action"=>_("to be add"));
	  if (! $analyze) {
	    $newdir->Init();
	    $newdir->setTitle($file);
	    $err=$newdir->Add();
	    if ($err!="") {
	      $tr[$index]["action"]=_("not added");
	    } else {
	      $tr[$index]["action"]=_("added");
	      if ($dirid > 0) {
		$dir->AddFile($newdir->id);	 
	      }
	    }
	  }
	  }
	  $itr=import_directory($action, $absfile,$newdir->id,$famid,$dfldid,$onlycsv,$analyze);
	  $tr=array_merge($tr,$itr);
	}
      }

   

      closedir($handle);
      return $tr;
  
    } 
  }  else {
    $err = sprintf("cannot open local directory %s",$ldir);
    return array("err"=>$err);
  }
}

function analyze_csv($fdlcsv,$dbaccess,$dirid,&$famid,&$dfldid,$analyze) {
  $tr=array();
  $fcsv=fopen($fdlcsv,"r");
  if ($fcsv) {
    $ldir=dirname($fdlcsv);
    while ($data = fgetcsv ($fcsv, 0, ";")) {
      $nline++;
      $level = substr_count( $ldir,"/");
      $index="c$level/$nline";
      switch ($data[0]) {
	// -----------------------------------
      case "DFAMID":
	$famid =  $data[1];
	//print "\n\n change famid to $famid\n";
	break; 
	// -----------------------------------
      case "DFLDID":
	$dfldid =  $data[1];
	//print "\n\n change dfldid to $dfldid\n";
	break; 
      case "ORDER":  
	if (is_numeric($data[1]))   $orfromid = $data[1];
	else $orfromid = getFamIdFromName($dbaccess,$data[1]);
      
	$tcolorder[$orfromid]=getOrder($data);
	$tr[$index]["action"]=sprintf(_("new column order %s"),implode(" - ",$tcolorder[$orfromid]));      
	break;
      case "KEYS":  
	if (is_numeric($data[1]))   $orfromid = $data[1];
	else $orfromid = getFamIdFromName($dbaccess,$data[1]);
      
	$tkeys[$orfromid]=getOrder($data); 
	if (($tkeys[$orfromid][0]=="") || (count($tkeys[$orfromid])==0)) {	
	  $tr[$index]["err"]=sprintf(_("error in import keys : %s"),implode(" - ",$tkeys[$orfromid]));
	  unset($tkeys[$orfromid]);
	  $tr[$index]["action"]="ignored";
	} else {
	  $tr[$index]["action"]=sprintf(_("new import keys : %s"),implode(" - ",$tkeys[$orfromid]));
	}
	break;
      case "DOC":
	if (is_numeric($data[1]))   $fromid = $data[1];
	else $fromid = getFamIdFromName($dbaccess,$data[1]);
	if (isset($tkeys[$fromid])) $tk=$tkeys[$fromid];
	else $tk=array("title");
	$tr[$index]=csvAddDoc($dbaccess, $data, $dirid,$analyze,$ldir,"update",
			      $tk,array(),$tcolorder[$fromid]);
	if ($tr[$index]["err"]=="") $nbdoc++;
	if ($tr[$index]["action"]!="") $tr[$index]["action"]=_($tr[$index]["action"]);	 
	break;    
      }
    }
    fclose($fcsv);
  }
  return $tr;
}
/**
 * decode characters wihich comes from windows zip
 * @param $s string to decode
 * @return string decoded string
 */
function WNGBdecode($s) {
  $td=array(144=>"É",
	    130=>"é",
	    133=>"à",
	    135=>"ç",
	    138=>"è",
	    151=>"ù",
	    212=>"È",
	    210=>"Ê",
	    128=>"Ç",
	    183=>"ê",
	    136=>"û",
	    183=>"À",
	    136=>"ê",
	    150=>"û",
	    147=>"ô",
	    137=>"ë",
	    139=>"ï");

  $s2=$s;
  for ($i=0;$i<strlen($s);$i++) {
    if (isset($td[ord($s[$i])]))  $s2[$i]=$td[ord($s[$i])];
      
  }
  return $s2;
}

/**
 * rename file name which comes from windows zip
 * @param $ldir directory to decode
 * @return void
 */
function WNGBDirRename($ldir) {
  $handle=opendir($ldir);
  while (false !== ($file = readdir($handle))) {
   if ($file[0] != ".") {
     $afile="$ldir/$file";

     if (is_file($afile)) {
       rename($afile,"$ldir/".WNGBdecode($file));
     } else if (is_dir($afile)) {
       WNGBDirRename($afile);
     }
   }
 }
 
 closedir($handle);
 rename($ldir,WNGBdecode($ldir)); 
}
?>
