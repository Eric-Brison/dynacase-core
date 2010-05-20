<?php
/**
 * Functions used for edition help
 *
 * @author Anakeen 2003
 * @version $Id: FDL_external.php,v 1.71 2009/01/13 09:37:25 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
/**
 */

include_once("FDL/Class.Dir.php");
include_once("FDL/Lib.Dir.php");




function vault_filename($th, $fileid) {


  if (preg_match(PREGEXPFILE, $fileid, $reg)) {	 
    // reg[1] is mime type
      $vf = newFreeVaultFile($th->dbaccess);
    if ($vf -> Show ($reg[2], $info) == "") $fname = $info->name;
    else $fname=sprintf(_("file %d"),$th->initid);
  } else {
    $fname=sprintf(_("file %d"),$th->initid);
  }

  return array($fname);
}


/**
 * Functions used for edition help
 *
 * @param string $dbaccess database specification
 * @param int $docid identificator document 
 * @return array first item : the title
 */
function gettitle($dbaccess, $docid) {

  $doc=new_Doc($dbaccess, $docid);
  if ($doc->isAffected())  return array($doc->title);
  return array("?"," "); // suppress
}

/**
 * link enum definition from other def
 */
function linkenum($famid,$attrid) {
  
  $dbaccess=getParam("FREEDOM_DB");
  if (! is_numeric($famid)) $famid=getFamIdFromName($dbaccess,$famid);
  $soc = new_Doc($dbaccess, $famid);
  if ($soc->isAffected()) {
    $a = $soc->getAttribute($attrid);
    return $a->phpfunc;
  }
  return "";
}
// liste de personnes
function lmail( $dbaccess, $name) {     

  global $action;
  

  $filter=array();
  if ($name != "") {
    $name=pg_escape_string($name);
    $filter[]="(title ~* '$name') or (us_mail ~* '$name')";
  }

  $filter[] = "us_mail is not null";
  $famid=getFamIdFromName($dbaccess,"USER");

  $tinter = getChildDoc($dbaccess, 0,0,100, $filter,$action->user->id,"TABLE",$famid);
  
  $tr = array();

  while(list($k,$v) = each($tinter)) {
            
    $mail = getv($v,"us_mail");
    $usw=getv($v,"us_whatid");
    $uid="";
    if ($usw > 0) {
      $uid=$v["id"];
      $type="1";//$type="link";  // cause it is a bool
    } else {
      $type="0";//$type="plain";
      $uid=" ";
    }
    $tr[] = array($v["title"] ,$v["title"]." <$mail>",$uid,$type);
    
  }
  return $tr;  
}
function tplmail($dbaccess,$type,$famid,$wfamid,$name) {
  switch($type) {
  case 'F': // address fix
    return lmail($dbaccess,$name);
  case 'A': // value of attribute
    if (! $famid) return _("family must be defined");
    $ta=getFamAttribute($dbaccess,$famid,'text',false,$name);
    return $ta;
  case 'D': // value of attribute
    if (! $famid) return _("family must be defined");
    $ta=getFamAttribute($dbaccess,$famid,'docid',false,$name);
    return $ta;
  case 'G': // value of attribute
    if (! $famid) return _("family must be defined");
    $ta=getFamAttribute($dbaccess,$famid,'file',false,$name);
    $ta = array_merge($ta,getFamAttribute($dbaccess,$famid,'image',false,$name));
    return $ta;
  case 'E': // value of attribute
    if (! $famid) return _("family must be defined");
    $ta=getFamAttribute($dbaccess,$famid,'',true,$name);
    return $ta;
  case 'WA': // value of attribute
    if (! $wfamid) return _("cycle family must be defined");
    $ta=getFamAttribute($dbaccess,$wfamid,'text',false,$name);
    return $ta;
  case 'WE': // value of attribute
    if (! $wfamid) return _("cycle family must be defined");
    $ta=getFamAttribute($dbaccess,$wfamid,'',true,$name);
    return $ta;
  case 'WD': // value of attribute
    if (! $wfamid) return _("cycle family must be defined");
    $ta=getFamAttribute($dbaccess,$wfamid,'docid',false,$name);
    return $ta;
  case 'PR':    
    if (! $famid) return _("family must be defined");
    $ta=getFamAttribute($dbaccess,$famid,'docid',true,$name);
    return $ta;
  case 'WPR':    
    if (! $wfamid) return _("cycle family must be defined");
    $ta=getFamAttribute($dbaccess,$wfamid,'docid',true,$name);
    return $ta;
  case 'P':
    return getGlobalsParameters($name);
  }
  return "error tplmail($dbaccess,$type,$famid, $name)";
}
function tpluser($dbaccess,$type,$famid,$wfamid,$name) {
  switch($type) {
  case 'F': // address fix
    $users= lfamily($dbaccess,"IUSER",$name);
    if (is_array($users)) {
      foreach ($users as $k=>$v) {
	$users[$k][1]=$v[1].' ('.$v[2].')';
      }
    }
    return $users;
  default:
    return tplmail($dbaccess,$type,$famid,$wfamid,$name);
  }
}
function getGlobalsParameters($name) {
  include_once("Class.QueryDb.php");
  $q=new QueryDb("","ParamDef");

  $tr = array();
  $q->AddQuery("isglob = 'Y'");
  if ($name)  $q->AddQuery("name ~* '".pg_escape_string($name)."'");
  $q->order_by="name";
  $la=$q->Query(0,20,"TABLE");
  foreach ($la as $k=>$v) {
    $p=$v["name"].' ('.$v["descr"].')';
    $tr[] = array($p,$p);
  }
  return $tr;
}

/**
 * attribut list to be use in mail template
 */
function getFamAttribute($dbaccess, $famid, $type="text", $param=false, $name="") {  
  $doc = createDoc($dbaccess, $famid,false);  
  $tr = array();
  if ($param) $tinter=$doc->getParamAttributes();
  else  $tinter = $doc->GetNormalAttributes();  
  $name=strtolower($name);
  // HERE HERE HERE
  $pattern_name = preg_quote($name);
  $pattern_type = preg_quote($type);
  foreach($tinter as $k=>$v) {
    if (($name == "") ||    (preg_match("/$pattern_name/i", $v->getLabel() , $reg)) || (preg_match("/$pattern_name/", $v->id , $reg)))
      if (($type == "") || ($v->type==$type) || ((strpos($type,'|')>0) && (preg_match("/$pattern_type/", $v->type , $reg)))) {
	$r=$v->id. ' ('.$v->getLabel().')';
	$tr[] = array($r,$r);
      }
    
  }
  return $tr;  
}

// liste des familles
function lfamilies($dbaccess, $name='',$subfam="") {
  //'lsociety(D,US_SOCIETY):US_IDSOCIETY,US_SOCIETY,
  global $action;
  
  if ($subfam=="") {
    $tinter = GetClassesDoc($dbaccess, $action->user->id,0,"TABLE");
  } else {
    if (! is_numeric($subfam)) $subfam=getFamIdFromName($dbaccess,$subfam);
    $cdoc = new_Doc($dbaccess,$subfam);
    $tinter = $cdoc->GetChildFam();
    $tinter[]=get_object_vars($cdoc);
  }
  
  $tr = array();

  $name=strtolower($name);
  // HERE HERE HERE
  $pattern_name = preg_quote($name);
  while(list($k,$v) = each($tinter)) {
    $ftitle=DocFam::getLangTitle($v);
    if (($name == "") || (preg_match("/$pattern_name/i", $ftitle , $reg))) {

      $tr[] = array($ftitle ,
		    $v["id"],$ftitle);
    
    }
  }
  return $tr;  
}


// liste des documents par familles
/**
 * list of documents of a same family
 *
 * @param string $dbaccess database specification
 * @param string $famid family identifier (if 0 any family). It can be internal name
 * @param string $name string filter on the title
 * @param int $dirid identifier of folder for restriction to a folder tree (deprecated)
 * @param array $filter additionnals SQL filters
 * @param string $idid the document id to use (default: id)
 * @return array/string*3 array of (title, identifier, title)
 */
function lfamilly($dbaccess, $famid, $name="", $dirid=0, $filter=array(),$idid="id") {
  //'lsociety(D,US_SOCIETY):US_IDSOCIETY,US_SOCIETY,
  global $action;
  
  if (! is_array($filter)) $filter=array();
  $only=false;
  if ($famid[0]=='-') {
    $only=true;
    $famid=substr($famid,1);
  }

  if (! is_numeric($famid)) {
    $famid=getFamIdFromName($dbaccess,$famid);
  }


  if ($name != "" && is_string($name)) {
    $name=pg_escape_string($name);
    $filter[]="title ~* '$name'";
  }

  //$famid=-(abs($famid));
  if ($only) $famid=-($famid);
  $tinter = getChildDoc($dbaccess, $dirid,0,100, $filter,$action->user->id,"TABLE",$famid,false,"title");
  
  $tr = array();

  foreach($tinter as $k=>$v) {            
    $tr[] = array($v["title"] ,
		  $v[$idid],$v["title"]);
    
  }
  return $tr;
  
}

// alias name
function lfamily($dbaccess, $famid, $name="", $dirid=0, $filter=array(),$idid="id") {
  return lfamilly($dbaccess, $famid, $name, $dirid, $filter,$idid);
}

/**
 * list of documents of a same family and their specific attributes
 *
 * @param string $dbaccess database specification
 * @param string $famid family identifier (if 0 any family). It can be internal name
 * @param string $name string filter on the title
 * @param string $attrids argument variable of name of attribute to be returned
 * @return array/string*3 array of (title, identifier, attr1, attr2, ...)
 */
function lfamilyvalues($dbaccess, $famid, $name="") {
  //'lsociety(D,US_SOCIETY):US_IDSOCIETY,US_SOCIETY,
  global $action;
  
  $only=false;
  if ($famid[0]=='-') {
    $only=true;
    $famid=substr($famid,1);
  }

  if (! is_numeric($famid)) {
    $famid=getFamIdFromName($dbaccess,$famid);
  }

  if ($name != "") {
    $name=pg_escape_string($name);
    $filter[]="title ~* '$name'";
  }
  $attr=array();
  $args=func_get_args();
  foreach ($args as $k=>$v) {
    if ($k>2) $attr[]=strtolower($v);
  }

  //$famid=-(abs($famid));
  if ($only) $famid=-($famid);
  $tinter = getChildDoc($dbaccess, $dirid,0,100, $filter,$action->user->id,"TABLE",$famid,false,"title");
  
  $tr = array();

  foreach($tinter as $k=>$v) {
    $tr[$k] = array($v["title"]);
    foreach ($attr as $a) {
      $tr[$k][]=$v[$a];
    }    
  }
  return $tr;
}
/**
 * list of documents of a same family and which are in the $kid category
 *
 * @param string $dbaccess database specification
 * @param string $famname family internal name
 * @param string $aid enum attribute identifier 
 * @param string $kid enum key to search
 * @param string $name string filter on the title
 * @param array $filter additionnals SQL filters
 * @return array/string*3 array of (title, identifier, title)
 */
function lkfamily($dbaccess, $famname, $aid, 
		  $kid, $name, $filter=array()) {
  //'lsociety(D,US_SOCIETY):US_IDSOCIETY,US_SOCIETY,
  global $action;
  
  if ($name != "") {
    $name=pg_escape_string($name);
    $filter[]="title ~* '.*$name.*'";
  }

  $tinter = getKindDoc($dbaccess, $famname, $aid,$kid,$name,$filter);    
  
  $tr = array();


  while(list($k,$v) = each($tinter)) {
            
    $tr[] = array($v["title"] ,
		  $v["id"],$v["title"]);
    
  }
  return $tr;
  
}
/**
 * return list of string for multiple static choice
 *
 * @param string $val filter value - can be empty => see all choices
 * @param string $enum possible choices like 'the first|the second|the last'
 * @return array/string*2 array of (enum, enum)
 */
function lenum($val, $enum) {
  // $enum like 'a|b|c'
 

  $tenum=explode("|",$enum);

  $tr=array();
  // HERE HERE HERE
  $pattern_val = preg_quote($val);
  while(list($k,$v) = each($tenum)) {
            
    if (($val == "") || (preg_match("/$pattern_val/i", $v , $reg)))
      $tr[] = array($v , $v);
    
  }
  return $tr;
  
}
/**
 * return list of string for multiple static choice
 *
 * @param string $val filter value - can be empty => see all choices
 * @param string $enum possible choices like 'the first|the second|the last'
 * @return array/string*2 array of (enum, enum)
 */
function lenumvalues($enum, $val="" ) {
  // $enum like 'a|A,b|B'
  $val=trim($val);
  $val=str_replace(array('&comma;','&point;'),array(',','.'),$val);
  $tenum=explode("---",$enum);


  $tr=array();
  $val=str_replace(array(')','(',),array('\)','\('),$val);

  foreach($tenum as $k=>$v) {    
    list($key,$label)=explode("|",$v);
    $slabel=str_replace(array('&comma;','&point;'),array(',','.'),$label);
    if (($val == "") || (preg_match("/$val/i", $slabel , $reg)))   $tr[]=array("$slabel",$slabel,$key);
  }
  
  return $tr;
  
}
// liste des profils
function lprofil($dbaccess, $name,$famid=0) {
  //'lsociety(D,US_SOCIETY):US_IDSOCIETY,US_SOCIETY,
  global $action;
  $dirid= 0;
  if ($famid > 0) {
    $fdoc=createTmpDoc($dbaccess,$famid);
    if ($fdoc->defDoctype=='D') return lfamily($dbaccess, 4, $name);
    else if ($fdoc->defDoctype=='S') return lfamily($dbaccess, 6, $name);
    else return lfamily($dbaccess, 3, $name,0,array("fromid=3"));
				  
  }
  
  return lfamily($dbaccess, 3, $name, $dirid);
  
}

// liste des masque
function lmask($dbaccess, $name, $maskfamid="") {

  $filter=array();
  if ($maskfamid > 0) {
    $mdoc = new_Doc($dbaccess,$maskfamid);
    $chdoc=$mdoc->GetFromDoc();
    $filter[]=GetSqlCond($chdoc,"msk_famid");
    //    $filter[]="msk_famid='$maskfamid'"; // when workflow will have attribut to say the compatible families
  }
  return lfamilly($dbaccess, "MASK", $name, 0, $filter);
  
}

// view control list
function lcvdoc($dbaccess, $name, $cvfamid="") {
  $filter=array();
  if ($cvfamid > 0) {
    $mdoc = new_Doc($dbaccess,$cvfamid);
    $chdoc=$mdoc->GetFromDoc();
    $filter[]=GetSqlCond($chdoc,"cv_famid");
  }
  return lfamily($dbaccess, "CVDOC", $name, 0, $filter);
  
}

// mail template list
function lmailtemplatedoc($dbaccess, $name, $cvfamid="") {
  $filter=array();
  if ($cvfamid > 0) {
    $mdoc = new_Doc($dbaccess,$cvfamid);
    $chdoc=$mdoc->GetFromDoc();
    $filter[]="tmail_family is null or (".GetSqlCond($chdoc,"tmail_family").')';
  }
  return lfamily($dbaccess, "MAILTEMPLATE", $name, 0, $filter);  
}
// timer list
function ltimerdoc($dbaccess, $name, $cvfamid="") {
  $filter=array();
  if ($cvfamid > 0) {
    $mdoc = new_Doc($dbaccess,$cvfamid);
    $chdoc=$mdoc->GetFromDoc();
    $filter[]="tm_family is null or (".GetSqlCond($chdoc,"tm_family").')';
  }
  return lfamily($dbaccess, "TIMER", $name, 0, $filter);  
}


/**
 * search list not filters
 */
function lsearches($dbaccess, $name) {

  $filter=array("fromid=5 or fromid=16");
  return lfamilly($dbaccess, "SEARCH", $name, 0, $filter);
  
}

/**
 * tab list not filters
 */
function ltabs($dbaccess, $name) {

  $filter=array("fromid=5 or fromid=16");
  $ls= lfamily($dbaccess, "SEARCH", $name, 0, $filter);
  
  $fld= lfamily($dbaccess, "2", $name);

  $all=array_merge($ls,$fld);
  return $all;
}
// liste des zones possibles
// $tview VCONS|VEDIT
function lzone_($dbaccess, $tview, $famid ="") {
  $tz=array();

  $filter=array();
  if ($famid > 0) {
    $fdoc = new_Doc($dbaccess,$famid);
    $cdoc=createDoc($dbaccess,$famid,false);
    if ($tview == "VEDIT") $tz=$cdoc->eviews;
    else $tz=$cdoc->cviews;
    $oz=lzone_($dbaccess, $tview, $fdoc->fromid);
    $tz = array_merge($oz,$tz);
    
  } else {
    $fdoc = new_Doc($dbaccess);
    if ($tview == "VEDIT") $tz=$fdoc->eviews;
    else $tz=$fdoc->cviews;
  }
  

  return $tz;
  
}

function lzone($dbaccess, $tview, $famid ="") {
  $tz=lzone_($dbaccess, $tview, $famid);
  $tz=array_unique($tz);
  foreach ($tz as $v) {
    $tr[]=array($v,$v);
  }
  

  return $tr;
  
}

function lview($tidview, $tlview) {

  foreach ($tidview as $k=>$v) {
    $tr[]=array($tlview[$k],trim($v),$tlview[$k]);
  }
  

  return $tr;
  
}

// liste des attributs d'une famille
function getDocAttr($dbaccess, $famid, $name="") {
  return getSortAttr($dbaccess, $famid, $name,false);
}

// liste des attributs triable d'une famille
function getSortAttr($dbaccess, $famid, $name="",$sort=true) {
  //'lsociety(D,US_SOCIETY):US_IDSOCIETY,US_SOCIETY,
  
  $doc = createDoc($dbaccess, $famid,false);
  // internal attributes
  $ti = array("title" => _("doctitle"),
	      "revdate" => _("revdate"),
	      "revision" => _("revision"),
	      "owner" => _("owner"),
	      "state" => _("state"));
  
  $tr = array();
  $pattern_name = preg_quote($name);
  while(list($k,$v) = each($ti)) {
    if (($name == "") ||    (preg_match("/$pattern_name/i", $v , $reg)))
      $tr[] = array($v , $k,$v);
    
  }

  if ($sort)  $tinter = $doc->GetSortAttributes();
  else $tinter = $doc->GetNormalAttributes();
  

  while(list($k,$v) = each($tinter)) {
    if (($name == "") ||    (preg_match("/$pattern_name/i", $v->getLabel() , $reg)))
      $tr[] = array($v->getLabel() ,
		    $v->id,$v->getLabel());
    
  }
  return $tr;  
}


function laction($dbaccess, $famid, $name,$type) {
  $filter=array();
  $filter[]="act_type='$type'";
  return lfamilly($dbaccess, $famid,$name,0,$filter);
}

/**
 * return list of what application
 */
function lapplications($n="") {
  $q=new QueryDb("","Application");

  $tr = array();
  if ($n != "") $q->AddQuery("name ~* '$n'");
  $la=$q->Query(0,0,"TABLE");
  if (is_array($la)) {
    foreach ($la as $k=>$v) {
      $tr[] = array($v["name"].":".$v["short_name"],
		    $v["name"]);
    }
  }
  return $tr;
}
/**
 * return list of what action for one application
 */
function lactions($app,$n="") {
  $tr = array();
  $q=new QueryDb("","Application"); 
  $q->AddQuery("name = '$app'");
  $la=$q->Query(0,0,"TABLE");
  if ($q->nb == 1) {
    $appid=$la[0]["id"];
    if ($appid > 0) {
      $q=new QueryDb("","Action");
      $q->AddQuery("id_application = $appid");      
      if ($n != "") $q->AddQuery("name ~* '$n'");
      $la=$q->Query(0,0,"TABLE");

      if ($q->nb > 0) {
	foreach ($la as $k=>$v) {
	  $tr[] = array($v["name"].":"._($v["short_name"]),
			$v["name"]);
	}
      }
    }
  }
  return $tr;
}


function lapi($name="") {
  $cmd=sprintf("cd %s/API;ls -1 *.php| cut -f1 -d'.'",DEFAULT_PUBDIR);
  $apis=shell_exec($cmd);
  $tapi=explode("\n",$apis);

  $tr=array();
  // HERE HERE HERE
  $pattern_name = preg_quote($name);
  foreach ($tapi as $k=>$v) {
    $v0=trim($v);
    if (($name == "") || (preg_match("/$pattern_name/i", $v0 , $reg)))  $tr[] = array($v0,$v0);
  }
  return($tr);
}

function lstates($dbaccess, $wid, $name="") {
  $doc=createDoc($dbaccess,$wid,false);
  if ($doc && method_exists($doc,"getStates")) {
    $states=$doc->getStates();
    // HERE HERE HERE
    $pattern_name = preg_quote($name);
    foreach ($states as $k=>$v) {
      if (($name == "") ||    (preg_match("/$pattern_name/i", $v , $reg))) $tr[]=array($v.' ('._($v).')',$v);
    }
  } else return sprintf(_("need to select workflow"));
  
  return $tr;  
}
function ldocstates($dbaccess, $docid, $name="") {
  $doc=new_doc($dbaccess,$docid);
  $tr=array();
  if ($doc->isAlive() && $doc->wid) {
    $wdoc=new_doc($dbaccess,$doc->wid,false);
    if ($wdoc && method_exists($wdoc,"getStates")) {
      $states=$wdoc->getStates();      
      // HERE HERE HERE
      $pattern_name = preg_quote($name);
      foreach ($states as $k=>$v) {
	if (($name == "") ||    (preg_match("/$pattern_name/i", $v , $reg))) $tr[]=array($v.' ('._($v).')',$v.' ('._($v).')');
      }
    } else return sprintf(_("no workflow for this document"));
  } else return sprintf(_("no workflow for this document"));
  return $tr;  
}
function lmethods($dbaccess, $famid, $name="") {
  $doc=createDoc($dbaccess,$famid,false);
  if ($doc) {
    $methods=get_class_methods($doc);
    $pattern_name = preg_quote($name);
    foreach ($methods as $k=>$v) {
      if (($name == "") ||    (preg_match("/$pattern_name/i", $v , $reg))) $tr[]=array($v,'::'.$v.'()');
    }
  } else return sprintf(_("need to select family"));
  
  return $tr;  
}

/**
 * retrieve information from postgresql database 
 * @param string $dbaccess the database coordonates
 * @param string $table the name of sql table where search data
 * @param string $filter the sql where clause to filter 
 * @param string $more dynamic others arg to define column to retrieve
 */
function db_query($dbaccess,$table,$filter) {
  $conn = pg_connect($dbaccess);
  if (!$conn) {
    return sprintf(_("connexion to %s has failed"),$dbaccess);
  }
  
 $args=func_get_args();
 $cols=array();
 $order='';
 $tdn=array();// display name
  foreach ($args as $k=>$v) {
    if ($k>2) {
      if (substr($v,-2)==":H") {
	$v=substr($v,0,-2);
      } else {
	if (!$order) $order=strtolower($v);
	$tdn[]=$k-3;
      }
      $cols[]=(strtolower($v));

    }
  }
  if (count($cols)== 0) {
    return sprintf(_("no columns specified"));
  }
  if (count($tdn)== 0) {
    return sprintf(_("all columns are hiddens"));
  }

  $select="select ".implode(",",$cols);
  $from="from ".pg_escape_string($table);
  $orderby="order by ".($order);
  foreach ($_POST as $k=>$v) {
    if ($k[0]=='_') {
      $filter=preg_replace('/'.substr($k,1).'/i',pg_escape_string(trim(stripslashes($v))),$filter);
    }
  }


  $where = "where $filter";
  $limit="limit 100";
  $sql=$select .' '. $from .' '. $where.' '.$orderby.' '.$limit;
  $result = @pg_query($conn, $sql);
  if (!$result) {
    return sprintf(_("query %s has failed"),$sql);
  }

  $kr=0;
  $t=array();
  while ($row = pg_fetch_row($result)) {
    $dn='';
    foreach ($tdn as $vi) $dn.=$row[$vi].' ';
    $t[$kr][]=$dn;
    foreach ($row as $ki=>$vi) $t[$kr][]=$vi;    
    $kr++;
  }

  return $t;

}
?>
