<?php
/**
 * Import VCARD files
 *
 * @author Anakeen 2000 
 * @version $Id: usercard_importvcard.php,v 1.16 2005/11/23 14:03:50 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage USERCARD
 */
 /**
 */





include_once("FDL/Class.Dir.php");
include_once("FDL/Class.UsercardVcard.php");
include_once("GENERIC/generic_util.php");


function usercard_importvcard(&$action) {
  global $_FILES;

  // Get all the params      
  $id=GetHttpVars("id");
  $dbaccess = $action->GetParam("FREEDOM_DB");
  $policy = GetHttpVars("policy","add"); 
  $category = GetHttpVars("category"); 
  $privacity = GetHttpVars("privacity","R"); 


  $action->lay->Set("CR","");
  $vcard_import = new UsercardVcard();

  if (isset($_FILES["vcardfile"]))    
    {
      // importation 
      $vcardfile = $_FILES["vcardfile"]["tmp_name"];
      
    } else {      
    $vcardfile = GetHttpVars("file"); 
  }
  if (! $vcard_import-> Open($vcardfile)) $action->exitError(_("no vcard file specified"));

  $tvalue=array();

  $tabadd = array(); // memo each added person
  $tabdel = array(); // memo each deleted person
  while ( $vcard_import-> Read($tvalue))
    {
	 
      if (count($tvalue) > 0)
	{
	  // Add new contact card
	  $doc = createDoc($dbaccess, "USER" );

	  if (! $doc) $action->exitError(sprintf(_("no privilege to create this kind (%d) of document"),"USER"));
	      


	      
	  $doc->Add();



	  // set privacity
	  $doc->setvalue("US_PRIVCARD",$privacity);
	      
	  while(list($k,$v) = each($tvalue)) 
	    {
	      $doc->setvalue($k,$v);
	    }
	  $doc->refresh();
	  $doc->Modify();
	      

	  // add in each selected category
	  if (is_array($category)) {
	    reset($category);
		
	    while(list($k,$v) = each($category)) {
		  
	      $catg = new_Doc($dbaccess, $v);
	      $catg->AddFile($doc->id);
	    }
	  }
	  // duplicate policy
	  
	  switch ($policy)
	    {
	    case "add":
	      $doc->PostModify();
	      $tabadd[] = array("id"=>$doc->id,
				"title"=>$doc->title);
	      break;
	    case "update":


	      $doc->PostModify();
	      $tabadd[] = array("id"=>$doc->id,
				"title"=>$doc->title);
	      $ldoc = $doc->GetDocWithSameTitle();
	      while(list($k,$v) = each($ldoc)) {
		$err = $v->delete(); // delete all double (if has permission)
		$tabdel[] = array("id"=>$v->id,
				  "title"=>$v->title);
	      }	
	      break;
	    case "keep":
	      $ldoc = $doc->GetDocWithSameTitle();
	      if (count($ldoc) ==  0) {
		$doc->PostModify();
		$tabadd[] = array("id"=>$doc->id,
				  "title"=>$doc->title);
	      } else {
		// delete the new added doc
		$doc->delete();
	      }
	      break;
	    }


	}

    }
  $vcard_import-> Close();


  $action->lay->SetBlockData("ADDED",$tabadd);
  $action->lay->SetBlockData("DELETED",$tabdel);
    
}


?>
