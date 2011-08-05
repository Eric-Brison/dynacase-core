<?php
/**
 * Difference between 2 documents
 *
 * @author Anakeen 2006
 * @version $Id: diffdoc.php,v 1.5 2008/08/14 09:59:14 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage 
 */
 /**
 */



include_once("FDL/Class.Dir.php");


/**
 * Compare 2 documents
 * @param Action &$action current action
 * @global id1 Http var : first document identificator to compare
 * @global id2 Http var : second document identificator to compare
 */
function diffdoc(&$action) {  
  $docid1 = GetHttpVars("id1");
  $docid2 = GetHttpVars("id2");
  if ($docid1 > $docid2) {   
    $docid2 = GetHttpVars("id1");
    $docid1 = GetHttpVars("id2");
  }
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $d1=new_doc($dbaccess,$docid1);
  $err=$d1->control("view");
  if ($err != "") $action->exitError($err);
  $d2=new_doc($dbaccess,$docid2);
  $err=$d2->control("view");
  if ($err != "") $action->exitError($err);

  if ($d1->fromid != $d2->fromid) $action->exitError(sprintf(_("cannot compare two document which comes from two different family")));

  $la=$d1->GetNormalAttributes();

  foreach ($la as $k=>$a) {

    if ($a->type=="array") {
      $v1=$d1->getAValues($a->id);
      $v2=$d2->getAValues($a->id);
      if ($v1 == $v2) $cdiff="eq";
      else $cdiff="ne";
      
    } else {
      $v1=$d1->getValue($a->id);
      $v2=$d2->getValue($a->id);
      if ($v1 == $v2) $cdiff="eq";
      else $cdiff="ne";
    }

    if ($a->visibility == "H") $vdiff="hi";
    else $vdiff=$cdiff;

    if (! $a->inArray()) {

    switch ($a->type) {
      case  "image":
      $tattr[$a->id]=array("attname"=>$a->getLabel(),
			   "v1"=>sprintf("<img src=\"%s\">",$d1->getHtmlValue($a,$v1)),
			   "v2"=>sprintf("<img src=\"%s\">",$d2->getHtmlValue($a,$v2)),
			   "cdiff"=>$cdiff,
			   "vdiff"=>$vdiff,
			   "EQ"=>($cdiff=="eq"));
      break;
    default:
      $tattr[$a->id]=array("attname"=>$a->getLabel(),
			 "v1"=>$d1->getHtmlValue($a,$v1),
			   "v2"=>$d2->getHtmlValue($a,$v2),
			   "cdiff"=>$cdiff,
			   "vdiff"=>$vdiff,
			   "EQ"=>($cdiff=="eq"));
    }
    }
  }

  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/common.js");
  $action->lay->set("document1",$d1->title);
  $action->lay->set("id1",$d1->id);
  $action->lay->set("date1",strftime ("%a %d %b %Y %H:%M",$d1->revdate));
  $action->lay->set("version1",$d1->version);
  $action->lay->set("revision1",$d1->revision);

  $action->lay->set("document2",$d2->title);
  $action->lay->set("id2",$d2->id);
  $action->lay->set("date2",strftime ("%a %d %b %Y %H:%M",$d2->revdate));
  $action->lay->set("version2",$d2->version);
  $action->lay->set("revision2",$d2->revision);

  $action->lay->set("title",sprintf(_("comparison between<br>%s (rev %d) and %s (rev %d)"),
				    $d1->title,$d1->revision,
				    $d2->title,$d2->revision));
				    
  $action->lay->setBlockData("ATTRS",$tattr);
}
?>
