<?php
/**
 * Upload image from FCKeditor
 *
 * @author Anakeen 2007
 * @version $Id: fckupload.php,v 1.3 2008/03/10 10:45:52 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage 
 */
 /**
 */



include_once("FDL/modcard.php");

/**
 * Upload image from FCKeditor
 * @param Action &$action current action
 * @global $_FILES['NewFile'] Http var : file to store
 */
function fckupload(&$action) {
  
  $dbaccess = $action->GetParam("FREEDOM_DB");
  global $_FILES;

  
  $doc=createDoc($dbaccess,"IMAGE");
  
  $k='NewFile';
  $filename=insert_file($doc,$k,true);

  if ($filename != "")  {    
      $doc->SetValue("img_file", $filename);
      $err=$doc->add();
      if ($err=="") {
	$doc->postmodify();
	$err=$doc->modify();

	$action->lay->set("docid",$doc->id);	
	$action->lay->set("title",$doc->title);
	 if (preg_match(PREGEXPFILE,$filename , $reg)) {  
	   $vid=$reg[2];
	   $action->lay->set("vid",$vid);	   
	 }
      }
  }
  
}
?>