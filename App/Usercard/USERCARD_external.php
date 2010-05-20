<?php
/**
 * Functions used for edition help of USER, GROUP & SOCIETY Family
 *
 * @author Anakeen 2003
 * @version $Id: USERCARD_external.php,v 1.20 2008/11/06 10:16:24 jerome Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
/**
 */

include_once("FDL/Class.Dir.php");
include_once("FDL/Lib.Dir.php");
include_once("EXTERNALS/fdl.php");





/**
 * society list
 *
 * the SOCIETY documents and the SITE documents wich doesn't have society father
 * @param string $dbaccess database specification
 * @param string $name string filter on the title
 * @return array/string*3 array of (title, identifier, title)
 * see lfamilly()
 */
function lsociety($dbaccess, $name) {
  //'lsociety(D,US_SOCIETY):US_IDSOCIETY,US_SOCIETY,
  global $action;
  $dirid= 0;
  

  $societies =  lfamilly($dbaccess, 124, $name, $dirid, array("fromid=124"));



  $societies +=  lfamilly($dbaccess, 126, $name, $dirid, array("si_idsoc isnull"));
  
  return $societies;
}


/**
 * site list
 *
 * all the SITE documents
 * @param string $dbaccess database specification
 * @param string $name string filter on the title
 * @return array/string*3 array of (title, identifier, title)
 * see lfamilly()
 */
function lsite($dbaccess, $name) {
  //'lsociety(D,US_SOCIETY):US_IDSOCIETY,US_SOCIETY,

  $dirid= 0;
  

  return lfamilly($dbaccess, 124, $name, $dirid);
  
}

// liste des société
function laddrsoc($dbaccess, $idc) {
  //'laddrsoc(D,US_IDSOCIETY):US_SOCADDR,US_WORKADDR,US_WORKTOWN,US_WORKPOSTALCODE,US_WORKWEB,US_CEDEX,US_COUNTRY


  $doc = new_Doc($dbaccess, $idc);
  $tr=array();
  if ($doc->isAffected()) {
    $tr[] = array("adresse société",
		  "yes",
		  $doc->getValue("SI_ADDR"),
		  $doc->getValue("SI_TOWN"),
		  $doc->getValue("SI_POSTCODE"),
		  $doc->getValue("SI_WEB"),
		  $doc->getValue("SI_CEDEX"),
		  $doc->getValue("SI_COUNTRY"),
		  $doc->getValue("SI_PHONE"),
		  $doc->getValue("SI_FAX"));
  }
  
//   $tr[] = array("adresse propre",
// 		  " ",
// 		  "?",
// 		  "?",
// 		  "?",
// 		  "?",
// 		  "?",
// 		  "?",
// 		  "?",
// 		  "?");
  
  return $tr;
  
}
function getSphone($dbaccess, $idc) {
  $doc = new_Doc($dbaccess, $idc);

  $tr=array();
  if ($doc->isAlive()) {
    $tr[]=array($doc->getValue("SI_PHONE"), $doc->getValue("SI_PHONE"));
    
  }
  return $tr;
  
}
function getSFax($dbaccess, $idc) {
  $doc = new_Doc($dbaccess, $idc);

  $tr=array();
  if ($doc->isAlive()) {
    $tr[]=array($doc->getValue("SI_FAX"), $doc->getValue("SI_FAX"));
    
  }
  return $tr;
  
}

// liste des personnes d'une société
function lpersonnesociety( $dbaccess, $idsociety, $name="" ) {  

  // 'lpersonnesociety(D,CMF_IDSFUR,CMF_PFUR):CMF_IDPFUR,CMF_PFUR,CMF_AFUR,CMF_MFUR,CMF_TFUR,CMF_FFUR,CMF_SFUR,CMF_IDSFUR


  $filter=array();

  if ($idsociety > 0)  $filter[]="us_idsociety = '$idsociety'";
  
  if ($name != "")     $filter[]="title ~* '$name'";
  


  $tinter = getChildDoc($dbaccess, 0 ,0,100, $filter,$action->user->id, "TABLE",
			getFamIdFromName($dbaccess,"USER"));


  
  $tr = array();


  while(list($k,$v) = each($tinter)) {
            
    $sidfur= getv($v,"us_idsociety");
    
    $sfur= getv($v,"us_society");
    $afur= getv($v,"us_workaddr")."\n".getv($v,"us_workpostalcode")." ".getv($v,"us_worktown")." ".getv($v,"us_workcedex");
    if (getv($v,"us_country") != "") $afur.="\n".getv($v,"us_country");
    $tfur= getv($v,"us_phone");
    $ffur= getv($v,"us_fax");
    $mfur= getv($v,"us_mail");

    $tr[] = array($v["title"] ,$v["id"],$v["title"], $afur, $mfur, $tfur,$ffur, $sfur, $sidfur);
    
  }
  return $tr;
  
}


// identification société
function gsociety($dbaccess, $idc) {     
  //gsociety(D,US_IDSOCIETY):US_SOCIETY
  $doc = new_Doc($dbaccess, $idc);
  $cl = array($doc->title);

  return ($cl);
  }


// get enum list from society document
function enumscatg() {
  $dbaccess=getParam("FREEDOM_DB");
  $soc = new_Doc($dbaccess, 124);

  if ($soc->isAffected()) {
    $a = $soc->getAttribute("si_catg");
    return $a->phpfunc;
  }
  return "";
}

function members($dbaccess, $groupid, $name="") {
  $tr = array();

  $doc = new_Doc($dbaccess, $groupid);
  if ($doc->isAlive()) {
    $tmembers= $doc->getTvalue("GRP_RUSER");
    $tmembersid= $doc->getTvalue("GRP_IDRUSER");

    $pattern_name = preg_quote($name);
    foreach($tmembersid as $k => $v) {
      if (($name == "") || (preg_match("/$pattern_name/i",$tmembers[$k])))
	$tr[] = array($tmembers[$k] ,
		      $v,$tmembers[$k]);
    }
  }

  return $tr;
}

//get domain of IUSER
function getdomainiuser()
{
  $dbaccess=GetParam("CORE_DB");
  $tab=array();                                                 
  $domain=new Domain($dbaccess);                  
  $domain->ListAll(-1);                          
  while (list($k, $v) = each($domain->qlist)) {  
    $extmail="<"._("mail will be calculated from domain").">";
    $automail="1";
    if ($v->iddomain==1) {
      $v->name="local";
      $v->iddomain="1";
      $extmail="<"._("no mail").">";
     }
    if ($v->iddomain==0) {
      $v->name="externe";
      $v->iddomain="0";
      $extmail="";
      $automail=" ";
     } 
    $tab[$k] = array($v->name,$v->iddomain,$v->name,$extmail,$automail);  
  }                                                
  return $tab;
}

//get domain for group
function getdomainigroup()
{
  $dbaccess=GetParam("CORE_DB");
  $tab=array();                                                 
  $domain=new Domain($dbaccess);                  
  $domain->ListAll(0);                          
  while (list($k, $v) = each($domain->qlist)) {  
    
    
    $tab[$k] = array($v->name,$v->iddomain,$v->name);  
  }                                                
  return $tab;
}

//return my groups
function mygroups($name="") {   
  $dbaccess=GetParam("FREEDOM_DB");
  $docuid=doc::getUserId();
  $tr=array();
  $doc=new_Doc($dbaccess,$docuid);
  $grid=$doc->getTValue("us_idgroup");
  $grname=$doc->getTValue("us_group");

  $pattern_name = preg_quote($name);
  foreach ($grid as $k=>$v) {
      if (($name == "") || (preg_match("/$pattern_name/i",$grname[$k])))
	$tr[] = array($grname[$k] ,
		      $v,$grname[$k]);    
    }  
  return $tr; 
}

?>
