<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: viewacl.php,v 1.5 2007/03/12 17:38:41 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */

// ---------------------------------------------------------------
// $Id: viewacl.php,v 1.5 2007/03/12 17:38:41 eric Exp $
// $Source: /home/cvsroot/anakeen/freedom/freedom/Zone/Fdl/viewacl.php,v $
// ---------------------------------------------------------------
// ---------------------------------------------------------------

include_once("FDL/Class.Doc.php");


// -----------------------------------
function viewacl(&$action) {
// ------------------------

  $docid= intval(GetHttpVars("docid")) ;
  $userid= intval(GetHttpVars("userid")) ;

  $action->lay->Set("docid",$docid);
  $action->lay->Set("userid",$userid);


  $action->parent->AddJsRef($action->GetParam("CORE_PUBURL")."/FDL/Layout/viewacl.js");




  $dbaccess = $action->GetParam("FREEDOM_DB");

  $doc=new_Doc($dbaccess, $docid);

  //-------------------

  
  $perm = new DocPerm($dbaccess, array($docid,$userid));
  
  
  $acls = $doc->acls;
  $acls[]="viewacl";
  $acls[]="modifyacl"; //add this acl global for every document
  $tableacl= array();

  reset($acls);
  while(list($k,$v) = each($acls) ) {
      $tableacl[$k]["aclname"]=_($v);
      $tableacl[$k]["acldesc"]=" ("._($doc->dacls[$v]["description"]).")";

      $pos=$doc->dacls[$v]["pos"];

      $tableacl[$k]["aclid"]=$pos;
      $tableacl[$k]["iacl"]=$k; // index for table in xml
     
      if ($perm->ControlU($pos)) {
	    $tableacl[$k]["selected"]="checked";
      } else {
	    $tableacl[$k]["selected"]="";
      }
      if ($perm->ControlUn($pos)) {
	    $tableacl[$k]["selectedun"]="checked";
      } else {
	    $tableacl[$k]["selectedun"]="";
      } 
      if ($perm->ControlUp($pos)) {	
	    $tableacl[$k]["selectedup"]="checked";
      } else {
	    $tableacl[$k]["selectedup"]="";
      }
      if ($perm->ControlG($pos)) {	
	    $tableacl[$k]["selectedg"]="checked";
      } else {
	    $tableacl[$k]["selectedg"]="";
      }

    }

    $action->lay->SetBlockData("SELECTACL",$tableacl); 





}

?>
