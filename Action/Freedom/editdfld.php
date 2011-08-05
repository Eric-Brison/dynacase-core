<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: editdfld.php,v 1.8 2008/11/27 14:18:33 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage GED
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: editdfld.php,v 1.8 2008/11/27 14:18:33 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Action/Freedom/editdfld.php,v $
// ---------------------------------------------------------------


include_once("FDL/Lib.Dir.php");


function editdfld(&$action) {
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $docid = GetHttpVars("id",0);
  $firstfld = (GetHttpVars("current","N")=="Y");
  


  $action->lay->Set("docid",$docid);

  $action->lay->Set("TITLE",_("change root folder"));
 


  $doc= new_Doc($dbaccess,$docid);

  $action->lay->Set("doctitle",$doc->title);
  $sqlfilters=array();
  if ($firstfld) {
    $fldid=$doc->cfldid;
    $action->lay->Set("TITLE",_("Change default search"));
    $action->lay->Set("current","Y");

    $tclassdoc = getChildDoc($dbaccess,$doc->dfldid,"0","ALL",$sqlfilters, $action->user->id, "TABLE",5);
    //$tclassdoc = array_merge($tclassdoc,getChildDoc($dbaccess,$doc->dfldid,"0","ALL",$sqlfilters, $action->user->id, "TABLE",2));
  } else {
    $fldid=$doc->dfldid;
    $action->lay->Set("TITLE",_("change root folder"));
    $action->lay->Set("current","N");
    $sqlfilters[]="doctype='D'";
    $tclassdoc = getChildDoc($dbaccess,0,"0","ALL",$sqlfilters, $action->user->id, "TABLE",2);
  }

  $selectclass=array();
  if (is_array($tclassdoc)) {
    while (list($k,$pdoc)= each ($tclassdoc)) {
     
      $selectclass[$k]["idpdoc"]=$pdoc["id"];
      $selectclass[$k]["profname"]=$pdoc["title"];
	
      $selectclass[$k]["selected"]=($pdoc["id"]==$fldid)?"selected":"";
      
    }
  }


  $action->lay->Set("autodisabled",$firstfld||($fldid>0)?"disabled":"");
  $action->lay->Set("ROOTFOLDER",(!$firstfld));
  
  $action->lay->SetBlockData("SELECTFLD", $selectclass);
	  
      
    
}




?>
