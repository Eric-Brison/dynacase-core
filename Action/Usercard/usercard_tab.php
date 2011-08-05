<?php
/**
 * State document edition
 *
 * @author Anakeen 2000
 * @version $Id: usercard_tab.php,v 1.7 2005/05/19 14:38:44 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage
 */
 /**
 */
                                                                                                                                                             
include_once("FDL/Class.WDoc.php");                                                                                                                                      
include_once("Class.QueryDb.php");
include_once("FDL/freedom_util.php");
include_once("FDL/editutil.php");
include_once("FDL/editcard.php");
                                                                                                                                                             
// -----------------------------------
function usercard_tab(&$action) {  
   $dbaccess = $action->GetParam("FREEDOM_DB");
   $sdoc = createDoc($dbaccess,5,false); //new DocSearch($dbaccess);
   $sdoc->doctype = 'T';// it is a temporary document (will be delete after)
   $sdoc->Add();
  
   $default=GetHttpVars("default","Y");
   $fam ="USER";
  
   if ($default=="N"){
     $fam=GetHttpVars("family");
     }
  
   $famid = getFamIdFromName($dbaccess,$fam);
   // $sdoc->title=sprintf(_("%s"),getTitle($famid));
   $sdoc->title=_($fam);
   $sqlfilter[]= "(fromid=$famid)";
   

   //filters for USER or IUSER
   if (($fam=="USER" or $fam=="IUSER") and $default=="N"){
     //criteres
     $contact=GetHttpVars("contact");     
     $Tcontact=explode(" ",$contact);
     $ch="";
     foreach ($Tcontact as $k=>$v)
       {if ($ch<>"") $ch.=" and ";
       $ch.=" title ~* '$Tcontact[$k]'";
       }
     if ($contact<>"" ) $sqlfilter[]= "$ch or us_mail ~* '$contact' ";

     $society=GetHttpVars("society");
     $Tsoc=explode(" ",$society);
     $ch="";
     foreach ($Tsoc as $k=>$v)
       {if ($ch<>"") $ch.=" and ";
       $ch.=" us_society ~* '$Tsoc[$k]'";
       }
     if ($society<>"" ) $sqlfilter[]= "$ch";
     

     $private=GetHttpVars("private");      
     if ($private==1) $sqlfilter[]= "us_privcard='P' ";


     //details
     $allcond=GetHttpVars("allcond");
     if ($allcond==1) $op="and";
     else $op="or"; 	
     
     $mail=GetHttpVars("mail");
     $phone=GetHttpVars("phone");
     $pphone=GetHttpVars("pphone");
     $mobile=GetHttpVars("mobile");
     $adr=GetHttpVars("adr");
     $postalcode=GetHttpVars("postalcode");
     $town=GetHttpVars("town");
     $country=GetHttpVars("country");
     $function=GetHttpVars("function");
     $catg=GetHttpVars("catg");
     
     $sql=array();
     if ($mail<>"") $sql[]= " us_mail ~* '$mail'" ;
     if ($phone<>"") $sql[]=" us_phone ~* '$phone'";
     if ($pphone<>"") $sql[]=" us_pphone ~* '$pphone'";
     if ($mobile<>"") $sql[]=" us_mobile ~* '$mobile'";
     if ($adr<>"") $sql[]=" us_workaddr ~* '$adr'";
     if ($postalcode<>"") $sql[]=" us_workpostalcode ~* '$postalcode'";
     if ($town<>"") $sql[]=" us_worktown ~* '$town'";
     if ($country<>"" ) $sql[]= "us_country ~* '$country'";
     if ($function<>"" ) $sql[]="us_type ~* '$function'";
     if ($catg<>"" ) $sql[]="us_scatg ~* '$catg'";

     $ch="";
     foreach ($sql as $k=>$v){
       if ($ch<>"") $ch.=" $op ";
       $ch.=$sql[$k];
     }

     if ($ch<>"") $sqlfilter[]=$ch;   
   }

 

   //filters for SOCIETY 
   if ($fam=="SOCIETY" and $default=="N"){
     //criteres
     $society=GetHttpVars("society");   
     $Tsoc=explode(" ",$society);
     $ch="";
     foreach ($Tsoc as $k=>$v)
       {if ($ch<>"") $ch.=" and ";
       $ch.=" si_society ~* '$Tsoc[$k]'";
       }
     if ($society<>"" ) $sqlfilter[]= "$ch";


     //details
     $allcond=GetHttpVars("allcond");
     if ($allcond==1) $op="and";
     else $op="or"; 		

     $phone=GetHttpVars("phone");
     $adr=GetHttpVars("adr");
     $catg=GetHttpVars("catg");
     $postalcode=GetHttpVars("postalcode");
     $town=GetHttpVars("town");
     $country=GetHttpVars("country");



     $sql=array();     
     if ($phone<>"") $sql[]=" si_phone ~* '$phone'";
     if ($adr<>"") $sql[]=" si_addr ~* '$adr'";
     if ($postalcode<>"") $sql[]=" si_postalcode ~* '$postalcode'";
     if ($town<>"") $sql[]=" si_town ~* '$town'";
     if ($country<>"" ) $sql[]= " si_country ~* '$country'";
     if ($catg<>"" ) $sql[]="si_catg ~* '$catg'";

     $ch="";
     foreach ($sql as $k=>$v){
       if ($ch<>"") $ch.=" $op ";
       $ch.=$sql[$k];
     }

     if ($ch<>"") $sqlfilter[]=$ch;
  
    
     }





   //REQUETE
    $query=getSqlSearchDoc($dbaccess,
                         $sdirid,
                         $famid,
                         $sqlfilter);
                                                                                                                                                             
    $sdoc-> AddQuery($query);                                                                     
    redirect($action,"FREEDOM","FREEDOM_LISTDETAIL&dirid=".$sdoc->id."&catg=0");

}                                                                                                                                                             
?>