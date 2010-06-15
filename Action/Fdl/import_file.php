<?php
/**
 * Import documents
 *
 * @author Anakeen 2000 
 * @version $Id: import_file.php,v 1.149 2008/11/14 12:40:07 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.DocFam.php");
include_once("FDL/Class.DocSearch.php");
include_once("FDL/Class.Dir.php");
include_once("FDL/Class.QueryDir.php");
include_once("FDL/Lib.Attr.php");
include_once("FDL/Class.DocAttrLDAP.php");

define ("ALTSEPCHAR", ' --- ');
define ("SEPCHAR", ';');


function add_import_file(&$action, $fimport) {
  // -----------------------------------
  $gerr=""; // general errors 
  if (intval(ini_get("max_execution_time")) < 300) ini_set("max_execution_time", 300);
  $dirid = GetHttpVars("dirid",10); // directory to place imported doc 
  $analyze = (GetHttpVars("analyze","N")=="Y"); // just analyze
  $policy = GetHttpVars("policy","update"); 
  $reinit = GetHttpVars("reinitattr"); 
  $comma = GetHttpVars("comma", SEPCHAR); 

  $nbdoc=0; // number of imported document
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $cvsfile="";
  if (seemsODS($fimport)) {
    $cvsfile=ods2csv($fimport);
    $fdoc = fopen($cvsfile,"r");
  } else {
    $fdoc = fopen($fimport,"r");
  }
  if (! $fdoc) $action->exitError(_("no import file specified"));
  $nline=0;
  while (!feof($fdoc)) { 
    $buffer = rtrim(fgets($fdoc, 16384));
    $data=explode($comma,$buffer);
    $nline++;

    if (! isUTF8($data))    $data=array_map("utf8_encode",$data);
  // return structure
    $num = count ($data);
    if ($num < 1) continue;
    $tcr[$nline]=array("err"=>"",
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
	     "action"=>" ");
    $tcr[$nline]["title"]=substr($data[0],0,10);
    $data[0]=trim($data[0]);
    switch ($data[0]) {
      // -----------------------------------
    case "BEGIN":
      $err="";	
      // search from name or from id

      if (($data[3]=="") || ($data[3]=="-"))$doc=new DocFam($dbaccess,getFamIdFromName($dbaccess,$data[5]),'',0,false );
      else $doc=new DocFam($dbaccess, $data[3],'',0,false );
      $famicon="";
     
      if (! $doc->isAffected())  {
	
	if (! $analyze) {
	  $doc  =new DocFam($dbaccess);

	  if (isset($data[3]) && ($data[3] > 0)) $doc->id= $data[3]; // static id
	  if (is_numeric($data[1]))   $doc->fromid = $data[1];
	  else $doc->fromid = getFamIdFromName($dbaccess,$data[1]);
	  if (isset($data[5])) $doc->name = $data[5]; // internal name
	  $err = $doc->Add();

	}
	$tcr[$nline]["msg"]=sprintf(_("create %s family %s"),$data[2],$data[5]);
	$tcr[$nline]["action"]="added";
      } else {
	$tcr[$nline]["action"]="updated";
	$tcr[$nline]["msg"]=sprintf(_("update %s family %s"),$data[2],$data[5]);
      }
      if ($data[1] && ($data[1]!='-')) {
	if (is_numeric($data[1]))   $doc->fromid = $data[1];
	else $doc->fromid = getFamIdFromName($dbaccess,$data[1]);
      }
      if ($data[2] && ($data[2]!='-')) $doc->title =  $data[2];  
      if ($data[4] && ($data[4]!='-')) $doc->classname = $data[4]; // new classname for familly
      if ($data[5] && ($data[5]!='-')) $doc->name = $data[5]; // internal name


      $tcr[$nline]["err"].=$err;
      if ($reinit=="yes") {     
	$tcr[$nline]["msg"].=sprintf(_("reinit all attributes"));
	if ($analyze) continue;
	$oattr=new DocAttr($dbaccess);
	$oattr->docid=intval($doc->id);
	if ($oattr->docid > 0) {
	  $err=$oattr->exec_query("delete from docattr where docid=".$oattr->docid);
	}	      
	$tcr[$nline]["err"].=$err;
      }
	  
      break;
      // -----------------------------------
    case "END":
      // add messages
      $msg=sprintf(_("modify %s family"),$doc->title);
      $tcr[$nline]["msg"]=$msg;
      
      if ($analyze) {
	$nbdoc++;
	continue;
      }
      if (($num > 3) && ($data[3] != "")) $doc->doctype = "S";


      $doc->modify();

      if (  $doc->doctype=="C") {
	global $tFamIdName;
	$msg=refreshPhpPgDoc($dbaccess, $doc->id);
	if (isset($tFamIdName))	$tFamIdName[$doc->name]=$doc->id; // refresh getFamIdFromName for multiple family import
      }
      if (isset($data[2])) {
	if  ($data[2] > 0) { // dirid
	  $dir = new_Doc($dbaccess, $data[2]);
	  if ((method_exists($dir,"AddFile")) && $dir->isAlive())   $dir->AddFile($doc->id);
	} else if ($data[2] ==  0) {
	  $dir = new_Doc($dbaccess, $dirid);
	  if ((method_exists($dir,"AddFile")) && ($dir->isAlive())) $dir->AddFile($doc->id);
	}
      }
      if ((! $analyze) && ($famicon!="")) $doc->changeIcon($famicon);
      
      $doc->AddComment(_("Update by importation"));
     
      $nbdoc++;


      break;
      // -----------------------------------
    case "DOC":
      // case of specific order
      if (is_numeric($data[1]))   $fromid = $data[1];
      else $fromid = getFamIdFromName($dbaccess,$data[1]);

      if (isset($tkeys[$fromid])) $tk=$tkeys[$fromid];
      else $tk=array("title");

      $tcr[$nline]=csvAddDoc($dbaccess, $data, $dirid,$analyze,
			     '',$policy,$tk,array(),$tcolorder[$fromid]);
      if ($tcr[$nline]["err"]=="") $nbdoc++;
      break;    
      // -----------------------------------
    case "SEARCH":

      if  ($data[1] > 0) {
	$tcr[$nline]["id"]=$data[1];
	$doc = new_Doc($dbaccess, $data[1]);
	if (! $doc -> isAffected()) {
	  $doc = createDoc($dbaccess,5);
	  if (!$analyze) {
	    $doc->id= $data[1]; // static id
	    $err = $doc->Add();
	  }
	  $tcr[$nline]["msg"]=sprintf(_("update %s search"),$data[3]);
	  $tcr[$nline]["action"]="updated";
	}
      } else {
	$doc = createDoc($dbaccess,5);
	if (!$analyze) {
	  $err = $doc->Add();
	}
	$tcr[$nline]["msg"]=sprintf(_("add %s search"),$data[3]);
	$tcr[$nline]["action"]="added";
	$tcr[$nline]["err"].=$err;
      }
      if (($err != "") && ($doc->id > 0)) { // case only modify
	if ($doc -> Select($doc->id)) $err = "";
      }
      if (!$analyze) {
	// update title in finish
	$doc->title =  $data[3];
	$err=$doc->modify();
	$tcr[$nline]["err"].=$err;

	if (($data[4] != "")) { // specific search 
	  $err = $doc->AddStaticQuery($data[4]);
	  $tcr[$nline]["err"].=$err;
	}

	if ($data[2] > 0) { // dirid
	  $dir = new_Doc($dbaccess, $data[2]);
	  if ($dir->isAlive() && method_exists($dir,"AddFile")) $dir->AddFile($doc->id);
	} 
      }
      $nbdoc++;
      break;
      // -----------------------------------
    case "TYPE":
      $doc->doctype =  $data[1];
      $tcr[$nline]["msg"]=sprintf(_("set doctype to '%s'"),$data[1]);
      break;
      // -----------------------------------
    case "GENVERSION":
      $doc->genversion =  $data[1];
      $tcr[$nline]["msg"]=sprintf(_("generate version '%s'"),$data[1]);
      break;
      // -----------------------------------
    case "MAXREV":
      $doc->maxrev =  intval($data[1]);
      $tcr[$nline]["msg"]=sprintf(_("max revision '%d'"),$data[1]);
      break;
      // -----------------------------------
    case "ICON": // for family
      if ($doc->icon == "") {
	$famicon=$data[1]; // reported to end section
	
	$tcr[$nline]["msg"]=sprintf(_("set icon to '%s'"),$data[1]);
      } else {
	$tcr[$nline]["err"]=sprintf(_("icon already set. No update allowed"));
      }
      break;
      // -----------------------------------
    case "DOCICON":
      $idoc=new_doc($dbaccess,$data[1]);
      if (! $analyze) $idoc->changeIcon($data[2]);	
      if ($idoc->isAlive())   $tcr[$nline]["msg"]=sprintf(_("document %s : set icon to '%s'"),$idoc->title,$data[2]);
      else $tcr[$nline]["err"]=sprintf(_("no change icon : document %s not found"),$data[1]);
      
      break;
      // -----------------------------------
    case "DFLDID":
      if ($data[1] == "auto") {
	if ($doc->dfldid == "") {
	  if (! $analyze) {
	    // create auto
	    include_once("FDL/freedom_util.php");
	    $fldid=createAutoFolder($doc);
	    $tcr[$nline]["msg"].=sprintf(_("create default folder (id [%d])\n"),$fldid);
	  }
	} else {
	  $fldid=$doc->dfldid;
	  $tcr[$nline]["err"]=sprintf(_("default folder already set. Auto ignored"));
	}
      } elseif (is_numeric($data[1]))   $fldid = $data[1];
      else $fldid =  getIdFromName($dbaccess,$data[1],2);
      $doc->dfldid =  $fldid;
      $tcr[$nline]["msg"].=sprintf(_("set default folder to '%s'"),$data[1]);
      break;
      // -----------------------------------
    case "CFLDID":
      if (is_numeric($data[1]))   $cfldid = $data[1];
      else $cfldid =  getIdFromName($dbaccess,$data[1]);
      $doc->cfldid =  $cfldid;
      $tcr[$nline]["msg"]=sprintf(_("set primary folder to '%s'"),$data[1]);
      break;
      // -----------------------------------
    case "WID":
      if (is_numeric($data[1]))   $wid = $data[1];
      else $wid =  getIdFromName($dbaccess,$data[1],20);
      $doc->wid =  $wid;
      $tcr[$nline]["msg"]=sprintf(_("set default workflow to '%s'"),$data[1]);
      break;
      // -----------------------------------
    case "CVID":
      if (is_numeric($data[1]))   $cvid = $data[1];
      else $cvid =  getIdFromName($dbaccess,$data[1],28);
      $doc->ccvid =  $cvid;
      $tcr[$nline]["msg"]=sprintf(_("set view control to '%s'"),$data[1]);
      break;
      // -----------------------------------
    case "SCHAR":
      $doc->schar =  $data[1];
      $tcr[$nline]["msg"]=sprintf(_("set special characteristics to '%s'"),$data[1]);
      break;
      // -----------------------------------
    case "METHOD":

      $s1=$data[1][0];
      if (($s1=="+")||($s1=="*")) {
	if ($s1=="*") $method=$data[1];
	else $method=substr($data[1],1);

	if ($doc->methods == "") {
	  $doc->methods =  $method;
	} else {
	  $doc->methods .= "\n$method";
	  // not twice
	  $tmeth = explode("\n",$doc->methods);
	  $tmeth=array_unique($tmeth);
	  $doc->methods =  implode("\n",$tmeth);
	}
      } else  $doc->methods =  $data[1];
      
      $tcr[$nline]["msg"]=sprintf(_("change methods to '%s'"),$doc->methods);
      
      break;
      // -----------------------------------
    case "USEFORPROF":     
      $doc->usefor =  "P";
      $tcr[$nline]["msg"]=sprintf(_("change special use to '%s'"),$doc->usefor);
      break;
      // -----------------------------------
    case "USEFOR":     
      $doc->usefor =   $data[1];
      $tcr[$nline]["msg"]=sprintf(_("change special use to '%s'"),$doc->usefor);
      break;
      // -----------------------------------
    case "TAG":           
      $doc->AddATag($data[1]);
      $tcr[$nline]["msg"]=sprintf(_("change application tag to '%s'"),$doc->atags);
      break;
      // -----------------------------------
    case "CPROFID":     
      if (is_numeric($data[1]))   $pid = $data[1];
      else $pid =  getIdFromName($dbaccess,$data[1],3);
      $doc->cprofid =  $pid;
      $tcr[$nline]["msg"]=sprintf(_("change default creation profile id  to '%s'"),$data[1]);
      break;
      // -----------------------------------
    case "PROFID":     
      if (is_numeric($data[1]))   $pid = $data[1];
      else $pid =  getIdFromName($dbaccess,$data[1],3);
      $doc->setProfil($pid);// change profile
      $tcr[$nline]["msg"]=sprintf(_("change profile id  to '%s'"),$data[1]);
      break;
    case "DEFAULT":     
      $defv=str_replace(array('\n',ALTSEPCHAR),array("\n",SEPCHAR),$data[2]);
      $doc->setDefValue($data[1],$defv);
      $doc->setParam($data[1],$defv);

      $tcr[$nline]["msg"]=sprintf(_("add default value %s %s"),$data[1],$data[2]);
      break;
    case "IATTR":
      // import attribute definition from another family
      $fiid=$data[3];
      if (! is_numeric($fiid))    $fiid =  getFamIdFromName($dbaccess,$fiid);
      $fi=new_Doc($dbaccess,$fiid);
      if ($fi->isAffected()) {
	$fa=$fi->getAttribute($data[1]);
	if ($fa) {
	  $oattri=new DocAttr($dbaccess, array($fiid,strtolower($data[1])));
	  $oattr=new DocAttr($dbaccess, array($doc->id,strtolower($data[1])));
	  $oattri->docid=$doc->id; 
	  $tcr[$nline]["msg"]=sprintf(_("copy attribute %s from %s"),$data[1],$data[3]);
	  if (!$analyze) {
	    if ($oattr->isAffected()) {
	      $err=$oattri->modify();
	    } else {
	      $oattri->id=strtolower($data[1]);
	      $err=$oattri->add();
	    }
	    $tcr[$nline]["err"]=$err;
	  }


	  if (($err=="") && (strtolower(get_class($fa)) == "fieldsetattribute")) {
	    $frameid=$fa->id;
	    // import attributes included in fieldset
	    foreach($fi->attributes->attr as $k=>$v) {
	      if (strtolower(get_class($v)) == "normalattribute") {
		
		if (($v->fieldSet->id == $frameid) || ($v->fieldSet->fieldSet->id == $frameid)) {
		  $tcr[$nline]["msg"].="\n".sprintf(_("copy attribute %s from %s"),$v->id,$data[3]);
		  $oattri=new DocAttr($dbaccess, array($fiid,$v->id));
		  $oattr=new DocAttr($dbaccess, array($doc->id,$v->id));
		  $oattri->docid=$doc->id; 
		  if (!$analyze) {
		    if ($oattr->isAffected()) {
		      $err=$oattri->modify();
		    } else {
		      $oattri->id=$v->id;
		      $err=$oattri->add();
		    }
		    $tcr[$nline]["err"].=$err;
		  }
		}
	      }
	    }
	  }
	}
      }
      break;
      // -----------------------------------
    case "PARAM":
    case "OPTION":
    case "ATTR":
      if( $num < 9 ) {
	$tcr[$nline]["err"] = "Error in line $nline: $num cols < 9";
	break;
      }
    case "MODATTR":
      if( $num < 3 ) {
	$tcr[$nline]["err"] = "Error in line $nline: $num cols < 3";
	break;
      }
      foreach ($data as $kd=>$vd) {
	$data[$kd]=str_replace(ALTSEPCHAR,$comma,$vd); // restore ; semi-colon
      }
      if (trim($data[1])=='') {
	$tcr[$nline]["err"].=sprintf(_("attr key is empty"));
      } else {
	$modattr=($data[0]=="MODATTR");
	if ($data[0]=="MODATTR") $data[1]=':'.$data[1]; // to mark the modified attribute
	$tcr[$nline]["msg"].=sprintf(_("update %s attribute"),$data[1]);
	if ($analyze) continue;
	$oattr=new DocAttr($dbaccess, array($doc->id,strtolower($data[1])));
     
	if ($data[0]=="PARAM") $oattr->usefor='Q'; // parameters
	elseif ($data[0]=="OPTION") $oattr->usefor='O'; // options
      
	$oattr->docid = $doc->id;
	$oattr->id = trim(strtolower($data[1]));
      
	$oattr->frameid = trim(strtolower($data[2]));
	$oattr->labeltext=$data[3];

	$oattr->title = ($data[4] == "Y")?"Y":"N";

	$oattr->abstract = ($data[5] == "Y")?"Y":"N";
	if ($modattr) $oattr->abstract =$data[5];

     
	$oattr->type = trim($data[6]);

	$oattr->ordered = $data[7];
	$oattr->visibility = $data[8];
	$oattr->needed =  ($data[9]=="Y")?"Y":"N";
	if ($modattr) {
	  $oattr->title = $data[4];
	  $oattr->needed =  $data[9];
	}
	$oattr->link = $data[10];
	$oattr->phpfile = $data[11];
	if (isset($data[13])) $oattr->elink = $data[13];
	if (isset($data[14])) $oattr->phpconstraint = $data[14];
	if (isset($data[15])) $oattr->options = $data[15];
	if (((($data[11]!="")&&($data[11]!="-")) || (($data[6] != "enum")  && ($data[6] != "enumlist"))) || 
	    ($oattr->phpfunc == "") || (strpos($oattr->options,"system=yes")!==false)) $oattr->phpfunc = $data[12]; // don(t modify  enum possibilities
	if ($oattr->isAffected()) $err =$oattr->Modify();
	else    $err = $oattr->Add();
      
	$tcr[$nline]["err"].=$err;
      }
      break;    
    case "ORDER":  
      if (is_numeric($data[1]))   $orfromid = $data[1];
      else $orfromid = getFamIdFromName($dbaccess,$data[1]);
      
      $tcolorder[$orfromid]=getOrder($data);
      $tcr[$nline]["msg"]=sprintf(_("new column order %s"),implode(" - ",$tcolorder[$orfromid]));
      
      break;
    case "KEYS":  
      if (is_numeric($data[1]))   $orfromid = $data[1];
      else $orfromid = getFamIdFromName($dbaccess,$data[1]);
      
      $tkeys[$orfromid]=getOrder($data); 
      if (($tkeys[$orfromid][0]=="") || (count($tkeys[$orfromid])==0)) {	
	$tcr[$nline]["err"]=sprintf(_("error in import keys : %s"),implode(" - ",$tkeys[$orfromid]));
	unset($tkeys[$orfromid]);
	$tcr[$nline]["action"]="ignored";
      } else {
	$tcr[$nline]["msg"]=sprintf(_("new import keys : %s"),implode(" - ",$tkeys[$orfromid]));
      }
      break;
    case "PROFIL":  
      if (is_numeric($data[1]))   $pid = $data[1];
      else $pid =  getIdFromName($dbaccess,$data[1]);
      
      if (! ($pid>0)) $tcr[$nline]["err"]=sprintf(_("profil id unkonow %s"),$data[1]);
      else {

	$pdoc = new_Doc($dbaccess, $pid);
	if ($pdoc->isAlive()) {
	  $tcr[$nline]["msg"]=sprintf(_("change profil %s"),$data[1]);	  
	  if ($analyze) continue;
	  $fpid=$data[2];
	  if (($fpid != "") && (!is_numeric($fpid))) $fpid = getIdFromName($dbaccess,$fpid);
	  if ($fpid != "") {
	    // profil related of other profil
	    $pdoc->setProfil($fpid);
	    $err=$pdoc->modify(false,array("profid"),true);
	  } else {
	    // specific profil
	    if ($pdoc->profid != $pid) {
	      $pdoc->setProfil($pid);
	      $pdoc->SetControl(false);
	      $pdoc->disableEditControl(); // need because new profil is not enable yet
	      $tcr[$nline]["err"]= $pdoc-> Modify();  
	    }
	    $tacls=array_slice($data, 2); 
	    foreach ($tacls as $acl) {
	    
	      if (preg_match("/([^=]+)=(.*)/",$acl, $reg)) {
		$tuid= explode(",",$reg[2]);
		$perr="";
		foreach ($tuid as $uid) {
		  $perr.=$pdoc->AddControl($uid,$reg[1]);
		}
		$tcr[$nline]["err"]=$perr;
	      }
	    }
	  }
	  
	} else {
	  $tcr[$nline]["err"]=sprintf(_("profil id unkonow %s"),$data[1]);
	}
      }
      
      break;
    case "ACCESS":
      if (is_numeric($data[1]))   $wid = $data[1];
      else {
	$pid = getIdFromName($dbaccess,$data[1]);
	$tdoc=getTDoc($dbaccess,$pid);
	$wid=getv($tdoc,"us_whatid");
      }
      $idapp=$action->parent->GetIdFromName($data[2]);
      if ($idapp == 0) {
	$tcr[$nline]["err"]=sprintf(_("%s application not exists"),$data[2]);
      } else {
	$tcr[$nline]["msg"]="user #$wid";
	array_shift($data);
	array_shift($data);
	array_shift($data);
	$q=new QueryDb("","Acl");
	$q->AddQuery("id_application=$idapp");
	$la=$q->Query(0,0,"TABLE");
	if (!$la) {
	  $tcr[$nline]["err"]=sprintf(_("%s application has no aclss"),$data[2]);
	} else {
	  $tacl=array();
	  foreach ($la as $k=>$v) {
	    $tacl[$v["name"]]=$v["id"];
	  }

	  $p=new Permission();
	  $p->id_user=$wid;
	  $p->id_application=$idapp;
	  foreach ($data as $v) {
	    $v=trim($v);
	    if ($v!="") {
	      if ($analyze) {
		$tcr[$nline]["msg"].="\n".sprintf(_("try add acl %s"),$v);
		$tcr[$nline]["action"]="added";
		continue;
	      }
	      if (isset($tacl[$v])) {
		$p->id_acl=$tacl[$v];
		$err=$p->Add();
		if ($err) $tcr[$nline]["err"].="\n$err";
		else 	$tcr[$nline]["msg"].="\n".sprintf(_("add acl %s"),$v);
	      } else {
		$tcr[$nline]["err"].="\n".sprintf(_("unknow acl %s"),$v);
	      }
	    }
	  }
	}
      }
      break;
    case "LDAPMAP":      
      if (is_numeric($data[1])) $fid=$data[1];
      else $fid=getFamIdFromName($dbaccess,$data[1]);
      $aid=(trim($data[2]));
      $index=$data[5];
      $oa=new DocAttrLDAP($dbaccess,array($fid,$aid,$index));
      
	//	print_r2($oa);  
      if (substr($data[2],0,2)== "::") $oa->ldapname=$data[2];
      else $oa->ldapname=strtolower(trim($data[2]));

      $oa->ldapclass=trim($data[4]);
      $oa->famid=$fid;
      $oa->ldapmap=$data[3];
      $oa->index=$index;
      $oa->ldapname=$aid;
     
      if ($oa->isAffected()) {
	if (! $analyze) $err=$oa->modify();
	$tcr[$nline]["msg"]=sprintf(_("LDAP Attribute modified to %s %s"), $oa->ldapname,$oa->ldapmap);
	$tcr[$nline]["action"]="updated";
      } else {	
	if (! $analyze) $err=$oa->add();

	$tcr[$nline]["msg"]=sprintf(_("LDAP Attribute added to %s %s"), $oa->ldapname,$oa->ldapmap);
	$tcr[$nline]["action"]="added";
      }
      $tcr[$nline]["err"].=$err;
      
      break;
    default:
      // uninterpreted line
      unset($tcr[$nline]);
    }

	  
  }
      
  fclose ($fdoc);

  if ($csvfile) unlink($csvfile); // temporary csvfile
  
    
  return $tcr;
}


/**
 * Add a document from csv import file
 * @param string $dbaccess database specification
 * @param array $data  data information conform to {@link Doc::GetImportAttributes()}
 * @param int $dirid default folder id to add new document
 * @param bool $analyze true is want just analyze import file (not really import)
 * @param string $ldir path where to search imported files
 * @param string $policy add|update|keep policy use if similar document
 * @param array $tkey attribute key to search similar documents
 * @param array $prevalues default values for new documents
 * @param array $torder array to describe CSV column attributes
 * @global double Http var : Y if want double title document
 * @return array properties of document added (or analyzed to be added)
 */
function csvAddDoc($dbaccess, $data, $dirid=10,$analyze=false,$ldir='',$policy="add",
		   $tkey=array("title"),$prevalues=array(),$torder=array()) {

  // return structure
  $tcr=array("err"=>"",
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
  // like : DOC;120;...
  $err="";
  if (is_numeric($data[1]))   $fromid = $data[1];
  else $fromid = getFamIdFromName($dbaccess,$data[1]);
  $docc = createDoc($dbaccess, $fromid);
  if (! $docc) return;
 
  $msg =""; // information message
  $docc->fromid = $fromid;
  $tcr["familyid"]=$docc->fromid;
  $tcr["familyname"]=$docc->getTitle($docc->fromid);
  if  ($data[2] > 0) $docc->id= $data[2]; // static id
  elseif (trim($data[2]) != "") {
    if (! is_numeric(trim($data[2]))) {
      $docc->name=trim($data[2]); // logical name
      $docid=getIdFromName($dbaccess,$docc->name,$fromid);
      if ($docid > 0) $docc->id=$docid;
    }
  }
  if ($docc->id > 0) {
    $doc=new_doc($docc->dbaccess,$docc->id,true);
    if (! $doc->isAffected()) $doc=$docc;
  } else {
    $doc=$docc;
  }
  
  if ( (intval($doc->id) == 0) || (! $doc->isAffected())) {
    
    $tcr["action"]="added";    
  } else {
    if ($doc->fromid != $fromid) {
      //       $doc = new_Doc($doc->dbaccess,$doc->latestId());
      $tcr["action"]="ignored"; 
      $tcr["id"]=$doc->id;
      $tcr["err"]=_('not same family');
      return $tcr;
    }
    if ($doc->doctype=='Z') {
      if (! $analyze ) $doc->revive();
      $tcr["msg"].=_("restore document")."\n";   
    }

    if ($doc->locked == -1) {
      //       $doc = new_Doc($doc->dbaccess,$doc->latestId());
      $tcr["action"]="ignored"; 
      $tcr["id"]=$doc->id;
      $tcr["err"]=_('fixed document');
      return $tcr;
    }
    
    $tcr["action"]="updated";
    $tcr["id"]=$doc->id;
    $msg .= $err . sprintf(_("update id [%d] "),$doc->id);
  }
    
  if ($err != "") {
    global $nline, $gerr;
    $tcr["action"]="ignored";    
    $gerr="\nline $nline:".$err;
    $tcr["err"]=$err;
    return $tcr;
  }  

  if (count($torder) == 0) {
    $lattr = $doc->GetImportAttributes();
    $torder=array_keys($lattr);
  } else {
    $lattr = $doc->GetNormalAttributes();
  }
  $iattr = 4; // begin in 5th column
  foreach ($torder as $attrid) {
    if (isset($lattr[$attrid])) {
      $attr=$lattr[$attrid];
      if (isset($data[$iattr]) &&  ($data[$iattr] != "")) {
	$dv = str_replace(array('\n',ALTSEPCHAR),array("\n",';'),$data[$iattr]);
	if (!isUTF8($dv))    $dv=utf8_encode($dv);
	if (($attr->type == "file") || ($attr->type == "image")) {
	  // insert file
	  $tcr["foldername"]=$ldir;
	  $tcr["filename"]=$dv;

	  if (! $analyze) {
	    if ($attr->inArray()) {
	      $tabsfiles=$doc->_val2array($dv);
	      $tvfids=array();
	      foreach ($tabsfiles as $fi) {		
		$absfile="$ldir/$fi";
		$err=AddVaultFile($dbaccess,$absfile,$analyze,$vfid);
		if ($err != "") { 
		  $tcr["err"].="$err: $fi\n";
		} else {
		  $tvfids[]=$vfid;
		}
	      }
	      $doc->setValue($attr->id, $tvfids);
		
	    } else {
	      // one file only
	      $absfile="$ldir/$dv";
	      $err=AddVaultFile($dbaccess,$absfile,$analyze,$vfid);
	      if ($err != "") { 
		$tcr["err"]=$err;
	      } else {
		$doc->setValue($attr->id, $vfid);
	      }
	    }
      
	  }
	} else {
	  $doc->setValue($attr->id, $dv);
	  $tcr["values"][$attr->getLabel()]=$dv;
	}
      }
    }
    $iattr++;
  }
  

  if (($err == "") && (! $analyze)) {
    if (($doc->id > 0) || ($policy != "update")) {      
      $err=$doc->preImport();
    }
  }

  // update title in finish
  $doc->refresh(); // compute read attribute
  if ($err != "") {
    $tcr["action"]="ignored";    
    $tcr["err"]=$err;
    return $tcr;
  }  


  if (($doc->id == "") && ($doc->name == "")) {
    switch ($policy) {
    case "add": 
      $tcr["action"]="added"; # N_("added")
      if (! $analyze) {
	
	if ($doc->id == "") {
	  // insert default values
	  foreach($prevalues as $k=>$v) {
	    $doc->setValue($k,$v);
	  }
	  $err=$doc->preImport();
	  if ($err != "") {
	    $tcr["action"]="ignored";
	    $tcr["err"]=sprintf(_("pre-import:%s"),$err);
	    return $tcr;
	  }  
	  $err = $doc->Add(); 
	  $tcr["err"]=$err;
	}
	if ($err=="") {
	  $tcr["id"]=$doc->id;
	  $msg .= $err . sprintf(_("add %s id [%d]  "),$doc->title,$doc->id); 
	  $tcr["msg"]=sprintf(_("add %s id [%d]  "),$doc->title,$doc->id); 
	}else {
	  $tcr["action"]="ignored";
	}
      } else {
	$doc->RefreshTitle();
	$tcr["msg"]=sprintf(_("%s to be add"),$doc->title);
      }
      break;

	
    case "update": 
      $doc->RefreshTitle();
      $lsdoc = $doc->GetDocWithSameTitle($tkey[0],$tkey[1]);
      // test if same doc in database
      if (count($lsdoc) == 0) {
	$tcr["action"]="added";
	if (! $analyze) {
	  if ($doc->id == "") {
	    // insert default values
	    foreach($prevalues as $k=>$v) {
	      if ($doc->getValue($k) == "")  $doc->setValue($k,$v);
	    }
	    $err=$doc->preImport();
	    if ($err != "") {
	      $tcr["action"]="ignored";
	      $tcr["err"]=sprintf(_("pre-import:%s"),$err);
	      return $tcr;
	    }
	    $err = $doc->Add(); 
	    $tcr["err"]=$err;
	  }
	  if ($err=="") {
	    $tcr["id"]=$doc->id;
	    $tcr["action"]="added";
	    $tcr["msg"]=sprintf(_("add id [%d] "),$doc->id); 
	  } else {
	    $tcr["action"]="ignored";
	  }
	} else {	    
	  $tcr["msg"]=sprintf(_("%s to be add"),$doc->title);
	}
      } elseif (count($lsdoc) == 1) {	 
	// no double title found
	$tcr["action"]="updated";# N_("updated")
	if (! $analyze) {
	  $err=$lsdoc[0]->preImport();
	  if ($err != "") {
	    $tcr["action"]="ignored";
	    $tcr["err"]=sprintf(_("pre-import:%s"),$err);
	    return $tcr;
	  }  
	}
	$lsdoc[0]->transfertValuesFrom($doc);
	$doc=$lsdoc[0];
	$tcr["id"]=$doc->id;
	if (! $analyze) {
	  if (($data[2]!="") && (! is_numeric(trim($data[2]))) && ($doc->name=="")) {
	    $doc->name=$data[2];
	  }
	  $tcr["msg"]=sprintf(_("update %s [%d] "),$doc->title,$doc->id);
	} else {
	  $tcr["msg"]=sprintf(_("to be update %s [%d] "),$doc->title,$doc->id);
	  
	}
      } else {
	//more than one double
	$tcr["action"]="ignored";# N_("ignored")
	$tcr["err"]=sprintf(_("too many similar document %s <B>ignored</B> "),$doc->title);
	$msg .= $err.$tcr["err"];
	return $tcr;
      }
    
      break;
    case "keep": 
      $doc->RefreshTitle();
      $lsdoc = $doc->GetDocWithSameTitle($tkey[0],$tkey[1]);
      if (count($lsdoc) == 0) { 
	$tcr["action"]="added";
	if (! $analyze) {
	  if ($doc->id == "") {
	    // insert default values
	    foreach($prevalues as $k=>$v) {
	      if ($doc->getValue($k)=="") $doc->setValue($k,$v);
	    }
	    $err = $doc->Add(); 
	  }
	  $tcr["id"]=$doc->id;
	  $msg .= $err . sprintf(_("add id [%d] "),$doc->id); 
	} else {	    
	  $tcr["msg"]=sprintf(_("%s to be add"),$doc->title);
	}
      } else {
	//more than one double
	$tcr["action"]="ignored";
	$tcr["err"]=sprintf(_("similar document %s found. keep similar"),$doc->title);
	$msg .= $err.$tcr["err"];
	return $tcr;
      }
	
      break;
    }
  } else {
    // add special id
    if (! $doc->isAffected()) {
      $tcr["action"]="added"; 
      if (! $analyze) {
	// insert default values
	foreach($prevalues as $k=>$v) {
	  if ($doc->getValue($k)=="") $doc->setValue($k,$v);
	}
	$err=$doc->preImport();
          if ($err != "") {
            $tcr["action"]="ignored";
            $tcr["err"]=sprintf(_("pre-import:%s"),$err);
            return $tcr;
        }  
	$err = $doc->Add(); 
	
	$tcr["id"]=$doc->id;
	$msg .= $err . sprintf(_("add %s id [%d]  "),$doc->title,$doc->id); 
	$tcr["msg"]=sprintf(_("add %s id [%d]  "),$doc->title,$doc->id); 
      } else {
	$doc->RefreshTitle();
	$tcr["id"]=$doc->id;
	$tcr["msg"]=sprintf(_("%s to be add"),$doc->title);
      }
    }
  }
      
  $tcr["title"]=$doc->title;
  if (! $analyze) {
    if ($doc->isAffected()) {
      $tcr["specmsg"]=$doc->Refresh(); // compute read attribute
      $err=$doc->PostModify(); // compute read attribute
      if ($err=="") $err=$doc->modify();

      if ($err=="-") $err=""; // not really an error add addfile must be tested after
      if ($err=="") {
	$doc->AddComment(sprintf(_("updated by import")));
	$msg.=$doc->postImport();
      }
      $tcr["err"].=$err;
      $tcr["msg"].=$msg;
    }
  }
  //------------------
  // add in folder

  if (($err=="") && ($data[3]!="-")){
    $msg .= $doc->title;
    if (is_numeric($data[3])) $ndirid=$data[3];
    else $ndirid=getIdFromName($dbaccess,$data[3],2);

    if ($ndirid > 0) { // dirid
      $dir = new_Doc($dbaccess, $ndirid);
      if ($dir->isAffected()) {
	$tcr["folderid"]=$dir->id;
	$tcr["foldername"]=dirname($ldir)."/".$dir->title;
	if (! $analyze) {	
	  if ($dir->isAlive() && method_exists($dir,"AddFile")) {
	    $tcr["err"].=$dir->AddFile($doc->id);
	  }
	}
	$tcr["msg"].= $err." ".sprintf(_("and add in %s folder "),$dir->title); 
      }
    } else if ($ndirid ==  0) {
      if ($dirid > 0) {

	$dir = new_Doc($dbaccess, $dirid);
	if ($dir->isAlive() && method_exists($dir,"AddFile")) {
	  $tcr["folderid"]=$dir->id;
	  $tcr["foldername"]=dirname($ldir)."/".$dir->title;
	  if (! $analyze) {	    
	    if ($dir->isAlive() && method_exists($dir,"AddFile")) {
	      $tcr["err"].=$dir->AddFile($doc->id);
	    }
	  }
	  $tcr["msg"] .= $err." ".sprintf(_("and add in %s folder "),$dir->title); 
	}
      }
    }
  }    

  return $tcr;
}

function AddImportLog( $msg) {
  global $action;
  if ($action->lay) {
    $tmsg = $action->lay->GetBlockData("MSG");
    $tmsg[] = array("msg"=>$msg);
    $action->lay->SetBlockData("MSG",$tmsg);
  } else {
    print "\n$msg";
  }
}

function getOrder($orderdata) {
  return array_map("trim", array_slice ($orderdata, 4));
}

function AddVaultFile($dbaccess,$path,$analyze,&$vid) {
  global $importedFiles;

  $path=str_replace("//","/",$path);
  // return same if already imported (case of multi links)
  if (isset($importedFiles[$path])) {
    $vid=$importedFiles[$path];
    return "";
  }

  $absfile2=str_replace('"','\\"',$path);
  // $mime=mime_content_type($absfile);
  $mime=trim(`file -ib "$absfile2"`);
  if (!$analyze) {
    $vf = newFreeVaultFile($dbaccess);
    $err=$vf->Store($path, false , $vid);
  }
  if ($err != "") {

    AddWarningMsg($err);
    return $err;
  } else {
    $base=basename($path);
    $importedFiles[$path]="$mime|$vid|$base";
    $vid="$mime|$vid|$base";
  
   
    return "";
  }
  return false;
}
function seemsODS($filename) {
  if (preg_match('/\.ods$/',$filename)) return true;
  $sys = trim(`file -bi "$filename"`);
  if ($sys=="application/x-zip") return true;
  if ($sys=="application/vnd.oasis.opendocument.spreadsheet") return true;
  return false;
}

/**
 * convert ods file in csv file
 * the csv file must be delete by caller after using it
 * @return strint the path to the csv file
 */
function ods2csv($odsfile) {
    $csvfile = uniqid("/var/tmp/csv")."csv";
    $wsh =  getWshCmd();
    $cmd=sprintf("%s --api=ods2csv --odsfile=%s --csvfile=%s >/dev/null",
		 getWshCmd(),
		 $odsfile,
		 $csvfile );
    $err=system($cmd,$out);
    if ($err===false) return false;
    return $csvfile;
}

?>
