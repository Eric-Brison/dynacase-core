<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: fdl_pkey.php,v 1.1 2003/11/03 09:12:49 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FREEDOM
 * @subpackage 
 */
 /**
 */



// refreah for a classname
// use this only if you have changed title attributes


include_once("FDL/Class.DocFam.php");




$appl = new Application();
$appl->Set("FDL",	   $core);


$dbaccess=$appl->GetParam("FREEDOM_DB");
if ($dbaccess == "") {
  print "Database not found : param FREEDOM_DB";
  exit;
}



$docid = GetHttpVars("docid",0); // special docid


	
$query = new QueryDb($dbaccess,"DocFam");
$query->AddQuery("doctype='C'");
  
if ($docid > 0) $query->AddQuery("id=$docid");
      
    
$table1 = $query->Query(0,0,"TABLE");

     
if ($query->nb > 0)	{



  while(list($k,$v) = each($table1))   {	     

    
    print "alter TABLE doc".$v["id"]." ADD primary key (id);\n";

    
    
  }	 
  
}      
    

?>