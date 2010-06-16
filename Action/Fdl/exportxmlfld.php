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
      include_once("FDL/Class.SearchDoc.php");
/**
 * Exportation as xml of documents from folder or searches
 * @param Action &$action current action
 * @global fldid Http var : folder identificator to export
 * @global wprof Http var : (Y|N) if Y export associated profil also
 * @global wfile Http var : (Y|N) if Y export attached file export format will be tgz
 * @global wident Http var : (Y|N) if Y specid column is set with identificator of document
 * @global eformat Http var :  (X|Y) I:  Y: only one xml, X: zip by document with files
 * @global selection Http var :  JSON document selection object
 */
function exportxmlfld(&$action, $aflid="0", $famid="") {    
  if (ini_get("max_execution_time") < 3600) ini_set("max_execution_time",3600); // 60 minutes
    
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $fldid = GetHttpVars("id",$aflid);
  $wprof = (GetHttpVars("wprof","N")=="Y"); // with profil
  $wfile = (substr(strtolower(GetHttpVars("wfile","N")),0,1)=="y"); // with files
  $wident = (substr(strtolower(GetHttpVars("wident","Y")),0,1)=="y"); // with numeric identificator
  
  $nopref = (GetHttpVars("wcolumn")=="-"); // no preference read
  $eformat = GetHttpVars("eformat","X"); // export format 
  $selection = GetHttpVars("selection"); // export selection  object (JSON)


  if ((! $fldid) && $selection) {
      $selection=json_decode($selection);
      include_once("DATA/Class.DocumentSelection.php");
      $os=new Fdl_DocumentSelection($selection);
      $ids=$os->getIdentificators();     
      $s=new SearchDoc($dbaccess);
      
      $s->addFilter(getSqlCond($ids,"id",true));
      $s->setObjectReturn();
      $exportname="selection";       
  } else {
      if (! $fldid) $action->exitError(_("no export folder specified"));

      $fld = new_Doc($dbaccess, $fldid);
      if ($famid=="") $famid=GetHttpVars("famid");
      $exportname=str_replace(array(" ","'",'/'),array("_","","-"),$fld->title);
      //$tdoc = getChildDoc($dbaccess, $fldid,"0","ALL",array(),$action->user->id,"TABLE",$famid);
      
      $s=new SearchDoc($dbaccess,$famid);      
      $s->setObjectReturn();
      
      $s->dirid=$fldid;
      
      
  }
  
  $s->search();
  $err=$s->searchError();
  if ($err) $action->exitError($err);

  $foutdir = uniqid("/var/tmp/exportxml");
  if (! mkdir($foutdir)) $action->exitError(sprintf("cannot create directory %s",$foutdir));
  //$fname=sprintf("%s/FDL/Layout/fdl.xsd",DEFAULT_PUBDIR);
  //copy($fname,"$foutdir/fdl.xsd");
  $xsd=array();
  while ($doc=$s->nextDoc()) {
      //print $doc->exportXml();
      if ($doc->doctype != 'C') {
          $ftitle= str_replace(array('/','\\','?','*',':'),'-',$doc->getTitle());
          $fname=sprintf("%s/%s{%d}.xml",$foutdir,$ftitle,$doc->id);
          $err=$doc->exportXml($xml,$wfile,$fname,$wident);
         // file_put_contents($fname,$doc->exportXml($wfile));
          if ($err) $action->exitError($err);
          if (! isset($xsd[$doc->fromid])) {
              $fam=new_doc($dbaccess,$doc->fromid);
              $fname=sprintf("%s/%s.xsd",$foutdir,strtolower($fam->name));
              file_put_contents($fname,$fam->getXmlSchema());
              $xsd[$doc->fromid]=true;

          }
      }
  }

  if ($eformat=="X") {
      $zipfile = uniqid("/var/tmp/xml").".zip";
      system("cd $foutdir && zip -r $zipfile * > /dev/null",$ret);
      if (is_file($zipfile)) {
          system("rm -fr $foutdir");
          Http_DownloadFile($zipfile, "$exportname.zip", "application/x-zip",false,false,true);
      } else {
          $action->exitError(_("Zip Archive cannot be created"));
      }
  } elseif  ($eformat=="Y") {
      $xmlfile = uniqid("/var/tmp/xml").".xml";
      $cmde=array();
      $cmde[]="cd $foutdir";
      $cmde[]=sprintf("echo '<?xml version=\"1.0\" encoding=\"UTF-8\"?>' > %s",$xmlfile);
      $cmde[]=sprintf("echo '<documents date=\"%s\" author=\"%s\" name=\"%s\">' >> %s",
                      strftime("%FT%T"),User::getDisplayName($action->user->id),$exportname,$xmlfile);
      $cmde[]="cat *xml | grep -v '<?xml version=\"1.0\" encoding=\"UTF-8\"?>' >> $xmlfile";
      $cmde[]="echo '</documents>' >> $xmlfile";
      system(implode(" && ",$cmde),$ret);
      if (is_file($xmlfile)) {
          system("rm -fr $foutdir");
          Http_DownloadFile($xmlfile, "$exportname.xml", "text/xml",false,false,true);
      } else {
          $action->exitError(_("Xml file cannot be created"));
      }
  } 


}

?>
