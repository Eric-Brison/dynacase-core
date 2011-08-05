<?php
/**
 * Image document
 *
 * @author Anakeen 2000 
 * @version $Id: Method.DocImg.php,v 1.8 2008/03/10 10:45:52 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



  
/**
 * @begin-method-ignore
 * this part will be deleted when construct document class until end-method-ignore
 */
Class _IMAGE extends Doc {
        /*
         * @end-method-ignore
         */
  
var $defaultview= "FDL:VIEWIMGCARD";
	


var $cviews=array("FDL:VIEWIMGCARD:T");

// -----------------------------------
function viewimgcard($target="_self",$ulink=true,$abstract=false) {
  // -----------------------------------


  $nbimg=0;// number of image

  $this->viewattr($target,$ulink,$abstract);
  $this->viewprop($target,$ulink,$abstract);

  $listattr = $this->GetNormalAttributes();

  $tableimage=array();
  $vf = newFreeVaultFile($this->dbaccess);

  // view all (and only) images

  while (list($i,$attr) = each($listattr)) {

   
    $value = chop($this->GetValue($i));

    //------------------------------
    // Set the table value elements
      
    if (($value != "") && ($attr->visibility != "H"))	{
		

      // print values
      switch ($attr->type)   {
	      
      case "file": 
		  

      case "image": 
		  
	$tableimage[$nbimg]["imgsrc"]=$this->GetHtmlValue($attr,$value,$target,$ulink);
	if (preg_match(PREGEXPFILE, $value, $reg)) {		 
	  // reg[1] is mime type
	  $tableimage[$nbimg]["type"]=$reg[1];
	  if ($vf -> Show ($reg[2], $info) == "") $fname = $info->name;
	  else $fname=_("no filename");
	  $tableimage[$nbimg]["name"]=$fname;
	}
	break;
		
		
	
		
      }	      
	    
    }
  
  }

  // Out
  $this->lay->SetBlockData("TABLEIMG",	 $tableimage);

}


function PostModify() {
  $this->SetValue("IMG_TITLE",$this->vault_filename("IMG_FILE"));
}
/**
        * @begin-method-ignore
        * this part will be deleted when construct document class until end-method-ignore
        */
}

/*
 * @end-method-ignore
 */
?>