<?php
/**
 * Import document from CSV file
 *
 * @author Anakeen 2004
 * @version $Id: generic_editimport.php,v 1.19 2008/08/14 09:59:14 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage 
 */
 /**
 */




include_once("FDL/Class.Dir.php");
include_once("GENERIC/generic_util.php");  

// -----------------------------------
function generic_editimport(&$action) {
  // -----------------------------------

  global $dbaccess;
  $allcol = (GetHttpVars("allcol","N")=="Y"); // special controlled view
  
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/subwindow.js");
  $action->parent->AddJsRef($action->GetParam("CORE_JSURL")."/selectbox.js");
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $homefld = new_Doc( $dbaccess, getDefFld($action));


  
  $stree=array();
  if ($homefld->id > 0)  $stree=getChildCatg($homefld->id, 1,true,4);

  reset($stree);
  
  $action->lay->SetBlockData("CATG",$stree);
  $action->lay->Set("topdir", getDefFld($action));
  
  $famid = getDefFam($action);


  // spec for csv file
  $doc=new_Doc($dbaccess, $famid);
  $famid=$doc->id;
  if ($doc->name != "") $famname=$doc->name;
  else $famname=$doc->id;
  if ($doc->ccvid > 0) {
    // special controlled view
    $cvdoc= new_Doc($dbaccess, $doc->ccvid);
    $cvid=$cvdoc->getValue("CV_IDCVIEW");
    if ($cvid) {
      $err = $cvdoc->control($cvid); // control special view
      if ($err != "") $action->exitError($err);
      $tview = $cvdoc->getView($cvid);
      if (isset($tview["CV_MSKID"]))    $doc->setMask($tview["CV_MSKID"]);
    }
    
  }
  

  $action->lay->Set("TITLE",$doc->title);

  $action->lay->Set("dtitle",sprintf(_("import <I>%s</I> documents from"),$doc->title));
  if ($allcol) {
    $lattr = $doc->GetNormalAttributes();
    $action->lay->Set("dallcol","");
    $action->lay->Set("dnallcol","none");
  }else {
    $lattr = $doc->GetImportAttributes();
    $action->lay->Set("dallcol","none");
    $action->lay->Set("dnallcol","");
  }
  $format = "DOC;".$famname.";0;". getDefFld($action)."; ";

  foreach ($lattr as $k=>$attr) {
    $format .= $attr->getLabel()." ;";
  }
  $lattr = $doc->GetNormalAttributes();
  foreach ($lattr as $k=>$attr) {
    if ($attr->visibility =="O") continue; // only valuated attribut
    $tkey[]=array("idattr"=>$attr->id,
		  "lattr"=>$attr->getLabel());
  }
  if ($allcol) $lattr = $doc->GetNormalAttributes();
  else $lattr = $doc->GetImportAttributes();
  foreach ($lattr as $k=>$attr) {
    $tcol[]=array("idattr"=>$attr->id,
		  "lattr"=>$attr->getLabel());
  }

  $action->lay->SetBlockData("AKEYS1",$tkey);
  $action->lay->SetBlockData("AKEYS2",$tkey);
  $action->lay->SetBlockData("COLUMNS",$tcol);
  $action->lay->Set("format",$format);
  $action->lay->Set("classid",$famid);
  $action->lay->Set("classname",$famname);

}


?>
