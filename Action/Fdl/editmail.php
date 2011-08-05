<?php
/**
 * Edition to send mail
 *
 * @author Anakeen 2000 
 * @version $Id: editmail.php,v 1.21 2008/10/16 13:57:35 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */


include_once("FDL/Class.Doc.php");

/**
 * Edition to send mail
 * @param Action &$action current action
 * @global mid Http var : document id to send
 * @global mzone Http var : view zone to use to send mail
 * @global ulink Http var : with hyperlink (to use in internal) [Y|N]
 * @global dochead Http var : with header (icon/title) or not [Y|N]
 * @global viewdoc Http var : with preview of sended mail [Y|N]
 * @global mail_to Http var : recipient mail
 * @global mail_cc Http var : recipient copy
 * @global mail_subject Http var : subject
 */
function editmail(&$action) {
  $docid = GetHttpVars("mid"); 
  $zone = GetHttpVars("mzone"); 
  $ulink = GetHttpVars("ulink"); 
  $dochead = GetHttpVars("dochead"); 
  $viewdoc = (GetHttpVars("viewdoc","Y")=="Y"); 

  $from = GetHttpVars("_mail_from","");
  $to = GetHttpVars("mail_to");
  $cc = GetHttpVars("mail_cc");

  // for compliance with old notation
  $ts=array();
  $tt=array();
  if ($to != "") {
    $to=str_replace("\n",",",$to);
    $to=str_replace('\n',",",$to);
    $ts=explode(",",$to);
    $tt=array_fill(0,count($ts),"to");
  }
  if ($cc != "") {
    $ts=array_merge($ts,explode(",",$cc));
    $tt=array_merge($tt,array_fill(0,count(explode(",",$cc)),"cc"));
  }
  setHttpVar("mail_recip",$ts);
  setHttpVar("mail_copymode",$tt);

  
  if ($from == "") {
    $from=getMailAddr($action->user->id, true);    
  }
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $doc = new_Doc($dbaccess, $docid);
  

  // control sending
  $err=$doc->control('send');
  if ($err != "") $action->exitError($err);

  if ($zone=="") $zone=$doc->defaultmview;
  $zo=$doc->getZoneOption("$zone");
  
  $action->lay->Set("binarymode",($zo=="B"));
  if ($zo=="B") {
    $engine=$doc->getZoneTransform($zone);
    if ($engine=="pdf")  $action->lay->Set("iconmime",$action->getImageUrl("mime-pdf.png"));
    else $action->lay->Set("iconmime",$action->getImageUrl("mime-document2.png"));
  }

  $action->lay->Set("from",$from);
  $action->lay->Set("mid",$docid);
  $action->lay->Set("ulink",$ulink);
  $action->lay->Set("mzone",$zone);
  $action->lay->Set("dochead",$dochead);
  $action->lay->Set("title",$doc->title);
  $action->lay->set("VIEWDOC",$viewdoc);
  
}
?>