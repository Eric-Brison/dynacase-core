<?php
/**
 * Generated Header (not documented yet)
 *
 * @author Anakeen 2000 
 * @version $Id: migr_sql2.0.php,v 1.4 2005/06/28 08:37:46 eric Exp $
 * @license http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU Affero General Public License
 * @package FDL
 * @subpackage 
 */
 /**
 */



// refreah for a classname
// use this only if you have changed title attributes

include_once("FDL/Class.Doc.php");
include_once("FDL/Class.DocFam.php");

function migrTables() {


  $appl = new Application();
  $appl->Set("FDL",	   $core);


  $dbaccess=$appl->GetParam("FREEDOM_DB");
  if ($dbaccess == "") {
    print "Database not found : param FREEDOM_DB";
    exit;
  }




	
  
  $query = new QueryDb($dbaccess,"Doc");
  $query->AddQuery("doctype='C'");
  $query->order_by="fromid";
      
    
  $table1 = $query->Query();

     
  $docf = new DocFam($dbaccess);



  if ($query->nb > 0)	{

    $doc = new_Doc($dbaccess);
    $fields = implode(",",$doc->fields);
    //-------------------------
    // first part sql familly table
    //print $docf->sqlcreate;
    //print "insert into docfam ($fields)  select * from only doc where  doctype='C';\n";
    //print "delete from only doc where  doctype='C';\n";
    //-------------------------
    // second part sql table
    while(list($k,$v) = each($table1)) 	    {	  
      $qattr = new QueryDb($dbaccess,"DocAttr");
      $qattr->AddQuery("docid=".$v->id);   
      $qattr->AddQuery("visibility != 'F'");   
      $qattr->AddQuery("visibility != 'M'");   
      $lattr=$qattr->Query();
      if (! $lattr) $lattr=array();
      $attr=array();
      while (list($ka,$va) = each($lattr)) {
	$attr[] = "$va->id text";    	  
      }
      $sattr= implode(",",$attr);
      if ($v->fromid == 0) {
	//print "create table doc{$v->id} ($sattr) inherits (doc);\n";
      } else {
	//print "create table doc{$v->id} ($sattr) inherits (doc{$v->fromid});\n";
      }
      //   print "create unique index doc_pkey{$v->id} on doc{$v->id}(id);\n";
    
   
      print "insert into doc{$v->id} ($fields) select * from only doc where fromid = {$v->id} and doctype !='C';\n";
      print "delete from only doc where fromid = {$v->id} and doctype !='C';\n";
      reset($lattr);
      $attr=array();

      // with all attributes
      $qattr = new QueryDb($dbaccess,"DocAttr");
      $qattr->AddQuery("visibility != 'M'"); 
      $qattr->AddQuery("type != 'frame'"); 
      $sql_cond_doc = GetSqlCond(array_merge($v->GetFathersDoc(),$v->id), "docid");
      $qattr->AddQuery($sql_cond_doc);  
      $lattr=$qattr->Query();
      if (! $lattr) $lattr=array();
      while (list($ka,$va) = each($lattr)) {
	print "update doc{$v->id}  set {$va->id} = docvalue.value from docvalue where docvalue.docid=doc{$v->id}.id and attrid='{$va->id}';\n";    	  
      }
    }	 
  
  }      
}

//---------------------------------------------------------------------------------------------
function migrPerm() {
//---------------------------------------------------------------------------------------------

  include_once("FDL/Class.PDir.php");
  include_once("FDL/Class.PDoc.php");
  include_once("FDL/Class.PDocSearch.php");
  include_once("FDL/Class.WDocIncident.php");
  include_once("FDL/Class.WDocPropo.php");

  $appl = new Application();
  $appl->Set("FDL",	   $core);


  $dbaccess=$appl->GetParam("FREEDOM_DB");
  if ($dbaccess == "") {
    print "Database not found : param FREEDOM_DB";
    exit;
  }


  include_once("Class.ControlObject.php");


  include_once("Class.ObjectPermission.php");

  include_once("FDL/Class.DocPerm.php");

	
  
  $query = new QueryDb($dbaccess,"ControlObject");

      
    
  $table1 = $query->Query();




  if ($query->nb > 0)	{

    $qacl = new QueryDb($dbaccess,"Acl");
    $tacl = $qacl->Query();
    
    while(list($k,$v) = each($tacl)) {
      $aclToName[$v->id]=$v->name;
    }

    $qapp = new QueryDb($dbaccess,"Application");
    $tapp = $qapp->Query(0,0,"TABLE");
    
    while(list($k,$v) = each($tapp)) {
      $ClassApps[$v["id"]]=$v["name"];
    }

    while(list($k,$v) = each($table1)) 	    {

      $qp= new QueryDb($dbaccess,"ObjectPermission");
      $qp->AddQuery("id_obj=".$v->id_obj);
      $qp->AddQuery("ids_acl is not null");
      $lp=$qp->Query();
      if (count($lp>0)) {

	$class= $ClassApps[$v->id_class];
	$doc = new $class();


	print $v->description."\n";
	
	while(list($kp,$pdoc) = each($lp)) 	    {
	  if ($pdoc->isAffected()) {
	    $pdoc->GetPrivileges();
	    $dp= new Docperm($dbaccess, array($pdoc->id_obj,$pdoc->id_user));
	    $dp->UnSetControl();
	    while(list($ku,$upp) = each($pdoc->upprivileges)) 	    {
	      $pos = $doc->dacls[$aclToName[$upp]]["pos"];
	      $dp->SetControlP($pos);
	    }
	    while(list($ku,$upn) = each($pdoc->unprivileges)) 	    {
	      $pos = $doc->dacls[$aclToName[$upn]]["pos"];
	      $dp->SetControlN($pos);
	    }

	    $dp->Add();
	  }
	}
      }
      
    }	 
  
  }      
}


$table = (GetHttpVars("table","no") == "yes"); // 
$perm = (GetHttpVars("perm","no") == "yes"); // 
if ($table) migrTables();
if ($perm) migrPerm();
?>