<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: Method.DocFile.php,v 1.10 2008/03/10 10:45:52 eric Exp $
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
Class _FILE extends Doc {
        /*
         * @end-method-ignore
         */
  
  
//var $defaultview= "FDL:VIEWFILECARD";
		     

// -----------------------------------
function viewfilecard($target="_self",$ulink=true,$abstract=false) {
  // -----------------------------------


  $nbimg=0;// number of image


  $this->viewattr($target,$ulink,$abstract);
  $listattr[] = $this->GetAttribute("FI_FILE");


  $tableimage=array();
  $vf = newFreeVaultFile($this->dbaccess);

  // view all (and only) images

  while (list($i,$attr) = each($listattr)) {

  
    $value = chop($this->GetValue($attr->id));

    //------------------------------
    // Set the table value elements
      
    if (($value != "") && ($attr->visibility != "H"))	{
		

      // print values
      switch ($attr->type)   {
	      
      case "file": 
		  
	$tableimage[$nbimg]["imgsrc"]=$this->GetHtmlValue($attr,$value,$target,$ulink);
      if (preg_match(PREGEXPFILE, $value, $reg)) {		 
	// reg[1] is mime type
	$tableimage[$nbimg]["type"]=$reg[1];
	if ($vf -> Show ($reg[2], $info) == "") {
	  $fname = $info->name;
	  $tableimage[$nbimg]["size"]=round($info->size / 1024,2);
	}
	else $fname=_("no filename");

	$tableimage[$nbimg]["name"]=$fname;
      }

      break;
		
		
	
		
      }	      
	    
    }
  
  }

  // Out


  $this->lay->SetBlockData("TABLEFILE",	 $tableimage);

}


function PostModify() {
  $filename=$this->vault_filename("FI_FILE");
  /* to not view extension file
  $pos = strrpos($filename , ".");
  if ($pos !== false) { 
    $filename=substr($filename,0,$pos);
  }
  */
  if ($this->getValue("FI_TITLEW")=="")  $this->SetValue("FI_TITLE",$filename);
      else $this->SetValue("FI_TITLE",$this->getValue("FI_TITLEW"));

}/**
        * @begin-method-ignore
        * this part will be deleted when construct document class until end-method-ignore
        */
}

/*
 * @end-method-ignore
 */
?>
