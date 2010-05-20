<?php
/**
 * View document only - without any menu
 *
 * @author Anakeen 2000 
 * @version $Id: impcard.php,v 1.11 2008/02/08 09:50:26 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Doc.php");


function impcard(&$action) {

  // GetAllParameters

  $mime = GetHttpVars("mime"); // send to be view by word editor
  $ext = GetHttpVars("ext","html"); // extension
  $docid = GetHttpVars("id");
  $zonebodycard = GetHttpVars("zone"); // define view action
  $valopt=GetHttpVars("opt"); // value of  options
  $vid = GetHttpVars("vid"); // special controlled view
  $state = GetHttpVars("state"); // search doc in this state
  $inline=getHttpVars("inline"=="Y"); // view file inline
  $latest = GetHttpVars("latest");

  $szone=false;

  $dbaccess = $action->GetParam("FREEDOM_DB");

  if ($valopt != "") {
    include_once("FDL/editoption.php");
    $doc=getdocoption($action);
    $docid=$doc->id;
  } else {
    $doc = new_Doc($dbaccess, $docid);
  }

  if ($state != "") {
    $docid=$doc->getRevisionState($state,true);
    if ($docid==0) {
      $action->exitError(sprintf(_("Document %s in %s state not found"),
				 $doc->title,_($state)));
    }
    SetHttpVar("id",$docid);
  } else {
    if (($latest == "Y") && ($doc->locked == -1)) {
      // get latest revision
      $docid=$doc->latestId();
      SetHttpVar("id",$docid);
    } else if (($latest == "L") && ($doc->lmodify != 'L')) {
      // get latest fixed revision
      $docid=$doc->latestId(true);
      SetHttpVar("id",$docid);
    } else if (($latest == "P") && ($doc->revision > 0)) {
      // get previous fixed revision
      $pdoc = getRevTDoc($dbaccess, $doc->initid,$doc->revision-1);
      $docid=$pdoc["id"];
      SetHttpVar("id",$docid);
    }
  }

  $action->lay->set("TITLE",$doc->title);  
  if (($zonebodycard=="") && ($vid != "")) {
    $cvdoc= new_Doc($dbaccess, $doc->cvid);
    $tview = $cvdoc->getView($vid);
    $zonebodycard=$tview["CV_ZVIEW"];
  }
  if ($zonebodycard == "") $zonebodycard=$doc->defaultview;
  if ($zonebodycard == "") $zonebodycard="FDL:VIEWCARD";


  $zo=$doc->getZoneOption($zonebodycard);
  if ($zo=="B") {
    // binary layout file
    $ulink=false;
    $target="ooo";
    $file=$doc->viewdoc($zonebodycard,$target,$ulink);
    Http_DownloadFile($file,$doc->title.".odt",'application/vnd.oasis.opendocument.text',$inline,false);
    @unlink($file);
    exit;
  }

  if ($zo=='S')  $szone=true;// the zonebodycard is a standalone zone ?
  $action->lay->set("nocss",($zo=="U"));
  if ($szone) {
    // change layout
    include_once("FDL/viewscard.php");
    $action->lay = new Layout(getLayoutFile("FDL","viewscard.xml"),$action);
    viewscard($action); 
    
  }

  if ($mime != "") {
    $export_file = uniqid("/tmp/export").".$ext";
  
    $of = fopen($export_file,"w+");
    fwrite($of, $action->lay->gen());
    fclose($of);
  
    http_DownloadFile($export_file, chop($doc->title).".$ext", "$mime",false,false);
  
    unlink($export_file);
    exit;
  }
}


?>
