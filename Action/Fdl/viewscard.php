<?php
/**
 * View standalone document (without popup menu)
 *
 * @author Anakeen 2000 
 * @version $Id: viewscard.php,v 1.8 2005/11/04 15:38:29 marc Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: viewscard.php,v 1.8 2005/11/04 15:38:29 marc Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Fdl/viewscard.php,v $
// ---------------------------------------------------------------

include_once("FDL/Class.Doc.php");


/**
 * View a document without standard header and footer. It is display in raw format
 * @param Action &$action current action
 * @global id Http var : document identificator to see
 * @global latest Http var : (Y|N) if Y force view latest revision
 * @global abstract Http var : (Y|N) if Y view only abstract attribute
 * @global zonebodycard Http var : if set, view other specific representation
 * @global vid Http var : if set, view represention describe in view control (can be use only if doc has controlled view)
 * @global ulink Http var : (Y|N)if N hyperlink are disabled
 * @global target Http var : is set target of hyperlink can change (default _self)
 */
function viewscard(&$action) {

  // GetAllParameters
  $docid = GetHttpVars("id");
  $abstract = (GetHttpVars("abstract",'N') == "Y");// view doc abstract attributes
  $zonebodycard = GetHttpVars("zone"); // define view action
  $ulink = (GetHttpVars("ulink",'Y') == "Y"); // add url link
  $target = GetHttpVars("target"); // may be mail
  $wedit = (GetHttpVars("wedit")=="Y"); // send to be view by word editor
  $fromedit = (GetHttpVars("fromedit","N")=="Y"); // need to compose temporary doc
  $latest = GetHttpVars("latest");
  $tmime = GetHttpVars("tmime", "");  // type mime
  $charset = GetHttpVars("chset", "UTF-8");  // charset

  // Set the globals elements

  $dbaccess = $action->GetParam("FREEDOM_DB");

  $doc = new_Doc($dbaccess, $docid);
  if (($latest == "Y") && ($doc->locked == -1)) {
    // get latest revision
    $docid=$doc->latestId();
    $doc = new_Doc($dbaccess, $docid);
    SetHttpVar("id",$docid);
  } 
  $err = $doc->control("view");
  if ($err != "") $action->exitError($err);
  if ($fromedit) {
    include_once("FDL/modcard.php");

    $doc = $doc->copy(true,false,true);
    $err=setPostVars($doc);
    $doc->modify();
    setHttpVar("id",$doc->id);
  } 
  if ($zonebodycard == "") $zonebodycard=$doc->defaultview;
  if ($zonebodycard == "") $action->exitError(_("no zone specified"));


  $err=$doc->refresh();
  $action->lay->Set("ZONESCARD", $doc->viewDoc($zonebodycard,$target,$ulink,$abstract));
  
 

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/geometry.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");


  if ($wedit) {
    $export_file = uniqid(getTmpDir()."/export").".doc";
  
    $of = fopen($export_file,"w+");
    fwrite($of, $action->lay->gen());
    fclose($of);
  
    http_DownloadFile($export_file, chop($doc->title).".html", "application/msword");
  
    unlink($export_file);
    exit;
  }

  if ($tmime!="") {
    header("Content-Type: $tmime; charset=$charset");
    print $action->lay->gen();
    exit;
  }

}
?>
